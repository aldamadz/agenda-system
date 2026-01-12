<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    // 1. Modifikasi Tabel Users untuk Hierarki
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'parent_id')) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        }
    });

    // 2. Tabel Agenda (Induk Tugas)
    Schema::create('agendas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
        $table->string('title');
        $table->text('description')->nullable();
        $table->dateTime('deadline');
        $table->string('status')->default('pending'); // pending, ongoing, waiting_approval, completed, canceled
        $table->text('manager_note')->nullable(); // Catatan revisi/alasan tolak
        $table->timestamps();
    });

    // 3. Tabel Agenda Steps (Dynamic Checklist)
    Schema::create('agenda_steps', function (Blueprint $table) {
        $table->id();
        $table->foreignId('agenda_id')->constrained()->cascadeOnDelete();
        $table->string('step_name');
        $table->boolean('is_completed')->default(false);
        $table->timestamps();
    });

    // 4. Tabel Agenda Logs (Progress Harian & Kendala)
    Schema::create('agenda_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('agenda_id')->constrained()->cascadeOnDelete();
        $table->date('log_date'); // Tanggal pelaporan
        $table->text('progress_note');
        $table->string('issue_category')->nullable(); // Opsional: Untuk acara tak terduga
        $table->text('issue_description')->nullable(); // Detail kendala
        $table->string('file_proof')->nullable(); // Foto bukti (opsional)
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('initial_agenda_system_tables');
    }
};
