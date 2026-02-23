<?php
// ════════════════════════════════════════════════════════════════════
// ফাইল ১: database/migrations/2026_02_23_183650_create_budgets_table.php
// ════════════════════════════════════════════════════════════════════
// পুরনো migration টা DELETE করুন, এই নতুনটা দিন

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->decimal('limit_amount', 15, 2)->default(0);
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->timestamps();

            $table->foreign('category_id')
                  ->references('id')->on('categories')
                  ->cascadeOnDelete();
            $table->unique(['category_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
