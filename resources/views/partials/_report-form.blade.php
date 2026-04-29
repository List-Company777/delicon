{{--
  通報フォーム パーシャル
  必須変数: $reportTargetType ('shop'|'job'), $reportTargetId (int)
--}}

{{-- 通報完了モーダル --}}
@if(session('report_sent'))
<div x-data="{ show: true }"
     x-init="$nextTick(() => { window.scrollTo({ top: 0, behavior: 'smooth' }) })"
     x-show="show"
     class="fixed inset-0 z-50 flex items-center justify-center px-4"
     style="background:rgba(0,0,0,0.5)">
    <div class="bg-white rounded-2xl shadow-xl p-8 max-w-sm w-full text-center">
        <div class="text-3xl mb-4">✓</div>
        <h2 class="text-lg font-bold text-gray-800 mb-2">通報を受け付けました</h2>
        <p class="text-sm text-gray-500 mb-6">ご協力ありがとうございます。</p>
        <button @click="show = false"
                class="bg-business-700 hover:bg-business-600 text-white text-sm font-medium px-6 py-2.5 rounded-lg transition w-full">
            閉じる
        </button>
    </div>
</div>
@endif

<div class="mt-8" x-data="{ open: false }">
    <button @click="open = !open"
            type="button"
            class="text-xs text-gray-400 hover:text-gray-500 underline underline-offset-2">
        この{{ $reportTargetType === 'shop' ? '店舗' : '求人' }}を通報する
    </button>

    <div x-show="open" x-cloak class="mt-3 border border-gray-200 rounded-xl p-5 bg-gray-50">

        @if(session('report_error'))
            <p class="text-sm text-red-600">{{ session('report_error') }}</p>
        @else

        <p class="text-xs text-gray-500 mb-3">掲載情報に問題がある場合にご利用ください。</p>
        <p class="text-xs text-gray-400 mb-4">通報いただいた内容は精査の上で対応を決定させていただきますが、対応の詳細についてはご連絡いたしておりません。予めご了承ください。</p>

        <form action="{{ route('report.send') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="target_type" value="{{ $reportTargetType }}">
            <input type="hidden" name="target_id"   value="{{ $reportTargetId }}">

            <div>
                <p class="text-xs font-medium text-gray-600 mb-2">通報の種別 <span class="text-red-500">*</span></p>
                <div class="space-y-1.5">
                    @foreach(['closed' => '閉店している', 'false_info' => '虚偽・誇大情報', 'inappropriate' => '不適切なコンテンツ', 'other' => 'その他'] as $val => $label)
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="radio" name="reason" value="{{ $val }}" required
                               class="text-business-700 focus:ring-business-600">
                        {{ $label }}
                    </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">詳細コメント（任意・300文字以内）</label>
                <textarea name="comment" rows="3" maxlength="300"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-business-500"
                          placeholder="具体的な内容があればご記入ください"></textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">メールアドレス（任意）</label>
                <input type="email" name="reporter_email" maxlength="200"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-business-500"
                       placeholder="your@email.com">
            </div>

            <button type="submit"
                    class="bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                送信する
            </button>
        </form>

        @endif
    </div>
</div>
