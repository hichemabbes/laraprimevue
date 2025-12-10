<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Formation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'formations';

    protected $guarded = [];

    /* Relations */

    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id');
    }

    public function specialite()
    {
        return $this->belongsTo(Specialite::class, 'specialite_id');
    }

    public function theme()
    {
        return $this->belongsTo(Theme::class, 'theme_id');
    }
}
