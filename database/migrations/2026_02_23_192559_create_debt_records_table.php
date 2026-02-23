<?php
// ════════════════════════════════════════════════════════════════════
// ফাইল ৫: database/migrations/2026_02_23_192600_create_debt_records_table.php
// ════════════════════════════════════════════════════════════════════

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debt_records', function (Blueprint $table) {
            $table->id();
            $table->string('person_name');
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('type', ['given', 'taken'])->default('given');
            $table->date('date');
            $table->date('due_date')->nullable();
            $table->text('note')->nullable();
            $table->boolean('is_settled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_records');
    }
};
