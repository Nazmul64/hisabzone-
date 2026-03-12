<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {

        Schema::create('raw_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('unit', 50);
            $table->string('emoji', 10)->nullable();
            $table->decimal('stock', 15, 2)->default(0);
            $table->decimal('used', 15, 2)->default(0);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('low_stock_alert', 15, 2)->default(0);
            $table->timestamps();
        });


    }

    public function down(): void {

        Schema::dropIfExists('raw_materials');
    }
};
