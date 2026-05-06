<!DOCTYPE html>
<html lang="ja" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '管理画面') | 夜ビジ Admin</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="/favicon.ico" sizes="any">
@vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-gray-100 text-gray-800 antialiased">

    {{-- ヘッダー --}}
    <header class="bg-gray-900 text-white shadow">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="{{ route('admin.dashboard') }}/" class="text-lg font-bold tracking-wide hover:opacity-80 transition">
                    夜ビジ <span class="text-yellow-400 text-sm font-normal">Admin</span>
                </a>
                <nav class="hidden md:flex items-center gap-5 text-sm">
                    <a href="{{ route('admin.dashboard') }}/"
                       class="{{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-gray-400 hover:text-white' }} transition">
                        ダッシュボード
                    </a>
                    <a href="{{ route('admin.keywords.index') }}/"
                       class="{{ request()->routeIs('admin.keywords.*') ? 'text-yellow-400' : 'text-gray-400 hover:text-white' }} transition">
                        キーワード
                    </a>
                    <a href="{{ route('admin.shops.index') }}/"
                       class="{{ request()->routeIs('admin.shops.*') ? 'text-yellow-400' : 'text-gray-400 hover:text-white' }} transition">
                        掲載審査
                    </a>
                    <a href="{{ route('admin.plan-applications.index') }}/"
                       class="{{ request()->routeIs('admin.plan-applications.*') ? 'text-yellow-400' : 'text-gray-400 hover:text-white' }} transition">
                        有料店舗
                    </a>
                    <a href="{{ route('admin.partners.index') }}/"
                       class="{{ request()->routeIs('admin.partners.*') ? 'text-yellow-400' : 'text-gray-400 hover:text-white' }} transition">
                        代理店
                    </a>
                    <a href="{{ route('admin.billing.index') }}/"
                       class="{{ request()->routeIs('admin.billing.*') ? 'text-yellow-400' : 'text-gray-400 hover:text-white' }} transition">
                        月次集計
                    </a>
                    <a href="{{ route('admin.notices.index') }}/"
                       class="{{ request()->routeIs('admin.notices.*') ? 'text-yellow-400' : 'text-gray-400 hover:text-white' }} transition">
                        お知らせ
                    </a>
                    <a href="{{ route('admin.xml-shops.index') }}/"
                       class="{{ request()->routeIs('admin.xml-shops.*') ? 'text-yellow-400' : 'text-gray-400 hover:text-white' }} transition">
                        XML店舗
                    </a>
                    <a href="{{ route('admin.master.index') }}/"
                       class="{{ request()->routeIs('admin.master.*') ? 'text-yellow-400' : 'text-gray-400 hover:text-white' }} transition">
                        職種管理
                    </a>
                    <a href="{{ route('admin.articles.index') }}/"
                       class="{{ request()->routeIs('admin.articles.*') ? 'text-yellow-400' : 'text-gray-400 hover:text-white' }} transition">
                        コラム
                    </a>
                    <a href="{{ route('admin.search-page-views.index') }}/"
                       class="{{ request()->routeIs('admin.search-page-views.*') ? 'text-yellow-400' : 'text-gray-400 hover:text-white' }} transition">
                        PV分析
                    </a>
                    <a href="{{ route('admin.deletion-requests.index') }}/"
                       class="{{ request()->routeIs('admin.deletion-requests.*') ? 'text-red-400' : 'text-gray-400 hover:text-white' }} transition">
                        削除依頼
                    </a>
                </nav>
            </div>
            <div class="flex items-center gap-3 text-sm">

                {{-- エリア追加 --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" type="button"
                            class="flex items-center gap-1 text-xs px-3 py-1.5 bg-gray-700 hover:bg-gray-600 rounded transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        エリア
                    </button>
                    <div x-show="open" x-transition @click.outside="open = false"
                         class="absolute right-0 mt-2 w-72 bg-white text-gray-800 rounded-lg shadow-xl z-50 p-4">
                        <p class="text-xs font-bold text-gray-600 mb-3">エリアを追加</p>
                        <form action="{{ route('admin.master.area.store') }}/" method="POST" class="space-y-2"
                              x-data="{ prefId: '' }">
                            @csrf
                            <select name="prefecture_id" required x-model="prefId"
                                    class="w-full border border-gray-200 rounded px-2 py-1.5 text-xs focus:outline-none focus:border-yellow-400">
                                <option value="">都道府県を選択</option>
                                @foreach($prefectures as $pref)
                                    <option value="{{ $pref->id }}">{{ $pref->name }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="name" placeholder="エリア名（例：歌舞伎町）" required
                                   class="w-full border border-gray-200 rounded px-2 py-1.5 text-xs focus:outline-none focus:border-yellow-400">
                            <input type="text" name="slug" placeholder="スラッグ（例：kabukicho）" required
                                   class="w-full border border-gray-200 rounded px-2 py-1.5 text-xs focus:outline-none focus:border-yellow-400"
                                   pattern="[a-z0-9\-]+">
                            <select name="parent_id"
                                    class="w-full border border-gray-200 rounded px-2 py-1.5 text-xs focus:outline-none focus:border-yellow-400">
                                <option value="">親エリアなし</option>
                                @foreach($allAreas as $a)
                                    <option value="{{ $a->id }}"
                                            x-show="prefId === '' || prefId == '{{ $a->prefecture_id }}'">
                                        {{ $a->prefecture->name }} / {{ $a->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="w-full py-1.5 bg-yellow-500 hover:bg-yellow-400 text-white text-xs rounded font-medium transition">
                                追加する
                            </button>
                        </form>
                    </div>
                </div>

                {{-- 業種追加 --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" type="button"
                            class="flex items-center gap-1 text-xs px-3 py-1.5 bg-gray-700 hover:bg-gray-600 rounded transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        業種
                    </button>
                    <div x-show="open" x-transition @click.outside="open = false"
                         class="absolute right-0 mt-2 w-64 bg-white text-gray-800 rounded-lg shadow-xl z-50 p-4">
                        <p class="text-xs font-bold text-gray-600 mb-3">業種を追加</p>
                        <form action="{{ route('admin.master.job_type.store') }}/" method="POST" class="space-y-2">
                            @csrf
                            <input type="text" name="name" placeholder="職種名（例：バーテン）" required
                                   class="w-full border border-gray-200 rounded px-2 py-1.5 text-xs focus:outline-none focus:border-yellow-400">
                            <input type="text" name="slug" placeholder="スラッグ（例：bartender）" required
                                   class="w-full border border-gray-200 rounded px-2 py-1.5 text-xs font-mono focus:outline-none focus:border-yellow-400"
                                   pattern="[a-z0-9\-]+">
                            <select name="target_gender" required
                                    class="w-full border border-gray-200 rounded px-2 py-1.5 text-xs focus:outline-none focus:border-yellow-400">
                                <option value="female">女性ナイトワーク</option>
                                <option value="male">男性ナイトワーク</option>
                                <option value="both">両方</option>
                            </select>
                            <select name="role_type" required
                                    class="w-full border border-gray-200 rounded px-2 py-1.5 text-xs focus:outline-none focus:border-yellow-400">
                                <option value="cast">キャスト画面</option>
                                <option value="staff">スタッフ画面</option>
                                <option value="both">両方</option>
                            </select>
                            <input type="text" name="group_slug" placeholder="グループslug（例：cast）"
                                   class="w-full border border-gray-200 rounded px-2 py-1.5 text-xs font-mono focus:outline-none focus:border-yellow-400"
                                   pattern="[a-z0-9\-]*">
                            <button type="submit"
                                    class="w-full py-1.5 bg-yellow-500 hover:bg-yellow-400 text-white text-xs rounded font-medium transition">
                                追加する
                            </button>
                        </form>
                    </div>
                </div>

                <span class="text-gray-500">|</span>
                <span class="text-gray-400">{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}/" method="POST">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-white transition">ログアウト</button>
                </form>
            </div>
        </div>
    </header>

    {{-- フラッシュメッセージ --}}
    @if(session('success'))
    <div class="bg-green-50 border-b border-green-200 text-green-700 text-sm px-4 py-2 text-center">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border-b border-red-200 text-red-700 text-sm px-4 py-2 text-center">
        {{ session('error') }}
    </div>
    @endif

    {{-- メイン --}}
    <main class="max-w-7xl mx-auto px-4 py-8">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
