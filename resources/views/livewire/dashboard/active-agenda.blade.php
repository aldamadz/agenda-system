<?php
use App\Models\Agenda;
use App\Models\User;
use function Livewire\Volt\{computed, state};

state([
    'selectedAgenda' => null,
    'showModal' => false,
]);

$activeAgendas = computed(function () {
    $user = auth()->user();
    $isAdmin = $user->role === 'admin';
    $isManager = User::where('parent_id', $user->id)->exists();

    // Query Dasar dengan Eager Loading untuk performa
    $query = Agenda::with(['steps', 'user'])->where('status', 'ongoing');

    // LOGIKA RBAC (Role Based Access Control)
    if (!$isAdmin) {
        if ($isManager) {
            // Manager melihat agenda yang perlu dia approve ATAU miliknya sendiri
            $query->where(function ($q) use ($user) {
                $q->where('approver_id', $user->id)->orWhere('user_id', $user->id);
            });
        } else {
            // Staff hanya melihat miliknya sendiri
            $query->where('user_id', $user->id);
        }
    }

    return $query->latest()->take(10)->get();
});

$openDetail = function ($id) {
    $this->selectedAgenda = Agenda::with(['steps', 'user'])->find($id);
    $this->showModal = true;
};
?>

<div class="flex flex-col h-full">
    {{-- Header Section --}}
    <div class="flex items-center justify-between mb-5 px-1">
        <div>
            <flux:heading size="lg" class="!font-black tracking-tight uppercase italic flex items-center gap-2">
                <span class="size-2 bg-indigo-500 rounded-full animate-pulse"></span>
                Agenda Terkini
            </flux:heading>
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Progress real-time proyek
                aktif</p>
        </div>
        <flux:button variant="ghost" size="sm" :href="route('dashboard.monitoring')" wire:navigate
            class="!text-indigo-600 dark:!text-indigo-400 font-black hover:bg-indigo-50 dark:hover:bg-indigo-500/10 rounded-xl">
            LIHAT SEMUA
        </flux:button>
    </div>

    {{-- Scrollable List Area --}}
    <div class="overflow-y-auto pr-2 space-y-3 custom-scrollbar flex-1" style="max-height: 520px;">
        @forelse($this->activeAgendas as $agenda)
            @php
                $total = $agenda->steps->count();
                $done = $agenda->steps->where('is_completed', true)->count();
                $percent = $total > 0 ? ($done / $total) * 100 : 0;
                $isOverdue = $agenda->deadline && $agenda->deadline->isPast() && $agenda->status !== 'completed';
            @endphp

            <div wire:click="openDetail({{ $agenda->id }})"
                class="group p-5 bg-white dark:bg-zinc-900/50 border border-slate-200 dark:border-white/10 rounded-[2rem] shadow-sm hover:shadow-xl hover:shadow-indigo-500/5 hover:border-indigo-500 transition-all cursor-pointer relative overflow-hidden">

                {{-- Decorative Background --}}
                <div
                    class="absolute -right-4 -top-4 size-16 bg-indigo-500/5 rounded-full blur-xl group-hover:bg-indigo-500/10 transition-colors">
                </div>

                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1 pr-4">
                        <div class="flex items-center gap-2 mb-1">
                            @if (auth()->user()->role === 'admin')
                                <span
                                    class="text-[9px] font-black text-indigo-500 bg-indigo-500/10 px-2 py-0.5 rounded-full uppercase tracking-tighter">
                                    {{ $agenda->user->name }}
                                </span>
                            @endif
                            @if ($isOverdue)
                                <span
                                    class="text-[9px] font-black text-rose-500 bg-rose-500/10 px-2 py-0.5 rounded-full uppercase tracking-tighter">Terlambat</span>
                            @endif
                        </div>
                        <h4
                            class="font-bold text-sm text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors line-clamp-1">
                            {{ $agenda->title }}
                        </h4>
                        <div class="flex items-center gap-2 mt-2">
                            <span
                                class="text-[10px] text-slate-400 font-black uppercase tracking-widest">{{ $done }}/{{ $total }}
                                Steps</span>
                            <span class="size-1 rounded-full bg-slate-300 dark:bg-white/20"></span>
                            <span
                                class="text-[10px] text-indigo-600 dark:text-indigo-400 font-black tracking-tighter">{{ round($percent) }}%
                                COMPLETED</span>
                        </div>
                    </div>
                    <div
                        class="size-8 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center group-hover:bg-indigo-500 group-hover:text-white transition-all">
                        <flux:icon.chevron-right variant="mini" />
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="w-full bg-slate-100 dark:bg-white/5 h-2 rounded-full overflow-hidden shadow-inner">
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-400 h-full transition-all duration-1000 ease-out"
                        style="width: {{ $percent }}%"></div>
                </div>
            </div>
        @empty
            <div class="py-20 text-center border-2 border-dashed border-slate-200 dark:border-white/5 rounded-[3rem]">
                <div
                    class="size-16 bg-slate-50 dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4">
                    <flux:icon.document-text class="text-slate-300 size-8" />
                </div>
                <p class="text-xs text-slate-400 font-black uppercase italic tracking-widest">Belum ada agenda yang
                    berjalan</p>
            </div>
        @endforelse
    </div>

    {{-- MODAL DETAIL --}}
    <flux:modal wire:model="showModal"
        class="md:w-[600px] !bg-white/90 dark:!bg-zinc-950/90 border-white/20 rounded-[3rem] backdrop-blur-2xl">
        @if ($selectedAgenda)
            <div class="p-4">
                <div class="flex flex-col items-center text-center mb-8">
                    <flux:badge color="indigo" variant="solid"
                        class="mb-4 uppercase font-black text-[10px] tracking-[0.3em] px-4 py-1 rounded-full">Detail
                        Agenda</flux:badge>
                    <flux:heading size="xl" class="!font-black tracking-tighter leading-tight italic max-w-sm">
                        {{ $selectedAgenda->title }}
                    </flux:heading>

                    <div class="flex items-center gap-3 mt-4 px-4 py-2 bg-slate-100 dark:bg-white/5 rounded-2xl">
                        <div
                            class="size-7 rounded-full bg-indigo-600 flex items-center justify-center text-[10px] text-white font-black ring-4 ring-indigo-500/10">
                            {{ substr($selectedAgenda->user->name, 0, 1) }}
                        </div>
                        <span
                            class="text-xs font-black text-slate-700 dark:text-slate-200 uppercase tracking-tight">{{ $selectedAgenda->user->name }}</span>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-center border-b border-slate-100 dark:border-white/5 pb-2">
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Timeline Progress</p>
                        <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest">
                            {{ $selectedAgenda->steps->where('is_completed', true)->count() }} /
                            {{ $selectedAgenda->steps->count() }} Selesai</p>
                    </div>

                    <div class="max-h-[380px] overflow-y-auto space-y-3 pr-2 custom-scrollbar">
                        @foreach ($selectedAgenda->steps as $step)
                            <div
                                class="group/step flex items-start gap-4 p-4 rounded-[1.5rem] border transition-all {{ $step->is_completed ? 'bg-emerald-500/5 border-emerald-500/20' : 'bg-white dark:bg-white/5 border-slate-200 dark:border-white/10 shadow-sm' }}">
                                <div class="mt-0.5">
                                    @if ($step->is_completed)
                                        <div
                                            class="size-6 bg-emerald-500 rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/30">
                                            <flux:icon.check variant="micro" class="text-white size-3.5" />
                                        </div>
                                    @else
                                        <div
                                            class="size-6 rounded-full border-2 border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 flex items-center justify-center">
                                            <span
                                                class="size-2 bg-slate-200 dark:bg-zinc-700 rounded-full group-hover/step:bg-indigo-500 transition-colors"></span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p
                                        class="text-xs font-bold leading-relaxed {{ $step->is_completed ? 'line-through text-slate-400' : 'text-slate-800 dark:text-slate-200' }}">
                                        {{ $step->step_name }}
                                    </p>
                                    @if ($step->deadline)
                                        <div class="flex items-center gap-1.5 mt-2">
                                            <flux:icon.clock variant="micro" class="text-slate-400 size-3" />
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">
                                                Deadline:
                                                {{ \Carbon\Carbon::parse($step->deadline)->format('d M, H:i') }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-8">
                    <flux:button class="!rounded-[1.2rem] font-bold h-11" x-on:click="$wire.showModal = false">Kembali
                    </flux:button>
                    <flux:button variant="primary" color="indigo"
                        class="!rounded-[1.2rem] font-black h-11 shadow-lg shadow-indigo-500/20"
                        :href="route('dashboard.monitoring')" wire:navigate>
                        DETAIL MONITORING
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 20px;
            border: 2px solid transparent;
            background-clip: content-box;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #334155;
            background-clip: content-box;
        }
    </style>
</div>
