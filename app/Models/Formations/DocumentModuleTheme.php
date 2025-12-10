<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentModuleTheme extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'documents_modules_themes';

    protected $guarded = [];

    /* Relations */

    public function moduleTheme()
    {
        return $this->belongsTo(ModuleTheme::class, 'module_theme_id');
    }
}
