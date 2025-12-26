<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdNotifcation extends Model
{
    protected $fillable = [
        'title',
        'message',
        'icon',
        'is_active',
        'order',
        'redirect',
        'redirect_url'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'redirect' => 'boolean'
        ];
    }
}
