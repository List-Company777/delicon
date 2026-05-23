@php
    $cups = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N'];
@endphp
<table class="w-full text-sm" style="color:#111827">
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap">源氏名 <span class="text-red-400">*</span></th>
        <td class="px-4 py-3">
            <input type="text" name="name" value="{{ old('name', $cast?->name) }}" required maxlength="100"
                   class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400 @error('name') border-red-400 @enderror">
            @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">写真</th>
        <td class="px-4 py-3">
            @if($cast?->img_file_name)
                <img src="{{ $cast->img_url }}" class="w-16 h-24 object-cover rounded mb-2 bg-gray-100">
            @endif
            <label class="inline-flex items-center gap-2 cursor-pointer bg-gray-100 hover:bg-gray-200 border border-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">
                📷 写真を選択
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="hidden"
                       data-filename-display="1">
            </label>
            <p class="text-xs text-gray-500 mt-1">JPEG・PNG・WebP、5MB以下、400×600px推奨</p>
            @error('photo')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">生年月日</th>
        <td class="px-4 py-3">
            <div class="flex items-center gap-2 flex-wrap">
                <input type="date" name="date_of_birth" id="date_of_birth"
                       value="{{ old('date_of_birth', $cast?->date_of_birth?->format('Y-m-d')) }}"
                       max="{{ now()->subYears(18)->format('Y-m-d') }}"
                       class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400">
                <span class="text-xs text-gray-400">入力すると年齢が自動計算・毎日更新されます</span>
            </div>
            <p class="text-xs text-gray-400 mt-1">表示には利用されません。年齢計算にのみ利用されます。18歳未満は登録できません。</p>
            @error('date_of_birth')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">年齢 <span class="text-red-400">*</span></th>
        <td class="px-4 py-3">
            <div class="flex items-center gap-2">
                <input type="number" name="age" id="age_input" value="{{ old('age', $cast?->age) }}" min="18" max="99"
                       class="w-20 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400">
                <span class="text-gray-500 text-sm">歳</span>
                <span class="text-xs text-gray-400">（生年月日を入力すると自動入力）</span>
            </div>
            @error('age')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">身長 <span class="text-red-400">*</span></th>
        <td class="px-4 py-3">
            <div class="flex items-center gap-2">
                <input type="number" name="tall" value="{{ old('tall', $cast?->tall) }}" min="100" max="220"
                       class="w-20 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400">
                <span class="text-gray-500 text-sm">cm</span>
            </div>
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">スリーサイズ</th>
        <td class="px-4 py-3">
            <div class="flex items-center gap-2 flex-wrap">
                <div class="flex items-center gap-1">
                    <span class="text-xs text-gray-500">B</span>
                    <input type="number" name="bust" value="{{ old('bust', $cast?->bust) }}" min="50" max="200"
                           class="w-16 border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:border-red-400">
                </div>
                <div class="flex items-center gap-1">
                    <span class="text-xs text-gray-500">W</span>
                    <input type="number" name="west" value="{{ old('west', $cast?->west) }}" min="40" max="150"
                           class="w-16 border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:border-red-400">
                </div>
                <div class="flex items-center gap-1">
                    <span class="text-xs text-gray-500">H</span>
                    <input type="number" name="hip" value="{{ old('hip', $cast?->hip) }}" min="50" max="200"
                           class="w-16 border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:border-red-400">
                </div>
                <div class="flex items-center gap-1">
                    <span class="text-xs text-gray-500">カップ</span>
                    <select name="cup" class="border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:border-red-400">
                        <option value="">—</option>
                        @foreach($cups as $c)
                            <option value="{{ $c }}" @selected(old('cup', $cast?->cup) === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">タイプ</th>
        <td class="px-4 py-3">
            <select name="type_id" class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400">
                <option value="">選択してください</option>
                @foreach($castTypes as $t)
                    <option value="{{ $t->id }}" @selected(old('type_id', $cast?->type_id) == $t->id)>{{ $t->name }}</option>
                @endforeach
            </select>
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">体型</th>
        <td class="px-4 py-3">
            <p class="text-sm text-gray-500">
                カップ・身長から自動設定されます
                @if($cast?->bodyType)
                    &mdash; 現在: <span class="font-medium text-gray-700">{{ $cast->bodyType->name }}</span>
                @endif
            </p>
            <p class="text-xs text-gray-400 mt-1">Aカップ→貧乳 / E〜Gカップ→巨乳 / H〜→爆乳 / 170cm〜→長身 / 〜150cm→小柄</p>
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">店舗からのコメント</th>
        <td class="px-4 py-3">
            <textarea name="comment" id="cast-comment" rows="10" maxlength="2000"
                      class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400 resize-y">{{ old('comment', $cast?->comment) }}</textarea>
            @if($cast && mb_strlen($cast->comment ?? '') < 100)
            <p id="noindex-warning" class="mt-2 text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                ⚠ コメントが100文字未満のため、この女性の詳細ページは現在<strong>検索エンジンの対象外（noindex）</strong>になっています。100文字以上入力すると検索対象になります。（現在 <span id="comment-len">{{ mb_strlen($cast->comment ?? '') }}</span>文字）
            </p>
            @elseif(!$cast)
            <p class="mt-1 text-xs text-gray-400">100文字以上入力すると検索エンジンの対象になります。</p>
            @endif
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">女性からのメッセージ</th>
        <td class="px-4 py-3">
            <textarea name="message" rows="8" maxlength="2000"
                      class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400 resize-y">{{ old('message', $cast?->message) }}</textarea>
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">入店日</th>
        <td class="px-4 py-3">
            <input type="date" name="join_date" value="{{ old('join_date', $cast?->join_date?->format('Y-m-d')) }}"
                   class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400">
            <span class="text-xs text-gray-400 ml-2">入店日（任意）</span>
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">本日出勤</th>
        <td class="px-4 py-3">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="working_today" value="1"
                       @checked(old('working_today', $cast?->working_date?->isToday()))
                       class="w-4 h-4 accent-red-600">
                <span class="text-sm text-gray-700">今日出勤している（トップページの「本日の出勤」に表示されます）</span>
            </label>
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">おすすめ</th>
        <td class="px-4 py-3">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_recommended" value="1"
                       @checked(old('is_recommended', $cast?->is_recommended))
                       class="w-4 h-4 accent-red-600">
                <span class="text-sm text-gray-700">おすすめキャストとして表示する（一覧の上位に表示・最大3人）</span>
            </label>
            @error('is_recommended')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">新人</th>
        <td class="px-4 py-3">
            @php
                $canSetNew = !$cast || (is_null($cast->new_since) && $cast->created_at->gte(now()->subMonth()));
            @endphp
            @if($cast && !is_null($cast->new_since))
                {{-- 既に使用済み --}}
                <p class="text-sm text-gray-400">
                    @if($cast->is_new)
                        <span class="text-green-600 font-medium">新人バッジ表示中</span> &mdash;
                        有効期限: {{ \Carbon\Carbon::parse($cast->new_since)->addMonth()->format('Y/m/d') }} まで
                    @else
                        新人バッジは使用済みです（再設定不可）
                    @endif
                </p>
                <input type="hidden" name="is_new" value="{{ $cast->is_new ? '1' : '0' }}">
            @elseif(!$canSetNew && $cast)
                {{-- 登録から1ヶ月超過 --}}
                <p class="text-sm text-gray-400">登録から1ヶ月以上経過しているため新人バッジを設定できません</p>
                <input type="hidden" name="is_new" value="0">
            @else
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_new" value="1"
                           @checked(old('is_new', $cast?->is_new))
                           class="w-4 h-4 accent-red-600">
                    <span class="text-sm text-gray-700">新人バッジを表示する（フラグを付けた日または入店日の遅い方から1ヶ月間・1回限り）</span>
                </label>
            @endif
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 align-top whitespace-nowrap">特徴・チャーム</th>
        <td class="px-4 py-3">
            <div class="flex flex-wrap gap-2">
                @foreach($charmTypes as $charm)
                <label class="flex items-center gap-1.5 cursor-pointer bg-gray-50 border border-gray-200 hover:border-red-300 rounded-full px-3 py-1.5 text-sm transition has-[:checked]:bg-red-50 has-[:checked]:border-red-400">
                    <input type="checkbox" name="charm_ids[]" value="{{ $charm->id }}"
                           @checked(in_array($charm->id, old('charm_ids', $cast ? $cast->charms->pluck('id')->all() : [])))
                           class="accent-red-600 w-3.5 h-3.5">
                    {{ $charm->name }}
                </label>
                @endforeach
            </div>
            <p class="text-xs text-gray-400 mt-2">複数選択できます。AIが自動で入力することもあります（確認・修正可）</p>
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">公開状態</th>
        <td class="px-4 py-3">
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="status" value="active"
                           @checked(old('status', $cast?->status ?? 'active') === 'active')
                           class="accent-red-600">
                    <span class="text-sm text-gray-700">公開</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="status" value="inactive"
                           @checked(old('status', $cast?->status) === 'inactive')
                           class="accent-red-600">
                    <span class="text-sm text-gray-700">非公開</span>
                </label>
            </div>
        </td>
    </tr>
</table>

<script>
(function() {
    const dobInput = document.getElementById('date_of_birth');
    const ageInput = document.getElementById('age_input');
    if (!dobInput || !ageInput) return;

    function calcAge(dob) {
        const today = new Date();
        const d = new Date(dob);
        let age = today.getFullYear() - d.getFullYear();
        const m = today.getMonth() - d.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < d.getDate())) age--;
        return age;
    }

    dobInput.addEventListener('change', function() {
        if (this.value) {
            const age = calcAge(this.value);
            if (age >= 18 && age <= 99) ageInput.value = age;
        }
    });

    if (dobInput.value && !ageInput.value) {
        const age = calcAge(dobInput.value);
        if (age >= 18 && age <= 99) ageInput.value = age;
    }
})();
</script>

@push('scripts')
<script nonce="{{ Vite::cspNonce() }}">
document.querySelectorAll('[data-filename-display]').forEach(function(input) {
    input.addEventListener('change', function() {
        var span = this.closest('label') && this.closest('label').querySelector('span');
        if (span) span.textContent = this.files[0]?.name ?? '';
    });
});
</script>
@endpush
