<template>
    <div>
        <!-- Start Table -->
        <div class="card">
            <DataTable
                v-model:filters="filters"
                showGridlines
                stripedRows
                v-model:selection="selectedSecteurs"
                :frozenValue="FixLignes"
                :value="secteurs"
                dataKey="id"
                size="small"
                paginator
                :rows="100"
                filterDisplay="menu"
                :globalFilterFields="['code', 'nom_fr', 'nom_ar', 'type']"
                :loading="loading"
                scrollable
                scrollHeight="650px"
                :pt="{
                    table: { style: 'min-width: 50rem' },
                    bodyrow: ({ props }) => ({
                        class: [
                            {
                                'font-bold': props.frozenRow,
                                'text-blue-400': props.frozenRow,
                            },
                        ],
                    }),
                }"
            >
                <!-- Header Section -->
                <template #header>
                    <div
                        class="flex justify-content-between mb-2 align-items-center"
                    >
                        <!-- Section gauche -->
                        <div class="flex align-items-center">
                            <!-- Bouton pour ouvrir la Popup d'ajout -->
                            <Button
                                icon="pi pi-plus"
                                severity="success"
                                size="small"
                                class="mr-2"
                                @click="openAddSecteurPopup"
                            />
                            <!-- Champ de recherche -->
                            <span class="p-input-icon-left mr-2">
                                <i class="pi pi-search" />
                                <InputText
                                    v-model="filters['global'].value"
                                    size="small"
                                    placeholder="Recherche..."
                                    style="width: 100%"
                                />
                            </span>
                            <!-- Bouton Effacer avec icône "X" -->
                            <Button
                                type="button"
                                icon="pi pi-times"
                                size="small"
                                class="mr-2"
                                severity="danger"
                                outlined
                                @click="clearFilter"
                            />
                        </div>
                        <!-- Section droite -->
                        <div class="flex align-items-center">
                            <!-- Bouton pour accéder à la page des erreurs d'importation -->
                            <Button
                                icon="pi pi-file-import"
                                severity="success"
                                size="small"
                                class="mr-2"
                                label="Import XLS"
                                @click="importXLS"
                            />
                            <!-- Bouton pour afficher les erreurs d'importation -->
                            <Button
                                v-if="errors.length > 0"
                                icon="pi pi-exclamation-triangle"
                                severity="warning"
                                size="small"
                                class="mr-2"
                                label="Erreur Import"
                                @click="showImportErrorPopup = true"
                            />

                            <Button
                                icon="pi pi-file-export"
                                severity="info"
                                size="small"
                                class="mr-2"
                                label="Export XLS"
                                @click="exportXLS"
                            />
                            <SplitButton
                                label="Actions"
                                icon="pi pi-cog"
                                size="small"
                                :model="actions"
                            />
                        </div>
                    </div>
                </template>

                <!-- Empty Template -->
                <template #empty>
                    <div v-if="!loading">Aucun secteur trouvé.</div>
                    <div v-else>Chargement en cours...</div>
                </template>

                <!-- Columns -->
                <Column
                    selectionMode="multiple"
                    headerStyle="width: 3rem"
                ></Column>
                <Column
                    field="code"
                    header="Code"
                    sortable
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        {{ data.code }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par code"
                            @input="filterCallback"
                        />
                    </template>
                </Column>

                <Column
                    field="nom_fr"
                    header="Nom (FR)"
                    sortable
                    style="min-width: 14rem"
                >
                    <template #body="{ data }">
                        {{ data.nom_fr }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter"
                            placeholder="Rechercher par nom (FR)"
                            @input="filterCallback"
                        />
                    </template>
                </Column>

                <Column
                    field="nom_ar"
                    header="Nom (AR)"
                    sortable
                    style="min-width: 14rem"
                >
                    <template #body="{ data }">
                        <div class="arabic-gras">
                            {{ data.nom_ar }}
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            class="p-column-filter arabic-gras"
                            placeholder="Rechercher par nom (AR)"
                            @input="filterCallback"
                        />
                    </template>
                </Column>

                <Column
                    header="Type"
                    field="type"
                    sortable
                    :filterMenuStyle="{ width: '14rem' }"
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <div class="centered-content arabic-normal">
                            <!-- Ajout de la classe arabic-text -->
                            <!-- Utilisez la prop severity si disponible -->
                            <Tag
                                :value="data.type"
                                :severity="getSeverity(data.type)"
                                class="arabic-normal"
                            />
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="types"
                            placeholder="Sélectionner un type"
                            class="p-column-filter arabic-normal"
                            @change="filterCallback"
                        >
                            <template #option="{ option }">
                                <div class="centered-content arabic-normal">
                                    <Tag
                                        :value="option"
                                        :severity="getSeverity(option)"
                                        class="arabic-normal"
                                    />
                                </div>
                            </template>
                        </Dropdown>
                    </template>
                </Column>

                <!-- Colonne Actions -->
                <Column header="Actions" style="min-width: 10rem">
                    <template #body="{ data, frozenRow, index }">
                        <Button
                            type="button"
                            :icon="frozenRow ? 'pi pi-lock-open' : 'pi pi-lock'"
                            :disabled="
                                frozenRow ? false : FixLignes.length >= 10
                            "
                            text
                            size="small"
                            @click="toggleLock(data, frozenRow, index)"
                        />

                        <Button
                            icon="pi pi-pencil"
                            class="p-button-rounded p-button-text p-button-success mr-2"
                            @click="editSecteur(data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            class="p-button-rounded p-button-text p-button-danger"
                            @click="confirmDeleteSecteur(data)"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>
        <!-- End Table -->

        <!-- Add/Edit Secteur Popup -->
        <AddSecteur
            :visible="addSecteurPopupVisible"
            :secteurToEdit="secteurToEdit"
            @update:visible="addSecteurPopupVisible = $event"
            @save="handleSaveSecteur"
            @update="handleUpdateSecteur"
            @close="closeAddSecteurPopup"
        />

        <!-- Popup de confirmation de suppression -->
        <Dialog
            v-model:visible="deletePopupVisible"
            :style="{ width: '450px' }"
            header="Confirmation"
            :modal="true"
        >
            <div class="confirmation-content">
                <i
                    class="pi pi-exclamation-triangle mr-3"
                    style="font-size: 2rem"
                />
                <span v-if="secteurToDelete">
                    Êtes-vous sûr de vouloir supprimer le secteur
                    <strong>{{ secteurToDelete.nom_fr }}</strong> ?
                </span>
                <span v-else>
                    Êtes-vous sûr de vouloir supprimer les
                    {{ selectedSecteurs.length }} secteurs sélectionnés ?
                </span>
            </div>
            <template #footer>
                <Button
                    label="Non"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="deletePopupVisible = false"
                />
                <Button
                    label="Oui"
                    icon="pi pi-check"
                    class="p-button-text"
                    @click="confirmDeleteAction"
                />
            </template>
        </Dialog>

        <!-- Popup pour les erreurs d'importation -->
        <ImportError
            :errors="errors"
            :visible="showImportErrorPopup"
            @update:visible="showImportErrorPopup = $event"
            @line-imported="handleLineImported"
            @close="refreshTable"
        />

        <!-- Toast Component -->
        <Toast />
    </div>
</template>

<script>
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';
import SplitButton from 'primevue/splitbutton';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import AddSecteur from '@/Popup/AddSecteur.vue';
import ExcelJS from 'exceljs';
import ImportError from '@/Popup/ImportError.vue';

export default {
    components: {
        Button,
        InputText,
        DataTable,
        Column,
        Tag,
        Dropdown,
        SplitButton,
        Dialog,
        Toast,
        AddSecteur,
        ImportError,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            FixLignes: [],
            secteurs: [],
            selectedSecteurs: null,
            filters: null,
            types: ['مقيس', 'غير مقيس'],
            actions: [
                {
                    label: 'Modifier',
                    icon: 'pi pi-pencil',
                    command: () => this.editSelectedSecteur(),
                },
                {
                    label: 'Supprimer',
                    icon: 'pi pi-trash',
                    command: () => this.confirmDeleteSelectedSecteurs(),
                },
            ],
            addSecteurPopupVisible: false,
            deletePopupVisible: false,
            secteurToEdit: null,
            secteurToDelete: null,
            loading: true,
            errors: [],
            showImportErrorPopup: false,
        };
    },

    created() {
        this.initFilters();
        this.fetchSecteurs();
    },

    methods: {
        clearFilter() {
            this.initFilters();
        },
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                nom_fr: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                nom_ar: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                type: {
                    operator: FilterOperator.OR,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
            };
        },

        handleLineImported(importedLine) {
            this.errors = this.errors.filter(
                (err) => err.line !== importedLine.line
            );
        },

        async refreshTable() {
            this.loading = true;
            try {
                const response = await axios.get('/api/secteurs');
                this.secteurs = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec du chargement des secteurs.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },

        getSeverity(type) {
            switch (type) {
                case 'غير مقيس':
                    return 'danger'; // Rouge pour les types urgents
                case 'مقيس':
                    return 'success'; // Vert pour les types standard
                default:
                    return null; // Aucune classe severity pour les autres types
            }
        },

        openAddSecteurPopup() {
            this.secteurToEdit = null; // Réinitialiser secteurToEdit
            this.addSecteurPopupVisible = true;
        },

        closeAddSecteurPopup() {
            this.addSecteurPopupVisible = false;
            this.secteurToEdit = null; // Réinitialiser secteurToEdit
        },

        async fetchSecteurs() {
            this.loading = true;
            try {
                const response = await axios.get('/api/secteurs');
                this.secteurs = response.data;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec du chargement des secteurs.',
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },

        editSecteur(secteur) {
            this.secteurToEdit = secteur;
            this.addSecteurPopupVisible = true;
        },

        editSelectedSecteur() {
            if (this.selectedSecteurs && this.selectedSecteurs.length === 1) {
                const selectedSecteur = this.selectedSecteurs[0];

                // Vérifier si la ligne sélectionnée est dans FixLignes ou secteurs
                const secteurToEdit =
                    this.FixLignes.find((s) => s.id === selectedSecteur.id) ||
                    this.secteurs.find((s) => s.id === selectedSecteur.id);

                if (secteurToEdit) {
                    this.editSecteur(secteurToEdit);
                }
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucune sélection',
                    detail: 'Veuillez sélectionner un seul secteur à modifier.',
                    life: 3000,
                });
            }
        },

        async handleSaveSecteur(newSecteur) {
            try {
                const response = await axios.post('/api/secteurs', newSecteur);
                this.secteurs.unshift(response.data);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Le secteur a été ajouté avec succès.',
                    life: 3000,
                });
            } catch (error) {
                console.error('Erreur lors de la création du secteur :', error);
                if (error.response && error.response.status === 422) {
                    const validationErrors = error.response.data.errors;
                    for (const [key, value] of Object.entries(
                        validationErrors
                    )) {
                        this.toast.add({
                            severity: 'error',
                            summary: 'Erreur',
                            detail: `${key}: ${value[0]}`,
                            life: 5000,
                        });
                    }
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Échec de l'ajout du secteur.",
                        life: 3000,
                    });
                }
            } finally {
                this.closeAddSecteurPopup();
            }
        },

        async handleUpdateSecteur(updatedSecteur) {
            try {
                const response = await axios.put(
                    `/api/secteurs/${updatedSecteur.id}`,
                    updatedSecteur
                );

                // Mettre à jour la ligne dans les secteurs normaux
                const indexSecteurs = this.secteurs.findIndex(
                    (s) => s.id === updatedSecteur.id
                );
                if (indexSecteurs !== -1) {
                    this.secteurs.splice(indexSecteurs, 1, response.data);
                }

                // Mettre à jour la ligne dans les lignes gelées
                const indexFixLignes = this.FixLignes.findIndex(
                    (s) => s.id === updatedSecteur.id
                );
                if (indexFixLignes !== -1) {
                    this.FixLignes.splice(indexFixLignes, 1, response.data);
                }

                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Le secteur a été modifié avec succès.',
                    life: 3000,
                });
            } catch (error) {
                console.error(
                    'Erreur lors de la modification du secteur :',
                    error
                );
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec de la modification du secteur.',
                    life: 3000,
                });
            } finally {
                this.closeAddSecteurPopup();
            }
        },

        confirmDeleteAction() {
            if (this.secteurToDelete) {
                this.deleteSecteur(); // Supprimer un seul secteur
            } else if (
                this.selectedSecteurs &&
                this.selectedSecteurs.length > 0
            ) {
                this.deleteSelectedSecteurs(); // Supprimer les secteurs sélectionnés
            }
            this.deletePopupVisible = false; // Fermer le popup de confirmation
        },

        async deleteSecteur() {
            if (this.secteurToDelete) {
                try {
                    await axios.delete(
                        `/api/secteurs/${this.secteurToDelete.id}`
                    );

                    // Supprimer la ligne des secteurs normaux
                    this.secteurs = this.secteurs.filter(
                        (s) => s.id !== this.secteurToDelete.id
                    );

                    // Supprimer la ligne des lignes gelées
                    this.FixLignes = this.FixLignes.filter(
                        (s) => s.id !== this.secteurToDelete.id
                    );

                    this.toast.add({
                        severity: 'success',
                        summary: 'Supprimé',
                        detail: 'Le secteur a été supprimé avec succès.',
                        life: 3000,
                    });
                } catch (error) {
                    console.error(
                        'Erreur lors de la suppression du secteur :',
                        error
                    );
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Échec de la suppression du secteur.',
                        life: 3000,
                    });
                } finally {
                    this.secteurToDelete = null;
                }
            }
        },

        async deleteSelectedSecteurs() {
            if (this.selectedSecteurs && this.selectedSecteurs.length > 0) {
                try {
                    await axios.post('/api/secteurs/delete-selected', {
                        ids: this.selectedSecteurs.map((s) => s.id),
                    });

                    // Supprimer les lignes sélectionnées des secteurs normaux
                    this.secteurs = this.secteurs.filter(
                        (s) => !this.selectedSecteurs.includes(s)
                    );

                    // Supprimer les lignes sélectionnées des lignes gelées
                    this.FixLignes = this.FixLignes.filter(
                        (s) => !this.selectedSecteurs.includes(s)
                    );

                    this.toast.add({
                        severity: 'success',
                        summary: 'Supprimé',
                        detail: 'Les secteurs sélectionnés ont été supprimés avec succès.',
                        life: 3000,
                    });
                    this.selectedSecteurs = null;
                } catch (error) {
                    console.error(
                        'Erreur lors de la suppression des secteurs :',
                        error
                    );
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Échec de la suppression des secteurs sélectionnés.',
                        life: 3000,
                    });
                }
            }
        },

        confirmDeleteSecteur(secteur) {
            this.secteurToDelete = secteur;
            this.deletePopupVisible = true;
        },

        confirmDeleteSelectedSecteurs() {
            if (this.selectedSecteurs && this.selectedSecteurs.length > 0) {
                this.secteurToDelete = null;
                this.deletePopupVisible = true;
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Aucune sélection',
                    detail: 'Veuillez sélectionner un ou plusieurs secteurs à supprimer.',
                    life: 3000,
                });
            }
        },

        toggleLock(data, frozen, index) {
            if (frozen) {
                // Retirer la ligne gelée et la remettre dans les secteurs normaux
                this.FixLignes = this.FixLignes.filter((c, i) => i !== index);
                this.secteurs.push(data);
            } else {
                // Retirer la ligne normale et la mettre dans les lignes gelées
                this.secteurs = this.secteurs.filter((c, i) => i !== index);
                this.FixLignes.push(data);
            }

            // Trier les secteurs pour maintenir l'ordre
            this.secteurs.sort((val1, val2) => {
                return val1.id < val2.id ? -1 : 1;
            });
        },

        async importXLS() {
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.xls,.xlsx';
            fileInput.style.display = 'none';

            fileInput.onchange = async (event) => {
                const file = event.target.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('file', file);

                try {
                    const response = await axios.post(
                        '/api/secteurs/importxls',
                        formData,
                        {
                            headers: {
                                'Content-Type': 'multipart/form-data',
                            },
                        }
                    );

                    this.errors = response.data.error_lines || [];

                    if (response.data.success) {
                        let message = 'Importation réussie.';
                        let severity = 'success';

                        if (this.errors.length > 0) {
                            message =
                                'Importation terminée avec des erreurs. Veuillez vérifier les lignes concernées.';
                            severity = 'warn';
                            this.showImportErrorPopup = true;
                        }

                        this.toast.add({
                            severity: severity,
                            summary: 'Import XLS',
                            detail: message,
                            life: 10000,
                        });
                    } else {
                        this.toast.add({
                            severity: 'error',
                            summary: 'Erreur',
                            detail:
                                response.data.message ||
                                "Une erreur s'est produite lors de l'importation.",
                            life: 10000,
                        });
                    }
                } catch (error) {
                    console.error("Erreur lors de l'importation :", error);
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Une erreur s'est produite lors de l'importation.",
                        life: 10000,
                    });
                } finally {
                    fileInput.remove();
                }
            };

            document.body.appendChild(fileInput);
            fileInput.click();
        },

        exportXLS() {
            try {
                const data = this.secteurs.map((secteur) => ({
                    Code: secteur.code,
                    'Nom (FR)': secteur.nom_fr,
                    'Nom (AR)': secteur.nom_ar,
                    Type: secteur.type,
                }));

                const worksheet = XLSX.utils.json_to_sheet(data);
                const workbook = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(workbook, worksheet, 'Secteurs');
                XLSX.writeFile(workbook, 'secteurs.xlsx');

                this.toast.add({
                    severity: 'success',
                    summary: 'Export XLS',
                    detail: "L'exportation des secteurs a été réalisée avec succès.",
                    life: 3000,
                });
            } catch (error) {
                console.error("Erreur lors de l'export XLS :", error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Une erreur s'est produite lors de l'export XLS.",
                    life: 3000,
                });
            }
        },
    },
};
</script>

<style scoped>
.arabic-normal {
    font-family: 'Scheherazade New', sans-serif; /* Police utilisée */
    font-size: 1em !important; /* Réduction de la taille de police */
    font-weight: normal !important; /* Suppression du gras */
}
.arabic-gras {
    font-family: 'Scheherazade New', sans-serif; /* Police utilisée */
    font-size: 1em !important; /* Réduction de la taille de police */
}
</style>
