<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentProgrammeTheme extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'documents_programmes_themes';

    protected $guarded = [];

    /* Relations */

    public function programmeTheme()
    {
        return $this->belongsTo(ProgrammeTheme::class, 'programme_theme_id');
    }
}
