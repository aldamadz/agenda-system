<?php
use App\Models\AgendaLog;
use Livewire\WithPagination;
use function Livewire\Volt\{state, computed, layout, title, updating, usesPagination};

layout('components.layouts.app');
title('Log Aktivitas Sistem');

usesPagination();

state([
    'search' => '',
    'date_from' => '',
    'date_to' => '',
    'showModal' => false,
    'selectedLog' => null,
]);

// Fungsi untuk membuka modal detail
$openDetail = function ($logId) {
    // Memuat detail log beserta seluruh histori untuk agenda tersebut
    $this->selectedLog = AgendaLog::with(['agenda', 'agenda.user'])->find($logId);
    $this->showModal = true;
};

updating(['search', 'date_from', 'date_to'], function () {
    $this->resetPage();
});

$logs = computed(function () {
    // 1. Dapatkan ID log terbaru untuk setiap agenda (agar list tidak penuh dengan agenda yang sama)
    $latestLogIds = AgendaLog::selectRaw('MAX(id) as id')->groupBy('agenda_id')->pluck('id');

    // 2. Query utama dengan filter pencarian dan tanggal
    return AgendaLog::with(['agenda', 'agenda.user'])
        ->whereIn('id', $latestLogIds)
        ->when($this->search, function ($query) {
            $query->whereHas('agenda', fn($q) => $q->where('title', 'like', '%' . $this->search . '%'))->orWhere('issue_description', 'like', '%' . $this->search . '%');
        })
        ->when($this->date_from, fn($q) => $q->whereDate('log_date', '>=', $this->date_from))
        ->when($this->date_to, fn($q) => $q->whereDate('log_date', '<=', $this->date_to))
        ->latest('log_date')
        ->latest('log_time')
        ->paginate(10);
});
?>

<div class="p-6 lg:p-10 space-y-8 animate-in fade-in duration-500">
    {{-- Header & Filter Section --}}
    <div
        class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 border-b border-slate-200 dark:border-white/10 pb-8">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="h-1.5 w-6 bg-indigo-500 rounded-full"></span>
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-indigo-500">System Logs</span>
            </div>
            <h1
                class="text-4xl font-black text-slate-900 dark:text-white tracking-tighter uppercase italic leading-none">
                Log <span class="text-indigo-500">Aktivitas</span>
            </h1>
            <p class="text-slate-500 font-bold text-xs tracking-tight uppercase opacity-70 mt-2">Daftar ringkas riwayat
                progres dan anomali sistem.</p>
        </div>

        <div class="flex flex-col md:flex-row gap-4">
            <flux:input wire:model.live.debounce.500ms="search" icon="magnifying-glass"
                placeholder="Cari agenda atau aktivitas..." class="md:w-64" />
            <div class="flex gap-2 items-center">
                <flux:input type="date" wire:model.live="date_from" class="text-xs" />
                <span class="text-slate-400 font-bold">-</span>
                <flux:input type="date" wire:model.live="date_to" class="text-xs" />
            </div>
        </div>
    </div>

    {{-- LIST LOGS DENGAN EFEK HOVER SMOOTH --}}
    <div class="grid grid-cols-1 gap-3">
        @forelse($this->logs as $log)
            <div wire:click="openDetail({{ $log->id }})"
                class="group relative flex items-center justify-between p-5 bg-white/60 dark:bg-slate-900/40 border border-slate-200 dark:border-white/5 rounded-2xl cursor-pointer
                       transition-all duration-300 hover:scale-[1.01] hover:bg-white dark:hover:bg-slate-800 hover:shadow-lg hover:shadow-indigo-500/5 hover:border-indigo-500/50
                       will-change-transform transform-gpu">
                <div class="flex items-center gap-5">
                    {{-- Status Indicator --}}
                    <div class="relative flex h-3 w-3">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $log->issue_category === 'Penyelesaian' ? 'bg-emerald-400' : 'bg-indigo-400' }}"></span>
                        <span
                            class="relative inline-flex rounded-full h-3 w-3 {{ $log->issue_category === 'Penyelesaian' ? 'bg-emerald-500' : 'bg-indigo-500' }}"></span>
                    </div>

                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-0.5">
                            {{ $log->agenda->title ?? 'Agenda Tidak Diketahui' }}
                        </p>
                        <h4
                            class="text-sm font-black text-slate-800 dark:text-white tracking-tight group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                            {{ $log->issue_description }}
                        </h4>
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs text-slate-900 dark:text-slate-200 font-black tracking-tighter">
                            {{ \Carbon\Carbon::parse($log->log_date)->translatedFormat('d M Y') }}
                        </p>
                        <p class="text-[10px] text-slate-400 font-bold uppercase italic tracking-tighter">
                            {{ \Carbon\Carbon::parse($log->log_time)->format('H:i') }} WIB
                        </p>
                    </div>
                    <flux:icon.chevron-right variant="micro"
                        class="text-slate-300 group-hover:text-indigo-500 transition-transform group-hover:translate-x-1" />
                </div>
            </div>
        @empty
            <div
                class="py-20 flex flex-col items-center justify-center border-2 border-dashed border-slate-200 dark:border-white/5 rounded-[3rem] opacity-50">
                <flux:icon.magnifying-glass class="w-12 h-12 text-slate-300 mb-4" />
                <p class="text-sm font-black text-slate-400 uppercase tracking-widest">Tidak ada aktivitas ditemukan</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="pt-6">
        {{ $this->logs->links() }}
    </div>

    {{-- MODAL DETAIL LOG (TIMELINE STYLE) --}}
    <flux:modal wire:model="showModal"
        class="md:w-[700px] !p-0 !bg-white/95 dark:!bg-slate-900/95 backdrop-blur-xl border-none shadow-2xl rounded-[3rem] overflow-hidden">
        @if ($selectedLog)
            <div class="p-10 space-y-8">
                <div class="flex justify-between items-start">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span
                                class="px-3 py-1 bg-indigo-500/10 text-indigo-500 text-[9px] font-black rounded-full uppercase tracking-widest border border-indigo-500/20">Audit
                                Trail</span>
                        </div>
                        <h2
                            class="text-3xl font-black text-slate-900 dark:text-white tracking-tighter uppercase italic">
                            {{ $selectedLog->agenda->title }}</h2>
                        <p class="text-xs text-slate-500 font-bold uppercase tracking-tight italic opacity-70">PJ:
                            {{ $selectedLog->agenda->user->name }}</p>
                    </div>
                </div>

                <div class="space-y-6 max-h-[450px] overflow-y-auto pr-4 custom-scrollbar">
                    @php
                        $allHistory = \App\Models\AgendaLog::where('agenda_id', $selectedLog->agenda_id)
                            ->latest('log_date')
                            ->latest('log_time')
                            ->get();
                    @endphp

                    <div
                        class="relative space-y-8 before:absolute before:inset-0 before:ml-5 before:-translate-x-px before:h-full before:w-0.5 before:bg-gradient-to-b before:from-indigo-500 before:via-slate-200 dark:before:via-white/10 before:to-transparent">
                        @foreach ($allHistory as $history)
                            <div class="relative flex items-start gap-8 group">
                                {{-- Timeline Dot --}}
                                <div class="absolute left-0 w-10 h-10 flex items-center justify-center">
                                    <div
                                        class="w-4 h-4 rounded-full border-4 border-white dark:border-slate-900 shadow-sm z-10 transition-transform group-hover:scale-125 {{ $history->issue_category === 'Penyelesaian' ? 'bg-emerald-500 shadow-emerald-500/50' : 'bg-indigo-500 shadow-indigo-500/50' }}">
                                    </div>
                                </div>

                                <div
                                    class="ml-12 flex-1 bg-slate-50 dark:bg-white/5 p-6 rounded-[2rem] border border-slate-100 dark:border-white/5 transition-all group-hover:border-indigo-500/30">
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                            {{ \Carbon\Carbon::parse($history->log_date)->translatedFormat('d M Y') }}
                                            • {{ \Carbon\Carbon::parse($history->log_time)->format('H:i') }}
                                        </span>
                                        <flux:badge size="sm" variant="subtle"
                                            color="{{ $history->issue_category === 'Penyelesaian' ? 'emerald' : 'indigo' }}"
                                            class="!text-[9px] !font-black uppercase tracking-tighter">
                                            {{ $history->issue_category }}
                                        </flux:badge>
                                    </div>

                                    <p
                                        class="text-sm font-black text-slate-800 dark:text-white tracking-tight mb-3 italic">
                                        "{{ $history->issue_description }}"
                                    </p>

                                    <div
                                        class="p-4 bg-white dark:bg-black/20 rounded-2xl text-[11px] text-slate-500 dark:text-slate-400 font-bold leading-relaxed border-l-4 border-indigo-500/50">
                                        {{ $history->progress_note ?? 'Tidak ada catatan teknis tambahan.' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-4">
                    <button wire:click="$set('showModal', false)"
                        class="w-full py-5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-black uppercase tracking-[0.3em] rounded-2xl hover:bg-indigo-600 hover:text-white transition-all active:scale-95 shadow-xl">
                        Tutup Audit Log
                    </button>
                </div>
            </div>
        @endif
    </flux:modal>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(99, 102, 241, 0.2);
            border-radius: 20px;
        }
    </style>
</div>
