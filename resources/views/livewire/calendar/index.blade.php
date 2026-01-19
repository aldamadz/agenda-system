<?php

use App\Models\Agenda;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Str;
use function Livewire\Volt\{layout, title, state, computed};

// Set locale Indonesia secara global untuk Carbon
Carbon::setLocale('id');

layout('components.layouts.app');
title('Monitoring Progres Tim');

state([
    'targetDate' => fn() => now()->startOfMonth()->toDateTimeString(),
    'selectedAgendaId' => null,
    'filterUserId' => '',
    'search' => '',
]);

// Logika Hak Akses: Admin bisa lihat semua, User hanya tim/bawahan
$allowedUserIds = computed(function () {
    $user = auth()->user();
    if (!$user) {
        return collect();
    }

    // JIKA ADMIN: Bisa melihat semua ID user
    if ($user->role === 'admin') {
        return User::pluck('id');
    }

    // JIKA USER BIASA: Pakai logika hirarki (bawahan & rekan tim)
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

// Daftar user untuk Dropdown Filter
$subordinates = computed(function () {
    $user = auth()->user();
    if (!$user) {
        return collect();
    }

    // Admin bisa memfilter siapa saja
    if ($user->role === 'admin') {
        return User::where('id', '!=', $user->id)->orderBy('name')->get();
    }

    // Atasan hanya bisa memfilter bawahannya
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
            $q->where('title', 'like', '%' . $this->search . '%')->orWhereHas('user', fn($qu) => $qu->where('name', 'like', '%' . $this->search . '%'));
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
    // Logika PDF Anda di sini...
};
?>

<div class="p-4 md:p-10 space-y-6 bg-slate-50 dark:bg-slate-950 min-h-screen">
    {{-- Header Section --}}
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
        <div class="flex flex-col md:flex-row md:items-center gap-6">
            <div>
                <h1
                    class="text-2xl md:text-4xl font-black uppercase italic text-indigo-600 tracking-tighter leading-none">
                    {{ Carbon::parse($targetDate)->translatedFormat('F Y') }}
                </h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Monitoring Agenda Kerja
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
                {{-- Dropdown Filter: Tampil jika Admin atau Atasan --}}
                @if (auth()->user()->role === 'admin' || auth()->user()->parent_id === null)
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
                class="text-red-600 font-bold uppercase">Cetak PDF</flux:button>
            <div
                class="flex items-center gap-2 bg-white dark:bg-slate-900 p-1.5 rounded-2xl border border-slate-200 shadow-sm">
                <flux:button variant="ghost" icon="chevron-left" wire:click="prevMonth" size="sm" />
                <div class="relative flex items-center group">
                    <flux:button variant="ghost" wire:click="$set('targetDate', '{{ now()->toDateTimeString() }}')"
                        class="text-[10px] font-black uppercase px-4 border-r border-slate-100 rounded-none">Hari Ini
                    </flux:button>
                    <button onclick="document.getElementById('manualMonthPicker').showPicker()"
                        class="px-2 hover:bg-slate-50 transition-colors rounded-r-xl">
                        <flux:icon name="calendar" variant="mini"
                            class="w-4 h-4 text-slate-400 group-hover:text-indigo-600" />
                    </button>
                    <input type="month" id="manualMonthPicker" class="absolute inset-0 opacity-0 -z-10 cursor-pointer"
                        wire:change="goToMonth($event.target.value)"
                        value="{{ Carbon::parse($targetDate)->format('Y-m') }}">
                </div>
                <flux:button variant="ghost" icon="chevron-right" wire:click="nextMonth" size="sm" />
            </div>
        </div>
    </div>

    {{-- Kalender Grid --}}
    <div
        class="bg-white dark:bg-slate-900 rounded-[2rem] md:rounded-[3rem] border border-slate-200 overflow-hidden shadow-2xl">
        {{-- Nama Hari Desktop --}}
        <div
            class="hidden md:grid grid-cols-7 bg-slate-50/50 border-b border-slate-100 font-black uppercase text-[10px] text-slate-400">
            @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $dayName)
                <div class="py-4 text-center tracking-widest">{{ $dayName }}</div>
            @endforeach
        </div>

        {{-- Grid Hari --}}
        <div class="grid grid-cols-1 md:grid-cols-7 gap-px bg-slate-100 dark:bg-slate-800">
            @foreach ($this->calendarDays as $day)
                <div
                    class="bg-white dark:bg-slate-900 min-h-0 md:min-h-[160px] p-3 {{ !$day['isCurrentMonth'] ? 'hidden md:block opacity-25 grayscale' : '' }}">
                    <div class="flex items-center md:items-start justify-between mb-2">
                        <span
                            class="text-xs font-black p-2 {{ $day['isToday'] ? 'bg-indigo-600 text-white rounded-xl shadow-lg' : 'text-slate-400' }}">
                            {{ $day['date']->format('j') }}
                        </span>
                        <span class="md:hidden text-[10px] font-black text-indigo-600/50 uppercase">
                            {{ $day['date']->translatedFormat('l') }}
                        </span>
                    </div>

                    <div class="space-y-2">
                        @foreach ($day['agendas'] as $agenda)
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
                                class="w-full text-left p-3 rounded-2xl border transition-all hover:scale-[1.02] active:scale-95 {{ $isFullyDone ? 'bg-emerald-50 border-emerald-100 text-emerald-800' : 'bg-white border-slate-100 text-slate-600 shadow-sm' }}">
                                <div class="flex justify-between items-start mb-1.5">
                                    <span
                                        class="text-[8px] font-black uppercase opacity-60 truncate max-w-[70%]">{{ $agenda->user->name }}</span>
                                    <div
                                        class="text-[8px] font-black {{ $percent == 100 ? 'text-emerald-600' : 'text-indigo-600' }}">
                                        {{ $percent }}%</div>
                                </div>
                                <div
                                    class="text-[11px] md:text-[9px] font-bold leading-tight {{ $isFullyDone ? 'line-through opacity-50' : '' }}">
                                    {{ Str::limit($agenda->title, 35) }}
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Modal Detail --}}
    {{-- Modal Detail Optimasi --}}
    <flux:modal name="detail-agenda" class="w-full max-w-2xl !rounded-[2rem] md:!rounded-[3rem] overflow-hidden">
        @if ($this->selectedAgenda)
            @php
                $agenda = $this->selectedAgenda;
                $totalSteps = $agenda->steps->count();
                $doneSteps = $agenda->steps->where('is_completed', true)->count();
                $percent = $totalSteps > 0 ? round(($doneSteps / $totalSteps) * 100) : 0;
                $isCompleted = $agenda->status === 'completed';
            @endphp

            <div class="flex flex-col">
                {{-- Header Visual --}}
                <div class="relative p-6 md:p-10 text-white {{ $isCompleted ? 'bg-emerald-600' : 'bg-indigo-600' }}">
                    <div class="relative z-10">
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <span
                                class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-black uppercase tracking-widest">
                                {{ $agenda->status }}
                            </span>
                            @if ($agenda->deadline)
                                <span
                                    class="px-3 py-1 bg-black/10 backdrop-blur-md rounded-full text-[10px] font-black uppercase tracking-widest">
                                    Deadline: {{ \Carbon\Carbon::parse($agenda->deadline)->translatedFormat('d M Y') }}
                                </span>
                            @endif
                        </div>

                        <h2 class="text-2xl md:text-4xl font-black uppercase italic leading-none tracking-tighter mb-2">
                            {{ $agenda->title }}
                        </h2>

                        <div class="flex items-center gap-3 mt-6">
                            <div
                                class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center font-black text-sm border border-white/30">
                                {{ substr($agenda->user->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold opacity-70 leading-none">Person In Charge</p>
                                <p class="text-sm font-bold">{{ $agenda->user->name }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Background Dekoratif --}}
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <flux:icon name="clipboard-document-check" class="w-32 h-32" />
                    </div>
                </div>

                <div class="p-6 md:p-8 bg-white dark:bg-slate-900">
                    {{-- Progress Stats --}}
                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div
                            class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                            <p class="text-[9px] font-black text-slate-400 uppercase mb-1">Progres Kerja</p>
                            <div class="flex items-end gap-2">
                                <span class="text-2xl font-black text-indigo-600">{{ $percent }}%</span>
                                <span class="text-[10px] font-bold text-slate-400 mb-1">Selesai</span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full mt-2 overflow-hidden">
                                <div class="bg-indigo-600 h-full transition-all duration-500"
                                    style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                        <div
                            class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                            <p class="text-[9px] font-black text-slate-400 uppercase mb-1">Total Langkah</p>
                            <div class="flex items-end gap-2">
                                <span
                                    class="text-2xl font-black text-slate-700 dark:text-white">{{ $doneSteps }}<span
                                        class="text-slate-300 mx-1">/</span>{{ $totalSteps }}</span>
                            </div>
                            <p class="text-[10px] font-bold text-slate-400 mt-2 italic">Langkah Kerja</p>
                        </div>
                    </div>

                    {{-- Steps List --}}
                    <div class="space-y-3 max-h-[40vh] overflow-y-auto pr-2 custom-scrollbar">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Daftar Rencana
                            Aksi</h3>

                        @forelse ($agenda->steps as $step)
                            <div
                                class="group flex items-center justify-between p-4 rounded-2xl border transition-all {{ $step->is_completed ? 'bg-emerald-50/50 border-emerald-100' : 'bg-white dark:bg-slate-800 border-slate-100 dark:border-slate-700' }}">
                                <div class="flex items-center gap-4">
                                    <div class="relative">
                                        <flux:icon name="{{ $step->is_completed ? 'check-circle' : 'circle' }}"
                                            variant="mini"
                                            class="w-6 h-6 {{ $step->is_completed ? 'text-emerald-500' : 'text-slate-300' }}" />
                                        @if (!$loop->last)
                                            <div class="absolute top-6 left-3 w-px h-8 bg-slate-100"></div>
                                        @endif
                                    </div>
                                    <div>
                                        <div
                                            class="text-sm font-bold {{ $step->is_completed ? 'text-emerald-900 dark:text-emerald-400 line-through' : 'text-slate-700 dark:text-slate-200' }}">
                                            {{ $step->step_name }}
                                        </div>
                                        <div class="flex items-center gap-3 mt-1">
                                            @if ($step->duration)
                                                <span
                                                    class="text-[9px] font-black uppercase text-indigo-500 bg-indigo-50 px-2 py-0.5 rounded">
                                                    Estimasi: {{ $step->duration }}
                                                </span>
                                            @endif
                                            @if ($step->completed_at)
                                                <span class="text-[9px] font-bold text-slate-400">
                                                    Selesai:
                                                    {{ \Carbon\Carbon::parse($step->completed_at)->translatedFormat('H:i') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10">
                                <p class="text-sm text-slate-400 italic">Belum ada langkah kerja yang didefinisikan.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Footer --}}
                <div
                    class="p-6 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex gap-3">
                    <flux:modal.close class="w-full">
                        <flux:button variant="filled"
                            class="w-full !rounded-xl font-black uppercase italic py-3 shadow-lg shadow-indigo-200 dark:shadow-none">
                            Tutup Monitoring
                        </flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
