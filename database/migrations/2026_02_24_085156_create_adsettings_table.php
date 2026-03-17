<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adsettings', function (Blueprint $table) {

            $table->id();

            // AdMob App ID
            $table->string('admob_app_id');

            // Ad Type
            $table->enum('ad_type', [
                'banner',
                'interstitial',
                'rewarded_interstitial',
                'rewarded',
                'native_advanced',
                'app_open',
            ]);

            // Ad Unit ID
            $table->string('ad_unit_id');

            // Optional Label (for admin reference)
            $table->string('label')->nullable();

            // Trigger Type
            $table->enum('trigger', [
                'app_open',
                'level_complete',
                'every_x_actions',
                'page_change',
                'manual',
            ])->nullable();

            // ✅ default: 1 — 0 দিলে Flutter এ ad show হয় না
            $table->unsignedInteger('trigger_frequency')->default(1);

            // Status
            $table->boolean('is_active')->default(false);

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adsettings');
    }
};
