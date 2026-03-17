<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nursery_fertilizers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('emoji', 10)->default('🧪');
                $table->string('total_quantity', 50)->nullable();
                $table->string('used_quantity', 50)->nullable();
                $table->string('unit', 20)->default('কেজি');
                $table->decimal('total_qty_value', 10, 2)->default(0);
                $table->decimal('used_qty_value', 10, 2)->default(0);
                $table->string('color', 20)->default('#795548');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
    }
    public function down(): void { Schema::dropIfExists('nursery_fertilizers'); }
};
