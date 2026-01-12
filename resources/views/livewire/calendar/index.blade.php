<?php

use App\Models\Agenda;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use function Livewire\Volt\{layout, title, state, computed};

layout('components.layouts.app');
title('Kalender Agenda & Progres');

state([
    'targetDate' => fn() => now(),
    'selectedAgendaId' => null,
    'showCompleted' => true,
]);

// Fungsi internal untuk mengambil data berdasarkan state saat ini
// Kita melewatkan $targetDate dan $showCompleted sebagai parameter untuk menghindari error $this
$fetchAgendasForExport = function ($targetDate, $showCompleted) {
    $user = auth()->user();
    $allowedUserIds = collect([$user->id]);

    if ($user->parent_id === null) {
        $subordinateIds = User::where('parent_id', $user->id)->pluck('id');
        $allowedUserIds = $allowedUserIds->merge($subordinateIds);
    } else {
        $teamMemberIds = User::where('parent_id', $user->parent_id)->pluck('id');
        $allowedUserIds = $allowedUserIds->merge($teamMemberIds)->push($user->parent_id);
    }

    $statuses = $showCompleted ? ['ongoing', 'completed'] : ['ongoing'];

    return Agenda::whereMonth('deadline', $targetDate->month)->whereYear('deadline', $targetDate->year)->whereIn('status', $statuses)->whereIn('user_id', $allowedUserIds->unique())->with('user')->orderBy('deadline', 'asc')->get();
};

$exportExcel = function () use ($fetchAgendasForExport) {
    // Mengambil data dari state melalui context komponen
    $data = $fetchAgendasForExport($this->targetDate, $this->showCompleted);
    $filename = 'Agenda_' . $this->targetDate->format('F_Y') . '.xlsx';

    return Excel::download(
        new class ($data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function __construct(protected $data) {}
            public function collection()
            {
                return $this->data->map(fn($a) => [$a->deadline->format('d/m/Y H:i'), $a->user->name, $a->title, $a->status]);
            }
            public function headings(): array
            {
                return ['Deadline', 'PIC', 'Agenda', 'Status'];
            }
        },
        $filename,
    );
};

$exportPdf = function () use ($fetchAgendasForExport) {
    $data = $fetchAgendasForExport($this->targetDate, $this->showCompleted);
    $pdf = Pdf::loadHTML(
        view('pdf.agenda-report', [
            'data' => $data,
            'month' => $this->targetDate->translatedFormat('F Y'),
        ])->render(),
    )->setPaper('a4', 'portrait');

    $filename = 'Agenda_' . $this->targetDate->format('F_Y') . '.pdf';
    return response()->streamDownload(fn() => print $pdf->output(), $filename);
};

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
    $targetDate = $this->targetDate;
    $showCompleted = $this->showCompleted;

    $start = $targetDate->copy()->startOfMonth()->startOfWeek(\Carbon\CarbonInterface::MONDAY);
    $end = $targetDate->copy()->endOfMonth()->endOfWeek(\Carbon\CarbonInterface::SUNDAY);

    $user = auth()->user();
    $allowedUserIds = collect([$user->id]);
    if ($user->parent_id === null) {
        $allowedUserIds = $allowedUserIds->merge(User::where('parent_id', $user->id)->pluck('id'));
    } else {
        $allowedUserIds = $allowedUserIds->merge(User::where('parent_id', $user->parent_id)->pluck('id'))->push($user->parent_id);
    }

    $statuses = $showCompleted ? ['ongoing', 'completed'] : ['ongoing'];
    $allAgendas = Agenda::whereMonth('deadline', $targetDate->month)->whereYear('deadline', $targetDate->year)->whereIn('status', $statuses)->whereIn('user_id', $allowedUserIds->unique())->with('user')->get();

    $days = [];
    $current = $start->copy();
    while ($current <= $end) {
        $days[] = [
            'date' => $current->copy(),
            'isCurrentMonth' => $current->month === $targetDate->month,
            'isToday' => $current->isToday(),
            'agendas' => $allAgendas->filter(fn($a) => $a->deadline->isSameDay($current)),
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
            <div class="flex items-center gap-4">
                <flux:subheading class="flex items-center gap-2">
                    <flux:icon name="information-circle" variant="mini" class="text-indigo-500" />
                    Monitoring agenda tim aktif.
                </flux:subheading>
                <div
                    class="flex items-center gap-2 px-3 py-1 bg-white dark:bg-zinc-900 rounded-full border border-zinc-200 dark:border-zinc-800 shadow-sm">
                    <span
                        class="text-[9px] font-black uppercase tracking-widest {{ $showCompleted ? 'text-indigo-600' : 'text-zinc-400' }}">Selesai</span>
                    <flux:switch wire:model.live="showCompleted" variant="primary" size="sm" />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div
                class="flex items-center gap-1 bg-white dark:bg-zinc-900 p-1.5 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-800">
                <flux:button variant="ghost" size="sm" icon="document-text" wire:click="exportExcel"
                    class="text-emerald-600 dark:text-emerald-400 font-bold">EXCEL</flux:button>
                <flux:button variant="ghost" size="sm" icon="document-arrow-down" wire:click="exportPdf"
                    class="text-rose-600 dark:text-rose-400 font-bold">PDF</flux:button>
            </div>
            <div
                class="flex items-center gap-2 bg-white dark:bg-zinc-900 p-1.5 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-800">
                <flux:button variant="ghost" icon="chevron-left" wire:click="prevMonth" class="rounded-xl" />
                <flux:button variant="ghost" wire:click="targetDate = now()"
                    class="text-[10px] font-black uppercase tracking-widest px-4">Hari Ini</flux:button>
                <flux:button variant="ghost" icon="chevron-right" wire:click="nextMonth" class="rounded-xl" />
            </div>
        </div>
    </div>

    {{-- Calendar Grid --}}
    <div
        class="bg-white dark:bg-zinc-900 rounded-[2.5rem] border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-xl shadow-slate-200/50 dark:shadow-none">
        <div class="grid grid-cols-7 bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-100 dark:border-zinc-800">
            @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayName)
                <div
                    class="py-4 text-center text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500">
                    {{ $dayName }}</div>
            @endforeach
        </div>

        <div class="grid grid-cols-7 gap-px bg-zinc-100 dark:bg-zinc-800">
            @foreach ($this->getDays() as $day)
                <div
                    class="bg-white dark:bg-zinc-900 min-h-[180px] p-2 transition-colors {{ !$day['isCurrentMonth'] ? 'bg-zinc-50/30 dark:bg-zinc-950/20' : '' }}">
                    <div class="flex justify-start mb-2 p-1">
                        <span
                            class="text-xs font-black {{ $day['isToday'] ? 'bg-indigo-600 text-white w-7 h-7 flex items-center justify-center rounded-full shadow-lg shadow-indigo-500/30 ring-4 ring-indigo-50 dark:ring-indigo-900/30' : ($day['isCurrentMonth'] ? 'text-zinc-700 dark:text-zinc-300' : 'text-zinc-300 dark:text-zinc-700') }}">
                            {{ $day['date']->format('j') }}
                        </span>
                    </div>

                    <div class="space-y-4 max-h-[160px] overflow-y-auto pr-1 custom-scrollbar">
                        @foreach ($day['agendas']->groupBy('user.name') as $userName => $agendas)
                            <div class="space-y-1.5">
                                <div
                                    class="flex items-center gap-1.5 px-1 sticky top-0 bg-white dark:bg-zinc-900 z-10 py-0.5">
                                    <div
                                        class="w-1 h-3 bg-indigo-500 rounded-full shadow-[0_0_8px_rgba(99,102,241,0.5)]">
                                    </div>
                                    <span
                                        class="text-[9px] font-black uppercase italic tracking-wider text-indigo-600 dark:text-indigo-400 truncate">{{ $userName }}</span>
                                </div>
                                <div class="space-y-1 ml-2">
                                    @foreach ($agendas as $agenda)
                                        @php
                                            $isCompleted = $agenda->status === 'completed';
                                            $isMine = $agenda->user_id === auth()->id();
                                        @endphp
                                        <button wire:click="showDetail({{ $agenda->id }})"
                                            class="group w-full text-left p-2 rounded-xl border transition-all duration-300 ease-out transform hover:-translate-y-0.5 hover:shadow-md focus:outline-none
                                            {{ $isCompleted ? 'bg-zinc-50 dark:bg-zinc-800/40 border-zinc-200 dark:border-zinc-700 opacity-70' : ($isMine ? 'bg-indigo-600 border-indigo-700 text-white' : 'bg-white dark:bg-zinc-800/60 border-zinc-100 dark:border-zinc-700 hover:border-indigo-400') }}
                                            dark:hover:text-white">
                                            <div class="flex items-start gap-1.5">
                                                <div
                                                    class="text-[10px] font-bold leading-tight transition-all duration-300
                                                    {{ $isCompleted ? 'text-zinc-400 line-through' : ($isMine ? 'text-white' : 'text-zinc-800 dark:text-zinc-200 group-hover:text-indigo-600 dark:group-hover:text-white') }}">
                                                    {{ $agenda->title }}
                                                </div>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
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
                <div
                    class="relative overflow-hidden p-6 rounded-[2rem] {{ $this->selectedAgenda->status === 'completed' ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-100' : 'bg-indigo-600 text-white' }}">
                    <div class="relative z-10 flex justify-between items-start">
                        <div class="space-y-2 max-w-[70%]">
                            <flux:badge color="{{ $this->selectedAgenda->status === 'completed' ? 'zinc' : 'white' }}"
                                size="sm" class="font-black uppercase italic rounded-lg">
                                {{ $this->selectedAgenda->status }}</flux:badge>
                            <h2 class="text-2xl font-black uppercase italic tracking-tighter leading-none">
                                {{ $this->selectedAgenda->title }}</h2>
                            <p class="text-[10px] opacity-70 font-medium uppercase tracking-widest">PIC:
                                {{ $this->selectedAgenda->user->name }}</p>
                        </div>
                        <flux:icon name="calendar-days" class="w-12 h-12 opacity-20" />
                    </div>
                </div>
                <div class="flex justify-end pt-4">
                    <flux:modal.close>
                        <flux:button variant="filled" color="indigo"
                            class="!rounded-xl font-black uppercase text-[10px] italic tracking-widest">Tutup
                        </flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
