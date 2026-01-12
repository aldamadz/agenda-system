<?php

use App\Models\Agenda;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use function Livewire\Volt\{state, rules, computed};

state([
    'title' => '',
    'description' => '',
    'deadline' => '',
    'steps' => [],
]);

rules([
    'title' => 'required|min:5',
    'description' => 'required|min:5',
    'deadline' => 'required|after:now',
    'steps' => 'required|array|min:1',
    'steps.*.name' => 'required|min:2',
    'steps.*.percent' => 'required|numeric|min:0|max:100',
]);

$formatDuration = function ($totalMinutes) {
    $totalMinutes = max(0, $totalMinutes);
    $h = floor($totalMinutes / 60);
    $m = round($totalMinutes % 60);
    $res = [];
    if ($h > 0) {
        $res[] = "{$h}j";
    }
    if ($m > 0 || $h == 0) {
        $res[] = "{$m}m";
    }
    return implode(' ', $res);
};

$timeResources = computed(function () {
    if (!$this->deadline) {
        return null;
    }
    try {
        $now = Carbon::now();
        $target = Carbon::parse($this->deadline);
        return ['total_minutes' => $now->diffInMinutes($target)];
    } catch (\Exception $e) {
        return null;
    }
});

$addStep = function () {
    $this->steps[] = ['name' => '', 'percent' => 0];
};

$removeStep = function ($index) {
    unset($this->steps[$index]);
    $this->steps = array_values($this->steps);
};

$save = function () use ($formatDuration) {
    $this->validate();

    $totalUsed = collect($this->steps)->sum('percent');
    if ($totalUsed > 100) {
        $this->addError('steps', "Total alokasi ({$totalUsed}%) melebihi batas 100%.");
        return;
    }

    try {
        DB::beginTransaction();
        $user = auth()->user();

        $agenda = Agenda::create([
            'user_id' => $user->id,
            'approver_id' => $user->parent_id,
            'title' => $this->title,
            'description' => $this->description,
            'deadline' => $this->deadline,
            'status' => $user->parent_id ? 'pending' : 'ongoing',
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'agenda_id' => $agenda->id,
            'action' => 'Submission',
            'description' => "{$user->name} membuat agenda: \"{$this->title}\".",
            'icon' => 'plus',
            'color' => 'indigo',
        ]);

        $currentTime = Carbon::now();
        $totalMinutes = $this->timeResources['total_minutes'];
        $accumulatedMinutes = 0;

        foreach ($this->steps as $step) {
            $stepMinutes = ($step['percent'] / 100) * $totalMinutes;
            $accumulatedMinutes += $stepMinutes;

            $agenda->steps()->create([
                'step_name' => $step['name'],
                'duration' => $formatDuration($stepMinutes),
                'deadline' => $currentTime->copy()->addMinutes($accumulatedMinutes),
                'is_completed' => false,
            ]);
        }
        DB::commit();
        return redirect()->to('/dashboard');
    } catch (\Exception $e) {
        DB::rollBack();
        $this->addError('database_error', 'System Error: ' . $e->getMessage());
    }
};
?>

<div class="p-6 lg:p-10 bg-slate-50 dark:bg-slate-950 min-h-screen font-sans selection:bg-indigo-500 selection:text-white"
    x-data="{
        stepsData: @entangle('steps'),
        get totalUsed() {
            return this.stepsData.reduce((acc, step) => acc + parseInt(step.percent || 0), 0);
        }
    }">
    <div class="max-w-7xl mx-auto">

        @if ($errors->any())
            <div
                class="mb-6 p-4 bg-red-500/10 border border-red-500/50 rounded-2xl flex flex-col gap-1 text-red-600 dark:text-red-400">
                <div class="flex items-center gap-2">
                    <flux:icon.exclamation-triangle variant="mini" />
                    <span class="text-xs font-black uppercase tracking-widest">Validasi Gagal</span>
                </div>
                @error('steps')
                    <p class="text-[10px] font-bold uppercase ml-6">{{ $message }}</p>
                @enderror
                @error('database_error')
                    <p class="text-[10px] font-bold uppercase ml-6">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <form wire:submit.prevent="save" class="grid grid-cols-1 lg:grid-cols-12 gap-10">

            {{-- Kolom Kiri --}}
            <div class="lg:col-span-5 space-y-8">
                <div
                    class="relative bg-white/60 dark:bg-slate-900/60 backdrop-blur-2xl border border-white dark:border-white/10 rounded-[3rem] p-10 shadow-2xl space-y-6">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-10 h-10 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
                            <flux:icon.document-text variant="mini" />
                        </div>
                        <h2 class="text-sm font-black uppercase tracking-widest text-slate-800 dark:text-white italic">
                            Primary Info</h2>
                    </div>

                    <flux:input wire:model="title" label="Judul Proyek" class="!rounded-2xl" />
                    <flux:textarea wire:model="description" label="Deskripsi" rows="4" class="!rounded-2xl" />
                    <flux:input wire:model.live="deadline" type="datetime-local" label="Final Deadline"
                        class="!rounded-2xl" />
                </div>

                <div
                    class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-[2.5rem] p-10 text-white shadow-2xl relative overflow-hidden group">
                    <h3 class="font-black italic text-2xl mb-4 uppercase tracking-tighter">Smart Scaling</h3>
                    <p class="text-xs uppercase font-black opacity-80 italic">Slider bersifat relatif terhadap sisa
                        kuota.</p>
                </div>
            </div>

            {{-- Kolom Kanan --}}
            <div class="lg:col-span-7 flex flex-col">
                <div
                    class="relative bg-white/40 dark:bg-slate-900/40 backdrop-blur-3xl border border-white dark:border-white/10 rounded-[3.5rem] p-10 shadow-2xl flex flex-col h-full">

                    <div class="flex justify-between items-center mb-10">
                        <h2 class="text-lg font-black uppercase italic text-slate-800 dark:text-white">Workload <span
                                class="text-indigo-600">Steps</span></h2>
                        <button type="button" wire:click="addStep"
                            class="px-6 py-3 bg-slate-900 dark:bg-white dark:text-slate-900 text-white text-[10px] font-black uppercase rounded-2xl hover:scale-105 transition-all shadow-xl">
                            + Add Step
                        </button>
                    </div>

                    <div class="flex-1 space-y-6 mb-10 overflow-y-auto max-h-[500px] pr-2 custom-scrollbar">
                        @php $totalMinutes = $this->timeResources['total_minutes'] ?? 0; @endphp

                        @forelse ($steps as $index => $step)
                            {{-- Logic Alpine untuk sisa kuota dinamis --}}
                            <div class="p-8 bg-white/80 dark:bg-white/5 border border-white dark:border-white/10 rounded-[2.5rem] relative group/step"
                                wire:key="step-{{ $index }}" x-data="{
                                    get available() {
                                        // Menghitung sisa kuota SEBELUM step ini
                                        let usedBefore = stepsData.slice(0, {{ $index }}).reduce((acc, s) => acc + parseInt(s.percent || 0), 0);
                                        return Math.max(0, 100 - usedBefore);
                                    },
                                    get displayDuration() {
                                        let mins = (stepsData[{{ $index }}].percent / 100) * {{ $totalMinutes }};
                                        if ({{ $totalMinutes }} === 0) return '--';
                                        let h = Math.floor(mins / 60);
                                        let m = Math.round(mins % 60);
                                        return (h > 0 ? h + 'j ' : '') + (m > 0 || h === 0 ? m + 'm' : '');
                                    }
                                }">

                                <button type="button" wire:click="removeStep({{ $index }})"
                                    class="absolute top-8 right-8 text-slate-300 hover:text-red-500 transition-colors">
                                    <flux:icon.x-mark variant="mini" />
                                </button>

                                <div class="flex items-center gap-4 mb-6">
                                    <span
                                        class="w-8 h-8 rounded-lg bg-indigo-600/10 text-indigo-600 flex items-center justify-center text-[10px] font-black">{{ $index + 1 }}</span>
                                    <input wire:model="steps.{{ $index }}.name" placeholder="Nama tahapan..."
                                        class="flex-1 bg-transparent border-none font-black text-xl focus:ring-0 p-0" />
                                </div>

                                <div
                                    class="bg-slate-50/50 dark:bg-black/20 p-6 rounded-3xl border border-slate-100 dark:border-white/5">
                                    <div class="flex justify-between items-end mb-4">
                                        <div class="flex flex-col">
                                            <span
                                                class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic">Contribution</span>
                                            <span class="text-indigo-600 text-lg font-black italic">
                                                <span x-text="stepsData[{{ $index }}].percent"></span>%
                                            </span>
                                        </div>
                                        <div class="text-right flex flex-col">
                                            <span
                                                class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic">Duration</span>
                                            <span class="text-slate-900 dark:text-white text-lg font-black italic"
                                                x-text="displayDuration"></span>
                                        </div>
                                    </div>

                                    <input type="range" min="0" max="100" step="1"
                                        x-on:input="
                                            let sliderVal = $event.target.value;
                                            stepsData[{{ $index }}].percent = Math.round((sliderVal / 100) * available);
                                        "
                                        x-bind:value="available > 0 ? (stepsData[{{ $index }}].percent / available) * 100 : 0"
                                        class="w-full h-1.5 accent-indigo-600 bg-slate-200 dark:bg-slate-800 rounded-lg appearance-none cursor-pointer">
                                </div>
                            </div>
                        @empty
                            <div
                                class="py-20 text-center opacity-40 italic font-black text-[10px] uppercase tracking-widest">
                                No Milestones</div>
                        @endforelse
                    </div>

                    {{-- TOTAL INDICATOR (REAL-TIME VIA ALPINE) --}}
                    <div
                        class="mb-8 p-6 bg-slate-100/50 dark:bg-white/5 rounded-[2rem] border border-slate-200/50 dark:border-white/5">
                        <div
                            class="flex justify-between items-center mb-3 text-[10px] font-black uppercase tracking-widest italic">
                            <span class="text-slate-500">Total Terdistribusi</span>
                            <span :class="totalUsed > 100 ? 'text-red-500' : 'text-indigo-600'">
                                <span x-text="totalUsed"></span>% / 100%
                            </span>
                        </div>
                        <div class="w-full h-3 bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-600 transition-all duration-300 shadow-[0_0_15px_rgba(79,70,229,0.5)]"
                                :style="'width: ' + Math.min(100, totalUsed) + '%'"></div>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-6 bg-indigo-600 text-white rounded-[2rem] font-black uppercase text-xs tracking-[0.4em] transition-all hover:scale-[1.01] shadow-2xl shadow-indigo-500/40">
                        Deploy Agenda
                    </button>
                </div>
            </div>
        </form>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(99, 102, 241, 0.2);
            border-radius: 10px;
        }

        input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none;
            height: 18px;
            width: 18px;
            border-radius: 50%;
            background: #4f46e5;
            cursor: pointer;
            border: 3px solid white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</div>
