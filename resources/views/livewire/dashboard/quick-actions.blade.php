<?php
use App\Models\Agenda;
use function Livewire\Volt\{computed};

$pendingCount = computed(function () {
    return auth()->user()->parent_id === null
        ? Agenda::where('approver_id', auth()->id())
            ->where('status', 'pending')
            ->count()
        : 0;
});
?>

<div class="space-y-6 text-sm">
    @if ($this->pendingCount > 0)
        <div class="p-6 bg-orange-500/10 border border-orange-500/20 rounded-2xl">
            <flux:heading color="orange" class="mb-2">Butuh Persetujuan</flux:heading>
            <p class="text-zinc-400 mb-4">Ada {{ $this->pendingCount }} ajuan dari bawahan yang menunggu.</p>
            <flux:button color="orange" size="sm" class="w-full" :href="route('manager.approval')" wire:navigate>Buka
                Approval</flux:button>
        </div>
    @endif

    <div class="p-6 bg-zinc-900/50 border border-zinc-800 rounded-2xl">
        <flux:heading class="mb-4">Aksi Cepat</flux:heading>
        <div class="flex flex-col gap-2">
            <flux:button icon="plus" class="justify-start" :href="route('agenda.create')" wire:navigate>Agenda Baru
            </flux:button>
            <flux:button icon="clock" variant="ghost" class="justify-start" :href="route('dashboard.monitoring')"
                wire:navigate>Monitoring</flux:button>
            <flux:button icon="archive-box" variant="ghost" class="justify-start" :href="route('dashboard.history')"
                wire:navigate>Riwayat</flux:button>
        </div>
    </div>
</div>
