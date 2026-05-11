<?php

namespace App\Http\Controllers\Manage;

use App\Jobs\NotifyFavoritedCastWorking;
use App\Jobs\NotifyNewCast;
use App\Models\Cast;
use App\Models\CastFavorite;
use App\Models\CastType;
use App\Models\CastBodyType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\WebpEncoder;

class CastProfileController extends BaseController
{
    public function index()
    {
        $shop = $this->shopOrFail();
        $casts = Cast::where('shop_id', $shop->id)
            ->orderByDesc('is_recommended')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        $castIds = $casts->pluck('id')->all();

        // キャストごとのお気に入り数
        $favoriteCounts = CastFavorite::whereIn('cast_id', $castIds)
            ->selectRaw('cast_id, count(*) as cnt')
            ->groupBy('cast_id')
            ->pluck('cnt', 'cast_id');

        // 店舗全キャストのお気に入り登録者一覧（名前・対象キャスト名・希望曜日・時間帯）
        $shopFanList = CastFavorite::whereIn('cast_favorites.cast_id', $castIds)
            ->join('users', 'cast_favorites.user_id', '=', 'users.id')
            ->join('casts', 'cast_favorites.cast_id', '=', 'casts.id')
            ->select(
                'users.name as user_name',
                'casts.name as cast_name',
                'users.preferred_days',
                'users.preferred_times'
            )
            ->orderBy('casts.name')
            ->get()
            ->map(function($r) {
                $r->preferred_days  = is_string($r->preferred_days)  ? json_decode($r->preferred_days, true)  : ($r->preferred_days ?? []);
                $r->preferred_times = is_string($r->preferred_times) ? json_decode($r->preferred_times, true) : ($r->preferred_times ?? []);
                return $r;
            });

        return view('manage.cast_profile.index', compact('shop', 'casts', 'favoriteCounts', 'shopFanList'));
    }

    public function create()
    {
        $shop = $this->shopOrFail();
        $limit = $this->castLimit($shop->plan ?? 99);
        $currentCount = Cast::where('shop_id', $shop->id)->where('status', 'active')->count();
        $castTypes = CastType::orderBy('id')->get();
        $bodyTypes = CastBodyType::orderBy('id')->get();
        return view('manage.cast_profile.create', compact('shop', 'castTypes', 'bodyTypes', 'limit', 'currentCount'));
    }

    private function castLimit(int $plan): ?int
    {
        return null;
    }

    public function store(Request $request)
    {
        $shop = $this->shopOrFail();

        // プラン別登録上限チェック
        $limit = $this->castLimit($shop->plan ?? 99);
        if ($limit !== null) {
            $current = Cast::where('shop_id', $shop->id)->where('status', 'active')->count();
            if ($current >= $limit) {
                return back()->withErrors(['name' => "在籍登録数の上限（{$limit}人）に達しています。プランのアップグレードについてお問い合わせください。"])->withInput();
            }
        }

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'age'            => ['nullable', 'integer', 'min:18', 'max:99'],
            'tall'           => ['nullable', 'integer', 'min:100', 'max:220'],
            'bust'           => ['nullable', 'integer', 'min:50', 'max:200'],
            'cup'            => ['nullable', 'string', 'max:3'],
            'west'           => ['nullable', 'integer', 'min:40', 'max:150'],
            'hip'            => ['nullable', 'integer', 'min:50', 'max:200'],
            'type_id'        => ['nullable', 'integer', 'exists:cast_types,id'],
            'comment'        => ['nullable', 'string', 'max:2000'],
            'message'        => ['nullable', 'string', 'max:2000'],
            'status'         => ['required', 'in:active,inactive'],
            'is_recommended' => ['boolean'],
            'is_new'         => ['boolean'],
            'join_date'      => ['nullable', 'date'],
            'photo'          => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        // 名前＋年齢の重複チェック（同じ店舗内）
        if (!empty($data['age'])) {
            $exists = Cast::where('shop_id', $shop->id)
                ->where('name', $data['name'])
                ->where('age', (int) $data['age'])
                ->exists();
            if ($exists) {
                return back()->withErrors(['name' => "「{$data['name']}」（{$data['age']}歳）は既に登録されています。"])->withInput();
            }
        }

        $cast = new Cast();
        $cast->shop_id = $shop->id;
        $cast->fill($data);
        $cast->is_recommended = $request->boolean('is_recommended');
        $cast->is_new = $request->boolean('is_new');
        if ($cast->is_new) {
            // 新規登録時: is_new が ON なら new_since をセット（join_dateと今日の遅い方）
            $jd = $cast->join_date ? \Illuminate\Support\Carbon::parse($cast->join_date) : null;
            $cast->new_since = $jd && $jd->gt(now()) ? $jd->toDateString() : now()->toDateString();
        }
        $cast->working_date   = $request->boolean('working_today') ? today() : null;

        if ($request->hasFile('photo')) {
            $cast->img_file_name = $this->savePhoto($request->file('photo'));
        }

        $cast->save();

        // 新人登録通知
        if ($cast->join_date) {
            NotifyNewCast::dispatch($cast->id);
        }

        return redirect()->route('manage.cast-profile.index')
            ->with('success', 'キャストを登録しました');
    }

    public function edit(int $id)
    {
        $shop = $this->shopOrFail();
        $cast = Cast::where('shop_id', $shop->id)->findOrFail($id);
        $castTypes = CastType::orderBy('id')->get();
        $bodyTypes = CastBodyType::orderBy('id')->get();
        return view('manage.cast_profile.edit', compact('shop', 'cast', 'castTypes', 'bodyTypes'));
    }

    public function update(Request $request, int $id)
    {
        $shop = $this->shopOrFail();
        $cast = Cast::where('shop_id', $shop->id)->findOrFail($id);

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'age'            => ['nullable', 'integer', 'min:18', 'max:99'],
            'tall'           => ['nullable', 'integer', 'min:100', 'max:220'],
            'bust'           => ['nullable', 'integer', 'min:50', 'max:200'],
            'cup'            => ['nullable', 'string', 'max:3'],
            'west'           => ['nullable', 'integer', 'min:40', 'max:150'],
            'hip'            => ['nullable', 'integer', 'min:50', 'max:200'],
            'type_id'        => ['nullable', 'integer', 'exists:cast_types,id'],
            'comment'        => ['nullable', 'string', 'max:2000'],
            'message'        => ['nullable', 'string', 'max:2000'],
            'status'         => ['required', 'in:active,inactive'],
            'is_recommended' => ['boolean'],
            'is_new'         => ['boolean'],
            'join_date'      => ['nullable', 'date'],
            'photo'          => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $cast->fill($data);
        $cast->is_recommended = $request->boolean('is_recommended');
        $wasNew = $cast->is_new;
        $wantNew = $request->boolean('is_new');
        if ($wantNew && !$wasNew) {
            // 制約: new_since が null（未使用）かつ created_at から1ヶ月以内のみ許可
            $canSetNew = is_null($cast->new_since) && $cast->created_at->gte(now()->subMonth());
            if ($canSetNew) {
                $cast->is_new = true;
                $jd = $cast->join_date ? \Illuminate\Support\Carbon::parse($cast->join_date) : null;
                $cast->new_since = $jd && $jd->gt($cast->created_at) ? $jd->toDateString() : now()->toDateString();
            }
            // 条件を満たさない場合はフラグを変更しない（サイレントに無視）
        } elseif (!$wantNew && $wasNew) {
            $cast->is_new = false;
            // new_since は消さない（使用済みの記録として保持）
        }
        $wasWorking = $cast->working_date && $cast->working_date->isToday();
        $cast->working_date = $request->boolean('working_today') ? today() : null;
        $nowWorking = $request->boolean('working_today');

        if ($request->hasFile('photo')) {
            $cast->img_file_name = $this->savePhoto($request->file('photo'));
        }

        $cast->save();

        // 新たに出勤フラグが立った場合のみ通知
        if ($nowWorking && !$wasWorking) {
            NotifyFavoritedCastWorking::dispatch($cast->id);
        }

        return redirect()->route('manage.cast-profile.index')
            ->with('success', 'キャスト情報を更新しました');
    }

    public function destroy(int $id)
    {
        $shop = $this->shopOrFail();
        $cast = Cast::where('shop_id', $shop->id)->findOrFail($id);
        $cast->delete();
        return back()->with('success', 'キャストを削除しました');
    }

    private function savePhoto($file): string
    {
        $base = Str::random(20);
        $dir = public_path('img/girl/uploads');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $manager = new ImageManager(new Driver());
        $img = $manager->decode($file->getPathname());
        $img->cover(400, 600);

        $jpgPath = "{$dir}/{$base}big.jpg";
        file_put_contents($jpgPath, (string) $img->encode(new JpegEncoder(quality: 85)));
        file_put_contents("{$jpgPath}.webp", (string) $img->encode(new WebpEncoder(quality: 80)));

        return "/img/girl/uploads/{$base}";
    }
}
