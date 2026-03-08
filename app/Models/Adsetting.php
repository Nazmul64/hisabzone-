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
    // Supported Ad Types  ← key => label
    // ════════════════════════════════════════════════
    public static function adTypes(): array
    {
        return [
            'banner'                => 'Banner',
            'interstitial'          => 'Interstitial',
            'rewarded'              => 'Rewarded',
            'rewarded_interstitial' => 'Rewarded Interstitial (Beta)',
            'native_advanced'       => 'Native Advanced',
            'app_open'              => 'App Open',
        ];
    }

    // ════════════════════════════════════════════════
    // Supported Trigger Options  ← key => label
    // ════════════════════════════════════════════════
    public static function triggerOptions(): array
    {
        return [
            'app_open'        => 'App Open / Launch',
            'level_complete'  => 'Level Complete',
            'every_x_actions' => 'Every X Actions',
            'page_change'     => 'Page / Screen Change',
            'manual'          => 'Manual (Code-controlled)',
        ];
    }

    // ════════════════════════════════════════════════
    // Accessor: Human-readable ad type label
    // ════════════════════════════════════════════════
    public function getAdTypeLabelAttribute(): string
    {
        return static::adTypes()[$this->ad_type] ?? $this->ad_type;
    }

    // ════════════════════════════════════════════════
    // Scope: Only Active Ads
    // ════════════════════════════════════════════════
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
