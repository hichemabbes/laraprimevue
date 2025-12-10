<template>
    <div class="surface-ground pt-2 px-1.5 pb-1.5" style="margin-top: 0">
        <!-- Header Section with Navbar -->
        <div
            class="surface-card p-2 border-round border-1 surface-border"
            style="
                position: relative;
                top: -34px;
                box-shadow: none;
                margin-bottom: -32px;
            "
        >
            <div class="flex justify-content-between align-items-center">
                <div class="flex gap-3">
                    <Button
                        label="Accueil"
                        icon="pi pi-home"
                        class="p-button-text p-button-plain"
                        @click="$router.push({ name: 'dashboard' })"
                    />
                    <Button
                        label="Centres"
                        icon="pi pi-building"
                        class="p-button-text p-button-plain"
                        disabled
                    />
                </div>
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-refresh"
                        class="p-button-text p-button-plain"
                        @click="fetchCentres"
                        v-tooltip="'Rafraîchir'"
                    />
                    <Button
                        icon="pi pi-file-import"
                        class="p-button-text p-button-plain"
                        @click="$refs.fileInput.click()"
                        v-tooltip="'Importer XLS'"
                    />
                    <input
                        type="file"
                        ref="fileInput"
                        style="display: none"
                        accept=".xls,.xlsx"
                        @change="importXls"
                    />
                    <Button
                        icon="pi pi-file-export"
                        class="p-button-text p-button-plain"
                        @click="exportXls"
                        v-tooltip="'Exporter XLS'"
                    />
                </div>
            </div>
        </div>

        <!-- Main Content Section -->
        <div
            class="surface-card p-4 pt-2 border-round shadow-2 border-1 surface-border"
        >
            <!-- Table Header with Actions -->
            <div class="flex justify-content-between align-items-center mb-4">
                <div class="flex align-items-center gap-2">
                    <span class="p-input-icon-left search-field">
                        <i class="pi pi-search" />
                        <InputText
                            v-model="filters['global'].value"
                            placeholder="Rechercher..."
                        />
                    </span>
                    <Button
                        icon="pi pi-filter-slash"
                        class="p-button-outlined p-button-secondary"
                        size="small"
                        @click="clearFilter"
                        v-tooltip="'Réinitialiser les filtres'"
                    />
                </div>
                <Button
                    icon="pi pi-plus"
                    label="Ajouter"
                    class="p-button-success"
                    @click="openForm"
                />
            </div>

            <!-- Main DataTable -->
            <DataTable
                v-model:filters="filters"
                :value="centres"
                dataKey="id"
                size="small"
                stripedRows
                paginator
                :rows="20"
                :rowsPerPageOptions="[20, 50, 100]"
                filterDisplay="menu"
                :globalFilterFields="[
                    'code',
                    'nom_fr',
                    'nom_ar',
                    'gouvernorat_fr',
                    'statut_fr',
                    'telephone_1',
                    'email',
                    'economat.nom_fr',
                    'type',
                    'classe',
                ]"
                :loading="loading"
                scrollable
                scrollHeight="700px"
                removableSort
                class="p-datatable-sm border-1 surface-border"
            >
                <template #empty>
                    <div class="text-center py-4">
                        <i class="pi pi-inbox text-4xl text-400 mb-2" />
                        <p class="text-600">Aucun centre trouvé</p>
                    </div>
                </template>

                <Column
                    field="code"
                    header="Code"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <span class="flex align-items-center gap-2">
                            <i
                                class="pi pi-circle-fill"
                                :class="
                                    data.statut_fr === 'Fonctionnel'
                                        ? 'text-green-500'
                                        : 'text-red-500'
                                "
                                style="font-size: 0.5rem"
                            />
                            <span class="font-medium">{{ data.code }}</span>
                        </span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par code"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>

                <Column
                    field="nom_fr"
                    header="Nom (FR)"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.nom_fr }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par nom (FR)"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>

                <Column
                    field="nom_ar"
                    header="Nom (AR)"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.nom_ar }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par nom (AR)"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>

                <Column
                    field="gouvernorat_fr"
                    header="Gouvernorat"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <Tag :value="data.gouvernorat_fr" severity="info" />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="gouvernorats"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Sélectionner un gouvernorat"
                            class="p-column-filter"
                            :showClear="true"
                            @change="filterCallback()"
                        />
                    </template>
                </Column>

                <Column
                    field="telephone_1"
                    header="Téléphone"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.telephone_1 }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par téléphone"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>

                <Column
                    field="email"
                    header="Email"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.email || '-' }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par email"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>

                <Column
                    field="economat.nom_fr"
                    header="Économat (FR)"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="data.economat?.nom_fr || '-'"
                            severity="warning"
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="economats"
                            optionLabel="nom_fr"
                            optionValue="nom_fr"
                            placeholder="Sélectionner un économat"
                            class="p-column-filter"
                            :showClear="true"
                            @change="filterCallback()"
                        />
                    </template>
                </Column>

                <Column
                    field="type"
                    header="Type"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="getTypeLabel(data.type)"
                            :severity="getTypeSeverity(data.type)"
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="types"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Sélectionner un type"
                            class="p-column-filter"
                            :showClear="true"
                            @change="filterCallback()"
                        />
                    </template>
                </Column>

                <Column
                    field="classe"
                    header="Classe"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="data.classe || '-'"
                            :severity="getClasseSeverity(data.classe)"
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="classes"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Sélectionner une classe"
                            class="p-column-filter"
                            :showClear="true"
                            @change="filterCallback()"
                        />
                    </template>
                </Column>

                <Column
                    field="statut_fr"
                    header="Statut"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="data.statut_fr"
                            :severity="
                                data.statut_fr === 'Fonctionnel'
                                    ? 'success'
                                    : 'danger'
                            "
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="statuts"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Sélectionner un statut"
                            class="p-column-filter"
                            :showClear="true"
                            @change="filterCallback()"
                        />
                    </template>
                </Column>

                <Column header="Actions" style="min-width: 10rem">
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Button
                                icon="pi pi-eye"
                                class="p-button-text p-button-rounded p-button-primary"
                                v-tooltip="'Voir les détails'"
                                @click="viewCentre(data)"
                            />
                            <Button
                                icon="pi pi-pencil"
                                class="p-button-text p-button-rounded p-button-warning"
                                v-tooltip="'Modifier'"
                                @click="editCentre(data)"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-text p-button-rounded p-button-danger"
                                v-tooltip="'Supprimer'"
                                @click="confirmDelete(data)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <!-- Dialogs -->
            <CentresForm
                :visible="showForm"
                :centreToEdit="centreToEdit"
                @update:visible="showForm = $event"
                @close="closeForm"
                @save="onCentreSaved"
                @update="onCentreUpdated"
            />
            <Dialog
                v-model:visible="showDeleteDialog"
                header="Confirmation de suppression"
                :modal="true"
                :style="{ width: '400px' }"
            >
                <div class="flex align-items-center gap-2">
                    <i
                        class="pi pi-exclamation-triangle text-red-500 text-2xl"
                    ></i>
                    <span>Voulez-vous vraiment supprimer ce centre ?</span>
                </div>
                <div class="mt-3">
                    <label for="password" class="block mb-2"
                        >Mot de passe</label
                    >
                    <InputText
                        id="password"
                        v-model="deletePassword"
                        type="password"
                        class="w-full"
                        :class="{ 'p-invalid': passwordError }"
                    />
                    <small v-if="passwordError" class="p-error">{{
                        passwordError
                    }}</small>
                </div>
                <template #footer>
                    <Button
                        label="Annuler"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="showDeleteDialog = false"
                    />
                    <Button
                        label="Supprimer"
                        icon="pi pi-check"
                        class="p-button-danger"
                        @click="deleteCentre"
                        :loading="deleting"
                    />
                </template>
            </Dialog>
            <CentreDetails
                :showViewDialog="showViewDialog"
                :selectedCentre="selectedCentre"
                @update:showViewDialog="showViewDialog = $event"
                @edit="editCentre"
                @refresh="fetchCentres"
            />
            <ImportErrors
                :visible="showImportErrors"
                :errors="importErrors"
                @update:visible="showImportErrors = $event"
                @close="showImportErrors = false"
                @line-imported="handleLineImported"
            />
            <!-- Dialogue pour les instructions des macros -->
            <Dialog
                v-model:visible="showMacroInstructions"
                header="Instructions pour activer les macros"
                :modal="true"
                :style="{ width: '500px' }"
            >
                <div class="p-3">
                    <p class="mb-3">
                        Le fichier <strong>centres_export.xlsm</strong> contient
                        des macros qui sont bloquées par Excel pour des raisons
                        de sécurité. Suivez ces étapes pour activer les macros :
                    </p>
                    <ol class="list-decimal ml-5 mb-3">
                        <li>
                            Cliquez droit sur le fichier téléchargé dans
                            l'Explorateur Windows et sélectionnez
                            <strong>Propriétés</strong>.
                        </li>
                        <li>
                            Dans l'onglet <strong>Général</strong>, cochez la
                            case <strong>Débloquer</strong> (en bas).
                        </li>
                        <li>Cliquez sur <strong>OK</strong>.</li>
                        <li>Ouvrez le fichier dans Excel.</li>
                        <li>
                            Lorsque Excel affiche un avertissement de sécurité,
                            cliquez sur <strong>Activer le contenu</strong>.
                        </li>
                    </ol>
                    <p>
                        <strong>Note :</strong> Assurez-vous que le fichier
                        provient de cette application avant d'activer les
                        macros.
                    </p>
                </div>
                <template #footer>
                    <Button
                        label="Fermer"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="showMacroInstructions = false"
                    />
                </template>
            </Dialog>
        </div>
        <Toast position="top-right" />
    </div>
</template>

<script>
import { FilterMatchMode } from 'primevue/api';
import axios from 'axios';
import { saveAs } from 'file-saver';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';
import CentreDetails from '@/Components/Atfp/Centres/CentreDetails.vue';
import ImportErrors from '@/Components/Atfp/ImportError/CentresImportError.vue';
import { useToast } from 'primevue/usetoast';
import { useRouter } from 'vue-router';

export default {
    components: {
        Button,
        Column,
        DataTable,
        Dialog,
        Dropdown,
        InputText,
        Tag,
        Toast,
        CentreDetails,
        ImportErrors,
    },
    setup() {
        const toast = useToast();
        const router = useRouter();
        return { toast, router };
    },
    data() {
        return {
            centres: [],
            economats: [],
            loading: false,
            filters: {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_ar: { value: null, matchMode: FilterMatchMode.CONTAINS },
                gouvernorat_fr: {
                    value: null,
                    matchMode: FilterMatchMode.EQUALS,
                },
                statut_fr: { value: null, matchMode: FilterMatchMode.EQUALS },
                telephone_1: {
                    value: null,
                    matchMode: FilterMatchMode.CONTAINS,
                },
                email: { value: null, matchMode: FilterMatchMode.CONTAINS },
                'economat.nom_fr': {
                    value: null,
                    matchMode: FilterMatchMode.EQUALS,
                },
                type: { value: null, matchMode: FilterMatchMode.EQUALS },
                classe: { value: null, matchMode: FilterMatchMode.EQUALS },
            },
            showForm: false,
            centreToEdit: null,
            showDeleteDialog: false,
            centreToDelete: null,
            deletePassword: '',
            passwordError: '',
            deleting: false,
            showViewDialog: false,
            selectedCentre: null,
            showImportErrors: false,
            importErrors: [],
            showMacroInstructions: false, // Nouvelle variable pour le dialogue des macros
            gouvernorats: [
                { label: 'Tunis', value: 'Tunis' },
                { label: 'Ariana', value: 'Ariana' },
                { label: 'Manouba', value: 'Manouba' },
                { label: 'Ben Arous', value: 'Ben Arous' },
                { label: 'Nabeul', value: 'Nabeul' },
                { label: 'Zaghouan', value: 'Zaghouan' },
                { label: 'Bizerte', value: 'Bizerte' },
                { label: 'Béja', value: 'Béja' },
                { label: 'Jendouba', value: 'Jendouba' },
                { label: 'Kef', value: 'Kef' },
                { label: 'Siliana', value: 'Siliana' },
                { label: 'Kairouan', value: 'Kairouan' },
                { label: 'Kasserine', value: 'Kasserine' },
                { label: 'Sidi Bouzid', value: 'Sidi Bouzid' },
                { label: 'Sousse', value: 'Sousse' },
                { label: 'Monastir', value: 'Monastir' },
                { label: 'Mahdia', value: 'Mahdia' },
                { label: 'Sfax', value: 'Sfax' },
                { label: 'Gafsa', value: 'Gafsa' },
                { label: 'Tozeur', value: 'Tozeur' },
                { label: 'Kébili', value: 'Kébili' },
                { label: 'Gabès', value: 'Gabès' },
                { label: 'Médenine', value: 'Médenine' },
                { label: 'Tataouine', value: 'Tataouine' },
            ],
            statuts: [
                { label: 'Fonctionnel', value: 'Fonctionnel' },
                { label: 'Non Fonctionnel', value: 'Non Fonctionnel' },
            ],
            types: [
                { label: 'CSF', value: '1' },
                { label: 'CFA', value: '2' },
                { label: 'CFPTI', value: '3' },
                { label: 'CFA', value: '4' },
                { label: 'CFR', value: '5' },
            ],
            classes: [
                { label: 'A', value: 'A' },
                { label: 'B', value: 'B' },
                { label: 'C', value: 'C' },
            ],
            usersCache: {},
        };
    },
    created() {
        this.fetchCentres();
        this.fetchEconomats();
    },
    methods: {
        async fetchCentres() {
            this.loading = true;
            try {
                const response = await axios.get('/api/centres');
                this.centres = response.data;
                const userIds = [
                    ...new Set(
                        this.centres
                            .flatMap((centre) => centre.historique || [])
                            .map((item) => item.user_id)
                            .filter((id) => id)
                    ),
                ];
                if (userIds.length > 0) {
                    await this.fetchUserNames(userIds);
                }
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les centres.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        async fetchEconomats() {
            try {
                const response = await axios.get('/api/economats');
                this.economats = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les économats.',
                    life: 3000,
                });
            }
        },
        async fetchUserNames(userIds) {
            const validUserIds = userIds.filter(
                (id) => id && !isNaN(id) && !this.usersCache[id]
            );
            if (validUserIds.length === 0) return;

            try {
                const response = await axios.post('/api/users/batch', {
                    user_ids: validUserIds,
                });
                const users = response.data;
                users.forEach((user) => {
                    this.usersCache[user.id] =
                        `${user.nom_fr || ''} ${user.prenom_fr || ''}`.trim() ||
                        'Utilisateur inconnu';
                });
                validUserIds.forEach((id) => {
                    if (!this.usersCache[id]) {
                        this.usersCache[id] = 'Utilisateur inconnu';
                    }
                });
            } catch (error) {
                console.error(
                    'Erreur lors du chargement des utilisateurs :',
                    error
                );
                validUserIds.forEach((id) => {
                    this.usersCache[id] = 'Utilisateur inconnu';
                });
            }
        },
        getUserName(userId) {
            if (!userId) return 'Aucun utilisateur';
            return this.usersCache[userId] || 'Utilisateur inconnu';
        },
        async importXls(event) {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('file', file);

            try {
                const response = await axios.post(
                    '/api/centres/importxls',
                    formData,
                    {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    }
                );

                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: `Importation terminée. ${response.data.success_count || 0} lignes importées avec succès, ${response.data.error_count || 0} lignes en échec.`,
                    life: 5000,
                });

                if (
                    response.data.error_lines &&
                    response.data.error_lines.length > 0
                ) {
                    this.importErrors = response.data.error_lines;
                    this.showImportErrors = true;
                } else {
                    this.importErrors = [];
                    this.showImportErrors = false;
                }

                await this.fetchCentres();
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        "Erreur lors de l'importation.",
                    life: 5000,
                });
            } finally {
                event.target.value = '';
            }
        },
        async exportXls() {
            try {
                const response = await axios.get(
                    '/api/centres/exportxls-advanced',
                    {
                        responseType: 'blob',
                    }
                );

                // Vérifier si la réponse est un blob ou une erreur JSON
                const contentType = response.headers['content-type'];
                if (contentType.includes('application/json')) {
                    const errorData = await response.data.text();
                    const errorJson = JSON.parse(errorData);
                    throw new Error(errorJson.message || 'Erreur inconnue');
                }

                const blob = new Blob([response.data], {
                    type: 'application/vnd.ms-excel.sheet.macroEnabled.12',
                });
                saveAs(blob, 'centres_export.xlsm');
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Fichier Excel exporté avec succès.',
                    life: 3000,
                });
                // Afficher le dialogue des instructions pour les macros
                this.showMacroInstructions = true;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.message ||
                        "Erreur lors de l'exportation du fichier Excel.",
                    life: 5000,
                });
            }
        },
        openForm() {
            this.centreToEdit = null;
            this.showForm = true;
        },
        editCentre(centre) {
            this.centreToEdit = { ...centre };
            this.showForm = true;
        },
        viewCentre(centre) {
            this.selectedCentre = { ...centre };
            this.showViewDialog = true;
        },
        confirmDelete(centre) {
            this.centreToDelete = centre;
            this.deletePassword = '';
            this.passwordError = '';
            this.showDeleteDialog = true;
        },
        async deleteCentre() {
            this.deleting = true;
            try {
                await axios.delete(`/api/centres/${this.centreToDelete.id}`, {
                    data: { password: this.deletePassword },
                });
                this.centres = this.centres.filter(
                    (c) => c.id !== this.centreToDelete.id
                );
                this.showDeleteDialog = false;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Centre supprimé avec succès.',
                    life: 3000,
                });
            } catch (error) {
                this.passwordError =
                    error.response?.data?.error ||
                    'Erreur lors de la suppression.';
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: this.passwordError,
                    life: 3000,
                });
            } finally {
                this.deleting = false;
            }
        },
        onCentreSaved(centre) {
            this.centres.push(centre);
            this.showForm = false;
            this.toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Centre ajouté avec succès.',
                life: 3000,
            });
        },
        onCentreUpdated(centre) {
            const index = this.centres.findIndex((c) => c.id === centre.id);
            if (index !== -1) {
                this.centres.splice(index, 1, centre);
            }
            this.showForm = false;
            this.toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Centre mis à jour avec succès.',
                life: 3000,
            });
        },
        closeForm() {
            this.showForm = false;
            this.centreToEdit = null;
        },
        clearFilter() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_ar: { value: null, matchMode: FilterMatchMode.CONTAINS },
                gouvernorat_fr: {
                    value: null,
                    matchMode: FilterMatchMode.EQUALS,
                },
                statut_fr: { value: null, matchMode: FilterMatchMode.EQUALS },
                telephone_1: {
                    value: null,
                    matchMode: FilterMatchMode.CONTAINS,
                },
                email: { value: null, matchMode: FilterMatchMode.CONTAINS },
                'economat.nom_fr': {
                    value: null,
                    matchMode: FilterMatchMode.EQUALS,
                },
                type: { value: null, matchMode: FilterMatchMode.EQUALS },
                classe: { value: null, matchMode: FilterMatchMode.EQUALS },
            };
        },
        getTypeLabel(type) {
            const typeObj = this.types.find((t) => t.value === type);
            return typeObj ? typeObj.label : '-';
        },
        getTypeSeverity(type) {
            switch (type) {
                case '1':
                    return 'success'; // CSF
                case '2':
                    return 'info'; // CFA
                case '3':
                    return 'warning'; // CFPTI
                case '4':
                    return 'info'; // CFA
                case '5':
                    return 'danger'; // CFR
                default:
                    return 'secondary';
            }
        },
        getClasseSeverity(classe) {
            switch (classe) {
                case 'A':
                    return 'success';
                case 'B':
                    return 'info';
                case 'C':
                    return 'secondary';
                default:
                    return 'secondary';
            }
        },
        formatDate(date) {
            if (!date) return '-';
            const d = new Date(date);
            return d.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            });
        },
        formatDateTime(date) {
            if (!date) return '-';
            const d = new Date(date);
            return d.toLocaleString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        },
        onImageError() {
            this.toast.add({
                severity: 'warn',
                summary: 'Erreur',
                detail: "Impossible de charger l'image du logo.",
                life: 3000,
            });
        },
        async handleLineImported(lineData) {
            try {
                const response = await axios.post(
                    '/api/centres/import-line',
                    lineData
                );
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Ligne importée avec succès.',
                    life: 3000,
                });
                this.importErrors = this.importErrors.filter(
                    (e) => e.line !== lineData.line
                );
                if (this.importErrors.length === 0) {
                    this.showImportErrors = false;
                }
                await this.fetchCentres();
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        "Erreur lors de l'importation de la ligne.",
                    life: 3000,
                });
            }
        },
    },
};
</script>

<style scoped>
.search-field {
    min-width: 250px;
}
</style>
