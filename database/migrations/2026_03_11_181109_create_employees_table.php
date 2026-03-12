<?php
// ═══════════════════════════════════════════════════════
// 2024_01_01_000003_create_employees_table.php
// ═══════════════════════════════════════════════════════
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('designation', 100);
            $table->string('mobile', 20)->nullable();
            $table->text('address')->nullable();
            $table->decimal('monthly_salary', 10, 2)->default(0);
            $table->date('join_date')->nullable();
            $table->enum('status', ['active','inactive'])->default('active');
            $table->string('nid', 50)->nullable();
            $table->text('note')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('employees'); }
};
