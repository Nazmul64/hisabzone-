<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('transports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_id')->nullable()->nullOnDelete();
            $table->date('date');
            $table->string('vehicle_no', 50);
            $table->string('driver_name')->nullable();
            $table->string('driver_mobile', 20)->nullable();
            $table->decimal('fare', 10, 2)->default(0);
            $table->string('destination', 255)->nullable();
            $table->integer('brick_quantity')->default(0);
            $table->enum('status', ['pending','ongoing','delivered'])->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('transports'); }
};
