@extends('layouts.admin')

@section('title', 'キーワード正規化')

@push('head')
@php
    $areaOptionsJson = json_encode(
        $prefectures->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'slug' => $p->slug, 'type' => 'prefecture', 'badge' => '都道府県'])
            ->merge($areas->map(fn($a) => ['id' => $a->id, 'name' => $a->name, 'slug' => $a->slug, 'type' => 'area', 'badge' => 'エリア']))
            ->values(),
        JSON_UNESCAPED_UNICODE
    );
    $jobTypeOptionsJson = json_encode(
        $genres->map(fn($g) => ['id' => $g->id, 'name' => $g->name, 'slug' => $g->slug, 'type' => 'genre', 'badge' => '業種'])
            ->merge($jobTypes->map(fn($j) => ['id' => $j->id, 'name' => $j->name, 'slug' => $j->slug, 'type' => 'job_type', 'badge' => '職種']))
            ->values(),
        JSON_UNESCAPED_UNICODE
    );
@endphp
<script @nonce>
    window.areaOptions = {!! $areaOptionsJson !!};
    window.jobTypeOptions = {!! $jobTypeOptionsJson !!};
</script>
@endpush

@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-700">キーワード正規化</h1>
    <form action="{{ route('admin.keywords.generate_candidates') }}/" method="POST">
        @csrf
        <button type="submit"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-900 text-sm rounded font-medium"
                onclick="return confirm('確定済みの「エリアのみ」×「職種のみ」の組み合わせを未判定として登録します。よろしいですか？')">
            候補を自動生成
        </button>
    </form>
</div>

@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 mb-4 text-sm text-red-700">
    <p class="font-bold mb-1">入力エラーがあります</p>
    <ul class="list-disc list-inside space-y-0.5 text-xs">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 mb-4 text-sm text-green-700">
    {{ session('success') }}
</div>
@endif

{{-- タブ --}}
<div class="flex gap-1 mb-6 border-b border-gray-200">
    @foreach(['new' => '未判定', 'mapped' => '仮確定', 'confirmed' => '確定済み', 'excluded' => '除外済み'] as $s => $label)
    <a href="{{ route('admin.keywords.index', ['status' => $s]) }}/"
       class="{{ $status === $s
           ? 'border-b-2 border-yellow-500 text-yellow-600 font-bold'
           : 'text-gray-500 hover:text-gray-700' }}
          px-4 py-2 text-sm transition -mb-px">
        {{ $label }}
        <span class="ml-1 text-xs {{ $status === $s ? 'text-yellow-500' : 'text-gray-400' }}">
            {{ number_format($counts[$s]) }}
        </span>
    </a>
    @endforeach
</div>

@if($keywords->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center text-gray-400">
        <p>該当するキーワードはありません</p>
    </div>
@else

<div class="bg-white rounded-xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-8">#</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">検索ワード</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-20">性別</th>
                <th class="text-right px-4 py-3 text-xs font-bold text-gray-500 w-20">検索数</th>
                @if($status === 'new')
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">正規化先</th>
                    <th class="px-4 py-3 w-16"></th>
                @elseif($status === 'mapped')
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">正規化先 / URL</th>
                    <th class="px-4 py-3 w-40"></th>
                @elseif($status === 'confirmed')
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">正規化先 URL</th>
                    <th class="px-4 py-3 w-24"></th>
                @else
                    <th class="px-4 py-3 w-28"></th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($keywords as $kw)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3 text-gray-400 text-xs">{{ $kw->id }}</td>
                <td class="px-4 py-3 font-medium text-gray-800">{{ $kw->keyword }}</td>
                <td class="px-4 py-3">
                    @if($kw->gender === 'male')
                        <span class="text-xs px-2 py-0.5 bg-blue-50 text-blue-600 border border-blue-200 rounded-full">男性</span>
                    @elseif($kw->gender === 'female')
                        <span class="text-xs px-2 py-0.5 bg-pink-50 text-pink-600 border border-pink-200 rounded-full">女性</span>
                    @elseif($kw->gender === 'yoasobi')
                        <span class="text-xs px-2 py-0.5 bg-teal-50 text-teal-600 border border-teal-200 rounded-full">営業</span>
                    @else
                        <span class="text-xs text-gray-400">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right font-bold text-gray-700">{{ number_format($kw->search_count) }}</td>

                @if($status === 'new')
                <td class="px-4 py-3">
                    <form action="{{ route('admin.keywords.map', $kw->id) }}/" method="POST"
                          x-data="{ mode: 'lp', confirm: 0 }">
                        @csrf
                        <input type="hidden" name="directly_confirm" :value="confirm">
                        <div class="flex gap-2 mb-1.5 text-xs text-gray-500">
                            <label class="flex items-center gap-1 cursor-pointer">
                                <input type="radio" x-model="mode" value="lp"> LP誘導
                            </label>
                            <label class="flex items-center gap-1 cursor-pointer">
                                <input type="radio" x-model="mode" value="keyword"> フリーワード
                            </label>
                        </div>
                        <div class="flex gap-2 items-center flex-wrap">
                            <div x-show="mode === 'lp'" class="flex gap-2 items-center flex-wrap">
                                <x-keyword-select name="area_id" placeholder="エリア不問" :init-id="null" :init-name="null" init-type="area" js-var="areaOptions" />
                                <x-keyword-select name="job_type_id" placeholder="職種不問" :init-id="null" :init-name="null" init-type="job_type" js-var="jobTypeOptions" />
                                @if($filterTypes->isNotEmpty())
                                <select name="filter_slug"
                                        class="border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:border-yellow-400">
                                    <option value="">フィルターなし</option>
                                    @foreach($filterTypes as $ft)
                                        <option value="{{ $ft->slug }}">{{ $ft->name }}</option>
                                    @endforeach
                                </select>
                                @endif
                            </div>
                            <div x-show="mode === 'keyword'">
                                <input type="text" name="search_keyword" value="{{ $kw->keyword }}"
                                       placeholder="検索キーワード"
                                       class="border border-gray-300 rounded px-2 py-1 text-xs w-44 focus:outline-none focus:border-yellow-400">
                            </div>
                            <button type="submit" @click="confirm=0"
                                    class="px-3 py-1 bg-yellow-500 hover:bg-yellow-400 text-white text-xs rounded transition font-medium whitespace-nowrap">
                                仮確定
                            </button>
                            <button type="submit" @click="confirm=1"
                                    class="px-3 py-1 bg-blue-500 hover:bg-blue-400 text-white text-xs rounded transition font-medium whitespace-nowrap">
                                確定
                            </button>
                        </div>
                    </form>
                </td>
                <td class="px-2 py-3">
                    <form action="{{ route('admin.keywords.exclude', $kw->id) }}/" method="POST">
                        @csrf
                        <button type="submit"
                                class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-500 text-xs rounded transition"
                                onclick="return confirm('「{{ $kw->keyword }}」を除外しますか？')">
                            除外
                        </button>
                    </form>
                </td>

                @elseif($status === 'mapped')
                @php
                    $norm = $normalizationMap->get($kw->keyword . '_' . ($kw->gender ?? ''));
                    $isKwMode = $norm && $norm->search_keyword && !$norm->area_id && !$norm->prefecture_id && !$norm->job_type_id && !$norm->genre_id && !$norm->filter_slug;
                @endphp
                <td class="px-4 py-3">
                    <form id="confirm-form-{{ $kw->id }}" action="{{ route('admin.keywords.confirm', $kw->id) }}/" method="POST" class="hidden">@csrf</form>
                    <form action="{{ route('admin.keywords.map', $kw->id) }}/" method="POST"
                          x-data="{ mode: '{{ $isKwMode ? 'keyword' : 'lp' }}' }">
                        @csrf
                        <div class="flex gap-2 mb-1 text-xs text-gray-500 items-center">
                            <label class="flex items-center gap-1 cursor-pointer">
                                <input type="radio" x-model="mode" value="lp"> LP誘導
                            </label>
                            <label class="flex items-center gap-1 cursor-pointer">
                                <input type="radio" x-model="mode" value="keyword"> フリーワード
                            </label>
                        </div>
                        <div class="flex gap-2 items-center flex-wrap">
                            <div x-show="mode === 'lp'" class="flex gap-2 items-center flex-wrap">
                                <x-keyword-select name="area_id" placeholder="エリア不問"
                                    :init-id="$norm?->prefecture_id ?? $norm?->area_id"
                                    :init-name="$norm?->prefecture?->name ?? $norm?->area?->name"
                                    :init-type="$norm?->prefecture_id ? 'prefecture' : 'area'"
                                    js-var="areaOptions" />
                                <x-keyword-select name="job_type_id" placeholder="職種不問"
                                    :init-id="$norm?->genre_id ?? $norm?->job_type_id"
                                    :init-name="$norm?->genre?->name ?? $norm?->jobType?->name"
                                    :init-type="$norm?->genre_id ? 'genre' : 'job_type'"
                                    js-var="jobTypeOptions" />
                                @if($filterTypes->isNotEmpty())
                                <select name="filter_slug"
                                        class="border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:border-yellow-400">
                                    <option value="">フィルターなし</option>
                                    @foreach($filterTypes as $ft)
                                        <option value="{{ $ft->slug }}" {{ $norm?->filter_slug === $ft->slug ? 'selected' : '' }}>
                                            {{ $ft->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @endif
                                @if($norm && !$isKwMode)
                                    @php
                                        $normAreaSlug = $norm->prefecture?->slug ?? $norm->area?->slug ?? 'all';
                                        $normJobSlug  = $norm->genre?->slug ?? $norm->jobType?->slug ?? 'all';
                                        $normFilterPart = $norm->filter_slug ? '/' . $norm->filter_slug : '';
                                        $normUrl = '/' . $kw->gender . '/' . $normAreaSlug . '/' . $normJobSlug . $normFilterPart . '/';
                                    @endphp
                                    <span class="text-xs text-gray-400 whitespace-nowrap">{{ $normUrl }}</span>
                                @endif
                            </div>
                            <div x-show="mode === 'keyword'" class="flex items-center gap-2">
                                <input type="text" name="search_keyword"
                                       value="{{ $norm?->search_keyword ?? $kw->keyword }}"
                                       placeholder="検索キーワード"
                                       class="border border-gray-300 rounded px-2 py-1 text-xs w-44 focus:outline-none focus:border-yellow-400">
                                @if($isKwMode && $norm?->search_keyword)
                                    <span class="text-xs text-gray-400 whitespace-nowrap">→ 検索: {{ $norm->search_keyword }}</span>
                                @endif
                            </div>
                            <button type="submit" form="confirm-form-{{ $kw->id }}"
                                    class="ml-auto px-3 py-1 bg-blue-500 hover:bg-blue-400 text-white text-xs rounded font-medium whitespace-nowrap">
                                確定する
                            </button>
                        </div>
                    </form>
                </td>
                <td class="px-2 py-3">
                    <form action="{{ route('admin.keywords.reset', $kw->id) }}/" method="POST">
                        @csrf
                        <button type="submit"
                                class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-500 text-xs rounded transition whitespace-nowrap"
                                onclick="return confirm('「{{ $kw->keyword }}」の正規化を解除しますか？')">
                            解除
                        </button>
                    </form>
                </td>

                @elseif($status === 'confirmed')
                @php
                    $norm = $normalizationMap->get($kw->keyword . '_' . ($kw->gender ?? ''));
                    $confirmedUrl = '';
                    if ($norm) {
                        $cAreaSlug   = $norm->prefecture?->slug ?? $norm->area?->slug ?? 'all';
                        $cJobSlug    = $norm->genre?->slug ?? $norm->jobType?->slug ?? 'all';
                        $cFilterPart = $norm->filter_slug ? '/' . $norm->filter_slug : '';
                        $confirmedUrl = $norm->search_keyword
                            ? '/search/?gender=' . $kw->gender . '&keyword=' . urlencode($norm->search_keyword)
                            : '/' . $kw->gender . '/' . $cAreaSlug . '/' . $cJobSlug . $cFilterPart . '/';
                    }
                @endphp
                <td class="px-4 py-3">
                    @if($confirmedUrl)
                        <span class="text-xs text-gray-500 font-mono">{{ $confirmedUrl }}</span>
                    @else
                        <span class="text-xs text-gray-300">—</span>
                    @endif
                </td>
                <td class="px-2 py-3">
                    <form action="{{ route('admin.keywords.reset', $kw->id) }}/" method="POST">
                        @csrf
                        <button type="submit"
                                class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-500 text-xs rounded transition"
                                onclick="return confirm('「{{ $kw->keyword }}」の確定を解除して未判定に戻しますか？')">
                            解除
                        </button>
                    </form>
                </td>

                @else
                <td class="px-4 py-3" colspan="2">
                    <form action="{{ route('admin.keywords.reset', $kw->id) }}/" method="POST">
                        @csrf
                        <button type="submit"
                                class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs rounded transition">
                            未判定に戻す
                        </button>
                    </form>
                </td>
                @endif

            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- ページネーション --}}
@if($keywords->hasPages())
<div class="mt-6">
    {{ $keywords->appends(request()->query())->links() }}
</div>
@endif

@endif

@endsection
