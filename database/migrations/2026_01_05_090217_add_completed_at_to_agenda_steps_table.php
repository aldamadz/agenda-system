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
    Schema::table('agenda_steps', function (Blueprint $table) {
        // Menambahkan kolom completed_at setelah is_completed
        $table->timestamp('completed_at')->nullable()->after('is_completed');
    });
}

public function down(): void
{
    Schema::table('agenda_steps', function (Blueprint $table) {
        $table->dropColumn('completed_at');
    });
}
};
