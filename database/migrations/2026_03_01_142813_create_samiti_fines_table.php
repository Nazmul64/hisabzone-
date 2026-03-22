<?php
// ════════════════════════════════════════════════════════════
//  database/migrations/xxxx_create_samiti_fines_table.php
//  ✅ fine_id কলাম সরানো — এটাই 500 এর কারণ ছিল
//  ✅ samiti_member_id → samiti_members table সঠিক
// ════════════════════════════════════════════════════════════

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('samiti_fines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('samiti_member_id');
            $table->foreign('samiti_member_id')
                  ->references('id')
                  ->on('samiti_members')
                  ->onDelete('cascade');

            $table->string('reason');
            $table->decimal('amount', 12, 2);
            $table->date('date')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->date('paid_date')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('samiti_member_id');
            $table->index(['user_id', 'is_paid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('samiti_fines');
    }
};
