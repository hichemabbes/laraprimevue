<?php

namespace App\Models\Entreprises;

use App\Models\brillants\Specialites\Secteurs\Secteur;
use App\Models\brillants\Specialites\SousSecteurs\SousSecteur;
use App\Models\Liste;
use App\Models\Tuteur;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entreprise extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code_entre',
        'nom_fr',
        'nom_ar',
        'adresse_fr',
        'adresse_ar',
        'gouvernorat_id',
        'delegation_fr',
        'delegation_ar',
        'org_securite_sociale',
        'numero_affiliation',
        'secteur_id',
        'sous_secteur_id',
        'activite_principale_fr',
        'activite_principale_ar',
        'matricule_fiscale',
        'tel_entr_1',
        'tel_entr_2',
        'email_entr',
        'nom_responsable_fr',
        'prenom_responsable_fr',
        'nom_responsable_ar',
        'prenom_responsable_ar',
        'cin_responsable',
        'tel_responsable',
        'email_responsable',
        'observation',
    ];

    protected $dates = ['deleted_at'];

    public function secteur()
    {
        return $this->belongsTo(Secteur::class, 'secteur_id');
    }

    public function sousSecteur()
    {
        return $this->belongsTo(SousSecteur::class, 'sous_secteur_id');
    }

    public function gouvernorat()
    {
        return $this->belongsTo(Liste::class, 'gouvernorat_id');
    }

    public function tuteurs()
    {
        return $this->hasMany(Tuteur::class, 'entreprise_id');
    }
}
