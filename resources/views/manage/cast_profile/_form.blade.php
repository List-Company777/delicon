@php
    $cups = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N'];
@endphp
<table class="w-full text-sm">
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
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                   class="text-sm text-gray-600">
            <p class="text-xs text-gray-400 mt-1">JPEG・PNG・WebP、5MB以下、400×600px推奨</p>
            @error('photo')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">年齢</th>
        <td class="px-4 py-3">
            <div class="flex items-center gap-2">
                <input type="number" name="age" value="{{ old('age', $cast?->age) }}" min="18" max="99"
                       class="w-20 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400">
                <span class="text-gray-500 text-sm">歳</span>
            </div>
            @error('age')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">身長</th>
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
            <select name="body_id" class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400">
                <option value="">選択してください</option>
                @foreach($bodyTypes as $t)
                    <option value="{{ $t->id }}" @selected(old('body_id', $cast?->body_id) == $t->id)>{{ $t->name }}</option>
                @endforeach
            </select>
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">コメント</th>
        <td class="px-4 py-3">
            <textarea name="comment" rows="4" maxlength="2000"
                      class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400 resize-y">{{ old('comment', $cast?->comment) }}</textarea>
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">メッセージ</th>
        <td class="px-4 py-3">
            <textarea name="message" rows="3" maxlength="2000"
                      class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400 resize-y">{{ old('message', $cast?->message) }}</textarea>
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">入店日</th>
        <td class="px-4 py-3">
            <input type="date" name="join_date" value="{{ old('join_date', $cast?->join_date?->format('Y-m-d')) }}"
                   class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400">
            <span class="text-xs text-gray-400 ml-2">入店から30日以内は「新人」バッジが表示されます</span>
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
                <span class="text-sm text-gray-700">おすすめキャストとして表示する（一覧の上位に表示）</span>
            </label>
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
