<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Historique extends Model
{
    protected $table = 'historiques';

    protected $fillable = [
        'nom_table',
        'id_enregistrement',
        'nom_champ',
        'ancienne_valeur',
        'nouvelle_valeur',
        'id_table_liee',
        'nom_table_liee',
        'user_id',
    ];

    /**
     * Relation avec l'utilisateur qui a effectué la modification.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
