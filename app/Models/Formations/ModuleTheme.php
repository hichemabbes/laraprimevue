<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleTheme extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'modules_themes';

    protected $guarded = [];

    protected $casts = [
        'heures_theoriques' => 'integer',
        'heures_pratiques'  => 'integer',
        'heures_evaluation' => 'integer',
    ];

    /* Relations */

    public function programmeTheme()
    {
        return $this->belongsTo(ProgrammeTheme::class, 'programme_theme_id');
    }

    public function documentsModulesThemes()
    {
        return $this->hasMany(DocumentModuleTheme::class, 'module_theme_id');
    }
}
