<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Calendrier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'calendrier';

    protected $fillable = [
        'titre',
        'date_debut',
        'date_fin',
        'description',
        'observation',
        'type',
        'color',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
    ];

    public static $types = ['événement', 'tâche', 'note', 'repos_formation'];
}
