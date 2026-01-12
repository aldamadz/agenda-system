<flux:header container
    class="glass-card !bg-white/30 dark:!bg-zinc-950/30 !border-b !border-white/20 dark:!border-zinc-800/30 backdrop-blur-xl sticky top-0 z-50">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

    <a href="{{ route('dashboard') }}" class="ms-2 me-5 flex items-center space-x-2 lg:ms-0" wire:navigate>
        <x-app-logo class="size-6" />
    </a>

    <flux:navbar class="-mb-px max-lg:hidden">
        <flux:navbar.item :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
            {{ __('Dashboard') }}
        </flux:navbar.item>
    </flux:navbar>

    <flux:spacer />

    <flux:navbar class="me-1.5 space-x-0.5 py-0!">
        <flux:tooltip :content="__('Search')" position="bottom">
            <flux:navbar.item class="!h-10" icon="magnifying-glass" href="#" />
        </flux:tooltip>
    </flux:navbar>

    <flux:dropdown position="top" align="end">
        <flux:profile class="cursor-pointer" :initials="auth()->user()->initials()" />
        <flux:menu class="glass-card !bg-white/80 dark:!bg-zinc-900/80 backdrop-blur-xl">
            <div class="px-4 py-2 border-b border-zinc-200 dark:border-zinc-700 mb-2">
                <div class="text-sm font-bold truncate text-zinc-800 dark:text-white">{{ auth()->user()->name }}</div>
                <div class="text-[10px] text-zinc-500 truncate">{{ auth()->user()->email }}</div>
            </div>
            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}
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
</flux:header>
