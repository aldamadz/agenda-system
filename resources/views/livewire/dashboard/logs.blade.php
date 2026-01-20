<?php

use App\Models\AgendaLog;
use Illuminate\Support\Carbon;
use Livewire\WithPagination;
use function Livewire\Volt\{state, computed, layout, title, usesPagination};

layout('components.layouts.app');

title('Log Aktivitas Sistem');

usesPagination();

state([
    'search' => '',
    'selectedLogId' => null,
    'showModal' => false,
    'perPage' => 10,

    // State Filter Waktu Default
    'filterMode' => 'all',
    'filterDate' => fn() => date('Y-m-d'),
    'filterMonth' => fn() => date('Y-m'),
    'filterStart' => fn() => Carbon::now()->startOfMonth()->format('Y-m-d'),
    'filterEnd' => fn() => Carbon::now()->endOfMonth()->format('Y-m-d'),
]);

$updatedSearch = fn() => $this->resetPage();
$updatedFilterMode = fn() => $this->resetPage();

$logs = computed(function () {
    $user = auth()->user();

    // Mengambil ID log terbaru untuk setiap agenda agar tidak duplikat di grid utama
    $latestLogIds = AgendaLog::selectRaw('MAX(id) as id')->groupBy('agenda_id')->pluck('id');

    return AgendaLog::with(['agenda', 'agenda.user'])
        ->whereIn('id', $latestLogIds)
        ->when($this->search, function ($q) {
            $q->where(function ($query) {
                $query->whereHas('agenda', fn($adj) => $adj->where('title', 'like', '%' . $this->search . '%'))->orWhere('issue_description', 'like', '%' . $this->search . '%');
            });
        })
        ->when($this->filterMode === 'daily' && $this->filterDate, fn($q) => $q->whereDate('log_date', $this->filterDate))
        ->when($this->filterMode === 'monthly' && $this->filterMonth, function ($q) {
            $date = Carbon::parse($this->filterMonth);
            $q->whereMonth('log_date', $date->month)->whereYear('log_date', $date->year);
        })
        ->when($this->filterMode === 'range' && $this->filterStart && $this->filterEnd, function ($q) {
            $q->whereBetween('log_date', [$this->filterStart, $this->filterEnd]);
        })
        ->latest('log_date')
        ->paginate($this->perPage);
});

$selectedLog = computed(function () {
    return $this->selectedLogId ? AgendaLog::with(['agenda', 'agenda.user'])->find($this->selectedLogId) : null;
});

$openDetail = function ($id) {
    $this->selectedLogId = $id;
    $this->showModal = true;
};

$resetFilters = fn() => [($this->filterMode = 'all'), ($this->search = ''), $this->resetPage()];
?>

<div class="p-6 space-y-6 bg-slate-50 dark:bg-zinc-950 min-h-screen font-sans transition-colors duration-300">

    {{-- Header --}}
    <div
        class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 border-b border-slate-200 dark:border-zinc-800 pb-8">
        <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase leading-none">
                Log <span class="text-indigo-600 dark:text-indigo-500">Aktivitas</span>
            </h1>
            <p class="text-[10px] font-black text-slate-400 dark:text-zinc-500 uppercase tracking-[0.2em] mt-3 italic">
                Monitoring Progres & Perubahan Sistem</p>
        </div>

        <div class="w-full lg:w-96 relative group">
            <div
                class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-slate-400 dark:text-zinc-500 group-focus-within:text-indigo-500 transition-colors">
                <flux:icon.magnifying-glass variant="micro" class="size-4" />
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari Agenda atau Deskripsi..."
                class="w-full pl-11 pr-4 py-3 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-2xl text-xs font-bold text-slate-900 dark:text-zinc-100 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm">
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div
        class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 p-4 rounded-[2rem] flex flex-wrap items-center gap-4 shadow-sm relative z-10">
        <div
            class="flex items-center gap-2 pr-4 border-r border-slate-100 dark:border-zinc-700 text-slate-500 dark:text-zinc-400">
            <flux:icon.funnel variant="micro" class="size-4" />
            <span class="text-[10px] font-black uppercase">Filter</span>
        </div>

        <div class="relative min-w-[150px]">
            <select wire:model.live="filterMode"
                class="w-full appearance-none bg-slate-100 dark:bg-zinc-800 text-[10px] font-black uppercase border border-slate-200 dark:border-zinc-600 rounded-xl pl-4 pr-10 py-2.5 text-slate-700 dark:text-zinc-200 focus:ring-2 focus:border-indigo-500 outline-none cursor-pointer">
                <option value="all">Semua Waktu</option>
                <option value="daily">Harian</option>
                <option value="monthly">Bulanan</option>
                <option value="range">Rentang Tanggal</option>
            </select>
            <div
                class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400 dark:text-zinc-500">
                <flux:icon.chevron-down variant="micro" class="size-3" />
            </div>
        </div>

        @if ($filterMode !== 'all')
            <div class="flex flex-wrap items-center gap-3">
                @if ($filterMode === 'daily')
                    <input type="date" wire:model.live="filterDate"
                        class="bg-slate-100 dark:bg-zinc-800 text-slate-900 dark:text-white border border-slate-200 dark:border-zinc-600 rounded-xl text-[10px] font-black px-4 py-2.5 outline-none color-scheme-dark">
                @elseif($filterMode === 'monthly')
                    <input type="month" wire:model.live="filterMonth"
                        class="bg-slate-100 dark:bg-zinc-800 text-slate-900 dark:text-white border border-slate-200 dark:border-zinc-600 rounded-xl text-[10px] font-black px-4 py-2.5 outline-none color-scheme-dark">
                @elseif($filterMode === 'range')
                    <div
                        class="flex items-center gap-2 bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-600 rounded-xl px-2">
                        <input type="date" wire:model.live="filterStart"
                            class="bg-transparent text-slate-900 dark:text-white text-[10px] font-black p-2 outline-none color-scheme-dark">
                        <span class="text-zinc-400">—</span>
                        <input type="date" wire:model.live="filterEnd"
                            class="bg-transparent text-slate-900 dark:text-white text-[10px] font-black p-2 outline-none color-scheme-dark">
                    </div>
                @endif
            </div>
        @endif

        @if ($filterMode !== 'all' || $search)
            <button wire:click="resetFilters"
                class="ml-auto text-[10px] font-black uppercase text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 px-4 py-2 rounded-xl transition-all active:scale-95">Reset</button>
        @endif
    </div>

    {{-- Grid Content --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-5 pb-10">
        @forelse($this->logs as $log)
            <div wire:key="log-{{ $log->id }}"
                class="group bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 p-5 rounded-[2.5rem] shadow-sm hover:border-indigo-500 transition-all duration-300 flex flex-col justify-between overflow-hidden relative">
                <div>
                    <div class="flex justify-between items-start mb-5">
                        <span
                            class="text-[9px] font-black px-2.5 py-1 bg-slate-50 dark:bg-zinc-800 rounded-lg text-slate-400 dark:text-zinc-500 tracking-tighter uppercase">
                            LOG #{{ $log->id }}
                        </span>
                        <div class="relative flex h-3 w-3">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $log->issue_category === 'Penyelesaian' ? 'bg-emerald-400' : 'bg-indigo-400' }}"></span>
                            <span
                                class="relative inline-flex rounded-full h-3 w-3 {{ $log->issue_category === 'Penyelesaian' ? 'bg-emerald-500' : 'bg-indigo-500' }}"></span>
                        </div>
                    </div>

                    <h3
                        class="text-[12px] font-black text-slate-800 dark:text-zinc-100 uppercase mb-5 h-10 line-clamp-2 leading-tight group-hover:text-indigo-600 transition-colors">
                        {{ $log->issue_description }}
                    </h3>

                    <div class="space-y-3 border-t border-slate-50 dark:border-zinc-800/50 pt-5 mb-6">
                        <div
                            class="flex items-center gap-3 text-slate-500 dark:text-zinc-400 text-[10px] font-black uppercase truncate">
                            <flux:icon.briefcase variant="micro" class="size-3.5 opacity-50" />
                            {{ $log->agenda->title ?? 'N/A' }}
                        </div>
                        <div
                            class="flex items-center gap-3 text-slate-400 dark:text-zinc-500 text-[10px] font-black uppercase">
                            <flux:icon.clock variant="micro" class="size-3.5 opacity-50" />
                            {{ Carbon::parse($log->log_date)->format('d M Y') }}
                        </div>
                    </div>
                </div>
                <button wire:click="openDetail({{ $log->id }})"
                    class="w-full bg-slate-900 dark:bg-zinc-800 hover:bg-indigo-600 text-white text-[10px] font-black uppercase py-3.5 rounded-2xl transition-all shadow-lg shadow-slate-200/50 dark:shadow-none">
                    Buka Detail
                </button>
            </div>
        @empty
            <div
                class="col-span-full py-40 text-center border-2 border-dashed border-slate-200 dark:border-zinc-800 rounded-[3rem] opacity-50 bg-white/30">
                <flux:icon.clipboard-document-list class="size-12 mx-auto mb-4 text-slate-300" />
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.4em]">Database Log Kosong</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div
        class="flex flex-col md:flex-row items-center justify-between gap-8 pt-8 border-t border-slate-200 dark:border-zinc-800">
        <div
            class="flex bg-white dark:bg-zinc-900 p-1.5 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm">
            @foreach ([10, 20, 50] as $num)
                <button wire:click="$set('perPage', {{ $num }})"
                    class="px-5 py-2 text-[10px] font-black rounded-xl {{ $perPage == $num ? 'bg-slate-900 dark:bg-indigo-600 text-white shadow-xl' : 'text-slate-400 hover:text-slate-900 dark:hover:text-zinc-200' }} transition-all">
                    {{ $num }}
                </button>
            @endforeach
        </div>
        <div class="flex items-center gap-4">
            <button wire:click="previousPage" @disabled($this->logs->onFirstPage())
                class="px-6 py-3 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-2xl text-[10px] font-black uppercase text-slate-700 dark:text-zinc-300 disabled:opacity-20 hover:border-indigo-500 active:scale-95 transition-all shadow-sm">Prev</button>
            <div
                class="bg-white dark:bg-zinc-900 px-6 py-3 rounded-2xl border border-slate-200 dark:border-zinc-800 text-[10px] font-black text-slate-900 dark:text-white uppercase tracking-widest">
                {{ $this->logs->currentPage() }} / {{ $this->logs->lastPage() }}
            </div>
            <button wire:click="nextPage" @disabled(!$this->logs->hasMorePages())
                class="px-6 py-3 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-2xl text-[10px] font-black uppercase text-slate-700 dark:text-zinc-300 disabled:opacity-20 hover:border-indigo-500 active:scale-95 transition-all shadow-sm">Next</button>
        </div>
    </div>

    {{-- Modal Detail (LEBAR & LEGA) --}}
    @if ($this->selectedLog)
        <flux:modal wire:model="showModal"
            class="!p-0 !max-w-4xl !rounded-[3rem] overflow-hidden border-none shadow-2xl">
            <div class="p-8 bg-indigo-900 dark:bg-black text-white flex justify-between items-center">
                <div>
                    <span
                        class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.3em] block mb-1 underline decoration-indigo-500/50">
                        Riwayat Log Aktivitas
                    </span>
                    <h2 class="text-xl font-black uppercase tracking-tight truncate max-w-lg">
                        {{ $this->selectedLog->agenda->title }}
                    </h2>
                </div>
                <button wire:click="$set('showModal', false)"
                    class="size-10 flex items-center justify-center bg-white/10 rounded-full hover:bg-rose-500 transition-all active:scale-90">
                    <flux:icon.x-mark variant="micro" class="size-5" />
                </button>
            </div>

            <div class="p-8 space-y-8 max-h-[70vh] overflow-y-auto custom-scrollbar bg-white dark:bg-zinc-950">
                <div class="grid grid-cols-2 gap-6">
                    <div
                        class="p-6 bg-slate-50 dark:bg-zinc-900 rounded-[2rem] border border-slate-100 dark:border-zinc-800/50">
                        <span
                            class="text-[9px] text-zinc-400 block mb-2 font-black uppercase tracking-widest">Penanggung
                            Jawab</span>
                        <div class="flex items-center gap-2">
                            <div
                                class="size-6 bg-indigo-500 rounded-lg flex items-center justify-center text-white text-[10px] font-black">
                                {{ substr($this->selectedLog->agenda->user->name, 0, 1) }}
                            </div>
                            <span
                                class="text-[12px] font-black uppercase dark:text-zinc-100">{{ $this->selectedLog->agenda->user->name }}</span>
                        </div>
                    </div>
                    <div
                        class="p-6 bg-slate-50 dark:bg-zinc-900 rounded-[2rem] border border-slate-100 dark:border-zinc-800/50">
                        <span class="text-[9px] text-zinc-400 block mb-2 font-black uppercase tracking-widest">Waktu
                            Terakhir</span>
                        <span class="text-[12px] font-black uppercase dark:text-zinc-100">
                            {{ Carbon::parse($this->selectedLog->log_date)->format('d/m/Y') }} —
                            {{ $this->selectedLog->log_time }}
                        </span>
                    </div>
                </div>

                <div class="space-y-4 px-2">
                    <h4 class="text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em]">Timeline Progres Agenda
                    </h4>

                    @php
                        $history = App\Models\AgendaLog::where('agenda_id', $this->selectedLog->agenda_id)
                            ->latest('log_date')
                            ->latest('log_time')
                            ->get();
                    @endphp

                    @foreach ($history as $step)
                        <div
                            class="p-6 border border-slate-100 dark:border-zinc-800 rounded-[2.5rem] flex gap-5 bg-white dark:bg-zinc-900/40 hover:border-indigo-500/30 transition-all group">
                            <div
                                class="size-9 {{ $step->issue_category === 'Penyelesaian' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-indigo-500/10 text-indigo-600' }} flex items-center justify-center rounded-2xl shrink-0 group-hover:scale-110 transition-all">
                                <flux:icon.clock variant="micro" class="size-5" />
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <p
                                        class="text-[11px] font-black uppercase dark:text-zinc-200 leading-tight group-hover:text-indigo-500 transition-colors">
                                        {{ $step->issue_category }}
                                    </p>
                                    <span class="text-[9px] font-bold text-zinc-400 uppercase tracking-tighter">
                                        {{ Carbon::parse($step->log_date)->format('d M Y') }}
                                    </span>
                                </div>
                                <p class="text-[12px] font-bold text-slate-700 dark:text-zinc-300 mb-2 italic">
                                    "{{ $step->issue_description }}"</p>
                                <div
                                    class="p-4 bg-slate-50 dark:bg-black/20 rounded-2xl text-xs text-zinc-500 leading-relaxed font-medium">
                                    {{ $step->progress_note ?? 'Tidak ada catatan progres.' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="p-8 bg-slate-50 dark:bg-zinc-900 border-t border-slate-100 dark:border-zinc-800">
                <button wire:click="$set('showModal', false)"
                    class="w-full py-4 bg-slate-900 dark:bg-indigo-600 text-white rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest hover:brightness-110 shadow-xl transition-all">
                    Tutup Riwayat Log
                </button>
            </div>
        </flux:modal>
    @endif

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.05);
        }

        .color-scheme-dark {
            color-scheme: light;
        }

        .dark .color-scheme-dark {
            color-scheme: dark;
        }
    </style>
</div>
