<?php

use App\Models\Agenda;
use App\Models\User;
use function Livewire\Volt\{layout, title, state, computed};

layout('components.layouts.app');
title('Kalender Agenda & Progres');

state([
    'targetDate' => fn() => now(),
    'selectedAgendaId' => null,
]);

// Computed property untuk menarik data agenda yang dipilih secara reaktif
$selectedAgenda = computed(function () {
    if (!$this->selectedAgendaId) {
        return null;
    }
    return Agenda::with(['user', 'approver', 'steps'])->find($this->selectedAgendaId);
});

$nextMonth = function () {
    $this->targetDate = $this->targetDate->addMonth();
};

$prevMonth = function () {
    $this->targetDate = $this->targetDate->subMonth();
};

$showDetail = function ($id) {
    $this->selectedAgendaId = $id;
    $this->dispatch('modal-show', name: 'detail-agenda');
};

$getDays = function () {
    $start = $this->targetDate->copy()->startOfMonth()->startOfWeek(\Carbon\CarbonInterface::MONDAY);
    $end = $this->targetDate->copy()->endOfMonth()->endOfWeek(\Carbon\CarbonInterface::SUNDAY);

    $user = auth()->user();

    // LOGIKA FILTER AKSES
    $allowedUserIds = collect([$user->id]); // Selalu bisa melihat diri sendiri

    if ($user->parent_id === null) {
        // Jika dia Manager: Bisa melihat semua bawahannya
        $subordinateIds = User::where('parent_id', $user->id)->pluck('id');
        $allowedUserIds = $allowedUserIds->merge($subordinateIds);
    } else {
        // Jika dia Bawahan: Bisa melihat rekan tim (bawahan lain dari atasan yang sama)
        // dan melihat agenda Atasannya sendiri
        $teamMemberIds = User::where('parent_id', $user->parent_id)->pluck('id');
        $allowedUserIds = $allowedUserIds->merge($teamMemberIds)->push($user->parent_id);
    }

    $days = [];
    $current = $start->copy();

    while ($current <= $end) {
        $days[] = [
            'date' => $current->copy(),
            'isCurrentMonth' => $current->month === $this->targetDate->month,
            'isToday' => $current->isToday(),
            'agendas' => Agenda::whereDate('deadline', $current->format('Y-m-d'))
                ->where('status', 'ongoing')
                ->whereIn('user_id', $allowedUserIds->unique()) // Filter berdasarkan hak akses
                ->with('user')
                ->get(),
        ];
        $current->addDay();
    }
    return $days;
};

?>

<div class="p-6 lg:p-10 space-y-8 bg-slate-50 dark:bg-slate-950 min-h-screen font-sans transition-colors duration-500">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-1">
            <flux:heading size="xl"
                class="font-black uppercase tracking-tighter italic text-indigo-600 dark:text-indigo-400">
                {{ $targetDate->translatedFormat('F Y') }}
            </flux:heading>
            <flux:subheading class="flex items-center gap-2">
                <flux:icon name="information-circle" variant="mini" class="text-indigo-500" />
                Filter: Menampilkan agenda tim Anda.
            </flux:subheading>
        </div>

        <div
            class="flex items-center gap-2 bg-white dark:bg-zinc-900 p-1.5 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-800">
            <flux:button variant="ghost" icon="chevron-left" wire:click="prevMonth" class="rounded-xl" />
            <flux:button variant="ghost" wire:click="targetDate = now()"
                class="text-[10px] font-black uppercase tracking-widest px-4">
                Hari Ini
            </flux:button>
            <flux:button variant="ghost" icon="chevron-right" wire:click="nextMonth" class="rounded-xl" />
        </div>
    </div>

    {{-- Calendar Grid --}}
    <div
        class="bg-white dark:bg-zinc-900 rounded-[2.5rem] border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-xl shadow-slate-200/50 dark:shadow-none">
        <div class="grid grid-cols-7 bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-100 dark:border-zinc-800">
            @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayName)
                <div
                    class="py-4 text-center text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500">
                    {{ $dayName }}
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-7 gap-px bg-zinc-100 dark:bg-zinc-800">
            @foreach ($this->getDays() as $day)
                <div
                    class="bg-white dark:bg-zinc-900 min-h-[140px] p-3 transition-colors hover:bg-slate-50/50 dark:hover:bg-zinc-800/30 {{ !$day['isCurrentMonth'] ? 'bg-zinc-50/30 dark:bg-zinc-950/20' : '' }}">
                    <div class="flex justify-start mb-3">
                        <span
                            class="text-xs font-black {{ $day['isToday'] ? 'bg-indigo-600 text-white w-7 h-7 flex items-center justify-center rounded-full shadow-lg shadow-indigo-500/30 ring-4 ring-indigo-50 dark:ring-indigo-900/30' : ($day['isCurrentMonth'] ? 'text-zinc-700 dark:text-zinc-300' : 'text-zinc-300 dark:text-zinc-700') }}">
                            {{ $day['date']->format('j') }}
                        </span>
                    </div>

                    <div class="space-y-2">
                        @foreach ($day['agendas'] as $agenda)
                            <button wire:click="showDetail({{ $agenda->id }})"
                                class="group w-full text-left p-2.5 rounded-2xl border transition-all duration-300 transform hover:-translate-y-1
        {{ $agenda->user_id === auth()->id()
            ? 'bg-indigo-600 border-indigo-700 shadow-lg shadow-indigo-500/20'
            : 'bg-white dark:bg-zinc-800/50 border-zinc-100 dark:border-zinc-700 hover:border-indigo-500 dark:hover:bg-zinc-800' }}">

                                <div class="flex items-center gap-1.5 mb-1.5 overflow-hidden">
                                    {{-- Indikator Titik --}}
                                    <div
                                        class="w-1.5 h-1.5 rounded-full {{ $agenda->user_id === auth()->id() ? 'bg-white' : 'bg-indigo-500' }} {{ $agenda->user_id === auth()->id() ? 'animate-pulse' : '' }}">
                                    </div>

                                    <span
                                        class="text-[9px] font-black uppercase italic truncate {{ $agenda->user_id === auth()->id() ? 'text-indigo-100' : 'text-indigo-600 dark:text-indigo-400' }}">
                                        {{ $agenda->user->name }}
                                    </span>
                                </div>

                                <div
                                    class="text-[11px] font-bold leading-tight line-clamp-2 {{ $agenda->user_id === auth()->id() ? 'text-white' : 'text-zinc-800 dark:text-zinc-300' }}">
                                    {{ $agenda->title }}
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Modal Detail Agenda --}}
    <flux:modal name="detail-agenda" class="min-w-[600px] !rounded-[2rem]">
        @if ($this->selectedAgenda)
            <div class="space-y-8 p-2">
                {{-- Header Modal --}}
                <div class="relative overflow-hidden p-6 rounded-[2rem] bg-indigo-600 text-white">
                    <div class="relative z-10 flex justify-between items-start">
                        <div class="space-y-2 max-w-[70%]">
                            <flux:badge color="white" size="sm"
                                class="!text-indigo-600 font-black uppercase italic rounded-lg">
                                {{ $this->selectedAgenda->status }}
                            </flux:badge>
                            <h2 class="text-2xl font-black uppercase italic tracking-tighter leading-none">
                                {{ $this->selectedAgenda->title }}
                            </h2>
                            <p class="text-indigo-100 text-xs font-medium">PIC: {{ $this->selectedAgenda->user->name }}
                            </p>
                        </div>
                        <flux:icon name="calendar-days" class="w-12 h-12 text-indigo-400 opacity-50" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label
                            class="text-[10px] font-black uppercase tracking-widest text-zinc-400 ml-1 italic">Tenggat
                            Waktu</label>
                        <div
                            class="flex items-center gap-3 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-2xl border border-zinc-100 dark:border-zinc-700">
                            <flux:icon name="clock" variant="mini" class="text-indigo-500" />
                            <span
                                class="text-sm font-bold">{{ $this->selectedAgenda->deadline->translatedFormat('d F Y, H:i') }}</span>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label
                            class="text-[10px] font-black uppercase tracking-widest text-zinc-400 ml-1 italic">Penanggung
                            Jawab</label>
                        <div
                            class="flex items-center gap-3 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-2xl border border-zinc-100 dark:border-zinc-700">
                            <flux:icon name="user-circle" variant="mini" class="text-indigo-500" />
                            <span class="text-sm font-bold">{{ $this->selectedAgenda->approver->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Progres Tahapan --}}
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 italic">Tracking
                            Progres Tahapan</span>
                        <span
                            class="text-[10px] font-bold text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded-md">
                            {{ $this->selectedAgenda->steps->where('is_completed', true)->count() }}/{{ $this->selectedAgenda->steps->count() }}
                            Selesai
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        @forelse($this->selectedAgenda->steps as $step)
                            <div
                                class="group flex items-center gap-4 p-3 rounded-xl border {{ $step->is_completed ? 'bg-zinc-50/50 dark:bg-zinc-800/30 border-zinc-100 dark:border-zinc-800' : 'bg-transparent border-zinc-200 dark:border-zinc-800' }}">
                                <div class="flex-shrink-0">
                                    @if ($step->is_completed)
                                        <div class="w-6 h-6 flex items-center justify-center bg-indigo-600 rounded-lg">
                                            <flux:icon name="check" variant="mini" class="text-white w-4 h-4" />
                                        </div>
                                    @else
                                        <div
                                            class="w-6 h-6 rounded-lg border-2 border-zinc-300 dark:border-zinc-700 bg-transparent">
                                        </div>
                                    @endif
                                </div>
                                <div class="flex flex-col">
                                    <span
                                        class="text-sm font-bold {{ $step->is_completed ? 'text-zinc-400 line-through decoration-indigo-500/30' : 'text-zinc-800 dark:text-zinc-100' }}">
                                        {{ $step->step_name }}
                                    </span>
                                    @if ($step->description)
                                        <span
                                            class="text-[10px] text-zinc-500 font-medium leading-tight">{{ $step->description }}</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div
                                class="text-center py-10 border-2 border-dashed border-zinc-100 dark:border-zinc-800 rounded-[2rem]">
                                <p class="text-[10px] font-black uppercase tracking-widest text-zinc-300">Belum ada
                                    tahapan kerja</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <p class="text-[9px] text-zinc-400 italic font-medium">Sistem Pemantauan Agenda v2.0</p>
                    <flux:modal.close>
                        <flux:button variant="filled" color="indigo"
                            class="!rounded-xl font-black uppercase text-[10px] italic tracking-widest">Selesai
                        </flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
