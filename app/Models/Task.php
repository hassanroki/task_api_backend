<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['user_id', 'title', 'description', 'status', 'task_type'];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
