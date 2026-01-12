<?php

use App\Models\Agenda;
use App\Models\AgendaStep;
use Carbon\Carbon;
use function Livewire\Volt\{computed, state};

state([
    'editingDeadlineId' => null,
    'tempDeadline' => '',
    'rejectionNote' => '',
    'showRejectModal' => null,
]);

$formatDurationToString = function ($totalMinutes) {
    $totalMinutes = max(1, (int) round($totalMinutes));
    $days = floor($totalMinutes / 1440);
    $hours = floor(($totalMinutes % 1440) / 60);
    $minutes = $totalMinutes % 60;

    $res = [];
    if ($days > 0) {
        $res[] = "{$days}h";
    }
    if ($hours > 0) {
        $res[] = "{$hours}j";
    }
    if ($minutes > 0 || empty($res)) {
        $res[] = "{$minutes}m";
    }

    return (string) implode(' ', $res);
};

$pendingAgendas = computed(
    fn() => Agenda::with(['user', 'steps'])
        ->where('approver_id', auth()->id())
        ->where('status', 'pending')
        ->latest()
        ->get(),
);

$editDeadline = function ($id, $currentDeadline) {
    $this->editingDeadlineId = $id;
    $this->tempDeadline = Carbon::parse($currentDeadline)->format('Y-m-d\TH:i');
};

$updateDeadline = function ($id) use ($formatDurationToString) {
    $agenda = Agenda::with('steps')->findOrFail($id);
    $newDeadline = Carbon::parse($this->tempDeadline);
    $startTime = $agenda->created_at;

    $newTotalMinutes = $startTime->diffInMinutes($newDeadline, false);
    if ($newTotalMinutes <= 0) {
        $this->dispatch('swal', ['icon' => 'error', 'title' => 'Gagal', 'text' => 'Deadline tidak boleh kurang dari waktu buat.']);
        return;
    }

    $oldTotalMinutes = $startTime->diffInMinutes($agenda->getOriginal('deadline')) ?: 1;
    $agenda->update(['deadline' => $this->tempDeadline]);

    $accumulatedMinutes = 0;
    $steps = $agenda->steps->sortBy('id')->values();

    foreach ($steps as $index => $step) {
        $prevDeadline = $index === 0 ? $startTime : Carbon::parse($steps[$index - 1]->getOriginal('deadline'));
        $stepOldMinutes = $prevDeadline->diffInMinutes(Carbon::parse($step->getOriginal('deadline'))) ?: 1;

        $ratio = $stepOldMinutes / $oldTotalMinutes;
        $newStepMinutes = $ratio * $newTotalMinutes;
        $accumulatedMinutes += $newStepMinutes;

        $step->update([
            'duration' => $formatDurationToString($newStepMinutes),
            'deadline' => $startTime->copy()->addMinutes(round($accumulatedMinutes)),
        ]);
    }

    $this->editingDeadlineId = null;
    $this->dispatch('swal', ['icon' => 'success', 'title' => 'Berhasil', 'text' => 'Deadline telah disesuaikan secara proporsional.']);
};

$approve = function ($id) {
    Agenda::where('id', $id)->update(['status' => 'ongoing']);
    $this->dispatch('refresh-stats');
};

$confirmReject = fn($id) => ($this->showRejectModal = $id);

$submitReject = function () {
    if (!$this->rejectionNote) {
        $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Catatan diperlukan']);
        return;
    }

    // FIXED: Menggunakan 'manager_note' sesuai kolom database Anda
    Agenda::where('id', $this->showRejectModal)->update([
        'status' => 'rejected',
        'manager_note' => $this->rejectionNote,
    ]);

    $this->showRejectModal = null;
    $this->rejectionNote = '';
    $this->dispatch('refresh-stats');
};
?>

<div>
    {{-- Header Section --}}
    <header class="max-w-7xl mx-auto mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                Persetujuan <span class="text-indigo-600 dark:text-indigo-400">Agenda</span>
            </h1>
            <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg">
                Tinjau dan kelola jadwal yang memerlukan otorisasi Anda.
            </p>
        </div>
        <div
            class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-900 rounded-full border border-slate-200 dark:border-slate-800 shadow-sm">
            <span class="relative flex h-3 w-3">
                <span
                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-500"></span>
            </span>
            <span class="text-sm font-medium text-slate-600 dark:text-slate-300">
                {{ count($this->pendingAgendas) }} Menunggu
            </span>
        </div>
    </header>

    {{-- Grid Content --}}
    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($this->pendingAgendas as $agenda)
            <div
                class="group bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col">
                <div class="p-6 space-y-6 flex-1">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <div
                                class="size-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl text-white flex items-center justify-center font-bold text-lg shadow-lg">
                                {{ substr($agenda->user->name, 0, 1) }}
                            </div>
                            <div
                                class="absolute -bottom-1 -right-1 size-4 bg-green-500 border-2 border-white dark:border-slate-900 rounded-full">
                            </div>
                        </div>
                        <div>
                            <h3
                                class="font-bold text-slate-900 dark:text-white leading-tight group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                {{ $agenda->title }}
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Diusulkan oleh <span
                                    class="font-medium text-slate-700 dark:text-slate-200">{{ $agenda->user->name }}</span>
                            </p>
                        </div>
                    </div>

                    {{-- Deadline Highlight --}}
                    <div class="relative overflow-hidden bg-slate-900 dark:bg-indigo-950/40 rounded-2xl p-5 text-white">
                        <div class="relative z-10">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[10px] uppercase tracking-widest text-indigo-300 font-bold">Target
                                    Deadline</span>
                                @if ($editingDeadlineId !== $agenda->id)
                                    <button wire:click="editDeadline({{ $agenda->id }}, '{{ $agenda->deadline }}')"
                                        class="text-[10px] bg-white/10 hover:bg-white/20 px-2 py-1 rounded transition">Ubah</button>
                                @endif
                            </div>

                            @if ($editingDeadlineId === $agenda->id)
                                <div class="space-y-3 animate-in fade-in slide-in-from-top-2">
                                    <input type="datetime-local" wire:model="tempDeadline"
                                        class="w-full bg-white/10 border-white/20 rounded-lg text-sm text-white focus:ring-indigo-500 focus:border-indigo-500" />
                                    <div class="flex gap-2">
                                        <button wire:click="updateDeadline({{ $agenda->id }})"
                                            class="flex-1 bg-white text-indigo-900 text-xs font-bold py-2 rounded-lg">Simpan</button>
                                        <button wire:click="$set('editingDeadlineId', null)"
                                            class="px-3 bg-red-500/20 text-red-200 text-xs py-2 rounded-lg">Batal</button>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-end justify-between">
                                    <div>
                                        <p class="text-2xl font-black">
                                            {{ $agenda->deadline->translatedFormat('d M Y') }}</p>
                                        <p class="text-indigo-300 text-sm font-medium">
                                            {{ $agenda->deadline->format('H:i') }} WIB</p>
                                    </div>
                                    <svg class="size-8 text-white/10" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.4 0-8-3.6-8-8s3.6-8 8-8 8 3.6 8 8-3.6 8-8 8zm.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7V7z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Steps --}}
                    @if (count($agenda->steps) > 0)
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-tighter">Tahapan
                                    Pengerjaan</p>
                                <span
                                    class="text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded-full uppercase font-bold">{{ count($agenda->steps) }}
                                    Steps</span>
                            </div>
                            <div class="space-y-2 max-h-[200px] overflow-y-auto pr-1 custom-scrollbar">
                                @foreach ($agenda->steps as $step)
                                    <div
                                        class="group/step flex justify-between items-center bg-slate-50 dark:bg-slate-800/50 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 border border-slate-100 dark:border-slate-800 rounded-xl px-4 py-3 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="size-2 rounded-full bg-slate-300 dark:bg-slate-600 group-hover/step:bg-indigo-500 transition-colors">
                                            </div>
                                            <div>
                                                <p
                                                    class="text-sm font-semibold text-slate-700 dark:text-slate-200 leading-none mb-1">
                                                    {{ $step->step_name }}</p>
                                                <p class="text-[10px] text-slate-400 font-medium tracking-tight">Hingga
                                                    {{ $step->deadline->format('H:i') }}</p>
                                            </div>
                                        </div>
                                        <span
                                            class="text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-100 dark:bg-indigo-900/30 px-2 py-1 rounded-md">{{ $step->duration }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="p-6 pt-0 flex gap-3">
                    <button wire:click="approve({{ $agenda->id }})"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white rounded-2xl py-3.5 font-bold shadow-lg shadow-indigo-200 dark:shadow-none transition-all flex items-center justify-center gap-2">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        Setujui
                    </button>
                    <button wire:click="confirmReject({{ $agenda->id }})"
                        class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-red-50 hover:text-red-600 hover:border-red-200 dark:hover:bg-red-950/30 dark:hover:text-red-400 rounded-2xl py-3.5 font-bold transition-all">
                        Tolak
                    </button>
                </div>
            </div>
        @empty
            <div
                class="col-span-full flex flex-col items-center justify-center py-24 bg-white dark:bg-slate-900 rounded-[3rem] border-2 border-dashed border-slate-200 dark:border-slate-800">
                <div
                    class="size-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4 text-slate-400">
                    <svg class="size-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Semua Terkendali!</h3>
                <p class="text-slate-500">Tidak ada agenda yang perlu diperiksa saat ini.</p>
            </div>
        @endforelse
    </div>

    {{-- Reject Modal --}}
    @if ($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm animate-in fade-in duration-300"
                wire:click="$set('showRejectModal', null)"></div>

            <div
                class="relative bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl w-full max-w-md overflow-hidden animate-in zoom-in-95 duration-200 border border-slate-200 dark:border-slate-800">
                <div class="p-8">
                    <div
                        class="size-14 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="size-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Konfirmasi Penolakan</h3>
                    <p class="text-slate-500 dark:text-slate-400 mb-6 text-sm">Berikan alasan mengapa agenda ini ditolak
                        agar pemohon dapat melakukan perbaikan.</p>

                    <div class="mb-8">
                        {{-- FIXED: Perbaikan warna background dan text pada dark mode --}}
                        <textarea wire:model="rejectionNote" rows="4"
                            class="w-full bg-slate-100 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl p-4 text-slate-900 dark:text-white focus:ring-red-500 focus:border-red-500 placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                            placeholder="Contoh: Jadwal bertabrakan dengan meeting internal..."></textarea>
                    </div>

                    <div class="flex gap-3">
                        <button wire:click="$set('showRejectModal', null)"
                            class="flex-1 px-6 py-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold rounded-2xl transition-colors">Batal</button>
                        <button wire:click="submitReject"
                            class="flex-1 px-6 py-4 bg-red-600 hover:bg-red-700 text-white font-bold rounded-2xl shadow-lg shadow-red-200 dark:shadow-none transition-all active:scale-95">Ya,
                            Tolak</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #334155;
        }
    </style>
</div>
