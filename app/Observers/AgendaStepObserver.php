<?php

namespace App\Observers;

use App\Models\AgendaStep;
use App\Models\AgendaLog;

class AgendaStepObserver
{
    public function created(AgendaStep $agendaStep): void
    {
        $this->createLog($agendaStep);
    }

    public function updated(AgendaStep $agendaStep): void
    {
        if ($agendaStep->wasChanged(['is_completed', 'notes', 'deadline'])) {
            $this->createLog($agendaStep);
        }
    }

protected function createLog(AgendaStep $step): void
{
    AgendaLog::create([
        'agenda_id'         => $step->agenda_id,
        'log_date'          => now()->toDateString(), // Hanya Tanggal (YYYY-MM-DD)
        'log_time'          => now()->toTimeString(), // Hanya Jam (HH:MM:SS)
        'progress_note'     => $step->notes ?? '-',
        'issue_category'    => $step->is_completed ? 'Penyelesaian' : 'Update Progres',
        'issue_description' => "Langkah: " . $step->step_name,
        'file_proof'        => null,
    ]);
}
}
