<?php
use App\Models\Agenda;
use Illuminate\Support\Carbon;
use Livewire\WithPagination;
use function Livewire\Volt\{state, computed, layout, title, usesPagination};

layout('components.layouts.app');

title(fn() => auth()->user()->parent_id === null ? 'Database Arsip Kontrol' : 'Arsip Riwayat Saya');

usesPagination();

state([
    'search' => '',
    'selectedAgendaId' => null,
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

$historyAgendas = computed(function () {
    $user = auth()->user();
    return Agenda::with(['user', 'steps'])
        ->where('status', 'completed')
        ->when($this->filterMode === 'daily' && $this->filterDate, fn($q) => $q->whereDate('updated_at', $this->filterDate))
        ->when($this->filterMode === 'monthly' && $this->filterMonth, function ($q) {
            $date = Carbon::parse($this->filterMonth);
            $q->whereBetween('updated_at', [$date->startOfMonth()->toDateTimeString(), $date->endOfMonth()->toDateTimeString()]);
        })
        ->when($this->filterMode === 'range' && $this->filterStart && $this->filterEnd, function ($q) {
            $q->whereBetween('updated_at', [Carbon::parse($this->filterStart)->startOfDay(), Carbon::parse($this->filterEnd)->endOfDay()]);
        })
        ->when($this->search, function ($q) {
            $q->where(
                fn($query) => $query
                    ->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('id', $this->search)
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', '%' . $this->search . '%')),
            );
        })
        ->where(function ($query) use ($user) {
            $isAdmin = $user->parent_id === null && ($user->role === 'admin' || $user->email === 'admin@gmail.com');
            if ($isAdmin) {
                return $query;
            }
            return $user->parent_id === null ? $query->where(fn($q) => $q->where('approver_id', $user->id)->orWhere('user_id', $user->id)) : $query->where('user_id', $user->id);
        })
        ->latest('updated_at')
        ->paginate($this->perPage);
});

$selectedAgenda = computed(function () {
    return $this->selectedAgendaId ? Agenda::with(['user', 'steps'])->find($this->selectedAgendaId) : null;
});

$openDetail = function ($id) {
    $this->selectedAgendaId = $id;
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
                Database <span class="text-emerald-600 dark:text-emerald-500">Arsip</span>
            </h1>
            <p class="text-[10px] font-black text-slate-400 dark:text-zinc-500 uppercase tracking-[0.2em] mt-3 italic">
                Penyimpanan Laporan Terverifikasi</p>
        </div>

        <div class="w-full lg:w-96 relative group">
            <div
                class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-slate-400 dark:text-zinc-500 group-focus-within:text-emerald-500 transition-colors">
                <flux:icon.magnifying-glass variant="micro" class="size-4" />
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari Judul atau ID..."
                class="w-full pl-11 pr-4 py-3 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-2xl text-xs font-bold text-slate-900 dark:text-zinc-100 outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all shadow-sm">
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div
        class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 p-4 rounded-[1.5rem] md:rounded-[2rem] flex flex-wrap items-center gap-4 shadow-sm relative z-10">
        {{-- Label Filter - Sembunyi di mobile kecil jika terlalu sempit --}}
        <div
            class="hidden sm:flex items-center gap-2 pr-4 border-r border-slate-100 dark:border-zinc-700 text-slate-500 dark:text-zinc-400">
            <flux:icon.funnel variant="micro" class="size-4" />
            <span class="text-[10px] font-black uppercase">Filter</span>
        </div>

        {{-- Mode Selector - Full width di mobile --}}
        <div class="relative w-full sm:w-auto min-w-[150px]">
            <select wire:model.live="filterMode"
                class="w-full appearance-none bg-slate-100 dark:bg-zinc-800 text-[10px] font-black uppercase border border-slate-200 dark:border-zinc-600 rounded-xl pl-4 pr-10 py-2.5 text-slate-700 dark:text-zinc-200 focus:ring-2 focus:border-emerald-500 outline-none cursor-pointer transition-all">
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

        {{-- Input Tanggal Responsif --}}
        @if ($filterMode !== 'all')
            <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                @if ($filterMode === 'daily')
                    <input type="date" wire:model.live="filterDate"
                        class="w-full sm:w-auto bg-slate-100 dark:bg-zinc-800 text-slate-900 dark:text-white border border-slate-200 dark:border-zinc-600 rounded-xl text-[10px] font-black px-4 py-2.5 outline-none color-scheme-dark">
                @elseif($filterMode === 'monthly')
                    <input type="month" wire:model.live="filterMonth"
                        class="w-full sm:w-auto bg-slate-100 dark:bg-zinc-800 text-slate-900 dark:text-white border border-slate-200 dark:border-zinc-600 rounded-xl text-[10px] font-black px-4 py-2.5 outline-none color-scheme-dark">
                @elseif($filterMode === 'range')
                    <div
                        class="flex flex-col sm:flex-row items-center gap-2 bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-600 rounded-xl px-2 w-full sm:w-auto">
                        <input type="date" wire:model.live="filterStart"
                            class="w-full sm:w-auto bg-transparent text-slate-900 dark:text-white text-[10px] font-black p-2 outline-none color-scheme-dark">
                        <span class="hidden sm:block text-zinc-400">—</span>
                        <input type="date" wire:model.live="filterEnd"
                            class="w-full sm:w-auto bg-transparent text-slate-900 dark:text-white text-[10px] font-black p-2 outline-none color-scheme-dark">
                    </div>
                @endif
            </div>
        @endif

        {{-- Reset Button --}}
        @if ($filterMode !== 'all' || $search)
            <button wire:click="resetFilters"
                class="w-full sm:w-auto sm:ml-auto text-[10px] font-black uppercase text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 px-4 py-2 rounded-xl transition-all active:scale-95">
                Reset Filter
            </button>
        @endif
    </div>

    {{-- Grid Content --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-5 pb-10">
        @forelse($this->historyAgendas as $agenda)
            <div wire:key="agenda-{{ $agenda->id }}"
                class="group bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 p-5 rounded-[2.5rem] shadow-sm hover:border-emerald-500 transition-all duration-300 flex flex-col justify-between overflow-hidden relative">
                <div>
                    <div class="flex justify-between items-start mb-5">
                        <span
                            class="text-[9px] font-black px-2.5 py-1 bg-slate-50 dark:bg-zinc-800 rounded-lg text-slate-400 dark:text-zinc-500 tracking-tighter uppercase">ID
                            #{{ $agenda->id }}</span>
                        <flux:icon.check-circle variant="solid"
                            class="size-5 text-emerald-500 dark:text-emerald-400/90" />
                    </div>
                    <h3
                        class="text-[12px] font-black text-slate-800 dark:text-zinc-100 uppercase mb-5 h-10 line-clamp-2 leading-tight group-hover:text-emerald-600 transition-colors">
                        {{ $agenda->title }}</h3>
                    <div class="space-y-3 border-t border-slate-50 dark:border-zinc-800/50 pt-5 mb-6">
                        <div
                            class="flex items-center gap-3 text-slate-500 dark:text-zinc-400 text-[10px] font-black uppercase truncate">
                            <flux:icon.user variant="micro" class="size-3.5 opacity-50" />{{ $agenda->user->name }}
                        </div>
                        <div
                            class="flex items-center gap-3 text-slate-400 dark:text-zinc-500 text-[10px] font-black uppercase">
                            <flux:icon.calendar variant="micro" class="size-3.5 opacity-50" />
                            {{ $agenda->updated_at->format('d M Y') }}
                        </div>
                    </div>
                </div>
                <button wire:click="openDetail({{ $agenda->id }})"
                    class="w-full bg-slate-900 dark:bg-zinc-800 hover:bg-emerald-600 text-white text-[10px] font-black uppercase py-3.5 rounded-2xl transition-all shadow-lg shadow-slate-200/50 dark:shadow-none">Buka
                    Arsip</button>
            </div>
        @empty
            <div
                class="col-span-full py-40 text-center border-2 border-dashed border-slate-200 dark:border-zinc-800 rounded-[3rem] opacity-50 bg-white/30 dark:bg-transparent">
                <flux:icon.archive-box-x-mark class="size-12 mx-auto mb-4 text-slate-300" />
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.4em]">Database Kosong</p>
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
                    class="px-5 py-2 text-[10px] font-black rounded-xl {{ $perPage == $num ? 'bg-slate-900 dark:bg-emerald-600 text-white shadow-xl' : 'text-slate-400 hover:text-slate-900 dark:hover:text-zinc-200' }} transition-all">{{ $num }}</button>
            @endforeach
        </div>
        <div class="flex items-center gap-4">
            <button wire:click="previousPage" @disabled($this->historyAgendas->onFirstPage())
                class="px-6 py-3 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-2xl text-[10px] font-black uppercase text-slate-700 dark:text-zinc-300 disabled:opacity-20 hover:border-emerald-500 active:scale-95 transition-all shadow-sm">Prev</button>
            <div
                class="bg-white dark:bg-zinc-900 px-6 py-3 rounded-2xl border border-slate-200 dark:border-zinc-800 text-[10px] font-black text-slate-900 dark:text-white uppercase tracking-widest">
                {{ $this->historyAgendas->currentPage() }} / {{ $this->historyAgendas->lastPage() }}</div>
            <button wire:click="nextPage" @disabled(!$this->historyAgendas->hasMorePages())
                class="px-6 py-3 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-2xl text-[10px] font-black uppercase text-slate-700 dark:text-zinc-300 disabled:opacity-20 hover:border-emerald-500 active:scale-95 transition-all shadow-sm">Next</button>
        </div>
    </div>

    {{-- Modal Detail --}}
    @if ($this->selectedAgenda)
        <flux:modal wire:model="showModal"
            class="!p-0 !max-w-2xl !rounded-[3rem] overflow-hidden border-none shadow-2xl">
            <div class="p-8 bg-slate-900 dark:bg-black text-white flex justify-between items-center">
                <div>
                    <span
                        class="text-[10px] font-black text-emerald-400 uppercase tracking-[0.3em] block mb-1 underline decoration-emerald-500/50">Laporan
                        Terverifikasi</span>
                    <h2 class="text-xl font-black uppercase tracking-tight truncate max-w-sm">
                        {{ $this->selectedAgenda->title }}</h2>
                </div>
                <button wire:click="$set('showModal', false)"
                    class="size-10 flex items-center justify-center bg-white/10 rounded-full hover:bg-rose-500 transition-all active:scale-90"><flux:icon.x-mark
                        variant="micro" class="size-5" /></button>
            </div>

            <div class="p-8 space-y-8 max-h-[60vh] overflow-y-auto custom-scrollbar bg-white dark:bg-zinc-950">
                <div class="grid grid-cols-2 gap-4">
                    <div
                        class="p-6 bg-slate-50 dark:bg-zinc-900 rounded-[2rem] border border-slate-100 dark:border-zinc-800/50">
                        <span
                            class="text-[9px] text-zinc-400 block mb-2 font-black uppercase tracking-widest">Penanggung
                            Jawab</span>
                        <span
                            class="text-[12px] font-black uppercase dark:text-zinc-100">{{ $this->selectedAgenda->user->name }}</span>
                    </div>
                    <div
                        class="p-6 bg-slate-50 dark:bg-zinc-900 rounded-[2rem] border border-slate-100 dark:border-zinc-800/50">
                        <span class="text-[9px] text-zinc-400 block mb-2 font-black uppercase tracking-widest">Waktu
                            Verifikasi</span>
                        <span
                            class="text-[12px] font-black uppercase dark:text-zinc-100">{{ $this->selectedAgenda->updated_at->format('d/m/Y - H:i') }}</span>
                    </div>
                </div>
                <div class="space-y-4 px-2">
                    <h4 class="text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em]">Log Verifikasi Tahapan
                    </h4>
                    @foreach ($this->selectedAgenda->steps as $step)
                        <div
                            class="p-6 border border-slate-100 dark:border-zinc-800 rounded-[2rem] flex gap-5 bg-white dark:bg-zinc-900/40 hover:border-emerald-500/30 transition-all group">
                            <div
                                class="size-9 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center rounded-2xl shrink-0 group-hover:bg-emerald-500 group-hover:text-white transition-all">
                                <flux:icon.check variant="micro" class="size-5" />
                            </div>
                            <div>
                                <p
                                    class="text-[11px] font-black uppercase dark:text-zinc-200 mb-1 leading-tight group-hover:text-emerald-500 transition-colors">
                                    {{ $step->step_name }}</p>
                                <p class="text-xs text-zinc-500 leading-relaxed font-medium italic">
                                    "{{ $step->notes ?? 'Selesai tanpa catatan tambahan.' }}"</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="p-8 bg-slate-50 dark:bg-zinc-900 border-t border-slate-100 dark:border-zinc-800">
                <button wire:click="$set('showModal', false)"
                    class="w-full py-4 bg-slate-900 dark:bg-emerald-600 text-white rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest hover:brightness-110 shadow-xl transition-all">Tutup
                    Arsip Kontrol</button>
            </div>
        </flux:modal>
    @endif

    <style>
        :root {
            scrollbar-gutter: stable;
        }

        html {
            overflow-x: hidden;
        }

        .font-sans,
        .font-sans * {
            font-style: normal !important;
        }

        [x-cloak] {
            display: none !important;
        }

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

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .color-scheme-dark {
            color-scheme: light;
        }

        .dark .color-scheme-dark {
            color-scheme: dark;
        }
    </style>
</div>
