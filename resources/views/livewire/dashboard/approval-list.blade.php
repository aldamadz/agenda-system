<?php

use App\Models\Agenda;
use App\Models\AgendaStep;
use App\Models\AgendaLog;
use Carbon\Carbon;
use function Livewire\Volt\{computed, state, layout, title};

layout('components.layouts.app');
title('Persetujuan Agenda Kerja');

state([
    'editingDeadlineId' => null,
    'tempDeadline' => '',
    'rejectionNote' => '',
    'showRejectModal' => null,
]);

/**
 * Format durasi menit ke string manusia (Contoh: 1h 30j 5m)
 */
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

/**
 * Mengambil agenda yang berstatus PENDING
 */
$pendingAgendas = computed(function () {
    $user = auth()->user();
    $query = Agenda::with(['user', 'approver', 'steps'])->where('status', 'pending');

    if (!$user->isAdmin()) {
        $query->where('approver_id', $user->id);
    }

    return $query->latest()->get();
});

/**
 * Fungsi untuk memulai mode edit deadline
 */
$editDeadline = function ($id, $currentDeadline) {
    $this->editingDeadlineId = $id;
    $this->tempDeadline = Carbon::parse($currentDeadline)->format('Y-m-d\TH:i');
};

/**
 * Update deadline secara proporsional ke semua tahapan
 */
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

    // Simpan Deadline Baru
    $agenda->update(['deadline' => $this->tempDeadline]);

    // Update Tahapan (Steps) secara proporsional
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

    // CATAT KE LOG: Perubahan Jadwal
    AgendaLog::create([
        'agenda_id' => $id,
        'log_date' => now()->toDateString(),
        'log_time' => now()->toTimeString(),
        'issue_category' => 'Perubahan Jadwal',
        'issue_description' => 'Deadline disesuaikan oleh ' . auth()->user()->name,
        'progress_note' => 'Batas waktu baru ditetapkan pada: ' . $newDeadline->format('d/m/Y H:i'),
    ]);

    $this->editingDeadlineId = null;
    $this->dispatch('swal', ['icon' => 'success', 'title' => 'Berhasil', 'text' => 'Jadwal telah disesuaikan secara proporsional.']);
};

/**
 * Menyetujui Agenda
 */
$approve = function ($id) {
    $agenda = Agenda::findOrFail($id);

    // Update Status
    $agenda->update(['status' => 'ongoing']);

    // CATAT KE LOG: Persetujuan
    AgendaLog::create([
        'agenda_id' => $id,
        'log_date' => now()->toDateString(),
        'log_time' => now()->toTimeString(),
        'issue_category' => 'Persetujuan',
        'issue_description' => 'Agenda disetujui oleh ' . auth()->user()->name,
        'progress_note' => 'Status beralih ke ONGOING. Staff pelaksana dapat memulai pekerjaan.',
    ]);

    $this->dispatch('refresh-stats');
    $this->dispatch('swal', ['icon' => 'success', 'title' => 'Disetujui', 'text' => 'Agenda telah aktif.']);
};

$confirmReject = fn($id) => ($this->showRejectModal = $id);

/**
 * Menolak Agenda
 */
$submitReject = function () {
    if (!$this->rejectionNote) {
        $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Catatan diperlukan']);
        return;
    }

    $agenda = Agenda::findOrFail($this->showRejectModal);

    // Update Status
    $agenda->update([
        'status' => 'rejected',
        'manager_note' => $this->rejectionNote,
    ]);

    // CATAT KE LOG: Penolakan
    AgendaLog::create([
        'agenda_id' => $this->showRejectModal,
        'log_date' => now()->toDateString(),
        'log_time' => now()->toTimeString(),
        'issue_category' => 'Penolakan',
        'issue_description' => 'Agenda dikembalikan oleh ' . auth()->user()->name,
        'progress_note' => 'Alasan Penolakan: ' . $this->rejectionNote,
    ]);

    $this->showRejectModal = null;
    $this->rejectionNote = '';
    $this->dispatch('refresh-stats');
    $this->dispatch('swal', ['icon' => 'info', 'title' => 'Ditolak', 'text' => 'Agenda telah dikembalikan ke staff.']);
};
?>

<div class="p-6 bg-slate-50 dark:bg-zinc-950 min-h-screen transition-colors duration-300" wire:poll.15s>
    {{-- Header Section --}}
    <header class="max-w-7xl mx-auto mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white uppercase leading-none">
                Persetujuan <span class="text-indigo-600 dark:text-indigo-500">Agenda</span>
                @if (auth()->user()->isAdmin())
                    <span
                        class="ml-3 text-[10px] bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 px-3 py-1.5 rounded-xl uppercase font-black border border-amber-200 dark:border-amber-800/50 align-middle">
                        Admin Mode
                    </span>
                @endif
            </h1>
            <p class="text-slate-500 dark:text-zinc-500 mt-4 text-sm font-bold uppercase tracking-widest italic">
                {{ auth()->user()->isAdmin() ? 'Otoritas monitoring antrean sistem secara menyeluruh.' : 'Tinjau dan validasi agenda kerja staff Anda.' }}
            </p>
        </div>
        <div
            class="flex items-center gap-4 px-6 py-4 bg-white dark:bg-zinc-900 rounded-[2rem] border border-slate-200 dark:border-zinc-800 shadow-sm">
            <div class="relative flex h-3 w-3">
                <span
                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-500"></span>
            </div>
            <span class="text-xs font-black text-slate-700 dark:text-zinc-300 uppercase tracking-widest">
                {{ count($this->pendingAgendas) }} Antrean Pending
            </span>
        </div>
    </header>

    {{-- Grid Content --}}
    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
        @forelse($this->pendingAgendas as $agenda)
            <div
                class="group bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-[3rem] overflow-hidden shadow-sm hover:border-indigo-500 transition-all duration-500 flex flex-col">
                <div class="p-8 flex-1 space-y-7">
                    {{-- Staff & Approver Info --}}
                    <div class="flex flex-col gap-5">
                        <div class="flex items-start justify-between">
                            <h3
                                class="font-black text-sm text-slate-900 dark:text-zinc-100 leading-tight uppercase tracking-tight group-hover:text-indigo-600 transition-colors line-clamp-2 pr-4">
                                {{ $agenda->title }}
                            </h3>
                            <div
                                class="size-10 flex-shrink-0 bg-indigo-50 dark:bg-indigo-500/10 rounded-2xl flex items-center justify-center">
                                <flux:icon.briefcase variant="mini"
                                    class="text-indigo-600 dark:text-indigo-400 size-5" />
                            </div>
                        </div>

                        <div
                            class="grid grid-cols-2 gap-3 p-4 bg-slate-50 dark:bg-black/20 rounded-[2rem] border border-slate-100 dark:border-zinc-800/50">
                            <div class="space-y-1.5 border-r border-slate-200 dark:border-zinc-800 pr-2">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Pelaksana</p>
                                <div class="flex items-center gap-2">
                                    <div
                                        class="size-5 rounded-lg bg-indigo-600 flex items-center justify-center text-[9px] text-white font-black">
                                        {{ substr($agenda->user->name, 0, 1) }}
                                    </div>
                                    <span
                                        class="text-[10px] font-black text-slate-700 dark:text-zinc-200 truncate">{{ $agenda->user->name }}</span>
                                </div>
                            </div>
                            <div class="space-y-1.5 pl-2">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Atasan</p>
                                <div class="flex items-center gap-2">
                                    <flux:icon.shield-check variant="mini" class="size-4 text-emerald-500" />
                                    <span
                                        class="text-[10px] font-black text-slate-700 dark:text-zinc-200 truncate">{{ $agenda->approver->name ?? 'System' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Deadline Area --}}
                    <div
                        class="relative overflow-hidden bg-slate-900 dark:bg-indigo-950 rounded-[2.5rem] p-6 text-white transition-all shadow-lg">
                        <div class="relative z-10">
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-[9px] uppercase font-black tracking-[0.3em] text-indigo-400">Target
                                    Selesai</span>
                                @if ($editingDeadlineId !== $agenda->id)
                                    <button wire:click="editDeadline({{ $agenda->id }}, '{{ $agenda->deadline }}')"
                                        class="text-[9px] bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-xl transition-all font-black uppercase tracking-widest">Adjust</button>
                                @endif
                            </div>

                            @if ($editingDeadlineId === $agenda->id)
                                <div class="space-y-4">
                                    <input type="datetime-local" wire:model="tempDeadline"
                                        class="w-full bg-white/10 border-white/20 rounded-2xl text-xs text-white focus:ring-indigo-500 py-3 px-4 outline-none color-scheme-dark" />
                                    <div class="flex gap-2">
                                        <button wire:click="updateDeadline({{ $agenda->id }})"
                                            class="flex-1 bg-white text-indigo-950 text-[10px] font-black py-3 rounded-2xl uppercase tracking-widest hover:bg-indigo-50 transition-colors">Update</button>
                                        <button wire:click="$set('editingDeadlineId', null)"
                                            class="px-5 bg-red-500/20 text-red-200 text-[10px] font-black py-3 rounded-2xl uppercase">Batal</button>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-end justify-between">
                                    <div>
                                        <p class="text-2xl font-black tracking-tighter">
                                            {{ $agenda->deadline->translatedFormat('d M Y') }}</p>
                                        <p
                                            class="text-indigo-400 text-[10px] font-black uppercase tracking-widest mt-1">
                                            {{ $agenda->deadline->format('H:i') }} WIB</p>
                                    </div>
                                    <flux:icon.clock variant="mini" class="size-10 text-white/5" />
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Steps Section --}}
                    @if (count($agenda->steps) > 0)
                        <div class="space-y-4">
                            <div class="flex items-center justify-between px-1">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tahapan
                                    Kerja</span>
                                <span
                                    class="text-[10px] font-black text-indigo-600 bg-indigo-50 dark:bg-indigo-500/10 px-3 py-1 rounded-xl border border-indigo-100 dark:border-indigo-500/20">
                                    {{ count($agenda->steps) }} STEPS
                                </span>
                            </div>
                            <div class="space-y-2.5 max-h-[160px] overflow-y-auto pr-2 custom-scrollbar">
                                @foreach ($agenda->steps as $step)
                                    <div
                                        class="flex justify-between items-center bg-slate-50 dark:bg-zinc-800/40 border border-slate-100 dark:border-zinc-800 rounded-2xl px-5 py-4 group/step hover:border-indigo-500/30 transition-colors">
                                        <div class="min-w-0">
                                            <p
                                                class="text-[11px] font-black text-slate-700 dark:text-zinc-200 truncate mb-1 uppercase tracking-tight">
                                                {{ $step->step_name }}
                                            </p>
                                            <p class="text-[9px] text-zinc-400 font-bold uppercase tracking-widest">
                                                {{ $step->deadline->format('H:i') }} WIB
                                            </p>
                                        </div>
                                        <span
                                            class="text-[9px] font-black text-indigo-600 dark:text-indigo-400 bg-white dark:bg-zinc-900 shadow-sm px-3 py-1.5 rounded-xl border border-indigo-50 dark:border-zinc-700">
                                            {{ $step->duration }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Action Area --}}
                <div class="p-8 pt-0 flex gap-4">
                    <button wire:click="approve({{ $agenda->id }})"
                        class="flex-1 bg-slate-900 dark:bg-indigo-600 hover:bg-indigo-700 dark:hover:bg-indigo-500 text-white rounded-[1.5rem] py-5 font-black shadow-xl shadow-slate-200 dark:shadow-none transition-all active:scale-95 uppercase text-[10px] tracking-widest flex items-center justify-center gap-2">
                        <flux:icon.check-circle variant="mini" class="size-4" />
                        Setujui
                    </button>
                    <button wire:click="confirmReject({{ $agenda->id }})"
                        class="flex-1 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 text-slate-400 hover:text-rose-600 hover:border-rose-200 dark:hover:bg-rose-950/20 rounded-[1.5rem] py-5 font-black transition-all uppercase text-[10px] tracking-widest">
                        Tolak
                    </button>
                </div>
            </div>
        @empty
            <div
                class="col-span-full flex flex-col items-center justify-center py-40 bg-white dark:bg-zinc-900 rounded-[4rem] border-4 border-dashed border-slate-100 dark:border-zinc-800">
                <div class="size-24 bg-slate-50 dark:bg-zinc-800 rounded-full flex items-center justify-center mb-8">
                    <flux:icon.check variant="solid" class="size-12 text-emerald-500" />
                </div>
                <h3 class="text-3xl font-black text-slate-900 dark:text-white mb-3 tracking-tight uppercase">Antrean
                    Bersih</h3>
                <p class="text-zinc-500 font-bold uppercase text-xs tracking-[0.2em]">Semua agenda telah diproses.</p>
            </div>
        @endforelse
    </div>

    {{-- Reject Modal --}}
    @if ($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-md animate-in fade-in duration-300"
                wire:click="$set('showRejectModal', null)"></div>
            <div
                class="relative bg-white dark:bg-zinc-900 rounded-[3.5rem] shadow-2xl w-full max-w-md p-12 border border-slate-200 dark:border-zinc-800 animate-in zoom-in-95 duration-200">
                <div
                    class="size-20 bg-rose-50 dark:bg-rose-500/10 text-rose-600 rounded-[2rem] flex items-center justify-center mb-8">
                    <flux:icon.exclamation-triangle variant="mini" class="size-10" />
                </div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-3 tracking-tight uppercase">Konfirmasi
                    Tolak</h3>
                <p class="text-zinc-500 mb-8 font-bold text-xs uppercase leading-relaxed tracking-wider">
                    Agenda akan dikembalikan ke staff. Berikan catatan perbaikan agar dapat segera direvisi.
                </p>

                <textarea wire:model="rejectionNote" rows="4"
                    class="w-full bg-slate-50 dark:bg-black/20 border-2 border-slate-100 dark:border-zinc-800 rounded-[2rem] p-6 text-sm text-slate-900 dark:text-white outline-none focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 transition-all mb-8 placeholder:font-black placeholder:uppercase placeholder:text-[9px] placeholder:tracking-widest"
                    placeholder="Contoh: Lampiran belum lengkap..."></textarea>

                <div class="flex gap-4">
                    <button wire:click="$set('showRejectModal', null)"
                        class="flex-1 py-5 bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400 font-black rounded-2xl uppercase text-[10px] tracking-widest transition-colors hover:bg-slate-200 dark:hover:bg-zinc-700">Batal</button>
                    <button wire:click="submitReject"
                        class="flex-1 py-5 bg-rose-600 hover:bg-rose-700 text-white font-black rounded-2xl shadow-xl shadow-rose-200 dark:shadow-none transition-all uppercase text-[10px] tracking-widest active:scale-95">Ya,
                        Tolak</button>
                </div>
            </div>
        </div>
    @endif

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #27272a;
        }

        .color-scheme-dark {
            color-scheme: light;
        }

        .dark .color-scheme-dark {
            color-scheme: dark;
        }
    </style>
</div>
