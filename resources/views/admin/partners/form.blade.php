@extends('layouts.admin')
@section('title', $partner ? 'パートナー編集' : 'パートナー新規登録')
@section('content')
<div class="bg-gray-800 text-white py-4">
    <div class="max-w-3xl mx-auto px-4">
        <h1 class="font-bold">Admin › パートナー{{ $partner ? '編集' : '新規登録' }}</h1>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 py-8">
    <a href="{{ route('admin.partners.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← パートナー一覧</a>

    @if($errors->any())
        <div class="mt-4 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @if($partner)
        <form action="{{ route('admin.partners.update', $partner) }}" method="POST" class="mt-6 bg-white rounded-xl shadow-sm overflow-hidden">
        @csrf @method('PUT')
    @else
        <form action="{{ route('admin.partners.store') }}" method="POST" class="mt-6 bg-white rounded-xl shadow-sm overflow-hidden">
        @csrf
    @endif
        <table class="w-full text-sm">
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-36 whitespace-nowrap">種別 <span class="text-red-400">*</span></th>
                <td class="px-4 py-3">
                    <div class="flex gap-6">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="type" value="referral" {{ old('type', $partner?->type ?? 'referral') === 'referral' ? 'checked' : '' }}>
                            <span class="font-medium">紹介代理店</span>
                            <span class="text-xs text-gray-400">（当社→代理店へ手数料をバック）</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="type" value="management" {{ old('type', $partner?->type) === 'management' ? 'checked' : '' }}>
                            <span class="font-medium">管理代行代理店</span>
                            <span class="text-xs text-gray-400">（代理店→当社へ割引請求）</span>
                        </label>
                    </div>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-36 whitespace-nowrap">会社名 <span class="text-red-400">*</span></th>
                <td class="px-4 py-3">
                    <input type="text" name="company_name" value="{{ old('company_name', $partner?->company_name) }}" required
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500 @error('company_name') border-red-400 @enderror">
                    @error('company_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">担当者名</th>
                <td class="px-4 py-3">
                    <input type="text" name="contact_name" value="{{ old('contact_name', $partner?->contact_name) }}"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">メールアドレス <span class="text-red-400">*</span></th>
                <td class="px-4 py-3">
                    <input type="email" name="email" value="{{ old('email', $partner?->email) }}" required
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500 @error('email') border-red-400 @enderror">
                    @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">電話番号</th>
                <td class="px-4 py-3">
                    <input type="tel" name="tel" value="{{ old('tel', $partner?->tel) }}"
                           class="w-48 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">紹介コード</th>
                <td class="px-4 py-3">
                    <input type="text" name="referral_code" value="{{ old('referral_code', $partner?->referral_code) }}"
                           placeholder="空欄で自動生成（英数字8文字）"
                           class="w-48 border border-gray-300 rounded px-3 py-1.5 text-sm font-mono focus:outline-none focus:border-business-500 @error('referral_code') border-red-400 @enderror">
                    @if($partner)
                        <p class="text-xs text-gray-400 mt-1">紹介URL：{{ url('/register?ref=' . $partner->referral_code) }}</p>
                    @endif
                    @error('referral_code')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </td>
            </tr>
            @php
                $calcRatePercent = ($partner && $partner->isManagement())
                    ? number_format($partner->calculatedManagementRate() * 100, 2)
                    : '20.00';
                $overrideDecimal = ($partner && $partner->isManagement() && $partner->commission_rate_override !== null)
                    ? old('commission_rate_override', (float) $partner->commission_rate_override)
                    : old('commission_rate_override', '');
            @endphp
            <tr class="border-b border-gray-100" x-data="{ type: '{{ old('type', $partner?->type ?? 'referral') }}' }" @change.window="type = document.querySelector('input[name=type]:checked')?.value ?? type">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">
                    <span x-text="type === 'management' ? '割引率' : '手数料率'">手数料率</span>
                    <span class="text-red-400" x-show="type !== 'management'">*</span>
                </th>
                <td class="px-4 py-3">
                    {{-- 紹介代理店：手動入力 --}}
                    <div x-show="type === 'referral'">
                        <div class="flex items-center gap-2">
                            <input type="number" name="commission_rate" step="0.0001" min="0" max="1"
                                   value="{{ old('commission_rate', $partner && $partner->isReferral() ? (float)$partner->commission_rate : 0.1) }}"
                                   class="w-28 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500 @error('commission_rate') border-red-400 @enderror">
                            <span class="text-xs text-gray-400">（0.1 = 10%、0.2 = 20%）</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">紹介代理店：当社が支払う手数料率</p>
                        @error('commission_rate')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- 管理代行代理店：自動計算 + オーバーライド --}}
                    <div x-show="type === 'management'" class="space-y-3">
                        <div class="bg-gray-50 rounded-lg px-4 py-3 text-sm">
                            <p class="text-xs text-gray-400 mb-1">自動計算レート（掲載中店舗数をもとに算出）</p>
                            <p class="text-lg font-bold text-gray-800">{{ $calcRatePercent }}%</p>
                            <p class="text-xs text-gray-400 mt-1">ベース 20%・掲載中100店舗ごとに +1%・上限 30%</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium mb-1">オーバーライド（任意）</p>
                            <div class="flex items-center gap-2">
                                <input type="number" name="commission_rate_override" step="0.0001" min="0" max="1"
                                       value="{{ $overrideDecimal }}"
                                       placeholder="空欄で自動計算を使用"
                                       class="w-44 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500 @error('commission_rate_override') border-red-400 @enderror">
                                <span class="text-xs text-gray-400">（0.25 = 25%）</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">入力した場合はその値を固定。空欄に戻すと自動計算に戻ります。</p>
                            @error('commission_rate_override')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="bg-yellow-50 border border-yellow-200 rounded px-3 py-2 text-xs text-yellow-800">
                            ※ 重複・閉店・非公開店舗はカウントの対象になりません。
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">振込先情報</th>
                <td class="px-4 py-3">
                    <textarea name="bank_info" rows="3"
                              class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-business-500"
                              placeholder="例：○○銀行 △△支店 普通 1234567 カ）サンプル">{{ old('bank_info', $partner?->bank_info) }}</textarea>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">インボイス番号</th>
                <td class="px-4 py-3">
                    <input type="text" name="invoice_number" value="{{ old('invoice_number', $partner?->invoice_number) }}"
                           placeholder="T1234567890123"
                           class="w-48 border border-gray-300 rounded px-3 py-1.5 text-sm font-mono focus:outline-none focus:border-business-500 @error('invoice_number') border-red-400 @enderror">
                    <p class="text-xs text-gray-400 mt-1">適格請求書発行事業者の場合のみ入力（T + 13桁）</p>
                    @error('invoice_number')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">ステータス</th>
                <td class="px-4 py-3">
                    <label class="inline-flex items-center gap-2 mr-6">
                        <input type="radio" name="status" value="active" {{ old('status', $partner?->status ?? 'active') === 'active' ? 'checked' : '' }}>
                        <span class="text-green-700 font-medium">有効</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="status" value="inactive" {{ old('status', $partner?->status) === 'inactive' ? 'checked' : '' }}>
                        <span class="text-gray-500">無効</span>
                    </label>
                </td>
            </tr>
            <tr>
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">メモ</th>
                <td class="px-4 py-3">
                    <textarea name="notes" rows="3"
                              class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-business-500">{{ old('notes', $partner?->notes) }}</textarea>
                </td>
            </tr>
        </table>
        <div class="px-4 py-4 bg-gray-50 border-t border-gray-100 text-right">
            <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white text-sm font-bold px-6 py-2 rounded-lg transition">
                {{ $partner ? '更新する' : '登録する' }}
            </button>
        </div>
    </form>
</div>
@endsection
