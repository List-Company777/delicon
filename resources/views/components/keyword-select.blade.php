{{--
    インクリメンタル検索つきセレクト（グループ対応版）
    Props:
      name       : フォームのinput name (例: area_id)
                   'area_id'     → area_id + prefecture_id の hidden を管理
                   'job_type_id' → job_type_id + genre_id の hidden を管理
      placeholder: 未選択時のラベル
      initId     : 初期選択ID
      initName   : 初期選択名
      initType   : 初期選択タイプ (area/prefecture/job_type/genre)
      jsVar      : 選択肢データの window 変数名（items は {id, name, slug, type} を含む）
--}}
<div
    x-data="{
        search: '',
        selectedId:   {{ $initId ?? 'null' }},
        selectedType: {{ $initType ? json_encode($initType) : 'null' }},
        selectedName: {{ $initName ? json_encode($initName) : json_encode($placeholder) }},
        open: false,
        items: window.{{ $jsVar }},
        get filtered() {
            if (!this.search) return this.items;
            const q = this.search.toLowerCase();
            return this.items.filter(i =>
                i.name.toLowerCase().includes(q) || i.slug.toLowerCase().includes(q)
            );
        },
        select(item) {
            this.selectedId   = item ? item.id   : null;
            this.selectedType = item ? item.type : null;
            this.selectedName = item ? item.name : {{ json_encode($placeholder) }};
            this.search = '';
            this.open   = false;
        },
        openDropdown() {
            this.open = true;
            this.$nextTick(() => this.$refs.searchInput && this.$refs.searchInput.focus());
        },
        onEnter() {
            if (this.filtered.length === 1) {
                this.select(this.filtered[0]);
            } else if (this.filtered.length > 1) {
                const exact = this.filtered.find(i => i.name === this.search);
                if (exact) this.select(exact);
            }
        }
    }"
    class="relative"
    @click.outside="open = false; search = ''"
>
    @if($name === 'area_id')
    <input type="hidden" name="area_id"       :value="selectedType === 'area'       ? selectedId : ''">
    <input type="hidden" name="prefecture_id" :value="selectedType === 'prefecture' ? selectedId : ''">
    @elseif($name === 'job_type_id')
    <input type="hidden" name="job_type_id"   :value="selectedType === 'job_type'   ? selectedId : ''">
    <input type="hidden" name="genre_id"      :value="selectedType === 'genre'      ? selectedId : ''">
    @else
    <input type="hidden" name="{{ $name }}" :value="selectedId ?? ''">
    @endif

    {{-- トリガーボタン --}}
    <button type="button"
            @click="openDropdown()"
            class="w-36 border border-gray-200 rounded px-2 py-1 text-xs text-left flex items-center justify-between gap-1 bg-white hover:border-gray-300 focus:outline-none">
        <span class="truncate" x-text="selectedName"></span>
        <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- ドロップダウン --}}
    <div x-show="open"
         x-transition
         class="absolute z-20 mt-1 w-56 bg-white border border-gray-200 rounded-lg shadow-lg">

        {{-- 絞り込み入力 --}}
        <div class="p-2 border-b border-gray-100">
            <input type="text"
                   x-model="search"
                   x-ref="searchInput"
                   @keydown.enter.prevent="onEnter()"
                   @keydown.escape="open = false; search = ''"
                   placeholder="絞り込み..."
                   class="w-full px-2 py-1 text-xs border border-gray-200 rounded focus:outline-none focus:border-yellow-400">
        </div>

        {{-- 選択肢リスト --}}
        <div class="max-h-52 overflow-y-auto py-1">
            {{-- 不問オプション --}}
            <button type="button"
                    @mousedown.prevent="select(null)"
                    class="w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50 text-gray-400">
                — {{ $placeholder }} —
            </button>

            <template x-for="item in filtered" :key="item.type + ':' + item.id">
                <button type="button"
                        @mousedown.prevent="select(item)"
                        class="w-full text-left px-3 py-1.5 text-xs hover:bg-yellow-50 flex items-center justify-between">
                    <span x-text="item.name"></span>
                    <span class="text-gray-300 text-xs shrink-0 ml-2"
                          x-text="item.badge ?? item.slug"></span>
                </button>
            </template>

            <div x-show="filtered.length === 0" class="px-3 py-2 text-xs text-gray-400">
                該当なし
            </div>
        </div>
    </div>
</div>
