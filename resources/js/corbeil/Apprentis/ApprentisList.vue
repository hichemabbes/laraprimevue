<template>
    <div>
        <DataTable
            v-model:filters="filters"
            showGridlines
            stripedRows
            :value="formateurs"
            dataKey="id"
            size="small"
            :rows="20"
            filterDisplay="menu"
            :globalFilterFields="[
                'user.name',
                'prenom_fr',
                'mail',
                'specialite_diplome_fr',
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
                    Aucun formateur trouvé.
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
                field="prenom_fr"
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
                field="mail"
                header="Email Professionnel"
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
                field="specialite_diplome_fr"
                header="Spécialité"
                sortable
                style="width: 15rem"
            >
                <template #filter="{ filterModel, filterCallback }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Rechercher par spécialité"
                        @input="filterCallback"
                    />
                </template>
            </Column>
            <Column header="Centre" style="width: 15rem">
                <template #body="{ data }">
                    <div class="flex justify-content-center align-items-center">
                        <Tag
                            :value="data.centres?.[0]?.nom_fr || 'Non affecté'"
                            :severity="
                                data.centres?.length ? 'success' : 'warning'
                            "
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
                            @click="editFormateur(data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            severity="danger"
                            text
                            @click="confirmDeleteFormateur(data)"
                        />
                    </div>
                </template>
            </Column>
        </DataTable>

        <!-- Formulaire d'ajout/édition -->
        <Dialog
            v-model:visible="formVisible"
            :header="
                formateurToEdit
                    ? 'Modifier un formateur'
                    : 'Ajouter un formateur'
            "
            :modal="true"
            :style="{ width: '600px' }"
        >
            <div class="p-fluid">
                <div class="field">
                    <label for="nom_fr"
                        >Nom (FR) <span class="text-red-500">*</span></label
                    >
                    <InputText
                        id="nom_fr"
                        v-model="newFormateur.nom_fr"
                        :class="{ 'p-invalid': errors.nom_fr }"
                    />
                    <small v-if="errors.nom_fr" class="p-error">{{
                        errors.nom_fr[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="prenom_fr"
                        >Prénom (FR) <span class="text-red-500">*</span></label
                    >
                    <InputText
                        id="prenom_fr"
                        v-model="newFormateur.prenom_fr"
                        :class="{ 'p-invalid': errors.prenom_fr }"
                    />
                    <small v-if="errors.prenom_fr" class="p-error">{{
                        errors.prenom_fr[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="email"
                        >Email <span class="text-red-500">*</span></label
                    >
                    <InputText
                        id="email"
                        v-model="newFormateur.email"
                        :class="{ 'p-invalid': errors.email }"
                    />
                    <small v-if="errors.email" class="p-error">{{
                        errors.email[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="matricule"
                        >Matricule <span class="text-red-500">*</span></label
                    >
                    <InputText
                        id="matricule"
                        v-model="newFormateur.matricule"
                        :class="{ 'p-invalid': errors.matricule }"
                    />
                    <small v-if="errors.matricule" class="p-error">{{
                        errors.matricule[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="nom_ar"
                        >Nom (AR) <span class="text-red-500">*</span></label
                    >
                    <InputText
                        id="nom_ar"
                        v-model="newFormateur.nom_ar"
                        :class="{ 'p-invalid': errors.nom_ar }"
                    />
                    <small v-if="errors.nom_ar" class="p-error">{{
                        errors.nom_ar[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="prenom_ar">Prénom (AR)</label>
                    <InputText
                        id="prenom_ar"
                        v-model="newFormateur.prenom_ar"
                        :class="{ 'p-invalid': errors.prenom_ar }"
                    />
                    <small v-if="errors.prenom_ar" class="p-error">{{
                        errors.prenom_ar[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="niveau_etude">Niveau d'étude</label>
                    <InputText
                        id="niveau_etude"
                        v-model="newFormateur.niveau_etude"
                        :class="{ 'p-invalid': errors.niveau_etude }"
                    />
                    <small v-if="errors.niveau_etude" class="p-error">{{
                        errors.niveau_etude[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="specialite_diplome_fr"
                        >Spécialité Diplôme (FR)</label
                    >
                    <InputText
                        id="specialite_diplome_fr"
                        v-model="newFormateur.specialite_diplome_fr"
                        :class="{ 'p-invalid': errors.specialite_diplome_fr }"
                    />
                    <small
                        v-if="errors.specialite_diplome_fr"
                        class="p-error"
                        >{{ errors.specialite_diplome_fr[0] }}</small
                    >
                </div>
                <div class="field">
                    <label for="specialite_diplome_ar"
                        >Spécialité Diplôme (AR)</label
                    >
                    <InputText
                        id="specialite_diplome_ar"
                        v-model="newFormateur.specialite_diplome_ar"
                        :class="{ 'p-invalid': errors.specialite_diplome_ar }"
                    />
                    <small
                        v-if="errors.specialite_diplome_ar"
                        class="p-error"
                        >{{ errors.specialite_diplome_ar[0] }}</small
                    >
                </div>
                <div class="field">
                    <label for="matiere_enseigne_fr"
                        >Matière Enseignée (FR)</label
                    >
                    <InputText
                        id="matiere_enseigne_fr"
                        v-model="newFormateur.matiere_enseigne_fr"
                        :class="{ 'p-invalid': errors.matiere_enseigne_fr }"
                    />
                    <small v-if="errors.matiere_enseigne_fr" class="p-error">{{
                        errors.matiere_enseigne_fr[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="matiere_enseigne_ar"
                        >Matière Enseignée (AR)</label
                    >
                    <InputText
                        id="matiere_enseigne_ar"
                        v-model="newFormateur.matiere_enseigne_ar"
                        :class="{ 'p-invalid': errors.matiere_enseigne_ar }"
                    />
                    <small v-if="errors.matiere_enseigne_ar" class="p-error">{{
                        errors.matiere_enseigne_ar[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="date_prise_fonction"
                        >Date de prise de fonction</label
                    >
                    <Calendar
                        id="date_prise_fonction"
                        v-model="newFormateur.date_prise_fonction"
                        dateFormat="yy-mm-dd"
                        :class="{ 'p-invalid': errors.date_prise_fonction }"
                    />
                    <small v-if="errors.date_prise_fonction" class="p-error">{{
                        errors.date_prise_fonction[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="date_fin_mission">Date de fin de mission</label>
                    <Calendar
                        id="date_fin_mission"
                        v-model="newFormateur.date_fin_mission"
                        dateFormat="yy-mm-dd"
                        :class="{ 'p-invalid': errors.date_fin_mission }"
                    />
                    <small v-if="errors.date_fin_mission" class="p-error">{{
                        errors.date_fin_mission[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="code_niveau">Code Niveau</label>
                    <InputText
                        id="code_niveau"
                        v-model="newFormateur.code_niveau"
                        :class="{ 'p-invalid': errors.code_niveau }"
                    />
                    <small v-if="errors.code_niveau" class="p-error">{{
                        errors.code_niveau[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="charge_horaire">Charge Horaire</label>
                    <InputNumber
                        id="charge_horaire"
                        v-model="newFormateur.charge_horaire"
                        :class="{ 'p-invalid': errors.charge_horaire }"
                    />
                    <small v-if="errors.charge_horaire" class="p-error">{{
                        errors.charge_horaire[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="rabattement">Rabattement</label>
                    <InputNumber
                        id="rabattement"
                        v-model="newFormateur.rabattement"
                        mode="decimal"
                        :decimals="2"
                        :class="{ 'p-invalid': errors.rabattement }"
                    />
                    <small v-if="errors.rabattement" class="p-error">{{
                        errors.rabattement[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="code_secteur">Code Secteur</label>
                    <InputText
                        id="code_secteur"
                        v-model="newFormateur.code_secteur"
                        :class="{ 'p-invalid': errors.code_secteur }"
                    />
                    <small v-if="errors.code_secteur" class="p-error">{{
                        errors.code_secteur[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="code_sous_secteur">Code Sous-Secteur</label>
                    <InputText
                        id="code_sous_secteur"
                        v-model="newFormateur.code_sous_secteur"
                        :class="{ 'p-invalid': errors.code_sous_secteur }"
                    />
                    <small v-if="errors.code_sous_secteur" class="p-error">{{
                        errors.code_sous_secteur[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="cin">CIN</label>
                    <InputText
                        id="cin"
                        v-model="newFormateur.cin"
                        :class="{ 'p-invalid': errors.cin }"
                    />
                    <small v-if="errors.cin" class="p-error">{{
                        errors.cin[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="date_naissance">Date de naissance</label>
                    <Calendar
                        id="date_naissance"
                        v-model="newFormateur.date_naissance"
                        dateFormat="yy-mm-dd"
                        :class="{ 'p-invalid': errors.date_naissance }"
                    />
                    <small v-if="errors.date_naissance" class="p-error">{{
                        errors.date_naissance[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="lieu_naissance">Lieu de naissance</label>
                    <InputText
                        id="lieu_naissance"
                        v-model="newFormateur.lieu_naissance"
                        :class="{ 'p-invalid': errors.lieu_naissance }"
                    />
                    <small v-if="errors.lieu_naissance" class="p-error">{{
                        errors.lieu_naissance[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="adresse_fr">Adresse (FR)</label>
                    <InputText
                        id="adresse_fr"
                        v-model="newFormateur.adresse_fr"
                        :class="{ 'p-invalid': errors.adresse_fr }"
                    />
                    <small v-if="errors.adresse_fr" class="p-error">{{
                        errors.adresse_fr[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="adresse_ar">Adresse (AR)</label>
                    <InputText
                        id="adresse_ar"
                        v-model="newFormateur.adresse_ar"
                        :class="{ 'p-invalid': errors.adresse_ar }"
                    />
                    <small v-if="errors.adresse_ar" class="p-error">{{
                        errors.adresse_ar[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="gouvernorat">Gouvernorat</label>
                    <InputText
                        id="gouvernorat"
                        v-model="newFormateur.gouvernorat"
                        :class="{ 'p-invalid': errors.gouvernorat }"
                    />
                    <small v-if="errors.gouvernorat" class="p-error">{{
                        errors.gouvernorat[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="delegation">Délégation</label>
                    <InputText
                        id="delegation"
                        v-model="newFormateur.delegation"
                        :class="{ 'p-invalid': errors.delegation }"
                    />
                    <small v-if="errors.delegation" class="p-error">{{
                        errors.delegation[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="tele1">Téléphone 1</label>
                    <InputText
                        id="tele1"
                        v-model="newFormateur.tele1"
                        :class="{ 'p-invalid': errors.tele1 }"
                    />
                    <small v-if="errors.tele1" class="p-error">{{
                        errors.tele1[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="tele2">Téléphone 2</label>
                    <InputText
                        id="tele2"
                        v-model="newFormateur.tele2"
                        :class="{ 'p-invalid': errors.tele2 }"
                    />
                    <small v-if="errors.tele2" class="p-error">{{
                        errors.tele2[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="mail">Email Professionnel</label>
                    <InputText
                        id="mail"
                        v-model="newFormateur.mail"
                        :class="{ 'p-invalid': errors.mail }"
                    />
                    <small v-if="errors.mail" class="p-error">{{
                        errors.mail[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="etat_civil">État Civil</label>
                    <InputText
                        id="etat_civil"
                        v-model="newFormateur.etat_civil"
                        :class="{ 'p-invalid': errors.etat_civil }"
                    />
                    <small v-if="errors.etat_civil" class="p-error">{{
                        errors.etat_civil[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="formateur_conseiller"
                        >Formateur/Conseiller</label
                    >
                    <InputText
                        id="formateur_conseiller"
                        v-model="newFormateur.formateur_conseiller"
                        :class="{ 'p-invalid': errors.formateur_conseiller }"
                    />
                    <small v-if="errors.formateur_conseiller" class="p-error">{{
                        errors.formateur_conseiller[0]
                    }}</small>
                </div>
                <div class="field">
                    <label for="statut">Statut</label>
                    <Dropdown
                        id="statut"
                        v-model="newFormateur.statut"
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
                    <label for="type">Type</label>
                    <InputText
                        id="type"
                        v-model="newFormateur.type"
                        :class="{ 'p-invalid': errors.type }"
                    />
                    <small v-if="errors.type" class="p-error">{{
                        errors.type[0]
                    }}</small>
                </div>
                <div class="field" v-if="isCentreRole === 0">
                    <label for="centre_id"
                        >Centre <span class="text-red-500">*</span></label
                    >
                    <Dropdown
                        id="centre_id"
                        v-model="newFormateur.centre_id"
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
                    :label="formateurToEdit ? 'Mettre à jour' : 'Créer'"
                    icon="pi pi-check"
                    :loading="saving"
                    @click="
                        formateurToEdit ? updateFormateur() : createFormateur()
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
                <p v-if="formateurToDelete">
                    Êtes-vous sûr de vouloir supprimer le formateur
                    <strong
                        >{{ formateurToDelete.user.name }}
                        {{ formateurToDelete.prenom_fr }}</strong
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
                    @click="deleteFormateur"
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
import InputNumber from 'primevue/inputnumber';
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
        InputNumber,
        Toast,
    },
    data() {
        return {
            formateurs: [],
            filters: null,
            loading: false,
            formVisible: false,
            saving: false,
            deleteFormVisible: false,
            formateurToEdit: null,
            formateurToDelete: null,
            newFormateur: {
                nom_fr: '',
                prenom_fr: '',
                email: '',
                matricule: '',
                nom_ar: '',
                prenom_ar: null,
                niveau_etude: null,
                specialite_diplome_fr: null,
                specialite_diplome_ar: null,
                matiere_enseigne_fr: null,
                matiere_enseigne_ar: null,
                date_prise_fonction: null,
                date_fin_mission: null,
                code_niveau: null,
                charge_horaire: null,
                rabattement: null,
                code_secteur: null,
                code_sous_secteur: null,
                cin: null,
                date_naissance: null,
                lieu_naissance: null,
                adresse_fr: null,
                adresse_ar: null,
                gouvernorat: null,
                delegation: null,
                tele1: null,
                tele2: null,
                mail: null,
                etat_civil: null,
                formateur_conseiller: null,
                statut: 'actif',
                type: null,
                centre_id: localStorage.getItem('centreId') || null,
            },
            statutOptions: [
                { label: 'Actif', value: 'actif' },
                { label: 'Inactif', value: 'inactif' },
                { label: 'En congé', value: 'en_conge' },
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
        this.fetchFormateurs();
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
                prenom_fr: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                mail: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                specialite_diplome_fr: {
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
        async fetchFormateurs() {
            try {
                this.loading = true;
                const response = await axios.get('/formateurs');
                this.formateurs = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors du chargement des formateurs.',
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
                    this.newFormateur.centre_id = response.data.centre_id;
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
            this.formateurToEdit = null;
            this.resetForm();
            this.errors = {};
            this.formVisible = true;
        },
        closeForm() {
            this.formVisible = false;
            this.formateurToEdit = null;
            this.resetForm();
            this.errors = {};
        },
        async createFormateur() {
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
                    !this.newFormateur.nom_fr ||
                    !this.newFormateur.prenom_fr ||
                    !this.newFormateur.email ||
                    !this.newFormateur.matricule ||
                    !this.newFormateur.nom_ar
                ) {
                    throw new Error(
                        'Les champs Nom (FR), Prénom (FR), Email, Matricule et Nom (AR) sont obligatoires.'
                    );
                }
                if (this.isCentreRole === 0 && !this.newFormateur.centre_id) {
                    throw new Error(
                        'Le champ Centre est obligatoire pour un utilisateur central.'
                    );
                }

                const formData = {
                    ...this.newFormateur,
                    email: this.newFormateur.email.toLowerCase(),
                    centre_id:
                        this.isCentreRole === 1
                            ? localStorage.getItem('centreId')
                            : this.newFormateur.centre_id,
                };
                Object.keys(formData).forEach((key) => {
                    if (formData[key] === '' && key !== 'centre_id') {
                        formData[key] = null;
                    }
                });

                console.log('Données envoyées:', formData);

                const response = await axios.post('/formateurs', formData);
                this.formateurs.unshift(response.data.formateur);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: `Formateur créé ! Mot de passe : ${response.data.generated_password}`,
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
        editFormateur(formateur) {
            this.formateurToEdit = formateur;
            this.newFormateur = {
                nom_fr: formateur.nom_fr || '',
                prenom_fr: formateur.prenom_fr || '',
                email: formateur.user.email || '',
                matricule: formateur.matricule || '',
                nom_ar: formateur.nom_ar || '',
                prenom_ar: formateur.prenom_ar || null,
                niveau_etude: formateur.niveau_etude || null,
                specialite_diplome_fr: formateur.specialite_diplome_fr || null,
                specialite_diplome_ar: formateur.specialite_diplome_ar || null,
                matiere_enseigne_fr: formateur.matiere_enseigne_fr || null,
                matiere_enseigne_ar: formateur.matiere_enseigne_ar || null,
                date_prise_fonction: formateur.date_prise_fonction || null,
                date_fin_mission: formateur.date_fin_mission || null,
                code_niveau: formateur.code_niveau || null,
                charge_horaire: formateur.charge_horaire || null,
                rabattement: formateur.rabattement || null,
                code_secteur: formateur.code_secteur || null,
                code_sous_secteur: formateur.code_sous_secteur || null,
                cin: formateur.cin || null,
                date_naissance: formateur.date_naissance || null,
                lieu_naissance: formateur.lieu_naissance || null,
                adresse_fr: formateur.adresse_fr || null,
                adresse_ar: formateur.adresse_ar || null,
                gouvernorat: formateur.gouvernorat || null,
                delegation: formateur.delegation || null,
                tele1: formateur.tele1 || null,
                tele2: formateur.tele2 || null,
                mail: formateur.mail || null,
                etat_civil: formateur.etat_civil || null,
                formateur_conseiller: formateur.formateur_conseiller || null,
                statut: formateur.statut || 'actif',
                type: formateur.type || null,
                centre_id:
                    formateur.centres?.[0]?.id ||
                    (this.isCentreRole === 1
                        ? localStorage.getItem('centreId')
                        : null),
            };
            this.errors = {};
            this.formVisible = true;
        },
        async updateFormateur() {
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
                    !this.newFormateur.nom_fr ||
                    !this.newFormateur.prenom_fr ||
                    !this.newFormateur.email ||
                    !this.newFormateur.matricule ||
                    !this.newFormateur.nom_ar
                ) {
                    throw new Error(
                        'Les champs Nom (FR), Prénom (FR), Email, Matricule et Nom (AR) sont obligatoires.'
                    );
                }
                if (this.isCentreRole === 0 && !this.newFormateur.centre_id) {
                    throw new Error(
                        'Le champ Centre est obligatoire pour un utilisateur central.'
                    );
                }

                const formData = {
                    ...this.newFormateur,
                    email: this.newFormateur.email.toLowerCase(),
                    centre_id:
                        this.isCentreRole === 1
                            ? localStorage.getItem('centreId')
                            : this.newFormateur.centre_id,
                };
                Object.keys(formData).forEach((key) => {
                    if (formData[key] === '' && key !== 'centre_id') {
                        formData[key] = null;
                    }
                });

                console.log('Données envoyées:', formData);

                const response = await axios.put(
                    `/formateurs/${this.formateurToEdit.id}`,
                    formData
                );
                const index = this.formateurs.findIndex(
                    (f) => f.id === this.formateurToEdit.id
                );
                if (index !== -1)
                    this.formateurs.splice(index, 1, response.data.formateur);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Formateur mis à jour.',
                    life: 3000,
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
                            'Erreur lors de la mise à jour.',
                        life: 3000,
                    });
                }
            } finally {
                this.saving = false;
            }
        },
        confirmDeleteFormateur(formateur) {
            this.formateurToDelete = formateur;
            this.deleteFormVisible = true;
        },
        async deleteFormateur() {
            if (this.formateurToDelete) {
                try {
                    await axios.delete(
                        `/formateurs/${this.formateurToDelete.id}`
                    );
                    this.formateurs = this.formateurs.filter(
                        (f) => f.id !== this.formateurToDelete.id
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Formateur supprimé.',
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
                    this.formateurToDelete = null;
                }
            }
        },
        resetForm() {
            this.newFormateur = {
                nom_fr: '',
                prenom_fr: '',
                email: '',
                matricule: '',
                nom_ar: '',
                prenom_ar: null,
                niveau_etude: null,
                specialite_diplome_fr: null,
                specialite_diplome_ar: null,
                matiere_enseigne_fr: null,
                matiere_enseigne_ar: null,
                date_prise_fonction: null,
                date_fin_mission: null,
                code_niveau: null,
                charge_horaire: null,
                rabattement: null,
                code_secteur: null,
                code_sous_secteur: null,
                cin: null,
                date_naissance: null,
                lieu_naissance: null,
                adresse_fr: null,
                adresse_ar: null,
                gouvernorat: null,
                delegation: null,
                tele1: null,
                tele2: null,
                mail: null,
                etat_civil: null,
                formateur_conseiller: null,
                statut: 'actif',
                type: null,
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
