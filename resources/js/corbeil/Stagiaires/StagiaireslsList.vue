<template>
    <div>
        <DataTable
            v-model:filters="filters"
            showGridlines
            stripedRows
            :value="stagiaires"
            dataKey="id"
            size="small"
            :rows="20"
            filterDisplay="menu"
            :globalFilterFields="[
                'user.name',
                'prenom',
                'email_personnel',
                'formation',
            ]"
            :loading="loading"
            scrollable
            scrollHeight="700px"
            :pt="{ table: { style: 'min-width: 50rem' } }"
        >
            <template #header>
                <div
                    class="flex justify-content-between mb-2 align-items-center"
                >
                    <div class="flex align-items-center gap-2">
                        <Button
                            icon="pi pi-plus"
                            severity="success"
                            size="small"
                            @click="openForm"
                        />
                        <span class="p-input-icon-right mr-2">
                            <InputText
                                v-model="filters['global'].value"
                                size="small"
                                placeholder="Recherche..."
                            />
                            <i class="pi pi-search" />
                        </span>
                        <Button
                            type="button"
                            icon="pi pi-times"
                            size="small"
                            severity="danger"
                            outlined
                            @click="clearFilter"
                        />
                    </div>
                </div>
            </template>

            <template #empty>
                <div v-if="!loading" class="text-center p-3">
                    Aucun stagiaire trouvé.
                </div>
                <div v-else class="text-center p-3">Chargement en cours...</div>
            </template>

            <Column
                field="user.name"
                header="Nom"
                sortable
                style="width: 15rem"
            >
                <template #filter="{ filterModel, filterCallback }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Rechercher par nom"
                        @input="filterCallback"
                    />
                </template>
            </Column>
            <Column
                field="prenom"
                header="Prénom"
                sortable
                style="width: 15rem"
            >
                <template #filter="{ filterModel, filterCallback }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Rechercher par prénom"
                        @input="filterCallback"
                    />
                </template>
            </Column>
            <Column
                field="email_personnel"
                header="Email Personnel"
                sortable
                style="width: 20rem"
            >
                <template #filter="{ filterModel, filterCallback }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Rechercher par email"
                        @input="filterCallback"
                    />
                </template>
            </Column>
            <Column
                field="formation"
                header="Formation"
                sortable
                style="width: 15rem"
            >
                <template #filter="{ filterModel, filterCallback }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Rechercher par formation"
                        @input="filterCallback"
                    />
                </template>
            </Column>
            <Column header="Centre" style="width: 15rem">
                <template #body="{ data }">
                    <div class="flex justify-content-center align-items-center">
                        <Tag
                            :value="data.centre?.nom_fr || 'Non affecté'"
                            :severity="data.centre ? 'success' : 'warning'"
                        />
                    </div>
                </template>
            </Column>
            <Column header="Actions" style="width: 10rem" frozen>
                <template #body="{ data }">
                    <div class="flex gap-2">
                        <Button
                            icon="pi pi-pencil"
                            severity="info"
                            text
                            @click="editStagiaire(data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            severity="danger"
                            text
                            @click="confirmDeleteStagiaire(data)"
                        />
                    </div>
                </template>
            </Column>
        </DataTable>

        <!-- Formulaire d'ajout/édition -->
        <Dialog
            v-model:visible="formVisible"
            :header="
                stagiaireToEdit
                    ? 'Modifier un stagiaire'
                    : 'Ajouter un stagiaire'
            "
            :modal="true"
            :style="{ width: '450px' }"
        >
            <div class="p-fluid">
                <div class="field">
                    <label for="name"
                        >Nom <span class="text-red-500">*</span></label
                    >
                    <InputText
                        id="name"
                        v-model="newStagiaire.name"
                        :class="{ 'p-invalid': errors.name }"
                    />
                    <small v-if="errors.name" class="p-error">{{
                        errors.name[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="prenom"
                        >Prénom <span class="text-red-500">*</span></label
                    >
                    <InputText
                        id="prenom"
                        v-model="newStagiaire.prenom"
                        :class="{ 'p-invalid': errors.prenom }"
                    />
                    <small v-if="errors.prenom" class="p-error">{{
                        errors.prenom[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="email"
                        >Email <span class="text-red-500">*</span></label
                    >
                    <InputText
                        id="email"
                        v-model="newStagiaire.email"
                        :class="{ 'p-invalid': errors.email }"
                    />
                    <small v-if="errors.email" class="p-error">{{
                        errors.email[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="matricule">Matricule</label>
                    <InputText
                        id="matricule"
                        v-model="newStagiaire.matricule"
                        :class="{ 'p-invalid': errors.matricule }"
                    />
                    <small v-if="errors.matricule" class="p-error">{{
                        errors.matricule[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="telephone">Téléphone</label>
                    <InputText
                        id="telephone"
                        v-model="newStagiaire.telephone"
                        :class="{ 'p-invalid': errors.telephone }"
                    />
                    <small v-if="errors.telephone" class="p-error">{{
                        errors.telephone[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="adresse">Adresse</label>
                    <InputText
                        id="adresse"
                        v-model="newStagiaire.adresse"
                        :class="{ 'p-invalid': errors.adresse }"
                    />
                    <small v-if="errors.adresse" class="p-error">{{
                        errors.adresse[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="date_naissance">Date de naissance</label>
                    <Calendar
                        id="date_naissance"
                        v-model="newStagiaire.date_naissance"
                        dateFormat="yy-mm-dd"
                        :class="{ 'p-invalid': errors.date_naissance }"
                    />
                    <small v-if="errors.date_naissance" class="p-error">{{
                        errors.date_naissance[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="cin">CIN</label>
                    <InputText
                        id="cin"
                        v-model="newStagiaire.cin"
                        :class="{ 'p-invalid': errors.cin }"
                    />
                    <small v-if="errors.cin" class="p-error">{{
                        errors.cin[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="formation">Formation</label>
                    <InputText
                        id="formation"
                        v-model="newStagiaire.formation"
                        :class="{ 'p-invalid': errors.formation }"
                    />
                    <small v-if="errors.formation" class="p-error">{{
                        errors.formation[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="niveau_etude">Niveau d'étude</label>
                    <InputText
                        id="niveau_etude"
                        v-model="newStagiaire.niveau_etude"
                        :class="{ 'p-invalid': errors.niveau_etude }"
                    />
                    <small v-if="errors.niveau_etude" class="p-error">{{
                        errors.niveau_etude[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="statut">Statut</label>
                    <Dropdown
                        id="statut"
                        v-model="newStagiaire.statut"
                        :options="statutOptions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Sélectionner un statut"
                        :class="{ 'p-invalid': errors.statut }"
                    />
                    <small v-if="errors.statut" class="p-error">{{
                        errors.statut[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="date_inscription">Date d'inscription</label>
                    <Calendar
                        id="date_inscription"
                        v-model="newStagiaire.date_inscription"
                        dateFormat="yy-mm-dd"
                        :class="{ 'p-invalid': errors.date_inscription }"
                    />
                    <small v-if="errors.date_inscription" class="p-error">{{
                        errors.date_inscription[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="date_fin_formation"
                        >Date de fin de formation</label
                    >
                    <Calendar
                        id="date_fin_formation"
                        v-model="newStagiaire.date_fin_formation"
                        dateFormat="yy-mm-dd"
                        :class="{ 'p-invalid': errors.date_fin_formation }"
                    />
                    <small v-if="errors.date_fin_formation" class="p-error">{{
                        errors.date_fin_formation[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="email_personnel">Email Personnel</label>
                    <InputText
                        id="email_personnel"
                        v-model="newStagiaire.email_personnel"
                        :class="{ 'p-invalid': errors.email_personnel }"
                    />
                    <small v-if="errors.email_personnel" class="p-error">{{
                        errors.email_personnel[0]
                    }}</small>
                </div>
                <div class="field" v-if="isCentreRole === 0">
                    <label for="centre_id"
                        >Centre <span class="text-red-500">*</span></label
                    >
                    <Dropdown
                        id="centre_id"
                        v-model="newStagiaire.centre_id"
                        :options="centres"
                        optionLabel="nom_fr"
                        optionValue="id"
                        placeholder="Sélectionner un centre"
                        :loading="centresLoading"
                        :class="{ 'p-invalid': errors.centre_id }"
                    />
                    <small v-if="errors.centre_id" class="p-error">{{
                        errors.centre_id[0]
                    }}</small>
                </div>
            </div>
            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    text
                    @click="closeForm"
                />
                <Button
                    :label="stagiaireToEdit ? 'Mettre à jour' : 'Créer'"
                    icon="pi pi-check"
                    :loading="saving"
                    @click="
                        stagiaireToEdit ? updateStagiaire() : createStagiaire()
                    "
                />
            </template>
        </Dialog>

        <!-- Confirmation de suppression -->
        <Dialog
            v-model:visible="deleteFormVisible"
            header="Confirmation de suppression"
            :modal="true"
            :style="{ width: '450px' }"
        >
            <div class="p-fluid">
                <p v-if="stagiaireToDelete">
                    Êtes-vous sûr de vouloir supprimer le stagiaire
                    <strong
                        >{{ stagiaireToDelete.user.name }}
                        {{ stagiaireToDelete.prenom }}</strong
                    >
                    ?
                </p>
            </div>
            <template #footer>
                <Button
                    label="Non"
                    icon="pi pi-times"
                    text
                    @click="deleteFormVisible = false"
                />
                <Button
                    label="Oui"
                    icon="pi pi-check"
                    text
                    @click="deleteStagiaire"
                />
            </template>
        </Dialog>

        <Toast />
    </div>
</template>

<script>
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import { useToast } from 'primevue/usetoast';
import axios from '@/axios.js';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
import Toast from 'primevue/toast';

export default {
    components: {
        Button,
        InputText,
        DataTable,
        Column,
        Tag,
        Dialog,
        Calendar,
        Dropdown,
        Toast,
    },
    data() {
        return {
            stagiaires: [],
            filters: null,
            loading: false,
            formVisible: false,
            saving: false,
            deleteFormVisible: false,
            stagiaireToEdit: null,
            stagiaireToDelete: null,
            newStagiaire: {
                name: '',
                prenom: '',
                email: '',
                matricule: null,
                telephone: null,
                adresse: null,
                date_naissance: null,
                cin: null,
                formation: null,
                niveau_etude: null,
                statut: 'actif',
                date_inscription: null,
                date_fin_formation: null,
                email_personnel: null,
                centre_id: localStorage.getItem('centreId') || null, // Pré-rempli pour utilisateurs de centre
            },
            statutOptions: [
                { label: 'Actif', value: 'actif' },
                { label: 'Inactif', value: 'inactif' },
                { label: 'Terminé', value: 'termine' },
            ],
            centres: [],
            centresLoading: false,
            errors: {},
            isCentreRole: null,
        };
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    created() {
        this.initFilters();
        this.fetchStagiaires();
        this.loadProfile();
        this.fetchCentres();
    },
    methods: {
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                'user.name': {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                prenom: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                email_personnel: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                formation: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
            };
        },
        clearFilter() {
            this.initFilters();
        },
        async fetchStagiaires() {
            try {
                this.loading = true;
                const response = await axios.get('/stagiaires');
                this.stagiaires = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors du chargement des stagiaires.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        async loadProfile() {
            try {
                const userId = localStorage.getItem('user_id');
                if (!userId) {
                    this.toast.add({
                        severity: 'warn',
                        summary: 'Attention',
                        detail: 'Utilisateur non identifié. Connectez-vous pour continuer.',
                        life: 3000,
                    });
                    this.$router.push({ name: 'login' });
                    return;
                }
                const response = await axios.get('/user/profile');
                this.isCentreRole = response.data.is_centre_role;
                if (this.isCentreRole === 1 && response.data.centre_id) {
                    this.newStagiaire.centre_id = response.data.centre_id;
                }
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Impossible de charger le profil',
                    life: 3000,
                });
            }
        },
        async fetchCentres() {
            try {
                this.centresLoading = true;
                const response = await axios.get('/centres');
                this.centres = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors du chargement des centres.',
                    life: 3000,
                });
            } finally {
                this.centresLoading = false;
            }
        },
        openForm() {
            this.stagiaireToEdit = null;
            this.resetForm();
            this.errors = {};
            this.formVisible = true;
        },
        closeForm() {
            this.formVisible = false;
            this.stagiaireToEdit = null;
            this.resetForm();
            this.errors = {};
        },
        async createStagiaire() {
            this.errors = {};
            try {
                this.saving = true;
                const userId = localStorage.getItem('user_id');
                if (!userId) {
                    throw new Error(
                        'Utilisateur non identifié. Veuillez vous reconnecter.'
                    );
                }

                if (
                    !this.newStagiaire.name ||
                    !this.newStagiaire.prenom ||
                    !this.newStagiaire.email
                ) {
                    throw new Error(
                        'Les champs Nom, Prénom et Email sont obligatoires.'
                    );
                }
                if (this.isCentreRole === 0 && !this.newStagiaire.centre_id) {
                    throw new Error(
                        'Le champ Centre est obligatoire pour un utilisateur central.'
                    );
                }

                const formData = {
                    ...this.newStagiaire,
                    email: this.newStagiaire.email.toLowerCase(),
                    centre_id:
                        this.isCentreRole === 1
                            ? localStorage.getItem('centreId')
                            : this.newStagiaire.centre_id,
                };
                Object.keys(formData).forEach((key) => {
                    if (formData[key] === '' && key !== 'centre_id') {
                        formData[key] = null;
                    }
                });

                console.log('Données envoyées:', formData);

                const response = await axios.post('/stagiaires', formData);
                this.stagiaires.unshift(response.data.stagiaire);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: `Stagiaire créé ! Mot de passe : ${response.data.generated_password}`,
                    life: 5000,
                });
                this.formVisible = false;
            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors || {};
                    console.log('Erreurs de validation:', this.errors);
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur de validation',
                        detail:
                            Object.values(this.errors).flat().join(', ') ||
                            'Erreur de validation non spécifiée',
                        life: 5000,
                    });
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail:
                            error.response?.data?.message ||
                            error.message ||
                            'Erreur lors de la création.',
                        life: 3000,
                    });
                }
            } finally {
                this.saving = false;
            }
        },
        editStagiaire(stagiaire) {
            this.stagiaireToEdit = stagiaire;
            this.newStagiaire = {
                name: stagiaire.user.name,
                prenom: stagiaire.prenom,
                email: stagiaire.user.email,
                matricule: stagiaire.matricule || null,
                telephone: stagiaire.telephone || null,
                adresse: stagiaire.adresse || null,
                date_naissance: stagiaire.date_naissance || null,
                cin: stagiaire.cin || null,
                formation: stagiaire.formation || null,
                niveau_etude: stagiaire.niveau_etude || null,
                statut: stagiaire.statut || 'actif',
                date_inscription: stagiaire.date_inscription || null,
                date_fin_formation: stagiaire.date_fin_formation || null,
                email_personnel: stagiaire.email_personnel || null,
                centre_id:
                    stagiaire.centre_id ||
                    (this.isCentreRole === 1
                        ? localStorage.getItem('centreId')
                        : null),
            };
            this.errors = {};
            this.formVisible = true;
        },
        async updateStagiaire() {
            this.errors = {};
            try {
                this.saving = true;
                const userId = localStorage.getItem('user_id');
                if (!userId) {
                    throw new Error(
                        'Utilisateur non identifié. Veuillez vous reconnecter.'
                    );
                }

                if (
                    !this.newStagiaire.name ||
                    !this.newStagiaire.prenom ||
                    !this.newStagiaire.email
                ) {
                    throw new Error(
                        'Les champs Nom, Prénom et Email sont obligatoires.'
                    );
                }
                if (this.isCentreRole === 0 && !this.newStagiaire.centre_id) {
                    throw new Error(
                        'Le champ Centre est obligatoire pour un utilisateur central.'
                    );
                }

                const formData = {
                    ...this.newStagiaire,
                    email: this.newStagiaire.email.toLowerCase(),
                    centre_id:
                        this.isCentreRole === 1
                            ? localStorage.getItem('centreId')
                            : this.newStagiaire.centre_id,
                };
                Object.keys(formData).forEach((key) => {
                    if (formData[key] === '' && key !== 'centre_id') {
                        formData[key] = null;
                    }
                });

                console.log('Données envoyées:', formData);

                const response = await axios.put(
                    `/stagiaires/${this.stagiaireToEdit.id}`,
                    formData
                );
                const index = this.stagiaires.findIndex(
                    (s) => s.id === this.stagiaireToEdit.id
                );
                if (index !== -1)
                    this.stagiaires.splice(index, 1, response.data.stagiaire);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Stagiaire mis à jour.',
                    life: 3000,
                });
                this.formVisible = false;
            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors || {};
                    console.log('Erreurs de validation:', this.errors);
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail:
                            error.response?.data?.message ||
                            error.message ||
                            'Erreur lors de la mise à jour.',
                        life: 3000,
                    });
                }
            } finally {
                this.saving = false;
            }
        },
        confirmDeleteStagiaire(stagiaire) {
            this.stagiaireToDelete = stagiaire;
            this.deleteFormVisible = true;
        },
        async deleteStagiaire() {
            if (this.stagiaireToDelete) {
                try {
                    await axios.delete(
                        `/stagiaires/${this.stagiaireToDelete.id}`
                    );
                    this.stagiaires = this.stagiaires.filter(
                        (s) => s.id !== this.stagiaireToDelete.id
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Stagiaire supprimé.',
                        life: 3000,
                    });
                } catch (error) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail:
                            error.response?.data?.message ||
                            error.message ||
                            'Erreur lors de la suppression.',
                        life: 3000,
                    });
                } finally {
                    this.deleteFormVisible = false;
                    this.stagiaireToDelete = null;
                }
            }
        },
        resetForm() {
            this.newStagiaire = {
                name: '',
                prenom: '',
                email: '',
                matricule: null,
                telephone: null,
                adresse: null,
                date_naissance: null,
                cin: null,
                formation: null,
                niveau_etude: null,
                statut: 'actif',
                date_inscription: null,
                date_fin_formation: null,
                email_personnel: null,
                centre_id:
                    this.isCentreRole === 1
                        ? localStorage.getItem('centreId')
                        : null,
            };
        },
    },
};
</script>

<style scoped>
.field {
    margin-bottom: 1.5rem;
}
.p-error {
    color: #dc3545;
}
.text-red-500 {
    color: #dc3545;
}
</style>
