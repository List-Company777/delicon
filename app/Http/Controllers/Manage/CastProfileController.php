<?php

namespace App\Http\Controllers\Manage;

use App\Jobs\NotifyFavoritedCastWorking;
use App\Jobs\NotifyNewCast;
use App\Models\Cast;
use App\Models\CastType;
use App\Models\CastBodyType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;

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
        return view('manage.cast_profile.index', compact('shop', 'casts'));
    }

    public function create()
    {
        $shop = $this->shopOrFail();
        $castTypes = CastType::orderBy('id')->get();
        $bodyTypes = CastBodyType::orderBy('id')->get();
        return view('manage.cast_profile.create', compact('shop', 'castTypes', 'bodyTypes'));
    }

    public function store(Request $request)
    {
        $shop = $this->shopOrFail();

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'age'            => ['nullable', 'integer', 'min:18', 'max:99'],
            'tall'           => ['nullable', 'integer', 'min:100', 'max:220'],
            'bust'           => ['nullable', 'integer', 'min:50', 'max:200'],
            'cup'            => ['nullable', 'string', 'max:3'],
            'west'           => ['nullable', 'integer', 'min:40', 'max:150'],
            'hip'            => ['nullable', 'integer', 'min:50', 'max:200'],
            'type_id'        => ['nullable', 'integer', 'exists:cast_types,id'],
            'body_id'        => ['nullable', 'integer', 'exists:cast_body_types,id'],
            'comment'        => ['nullable', 'string', 'max:2000'],
            'message'        => ['nullable', 'string', 'max:2000'],
            'status'         => ['required', 'in:active,inactive'],
            'is_recommended' => ['boolean'],
            'is_new'         => ['boolean'],
            'join_date'      => ['nullable', 'date'],
            'photo'          => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $cast = new Cast();
        $cast->shop_id = $shop->id;
        $cast->fill($data);
        $cast->is_recommended = $request->boolean('is_recommended');
        $wasNew = $cast->is_new;
        $cast->is_new = $request->boolean('is_new');
        if ($cast->is_new && !$wasNew) {
            // 新人フラグを新たに付けた時点でnew_sinceをセット（join_dateとcreated_atの遅い方）
            $jd = $cast->join_date ? \Illuminate\Support\Carbon::parse($cast->join_date) : null;
            $cast->new_since = $jd && $jd->gt($cast->created_at ?? now()) ? $jd->toDateString() : now()->toDateString();
        } elseif (!$cast->is_new) {
            $cast->new_since = null;
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
            'body_id'        => ['nullable', 'integer', 'exists:cast_body_types,id'],
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
        $cast->is_new = $request->boolean('is_new');
        if ($cast->is_new && !$wasNew) {
            $jd = $cast->join_date ? \Illuminate\Support\Carbon::parse($cast->join_date) : null;
            $cast->new_since = $jd && $jd->gt($cast->created_at ?? now()) ? $jd->toDateString() : now()->toDateString();
        } elseif (!$cast->is_new) {
            $cast->new_since = null;
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
        $filename = Str::random(20) . '.jpg';
        $dir = public_path('img/girl/00');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $manager = new ImageManager(new Driver());
        $img = $manager->decode($file->getPathname());
        $img->cover(400, 600);
        file_put_contents($dir . '/' . $filename, (string) $img->encode(new JpegEncoder(quality: 85)));
        return $filename;
    }
}
