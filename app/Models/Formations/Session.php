<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Session extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sessions';

    protected $guarded = [];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
    ];

    /* Relations */

    public function anneeFormation()
    {
        return $this->belongsTo(AnneeFormation::class, 'annee_formation_id');
    }

    public function formations()
    {
        return $this->hasMany(Formation::class, 'session_id');
    }
}
