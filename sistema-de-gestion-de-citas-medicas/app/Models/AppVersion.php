<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $fillable = [
        'version',
        'run_number',
        'download_url',
        'notas',
    ];

    protected $casts = [
        'run_number' => 'integer',
    ];
}
