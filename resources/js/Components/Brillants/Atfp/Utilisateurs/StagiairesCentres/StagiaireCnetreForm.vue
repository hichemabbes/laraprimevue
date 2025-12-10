<template>
    <div class="stagiaire-form">
        <form @submit.prevent="submitForm">
            <div class="form-section">
                <h3>Informations Personnelles</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom_fr">Nom (FR)</label>
                        <input
                            id="nom_fr"
                            v-model="formData.user.nom_fr"
                            type="text"
                            required
                        />
                    </div>
                    <div class="form-group">
                        <label for="prenom_fr">Prénom (FR)</label>
                        <input
                            id="prenom_fr"
                            v-model="formData.user.prenom_fr"
                            type="text"
                            required
                        />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nom_ar">Nom (AR)</label>
                        <input
                            id="nom_ar"
                            v-model="formData.user.nom_ar"
                            type="text"
                        />
                    </div>
                    <div class="form-group">
                        <label for="prenom_ar">Prénom (AR)</label>
                        <input
                            id="prenom_ar"
                            v-model="formData.user.prenom_ar"
                            type="text"
                        />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cin">CIN</label>
                        <input
                            id="cin"
                            v-model="formData.user.cin"
                            type="text"
                            required
                        />
                    </div>
                    <div class="form-group">
                        <label for="date_naissance">Date de Naissance</label>
                        <input
                            id="date_naissance"
                            v-model="formData.user.date_naissance"
                            type="date"
                        />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="lieu_naissance_fr"
                            >Lieu de Naissance (FR)</label
                        >
                        <input
                            id="lieu_naissance_fr"
                            v-model="formData.user.lieu_naissance_fr"
                            type="text"
                        />
                    </div>
                    <div class="form-group">
                        <label for="lieu_naissance_ar"
                            >Lieu de Naissance (AR)</label
                        >
                        <input
                            id="lieu_naissance_ar"
                            v-model="formData.user.lieu_naissance_ar"
                            type="text"
                        />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nationalite">Nationalité</label>
                        <input
                            id="nationalite"
                            v-model="formData.user.nationalite"
                            type="text"
                        />
                    </div>
                    <div class="form-group">
                        <label for="genre_id">Genre</label>
                        <select id="genre_id" v-model="formData.user.genre_id">
                            <option value="">Sélectionner...</option>
                            <option
                                v-for="genre in genreOptions"
                                :key="genre.id"
                                :value="genre.id"
                            >
                                {{ genre.nom }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input
                            id="email"
                            v-model="formData.user.email"
                            type="email"
                        />
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input
                            id="password"
                            v-model="formData.user.password"
                            type="password"
                            :required="!isEdit"
                        />
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>Informations du Stagiaire</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="num_extrait_naissance"
                            >Numéro Extrait de Naissance</label
                        >
                        <input
                            id="num_extrait_naissance"
                            v-model="formData.num_extrait_naissance"
                            type="text"
                        />
                    </div>
                    <div class="form-group">
                        <label for="date_cin">Date CIN</label>
                        <input
                            id="date_cin"
                            v-model="formData.date_cin"
                            type="date"
                        />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="lieu_cin_fr">Lieu CIN (FR)</label>
                        <input
                            id="lieu_cin_fr"
                            v-model="formData.lieu_cin_fr"
                            type="text"
                        />
                    </div>
                    <div class="form-group">
                        <label for="lieu_cin_ar">Lieu CIN (AR)</label>
                        <input
                            id="lieu_cin_ar"
                            v-model="formData.lieu_cin_ar"
                            type="text"
                        />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="etat_civil_id">État Civil</label>
                        <select
                            id="etat_civil_id"
                            v-model="formData.etat_civil_id"
                        >
                            <option value="">Sélectionner...</option>
                            <option
                                v-for="etat in etatCivilOptions"
                                :key="etat.id"
                                :value="etat.id"
                            >
                                {{ etat.nom }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="nombre_enfants">Nombre d'Enfants</label>
                        <input
                            id="nombre_enfants"
                            v-model="formData.nombre_enfants"
                            type="number"
                            min="0"
                        />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="niveau_etude">Niveau d'Étude</label>
                        <input
                            id="niveau_etude"
                            v-model="formData.niveau_etude"
                            type="text"
                        />
                    </div>
                    <div class="form-group">
                        <label for="specialite_diplome_fr"
                            >Spécialité Diplôme (FR)</label
                        >
                        <input
                            id="specialite_diplome_fr"
                            v-model="formData.specialite_diplome_fr"
                            type="text"
                        />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="specialite_diplome_ar"
                            >Spécialité Diplôme (AR)</label
                        >
                        <input
                            id="specialite_diplome_ar"
                            v-model="formData.specialite_diplome_ar"
                            type="text"
                        />
                    </div>
                    <div class="form-group">
                        <label for="date_inscription">Date d'Inscription</label>
                        <input
                            id="date_inscription"
                            v-model="formData.date_inscription"
                            type="date"
                            required
                        />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="statut_id">Statut</label>
                        <select
                            id="statut_id"
                            v-model="formData.statut_id"
                            required
                        >
                            <option value="">Sélectionner...</option>
                            <option
                                v-for="statut in statutOptions"
                                :key="statut.id"
                                :value="statut.id"
                            >
                                {{ statut.nom }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="visites_minimum"
                            >Visites Minimum Requises</label
                        >
                        <input
                            id="visites_minimum"
                            v-model="formData.visites_minimum"
                            type="number"
                            min="0"
                        />
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" @click="cancel" class="btn-cancel">
                    Annuler
                </button>
                <button type="submit" class="btn-submit">
                    {{ isEdit ? 'Mettre à jour' : 'Enregistrer' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
    props: {
        formData: {
            type: Object,
            required: true,
        },
        isEdit: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['submit', 'cancel'],
    setup(props, { emit }) {
        const genreOptions = ref([]);
        const etatCivilOptions = ref([]);
        const statutOptions = ref([]);

        onMounted(() => {
            fetchOptions();
        });

        const fetchOptions = async () => {
            try {
                // Fetch genre options
                const genreResponse = await axios.get('/api/options-listes', {
                    params: {
                        type_categorie_id: 'Genre',
                    },
                });
                genreOptions.value = genreResponse.data;

                // Fetch etat civil options
                const etatCivilResponse = await axios.get(
                    '/api/options-listes',
                    {
                        params: {
                            type_categorie_id: 'EtatCivil',
                        },
                    }
                );
                etatCivilOptions.value = etatCivilResponse.data;

                // Fetch statut options
                const statutResponse = await axios.get('/api/options-listes', {
                    params: {
                        type_categorie_id: 'StatutStagiaire',
                    },
                });
                statutOptions.value = statutResponse.data;
            } catch (error) {
                console.error('Error fetching options:', error);
            }
        };

        const submitForm = () => {
            emit('submit', props.formData);
        };

        const cancel = () => {
            emit('cancel');
        };

        return {
            genreOptions,
            etatCivilOptions,
            statutOptions,
            submitForm,
            cancel,
        };
    },
};
</script>

<style scoped>
.stagiaire-form {
    padding: 20px;
}

.form-section {
    margin-bottom: 30px;
    padding: 15px;
    border: 1px solid #eee;
    border-radius: 5px;
}

.form-section h3 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #333;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
}

.form-group {
    flex: 1;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #555;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #1976d2;
    box-shadow: 0 0 0 2px rgba(25, 118, 210, 0.2);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

.btn-cancel {
    padding: 10px 20px;
    background-color: #f5f5f5;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    color: #333;
}

.btn-cancel:hover {
    background-color: #e0e0e0;
}

.btn-submit {
    padding: 10px 20px;
    background-color: #1976d2;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    color: white;
}

.btn-submit:hover {
    background-color: #1565c0;
}
</style>
