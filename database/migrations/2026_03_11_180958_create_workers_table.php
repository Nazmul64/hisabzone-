<?php
// ═══════════════════════════════════════════════════════════════════
// FILE: database/migrations/2024_01_01_000002_create_workers_table.php
// ═══════════════════════════════════════════════════════════════════
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('mobile', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('work_type', 100)->nullable();
            $table->decimal('daily_wage', 10, 2)->default(0);
            $table->date('join_date')->nullable();
            $table->enum('status', ['active','inactive'])->default('active');
            $table->string('nid', 50)->nullable();
            $table->text('note')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('workers'); }
};
