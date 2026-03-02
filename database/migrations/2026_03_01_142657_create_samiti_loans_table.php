<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('samiti_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('samiti_member_id')->constrained()->onDelete('cascade');
            $table->string('loan_id')->unique(); // L001...
            $table->decimal('loan_amount', 12, 2);
            $table->decimal('interest_rate', 5, 2)->default(10);
            $table->decimal('total_payable', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('purpose')->nullable();
            $table->date('issue_date');
            $table->date('due_date');
            $table->enum('status', ['active', 'paid', 'overdue'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('samiti_loans');
    }
};
