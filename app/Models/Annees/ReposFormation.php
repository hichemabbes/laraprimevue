<?php

namespace App\Models\Annees;

use App\Models\Liste;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class ReposFormation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'repos_formations';

    protected $fillable = [
        'annee_formation_id',
        'type_repos_formation_fr',
        'type_repos_formation_ar',
        'date_debut',
        'nombre_jour',
        'date_fin',
        'description',
        'statut',
        'ordre',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'deleted_at' => 'datetime',
    ];

    public function anneeFormation()
    {
        return $this->belongsTo(AnneeFormation::class, 'annee_formation_id');
    }

    public function mapTypeAr()
    {
        if (! $this->type_repos_formation_fr) {
            return null;
        }

        $liste = Liste::where('nom_fr', 'Vacances et JF')->where('statut', 'Actif')->first();
        if (! $liste || ! is_array($liste->options)) {
            Log::warning("Liste 'Vacances et JF' introuvable ou options invalides pour mapping type_repos_formation_ar.");

            return null;
        }

        foreach ($liste->options as $option) {
            if (isset($option['nom_fr']) && $option['nom_fr'] === $this->type_repos_formation_fr) {
                return $option['nom_ar'] ?? null;
            }
        }

        Log::warning("Aucune correspondance trouvée pour type_repos_formation_fr: {$this->type_repos_formation_fr}");

        return null;
    }

    public static function validateListField(string $field, ?string $value, string $listName): void
    {
        if (empty($value)) {
            return;
        }

        $liste = Liste::where('nom_fr', $listName)->where('statut', 'Actif')->first();
        if (! $liste) {
            Log::warning("Liste '$listName' introuvable ou inactive pour la validation du champ '$field'.");
            throw new \Exception("La liste '$listName' n'existe pas ou est inactive.");
        }

        $options = $liste->options;
        if (! is_array($options)) {
            Log::warning("Options invalides pour la liste '$listName' lors de la validation du champ '$field'.");
            throw new \Exception("Les options de la liste '$listName' ne sont pas valides.");
        }

        Log::debug("Validation du champ '$field' avec la valeur '$value' dans la liste '$listName'. Options: ".json_encode($options));

        foreach ($options as $option) {
            if (isset($option['nom_fr']) && $option['nom_fr'] === $value) {
                Log::debug("Validation réussie pour le champ '$field' avec la valeur '$value' dans la liste '$listName'.");

                return;
            }
        }

        Log::warning("Échec de la validation pour le champ '$field' avec la valeur '$value' dans la liste '$listName'. Aucune correspondance trouvée.");
        throw new \Exception("La valeur '$value' n'est pas valide pour le champ $field.");
    }
}
