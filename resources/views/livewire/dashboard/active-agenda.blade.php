<?php
use App\Models\Agenda;
use function Livewire\Volt\{computed, state};

state([
    'selectedAgenda' => null,
    'showModal' => false,
]);

$activeAgendas = computed(function () {
    $user = auth()->user();
    // Mengambil 10 agar scroll terlihat
    return Agenda::with('steps')
        ->where($user->parent_id === null ? 'approver_id' : 'user_id', $user->id)
        ->where('status', 'ongoing')
        ->latest()
        ->take(10)
        ->get();
});

$openDetail = function ($id) {
    $this->selectedAgenda = Agenda::with(['steps', 'user'])->find($id);
    $this->showModal = true;
};
?>

{{-- Root Element Tunggal --}}
<div class="flex flex-col h-full">
    <div class="flex items-center justify-between mb-4 px-1">
        <flux:heading size="lg" class="!font-black tracking-tight uppercase italic">Agenda Terkini</flux:heading>
        <flux:button variant="ghost" size="sm" :href="route('dashboard.monitoring')" wire:navigate
            class="!text-indigo-500 font-bold hover:bg-indigo-50 dark:hover:bg-indigo-500/10">
            Lihat Semua
        </flux:button>
    </div>

    {{-- Area Scrollable --}}
    <div class="overflow-y-auto pr-2 space-y-3 custom-scrollbar" style="max-height: 480px;">
        @forelse($this->activeAgendas as $agenda)
            @php
                $total = $agenda->steps->count();
                $done = $agenda->steps->where('is_completed', true)->count();
                $percent = $total > 0 ? ($done / $total) * 100 : 0;
            @endphp

            <div wire:click="openDetail({{ $agenda->id }})"
                class="group p-4 bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl shadow-sm hover:border-indigo-500 transition-all cursor-pointer relative">

                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1 pr-4">
                        <h4
                            class="font-bold text-sm text-slate-900 dark:text-white group-hover:text-indigo-500 transition-colors line-clamp-1">
                            {{ $agenda->title }}</h4>
                        <div class="flex items-center gap-2 mt-1">
                            <span
                                class="text-[10px] text-slate-400 font-black uppercase tracking-tighter">{{ $done }}/{{ $total }}
                                Tahap</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span class="text-[10px] text-indigo-500 font-bold italic">{{ round($percent) }}%</span>
                        </div>
                    </div>
                    <flux:icon.chevron-right variant="mini"
                        class="text-slate-300 group-hover:text-indigo-500 transition-colors" />
                </div>

                {{-- Progress Bar --}}
                <div class="w-full bg-slate-100 dark:bg-white/10 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-indigo-500 h-full transition-all duration-1000 ease-out shadow-[0_0_8px_rgba(99,102,241,0.4)]"
                        style="width: {{ $percent }}%"></div>
                </div>
            </div>
        @empty
            <div class="py-16 text-center border-2 border-dashed border-slate-200 dark:border-white/10 rounded-[2rem]">
                <flux:icon.document-text class="mx-auto mb-2 text-slate-200 size-10" />
                <p class="text-[10px] text-slate-400 font-black uppercase italic tracking-widest">Belum ada agenda aktif
                </p>
            </div>
        @endforelse
    </div>

    {{-- MODAL DETAIL --}}
    <flux:modal wire:model="showModal" class="md:w-[550px] border-white/20 rounded-[2.5rem] backdrop-blur-xl">
        @if ($selectedAgenda)
            <div class="space-y-6 p-2">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <flux:badge color="indigo" variant="solid"
                            class="mb-2 uppercase font-black text-[9px] tracking-widest">Agenda Detail</flux:badge>
                        <flux:heading size="xl" class="!font-black tracking-tighter leading-tight italic">
                            {{ $selectedAgenda->title }}</flux:heading>
                        <div class="flex items-center gap-2 mt-2">
                            <div
                                class="w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center text-[10px] text-white font-bold">
                                {{ substr($selectedAgenda->user->name, 0, 1) }}
                            </div>
                            <span
                                class="text-xs font-bold text-slate-600 dark:text-slate-300">{{ $selectedAgenda->user->name }}</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <p
                        class="text-[10px] font-black uppercase text-slate-400 tracking-widest border-b border-slate-100 dark:border-white/5 pb-2">
                        Tracking Progress</p>
                    <div class="max-h-[350px] overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                        @foreach ($selectedAgenda->steps as $step)
                            <div
                                class="flex items-center gap-4 p-4 rounded-2xl {{ $step->is_completed ? 'bg-emerald-500/5 border-emerald-500/20' : 'bg-slate-50 dark:bg-white/5 border-slate-100 dark:border-white/10' }} border transition-colors">
                                <div class="shrink-0">
                                    @if ($step->is_completed)
                                        <div class="bg-emerald-500 rounded-full p-1">
                                            <flux:icon.check variant="micro" class="text-white size-3" />
                                        </div>
                                    @else
                                        <div
                                            class="w-5 h-5 rounded-full border-2 border-slate-300 dark:border-white/20">
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p
                                        class="text-xs font-bold {{ $step->is_completed ? 'line-through text-slate-400' : 'text-slate-700 dark:text-slate-200' }}">
                                        {{ $step->step_name }}
                                    </p>
                                    @if ($step->deadline)
                                        <div class="flex items-center gap-1 mt-1">
                                            <flux:icon.clock variant="micro" class="text-slate-400" />
                                            <p class="text-[9px] font-bold text-slate-400 uppercase">
                                                {{ $step->deadline->format('d M, H:i') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <flux:button class="flex-1 !rounded-xl font-bold" x-on:click="$wire.showModal = false">Tutup
                    </flux:button>
                    <flux:button variant="primary" color="indigo"
                        class="flex-1 !rounded-xl font-black shadow-lg shadow-indigo-500/20"
                        :href="route('dashboard.monitoring')" wire:navigate>
                        Lanjut Monitoring
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

    {{-- Style CSS --}}
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
            background: #1e293b;
        }
    </style>
</div>
