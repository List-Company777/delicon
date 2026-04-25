@extends('layouts.admin')
@section('title', '職種管理')

@section('content')

<div class="flex items-center gap-3 mb-6">
    <h1 class="text-xl font-bold text-gray-700">職種管理</h1>
    <span class="text-sm text-gray-400">{{ $jobTypes->count() }}件</span>
</div>

{{-- 外部フォーム群（form="..." で各行の入力と紐付け） --}}
@foreach($jobTypes as $jt)
<form id="form-jt-{{ $jt->id }}"
      action="{{ route('admin.master.job_type.update', $jt->id) }}/"
      method="POST">
    @csrf @method('PATCH')
</form>
@endforeach

{{-- 職種テーブル --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-8">
    <div class="px-5 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-bold text-gray-600">職種一覧</h2>
        <p class="text-xs text-gray-400">group_slug：LPの絞り込みで複数職種をまとめる親スラッグ（例：cast）</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-500 w-10">ID</th>
                    <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-500 w-36">職種名</th>
                    <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-500 w-32">スラッグ</th>
                    <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-500 w-36">対象性別</th>
                    <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-500 w-28">管理画面</th>
                    <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-500 w-36">グループslug</th>
                    <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-500 w-36">キーワードフィルター</th>
                    <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-500 w-20">順序</th>
                    <th class="px-4 py-2.5 w-36"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($jobTypes as $jt)
                @php
                    $genderLabel = match($jt->target_gender) {
                        'female' => ['女性', 'text-pink-600'],
                        'male'   => ['男性', 'text-blue-600'],
                        default  => ['両方', 'text-gray-500'],
                    };
                @endphp
                <tr x-data="{ editing: false }" class="hover:bg-gray-50 transition">
                    <td class="px-4 py-2.5 text-gray-400 text-xs">{{ $jt->id }}</td>

                    {{-- 職種名 --}}
                    <td class="px-4 py-2.5">
                        <span x-show="!editing" class="text-gray-800 font-medium">{{ $jt->name }}</span>
                        <input x-show="editing" x-cloak
                               type="text" name="name" value="{{ $jt->name }}" required maxlength="50"
                               form="form-jt-{{ $jt->id }}"
                               class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:border-yellow-400">
                    </td>

                    {{-- スラッグ（読み取り専用） --}}
                    <td class="px-4 py-2.5 text-gray-400 text-xs font-mono">{{ $jt->slug }}</td>

                    {{-- 対象性別 --}}
                    <td class="px-4 py-2.5">
                        <span x-show="!editing" class="text-xs {{ $genderLabel[1] }}">{{ $genderLabel[0] }}</span>
                        <select x-show="editing" x-cloak
                                name="target_gender"
                                form="form-jt-{{ $jt->id }}"
                                class="border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:border-yellow-400">
                            <option value="female" {{ $jt->target_gender === 'female' ? 'selected' : '' }}>女性</option>
                            <option value="male"   {{ $jt->target_gender === 'male'   ? 'selected' : '' }}>男性</option>
                            <option value="both"   {{ $jt->target_gender === 'both'   ? 'selected' : '' }}>両方</option>
                        </select>
                    </td>

                    {{-- 管理画面（role_type） --}}
                    <td class="px-4 py-2.5">
                        @php
                            $roleLabel = match($jt->role_type) {
                                'cast'  => ['キャスト', 'text-pink-600'],
                                'staff' => ['スタッフ', 'text-blue-600'],
                                default => ['両方', 'text-gray-500'],
                            };
                        @endphp
                        <span x-show="!editing" class="text-xs {{ $roleLabel[1] }}">{{ $roleLabel[0] }}</span>
                        <select x-show="editing" x-cloak
                                name="role_type"
                                form="form-jt-{{ $jt->id }}"
                                class="border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:border-yellow-400">
                            <option value="cast"  {{ $jt->role_type === 'cast'  ? 'selected' : '' }}>キャスト</option>
                            <option value="staff" {{ $jt->role_type === 'staff' ? 'selected' : '' }}>スタッフ</option>
                            <option value="both"  {{ $jt->role_type === 'both'  ? 'selected' : '' }}>両方</option>
                        </select>
                    </td>

                    {{-- グループslug --}}
                    <td class="px-4 py-2.5">
                        <span x-show="!editing" class="text-xs text-gray-400 font-mono">{{ $jt->group_slug ?: '—' }}</span>
                        <input x-show="editing" x-cloak
                               type="text" name="group_slug" value="{{ $jt->group_slug }}"
                               placeholder="例: cast"
                               form="form-jt-{{ $jt->id }}"
                               pattern="[a-z0-9\-]*"
                               class="w-full border border-gray-300 rounded px-2 py-1 text-xs font-mono focus:outline-none focus:border-yellow-400">
                    </td>

                    {{-- キーワードフィルター --}}
                    <td class="px-4 py-2.5">
                        <span x-show="!editing" class="text-xs text-gray-400">{{ $jt->keyword_filter ?: '—' }}</span>
                        <input x-show="editing" x-cloak
                               type="text" name="keyword_filter" value="{{ $jt->keyword_filter }}"
                               placeholder="例: 未経験歓迎"
                               form="form-jt-{{ $jt->id }}"
                               class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:border-yellow-400">
                    </td>

                    {{-- 順序 --}}
                    <td class="px-4 py-2.5">
                        <span x-show="!editing" class="text-xs text-gray-500">{{ $jt->sort_order }}</span>
                        <input x-show="editing" x-cloak
                               type="number" name="sort_order" value="{{ $jt->sort_order }}"
                               min="0" max="255"
                               form="form-jt-{{ $jt->id }}"
                               class="w-16 border border-gray-300 rounded px-2 py-1 text-xs text-right focus:outline-none focus:border-yellow-400">
                    </td>

                    {{-- 操作 --}}
                    <td class="px-4 py-2.5">
                        <div x-show="!editing">
                            <button type="button" @click="editing = true"
                                    class="text-xs text-blue-600 hover:underline">編集</button>
                        </div>
                        <div x-show="editing" x-cloak class="flex items-center gap-2">
                            <button type="submit" form="form-jt-{{ $jt->id }}"
                                    class="text-xs px-2.5 py-1 bg-yellow-500 hover:bg-yellow-400 text-white rounded font-medium transition">
                                保存
                            </button>
                            <button type="button" @click="editing = false"
                                    class="text-xs text-gray-400 hover:text-gray-600">キャンセル</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- 新規追加フォーム --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-3 bg-gray-50 border-b border-gray-100">
        <h2 class="text-sm font-bold text-gray-600">職種を追加</h2>
    </div>
    <div class="p-5">
        <form action="{{ route('admin.master.job_type.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">職種名 <span class="text-red-400">*</span></label>
                    <input type="text" name="name" required maxlength="50"
                           placeholder="例：バーテン"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-yellow-400">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">スラッグ <span class="text-red-400">*</span></label>
                    <input type="text" name="slug" required maxlength="50"
                           placeholder="例：bartender" pattern="[a-z0-9\-]+"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm font-mono focus:outline-none focus:border-yellow-400">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">対象性別 <span class="text-red-400">*</span></label>
                    <select name="target_gender" required
                            class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-yellow-400">
                        <option value="female">女性</option>
                        <option value="male">男性</option>
                        <option value="both">両方</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">グループslug</label>
                    <input type="text" name="group_slug" maxlength="100"
                           placeholder="例：cast" pattern="[a-z0-9\-]*"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm font-mono focus:outline-none focus:border-yellow-400">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">キーワードフィルター</label>
                    <input type="text" name="keyword_filter" maxlength="100"
                           placeholder="例：未経験歓迎"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-yellow-400">
                </div>
                <div>
                    <button type="submit"
                            class="w-full py-1.5 bg-yellow-500 hover:bg-yellow-400 text-white text-sm rounded font-medium transition">
                        追加する
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- 住所→エリア マッピング --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden mt-8">
    <div class="px-5 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-bold text-gray-600">住所エリアマッピング</h2>
        <p class="text-xs text-gray-400">XMLインポート時に自動マッチできなかった住所。エリアを設定すると次回以降自動適用されます。</p>
    </div>

    @if($addressMappings->isEmpty())
        <p class="text-sm text-gray-400 px-5 py-6">未解決の住所はありません</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-500">住所キーワード</th>
                        <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-500">現在のエリア</th>
                        <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-500 w-72">エリアを設定</th>
                        <th class="px-4 py-2.5 w-16"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($addressMappings as $mapping)
                    <tr class="{{ $mapping->area_id ? '' : 'bg-amber-50' }}">
                        <td class="px-4 py-3 font-mono text-xs text-gray-700">
                            {{ $mapping->keyword }}
                            @if(!$mapping->area_id)
                                <span class="ml-1 text-xs text-amber-600 font-bold">未設定</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            {{ $mapping->area ? $mapping->area->prefecture->name . ' / ' . $mapping->area->name : '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <form action="{{ route('admin.master.address_mapping.update', $mapping->id) }}/"
                                  method="POST" class="flex items-center gap-2">
                                @csrf @method('PATCH')
                                <select name="area_id"
                                        class="flex-1 border border-gray-200 rounded px-2 py-1 text-xs focus:outline-none focus:border-yellow-400">
                                    <option value="">エリアなし</option>
                                    @foreach($areas as $a)
                                        <option value="{{ $a->id }}" {{ $mapping->area_id == $a->id ? 'selected' : '' }}>
                                            {{ $a->prefecture->name }} / {{ $a->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit"
                                        class="px-3 py-1 bg-yellow-500 hover:bg-yellow-400 text-white text-xs rounded transition whitespace-nowrap">
                                    保存
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <form action="{{ route('admin.master.address_mapping.delete', $mapping->id) }}/"
                                  method="POST"
                                  onsubmit="return confirm('削除しますか？')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition">削除</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
