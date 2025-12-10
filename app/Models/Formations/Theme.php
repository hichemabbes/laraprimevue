<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Theme extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'themes';

    protected $guarded = [];

    protected $casts = [
        'criteres_admission_fr' => 'array',
        'criteres_admission_ar' => 'array',
    ];

    /* Relations */

    public function formations()
    {
        return $this->hasMany(Formation::class, 'theme_id');
    }

    public function programmesThemes()
    {
        return $this->hasMany(ProgrammeTheme::class, 'theme_id');
    }
}
