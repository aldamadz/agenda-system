<?php

use App\Models\Agenda;
use App\Models\AgendaStep;
use Carbon\Carbon;
use function Livewire\Volt\{state, computed};

// State untuk menangani modal catatan
state([
    'activeStepId' => null,
    'stepNote' => '',
    'showNoteModal' => false,
]);

$agendas = computed(
    fn() => Agenda::where('user_id', auth()->id())
        ->where('status', 'ongoing')
        ->with('steps')
        ->latest()
        ->get(),
);

// Fungsi saat checkbox diklik
$prepareToggle = function ($stepId, $isCurrentlyCompleted) {
    // PROTEKSI JAM 21:00
    if (now()->hour >= 21) {
        $this->dispatch('swal', [
            'icon' => 'error',
            'title' => 'Waktu Habis!',
            'text' => 'Batas pengisian log adalah jam 21:00.',
        ]);
        return;
    }

    $step = AgendaStep::find($stepId);

    // Jika mau UNCHECK (membatalkan selesai), langsung proses tanpa catatan
    if ($isCurrentlyCompleted) {
        $step->update(['is_completed' => false, 'completed_at' => null, 'notes' => null]);
        return;
    }

    // Jika mau CHECK (menyelesaikan), buka modal catatan
    $this->activeStepId = $stepId;
    $this->stepNote = '';
    $this->showNoteModal = true;
};

// Fungsi menyimpan catatan dan menyelesaikan step
$saveStepWithNote = function () {
    $this->validate(['stepNote' => 'required|min:3']);

    $step = AgendaStep::find($this->activeStepId);
    $step->update([
        'is_completed' => true,
        'completed_at' => now(),
        'notes' => $this->stepNote,
    ]);

    $this->showNoteModal = false;
    $this->dispatch('swal', [
        'icon' => 'success',
        'title' => 'Berhasil!',
        'text' => 'Tahapan selesai dengan catatan.',
    ]);
};

$finishAgenda = function ($agendaId) {
    $agenda = Agenda::find($agendaId);
    $unfinishedSteps = $agenda->steps()->where('is_completed', false)->count();

    if ($unfinishedSteps > 0) {
        $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Belum Lengkap', 'text' => "Masih ada $unfinishedSteps tahapan yang belum selesai."]);
        return;
    }

    $agenda->update(['status' => 'completed']);
    $this->dispatch('swal', ['icon' => 'success', 'title' => 'Selesai!', 'text' => 'Agenda telah ditutup.']);
};
?>

<div class="p-6">
    <div class="mb-6">
        <flux:heading size="xl">Log Progress Harian</flux:heading>
        <flux:subheading>Update tahapan kerja dan berikan catatan singkat.</flux:subheading>
    </div>

    @forelse($this->agendas as $agenda)
        <div class="mb-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm">
            <h3 class="font-bold text-lg mb-4">{{ $agenda->title }}</h3>

            <div class="space-y-3">
                @foreach ($agenda->steps as $step)
                    <div
                        class="p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-700">
                        <div class="flex items-center gap-3">
                            <input type="checkbox"
                                wire:click="prepareToggle({{ $step->id }}, {{ $step->is_completed ? 'true' : 'false' }})"
                                {{ $step->is_completed ? 'checked' : '' }}
                                class="w-5 h-5 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:bg-zinc-700 dark:border-zinc-600">
                            <div class="flex-1">
                                <p
                                    class="{{ $step->is_completed ? 'line-through text-zinc-400' : 'text-zinc-700 dark:text-zinc-300' }} font-medium">
                                    {{ $step->step_name }}
                                </p>
                                @if ($step->notes)
                                    <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-1">
                                        <strong>Catatan:</strong> {{ $step->notes }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex justify-end">
                <flux:button variant="primary" size="sm" wire:click="finishAgenda({{ $agenda->id }})">Selesaikan
                    Agenda</flux:button>
            </div>
        </div>
    @empty
        <p class="text-center text-zinc-500">Tidak ada agenda aktif.</p>
    @endforelse

    <flux:modal wire:model="showNoteModal" class="md:w-[500px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Tambahkan Catatan</flux:heading>
                <flux:subheading>Apa yang Anda lakukan di tahapan ini?</flux:subheading>
            </div>

            <flux:textarea wire:model="stepNote"
                placeholder="Contoh: Sudah menghubungi vendor, hasil meeting terlampir..." rows="3" />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button variant="ghost" wire:click="$set('showNoteModal', false)">Batal</flux:button>
                <flux:button variant="primary" wire:click="saveStepWithNote">Simpan & Selesaikan</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
