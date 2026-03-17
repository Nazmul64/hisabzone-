<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nursery_garden_areas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('emoji', 10)->default('🌳');
                $table->integer('plant_count')->default(0);
                $table->string('description', 500)->nullable();
                $table->string('color', 20)->default('#2E7D32');
                $table->timestamps();
            });
    }
    public function down(): void { Schema::dropIfExists('nursery_garden_areas'); }
};
