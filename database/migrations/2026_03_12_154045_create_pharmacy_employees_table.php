<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pharmacy_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('position', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->decimal('salary', 10, 2)->default(0);
            $table->date('joining_date')->nullable();
            $table->text('address')->nullable();
            $table->string('emoji', 10)->nullable();
            $table->string('color', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pharmacy_employees'); }
};
