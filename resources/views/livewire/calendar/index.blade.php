<?php

use App\Models\Agenda;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Str;
use function Livewire\Volt\{layout, title, state, computed};

layout('components.layouts.app');
title('Monitoring Progres Tim');

state([
    'targetDate' => fn() => now()->startOfMonth()->toDateTimeString(),
    'selectedAgendaId' => null,
    'filterUserId' => '',
    'search' => '',
]);

$allowedUserIds = computed(function () {
    $user = auth()->user();
    if (!$user) {
        return collect();
    }
    $ids = collect([$user->id]);
    if ($user->parent_id === null) {
        $subordinateIds = User::where('parent_id', $user->id)->pluck('id');
        $ids = $ids->merge($subordinateIds);
    } else {
        $teamMemberIds = User::where('parent_id', $user->parent_id)->pluck('id');
        $ids = $ids->merge($teamMemberIds)->push($user->parent_id);
    }
    return $ids->unique();
});

$subordinates = computed(function () {
    $user = auth()->user();
    return $user->parent_id === null ? User::where('parent_id', $user->id)->get() : collect();
});

$agendas = computed(function () {
    $query = Agenda::whereIn('status', ['ongoing', 'completed'])->with(['user', 'steps']);
    if ($this->filterUserId) {
        $query->where('user_id', $this->filterUserId);
    } else {
        $query->whereIn('user_id', $this->allowedUserIds);
    }
    if ($this->search) {
        $query->where(function ($q) {
            $q->where('title', 'like', '%' . $this->search . '%')->orWhereHas('user', function ($qu) {
                $qu->where('name', 'like', '%' . $this->search . '%');
            });
        });
    }
    return $query->get();
});

$calendarDays = computed(function () {
    $currentDate = Carbon::parse($this->targetDate);
    $today = now()->startOfDay();
    $start = $currentDate->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
    $end = $currentDate->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

    $days = [];
    $current = $start->copy();
    while ($current <= $end) {
        $dateRef = $current->copy()->startOfDay();
        $dailyAgendas = $this->agendas->filter(function ($agenda) use ($dateRef, $today) {
            $createdAt = Carbon::parse($agenda->created_at)->startOfDay();
            if ($agenda->status === 'completed') {
                $completedAt = Carbon::parse($agenda->updated_at)->startOfDay();
                return $dateRef->between($createdAt, $completedAt);
            }
            return $dateRef->greaterThanOrEqualTo($createdAt) && $dateRef->lessThanOrEqualTo($today);
        });

        $days[] = [
            'date' => $current->copy(),
            'isCurrentMonth' => $current->month === $currentDate->month,
            'isToday' => $current->isToday(),
            'agendas' => $dailyAgendas,
        ];
        $current->addDay();
    }
    return $days;
});

$selectedAgenda = computed(fn() => $this->selectedAgendaId ? Agenda::with(['user', 'steps'])->find($this->selectedAgendaId) : null);

$nextMonth = fn() => ($this->targetDate = Carbon::parse($this->targetDate)->addMonth()->toDateTimeString());
$prevMonth = fn() => ($this->targetDate = Carbon::parse($this->targetDate)->subMonth()->toDateTimeString());
$goToMonth = fn($val) => ($this->targetDate = Carbon::parse($val . '-01')->toDateTimeString());
$showDetail = fn($id) => ($this->selectedAgendaId = $id) && $this->dispatch('modal-show', name: 'detail-agenda');

$exportPdf = function () {
    Carbon::setLocale('id');
    $currentDate = Carbon::parse($this->targetDate);
    $startOfMonth = $currentDate->copy()->startOfMonth();
    $endOfMonth = $currentDate->copy()->endOfMonth();
    $today = now()->startOfDay();

    $filterInfo = $this->filterUserId ? User::find($this->filterUserId)->name : 'Seluruh Tim';
    $reportData = collect();

    for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
        $dateRef = $date->copy()->startOfDay();
        $dailyAgendas = $this->agendas->filter(function ($agenda) use ($dateRef, $today) {
            $createdAt = Carbon::parse($agenda->created_at)->startOfDay();
            if ($agenda->status === 'completed') {
                $completedAt = Carbon::parse($agenda->updated_at)->startOfDay();
                return $dateRef->between($createdAt, $completedAt);
            }
            return $dateRef->greaterThanOrEqualTo($createdAt) && $dateRef->lessThanOrEqualTo($today);
        });

        if ($dateRef->isWeekend()) {
            $reportData->push((object) ['type' => 'weekend', 'display_date' => $dateRef->toDateTimeString()]);
        } elseif ($dailyAgendas->isEmpty()) {
            $reportData->push((object) ['type' => 'empty_day', 'display_date' => $dateRef->toDateTimeString()]);
        } else {
            foreach ($dailyAgendas as $agenda) {
                $clonedAgenda = clone $agenda;
                $clonedAgenda->type = 'agenda';
                $clonedAgenda->display_date = $dateRef->toDateTimeString();

                $jamBuat = Carbon::parse($agenda->created_at);
                $jamUpdate = Carbon::parse($agenda->updated_at);
                $deadline = $agenda->deadline ? Carbon::parse($agenda->deadline) : null;

                $clonedAgenda->jam_dibuat = $jamBuat->format('H:i');
                $clonedAgenda->jam_deadline = $deadline ? ($deadline->isSameDay($dateRef) ? $deadline->format('H:i') : $deadline->translatedFormat('d M H:i')) : '-';
                $clonedAgenda->jam_selesai = $agenda->status === 'completed' ? $jamUpdate->format('H:i') : null;
                $clonedAgenda->display_status = $agenda->status === 'completed' && $dateRef->greaterThanOrEqualTo($jamUpdate->copy()->startOfDay()) ? 'completed' : 'ongoing';

                // Logika Telat Agenda Utama
                $clonedAgenda->is_agenda_late = $agenda->status === 'completed' && $deadline && $jamUpdate->gt($deadline);

                $clonedAgenda->display_steps = $agenda->steps->map(function ($step, $index) use ($dateRef, $agenda, $jamBuat) {
                    $stepDoneAt = $step->completed_at ? Carbon::parse($step->completed_at) : null;
                    $isDoneOnThisDate = $stepDoneAt && $dateRef->greaterThanOrEqualTo($stepDoneAt->copy()->startOfDay());

                    $isOverdue = false;
                    $overdueLabel = '';

                    if ($stepDoneAt) {
                        $startTime = $index === 0 ? $jamBuat : Carbon::parse($agenda->steps[$index - 1]->completed_at ?? $agenda->steps[$index - 1]->updated_at);
                        $actualMinutes = $startTime->diffInMinutes($stepDoneAt);
                        $limitMinutes = 0;
                        if (preg_match('/(\d+)j/', $step->duration, $h)) {
                            $limitMinutes += $h[1] * 60;
                        }
                        if (preg_match('/(\d+)m/', $step->duration, $m)) {
                            $limitMinutes += $m[1];
                        }

                        if ($actualMinutes > $limitMinutes) {
                            $isOverdue = true;
                            $diff = $actualMinutes - $limitMinutes;
                            $overdueLabel = floor($diff / 60) . 'j ' . $diff % 60 . 'm';
                        }
                    }

                    return (object) [
                        'step_name' => $step->step_name,
                        'is_completed' => $isDoneOnThisDate,
                        'completed_time' => $isDoneOnThisDate ? $stepDoneAt->format('H:i') : null,
                        'duration' => $step->duration,
                        'is_overdue' => $isOverdue,
                        'overdue_label' => $overdueLabel,
                    ];
                });
                $reportData->push($clonedAgenda);
            }
        }
    }

    $pdf = Pdf::loadView('pdf.agenda-report', ['data' => $reportData, 'month' => $currentDate->translatedFormat('F Y'), 'filterInfo' => $filterInfo]);
    return response()->streamDownload(fn() => print $pdf->output(), 'Laporan_Agenda_' . $currentDate->format('M_Y') . '.pdf');
};
?>

<div class="p-4 md:p-10 space-y-6 bg-slate-50 dark:bg-slate-950 min-h-screen">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
        <div class="flex flex-col md:flex-row md:items-center gap-6">
            <div>
                <h1
                    class="text-2xl md:text-3xl font-black uppercase italic text-indigo-600 tracking-tighter leading-none">
                    {{ Carbon::parse($targetDate)->translatedFormat('F Y') }}
                </h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">
                    Monitoring Agenda Kerja
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
                @if (auth()->user()->parent_id === null)
                    <div class="w-full sm:w-56">
                        <flux:select wire:model.live="filterUserId" placeholder="Pilih Anggota Tim">
                            <flux:select.option value="">Semua Anggota</flux:select.option>
                            @foreach ($this->subordinates as $sub)
                                <flux:select.option value="{{ $sub->id }}">{{ $sub->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                @endif
                <div class="w-full sm:w-64">
                    <flux:input wire:model.live.debounce.500ms="search" icon="magnifying-glass"
                        placeholder="Cari agenda atau PIC..." />
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between md:justify-end gap-3 w-full lg:w-auto">
            <flux:button variant="ghost" size="sm" icon="printer" wire:click="exportPdf"
                class="text-red-600 font-bold uppercase">PDF</flux:button>

            <div
                class="flex items-center gap-2 bg-white dark:bg-slate-900 p-1 rounded-xl md:rounded-2xl border border-slate-200 shadow-sm">
                <flux:button variant="ghost" icon="chevron-left" wire:click="prevMonth" size="sm" />
                <div class="relative flex items-center">
                    <flux:button variant="ghost" wire:click="$set('targetDate', '{{ now()->toDateTimeString() }}')"
                        class="text-[9px] md:text-[10px] font-black uppercase px-2 md:px-4 border-r border-slate-100 rounded-none">
                        Hari Ini
                    </flux:button>
                    <button onclick="document.getElementById('manualMonthPicker').showPicker()" class="px-2">
                        <flux:icon name="calendar" variant="mini" class="w-4 h-4 text-slate-400" />
                    </button>
                    <input type="month" id="manualMonthPicker" class="absolute inset-0 opacity-0 -z-10"
                        wire:change="goToMonth($event.target.value)"
                        value="{{ Carbon::parse($targetDate)->format('Y-m') }}">
                </div>
                <flux:button variant="ghost" icon="chevron-right" wire:click="nextMonth" size="sm" />
            </div>
        </div>
    </div>

    {{-- Kalender Grid --}}
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl md:rounded-[3rem] border border-slate-200 overflow-hidden shadow-2xl">
        {{-- Nama Hari (Hanya muncul di Desktop) --}}
        <div
            class="hidden md:grid grid-cols-7 bg-slate-50/50 border-b border-slate-100 font-black uppercase text-[10px] text-slate-400">
            @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayName)
                <div class="py-4 text-center tracking-widest">{{ $dayName }}</div>
            @endforeach
        </div>

        {{-- Grid Hari --}}
        <div class="grid grid-cols-1 md:grid-cols-7 gap-px bg-slate-100 dark:bg-slate-800">
            @foreach ($this->calendarDays as $day)
                {{-- Di Mobile, sembunyikan tanggal dari bulan lain agar tidak panjang --}}
                <div
                    class="bg-white dark:bg-slate-900 min-h-0 md:min-h-[160px] p-3
                    {{ !$day['isCurrentMonth'] ? 'hidden md:block opacity-25 grayscale' : '' }}">

                    <div class="flex items-center md:items-start justify-between md:mb-3">
                        <span
                            class="text-xs font-black p-2 {{ $day['isToday'] ? 'bg-indigo-600 text-white rounded-xl shadow-lg' : 'text-slate-400' }}">
                            {{ $day['date']->format('j') }}
                        </span>
                        {{-- Indikator hari di Mobile --}}
                        <span class="md:hidden text-[10px] font-bold text-slate-400 uppercase">
                            {{ $day['date']->translatedFormat('l') }}
                        </span>
                    </div>

                    <div class="space-y-2 mt-2 md:mt-0">
                        @forelse ($day['agendas'] as $agenda)
                            {{-- ... logika agenda (sama seperti kode Anda) ... --}}
                            @php
                                $totalSteps = $agenda->steps->count();
                                $currentRef = $day['date']->startOfDay();
                                $doneCount =
                                    $totalSteps > 0
                                        ? $agenda->steps
                                            ->filter(
                                                fn($s) => $s->completed_at &&
                                                    Carbon::parse($s->completed_at)
                                                        ->startOfDay()
                                                        ->lessThanOrEqualTo($currentRef),
                                            )
                                            ->count()
                                        : 0;
                                $percent =
                                    $totalSteps > 0
                                        ? round(($doneCount / $totalSteps) * 100)
                                        : ($agenda->status === 'completed'
                                            ? 100
                                            : 0);
                                $isFullyDone = $agenda->status === 'completed' && $percent == 100;
                            @endphp
                            <button wire:click="showDetail({{ $agenda->id }})"
                                class="w-full text-left p-3 rounded-2xl border transition-all active:scale-95 {{ $isFullyDone ? 'bg-emerald-50 border-emerald-100 text-emerald-800' : 'bg-white border-slate-100 text-slate-600 shadow-sm' }}">
                                <div class="flex justify-between items-start mb-1">
                                    <span
                                        class="text-[7px] font-black uppercase opacity-60 truncate max-w-[70%]">{{ $agenda->user->name }}</span>
                                    <span
                                        class="text-[7px] font-black {{ $percent == 100 ? 'text-emerald-600' : 'text-indigo-600' }}">{{ $percent }}%</span>
                                </div>
                                <div
                                    class="text-[10px] md:text-[9px] font-black leading-tight {{ $isFullyDone ? 'line-through opacity-50' : '' }}">
                                    {{ Str::limit($agenda->title, 40) }}
                                </div>
                            </button>
                        @empty
                            {{-- Di mobile, beri keterangan kosong jika perlu, atau biarkan ringkas --}}
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Modal Detail --}}
    <flux:modal name="detail-agenda" class="w-full max-w-lg !rounded-3xl md:!rounded-[3rem]">
        {{-- Gunakan max-w-lg dan w-full agar modal fleksibel di HP --}}
        @if ($this->selectedAgenda)
            <div class="space-y-6">
                <div
                    class="p-6 md:p-8 rounded-[2rem] md:rounded-[3rem] text-white shadow-2xl {{ $this->selectedAgenda->status === 'completed' ? 'bg-emerald-600' : 'bg-indigo-600' }}">
                    <h2 class="text-xl md:text-3xl font-black uppercase italic leading-tight tracking-tighter">
                        {{ $this->selectedAgenda->title }}
                    </h2>
                    <p class="text-[10px] mt-4 uppercase font-bold opacity-80">PIC:
                        {{ $this->selectedAgenda->user->name }}</p>
                </div>

                <div class="px-2 md:px-4 space-y-3 max-h-[50vh] overflow-y-auto">
                    {{-- Tambahkan max-h agar modal tidak melebihi tinggi layar HP --}}
                    @foreach ($this->selectedAgenda->steps as $step)
                        <div
                            class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100 text-sm">
                            <div class="flex items-center gap-3">
                                <flux:icon name="{{ $step->is_completed ? 'check-circle' : 'minus-circle' }}"
                                    variant="mini"
                                    class="w-5 h-5 {{ $step->is_completed ? 'text-emerald-500' : 'text-slate-300' }}" />
                                <div>
                                    <div class="font-bold text-slate-700 leading-snug">{{ $step->step_name }}</div>
                                    @if ($step->duration)
                                        <div class="text-[9px] text-indigo-600 font-bold uppercase mt-1">Estimasi:
                                            {{ $step->duration }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-4">
                    <flux:modal.close class="w-full">
                        <flux:button class="w-full !rounded-2xl font-black uppercase italic">Tutup</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
