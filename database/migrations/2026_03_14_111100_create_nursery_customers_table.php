<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
         Schema::create('nursery_customers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('phone', 20)->nullable();
                $table->string('email')->nullable();
                $table->string('address', 500)->nullable();
                $table->decimal('total_purchase', 12, 2)->default(0);
                $table->date('last_purchase_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
    }
    public function down(): void { Schema::dropIfExists('nursery_customers'); }
};
