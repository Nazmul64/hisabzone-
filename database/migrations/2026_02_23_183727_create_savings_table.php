<?php
// ════════════════════════════════════════════════════════════════════
// ফাইল ২: database/migrations/2026_02_23_183727_create_savings_table.php
// ════════════════════════════════════════════════════════════════════

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('target_amount', 15, 2)->default(0);
            $table->decimal('saved_amount',  15, 2)->default(0);
            $table->date('deadline')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings');
    }
};
