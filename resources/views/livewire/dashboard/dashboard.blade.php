<?php
use App\Models\User;
use function Livewire\Volt\{layout, title, computed};

layout('components.layouts.app');
title('Dashboard Overview');

// State check untuk Role
$isManager = computed(fn() => User::where('parent_id', auth()->id())->exists());
$isAdmin = computed(fn() => auth()->user()->role === 'admin');
?>

<div class="p-6 lg:p-10 space-y-10 min-h-screen">

    {{-- 1. Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <flux:heading size="xl" class="!font-black uppercase italic tracking-tighter">
                @if ($this->isAdmin)
                    Admin <span class="text-indigo-600">Command Center</span>
                @elseif ($this->isManager)
                    Manager <span class="text-indigo-600">Control Panel</span>
                @else
                    My <span class="text-indigo-600">Workspace</span>
                @endif
            </flux:heading>
            <flux:subheading class="max-w-2xl">
                @if ($this->isAdmin)
                    Otoritas penuh sistem: Kelola pengguna, audit log, dan pantau seluruh agenda perusahaan secara
                    real-time.
                @elseif ($this->isManager)
                    Pantau kinerja tim, berikan approval, dan arahkan setiap tahapan agenda bawahan Anda dengan tepat.
                @else
                    Kelola agenda pribadimu dengan efisien, laporkan progres setiap tahapan, dan capai target tepat
                    waktu.
                @endif
            </flux:subheading>
        </div>

        {{-- Action Buttons Header (Quick Link Utama) --}}
        <div class="flex flex-wrap gap-3">
            @if ($this->isAdmin)
                <flux:button icon="users" variant="outline" :href="route('admin.users')" wire:navigate
                    class="!rounded-xl font-black uppercase italic tracking-widest text-[10px]">
                    Users
                </flux:button>
            @endif

            @if ($this->isManager || $this->isAdmin)
                <flux:button icon="clipboard-document-check" variant="filled" color="indigo"
                    :href="route('manager.approval')" wire:navigate
                    class="!rounded-xl shadow-lg shadow-indigo-500/20 font-black uppercase italic tracking-widest text-[10px]">
                    Approval List
                </flux:button>
            @endif

            <flux:button icon="plus" variant="primary" color="indigo" :href="route('agenda.create')" wire:navigate
                class="!rounded-xl shadow-lg shadow-indigo-500/20 font-black uppercase italic tracking-widest text-[10px]">
                Buat Agenda
            </flux:button>
        </div>
    </div>

    {{-- 2. Statistik Overview --}}
    <livewire:dashboard.stats />

    {{-- 3. Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">

        {{-- KOLOM KIRI: Monitoring Agenda (Lebar: 8/12) --}}
        <div class="lg:col-span-8 space-y-8">
            <div class="flex items-center justify-between px-2">
                <flux:heading size="lg" class="!font-black uppercase tracking-tight italic">
                    @if ($this->isAdmin)
                        Global <span class="text-indigo-600">Monitoring</span>
                    @elseif($this->isManager)
                        Tim <span class="text-indigo-600">Progress</span>
                    @else
                        Agenda <span class="text-indigo-600">Aktif</span>
                    @endif
                </flux:heading>
                <div
                    class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-200 dark:via-white/10 to-transparent mx-6 hidden sm:block">
                </div>
                <flux:button variant="ghost" size="sm" :href="route('dashboard.monitoring')" wire:navigate
                    icon-trailing="chevron-right" class="text-[10px] font-black uppercase italic">Lihat Semua
                </flux:button>
            </div>

            {{-- Komponen List Agenda --}}
            <livewire:dashboard.active-agenda />
        </div>

        {{-- KOLOM KANAN: Sidebar Widgets (Lebar: 4/12) --}}
        <div class="lg:col-span-4 space-y-8">


            {{-- Feed Aktivitas Terbaru --}}
            <div class="space-y-4">
                <div class="flex items-center gap-2 px-2">
                    <flux:heading size="sm" class="!font-black uppercase italic tracking-tighter">Update <span
                            class="text-indigo-600">Aktivitas</span></flux:heading>
                </div>

                <div
                    class="bg-white dark:bg-zinc-900/50 rounded-[2.5rem] border border-slate-200 dark:border-white/5 p-6 shadow-sm overflow-hidden">
                    <livewire:dashboard.activity-feed />
                </div>
            </div>

        </div>
    </div>
</div>
