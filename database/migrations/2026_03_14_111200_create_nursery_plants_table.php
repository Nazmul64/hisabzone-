<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nursery_plants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('nursery_plant_category_id')->nullable()->constrained('nursery_plant_categories')->nullOnDelete();
                $table->string('name');
                $table->string('scientific_name')->nullable();
                $table->string('category')->nullable();
                $table->decimal('price', 10, 2)->default(0);
                $table->integer('quantity')->default(0);
                $table->integer('min_stock')->default(5);
                $table->string('age', 50)->nullable();
                $table->string('size', 50)->nullable();
                $table->string('emoji', 10)->default('🌱');
                $table->text('description')->nullable();
                $table->timestamps();
            });
    }
    public function down(): void { Schema::dropIfExists('nursery_plants'); }
};
