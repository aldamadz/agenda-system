@php
    $user = auth()->user();
    $isAdmin = false;
    $isManager = false;
    $userInitials = '??';

    if ($user) {
        $isAdmin = $user->role === 'admin';
        $hasSubordinates = \App\Models\User::where('parent_id', $user->id)->exists();
        $isManager = $user->role === 'manager' || $isAdmin || $hasSubordinates;
        $userInitials = method_exists($user, 'initials') ? $user->initials() : strtoupper(substr($user->name, 0, 2));
    }
@endphp

<flux:sidebar sticky stashable wire:poll.30s
    class="sidebar-glass glass-card bg-slate-50/90 dark:bg-zinc-950/20 border-e border-slate-200 dark:border-zinc-800/30 backdrop-blur-2xl">

    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

    <div class="mb-8 flex items-center px-4">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 group" wire:navigate>
            <x-app-logo class="size-8 transition-transform group-hover:rotate-12 duration-300" />
            <h1 class="text-xl font-black tracking-tighter text-slate-900 dark:text-white uppercase">
                Agenda<span class="text-indigo-600">System</span>
            </h1>
        </a>
    </div>

    <flux:navlist variant="outline">
        <flux:navlist.group :heading="__('Platform')"
            class="grid gap-1 text-slate-900 dark:text-zinc-400 font-semibold">
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
            <flux:navlist.item icon="archive-box" :href="route('dashboard.history')"
                :current="request()->routeIs('dashboard.history')" wire:navigate>
                {{ __('Riwayat Agenda') }}
            </flux:navlist.item>
        </flux:navlist.group>

        @auth
            @if ($isManager)
                <flux:navlist.group :heading="__('Managerial')"
                    class="grid mt-6 gap-1 text-slate-900 dark:text-zinc-400 font-semibold">
                    <flux:navlist.item icon="check-badge" :href="route('manager.approval')"
                        :current="request()->routeIs('manager.approval')" wire:navigate>
                        {{ __('Persetujuan') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="exclamation-triangle" :href="route('manager.issues')"
                        :current="request()->routeIs('manager.issues')" wire:navigate>
                        {{ __('Issue Center') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            @endif

            @if ($isAdmin)
                <flux:navlist.group :heading="__('Admin Panel')"
                    class="grid mt-6 gap-1 text-slate-900 dark:text-zinc-400 font-semibold">
                    <flux:navlist.item icon="users" :href="route('admin.users')"
                        :current="request()->routeIs('admin.users')" wire:navigate>
                        {{ __('User Management') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="document-magnifying-glass" :href="route('dashboard.logs')"
                        :current="request()->routeIs('dashboard.logs')" wire:navigate>
                        {{ __('System Logs') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            @endif
        @endauth
    </flux:navlist>

    <flux:spacer />

    <flux:navlist variant="outline" class="mb-2">
        <flux:navlist.item icon="cog-6-tooth" :href="route('profile.edit')"
            :current="request()->routeIs('profile.edit') || request()->routeIs('user-password.edit') || request()->routeIs('appearance.edit')"
            class="text-slate-700 dark:text-zinc-400" wire:navigate>
            {{ __('Settings') }}
        </flux:navlist.item>

        <div class="lg:hidden border-t border-zinc-200 dark:border-zinc-800 mt-2 pt-2">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:navlist.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                    class="w-full text-red-600">
                    {{ __('Log Out') }}
                </flux:navlist.item>
            </form>
        </div>
    </flux:navlist>

    @auth
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
            <flux:profile class="cursor-pointer text-slate-900 dark:text-white" :name="$user->name"
                :initials="$userInitials" icon:trailing="chevrons-up-down" />
            <flux:menu class="w-[220px]">
                <flux:menu.item :href="route('profile.edit')" icon="user" wire:navigate>{{ __('My Profile') }}
                </flux:menu.item>
                <flux:menu.separator />
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full text-red-500">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    @endauth
</flux:sidebar>
