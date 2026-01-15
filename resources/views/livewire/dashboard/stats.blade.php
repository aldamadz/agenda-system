<?php
use App\Models\Agenda;
use App\Models\User;
use Carbon\Carbon;
use function Livewire\Volt\{state, computed, on, updated};

state([
    'filter_type' => 'month',
    'selected_date' => date('Y-m'),
    'date_from' => date('Y-m-01'),
    'date_to' => date('Y-m-t'),
]);

/**
 * Sinkronisasi format input browser saat tipe filter berubah
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
    $isAdmin = $user->role === 'admin';
    $isManager = User::where('parent_id', $user->id)->exists();

    // Inisialisasi Query berdasarkan Role (RBAC)
    $query = Agenda::query();

    if (!$isAdmin) {
        if ($isManager) {
            $query->where(function ($q) use ($user) {
                $q->where('approver_id', $user->id)->orWhere('user_id', $user->id);
            });
        } else {
            $query->where('user_id', $user->id);
        }
    }

    // Logic Filtering Waktu
    try {
        if ($this->filter_type === 'day') {
            $dateString = strlen($this->selected_date) > 7 ? $this->selected_date : date('Y-m-d');
            $query->whereDate('created_at', $dateString);
        } elseif ($this->filter_type === 'month') {
            $dateString = strlen($this->selected_date) <= 7 ? $this->selected_date : date('Y-m');
            $date = Carbon::parse($dateString . '-01');
            $query->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year);
        } elseif ($this->filter_type === 'range') {
            $query->whereBetween('created_at', [Carbon::parse($this->date_from)->startOfDay(), Carbon::parse($this->date_to)->endOfDay()]);
        }
    } catch (\Exception $e) {
        // Fallback jika parsing gagal
    }

    $total = (clone $query)->count();
    $ongoing = (clone $query)->where('status', 'ongoing')->count();
    $completed = (clone $query)->where('status', 'completed')->count();

    // Query khusus untuk Deadline Hari Ini
    $dueQuery = Agenda::where('status', 'ongoing')->whereDate('deadline', Carbon::today());
    if (!$isAdmin) {
        if ($isManager) {
            $dueQuery->where(function ($q) use ($user) {
                $q->where('approver_id', $user->id)->orWhere('user_id', $user->id);
            });
        } else {
            $dueQuery->where('user_id', $user->id);
        }
    }

    return [
        'total' => $total,
        'ongoing' => $ongoing,
        'completed' => $completed,
        'due_today' => $dueQuery->count(),
        'rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
        'active_users' => (clone $query)->where('status', 'ongoing')->with('user')->get()->pluck('user')->filter()->unique('id')->take(5),
        'role_label' => $isAdmin ? 'Seluruh Perusahaan' : ($isManager ? 'Tim Anda' : 'Pribadi'),
    ];
});

on(['refresh-stats' => function () {}]);
?>

<div class="space-y-6">
    <div class="flex flex-col lg:flex-row gap-4">
        {{-- 1. Digital Clock Widget --}}
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
            class="px-6 py-4 rounded-[2rem] border border-white/20 shadow-xl flex items-center gap-4 bg-white/50 dark:bg-zinc-900/50 backdrop-blur-xl min-w-[280px]">
            <div
                class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
                <flux:icon.clock variant="solid" class="w-6 h-6" />
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900 dark:text-white leading-none" x-text="time"></div>
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mt-1" x-text="date"></div>
            </div>
        </div>

        {{-- 2. Advanced Filter Controls --}}
        <div
            class="p-3 rounded-[2rem] flex flex-1 flex-col md:flex-row items-center justify-between gap-4 border border-white/20 shadow-xl bg-white/50 dark:bg-zinc-900/50 backdrop-blur-xl">
            <div class="flex items-center gap-3 px-4">
                <div
                    class="w-10 h-10 bg-indigo-500/10 rounded-2xl flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <flux:icon.adjustments-horizontal class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-[9px] font-black uppercase text-indigo-600 dark:text-indigo-400 tracking-tighter">
                        Scope: {{ $this->getStats['role_label'] }}</p>
                    <p class="text-sm font-bold text-slate-800 dark:text-white leading-tight">
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

            <div
                class="flex flex-wrap items-center gap-2 p-1.5 bg-slate-100 dark:bg-white/5 rounded-2xl border border-slate-200 dark:border-white/5">
                <flux:select wire:model.live="filter_type"
                    class="!bg-transparent !border-none !shadow-none font-bold text-xs !w-28 focus:ring-0">
                    <option value="month">Bulanan</option>
                    <option value="day">Harian</option>
                    <option value="range">Rentang</option>
                </flux:select>

                <div class="flex items-center gap-2">
                    @if ($filter_type === 'month')
                        <flux:input wire:key="input-month" type="month" wire:model.live="selected_date"
                            class="!bg-white dark:!bg-zinc-800 !border-slate-200 dark:!border-zinc-700 !rounded-xl !h-8 text-[11px] shadow-sm w-36" />
                    @elseif($filter_type === 'day')
                        <flux:input wire:key="input-day" type="date" wire:model.live="selected_date"
                            class="!bg-white dark:!bg-zinc-800 !border-slate-200 dark:!border-zinc-700 !rounded-xl !h-8 text-[11px] shadow-sm w-36" />
                    @else
                        {{-- Filter Rentang dengan Perbaikan Warna --}}
                        <div wire:key="input-range" class="flex items-center gap-1 pr-2">
                            <flux:input type="date" wire:model.live="date_from"
                                class="!bg-white dark:!bg-zinc-800 !border-slate-200 dark:!border-zinc-700 !rounded-xl !h-8 !w-32 text-[10px] shadow-sm" />
                            <span class="text-[10px] font-black text-slate-400 mx-1">TO</span>
                            <flux:input type="date" wire:model.live="date_to"
                                class="!bg-white dark:!bg-zinc-800 !border-slate-200 dark:!border-zinc-700 !rounded-xl !h-8 !w-32 text-[10px] shadow-sm" />
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Statistics Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        {{-- Total Agenda Main Card --}}
        <div
            class="md:col-span-8 p-8 rounded-[2.5rem] relative overflow-hidden bg-white/50 dark:bg-zinc-900/50 border border-white/20 shadow-2xl group">
            <div
                class="absolute -right-10 -top-10 size-48 bg-indigo-600/5 rounded-full blur-3xl group-hover:bg-indigo-600/10 transition-colors duration-700">
            </div>

            <div class="relative z-10 h-full flex flex-col justify-between">
                <div>
                    <span
                        class="text-[10px] font-black uppercase tracking-[0.4em] text-indigo-600 dark:text-indigo-400 mb-1 block">Accumulated
                        Data</span>
                    <h2 class="text-8xl font-black text-slate-900 dark:text-white tracking-tighter leading-none">
                        {{ $this->getStats['total'] }}
                    </h2>
                </div>

                <div class="mt-12 flex flex-col sm:flex-row sm:items-center gap-6">
                    <div class="flex -space-x-3 overflow-hidden">
                        @foreach ($this->getStats['active_users'] as $u)
                            <div class="size-10 rounded-full border-2 border-white dark:border-zinc-900 bg-indigo-600 flex items-center justify-center text-white text-[10px] font-black shadow-md ring-2 ring-indigo-500/20"
                                title="{{ $u->name }}">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                        @endforeach
                    </div>
                    <div class="space-y-1">
                        <div class="text-sm font-bold text-slate-700 dark:text-slate-300">
                            <span
                                class="text-indigo-600 dark:text-indigo-400 font-black">{{ $this->getStats['ongoing'] }}</span>
                            Active Projects
                        </div>
                        @if ($this->getStats['due_today'] > 0)
                            <div
                                class="flex items-center gap-1.5 text-[10px] font-black text-rose-500 uppercase tracking-wider">
                                <span class="size-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                {{ $this->getStats['due_today'] }} Critical Deadlines Today
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Success Rate Circular Card --}}
        <div
            class="md:col-span-4 p-8 rounded-[2.5rem] flex flex-col justify-between bg-white/50 dark:bg-zinc-900/50 border border-white/20 shadow-xl group overflow-hidden relative">
            <div class="flex justify-between items-start relative z-10">
                <div
                    class="size-14 bg-emerald-500/10 rounded-2xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 shadow-inner group-hover:rotate-12 transition-transform duration-500">
                    <flux:icon.check-badge variant="solid" class="w-8 h-8" />
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Productivity</p>
                    <span
                        class="text-5xl font-black text-slate-900 dark:text-white tracking-tighter">{{ $this->getStats['rate'] }}%</span>
                </div>
            </div>

            <div class="mt-10 space-y-4 relative z-10">
                <div class="h-5 w-full bg-slate-200 dark:bg-zinc-800 rounded-full overflow-hidden p-1 shadow-inner">
                    <div class="bg-gradient-to-r from-indigo-600 via-indigo-400 to-emerald-500 h-full rounded-full transition-all duration-1000 ease-out shadow-sm"
                        style="width: {{ $this->getStats['rate'] }}%"></div>
                </div>
                <div class="flex justify-between items-center px-1">
                    <p class="text-[10px] font-bold text-slate-500 uppercase">Efficiency Rate</p>
                    <p class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase">
                        {{ $this->getStats['completed'] }} / {{ $this->getStats['total'] }} Done
                    </p>
                </div>
            </div>

            {{-- Subtle Background Decoration --}}
            <div class="absolute -bottom-6 -right-6 size-24 bg-emerald-500/5 rounded-full blur-2xl"></div>
        </div>
    </div>
</div>
