@extends('layouts.admin')
@section('title', 'お知らせ作成')
@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-700">お知らせ作成</h1>
    <a href="{{ route('admin.notices.index') }}/" class="text-sm text-gray-400 hover:text-gray-600">← 一覧に戻る</a>
</div>

@if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ route('admin.notices.store') }}/" method="POST"
      class="bg-white rounded-xl shadow-sm overflow-hidden">
    @csrf
    <table class="w-full text-sm">
        <tr class="border-b border-gray-100">
            <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-36 whitespace-nowrap">
                送信対象 <span class="text-red-400">*</span>
            </th>
            <td class="px-4 py-3">
                <div class="flex gap-6">
                    @foreach(['all' => '全店舗オーナー', 'active' => '掲載中のみ', 'inactive' => '非公開のみ'] as $val => $label)
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="target" value="{{ $val }}"
                                   {{ old('target', 'all') === $val ? 'checked' : '' }}>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </td>
        </tr>
        <tr class="border-b border-gray-100">
            <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">
                件名 <span class="text-red-400">*</span>
            </th>
            <td class="px-4 py-3">
                <input type="text" name="title" value="{{ old('title') }}" required
                       placeholder="例：システムメンテナンスのお知らせ"
                       class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-yellow-400 @error('title') border-red-400 @enderror">
                <p class="text-xs text-gray-400 mt-1">件名の前に「【ナイトワークリスト】」が自動で付きます</p>
                @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </td>
        </tr>
        <tr class="border-b border-gray-100">
            <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">
                本文 <span class="text-red-400">*</span>
            </th>
            <td class="px-4 py-3">
                <textarea name="body" rows="12" required
                          placeholder="お知らせの本文を入力してください。"
                          class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-yellow-400 @error('body') border-red-400 @enderror">{{ old('body') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">最大5,000文字。末尾に署名とお問い合わせ案内が自動で付きます。</p>
                @error('body')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </td>
        </tr>
    </table>
    <div class="px-4 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-400">下書き保存後、プレビューで確認してから送信できます</p>
        <button type="submit"
                class="bg-gray-800 hover:bg-gray-700 text-white text-sm font-bold px-6 py-2 rounded-lg transition">
            下書き保存
        </button>
    </div>
</form>

@endsection
