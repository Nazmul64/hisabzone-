<?php
// ════════════════════════════════════════════════════════════════════
// ফাইল ৩: database/migrations/2026_02_23_183752_create_wallets_table.php
// ════════════════════════════════════════════════════════════════════

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('icon')->default('account_balance_wallet');
            $table->string('color')->default('#0EA5E9');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
