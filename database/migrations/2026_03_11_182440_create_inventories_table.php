<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('brick_type', 50);
            $table->string('label', 100)->nullable();
            $table->string('emoji', 10)->nullable();
            $table->integer('total')->default(0);
            $table->integer('sold')->default(0);
            $table->integer('available')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'brick_type']);
        });
    }
    public function down(): void { Schema::dropIfExists('inventories'); }
};
