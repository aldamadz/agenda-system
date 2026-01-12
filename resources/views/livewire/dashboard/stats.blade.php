<?php
use App\Models\Agenda;
use Carbon\Carbon;
use function Livewire\Volt\{state, computed, on};

state([
    'filter_type' => 'month',
    'selected_date' => date('Y-m'),
    'date_from' => date('Y-m-01'),
    'date_to' => date('Y-m-t'),
]);

$getStats = computed(function () {
    $user = auth()->user();
    $isM = $user->parent_id === null;
    $query = Agenda::where($isM ? 'approver_id' : 'user_id', $user->id);

    // 1. Logika Filter
    if ($this->filter_type === 'day') {
        $dayDate = strlen($this->selected_date) > 7 ? $this->selected_date : date('Y-m-d');
        $query->whereDate('created_at', $dayDate);
    } elseif ($this->filter_type === 'month') {
        $date = Carbon::parse($this->selected_date);
        $query->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year);
    } elseif ($this->filter_type === 'range') {
        $query->whereBetween('created_at', [Carbon::parse($this->date_from)->startOfDay(), Carbon::parse($this->date_to)->endOfDay()]);
    }

    // 2. Kalkulasi Metrik
    $total = (clone $query)->count();
    $ongoing = (clone $query)->where('status', 'ongoing')->count();
    $completed = (clone $query)->where('status', 'completed')->count();

    $dueToday = Agenda::where($isM ? 'approver_id' : 'user_id', $user->id)
        ->where('status', 'ongoing')
        ->whereDate('deadline', Carbon::today())
        ->count();

    // 3. Ambil User Aktif Secara Dinamis (Relasi: agenda -> user)
    $activeUsers = (clone $query)
        ->where('status', 'ongoing')
        ->with('user')
        ->get()
        ->pluck('user')
        ->filter() // Menghapus null jika ada data tidak valid
        ->unique('id')
        ->take(3);

    return [
        'total' => $total,
        'ongoing' => $ongoing,
        'completed' => $completed,
        'due_today' => $dueToday,
        'rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
        'active_users' => $activeUsers,
    ];
});

on(['refresh-stats' => function () {}]);
?>

<div class="space-y-6">
    {{-- BARIS ATAS: JAM & FILTER --}}
    <div class="flex flex-col lg:flex-row gap-4">
        {{-- Panel Jam Real-time --}}
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
            class="glass-card px-6 py-3 rounded-[2rem] border-white/10 shadow-xl flex items-center gap-4 min-w-[280px]">
            <div
                class="w-12 h-12 bg-indigo-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
                <flux:icon.clock variant="solid" class="w-6 h-6" />
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-none"
                    x-text="time"></div>
                <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1"
                    x-text="date"></div>
            </div>
        </div>

        {{-- Panel Filter Kontrol --}}
        <div
            class="glass-card p-3 rounded-[2rem] flex flex-1 flex-col md:flex-row items-center justify-between gap-4 border-white/10 shadow-xl">
            <div class="flex items-center gap-3 px-4">
                <div class="w-10 h-10 bg-indigo-500/10 rounded-2xl flex items-center justify-center text-indigo-500">
                    <flux:icon.adjustments-horizontal class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">
                        Rentang Waktu</p>
                    <p class="text-sm font-bold text-slate-800 dark:text-white capitalize">
                        @if ($filter_type === 'month')
                            {{ Carbon::parse($selected_date)->translatedFormat('F Y') }}
                        @elseif($filter_type === 'day')
                            {{ Carbon::parse($selected_date)->translatedFormat('d F Y') }}
                        @else
                            Rentang Kustom
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 p-1 bg-slate-200/50 dark:bg-white/5 rounded-2xl">
                <flux:select wire:model.live="filter_type"
                    class="!bg-transparent !border-none !shadow-none font-bold text-sm !w-32 focus:ring-0">
                    <option value="month">Bulanan</option>
                    <option value="day">Harian</option>
                    <option value="range">Rentang</option>
                </flux:select>

                <div class="flex items-center gap-2">
                    @if ($filter_type === 'month')
                        <flux:input type="month" wire:model.live="selected_date"
                            class="!bg-white/50 dark:!bg-white/5 !border-none !shadow-none !rounded-xl !h-9 text-xs" />
                    @elseif($filter_type === 'day')
                        <flux:input type="date" wire:model.live="selected_date"
                            class="!bg-white/50 dark:!bg-white/5 !border-none !shadow-none !rounded-xl !h-9 text-xs" />
                    @else
                        <div class="flex items-center gap-2 pr-2 animate-in fade-in zoom-in-95">
                            <flux:input type="date" wire:model.live="date_from"
                                class="!bg-white/50 dark:!bg-white/5 !border-none !shadow-none !rounded-xl !h-9 !w-32 text-xs" />
                            <span class="text-[10px] font-black text-slate-400">KE</span>
                            <flux:input type="date" wire:model.live="date_to"
                                class="!bg-white/50 dark:!bg-white/5 !border-none !shadow-none !rounded-xl !h-9 !w-32 text-xs" />
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- BENTO GRID STATISTIK --}}
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        {{-- KARTU UTAMA: TOTAL AGENDA & USER AKTIF --}}
        <div
            class="md:col-span-8 glass-card p-8 rounded-[2.5rem] relative overflow-hidden group border-white/10 shadow-2xl">
            {{-- Background Blur Decor --}}
            <div
                class="absolute -right-20 -top-20 w-80 h-80 bg-indigo-500/10 rounded-full blur-[100px] group-hover:bg-indigo-500/20 transition-all duration-700">
            </div>

            <div class="relative z-10 flex flex-col justify-between h-full">
                <div>
                    <span class="text-xs font-black uppercase tracking-[0.3em] text-indigo-500 mb-2 block">Ringkasan
                        Data Agenda</span>
                    <h2 class="text-7xl font-black text-slate-900 dark:text-white tracking-tighter">
                        {{ $this->getStats['total'] }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 mt-2 font-semibold italic text-sm">Jumlah total agenda
                        dalam periode ini</p>
                </div>

                {{-- Bagian User Dinamis --}}
                <div class="mt-8 flex items-center gap-4">
                    <div class="flex -space-x-3">
                        @forelse($this->getStats['active_users'] as $activeUser)
                            <div class="w-10 h-10 rounded-full border-4 border-white dark:border-slate-900 bg-gradient-to-br from-indigo-500 to-fuchsia-500 flex items-center justify-center text-white font-bold text-xs shadow-xl overflow-hidden"
                                title="{{ $activeUser->name }}">
                                @if ($activeUser->avatar_url)
                                    <img src="{{ $activeUser->avatar_url }}" class="w-full h-full object-cover">
                                @else
                                    {{ strtoupper(substr($activeUser->name, 0, 1)) }}
                                @endif
                            </div>
                        @empty
                            <div
                                class="w-10 h-10 rounded-full border-4 border-white dark:border-slate-900 bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-slate-400 text-[10px] italic font-bold shadow-xl">
                                NA</div>
                        @endforelse

                        @if ($this->getStats['ongoing'] > 3)
                            <div
                                class="w-10 h-10 rounded-full border-4 border-white dark:border-slate-900 bg-slate-800 flex items-center justify-center text-white font-bold text-[10px] shadow-xl">
                                +{{ $this->getStats['ongoing'] - 3 }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-400 block">Tugas yang aktif</span>
                        @if ($this->getStats['active_users']->isNotEmpty())
                            <span class="text-[9px] text-indigo-400 font-medium tracking-tight">Oleh:
                                {{ $this->getStats['active_users']->pluck('name')->implode(', ') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU PENCAPAIAN --}}
        <div
            class="md:col-span-4 glass-card p-8 rounded-[2.5rem] flex flex-col justify-between border-white/5 shadow-xl">
            <div class="flex justify-between items-start">
                <div
                    class="w-14 h-14 bg-emerald-500/20 rounded-[1.5rem] flex items-center justify-center text-emerald-500 shadow-inner">
                    <flux:icon.check-badge variant="solid" class="w-8 h-8" />
                </div>
                <div class="text-right">
                    <span
                        class="text-4xl font-black text-slate-900 dark:text-white tracking-tighter">{{ $this->getStats['rate'] }}%</span>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pencapaian</p>
                </div>
            </div>
            <div class="mt-12">
                <div class="flex justify-between items-end mb-3">
                    <p class="text-sm font-bold text-slate-800 dark:text-white">Tingkat Selesai</p>
                    <span class="text-xs font-bold text-emerald-500">{{ $this->getStats['completed'] }} Tuntas</span>
                </div>
                <div class="h-3 w-full bg-slate-200 dark:bg-slate-800/50 rounded-full overflow-hidden p-0.5">
                    <div class="bg-gradient-to-r from-indigo-500 to-emerald-400 h-full rounded-full transition-all duration-1000 shadow-[0_0_15px_rgba(99,102,241,0.5)]"
                        style="width: {{ $this->getStats['rate'] }}%"></div>
                </div>
            </div>
        </div>

        {{-- SEDANG DIPROSES --}}
        <div
            class="md:col-span-6 glass-card p-6 rounded-[2rem] flex items-center gap-6 group hover:bg-white/5 transition-all border-white/5 shadow-lg">
            <div
                class="w-16 h-16 bg-slate-200 dark:bg-white/5 rounded-3xl flex items-center justify-center text-slate-600 dark:text-white group-hover:scale-105 transition-transform">
                <flux:icon.clock class="w-8 h-8" />
            </div>
            <div>
                <h4 class="text-3xl font-black text-slate-900 dark:text-white">{{ $this->getStats['ongoing'] }}</h4>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-[0.2em]">Sedang Diproses</p>
            </div>
        </div>

        {{-- DEADLINE HARI INI --}}
        <div
            class="md:col-span-6 glass-card p-6 rounded-[2rem] flex items-center gap-6 transition-all border-white/5 {{ $this->getStats['due_today'] > 0 ? 'bg-red-500/5 !border-red-500/20' : 'shadow-lg' }}">
            <div
                class="w-16 h-16 {{ $this->getStats['due_today'] > 0 ? 'bg-red-500 text-white shadow-[0_0_25px_rgba(239,68,68,0.4)] animate-pulse' : 'bg-slate-200 dark:bg-white/5 text-slate-600 dark:text-white' }} rounded-3xl flex items-center justify-center">
                <flux:icon.exclamation-triangle class="w-8 h-8" />
            </div>
            <div>
                <h4 class="text-3xl font-black text-slate-900 dark:text-white">{{ $this->getStats['due_today'] }}</h4>
                <p
                    class="text-xs font-bold {{ $this->getStats['due_today'] > 0 ? 'text-red-500' : 'text-slate-500' }} uppercase tracking-[0.2em]">
                    Deadline Hari Ini</p>
            </div>
        </div>
    </div>
</div>
