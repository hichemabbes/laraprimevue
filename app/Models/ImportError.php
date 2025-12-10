<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportError extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_name',
        'row_data',
        'error_message',
    ];

    protected $casts = [
        'row_data' => 'array',
    ];
}
