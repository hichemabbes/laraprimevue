<?php

namespace App\Exports;

use App\Models\Centre\Centre;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CentresExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Centre::withTrashed()->get();
    }

    public function headings(): array
    {
        return [
            'Code',
            'Nom (FR)',
            'Nom (AR)',
            'Adresse (FR)',
            'Adresse (AR)',
            'Téléphone 1',
            'Téléphone 2',
            'Fax 1',
            'Fax 2',
            'Email',
            'Gouvernorat (FR)',
            'Gouvernorat (AR)',
            'Type de centre (FR)',
            'Type de centre (AR)',
            'Classe (FR)',
            'Classe (AR)',
            'Économat (FR)',
            'Économat (AR)',
            'Logo',
            'État (FR)',
            'État (AR)',
            'Statut (FR)',
            'Statut (AR)',
            'Date de création',
            'Date d\'ouverture',
            'Date de fermeture',
            'Observation (FR)',
            'Observation (AR)',
        ];
    }

    public function map($centre): array
    {
        return [
            $centre->code,
            $centre->nom_fr,
            $centre->nom_ar,
            $centre->adresse_fr,
            $centre->adresse_ar,
            $centre->telephone_1,
            $centre->telephone_2,
            $centre->fax_1,
            $centre->fax_2,
            $centre->email,
            $centre->gouvernorat_fr,
            $centre->gouvernorat_ar,
            $centre->type_centre_fr,
            $centre->type_centre_ar,
            $centre->classe_fr,
            $centre->classe_ar,
            $centre->economat_fr,
            $centre->economat_ar,
            $centre->logo,
            $centre->etat_fr,
            $centre->etat_ar,
            $centre->statut_fr,
            $centre->statut_ar,
            $centre->date_creation,
            $centre->date_ouverture,
            $centre->date_fermeture,
            $centre->observation_fr,
            $centre->observation_ar,
        ];
    }
}
