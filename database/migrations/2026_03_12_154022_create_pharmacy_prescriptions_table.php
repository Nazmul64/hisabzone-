<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pharmacy_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('pharmacy_customers')->nullOnDelete();
            $table->string('patient_name');
            $table->string('doctor_name')->nullable();
            $table->date('date');
            $table->json('medicines')->nullable();
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pharmacy_prescriptions'); }
};
