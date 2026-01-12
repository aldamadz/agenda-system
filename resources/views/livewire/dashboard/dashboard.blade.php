<?php
use App\Models\User;
use function Livewire\Volt\{layout, title, computed};

layout('components.layouts.app');
title('Dashboard Overview');

$isManager = computed(fn() => User::where('parent_id', auth()->id())->exists());
?>

<div class="p-6 lg:p-10 space-y-10 bg-slate-50 dark:bg-slate-950 min-h-screen transition-colors duration-500">

    {{-- 1. Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" class="mb-1 text-zinc-800 dark:text-white font-bold uppercase tracking-tighter">
                @if ($this->isManager)
                    Manager <span class="text-indigo-600 font-bold">Control Panel</span>
                @else
                    My <span class="text-indigo-600 font-bold">Workspace</span>
                @endif
            </flux:heading>
            <flux:subheading>
                {{ $this->isManager ? 'Pantau kinerja tim dan berikan arahan pada agenda.' : 'Kelola agenda pribadimu dengan efisien.' }}
            </flux:subheading>
        </div>

        <div class="flex gap-3">
            @if ($this->isManager)
                <flux:button icon="clipboard-document-check" variant="filled" color="indigo"
                    :href="route('manager.approval')" wire:navigate
                    class="shadow-lg shadow-indigo-500/20 uppercase font-bold tracking-wider">
                    Approval List
                </flux:button>
            @endif
            <flux:button icon="plus" variant="primary" color="indigo" :href="route('agenda.create')" wire:navigate
                class="shadow-lg shadow-indigo-500/20 uppercase font-bold tracking-wider">
                Buat Agenda
            </flux:button>
        </div>
    </div>

    {{-- 2. Statistik --}}
    <livewire:dashboard.stats />

    {{-- 3. Main Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <div class="lg:col-span-2 space-y-12">
            {{-- Progres Agenda --}}
            <div class="space-y-6">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-4">
                    <flux:heading size="lg" class="font-black uppercase tracking-tight italic">
                        {{ $this->isManager ? 'Monitoring' : 'Progres' }} <span class="text-indigo-600">Agenda
                            Aktif</span>
                    </flux:heading>
                </div>
                <livewire:dashboard.active-agenda />
            </div>
        </div>

        {{-- Kolom Kanan --}}
        <div class="space-y-6">
            <div class="border-b border-zinc-100 dark:border-zinc-800 pb-4">
                <flux:heading size="lg" class="font-black uppercase tracking-tight italic">Update <span
                        class="text-indigo-600">Aktivitas</span></flux:heading>
            </div>
            <div
                class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 p-8 shadow-sm">
                <livewire:dashboard.activity-feed />
            </div>
        </div>
    </div>
</div>
