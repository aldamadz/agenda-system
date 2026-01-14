<?php
use App\Models\{Agenda, AgendaStep, ActivityLog};
use Carbon\Carbon;
use function Livewire\Volt\{computed, state, usesFileUploads, on};

usesFileUploads();

state([
    'showNoteModal' => false,
    'editingStepId' => null,
    'stepNote' => '',
    'attachment' => null,
]);

// Query Agenda berdasarkan Role
$monitoringAgendas = computed(function () {
    $user = auth()->user();
    $query = Agenda::with(['user', 'steps'])->where('status', 'ongoing');

    return $user->parent_id === null ? $query->where('approver_id', $user->id)->latest()->get() : $query->where('user_id', $user->id)->latest()->get();
});

// Menangani Klik Checkbox
$clickStep = function ($stepId) {
    $step = AgendaStep::findOrFail($stepId);

    if ($step->is_completed) {
        // Logika Un-check: Langsung eksekusi tanpa modal
        $step->update([
            'is_completed' => false,
            'completed_at' => null,
            'attachment' => null,
            'notes' => null,
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'agenda_id' => $step->agenda_id,
            'action' => 'Update',
            'description' => auth()->user()->name . " membatalkan penyelesaian tahap: \"{$step->step_name}\".",
            'icon' => 'arrow-path',
            'color' => 'slate',
        ]);

        // Reset state modal agar tidak ikut terbuka
        $this->reset(['showNoteModal', 'editingStepId', 'stepNote', 'attachment']);
        $this->dispatch('refresh-stats');
    } else {
        // Logika Check: Buka Modal Konfirmasi
        $this->editingStepId = $stepId;
        $this->stepNote = '';
        $this->attachment = null;
        $this->showNoteModal = true;
    }
};

// Simpan dari Modal
$saveStepCompletion = function () {
    $this->validate([
        'stepNote' => 'required|min:3',
        'attachment' => 'nullable|max:2048',
    ]);

    $step = AgendaStep::findOrFail($this->editingStepId);
    $data = [
        'is_completed' => true,
        'completed_at' => now(),
        'notes' => $this->stepNote,
    ];

    if ($this->attachment) {
        $path = $this->attachment->store('attachments', 'public');
        $data['attachment'] = $path;
    }

    $step->update($data);

    ActivityLog::create([
        'user_id' => auth()->id(),
        'agenda_id' => $step->agenda_id,
        'action' => 'Progress',
        'description' => auth()->user()->name . " menyelesaikan tahap: \"{$step->step_name}\".",
        'icon' => 'check-circle',
        'color' => 'emerald',
    ]);

    $this->reset(['showNoteModal', 'editingStepId', 'stepNote', 'attachment']);
    $this->dispatch('refresh-stats');
};

$cancelCompletion = function () {
    $this->reset(['showNoteModal', 'editingStepId', 'stepNote', 'attachment']);
};

$completeAgenda = function ($id) {
    $agenda = Agenda::findOrFail($id);
    $agenda->update(['status' => 'completed']);

    ActivityLog::create([
        'user_id' => auth()->id(),
        'agenda_id' => $agenda->id,
        'action' => 'Completion',
        'description' => auth()->user()->name . " telah menyelesaikan dan mengarsipkan agenda: \"{$agenda->title}\".",
        'icon' => 'archive-box-arrow-down',
        'color' => 'indigo',
    ]);

    $this->dispatch('refresh-stats');
    $this->dispatch('swal', icon: 'success', title: 'Berhasil', text: 'Agenda telah diarsipkan.');
};

$rejectAgenda = function ($id) {
    Agenda::findOrFail($id)->delete();
    $this->dispatch('refresh-stats');
};

on(['refresh-stats' => function () {}]);
?>

<section class="space-y-6 pt-10" wire:poll.30s>
    {{-- Header --}}
    <div class="flex items-center justify-between px-2">
        <div>
            <flux:heading size="xl" class="!font-black tracking-tighter uppercase italic">
                {{ auth()->user()->parent_id === null ? 'Monitoring Progres Tim' : 'Agenda Kerja Saya' }}
            </flux:heading>
            <flux:subheading>Pantau real-time setiap tahapan pekerjaan</flux:subheading>
        </div>
        <div
            class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-200 dark:via-white/10 to-transparent mx-8 hidden md:block">
        </div>
    </div>

    {{-- Agenda Cards Container --}}
    <div class="grid grid-cols-1 gap-8">
        @foreach ($this->monitoringAgendas as $agenda)
            @php
                $isAllStepsDone = $agenda->steps->every(fn($step) => $step->is_completed);
                $isOverdue = $agenda->deadline->isPast() && !$isAllStepsDone;
            @endphp

            <div
                class="bg-white dark:bg-white/5 rounded-[2.5rem] overflow-hidden border border-slate-200 dark:border-white/10 shadow-xl relative group">
                @if ($isAllStepsDone)
                    <div class="absolute inset-0 bg-emerald-500/5 pointer-events-none animate-pulse"></div>
                @endif

                {{-- CARD HEADER --}}
                <div class="p-8 border-b border-slate-100 dark:border-white/10 relative z-10">
                    <div class="flex flex-col md:flex-row justify-between items-start gap-6">
                        <div class="flex items-start gap-5 flex-1">
                            <div
                                class="w-14 h-14 shrink-0 rounded-2xl {{ $isAllStepsDone ? 'bg-emerald-500 text-white rotate-6' : 'bg-indigo-600 text-white' }} flex items-center justify-center shadow-lg">
                                @if ($isAllStepsDone)
                                    <flux:icon.check-badge variant="solid" class="w-8 h-8" />
                                @else
                                    <flux:icon.clipboard-document-list variant="solid" class="w-8 h-8" />
                                @endif
                            </div>

                            <div class="space-y-3 flex-1">
                                <h4
                                    class="font-black text-xl text-slate-900 dark:text-white leading-none italic uppercase tracking-tight">
                                    {{ $agenda->title }}
                                </h4>
                                @if ($agenda->description)
                                    <p
                                        class="mt-2 text-sm text-slate-500 dark:text-slate-400 font-medium leading-relaxed max-w-4xl line-clamp-2">
                                        {{ $agenda->description }}
                                    </p>
                                @endif

                                <div
                                    class="flex flex-wrap items-center gap-x-5 gap-y-2 pt-1 text-[10px] font-black uppercase tracking-[0.15em]">
                                    <span class="flex items-center gap-1.5 text-slate-400">
                                        <flux:icon.user variant="micro" class="text-indigo-500" />
                                        PIC: {{ $agenda->user->name }}
                                    </span>
                                    <div
                                        class="flex items-center gap-1.5 {{ $isOverdue ? 'text-red-500 animate-pulse' : 'text-amber-500' }}">
                                        <flux:icon.calendar-days variant="micro" />
                                        <span>Deadline: {{ $agenda->deadline->translatedFormat('d F Y') }}</span>
                                    </div>
                                    <span class="text-indigo-500 flex items-center gap-1.5">
                                        <flux:icon.list-bullet variant="micro" />
                                        {{ $agenda->steps->where('is_completed', true)->count() }} /
                                        {{ $agenda->steps->count() }} TAHAP SELESAI
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0 self-center md:self-start">
                            @if ($isAllStepsDone)
                                <flux:button size="sm" color="emerald" variant="primary"
                                    class="!rounded-xl font-black uppercase italic tracking-widest animate-bounce shadow-lg shadow-emerald-500/20"
                                    wire:click="completeAgenda({{ $agenda->id }})"
                                    wire:confirm="Arsipkan agenda ini?">
                                    Arsipkan Agenda
                                </flux:button>
                            @endif
                            @if (auth()->user()->parent_id === null)
                                <flux:button variant="ghost" size="sm" icon="trash" color="red"
                                    class="!rounded-xl border border-red-500/10 hover:bg-red-50 dark:hover:bg-red-500/10"
                                    wire:click="rejectAgenda({{ $agenda->id }})"
                                    wire:confirm="Hapus seluruh agenda ini?" />
                            @endif
                        </div>
                    </div>
                </div>

                {{-- STEPS GRID --}}
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 relative z-10 bg-slate-50/30 dark:bg-transparent">
                    @foreach ($agenda->steps as $step)
                        <div
                            class="p-6 flex flex-col justify-between min-h-[160px] border-b md:border-b-0 md:border-r border-slate-100 dark:border-white/5 last:border-r-0 hover:bg-white dark:hover:bg-white/5 transition-all duration-300">
                            <div class="space-y-4">
                                <div class="flex justify-between items-start gap-4">
                                    <p
                                        class="text-sm leading-tight {{ $step->is_completed ? 'text-slate-400 line-through opacity-60' : 'text-slate-800 dark:text-white font-bold' }}">
                                        {{ $step->step_name }}
                                    </p>

                                    {{-- FIX: Checkbox dengan wire:key unik dan prevent --}}
                                    <input type="checkbox"
                                        wire:key="step-{{ $step->id }}-{{ $step->is_completed ? 'done' : 'pending' }}"
                                        @checked($step->is_completed)
                                        wire:click.prevent="clickStep({{ $step->id }})"
                                        class="w-5 h-5 rounded-lg border-slate-300 dark:border-white/10 text-indigo-600 cursor-pointer transition-all duration-300 transform active:scale-125 focus:ring-indigo-500">
                                </div>

                                @if ($step->attachment)
                                    <a href="{{ asset('storage/' . $step->attachment) }}" target="_blank"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-[9px] font-black uppercase hover:bg-indigo-600 hover:text-white transition-all duration-300">
                                        <flux:icon.paper-clip variant="micro" /> Lihat Bukti
                                    </a>
                                @endif
                            </div>

                            <div
                                class="mt-6 pt-4 border-t border-slate-100 dark:border-white/5 flex items-center justify-between text-[10px] font-black uppercase tracking-tighter">
                                <div class="flex items-center gap-2 text-slate-400">
                                    <flux:icon.clock variant="micro" />
                                    <span>Target: {{ $step->deadline?->format('H:i') ?? '--:--' }}</span>
                                </div>
                                @if ($step->is_completed)
                                    <div class="flex items-center gap-1 text-emerald-500 italic">
                                        <flux:icon.check-circle variant="micro" />
                                        <span>Selesai</span>
                                    </div>
                                @else
                                    <span class="text-slate-300 dark:text-slate-600">Pending</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- MODAL KONFIRMASI --}}
    <flux:modal wire:model="showNoteModal" class="md:w-[450px] !rounded-[2.5rem]" variant="flyout"
        x-on:close="$wire.cancelCompletion()">
        <div class="space-y-6 p-2">
            <div class="text-center">
                <div
                    class="w-16 h-16 bg-indigo-500/20 rounded-3xl flex items-center justify-center text-indigo-500 mx-auto mb-4 rotate-3">
                    <flux:icon.document-check variant="solid" class="w-8 h-8" />
                </div>
                <flux:heading size="lg" class="!font-black uppercase italic">Konfirmasi Progres</flux:heading>
                <flux:subheading>Isi catatan untuk menyelesaikan tahap ini</flux:subheading>
            </div>

            <div class="space-y-4">
                <flux:textarea label="Catatan Pekerjaan" wire:model="stepNote" placeholder="Apa yang Anda kerjakan?"
                    rows="3" />
                <div
                    class="p-4 rounded-3xl bg-slate-50 dark:bg-white/5 border border-dashed border-slate-200 dark:border-white/10 transition-all hover:border-indigo-400">
                    <flux:label class="mb-2 block !font-bold !text-[10px] uppercase">Upload Bukti (Opsional)
                    </flux:label>
                    <input type="file" wire:model="attachment"
                        class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 transition-all" />

                    <div wire:loading wire:target="attachment" class="mt-3">
                        <div class="flex items-center gap-2 text-[10px] text-indigo-500 font-bold">
                            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Mengunggah dokumen...
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <flux:button variant="ghost" class="flex-1 !rounded-2xl" wire:click="cancelCompletion">Batal
                </flux:button>
                <flux:button variant="primary" color="indigo" class="flex-1 !rounded-2xl font-black"
                    wire:click="saveStepCompletion" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveStepCompletion">Kirim Progres</span>
                    <span wire:loading wire:target="saveStepCompletion">Menyimpan...</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>
</section>
