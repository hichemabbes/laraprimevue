<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgrammeTheme extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'programmes_themes';

    protected $guarded = [];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
    ];

    /* Relations */

    public function theme()
    {
        return $this->belongsTo(Theme::class, 'theme_id');
    }

    public function modulesThemes()
    {
        return $this->hasMany(ModuleTheme::class, 'programme_theme_id');
    }

    public function documentsProgrammesThemes()
    {
        return $this->hasMany(DocumentProgrammeTheme::class, 'programme_theme_id');
    }
}
