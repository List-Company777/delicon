@php
    $employmentLabels = ['PART_TIME'=>'アルバイト','CONTRACTOR'=>'業務委託','FULL_TIME'=>'正社員','PER_DIEM'=>'日払い','OTHER'=>'その他'];
    $wagePlaceholders = [
        'hourly'  => ['min'=>'例：3000',   'max'=>'例：8000'],
        'daily'   => ['min'=>'例：30000',  'max'=>'例：80000'],
        'monthly' => ['min'=>'例：250000', 'max'=>'例：350000'],
    ];
    $currentWageType = old('wage_type', $job?->wage_type ?? 'hourly');
@endphp
<table class="w-full text-sm">
    <tbody>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-36 whitespace-nowrap">公開設定</th>
        <td class="px-4 py-3">
            @foreach(['active'=>'公開','inactive'=>'非公開','draft'=>'下書き'] as $val => $label)
                <label class="inline-flex items-center gap-1.5 mr-4">
                    <input type="radio" name="status" value="{{ $val }}"
                           {{ old('status', $job?->status ?? 'inactive') === $val ? 'checked' : '' }}>
                    <span @class(['text-green-700 font-medium'=>$val==='active','text-gray-400'=>$val==='draft','text-gray-600'=>$val==='inactive'])>{{ $label }}</span>
                </label>
            @endforeach
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">職種 <span class="text-red-400">*</span></th>
        <td class="px-4 py-3">
            <select name="job_type_id" required class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                @foreach($jobTypes as $jt)
                    <option value="{{ $jt->id }}" {{ old('job_type_id', $job?->job_type_id) == $jt->id ? 'selected' : '' }}>{{ $jt->name }}</option>
                @endforeach
            </select>
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">求人タイトル <span class="text-red-400">*</span></th>
        <td class="px-4 py-3" x-data="{ n: {{ mb_strlen(old('title', $job?->title ?? ''), 'UTF-8') }} }">
            <input type="text" name="title" value="{{ old('title', $job?->title) }}" required
                   maxlength="60" @input="n = $event.target.value.length"
                   class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500 @error('title') border-red-400 @enderror">
            <div class="flex justify-between items-center mt-0.5">
                <span>@error('title')<span class="text-xs text-red-500">{{ $message }}</span>@enderror</span>
                <span class="text-xs" :class="n > 54 ? 'text-red-500 font-medium' : n > 44 ? 'text-amber-500' : 'text-gray-400'" x-text="n + ' / 60文字'"></span>
            </div>

            {{-- タイトルヒントパネル --}}
            <div x-data="{ open: false }" class="mt-2">
                <button type="button" @click="open = !open"
                        class="text-xs text-business-600 hover:text-business-800 flex items-center gap-1">
                    <span x-text="open ? '▲ ヒントを閉じる' : '▼ クリックされやすいタイトルの作り方'"></span>
                </button>
                <div x-show="open" x-transition class="mt-3 bg-amber-50 border border-amber-100 rounded-lg p-4 text-xs text-gray-700 space-y-3">
                    <p class="text-amber-800 bg-amber-100 rounded px-3 py-2 text-xs">職種名（ホステス・バーテンダーなど）はシステムが自動で検索対象にします。タイトルには<strong>求職者が検索しそうな条件ワード</strong>を入れると効果的です。</p>
                    <div class="space-y-2">
                        <p class="font-semibold text-amber-700">✅ 入れると効果的なキーワード</p>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-gray-600">
                            <span>・未経験歓迎</span>
                            <span>・日払い・週払いOK</span>
                            <span>・短時間勤務OK</span>
                            <span>・副業・Wワーク歓迎</span>
                            <span>・交通費全額支給</span>
                            <span>・研修充実</span>
                            <span>・ノルマなし</span>
                            <span>・個室待機あり</span>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <p class="font-semibold text-amber-700">📝 タイトル例</p>
                        <ul class="space-y-1 text-gray-600">
                            <li>👉「未経験・短時間OK｜日払い可・ノルマなし｜丁寧に研修します」</li>
                            <li>👉「週2日〜・交通費全額支給｜アットホームな職場でWワーク歓迎」</li>
                            <li>👉「高収入＆安心サポート｜未経験スタート月収30万円も可」</li>
                        </ul>
                    </div>
                    <div class="space-y-1">
                        <p class="font-semibold text-amber-700">⚠️ 避けたほうがよい表現</p>
                        <ul class="space-y-0.5 text-gray-500">
                            <li>・「急募」「大量採用」だけでは差別化できない</li>
                            <li>・店舗名のみ（求職者には伝わらない）</li>
                            <li>・曖昧な表現「いい仕事あります」（何の仕事か不明）</li>
                        </ul>
                    </div>
                </div>
            </div>
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">求人詳細</th>
        <td class="px-4 py-3" x-data="{ n: {{ mb_strlen(old('description', $job?->description ?? ''), 'UTF-8') }} }">
            <textarea name="description" rows="8" maxlength="3000" @input="n = $event.target.value.length"
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-business-500">{{ old('description', $job?->description) }}</textarea>
            <div class="flex justify-end mt-0.5">
                <span class="text-xs" :class="n > 2700 ? 'text-red-500 font-medium' : n > 2000 ? 'text-amber-500' : 'text-gray-400'" x-text="n + ' / 3,000文字'"></span>
            </div>

            {{-- 記入ヒントパネル --}}
            <div x-data="{ open: false }" class="mt-2">
                <button type="button" @click="open = !open"
                        class="text-xs text-business-600 hover:text-business-800 flex items-center gap-1">
                    <span x-text="open ? '▲ 記入ヒントを閉じる' : '▼ 記入ヒントを見る（Google に評価される項目）'"></span>
                </button>
                <div x-show="open" x-transition class="mt-3 bg-blue-50 border border-blue-100 rounded-lg p-4 text-xs text-gray-700 space-y-3">
                    <p class="text-blue-700 font-medium text-xs">以下の項目を詳細に書くほど、Googleへの情報提供が充実し求職者に伝わりやすくなります。</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3">
                        <div>
                            <p class="font-semibold text-gray-600 mb-1">📋 仕事内容</p>
                            <ul class="space-y-0.5 text-gray-500 list-disc list-inside">
                                <li>接客スタイル・業務の流れ</li>
                                <li>1日のスケジュール例</li>
                                <li>ノルマの有無</li>
                            </ul>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-600 mb-1">✅ 応募資格・条件</p>
                            <ul class="space-y-0.5 text-gray-500 list-disc list-inside">
                                <li>年齢・経験の条件（未経験歓迎など）</li>
                                <li>外見・体型の条件（ある場合）</li>
                                <li>在籍しながらの掛け持ち可否</li>
                            </ul>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-600 mb-1">💰 給与・収入の補足</p>
                            <ul class="space-y-0.5 text-gray-500 list-disc list-inside">
                                <li>バック制度・インセンティブの内容</li>
                                <li>平均的な月収・稼ぎ例</li>
                                <li>日払い・週払いの可否</li>
                            </ul>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-600 mb-1">🎁 待遇・福利厚生</p>
                            <ul class="space-y-0.5 text-gray-500 list-disc list-inside">
                                <li>交通費支給の有無・上限</li>
                                <li>衣装・ドレス代の負担</li>
                                <li>ロッカー・シャワー・寮の有無</li>
                            </ul>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-600 mb-1">🎓 研修・サポート</p>
                            <ul class="space-y-0.5 text-gray-500 list-disc list-inside">
                                <li>未経験者向け研修の有無・期間</li>
                                <li>指名が付くまでのフォロー体制</li>
                                <li>在籍スタッフ数・雰囲気</li>
                            </ul>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-600 mb-1">🕐 シフト・働き方</p>
                            <ul class="space-y-0.5 text-gray-500 list-disc list-inside">
                                <li>週何日・何時間からOKか</li>
                                <li>学生・副業・Wワーク可否</li>
                                <li>急なシフト変更への対応</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </td>
    </tr>
    </tbody>
    {{-- 給与セクション：3行をまとめて1つのAlpineスコープで管理 --}}
    <tbody x-data="{
        wageType: '{{ $currentWageType }}',
        labels:   { hourly: '時給', daily: '日給', monthly: '月給' },
        pMin:     { hourly: '例：3000', daily: '例：30000', monthly: '例：250000' },
        pMax:     { hourly: '例：8000', daily: '例：80000', monthly: '例：350000' },
    }">
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">給与形態</th>
        <td class="px-4 py-3">
            <input type="hidden" name="wage_type" :value="wageType">
            @foreach(['hourly'=>'時給','daily'=>'日給','monthly'=>'月給'] as $val=>$wageLabel)
                <label class="inline-flex items-center gap-1.5 mr-4">
                    <input type="radio" value="{{ $val }}" @change="wageType = '{{ $val }}'"
                           {{ $currentWageType === $val ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">{{ $wageLabel }}</span>
                </label>
            @endforeach
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap" x-text="labels[wageType] + '（下限）'">{{ ['hourly'=>'時給','daily'=>'日給','monthly'=>'月給'][$currentWageType] }}（下限）</th>
        <td class="px-4 py-3">
            <input type="number" name="hourly_wage_min" value="{{ old('hourly_wage_min', $job?->hourly_wage_min) }}"
                   min="0" :placeholder="pMin[wageType]"
                   class="w-36 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500"> 円
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap" x-text="labels[wageType] + '（上限）'">{{ ['hourly'=>'時給','daily'=>'日給','monthly'=>'月給'][$currentWageType] }}（上限）</th>
        <td class="px-4 py-3">
            <input type="number" name="hourly_wage_max" value="{{ old('hourly_wage_max', $job?->hourly_wage_max) }}"
                   min="0" :placeholder="pMax[wageType]"
                   class="w-36 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500"> 円
        </td>
    </tr>
    </tbody>
    <tbody>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">勤務時間</th>
        <td class="px-4 py-3">
            <input type="text" name="working_hours" value="{{ old('working_hours', $job?->working_hours) }}"
                   placeholder="例：20:00〜翌5:00" maxlength="100"
                   class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
            <p class="text-xs text-gray-400 mt-0.5 text-right">最大100文字</p>
        </td>
    </tr>
    <tr class="border-b border-gray-100">
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">雇用形態</th>
        <td class="px-4 py-3">
            <select name="employment_type" class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                <option value="">選択してください</option>
                @foreach($employmentLabels as $val => $label)
                    <option value="{{ $val }}" {{ old('employment_type', $job?->employment_type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">※ キャスト募集で雇用形態がわからない場合、お店が働き方を決めるならアルバイト、本人の裁量で働くなら業務委託を選択ください。</p>
        </td>
    </tr>
    <tr>
        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">求人画像</th>
        <td class="px-4 py-3">
            @if($job?->image_path)
                <div class="mb-3">
                    <picture>
                        <source srcset="{{ Storage::url(\App\Services\ImageService::webpPath($job->image_path)) }}" type="image/webp">
                        <img src="{{ Storage::url($job->image_path) }}" alt="現在の求人画像"
                             class="w-48 h-32 object-cover rounded border border-gray-200">
                    </picture>
                    <label class="flex items-center gap-1.5 mt-2 text-sm text-red-600 cursor-pointer">
                        <input type="checkbox" name="delete_image" value="1">
                        <span>この画像を削除する</span>
                    </label>
                </div>
            @endif
            <label class="inline-block cursor-pointer">
                <span class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 transition">
                    📁 ファイルを選択
                </span>
                <input type="file" name="image" accept="image/*" class="hidden"
                       onchange="this.parentElement.querySelector('span').textContent = this.files[0]?.name ?? 'ファイルを選択'">
            </label>
            <p class="text-xs text-gray-400 mt-1">JPEG/PNG/WebP・5MB以下。推奨サイズ：1280×720px（16:9）</p>
            <p class="text-xs text-blue-600 mt-1">💡 求人専用の画像を登録すると、検索結果カードでは店舗メイン画像より優先して表示されます。</p>
            @error('image')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </td>
    </tr>
    </tbody>
</table>
<div class="mx-4 my-4 rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-xs text-blue-800 leading-relaxed">
    <p class="font-bold mb-1">🔍 検索結果への反映について</p>
    <ul class="space-y-0.5 text-blue-700">
        <li>・<span class="font-medium">求人タイトル・職種</span>：フリーワード検索の対象になります。具体的なキーワードを含めると見つかりやすくなります。</li>
        <li>・<span class="font-medium">検索表示対象</span>：女性ナイトワーク／男性ナイトワークのどちらの検索結果に表示するかを決定します。</li>
        <li>・<span class="font-medium">給与・給与形態</span>：給与フィルターで絞り込まれる際の条件になります。</li>
    </ul>
</div>
