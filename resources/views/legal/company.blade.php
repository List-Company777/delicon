@extends('layouts.app')

@section('title', '運営会社')
@section('canonical', route('company') . '/')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-2xl font-bold text-gray-800 mb-8">運営会社</h1>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-6 py-4 w-36 whitespace-nowrap">会社名</th>
                <td class="px-6 py-4 text-gray-700">株式会社リスト</td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-6 py-4 whitespace-nowrap">代表者</th>
                <td class="px-6 py-4 text-gray-700">高司 浩</td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-6 py-4 whitespace-nowrap">所在地</th>
                <td class="px-6 py-4 text-gray-700">〒112-0005 東京都文京区水道2-4-3 オフィス大岩201</td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-6 py-4 whitespace-nowrap">電話番号</th>
                <td class="px-6 py-4 text-gray-700">
                    <a href="tel:0352066966" class="text-business-700 underline underline-offset-2 hover:no-underline">03-5206-6966</a>
                    <span class="text-gray-400 text-xs ml-2">（平日 10:00〜19:00）</span>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-6 py-4 whitespace-nowrap">メール</th>
                <td class="px-6 py-4 text-gray-700">
                    <a href="mailto:info@list-company.net" class="text-business-700 underline underline-offset-2 hover:no-underline">info@list-company.net</a>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-6 py-4 whitespace-nowrap">事業内容</th>
                <td class="px-6 py-4 text-gray-700">求人情報・営業情報の提供サービスの運営</td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-6 py-4 whitespace-nowrap">登録番号</th>
                <td class="px-6 py-4 text-gray-700 space-y-1">
                    <p>募集情報等提供事業：51ー募ー000171</p>
                    <p>有料職業紹介事業：13ーユー316021</p>
                </td>
            </tr>
            <tr>
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-6 py-4 whitespace-nowrap">公式サイト</th>
                <td class="px-6 py-4">
                    <a href="https://list-company.net/" target="_blank" rel="noopener noreferrer"
                       class="text-business-700 underline underline-offset-2 hover:no-underline">https://list-company.net/</a>
                </td>
            </tr>
        </table>
    </div>

    <div class="mt-8 text-center">
        <a href="{{ route('top') }}/" class="text-sm text-gray-400 hover:text-gray-600">← トップに戻る</a>
    </div>
</div>
@endsection
