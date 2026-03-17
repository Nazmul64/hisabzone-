<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
       Schema::create('nursery_employees', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('position', 100)->nullable();
                $table->string('phone', 20)->nullable();
                $table->string('email')->nullable();
                $table->decimal('salary', 10, 2)->nullable();
                $table->date('joining_date')->nullable();
                $table->string('emoji', 10)->default('👤');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('nursery_employees');
    }
};
