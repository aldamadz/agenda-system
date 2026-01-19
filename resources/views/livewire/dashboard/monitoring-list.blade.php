<?php
use App\Models\{Agenda, AgendaStep, ActivityLog, AgendaLog, User};
use Carbon\Carbon;
use function Livewire\Volt\{computed, state, usesFileUploads, on};

usesFileUploads();

state([
    'showIssueModal' => false,
    'selectedAgendaId' => null,
    'currentAgendaDeadline' => '',
    'issueCategory' => '',
    'issueDescription' => '',
    'requestedDeadline' => '',
    'showNoteModal' => false,
    'editingStepId' => null,
    'stepNote' => '',
]);

// Helper untuk membuat ActivityLog secara konsisten
$logActivity = function ($agendaId, $action, $description, $icon = 'document-text', $color = 'indigo') {
    ActivityLog::create([
        'user_id' => auth()->id(),
        'agenda_id' => $agendaId,
        'action' => $action,
        'description' => $description,
        'icon' => $icon,
        'color' => $color,
    ]);
};

$openIssueModal = function ($id) {
    $agenda = Agenda::findOrFail($id);
    $this->selectedAgendaId = $id;
    $this->currentAgendaDeadline = $agenda->deadline->format('d M Y, H:i');
    $this->reset(['issueCategory', 'issueDescription', 'requestedDeadline']);
    $this->showIssueModal = true;
};

$monitoringAgendas = computed(function () {
    $user = auth()->user();
    $isAdmin = $user->role === 'admin' || $user->is_admin;
    $isManager = User::where('parent_id', $user->id)->exists();

    $query = Agenda::with(['user', 'steps', 'logs'])->where('status', 'ongoing');

    if (!$isAdmin) {
        if ($isManager) {
            $query->where(function ($q) use ($user) {
                $q->where('approver_id', $user->id)->orWhere('user_id', $user->id);
            });
        } else {
            $query->where('user_id', $user->id);
        }
    }
    return $query->latest()->get();
});

$submitIssue = function () use ($logActivity) {
    $this->validate([
        'issueCategory' => 'required',
        'issueDescription' => 'required|min:5',
        'requestedDeadline' => 'nullable',
    ]);

    $agenda = Agenda::findOrFail($this->selectedAgendaId);
    $isExtension = !empty($this->requestedDeadline);
    $now = now();

    // 1. Masuk ke AgendaLog (Detail Agenda)
    AgendaLog::create([
        'agenda_id' => $this->selectedAgendaId,
        'log_date' => $now->format('Y-m-d'),
        'log_time' => $now->format('H:i:s'),
        'progress_note' => $isExtension ? 'MENUNGGU PERSETUJUAN' : 'LAPORAN KENDALA',
        'issue_category' => $isExtension ? 'Extension Request' : $this->issueCategory,
        'issue_description' => $this->issueDescription,
    ]);

    // 2. Masuk ke ActivityLog (Dashboard Feed)
    $actionType = $isExtension ? 'Request Extension' : 'Report Issue';
    $desc = auth()->user()->name . ' melaporkan kendala pada agenda: ' . $agenda->title;
    $logActivity($agenda->id, $actionType, $desc, 'exclamation-circle', 'amber');

    $this->reset(['showIssueModal', 'selectedAgendaId', 'issueCategory', 'issueDescription', 'requestedDeadline']);
    $this->dispatch('swal', ['icon' => 'success', 'title' => 'Laporan Terkirim']);
};

$clickStep = function ($stepId) use ($logActivity) {
    $step = AgendaStep::with('agenda')->findOrFail($stepId);

    if ($step->is_completed) {
        $step->update(['is_completed' => false, 'completed_at' => null, 'notes' => null]);

        // Log pembatalan
        $logActivity($step->agenda_id, 'Step Reopened', auth()->user()->name . ' membuka kembali langkah: ' . $step->step_name, 'arrow-path', 'slate');
    } else {
        $this->editingStepId = $stepId;
        $this->stepNote = '';
        $this->showNoteModal = true;
    }
};

$saveStepCompletion = function () use ($logActivity) {
    $step = AgendaStep::with('agenda')->findOrFail($this->editingStepId);
    $step->update(['is_completed' => true, 'completed_at' => now(), 'notes' => $this->stepNote]);

    // 1. Masuk ke AgendaLog
    AgendaLog::create([
        'agenda_id' => $step->agenda_id,
        'log_date' => now()->format('Y-m-d'),
        'log_time' => now()->format('H:i:s'),
        'issue_category' => 'Penyelesaian',
        'issue_description' => "Selesai: {$step->step_name}. Catatan: {$this->stepNote}",
        'progress_note' => 'Step Selesai',
    ]);

    // 2. Masuk ke ActivityLog
    $logActivity($step->agenda_id, 'Step Done', auth()->user()->name . ' menyelesaikan langkah: ' . $step->step_name, 'check-circle', 'emerald');

    $this->reset(['showNoteModal', 'editingStepId', 'stepNote']);
    $this->dispatch('swal', ['icon' => 'success', 'title' => 'Progres Disimpan']);
};

$completeAgenda = function ($id) use ($logActivity) {
    $agenda = Agenda::findOrFail($id);
    $agenda->update(['status' => 'completed']);

    // Log Penutupan Agenda
    $logActivity($id, 'Agenda Completed', auth()->user()->name . ' telah merampungkan agenda: ' . $agenda->title, 'archive-box', 'indigo');

    $this->dispatch('swal', ['icon' => 'success', 'title' => 'Agenda Selesai & Diarsipkan']);
};
?>

<section class="space-y-6 pt-10" wire:poll.60s>
    <div class="px-6">
        <flux:heading size="xl" class="!font-black tracking-tighter uppercase italic">Monitoring System
        </flux:heading>
        <flux:subheading>Pantau progres tim secara real-time.</flux:subheading>
    </div>

    <div class="grid grid-cols-1 gap-8 px-6 pb-20">
        @forelse ($this->monitoringAgendas as $agenda)
            @php
                $hasSteps = $agenda->steps->isNotEmpty();
                // KUNCI PERBAIKAN: Jika tidak ada steps, dianggap "siap arsip"
                $isAllStepsDone = $hasSteps ? $agenda->steps->every(fn($s) => $s->is_completed) : true;
                $isOverdue = $agenda->deadline->isPast() && !$isAllStepsDone;
            @endphp

            <div
                class="bg-white dark:bg-zinc-900 border-2 {{ $isOverdue ? 'border-red-500' : 'border-slate-100 dark:border-white/5' }} rounded-[2.5rem] shadow-xl">
                <div class="p-8 border-b border-slate-100 dark:border-white/5">
                    <div class="flex flex-col md:flex-row justify-between items-start gap-6">
                        <div class="flex items-start gap-6 flex-1">
                            <div
                                class="w-16 h-16 shrink-0 rounded-3xl flex items-center justify-center text-white bg-indigo-600 shadow-xl">
                                <flux:icon name="clipboard-document-list" variant="solid" class="w-8 h-8" />
                            </div>

                            <div class="space-y-3">
                                <h4 class="font-black text-2xl uppercase italic tracking-tight dark:text-white">
                                    {{ $agenda->title }}</h4>
                                <div class="flex flex-wrap gap-3 items-center text-[10px] font-black uppercase">
                                    <span
                                        class="bg-slate-100 dark:bg-white/5 px-3 py-1 rounded-full text-slate-500 tracking-widest">PIC:
                                        {{ $agenda->user->name }}</span>
                                    <span
                                        class="px-3 py-1 rounded-full {{ $isOverdue ? 'bg-red-500 text-white' : 'bg-slate-100 dark:bg-white/5 text-slate-500' }} tracking-widest">
                                        DEADLINE: {{ $agenda->deadline->format('d M Y, H:i') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            {{-- Lapor kendala hanya muncul jika agenda belum benar-benar selesai langkah kerjanya --}}
                            @if (auth()->id() === $agenda->user_id && ($hasSteps ? !$isAllStepsDone : true))
                                <flux:button variant="subtle" wire:click="openIssueModal({{ $agenda->id }})"
                                    class="!rounded-xl font-bold uppercase">
                                    Kendala
                                </flux:button>
                            @endif

                            {{-- Tombol Arsipkan muncul jika steps selesai ATAU tidak punya steps --}}
                            @if ($isAllStepsDone)
                                <flux:button variant="filled" color="indigo"
                                    wire:click="completeAgenda({{ $agenda->id }})"
                                    class="!rounded-xl font-black italic uppercase">
                                    Arsipkan
                                </flux:button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Tampilkan daftar langkah hanya jika ada datanya --}}
                @if ($hasSteps)
                    <div class="grid grid-cols-1 md:grid-cols-4 bg-slate-50/50 dark:bg-white/5">
                        @foreach ($agenda->steps as $step)
                            <div
                                class="p-6 border-r border-slate-100 dark:border-white/5 last:border-r-0 min-h-[100px]">
                                <div class="flex justify-between items-start">
                                    <span
                                        class="text-sm font-bold {{ $step->is_completed ? 'text-slate-300 line-through' : 'text-slate-700 dark:text-slate-200' }}">
                                        {{ $step->step_name }}
                                    </span>
                                    <input type="checkbox" @checked($step->is_completed)
                                        wire:click.stop="clickStep({{ $step->id }})"
                                        class="w-5 h-5 rounded-lg border-slate-300 text-indigo-600 cursor-pointer focus:ring-0">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="py-32 text-center border-4 border-dashed border-slate-100 dark:border-white/5 rounded-[3rem]">
                <p class="text-slate-400 font-black uppercase tracking-widest">Tidak ada agenda aktif</p>
            </div>
        @endforelse
    </div>

    {{-- MODAL LAPORAN --}}
    <flux:modal wire:model="showIssueModal" class="md:w-[450px] !rounded-[2.5rem]">
        <div class="space-y-6 p-2">
            <flux:heading size="lg" class="!font-black text-indigo-600 uppercase italic text-center">Laporan
                Kendala</flux:heading>
            <div class="space-y-4">
                <flux:select label="Kategori" wire:model="issueCategory">
                    <flux:select.option value="Teknis">Teknis</flux:select.option>
                    <flux:select.option value="Koordinasi">Koordinasi</flux:select.option>
                    <flux:select.option value="Lainnya">Lainnya</flux:select.option>
                </flux:select>
                <flux:input type="datetime-local" label="Deadline Baru (Opsional)" wire:model="requestedDeadline" />
                <flux:textarea label="Keterangan" wire:model="issueDescription" rows="4" />
            </div>
            <div class="flex gap-3">
                <flux:button variant="ghost" wire:click="$set('showIssueModal', false)" class="flex-1">Batal
                </flux:button>
                <flux:button variant="filled" color="indigo" wire:click="submitIssue"
                    class="flex-1 font-black uppercase">Kirim</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- MODAL CATATAN --}}
    <flux:modal wire:model="showNoteModal" class="md:w-[400px] !rounded-[2.5rem]">
        <div class="space-y-6 p-2">
            <flux:heading size="lg" class="!font-black uppercase italic text-center text-indigo-600">Catatan
                Progres</flux:heading>
            <flux:textarea label="Apa hasil pekerjaan Anda?" wire:model="stepNote" rows="3" />
            <div class="flex gap-3">
                <flux:button variant="ghost" wire:click="$set('showNoteModal', false)" class="flex-1">Batal
                </flux:button>
                <flux:button variant="filled" color="indigo" wire:click="saveStepCompletion"
                    class="flex-1 font-black uppercase">Simpan</flux:button>
            </div>
        </div>
    </flux:modal>
</section>
