<?php

// ════════════════════════════════════════════════════════════════════
// ফাইল ২: app/Models/Profile.php
// ════════════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\FinanceManage;
use App\Models\Saving;
use App\Models\Task;

class Profile extends Model
{
    protected $fillable = ['name', 'email', 'phone'];

    // ── সবসময় শুধু একটাই row থাকবে (id = 1) ──────────────────────
    public static function getSingleton(): static
    {
        return static::firstOrCreate(
            ['id' => 1],
            ['name' => 'ব্যবহারকারী', 'email' => null, 'phone' => null]
        );
    }
}
