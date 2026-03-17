<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
         Schema::create('nursery_plant_care_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('nursery_plant_id')->nullable()->constrained('nursery_plants')->nullOnDelete();
                $table->string('plant_name')->nullable();
                $table->enum('care_type', ['watering', 'pruning', 'treatment', 'fertilizing', 'other']);
                $table->string('care_type_label', 100)->nullable();
                $table->string('emoji', 10)->default('🌿');
                $table->date('date');
                $table->text('note')->nullable();
                $table->timestamps();
            });
    }
    public function down(): void { Schema::dropIfExists('nursery_plant_care_records'); }
};
