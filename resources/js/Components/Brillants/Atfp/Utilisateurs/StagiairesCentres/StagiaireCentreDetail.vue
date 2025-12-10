<template>
    <div class="stagiaire-details">
        <div class="header">
            <h1>Détails du Stagiaire</h1>
            <div class="actions">
                <button @click="goBack" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </button>
                <button @click="editStagiaire" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Modifier
                </button>
            </div>
        </div>

        <div v-if="loading" class="loading">
            <i class="fas fa-spinner fa-spin"></i> Chargement...
        </div>

        <div v-else-if="stagiaire" class="content">
            <div class="profile-section">
                <div class="profile-header">
                    <div class="avatar">
                        <img
                            v-if="stagiaire.user.photo"
                            :src="stagiaire.user.photo"
                            alt="Photo de profil"
                        />
                        <div v-else class="avatar-placeholder">
                            {{ initials }}
                        </div>
                    </div>
                    <div class="profile-info">
                        <h2>{{ fullNameFr }}</h2>
                        <p class="matricule">
                            Matricule: {{ stagiaire.user.matricule || 'N/A' }}
                        </p>
                        <p class="cin">
                            CIN: {{ stagiaire.user.cin || 'N/A' }}
                        </p>
                        <div class="status-badge" :class="statusClass">
                            {{ statusName }}
                        </div>
                    </div>
                </div>

                <div class="tabs">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="{ active: activeTab === tab.id }"
                    >
                        {{ tab.label }}
                    </button>
                </div>
            </div>

            <div class="tab-content">
                <!-- Informations Personnelles Tab -->
                <div v-if="activeTab === 'info'" class="tab-pane">
                    <div class="info-grid">
                        <div class="info-section">
                            <h3>Informations Personnelles</h3>
                            <div class="info-row">
                                <span class="label">Nom (FR):</span>
                                <span class="value">{{
                                    stagiaire.user.nom_fr
                                }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Prénom (FR):</span>
                                <span class="value">{{
                                    stagiaire.user.prenom_fr
                                }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Nom (AR):</span>
                                <span class="value">{{
                                    stagiaire.user.nom_ar || 'N/A'
                                }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Prénom (AR):</span>
                                <span class="value">{{
                                    stagiaire.user.prenom_ar || 'N/A'
                                }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Date de Naissance:</span>
                                <span class="value">{{
                                    formatDate(stagiaire.user.date_naissance)
                                }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label"
                                    >Lieu de Naissance (FR):</span
                                >
                                <span class="value">{{
                                    stagiaire.user.lieu_naissance_fr || 'N/A'
                                }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label"
                                    >Lieu de Naissance (AR):</span
                                >
                                <span class="value">{{
                                    stagiaire.user.lieu_naissance_ar || 'N/A'
                                }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Nationalité:</span>
                                <span class="value">{{
                                    stagiaire.user.nationalite || 'N/A'
                                }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Genre:</span>
                                <span class="value">{{ genreName }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Email:</span>
                                <span class="value">{{
                                    stagiaire.user.email || 'N/A'
                                }}</span>
                            </div>
                        </div>

                        <div class="info-section">
                            <h3>Informations du Stagiaire</h3>
                            <div class="info-row">
                                <span class="label"
                                    >Numéro Extrait de Naissance:</span
                                >
                                <span class="value">{{
                                    stagiaire.num_extrait_naissance || 'N/A'
                                }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Date CIN:</span>
                                <span class="value">{{
                                    formatDate(stagiaire.date_cin)
                                }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Lieu CIN (FR):</span>
                                <span class="value">{{
                                    stagiaire.lieu_cin_fr || 'N/A'
                                }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Lieu CIN (AR):</span>
                                <span class="value">{{
                                    stagiaire.lieu_cin_ar || 'N/A'
                                }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">État Civil:</span>
                                <span class="value">{{ etatCivilName }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Nombre d'Enfants:</span>
                                <span class="value">{{
                                    stagiaire.nombre_enfants
                                }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Niveau d'Étude:</span>
                                <span class="value">{{
                                    stagiaire.niveau_etude || 'N/A'
                                }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label"
                                    >Spécialité Diplôme (FR):</span
                                >
                                <span class="value">{{
                                    stagiaire.specialite_diplome_fr || 'N/A'
                                }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label"
                                    >Spécialité Diplôme (AR):</span
                                >
                                <span class="value">{{
                                    stagiaire.specialite_diplome_ar || 'N/A'
                                }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Date d'Inscription:</span>
                                <span class="value">{{
                                    formatDate(stagiaire.date_inscription)
                                }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Statut:</span>
                                <span class="value">{{ statusName }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label"
                                    >Visites Minimum Requises:</span
                                >
                                <span class="value">{{
                                    stagiaire.visites_minimum
                                }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Groupes Tab -->
                <div v-if="activeTab === 'groupes'" class="tab-pane">
                    <h3>Historique des Groupes</h3>
                    <DataTable
                        :columns="groupeColumns"
                        :data="stagiaire.groupes"
                        :loading="loadingGroupes"
                    />
                </div>

                <!-- Evaluations Tab -->
                <div v-if="activeTab === 'evaluations'" class="tab-pane">
                    <h3>Évaluations</h3>
                    <DataTable
                        :columns="evaluationColumns"
                        :data="stagiaire.evaluations"
                        :loading="loadingEvaluations"
                    />
                </div>

                <!-- Presence Tab -->
                <div v-if="activeTab === 'presences'" class="tab-pane">
                    <h3>Présences</h3>
                    <DataTable
                        :columns="presenceColumns"
                        :data="stagiaire.presences"
                        :loading="loadingPresences"
                    />
                </div>

                <!-- Documents Tab -->
                <div v-if="activeTab === 'documents'" class="tab-pane">
                    <h3>Documents</h3>
                    <button @click="showAddDocument" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Ajouter Document
                    </button>
                    <DataTable
                        :columns="documentColumns"
                        :data="stagiaire.documents"
                        :loading="loadingDocuments"
                    />
                </div>
            </div>
        </div>

        <div v-else class="no-data">
            Aucune donnée disponible pour ce stagiaire
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { format } from 'date-fns';
import DataTable from '@/Components/Shared/DataTable.vue';

export default {
    components: {
        DataTable,
    },
    setup() {
        const route = useRoute();
        const router = useRouter();
        const stagiaire = ref(null);
        const loading = ref(true);
        const activeTab = ref('info');
        const statusOptions = ref([]);
        const genreOptions = ref([]);
        const etatCivilOptions = ref([]);

        const tabs = [
            { id: 'info', label: 'Informations' },
            { id: 'groupes', label: 'Groupes' },
            { id: 'evaluations', label: 'Évaluations' },
            { id: 'presences', label: 'Présences' },
            { id: 'documents', label: 'Documents' },
        ];

        const groupeColumns = [
            { key: 'code_groupe', label: 'Code Groupe' },
            { key: 'nom', label: 'Nom' },
            { key: 'date_debut', label: 'Date Début', type: 'date' },
            { key: 'date_fin', label: 'Date Fin', type: 'date' },
            { key: 'statut', label: 'Statut' },
        ];

        const evaluationColumns = [
            { key: 'module', label: 'Module' },
            { key: 'type_evaluation', label: 'Type' },
            { key: 'date_evaluation', label: 'Date', type: 'date' },
            { key: 'note', label: 'Note' },
            { key: 'resultat', label: 'Résultat' },
        ];

        const presenceColumns = [
            { key: 'date', label: 'Date', type: 'date' },
            { key: 'module', label: 'Module' },
            { key: 'formateur', label: 'Formateur' },
            { key: 'present', label: 'Présent', type: 'boolean' },
            { key: 'motif_absence', label: 'Motif Absence' },
        ];

        const documentColumns = [
            { key: 'type', label: 'Type' },
            { key: 'titre', label: 'Titre' },
            { key: 'date_emission', label: 'Date Émission', type: 'date' },
            { key: 'date_expiration', label: 'Date Expiration', type: 'date' },
            { key: 'actions', label: 'Actions', type: 'actions' },
        ];

        onMounted(() => {
            fetchStagiaire();
            fetchOptions();
        });

        const fetchStagiaire = async () => {
            try {
                const response = await axios.get(
                    `/api/stagiaires/${route.params.id}`
                );
                stagiaire.value = response.data;
            } catch (error) {
                console.error('Error fetching stagiaire:', error);
            } finally {
                loading.value = false;
            }
        };

        const fetchOptions = async () => {
            try {
                // Fetch status options
                const statusResponse = await axios.get('/api/options-listes', {
                    params: {
                        type_categorie_id: 'StatutStagiaire',
                    },
                });
                statusOptions.value = statusResponse.data;

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
            } catch (error) {
                console.error('Error fetching options:', error);
            }
        };

        const fullNameFr = computed(() => {
            if (!stagiaire.value) return '';
            return `${stagiaire.value.user.prenom_fr} ${stagiaire.value.user.nom_fr}`;
        });

        const initials = computed(() => {
            if (!stagiaire.value) return '';
            const first = stagiaire.value.user.prenom_fr
                ? stagiaire.value.user.prenom_fr.charAt(0)
                : '';
            const last = stagiaire.value.user.nom_fr
                ? stagiaire.value.user.nom_fr.charAt(0)
                : '';
            return `${first}${last}`.toUpperCase();
        });

        const statusName = computed(() => {
            if (!stagiaire.value) return '';
            const status = statusOptions.value.find(
                (s) => s.id === stagiaire.value.statut_id
            );
            return status ? status.nom : 'Inconnu';
        });

        const statusClass = computed(() => {
            if (!stagiaire.value) return '';
            const status = statusOptions.value.find(
                (s) => s.id === stagiaire.value.statut_id
            );
            if (!status) return '';

            switch (status.code) {
                case 'ACTIF':
                    return 'active';
                case 'INACTIF':
                    return 'inactive';
                case 'SUSPENDU':
                    return 'suspended';
                case 'ABANDON':
                    return 'abandoned';
                case 'DIPLOME':
                    return 'graduated';
                default:
                    return '';
            }
        });

        const genreName = computed(() => {
            if (!stagiaire.value) return 'N/A';
            const genre = genreOptions.value.find(
                (g) => g.id === stagiaire.value.user.genre_id
            );
            return genre ? genre.nom : 'N/A';
        });

        const etatCivilName = computed(() => {
            if (!stagiaire.value) return 'N/A';
            const etat = etatCivilOptions.value.find(
                (e) => e.id === stagiaire.value.etat_civil_id
            );
            return etat ? etat.nom : 'N/A';
        });

        const formatDate = (dateString) => {
            return dateString
                ? format(new Date(dateString), 'dd/MM/yyyy')
                : 'N/A';
        };

        const goBack = () => {
            router.go(-1);
        };

        const editStagiaire = () => {
            router.push({
                name: 'EditStagiaire',
                params: { id: route.params.id },
            });
        };

        const showAddDocument = () => {
            // Logic to show add document modal
        };

        return {
            stagiaire,
            loading,
            activeTab,
            tabs,
            groupeColumns,
            evaluationColumns,
            presenceColumns,
            documentColumns,
            fullNameFr,
            initials,
            statusName,
            statusClass,
            genreName,
            etatCivilName,
            formatDate,
            goBack,
            editStagiaire,
            showAddDocument,
        };
    },
};
</script>

<style scoped>
.stagiaire-details {
    padding: 20px;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.actions {
    display: flex;
    gap: 10px;
}

.loading,
.no-data {
    padding: 40px;
    text-align: center;
    color: #666;
}

.profile-section {
    margin-bottom: 30px;
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
}

.avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
    background-color: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: bold;
    color: #555;
}

.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-info h2 {
    margin: 0;
    color: #333;
}

.matricule,
.cin {
    margin: 5px 0;
    color: #666;
    font-size: 14px;
}

.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    margin-top: 5px;
}

.status-badge.active {
    background-color: #e6f7e6;
    color: #2e7d32;
}

.status-badge.inactive {
    background-color: #fff3e0;
    color: #e65100;
}

.status-badge.suspended {
    background-color: #ffebee;
    color: #c62828;
}

.status-badge.abandoned {
    background-color: #f5f5f5;
    color: #616161;
}

.status-badge.graduated {
    background-color: #e3f2fd;
    color: #1565c0;
}

.tabs {
    display: flex;
    border-bottom: 1px solid #ddd;
    margin-bottom: 20px;
}

.tabs button {
    padding: 10px 20px;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-weight: 500;
    color: #555;
}

.tabs button.active {
    border-bottom-color: #1976d2;
    color: #1976d2;
}

.tabs button:hover:not(.active) {
    border-bottom-color: #e0e0e0;
}

.tab-content {
    padding: 0 10px;
}

.tab-pane {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.info-section {
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 5px;
}

.info-section h3 {
    margin-top: 0;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.info-row {
    display: flex;
    margin-bottom: 10px;
}

.label {
    font-weight: 500;
    color: #555;
    width: 200px;
}

.value {
    flex: 1;
    color: #333;
}
</style>
