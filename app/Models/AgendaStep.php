<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaStep extends Model
{
    // Tambahkan duration dan duration_minutes di sini
    protected $fillable = [
        'agenda_id',
        'step_name',
        'duration',
        'duration_minutes',
        'deadline',
        'is_completed',
        'completed_at',
        'notes'
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
        'duration_minutes' => 'integer', // Pastikan di-cast sebagai integer
    ];

    public function agenda()
    {
        return $this->belongsTo(Agenda::class);
    }
}
