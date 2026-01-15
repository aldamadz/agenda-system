@php
    $user = auth()->user();

    // Perbaikan Logika: Pastikan tidak ada pemanggilan $this di sini
    $isAdmin = $user->isAdmin();

    // Manager adalah orang yang punya bawahan ATAU memang rolenya manager
    $isManager = $user->isManager() || $isAdmin || \App\Models\User::where('parent_id', $user->id)->exists();

    // Inisial user (jika method initials() ada di model User)
    // Jika error berlanjut di initials(), gunakan: strtoupper(substr($user->name, 0, 2))
    $userInitials = method_exists($user, 'initials') ? $user->initials() : strtoupper(substr($user->name, 0, 2));
@endphp

<style>
    :where(html:not(.dark)) .sidebar-glass [data-flux-navlist-item][data-current="true"] {
        background-color: white !important;
        color: #1e293b !important;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        font-weight: 700 !important;
    }

    :where(html:not(.dark)) .sidebar-glass [data-flux-navlist-item][data-current="true"] svg {
        color: #4f46e5 !important;
    }

    :where(html:not(.dark)) .sidebar-glass [data-flux-navlist-group]>[data-flux-label] {
        color: #64748b !important;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
</style>

<flux:sidebar sticky stashable wire:poll.15s
    class="sidebar-glass glass-card !bg-white/50 dark:!bg-zinc-950/20 !border-e !border-white/20 dark:!border-zinc-800/30 backdrop-blur-2xl">

    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

    {{-- Logo --}}
    <div class="mb-8 flex items-center px-4">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 group" wire:navigate>
            <x-app-logo class="size-8 transition-transform group-hover:rotate-12 duration-300" />
            <h1 class="text-xl font-black tracking-tighter dark:text-white uppercase italic">
                Agenda<span class="text-indigo-600">System</span>
            </h1>
        </a>
    </div>

    <flux:navlist variant="outline">
        <flux:navlist.group :heading="__('Platform')" class="grid gap-1">
            <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                wire:navigate>
                {{ __('Dashboard') }}
            </flux:navlist.item>

            <flux:navlist.item icon="calendar-days" :href="route('calendar')" :current="request()->routeIs('calendar')"
                wire:navigate>
                {{ __('Kalender') }}
            </flux:navlist.item>

            <flux:navlist.item icon="clock" :href="route('dashboard.monitoring')"
                :current="request()->routeIs('dashboard.monitoring')" wire:navigate>
                {{ __('Monitoring') }}
            </flux:navlist.item>

            <flux:navlist.item icon="plus-circle" :href="route('agenda.create')"
                :current="request()->routeIs('agenda.create')" wire:navigate>
                {{ __('Buat Agenda') }}
            </flux:navlist.item>

            <flux:navlist.item icon="archive-box" :href="route('dashboard.history')"
                :current="request()->routeIs('dashboard.history')" wire:navigate>
                {{ __('Riwayat Selesai') }}
            </flux:navlist.item>
        </flux:navlist.group>

        {{-- Grup Managerial --}}
        @if ($isManager)
            <flux:navlist.group :heading="__('Managerial')" class="grid mt-6 gap-1">
                {{-- Menu Persetujuan (Agenda Baru) --}}
                <flux:navlist.item icon="check-badge" :href="route('manager.approval')"
                    :current="request()->routeIs('manager.approval')" wire:navigate>
                    {{ __('Persetujuan') }}

                    @php
                        $pendingQuery = \App\Models\Agenda::where('status', 'pending');
                        if (!$isAdmin) {
                            $pendingQuery->where('approver_id', $user->id);
                        }
                        $pendingCount = $pendingQuery->count();
                    @endphp

                    @if ($pendingCount > 0)
                        <flux:badge size="sm" color="red" variant="solid" class="ml-auto animate-pulse">
                            {{ $pendingCount }}
                        </flux:badge>
                    @endif
                </flux:navlist.item>

                {{-- NEW: Menu Issue Center (Laporan Kendala & Request Waktu) --}}
                <flux:navlist.item icon="exclamation-triangle" :href="route('manager.issues')"
                    :current="request()->routeIs('manager.issues')" wire:navigate>
                    {{ __('Issue Center') }}

                    @php
                        $issueQuery = \App\Models\AgendaLog::query();

                        // FILTER 1: Hanya ambil kategori yang merupakan masalah/request
                        // Kita mengecualikan kategori seperti 'Update' atau 'Progres' biasa
                        $issueQuery->whereIn('issue_category', [
                            'Extension Request',
                            'Technical Issue',
                            'Data Issue',
                            'Other Issue',
                        ]);

                        // FILTER 2: Keamanan (Manager hanya melihat issue dari bawahannya)
                        if (!$isAdmin) {
                            $issueQuery->whereHas('agenda', function ($q) use ($user) {
                                $q->where('approver_id', $user->id);
                            });
                        }

                        $issueCount = $issueQuery->count();
                    @endphp

                    @if ($issueCount > 0)
                        <flux:badge size="sm" color="amber" variant="solid" class="ml-auto font-black">
                            {{ $issueCount }}
                        </flux:badge>
                    @endif
                </flux:navlist.item>
            </flux:navlist.group>
        @endif

        {{-- Grup Admin Panel --}}
        @if ($isAdmin)
            <flux:navlist.group :heading="__('Admin Panel')" class="grid mt-6 gap-1">
                <flux:navlist.item icon="users" :href="route('admin.users')"
                    :current="request()->routeIs('admin.users')" wire:navigate>
                    {{ __('Manajemen User') }}
                </flux:navlist.item>

                <flux:navlist.item icon="clipboard-document-list" :href="route('dashboard.logs')"
                    :current="request()->routeIs('dashboard.logs')" wire:navigate>
                    {{ __('Log Aktivitas') }}
                </flux:navlist.item>
            </flux:navlist.group>
        @endif
    </flux:navlist>

    <flux:spacer />

    <flux:navlist variant="outline" class="mb-4">
        <flux:navlist.item icon="cog-6-tooth" :href="route('profile.edit')"
            :current="request()->routeIs('profile.edit')" wire:navigate>
            {{ __('Settings') }}
        </flux:navlist.item>
    </flux:navlist>

    <flux:dropdown class="hidden lg:block" position="bottom" align="start">
        <flux:profile class="cursor-pointer hover:bg-zinc-800/5 dark:hover:bg-white/10 p-2 rounded-2xl transition-all"
            :name="$user->name" :initials="$userInitials" icon:trailing="chevrons-up-down" />

        <flux:menu class="w-[220px] glass-card !bg-white/90 dark:!bg-zinc-900/80 backdrop-blur-xl">
            <flux:menu.item :href="route('profile.edit')" icon="user" wire:navigate>{{ __('My Profile') }}
            </flux:menu.item>
            <flux:menu.separator />
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                    class="w-full text-red-500 hover:!bg-red-500/10">
                    {{ __('Log Out') }}
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</flux:sidebar>
