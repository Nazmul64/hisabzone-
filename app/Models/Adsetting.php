<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adsetting extends Model
{
    protected $table = 'adsettings';

    protected $fillable = [
        'admob_app_id',
        'ad_type',
        'ad_unit_id',
        'label',
        'trigger',
        'trigger_frequency',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'trigger_frequency' => 'integer',
    ];

    // ════════════════════════════════════════════════
    // Supported Ad Types
    // ════════════════════════════════════════════════
    public static function adTypes(): array
    {
        return [
            'banner',
            'interstitial',
            'rewarded',
            'rewarded_interstitial',
            'native_advanced',
            'app_open',
        ];
    }

    // ════════════════════════════════════════════════
    // Supported Trigger Options (🔥 Missing Method Fixed)
    // ════════════════════════════════════════════════
    public static function triggerOptions(): array
    {
        return [
            'app_start',
            'app_resume',
            'screen_open',
            'button_click',
            'after_action',
            'custom',
        ];
    }

    // ════════════════════════════════════════════════
    // Scope: Only Active Ads
    // ════════════════════════════════════════════════
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
