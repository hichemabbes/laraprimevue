<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Liste extends Model
{
    use SoftDeletes;

    /**
     * Les attributs qui peuvent être assignés en masse.
     *
     * @var array
     */
    protected $fillable = [
        'nom_fr',
        'nom_ar',
        'options',
        'icone',
        'couleur',
        'fond',
        'ordre',
        'statut',
        'description',
    ];

    /**
     * Les attributs qui doivent être castés dans des types spécifiques.
     *
     * @var array
     */
    protected $casts = [
        'options' => 'array', // Cast JSON string to array
        'statut' => 'string', // Ensure statut is treated as string ('Actif', 'Inactif')
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Les attributs qui doivent être cachés lors de la sérialisation.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at',
    ];
}
