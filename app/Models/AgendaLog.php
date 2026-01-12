<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgendaLog extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model ini.
     * (Opsional jika nama tabel Anda sudah mengikuti standar jamak Laravel)
     */
    protected $table = 'agenda_logs';

    /**
     * Atribut yang dapat diisi secara massal (Mass Assignment).
     * Disesuaikan dengan struktur tabel: agenda_id, log_date, progress_note,
     * issue_category, issue_description, file_proof.
     */
    protected $fillable = [
        'agenda_id',
        'log_date',
        'log_time',
        'progress_note',
        'issue_category',
        'issue_description',
        'file_proof',
    ];

    /**
     * Konversi tipe data otomatis (Casting).
     * log_date dikonversi menjadi objek Carbon agar bisa diformat
     * dengan ->format() di Blade.
     */
    protected $casts = [
        'log_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke model Agenda.
     * Setiap log merujuk pada satu Agenda induk.
     */
    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class);
    }
}
