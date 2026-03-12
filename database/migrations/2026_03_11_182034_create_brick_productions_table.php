<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('brick_productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('worker_id')->nullable()->nullOnDelete();
            $table->string('worker_name')->nullable();
            $table->integer('raw_bricks')->default(0);
            $table->integer('burned_bricks')->default(0);
            $table->integer('broken_bricks')->default(0);
            $table->enum('brick_type', ['standard','premium','export'])->default('standard');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('brick_productions'); }
};
