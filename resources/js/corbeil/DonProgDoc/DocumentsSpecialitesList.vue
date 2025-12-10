<template>
    <DataTable
        v-model:expandedRows="expandedRows"
        v-model:filters="filters"
        stripedRows
        :value="filteredSpecialites"
        :rows="10"
        dataKey="id"
        filterDisplay="menu"
        :globalFilterFields="[
            'id',
            'code',
            'nom_ar',
            'nom_fr',
            'type',
            'diplome',
            'statut',
        ]"
        :loading="loading"
        scrollable
        scrollHeight="650px"
        :pt="{
            table: { style: 'min-width: 50rem' },
            bodyrow: ({ props }) => ({
                class: [{ 'font-bold': props.frozenRow }],
            }),
        }"
    >
        <template #header>
            <div class="flex flex-column gap-2">
                <!-- Première ligne : Titre avec filtre année et boutons -->
                <div
                    class="flex justify-content-between align-items-center mb-3"
                >
                    <div class="text-2xl font-bold text-primary">
                        Liste des documents
                        <span :style="{ color: '#EF4444' }">actifs</span>
                        <span class="font-bold text-primary"> de l'année </span>
                        <span :style="{ color: '#EF4444' }">{{
                            selectedAnnee
                                ? getAnneeIntitule(selectedAnnee)
                                : 'en cours'
                        }}</span>
                    </div>
                    <div class="flex gap-2 align-items-center">
                        <Dropdown
                            v-model="selectedAnnee"
                            :options="annees"
                            optionLabel="intitule"
                            optionValue="id"
                            placeholder="Sélectionner une année"
                            @change="filterByAnnee"
                        />
                        <Button
                            label="مقيس"
                            :severity="getSeverity('مقيس')"
                            @click="setActiveTab('مقيس')"
                        />
                        <Button
                            label="غير مقيس"
                            :severity="getSeverity('غير مقيس')"
                            @click="setActiveTab('غير مقيس')"
                        />
                        <Button
                            label="جديد"
                            :severity="getSeverity('جديد')"
                            @click="setActiveTab('جديد')"
                        />
                    </div>
                </div>
                <!-- Deuxième ligne : Recherche et boutons dans un cadre fin -->
                <div
                    class="flex justify-content-between align-items-center mb-2 p-2 border-round surface-border border-1"
                >
                    <div class="flex gap-2 align-items-center">
                        <span class="p-input-icon-right">
                            <InputText
                                v-model="globalFilter"
                                placeholder="Rechercher dans les spécialités"
                                @input="onGlobalFilter"
                            />
                            <i class="pi pi-search" />
                        </span>
                        <Button
                            icon="pi pi-times"
                            severity="secondary"
                            @click="clearSearch"
                        />
                    </div>
                    <div class="flex gap-2 align-items-center">
                        <Button
                            text
                            icon="pi pi-minus"
                            label="Tout Réduire"
                            @click="collapseAll"
                        />
                    </div>
                </div>
            </div>
        </template>

        <Column expander style="width: 5rem" />
        <Column field="id" header="ID" :showFilterMenu="false">
            <template #filter="{ filterModel, filterCallback }">
                <InputText
                    v-model="filterModel.value"
                    @input="filterCallback()"
                    placeholder="Rechercher par ID"
                />
            </template>
        </Column>
        <Column field="code" header="Code" sortable style="min-width: 10rem">
            <template #body="{ data }">
                {{ data.code }}
            </template>
            <template #filter="{ filterModel, filterCallback }">
                <InputText
                    v-model="filterModel.value"
                    type="text"
                    placeholder="Rechercher par code"
                    @input="filterCallback"
                />
            </template>
        </Column>
        <Column field="nom_fr" header="Nom (Fr)" :showFilterMenu="false">
            <template #filter="{ filterModel, filterCallback }">
                <InputText
                    v-model="filterModel.value"
                    @input="filterCallback()"
                    placeholder="Rechercher par Nom (Fr)"
                />
            </template>
        </Column>
        <Column field="nom_ar" header="Nom (AR)" :showFilterMenu="false">
            <template #body="{ data }">
                {{ data.nom_ar }}
            </template>
            <template #filter="{ filterModel, filterCallback }">
                <InputText
                    v-model="filterModel.value"
                    @input="filterCallback()"
                    placeholder="Rechercher par Nom (AR)"
                />
            </template>
        </Column>
        <Column field="diplome" header="Diplôme">
            <template #body="{ data }">
                <Tag
                    :value="data.diplome"
                    :severity="getSeverity(data.diplome)"
                />
            </template>
            <template #filter="{ filterModel, filterCallback }">
                <Dropdown
                    v-model="filterModel.value"
                    :options="['BTS', 'BTP', 'CAP', 'CC', 'CFA']"
                    placeholder="Sélectionner un diplôme"
                    @change="filterCallback()"
                />
            </template>
        </Column>
        <Column field="documentsCount" header="Documents">
            <template #body="{ data }">
                {{ data.documents ? data.documents.length : 0 }}
            </template>
        </Column>

        <template #expansion="slotProps">
            <div class="p-3 surface-100">
                <div
                    class="flex justify-content-between align-items-center mb-2"
                >
                    <h5 style="font-weight: bold">
                        <span style="color: #93c5fd"
                            >Documents disponibles pour la spécialité
                        </span>
                        <span style="color: #ef4444">{{
                            slotProps.data.nom_fr
                        }}</span>
                    </h5>
                    <div class="flex gap-2 align-items-center">
                        <InputText
                            v-model="documentFilter[slotProps.data.id]"
                            placeholder="Rechercher dans les documents"
                            @input="onDocumentFilter(slotProps.data.id)"
                        />
                        <Button
                            icon="pi pi-plus"
                            severity="success"
                            @click="openDocumentForm(slotProps.data)"
                        />
                    </div>
                </div>
                <DataTable
                    :value="
                        filteredDocuments(
                            slotProps.data.documents,
                            slotProps.data.id
                        )
                    "
                    dataKey="id"
                >
                    <Column field="id" header="Id"></Column>
                    <Column field="version" header="Version"></Column>
                    <Column
                        field="type_document"
                        header="Type Document"
                    ></Column>
                    <Column field="fichier" header="Fichier">
                        <template #body="{ data }">
                            <span v-if="data.fichier">
                                <a
                                    :href="`/storage/${data.fichier}`"
                                    target="_blank"
                                    @click.prevent="
                                        downloadDocument(data.id, data.fichier)
                                    "
                                    class="text-blue-600 hover:underline"
                                    >Télécharger</a
                                >
                            </span>
                            <span v-else>N/A</span>
                        </template>
                    </Column>
                    <Column field="date_debut" header="Date Début"></Column>
                    <Column field="date_fin" header="Date Fin"></Column>
                    <Column field="actif" header="Actif">
                        <template #body="{ data }">
                            <i
                                :class="[
                                    'pi',
                                    data.actif
                                        ? 'pi-check-circle text-green-500'
                                        : 'pi-times-circle text-red-500',
                                ]"
                            ></i>
                        </template>
                    </Column>
                    <Column
                        header="Actions"
                        :exportable="false"
                        style="min-width: 8rem"
                    >
                        <template #body="slotProps">
                            <Button
                                icon="pi pi-pencil"
                                severity="info"
                                text
                                @click="editDocument(slotProps.data)"
                            />
                            <Button
                                icon="pi pi-trash"
                                severity="danger"
                                text
                                @click="showDeleteConfirm(slotProps.data)"
                            />
                        </template>
                    </Column>
                    <template #empty>
                        <div class="text-center p-3">
                            Aucun document disponible.
                        </div>
                    </template>
                </DataTable>
            </div>
        </template>
    </DataTable>

    <DocumentsSpecialitesForm
        :visible="documentFormVisible"
        :initialData="selectedDocument"
        :specialiteId="
            selectedSpecialiteForDocument
                ? selectedSpecialiteForDocument.id
                : null
        "
        @update:visible="documentFormVisible = $event"
        @save="handleDocumentSaved"
        @update="handleDocumentUpdated"
    />

    <Dialog
        v-model:visible="deleteDialogVisible"
        header="Confirmation de suppression"
        :modal="true"
        :style="{ width: '450px' }"
    >
        <div class="p-fluid">
            <p>Êtes-vous sûr de vouloir supprimer ce document ?</p>
        </div>
        <template #footer>
            <Button
                label="Non"
                icon="pi pi-times"
                text
                @click="deleteDialogVisible = false"
            />
            <Button
                label="Oui"
                icon="pi pi-check"
                text
                @click="confirmDelete"
            />
        </template>
    </Dialog>

    <Toast />
</template>

<script>
import axios from 'axios';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import { FilterMatchMode } from 'primevue/api';
import { useToast } from 'primevue/usetoast';
import DocumentsSpecialitesForm from '@/corbeil/DonProgDoc/DocumentsSpecialitesForm.vue';

export default {
    components: {
        DataTable,
        Column,
        Tag,
        InputText,
        Dropdown,
        Button,
        Dialog,
        Toast,
        DocumentsSpecialitesForm,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            specialites: [],
            expandedRows: [],
            loading: true,
            activeTab: 'مقيس',
            filters: {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                id: { value: null, matchMode: FilterMatchMode.EQUALS },
                code: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_ar: { value: null, matchMode: FilterMatchMode.CONTAINS },
                diplome: { value: null, matchMode: FilterMatchMode.EQUALS },
            },
            documentFormVisible: false,
            selectedDocument: {},
            selectedSpecialiteForDocument: null,
            annees: [],
            selectedAnnee: null,
            globalFilter: '',
            documentFilter: {},
            deleteDialogVisible: false,
            itemToDelete: null,
        };
    },
    computed: {
        filteredSpecialites() {
            let result = this.specialites.filter(
                (s) => s.type === this.activeTab
            );
            if (this.globalFilter) {
                const filterLower = this.globalFilter.toLowerCase();
                result = result.filter(
                    (s) =>
                        s.id.toString().includes(filterLower) ||
                        s.code?.toLowerCase().includes(filterLower) ||
                        s.nom_fr?.toLowerCase().includes(filterLower) ||
                        s.nom_ar?.toLowerCase().includes(filterLower) ||
                        s.diplome?.toLowerCase().includes(filterLower)
                );
            }
            return result;
        },
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                const response = await axios.get('/api/specialites');
                this.specialites = response.data.map((s) => {
                    s.documents = s.documents || [];
                    return s;
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec de la récupération des données',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
        async fetchAnnees() {
            try {
                const response = await axios.get('/api/annees-formation');
                this.annees = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les années.',
                    life: 3000,
                });
            }
        },
        filterByAnnee() {
            this.fetchData();
        },
        getAnneeIntitule(anneeId) {
            const annee = this.annees.find((a) => a.id === anneeId);
            return annee ? annee.intitule : '';
        },
        filteredDocuments(documents, specialiteId) {
            let result = documents;
            if (this.selectedAnnee) {
                const selectedAnnee = this.annees.find(
                    (a) => a.id === this.selectedAnnee
                );
                if (selectedAnnee) {
                    const dateDebutAnnee = new Date(selectedAnnee.date_debut);
                    result = result.filter((d) => {
                        const dateDebutDocument = new Date(d.date_debut);
                        const dateFinDocument = d.date_fin
                            ? new Date(d.date_fin)
                            : null;
                        return (
                            (!dateFinDocument ||
                                dateFinDocument >= dateDebutAnnee) &&
                            dateDebutDocument <= dateDebutAnnee
                        );
                    });
                }
            }
            if (this.documentFilter[specialiteId]) {
                const filterLower =
                    this.documentFilter[specialiteId].toLowerCase();
                result = result.filter(
                    (d) =>
                        d.id.toString().includes(filterLower) ||
                        d.version?.toLowerCase().includes(filterLower) ||
                        d.type_document?.toLowerCase().includes(filterLower) ||
                        d.fichier?.toLowerCase().includes(filterLower) ||
                        d.date_debut?.toLowerCase().includes(filterLower) ||
                        d.date_fin?.toLowerCase().includes(filterLower)
                );
            }
            return result;
        },
        getSeverity(value) {
            const severityMap = {
                مقيس: this.activeTab === 'مقيس' ? 'success' : 'secondary',
                'غير مقيس':
                    this.activeTab === 'غير مقيس' ? 'danger' : 'secondary',
                جديد: this.activeTab === 'جديد' ? 'warning' : 'secondary',
                BTS: 'info',
                BTP: 'secondary',
                CAP: 'warning',
                CC: 'success',
                CFA: 'danger',
            };
            return severityMap[value] || 'secondary';
        },
        collapseAll() {
            this.expandedRows = [];
        },
        setActiveTab(tab) {
            this.activeTab = tab;
        },
        clearSearch() {
            this.globalFilter = '';
            this.$forceUpdate();
        },
        openDocumentForm(specialite) {
            this.selectedSpecialiteForDocument = specialite;
            this.selectedDocument = {};
            this.documentFormVisible = true;
        },
        editDocument(document) {
            this.selectedDocument = { ...document };
            this.selectedSpecialiteForDocument = this.specialites.find(
                (s) => s.id === document.specialite_id
            );
            this.documentFormVisible = true;
        },
        showDeleteConfirm(document) {
            this.itemToDelete = document;
            this.deleteDialogVisible = true;
        },
        async confirmDelete() {
            if (this.itemToDelete) {
                try {
                    await axios.delete(
                        `/api/documents/${this.itemToDelete.id}`
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Document.php supprimé avec succès',
                        life: 3000,
                    });
                    this.fetchData();
                } catch (error) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Échec de la suppression du document',
                        life: 3000,
                    });
                }
            }
            this.deleteDialogVisible = false;
            this.itemToDelete = null;
        },
        handleDocumentSaved() {
            this.documentFormVisible = false;
            this.fetchData();
        },
        handleDocumentUpdated() {
            this.documentFormVisible = false;
            this.fetchData();
        },
        async downloadDocument(documentId, fileName) {
            try {
                const response = await axios({
                    url: `/api/documents/${documentId}/download`,
                    method: 'GET',
                    responseType: 'blob',
                });
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', fileName);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
            } catch (error) {
                console.error('Erreur lors du téléchargement:', error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Échec du téléchargement.',
                    life: 3000,
                });
            }
        },
        onGlobalFilter() {
            this.$forceUpdate();
        },
        onDocumentFilter() {
            this.$forceUpdate();
        },
    },
    mounted() {
        this.fetchData();
        this.fetchAnnees();
    },
};
</script>
