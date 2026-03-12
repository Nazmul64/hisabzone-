<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('person_type', ['employee','worker']);
            $table->unsignedBigInteger('person_id');
            $table->string('person_name');
            $table->string('designation', 100)->nullable();
            $table->string('month', 50)->nullable();
            $table->integer('month_number');
            $table->integer('year_number');
            $table->decimal('base_salary', 10, 2)->default(0);
            $table->decimal('bonus', 10, 2)->default(0);
            $table->decimal('advance', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2)->default(0);
            $table->boolean('is_paid')->default(false);
            $table->date('paid_date')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('salaries'); }
};
