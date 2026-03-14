<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pharmacy_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('return_type', ['customer', 'supplier']);
            $table->string('party_name');
            $table->string('medicine_name');
            $table->integer('quantity');
            $table->decimal('amount', 10, 2);
            $table->text('reason')->nullable();
            $table->date('date');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pharmacy_returns'); }
};
