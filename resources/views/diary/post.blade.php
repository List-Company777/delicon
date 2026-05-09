@extends('layouts.app')
@section('title', $cast->name . ' - 写メ日記投稿')
@section('robots', 'noindex')

@section('content')
<div class="min-h-screen flex items-start justify-center px-4 py-8">
    <div class="w-full max-w-lg">

        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-full bg-surface-400 border border-surface-300 overflow-hidden mx-auto mb-3">
                <img src="{{ $cast->img_url }}" alt="{{ $cast->name }}"
                     class="img-onerror-cast w-full h-full object-cover">
            </div>
            <p class="text-[#F0ECE4] font-bold text-lg">{{ $cast->name }}</p>
            <p class="text-xs text-[#6A6A7E] mt-0.5">写メ日記を投稿</p>
            <a href="{{ route('cast.show', $cast) }}/"
               class="inline-block mt-2 text-xs text-deli-400 hover:text-deli-300 underline underline-offset-2 transition">
                自分のページを確認する →
            </a>
        </div>

        @if(session('success'))
        <div class="mb-5 bg-emerald-900/40 border border-emerald-600/40 text-emerald-400 text-sm px-4 py-3 rounded-xl text-center font-medium">
            {{ session('success') }}
        </div>
        @endif

        @if($postedToday)
        <div class="mb-5 bg-amber-900/40 border border-amber-600/40 text-amber-300 text-sm px-4 py-3 rounded-xl text-center font-medium">
            本日はすでに投稿済みです。写メ日記は1日1件までです。
        </div>
        @endif

        @error('limit')
        <div class="mb-5 bg-amber-900/40 border border-amber-600/40 text-amber-300 text-sm px-4 py-3 rounded-xl text-center font-medium">
            {{ $message }}
        </div>
        @enderror

        <div class="bg-amber-900/30 border border-amber-700/50 rounded-xl px-4 py-3 mb-4 text-xs text-amber-300 leading-relaxed"><span class="font-bold">⚠ 投稿時の注意</span>：写メ日記は人柄を知ってもらうための機能です。アダルトな内容の投稿はお控えください。掲載基準に反する投稿は予告なく削除する場合があります。</div>
        <form method="POST" action="/diary/post/{{ $dt->token }}/" enctype="multipart/form-data"
              class="bg-surface-500 border border-surface-300 rounded-2xl p-6 space-y-5 {{ $postedToday ? 'opacity-50 pointer-events-none' : '' }}">
            @csrf

            {{-- 写真（最大5枚） --}}
            <div>
                <label class="text-sm font-medium text-[#E8E4DC] block mb-2">写真（最大5枚）</label>

                {{-- プレビューグリッド --}}
                <div id="img-grid" class="grid grid-cols-3 gap-2 mb-2 hidden"></div>

                <label id="img-add-btn"
                       class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-surface-300 rounded-xl cursor-pointer hover:border-deli-500 transition bg-surface-600">
                    <svg class="w-8 h-8 text-[#6A6A7E] mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-sm text-[#6A6A7E]">タップして写真を追加</span>
                    <input type="file" name="images[]" accept="image/*" multiple class="hidden" id="img-input">
                </label>
                <p id="img-count-note" class="text-xs text-[#4A4A5E] mt-1 hidden"></p>
                @error('images.*')<p class="text-xs text-deli-400 mt-1">{{ $message }}</p>@enderror
                @error('images')<p class="text-xs text-deli-400 mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- 本文 --}}
            <div>
                <label class="text-sm font-medium text-[#E8E4DC] block mb-2">本文</label>
                <textarea name="body" rows="6" maxlength="2000"
                       placeholder="今日のことを書いてみよう..."
                       class="w-full bg-surface-600 border border-surface-300 rounded-xl px-4 py-3 text-base text-[#E8E4DC] placeholder-[#4A4A5E] focus:outline-none focus:border-deli-500 resize-none">{{ old('body') }}</textarea>
                @error('body')<p class="text-xs text-deli-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="sticky bottom-4">
            <button type="submit" {{ $postedToday ? 'disabled' : '' }}
                    class="w-full bg-deli-500 hover:bg-deli-400 active:scale-95 text-white font-bold py-4 rounded-xl transition text-base disabled:opacity-40 disabled:cursor-not-allowed shadow-lg">
                投稿する
            </button>
            </div>
        </form>

        <p class="text-center text-xs text-[#4A4A5E] mt-4">
            このURLは {{ $dt->expires_at->format('Y年m月d日') }} まで有効です
        </p>

        {{-- シフト申請 --}}
        <div id="shift" class="mt-8">
            <h2 class="text-sm font-bold text-[#E8E4DC] mb-1 flex items-center gap-2">
                <span class="w-1 h-4 bg-emerald-500 rounded-full"></span>今週のシフト申請
            </h2>
            <p class="text-xs text-[#6A6A7E] mb-4">出勤したい日と時間を申請できます。店舗が承認すると自動でシフトに反映されます。</p>

            @if(session('shift_success'))
            <div class="mb-4 bg-emerald-900/40 border border-emerald-600/40 text-emerald-400 text-sm px-4 py-3 rounded-xl text-center font-medium">
                {{ session('shift_success') }}
            </div>
            @endif

            {{-- 承認・却下の通知バナー --}}
            @php
                $approvedRecent = $shiftRequests->filter(fn($r) => $r->isApproved() && $r->approved_at && $r->approved_at->gte(now()->subDays(3)));
                $rejectedRecent = $shiftRequests->filter(fn($r) => $r->isRejected() && $r->updated_at && $r->updated_at->gte(now()->subDays(3)));
            @endphp
            @if($approvedRecent->isNotEmpty())
            <div class="mb-3 bg-emerald-900/40 border border-emerald-600/40 text-emerald-300 text-xs px-4 py-3 rounded-xl">
                ✅ {{ $approvedRecent->count() }}件のシフトが承認されました（シフトに反映済みです）
            </div>
            @endif
            @if($rejectedRecent->isNotEmpty())
            <div class="mb-3 bg-red-900/30 border border-red-600/30 text-red-300 text-xs px-4 py-3 rounded-xl">
                ❌ {{ $rejectedRecent->count() }}件のシフト申請が却下されました。店舗にご確認ください。
            </div>
            @endif

            {{-- 2週間グリッド --}}
            @php
                $dowLabels = ['日','月','火','水','木','金','土'];
            @endphp
            <div class="grid grid-cols-7 gap-1 text-center mb-5">
                @for($i = 0; $i < 14; $i++)
                @php
                    $day  = $today->copy()->addDays($i);
                    $key  = $day->format('Y-m-d');
                    $req  = $shiftRequests->get($key);
                    $dow  = (int)$day->format('w');
                    $isToday = $i === 0;
                    $statusClass = $req ? match($req->status) {
                        'approved' => 'bg-emerald-900/60 border-emerald-600/60',
                        'rejected' => 'bg-red-900/30 border-red-600/30',
                        default    => 'bg-amber-900/40 border-amber-600/50',
                    } : 'bg-surface-600 border-surface-400';
                    $textClass = match($dow) {
                        0 => 'text-red-400', 6 => 'text-blue-400', default => 'text-[#6A6A7E]'
                    };
                @endphp
                <button type="button"
                        data-date="{{ $key }}"
                        data-start="{{ $req ? substr($req->start_time ?? '', 0, 5) : '' }}"
                        data-end="{{ $req ? substr($req->end_time ?? '', 0, 5) : '' }}"
                        data-req-id="{{ $req?->id ?? '' }}"
                        data-status="{{ $req?->status ?? '' }}"
                        onclick="openShiftModal(this)"
                        class="rounded-lg py-2 px-0.5 border transition {{ $statusClass }} active:scale-95">
                    <p class="text-[10px] font-bold mb-0.5 {{ $isToday ? 'text-deli-400' : $textClass }}">{{ $dowLabels[$dow] }}</p>
                    <p class="text-xs font-bold {{ $isToday ? 'text-deli-400' : 'text-[#C8C4BC]' }}">{{ $day->format('j') }}</p>
                    @if($req)
                    <p class="text-[9px] mt-0.5 leading-tight {{ $req->isApproved() ? 'text-emerald-400' : ($req->isRejected() ? 'text-red-400' : 'text-amber-400') }}">
                        {{ $req->isApproved() ? '承認' : ($req->isRejected() ? '却下' : '申請中') }}
                    </p>
                    @else
                    <p class="text-[10px] text-[#3A3A4E] mt-0.5">—</p>
                    @endif
                </button>
                @endfor
            </div>

            <p class="text-[10px] text-[#4A4A5E] text-center mb-4 flex items-center justify-center gap-3">
                <span><span class="inline-block w-2 h-2 rounded-sm bg-amber-900/60 border border-amber-600/50 mr-1"></span>申請中</span>
                <span><span class="inline-block w-2 h-2 rounded-sm bg-emerald-900/60 border border-emerald-600/60 mr-1"></span>承認済</span>
                <span><span class="inline-block w-2 h-2 rounded-sm bg-red-900/30 border border-red-600/30 mr-1"></span>却下</span>
            </p>
        </div>
    </div>
</div>

{{-- シフト申請モーダル --}}
<div id="shift-modal" class="fixed inset-0 bg-black/70 z-50 flex items-end justify-center hidden" onclick="closeShiftModal(event)">
    <div class="bg-surface-500 border border-surface-300 rounded-t-2xl w-full max-w-lg p-6 pb-8" id="shift-modal-inner">
        <h3 class="text-base font-bold text-[#E8E4DC] mb-1" id="modal-title">シフト申請</h3>
        <p class="text-xs text-[#6A6A7E] mb-4" id="modal-date-label"></p>

        <div id="modal-body-apply">
            <form id="shift-form" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="_method" value="POST" id="shift-method">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-[#8A8A9E] block mb-1">開始時間</label>
                        <input type="time" name="start_time" id="modal-start"
                               class="w-full bg-surface-600 border border-surface-300 rounded-xl px-3 py-2 text-sm text-[#E8E4DC] focus:outline-none focus:border-deli-500">
                    </div>
                    <div>
                        <label class="text-xs text-[#8A8A9E] block mb-1">終了時間</label>
                        <input type="time" name="end_time" id="modal-end"
                               class="w-full bg-surface-600 border border-surface-300 rounded-xl px-3 py-2 text-sm text-[#E8E4DC] focus:outline-none focus:border-deli-500">
                    </div>
                </div>
                <div>
                    <label class="text-xs text-[#8A8A9E] block mb-1">メモ（任意）</label>
                    <input type="text" name="note" id="modal-note" maxlength="50" placeholder="例: 早退希望など"
                           class="w-full bg-surface-600 border border-surface-300 rounded-xl px-3 py-2 text-base text-[#E8E4DC] placeholder-[#4A4A5E] focus:outline-none focus:border-deli-500">
                </div>
                <input type="hidden" name="work_date" id="modal-work-date">
                <button type="submit"
                        class="w-full bg-deli-500 hover:bg-deli-400 text-white font-bold py-3 rounded-xl transition mt-2">
                    申請する
                </button>
            </form>
        </div>

        <div id="modal-body-cancel" class="hidden">
            <p class="text-sm text-[#B0AEAD] mb-4" id="modal-status-msg"></p>
            <form id="cancel-form" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="w-full bg-red-900/50 hover:bg-red-900/70 text-red-300 border border-red-700/50 font-bold py-3 rounded-xl transition">
                    申請を取り消す
                </button>
            </form>
            <button onclick="closeShiftModalDirect()" class="w-full mt-2 text-sm text-[#6A6A7E] py-2">閉じる</button>
        </div>

        <div id="modal-body-done" class="hidden">
            <p class="text-sm text-[#B0AEAD] mb-4" id="modal-done-msg"></p>
            <button onclick="closeShiftModalDirect()" class="w-full mt-2 text-sm text-[#6A6A7E] py-2">閉じる</button>
        </div>
    </div>
</div>

@push('scripts')
<script @nonce>
var _token = '{{ csrf_token() }}';
var _baseToken = '{{ $dt->token }}';

function openShiftModal(btn) {
    var date    = btn.dataset.date;
    var status  = btn.dataset.status;
    var reqId   = btn.dataset.reqId;
    var start   = btn.dataset.start;
    var end     = btn.dataset.end;

    var dowMap  = ['日','月','火','水','木','金','土'];
    var d       = new Date(date + 'T00:00:00');
    var label   = (d.getMonth()+1) + '月' + d.getDate() + '日（' + dowMap[d.getDay()] + '）';
    document.getElementById('modal-date-label').textContent = label;
    document.getElementById('modal-title').textContent = 'シフト申請 - ' + label;

    var applyEl  = document.getElementById('modal-body-apply');
    var cancelEl = document.getElementById('modal-body-cancel');
    var doneEl   = document.getElementById('modal-body-done');

    applyEl.classList.add('hidden');
    cancelEl.classList.add('hidden');
    doneEl.classList.add('hidden');

    if (status === 'approved') {
        document.getElementById('modal-done-msg').textContent = '✅ このシフトは承認済みです。シフトに反映されています。';
        doneEl.classList.remove('hidden');
    } else if (status === 'rejected') {
        document.getElementById('modal-done-msg').textContent = '❌ このシフト申請は却下されました。申請し直す場合は「申請を取り消す」後に再度申請してください。';
        doneEl.classList.remove('hidden');
    } else if (status === 'pending') {
        document.getElementById('modal-status-msg').textContent = '⏳ 申請中（' + (start || '--') + '〜' + (end || '--') + '）店舗の承認をお待ちください。';
        document.getElementById('cancel-form').action = '/diary/post/' + _baseToken + '/shift-request/' + reqId + '/';
        cancelEl.classList.remove('hidden');
    } else {
        document.getElementById('shift-form').action = '/diary/post/' + _baseToken + '/shift-request/';
        document.getElementById('modal-work-date').value = date;
        document.getElementById('modal-start').value = '';
        document.getElementById('modal-end').value   = '';
        document.getElementById('modal-note').value  = '';
        applyEl.classList.remove('hidden');
    }

    document.getElementById('shift-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeShiftModal(e) {
    if (e.target === document.getElementById('shift-modal')) {
        closeShiftModalDirect();
    }
}
function closeShiftModalDirect() {
    document.getElementById('shift-modal').classList.add('hidden');
    document.body.style.overflow = '';
}
</script>
<script @nonce>
(function() {
    var MAX = 5;
    var files = [];
    var input = document.getElementById('img-input');
    var grid  = document.getElementById('img-grid');
    var addBtn = document.getElementById('img-add-btn');
    var note  = document.getElementById('img-count-note');

    input.addEventListener('change', function() {
        var selected = Array.from(this.files);
        var remaining = MAX - files.length;
        selected.slice(0, remaining).forEach(function(file) {
            files.push(file);
        });
        this.value = '';
        render();
    });

    function render() {
        grid.innerHTML = '';
        files.forEach(function(file, i) {
            var r = new FileReader();
            r.onload = function(e) {
                var cell = document.createElement('div');
                cell.className = 'relative aspect-square rounded-lg overflow-hidden bg-surface-600';
                cell.innerHTML =
                    '<img src="' + e.target.result + '" class="w-full h-full object-cover">' +
                    '<button type="button" data-i="' + i + '" class="absolute top-1 right-1 w-5 h-5 bg-black/60 rounded-full text-white text-xs flex items-center justify-center leading-none">×</button>';
                grid.appendChild(cell);
            };
            r.readAsDataURL(file);
        });

        if (files.length > 0) {
            grid.classList.remove('hidden');
            note.textContent = files.length + '枚選択中（最大' + MAX + '枚）';
            note.classList.remove('hidden');
        } else {
            grid.classList.add('hidden');
            note.classList.add('hidden');
        }

        addBtn.style.display = (files.length >= MAX) ? 'none' : '';
        syncHiddenInputs();
    }

    grid.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-i]');
        if (!btn) return;
        files.splice(parseInt(btn.dataset.i), 1);
        render();
    });

    function syncHiddenInputs() {
        // フォーム送信前にFileListを再構成（DataTransferを使用）
        var dt = new DataTransfer();
        files.forEach(function(f) { dt.items.add(f); });
        input.files = dt.files;
    }
})();
</script>
@endpush
@endsection
