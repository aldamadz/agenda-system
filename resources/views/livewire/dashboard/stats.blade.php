<?php
use App\Models\Agenda;
use Carbon\Carbon;
use function Livewire\Volt\{state, computed, on, updated};

state([
    'filter_type' => 'month',
    'selected_date' => date('Y-m'),
    'date_from' => date('Y-m-01'),
    'date_to' => date('Y-m-t'),
]);

/**
 * FIX: Sinkronisasi format agar Browser Input mengenali nilainya
 */
updated([
    'filter_type' => function ($value) {
        if ($value === 'day') {
            $this->selected_date = date('Y-m-d');
        } elseif ($value === 'month') {
            $this->selected_date = date('Y-m');
        }
    },
]);

$getStats = computed(function () {
    $user = auth()->user();
    $isM = \App\Models\User::where('parent_id', $user->id)->exists();
    $query = Agenda::where($isM ? 'approver_id' : 'user_id', $user->id);

    try {
        if ($this->filter_type === 'day') {
            // Pastikan format Y-m-d
            $dateString = strlen($this->selected_date) > 7 ? $this->selected_date : date('Y-m-d');
            $query->whereDate('created_at', $dateString);
        } elseif ($this->filter_type === 'month') {
            // Pastikan format Y-m
            $dateString = strlen($this->selected_date) <= 7 ? $this->selected_date : date('Y-m');
            $date = Carbon::parse($dateString . '-01');
            $query->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year);
        } elseif ($this->filter_type === 'range') {
            $query->whereBetween('created_at', [Carbon::parse($this->date_from)->startOfDay(), Carbon::parse($this->date_to)->endOfDay()]);
        }
    } catch (\Exception $e) {
        // Fallback jika error parsing
    }

    $total = (clone $query)->count();
    $ongoing = (clone $query)->where('status', 'ongoing')->count();
    $completed = (clone $query)->where('status', 'completed')->count();

    return [
        'total' => $total,
        'ongoing' => $ongoing,
        'completed' => $completed,
        'due_today' => Agenda::where($isM ? 'approver_id' : 'user_id', $user->id)
            ->where('status', 'ongoing')
            ->whereDate('deadline', Carbon::today())
            ->count(),
        'rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
        'active_users' => (clone $query)->where('status', 'ongoing')->with('user')->get()->pluck('user')->filter()->unique('id')->take(3),
    ];
});

on(['refresh-stats' => function () {}]);
?>

<div class="space-y-6">
    <style>
        /* Perbaikan visibilitas menu aktif di Light Mode */
        :where(html:not(.dark)) [data-flux-navlist-item][data-current="true"] {
            background-color: #f1f5f9 !important;
            color: #4f46e5 !important;
            box-shadow: inset 0 0 0 1px #e2e8f0;
            font-weight: 700 !important;
        }
    </style>

    <div class="flex flex-col lg:flex-row gap-4">
        {{-- Jam Digital --}}
        <div x-data="{
            time: '',
            date: '',
            updateTime() {
                const now = new Date();
                this.time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                this.date = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' });
            }
        }" x-init="updateTime();
        setInterval(() => updateTime(), 1000)"
            class="glass-card px-6 py-3 rounded-[2rem] border-white/20 shadow-xl flex items-center gap-4 bg-white/50 dark:bg-zinc-900/50 backdrop-blur-xl min-w-[280px]">
            <div class="w-12 h-12 bg-indigo-500 rounded-2xl flex items-center justify-center text-white">
                <flux:icon.clock variant="solid" class="w-6 h-6" />
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900 dark:text-white" x-text="time"></div>
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider" x-text="date"></div>
            </div>
        </div>

        {{-- Filter Kontrol --}}
        <div
            class="glass-card p-3 rounded-[2rem] flex flex-1 flex-col md:flex-row items-center justify-between gap-4 border-white/20 shadow-xl bg-white/50 dark:bg-zinc-900/50 backdrop-blur-xl">
            <div class="flex items-center gap-3 px-4">
                <div class="w-10 h-10 bg-indigo-500/10 rounded-2xl flex items-center justify-center text-indigo-500">
                    <flux:icon.adjustments-horizontal class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase text-slate-500">Periode</p>
                    <p class="text-sm font-bold text-slate-800 dark:text-white">
                        @if ($filter_type === 'month')
                            {{ Carbon::parse($selected_date . (strlen($selected_date) <= 7 ? '-01' : ''))->translatedFormat('F Y') }}
                        @elseif($filter_type === 'day')
                            {{ Carbon::parse($selected_date)->translatedFormat('d F Y') }}
                        @else
                            {{ Carbon::parse($date_from)->translatedFormat('d M') }} -
                            {{ Carbon::parse($date_to)->translatedFormat('d M Y') }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 p-1 bg-slate-200/50 dark:bg-white/5 rounded-2xl">
                <flux:select wire:model.live="filter_type"
                    class="!bg-transparent !border-none !shadow-none font-bold text-sm !w-32">
                    <option value="month">Bulanan</option>
                    <option value="day">Harian</option>
                    <option value="range">Rentang</option>
                </flux:select>

                <div class="flex items-center gap-2">
                    {{--
                        PENTING: Penggunaan wire:key memastikan elemen dirender ulang
                        sehingga atribut 'type' dan 'value' disinkronkan ulang oleh browser.
                    --}}
                    @if ($filter_type === 'month')
                        <flux:input wire:key="input-month" type="month" wire:model.live="selected_date"
                            class="!bg-white/80 dark:!bg-white/5 !border-none !rounded-xl !h-9 text-xs" />
                    @elseif($filter_type === 'day')
                        <flux:input wire:key="input-day" type="date" wire:model.live="selected_date"
                            class="!bg-white/80 dark:!bg-white/5 !border-none !rounded-xl !h-9 text-xs" />
                    @else
                        <div wire:key="input-range" class="flex items-center gap-2 pr-2">
                            <flux:input type="date" wire:model.live="date_from"
                                class="!bg-white/80 !border-none !rounded-xl !h-9 !w-32 text-xs" />
                            <span class="text-[10px] font-black text-slate-400">KE</span>
                            <flux:input type="date" wire:model.live="date_to"
                                class="!bg-white/80 !border-none !rounded-xl !h-9 !w-32 text-xs" />
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Statistik Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <div
            class="md:col-span-8 glass-card p-8 rounded-[2.5rem] relative overflow-hidden bg-white/50 dark:bg-zinc-900/50 border-white/20 shadow-2xl">
            <div class="relative z-10 h-full flex flex-col justify-between">
                <div>
                    <span class="text-xs font-black uppercase tracking-[0.3em] text-indigo-500 mb-2 block">Ringkasan
                        Data</span>
                    <h2 class="text-7xl font-black text-slate-900 dark:text-white tracking-tighter">
                        {{ $this->getStats['total'] }}</h2>
                </div>
                <div class="mt-8 flex items-center gap-4">
                    <div class="flex -space-x-3">
                        @foreach ($this->getStats['active_users'] as $u)
                            <div
                                class="w-10 h-10 rounded-full border-4 border-white dark:border-slate-900 bg-indigo-500 flex items-center justify-center text-white text-xs font-bold">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                        @endforeach
                    </div>
                    <span class="text-xs font-bold text-slate-500">{{ $this->getStats['ongoing'] }} Agenda sedang
                        diproses</span>
                </div>
            </div>
        </div>

        <div
            class="md:col-span-4 glass-card p-8 rounded-[2.5rem] flex flex-col justify-between bg-white/50 dark:bg-zinc-900/50 border-white/20 shadow-xl">
            <div class="flex justify-between items-start">
                <div
                    class="w-14 h-14 bg-emerald-500/20 rounded-[1.5rem] flex items-center justify-center text-emerald-500">
                    <flux:icon.check-badge variant="solid" class="w-8 h-8" />
                </div>
                <div class="text-right">
                    <span
                        class="text-4xl font-black text-slate-900 dark:text-white">{{ $this->getStats['rate'] }}%</span>
                </div>
            </div>
            <div class="mt-12">
                <div class="h-3 w-full bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="bg-indigo-500 h-full transition-all duration-1000"
                        style="width: {{ $this->getStats['rate'] }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>
