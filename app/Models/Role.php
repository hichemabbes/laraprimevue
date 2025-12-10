<?php

namespace App\Models;

use App\Models\Utilisateurs\UserCentre;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = ['name', 'name_ar', 'guard_name', 'is_centre_role', 'statut', 'creer_par', 'desactive_par'];

    protected $casts = [
        'is_centre_role' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [];

    public function personnelCentres()
    {
        return $this->hasMany(UserCentre::class, 'role_id');
    }

    public function creerPar()
    {
        return $this->belongsTo(User::class, 'creer_par');
    }

    public function desactivePar()
    {
        return $this->belongsTo(User::class, 'desactive_par');
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar ?? 'Non défini',
            'guard_name' => $this->guard_name ?? 'web',
            'is_centre_role' => $this->is_centre_role,
            'statut' => $this->statut ?? 'actif',
            'creer_par' => $this->creerPar ? $this->creerPar->prenom_fr . ' ' . $this->creerPar->nom_fr : 'Non défini',
            'desactive_par' => $this->desactivePar ? $this->desactivePar->prenom_fr . ' ' . $this->desactivePar->nom_fr : 'Non défini',
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options | JSON_UNESCAPED_UNICODE);
    }
}
