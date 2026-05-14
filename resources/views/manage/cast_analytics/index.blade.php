@extends('layouts.app')
@section('title', '女性別統計')
@section('content')
<div class="bg-business-700 text-white py-4">
    <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
        <h1 class="font-bold text-lg">店舗管理</h1>
        <div class="flex items-center gap-4 text-sm">
            <span class="opacity-70">{{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}/" method="POST">
                @csrf
                <button type="submit" class="opacity-70 hover:opacity-100 transition">ログアウト</button>
            </form>
        </div>
    </div>
</div>

@include('manage._nav')

<div class="max-w-4xl mx-auto px-4 pb-12">
    <div class="mb-4 flex items-center justify-between flex-wrap gap-3">
        <h2 class="text-lg font-bold text-gray-800">女性別統計</h2>
        {{-- 期間切替 --}}
        <div class="flex rounded-lg overflow-hidden border border-gray-200 text-sm">
            @foreach([7 => '直近7日', 30 => '直近30日'] as $p => $label)
            <a href="{{ request()->fullUrlWithQuery(['period' => $p, 'sort' => $sort]) }}"
               @class([
                   'px-4 py-2 transition',
                   'bg-business-600 text-white font-medium' => $period === $p,
                   'bg-white text-gray-600 hover:bg-gray-50' => $period !== $p,
               ])>{{ $label }}</a>
            @endforeach
        </div>
    </div>

    @if(empty($rows) || $rows->isEmpty())
        <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-400 text-sm">
            在籍キャストが登録されていません
        </div>
    @else
    @php
        $cols = [
            'score'     => ['label' => 'スコア',      'help' => '電話×10 + お気に入り×3 + 口コミ×5 + PV×1'],
            'tel'       => ['label' => '電話クリック', 'help' => '期間内の電話ボタン押下数'],
            'views'     => ['label' => '詳細PV',       'help' => '期間内のキャスト詳細ページ閲覧数（重複除去）'],
            'favorites' => ['label' => 'お気に入り',   'help' => '累計お気に入り登録数'],
            'reviews'   => ['label' => '口コミ',       'help' => '承認済み口コミ数（累計）'],
        ];
    @endphp
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium w-8">#</th>
                        <th class="text-left px-4 py-3 font-medium">女性名</th>
                        @foreach($cols as $key => $col)
                        <th class="px-4 py-3 text-right font-medium">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => $key]) }}"
                               title="{{ $col['help'] }}"
                               @class([
                                   'inline-flex items-center gap-1 hover:text-gray-800 transition',
                                   'text-business-600 font-bold' => $sort === $key,
                               ])>
                                {{ $col['label'] }}
                                @if($sort === $key)
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 10l5-5 5 5H5z"/></svg>
                                @endif
                            </a>
                        </th>
                        @endforeach
                        <th class="px-4 py-3 text-left font-medium">状態</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($rows as $i => $row)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('manage.cast-profile.edit', $row->cast->id) }}/"
                               class="flex items-center gap-2.5 hover:opacity-80 transition">
                                @if($row->cast->img_file_name)
                                <img src="{{ $row->cast->img_url }}" alt=""
                                     class="w-8 h-8 rounded-full object-cover shrink-0">
                                @else
                                <span class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
                                </span>
                                @endif
                                <span class="font-medium text-gray-800">{{ $row->cast->name }}</span>
                            </a>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-bold text-gray-800">{{ number_format($row->score) }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($row->tel > 0)
                                <span class="font-semibold text-deli-600">{{ number_format($row->tel) }}</span>
                            @else
                                <span class="text-gray-300">0</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($row->views > 0)
                                <span class="font-semibold text-gray-700">{{ number_format($row->views) }}</span>
                            @else
                                <span class="text-gray-300">0</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($row->favorites > 0)
                                <span class="font-semibold text-pink-500">{{ number_format($row->favorites) }}</span>
                            @else
                                <span class="text-gray-300">0</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($row->reviews > 0)
                                <span class="font-semibold text-amber-600">{{ number_format($row->reviews) }}</span>
                            @else
                                <span class="text-gray-300">0</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($row->cast->status === 'active')
                                <span class="text-xs bg-green-50 text-green-700 border border-green-200 rounded-full px-2 py-0.5">公開中</span>
                            @else
                                <span class="text-xs bg-gray-50 text-gray-500 border border-gray-200 rounded-full px-2 py-0.5">非公開</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-400 px-4 py-3 border-t border-gray-100">
            電話クリック・詳細PVは{{ $period }}日間の集計。お気に入り・口コミは累計。スコア = 電話×10 + お気に入り×3 + 口コミ×5 + PV×1
        </p>
    </div>
    @endif
</div>
@endsection
