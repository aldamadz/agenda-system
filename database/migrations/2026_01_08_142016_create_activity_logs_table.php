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
    Schema::create('activity_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Siapa yang melakukan
        $table->foreignId('agenda_id')->constrained()->onDelete('cascade'); // Agenda mana
        $table->string('action'); // created, approved, rejected, completed, rescheduled
        $table->text('description'); // Pesan detail: "Menyetujui agenda X"
        $table->string('icon')->nullable(); // Opsional: untuk icon berbeda tiap aksi
        $table->string('color')->nullable(); // Opsional: untuk warna badge
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
