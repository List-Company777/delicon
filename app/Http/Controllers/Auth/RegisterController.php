<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Genre;
use App\Models\Partner;
use App\Models\Prefecture;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    // 登録フォーム表示（LINE認証後 or 直接アクセス）
    public function show(Request $request)
    {
        $genres      = Genre::orderBy('id')->get();
        $prefectures = Prefecture::orderBy('id')->get();
        $areas       = Area::orderBy('id')->get(['id', 'prefecture_id', 'name']);
        $partner     = null;

        if ($ref = $request->query('ref')) {
            $partner = Partner::where('referral_code', $ref)->where('status', 'active')->first();
            if ($partner) {
                session(['referral_code' => $ref]);
            }
        }

        return view('auth.register', compact('genres', 'prefectures', 'areas', 'partner'));
    }

    // 登録処理
    public function store(Request $request)
    {
        $isClaimMode = $request->filled('claim_shop_id');

        $rules = [
            'name'             => ['required', 'string', 'max:100'],
            'email'            => ['required', 'email', 'max:200', 'unique:users,email'],
            'password'         => ['required', 'confirmed', Password::min(8)],
            'agree_terms'      => ['accepted'],
            'agree_advertiser' => ['accepted'],
            'agree_privacy'    => ['accepted'],
        ];

        if ($isClaimMode) {
            // 引き継ぎ登録：既存のXML店舗に紐付け
            $rules['claim_shop_id'] = ['required', 'exists:shops,id'];
            $rules['tel']           = ['nullable', 'string', 'max:20'];
        } else {
            // 通常登録：新規店舗を作成
            $rules['shop_name']     = ['required', 'string', 'max:100'];
            $rules['genre_id']      = ['required', 'exists:genres,id'];
            $rules['prefecture_id'] = ['required', 'exists:prefectures,id'];
            $rules['tel']           = ['nullable', 'string', 'max:20'];
            $rules['area_id']       = ['nullable', 'exists:areas,id'];
        }

        $request->validate($rules, [
            'name.required'             => 'お名前を入力してください。',
            'email.required'            => 'メールアドレスを入力してください。',
            'email.email'               => '正しいメールアドレスの形式で入力してください。',
            'email.unique'              => 'このメールアドレスはすでに登録されています。',
            'password.required'         => 'パスワードを入力してください。',
            'password.confirmed'        => 'パスワードが一致していません。入力内容をご確認ください。',
            'password.min'              => 'パスワードは8文字以上で入力してください。',
            'shop_name.required'        => '店舗名を入力してください。',
            'genre_id.required'         => '業種を選択してください。',
            'prefecture_id.required'    => '都道府県を選択してください。',
            'agree_terms.accepted'      => 'サービス利用規約への同意が必要です。',
            'agree_advertiser.accepted' => '掲載規約への同意が必要です。',
            'agree_privacy.accepted'    => 'プライバシーポリシーへの同意が必要です。',
        ]);

        // LINE セッションがあれば紐付け
        $lineUserId = session('line_user_id');
        $lineName   = session('line_name');

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => $request->password,
            'line_user_id'      => $lineUserId,
            'line_name'         => $lineName,
            'email_verified_at' => $lineUserId ? now() : null,
        ]);

        // 紹介コードからパートナーを解決
        $refCode = session('referral_code') ?? $request->input('referral_code');
        $partner = $refCode
            ? Partner::where('referral_code', $refCode)->where('status', 'active')->first()
            : null;

        if ($isClaimMode) {
            // XML連携店舗を引き継ぎ
            $shop = Shop::where('id', $request->claim_shop_id)
                ->where('xml_source', 'upstage')
                ->whereDoesntHave('users')
                ->firstOrFail();

            // 電話番号が未設定なら入力値で補完
            if ($request->filled('tel') && !$shop->tel) {
                $shop->tel = $request->tel;
            }
            if ($partner && !$shop->partner_id) {
                $shop->partner_id = $partner->id;
            }
            // statusはそのまま（XML連携店舗はactive済み）
            $shop->save();
        } else {
            // 通常登録：新規店舗を作成
            $prefectureId = $request->area_id
                ? Area::find($request->area_id)->prefecture_id
                : $request->prefecture_id;

            $shop = Shop::create([
                'name'          => $request->shop_name,
                'genre_id'      => $request->genre_id,
                'tel'           => $request->tel,
                'area_id'       => $request->area_id,
                'prefecture_id' => $prefectureId,
                'status'        => 'inactive',
                'partner_id'    => $partner?->id,
            ]);
        }

        $shop->users()->attach($user->id, ['role' => 'owner']);

        session()->forget(['line_user_id', 'line_name', 'referral_code']);

        Auth::login($user);

        if (! $lineUserId) {
            $user->sendEmailVerificationNotification();
            return redirect()->route('verification.notice');
        }

        return redirect(route('manage.dashboard'));
    }

    // XML連携店舗の検索API（登録フォームのAJAX用）
    public function xmlShopSearch(Request $request): \Illuminate\Http\JsonResponse
    {
        $name = trim($request->query('name', ''));
        if (mb_strlen($name) < 1) {
            return response()->json([]);
        }

        $shops = Shop::where('xml_source', 'upstage')
            ->where(function ($q) use ($name) {
                $q->where('name', 'like', '%' . $name . '%')
                  ->orWhere('xml_id', $name);
            })
            ->whereDoesntHave('users')
            ->select('id', 'name', 'prefecture_id', 'area_id', 'genre_id')
            ->with(['prefecture:id,name', 'area:id,name', 'genre:id,name'])
            ->limit(5)
            ->get()
            ->map(fn($s) => [
                'id'         => $s->id,
                'name'       => $s->name,
                'prefecture' => $s->prefecture?->name,
                'area'       => $s->area?->name,
                'genre'      => $s->genre?->name,
            ]);

        return response()->json($shops);
    }
}
