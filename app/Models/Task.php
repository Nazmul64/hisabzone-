<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
   protected $fillable = ['title', 'due_date', 'priority', 'note', 'is_done'];

    protected $casts = ['is_done' => 'boolean'];
}
