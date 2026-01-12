<?php
use App\Models\Agenda;
use Livewire\WithPagination;
use function Livewire\Volt\{state, computed, layout, title, usesPagination};

layout('components.layouts.app');
title('Arsip Riwayat');

usesPagination();

state([
    'search' => '',
    'selectedAgenda' => null,
    'showModal' => false,
]);

// Reset halaman saat user mengetik agar tidak stuck di halaman belakang
$updatedSearch = fn() => $this->resetPage();

$historyAgendas = computed(function () {
    $user = auth()->user();

    $query = Agenda::with(['user', 'steps'])
        ->where('status', 'completed')
        ->when($this->search, function ($q) {
            $q->where('title', 'like', '%' . $this->search . '%');
        });

    $query = $user->parent_id === null ? $query->where('approver_id', $user->id) : $query->where('user_id', $user->id);

    return $query->latest('updated_at')->paginate(9);
});

$openDetail = function ($id) {
    $this->selectedAgenda = Agenda::with(['user', 'steps'])->find($id);
    $this->showModal = true;
};
?>

<div class="p-6 lg:p-10 space-y-10">
    {{-- Header Section --}}
    <div
        class="flex flex-col lg:flex-row lg:items-center justify-between gap-8 border-b border-slate-200 dark:border-white/10 pb-10">
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <span class="h-1.5 w-10 bg-emerald-500 rounded-full animate-pulse"></span>
                <span class="text-[10px] font-black uppercase tracking-[0.4em] text-emerald-500">Vault System</span>
            </div>
            <h1
                class="text-5xl font-black text-slate-900 dark:text-white tracking-tighter uppercase italic leading-none">
                Riwayat <span class="text-emerald-500">Selesai</span>
            </h1>
            <p class="text-slate-500 font-bold text-sm tracking-tight uppercase opacity-70">
                Arsip Terenkripsi: {{ $this->historyAgendas->total() }} Dokumen Ditemukan
            </p>
        </div>

        {{-- Search Bar - Smooth Focus --}}
        <div class="w-full lg:w-96 relative group">
            <div
                class="absolute inset-y-0 left-5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors duration-300">
                <flux:icon.magnifying-glass variant="micro" class="w-5 h-5" />
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari arsip agenda..."
                class="w-full pl-14 pr-6 py-5 bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-[2rem] text-sm font-black tracking-tight focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500/50 transition-all duration-300 outline-none shadow-sm">
        </div>
    </div>

    {{-- Bento Grid Layout dengan Animasi GPU --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($this->historyAgendas as $agenda)
            <div
                class="group relative flex flex-col justify-between p-8 rounded-[3rem]
                       bg-white/60 dark:bg-slate-900/40 border border-white/20 dark:border-white/5 backdrop-blur-sm shadow-xl
                       /* OPTIMASI ANIMASI SMOOTH */
                       transition-[transform,box-shadow,background-color] duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)]
                       hover:scale-[1.03] hover:shadow-2xl hover:shadow-emerald-500/15 hover:bg-white dark:hover:bg-slate-800/60
                       will-change-transform transform-gpu">
                {{-- Decorative Watermark --}}
                <div
                    class="absolute -right-6 -top-6 opacity-[0.03] group-hover:opacity-[0.08] group-hover:rotate-[25deg] group-hover:scale-125 transition-all duration-1000 ease-out pointer-events-none">
                    <flux:icon.check-badge variant="solid" class="w-48 h-48 text-emerald-500" />
                </div>

                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-8">
                        <div
                            class="w-14 h-14 bg-emerald-500 text-white rounded-[1.5rem] flex items-center justify-center shadow-lg shadow-emerald-500/30 group-hover:rotate-6 transition-transform duration-500">
                            <flux:icon.check-badge variant="solid" class="w-8 h-8" />
                        </div>
                        <div class="text-right">
                            <span
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Entry
                                ID</span>
                            <span
                                class="text-xs font-black text-slate-900 dark:text-white">#ARCHV-{{ $agenda->id }}</span>
                        </div>
                    </div>

                    <h3
                        class="text-2xl font-black text-slate-900 dark:text-white leading-[1.1] tracking-tighter mb-4 group-hover:text-emerald-500 transition-colors duration-300 uppercase italic">
                        {{ $agenda->title }}
                    </h3>

                    <div class="flex flex-col gap-2 mb-8">
                        <div class="flex items-center gap-2">
                            <span class="h-1 w-1 rounded-full bg-emerald-500"></span>
                            <span
                                class="text-[11px] font-black text-indigo-500 uppercase tracking-tight">{{ $agenda->user->name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase flex items-center gap-1.5 italic">
                                <flux:icon.calendar variant="micro" class="w-3.5 h-3.5" />
                                {{ $agenda->updated_at->translatedFormat('d M Y') }}
                            </span>
                        </div>
                    </div>

                    {{-- Visual Progress Bar --}}
                    <div class="space-y-3 mb-4">
                        <div
                            class="flex justify-between text-[9px] font-black uppercase text-slate-400 tracking-[0.2em]">
                            <span>Completed Steps</span>
                            <span class="text-emerald-500">{{ $agenda->steps->count() }} Data Points</span>
                        </div>
                        <div
                            class="h-2 w-full bg-slate-100 dark:bg-white/5 rounded-full overflow-hidden flex gap-0.5 p-0.5">
                            @foreach ($agenda->steps as $step)
                                <div
                                    class="h-full flex-1 bg-emerald-500 rounded-full shadow-[0_0_12px_rgba(16,185,129,0.4)] transition-all group-hover:brightness-110">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="relative z-10 mt-auto">
                    <button wire:click="openDetail({{ $agenda->id }})"
                        class="w-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-[11px] font-black uppercase py-5 rounded-2xl tracking-[0.3em]
                               transition-all duration-300 hover:bg-emerald-500 hover:text-white dark:hover:bg-emerald-500 dark:hover:text-white hover:shadow-lg hover:shadow-emerald-500/20 active:scale-95">
                        Buka Arsip Detail
                    </button>
                </div>
            </div>
        @empty
            <div
                class="col-span-full py-32 flex flex-col items-center justify-center glass-card rounded-[4rem] border-dashed border-slate-300 dark:border-white/10 text-center animate-in fade-in zoom-in duration-700">
                <div class="w-24 h-24 bg-slate-50 dark:bg-white/5 rounded-full flex items-center justify-center mb-8">
                    <flux:icon.magnifying-glass class="w-12 h-12 text-slate-200 dark:text-white/10" />
                </div>
                <h3 class="text-3xl font-black text-slate-900 dark:text-white tracking-tighter uppercase italic">Data
                    Kosong</h3>
                <p class="text-slate-400 font-bold uppercase text-xs tracking-widest mt-2">Tidak ditemukan hasil untuk
                    "{{ $this->search }}"</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination - Modern Style --}}
    <div class="pt-12 border-t border-slate-200 dark:border-white/10">
        {{ $this->historyAgendas->links() }}
    </div>

    {{-- MODAL DETAIL --}}
    @if ($selectedAgenda)
        <flux:modal wire:model="showModal"
            class="!bg-slate-50/98 dark:!bg-slate-900/98 backdrop-blur-3xl border-white/20 rounded-[4rem] md:w-[800px] !p-0 shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
            <div class="p-12 space-y-10">
                <div class="flex justify-between items-start">
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <span
                                class="px-4 py-1.5 bg-emerald-500/10 text-emerald-500 text-[10px] font-black rounded-full uppercase tracking-widest border border-emerald-500/20 shadow-sm">Verified
                                Report</span>
                        </div>
                        <h2
                            class="text-5xl font-black text-slate-900 dark:text-white tracking-tighter uppercase italic leading-none">
                            {{ $selectedAgenda->title }}</h2>
                    </div>
                    <button wire:click="$set('showModal', false)"
                        class="p-4 bg-white dark:bg-white/5 hover:bg-red-500 hover:text-white rounded-3xl transition-all duration-300 shadow-sm active:scale-90">
                        <flux:icon.x-mark class="w-6 h-6" />
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="p-8 bg-white dark:bg-white/5 rounded-[2.5rem] border border-white/20 shadow-sm">
                        <span
                            class="text-[11px] font-black text-indigo-500 uppercase tracking-[0.2em] block mb-3">Responsible
                            Officer</span>
                        <p class="text-2xl font-black text-slate-800 dark:text-white tracking-tight leading-none">
                            {{ $selectedAgenda->user->name }}</p>
                    </div>
                    <div class="p-8 bg-white dark:bg-white/5 rounded-[2.5rem] border border-white/20 shadow-sm">
                        <span
                            class="text-[11px] font-black text-emerald-500 uppercase tracking-[0.2em] block mb-3">Completion
                            Date</span>
                        <p class="text-2xl font-black text-slate-800 dark:text-white tracking-tight leading-none">
                            {{ $selectedAgenda->updated_at->translatedFormat('d F Y') }}</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="flex items-center gap-5">
                        <span class="text-xs font-black uppercase tracking-[0.4em] text-slate-400">Activity
                            Sequence</span>
                        <div class="flex-1 h-[2px] bg-slate-200 dark:bg-white/10 rounded-full"></div>
                    </div>

                    <div class="space-y-4 max-h-[400px] overflow-y-auto pr-4 custom-scrollbar">
                        @foreach ($selectedAgenda->steps as $index => $step)
                            <div x-data="{ open: false }"
                                class="bg-white/50 dark:bg-white/5 border border-white/10 rounded-[2rem] overflow-hidden transition-all duration-300 hover:border-emerald-500/30">
                                <button @click="open = !open" type="button"
                                    class="w-full flex items-center gap-6 p-6 text-left group">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shadow-lg shadow-emerald-500/20 shrink-0 group-hover:scale-110 transition-transform">
                                        <flux:icon.check-circle variant="solid" class="w-6 h-6" />
                                    </div>
                                    <div class="flex-1">
                                        <span
                                            class="text-base font-black text-slate-800 dark:text-slate-100 tracking-tight block mb-0.5 leading-tight">{{ $step->step_name }}</span>
                                        <span
                                            class="text-[10px] font-bold text-slate-400 uppercase italic tracking-tighter">Verified
                                            at: {{ $step->updated_at->format('H:i') }} WIB</span>
                                    </div>
                                    <flux:icon.chevron-down :class="open ? 'rotate-180 text-emerald-500' : ''"
                                        class="w-5 h-5 text-slate-400 transition-all duration-500" />
                                </button>

                                <div x-show="open" x-collapse x-cloak class="px-6 pb-6">
                                    <div
                                        class="p-6 bg-slate-100 dark:bg-black/30 rounded-[2rem] border-l-[6px] border-emerald-500 space-y-4">
                                        <div>
                                            <span
                                                class="text-[10px] font-black text-emerald-500 uppercase block mb-2 tracking-[0.2em]">Penyelesaian
                                                Dokumen:</span>
                                            <p
                                                class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed font-bold italic opacity-80">
                                                "{{ $step->notes ?? 'Pekerjaan selesai tanpa anomali catatan.' }}"</p>
                                        </div>
                                        @if ($step->attachment)
                                            <a href="{{ asset('storage/' . $step->attachment) }}" target="_blank"
                                                class="inline-flex items-center gap-3 text-[10px] font-black text-white bg-slate-900 dark:bg-indigo-600 px-6 py-4 rounded-2xl hover:scale-105 active:scale-95 transition-all shadow-xl uppercase tracking-widest">
                                                <flux:icon.paper-clip variant="micro" /> Lihat Dokumen Bukti
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button wire:click="$set('showModal', false)"
                        class="w-full md:w-auto px-16 py-5 bg-emerald-500 text-white rounded-[2rem] text-xs font-black uppercase tracking-[0.4em] shadow-2xl shadow-emerald-500/30 hover:bg-emerald-600 transition-all active:scale-95">
                        Close Archive
                    </button>
                </div>
            </div>
        </flux:modal>
    @endif

    {{-- Custom Layer Styles --}}
    <style>
        [x-cloak] {
            display: none !important;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(16, 185, 129, 0.2);
            border-radius: 50px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(16, 185, 129, 0.5);
        }

        /* Smooth Pagination Fix */
        .pagination span,
        .pagination a {
            border-radius: 1.5rem !important;
            border: none !important;
            font-weight: 900 !important;
            font-size: 11px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.1em;
            padding: 12px 20px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
    </style>
</div>
