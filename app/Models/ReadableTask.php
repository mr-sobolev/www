<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadableTask extends Model
{
    use HasFactory;

    protected $connection = 'pgsql_fdw';
    
    protected $casts = [
        'is_done' => 'boolean'
    ];
}
