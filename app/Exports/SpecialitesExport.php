<?php

namespace App\Exports;

use App\Models\brillants\Specialites\Specialite;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SpecialitesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Specialite::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'ID Année Formation',
            'Secteur ID',
            'Sous Secteur ID',
            'Code',
            'Nom AR',
            'Nom FR',
            'Type',
            'Homologation',
            'Date Arrêté',
            'Num Journal Officiel',
            'Date Journal Officiel',
            'Durée Formation',
            'Diplôme',
            'Heures Technique',
            'Heures Généraux',
            'Heures Total',
            'Date Création Spécialité',
            'Statut',
            'Observation',
            'Date Annulation Spécialité',
            'Created At',
            'Updated At',
            'Deleted At',
        ];
    }
}
