<?php
namespace App\Models\Centre;

use App\Models\brillants\Specialites\Specialite;
use App\Models\Liste;
use App\Models\User;
use App\Models\Utilisateurs\PersonnelsCentres\PersonnelCentre;
use App\Models\Utilisateurs\UserCentre;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Centre extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'nom_fr',
        'nom_ar',
        'adresse_fr',
        'adresse_ar',
        'telephone_1',
        'telephone_2',
        'fax_1',
        'fax_2',
        'email',
        'gouvernorat_fr',
        'gouvernorat_ar',
        'type_centre_fr',
        'type_centre_ar',
        'classe_fr',
        'classe_ar',
        'economat_fr',
        'economat_ar',
        'etat_fr',
        'etat_ar',
        'statut_fr',
        'statut_ar',
        'date_creation',
        'date_ouverture',
        'date_fermeture',
        'observation_fr',
        'observation_ar',
        'logo',
    ];

    public function specialites()
    {
        return $this->belongsToMany(Specialite::class, 'specialites_centres')
            ->withPivot('statut', 'observation', 'date_debut', 'date_fin')
            ->withTimestamps()
            ->whereNull('specialites_centres.deleted_at');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_centres')
            ->using(UserCentre::class)
            ->withPivot(['role_id', 'type_affectation_fr', 'type_affectation_ar', 'date_debut', 'date_fin', 'statut'])
            ->withTimestamps();
    }

    public function personnels()
    {
        return $this->hasManyThrough(PersonnelCentre::class, UserCentre::class, 'centre_id', 'user_centre_id');
    }

    // Ajout de la relation pour accéder aux personnelsCentre via les UserCentre
    public function personnelCentre()
    {
        return $this->hasManyThrough(
            PersonnelCentre::class,
            UserCentre::class,
            'centre_id',
            'user_centre_id',
            'id',
            'id'
        );
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
        Log::debug("Validation du champ '$field' avec la valeur '$value' dans la liste '$listName'. Options: " . json_encode($options));
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
