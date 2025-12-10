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
                        label="Spécialités"
                        icon="pi pi-list"
                        class="p-button-text p-button-plain"
                        @click="$router.push({ name: 'specialites' })"
                    />
                    <Button
                        label="Programmes"
                        icon="pi pi-book"
                        class="p-button-text p-button-plain"
                        disabled
                    />
                </div>
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-refresh"
                        class="p-button-text p-button-plain"
                        @click="refreshTable"
                        v-tooltip="'Rafraîchir'"
                    />
                    <Button
                        icon="pi pi-file-export"
                        class="p-button-text p-button-plain"
                        @click="exportXLS"
                        v-tooltip="'Exporter XLS'"
                    />
                </div>
            </div>
        </div>

        <!-- Main Content Section -->
        <div
            class="surface-card p-4 pt-2 border-round shadow-2 border-1 surface-border"
        >
            <!-- Table Header with Filters -->
            <div class="flex justify-content-between align-items-center mb-4">
                <div class="flex align-items-center gap-2">
                    <span class="p-input-icon-left search-field">
                        <InputText
                            v-model="filters['global'].value"
                            placeholder="Rechercher..."
                            :autofocus="false"
                        />
                    </span>
                    <Button
                        icon="pi pi-filter-slash"
                        class="p-button-outlined p-button-secondary"
                        size="small"
                        @click="clearFilter"
                        v-tooltip="'Réinitialiser les filtres'"
                    />
                    <div class="annee-filter">
                        <Dropdown
                            v-model="selectedAnnee"
                            :options="annees"
                            optionLabel="intitule"
                            optionValue="id"
                            placeholder="Année"
                            class="button-height"
                            @change="applyAnneeFilter"
                            :autofocus="false"
                        />
                        <Button
                            icon="pi pi-times"
                            class="p-button-outlined p-button-danger"
                            size="small"
                            @click="clearAnneeFilter"
                            v-tooltip="'Effacer le filtre année'"
                        />
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button
                        label="Tous"
                        :severity="
                            activeTab === 'tous' ? 'primary' : 'secondary'
                        "
                        class="arabic-normal button-height"
                        @click="setActiveTab('tous')"
                    />
                    <Button
                        label="مقيس"
                        :severity="
                            activeTab === 'مقيس' ? 'success' : 'secondary'
                        "
                        class="arabic-normal button-height"
                        @click="setActiveTab('مقيس')"
                    />
                    <Button
                        label="غير مقيس"
                        :severity="
                            activeTab === 'غير مقيس' ? 'danger' : 'secondary'
                        "
                        class="arabic-normal button-height"
                        @click="setActiveTab('غير مقيس')"
                    />
                    <Button
                        label="جديد"
                        :severity="
                            activeTab === 'جديد' ? 'warning' : 'secondary'
                        "
                        class="arabic-normal button-height"
                        @click="setActiveTab('جديد')"
                    />
                </div>
            </div>

            <!-- Main DataTable for Specialites -->
            <DataTable
                v-model:expandedRows="expandedSpecialites"
                v-model:filters="filters"
                :value="specialites.data"
                dataKey="id"
                size="small"
                stripedRows
                paginator
                :rows="20"
                :rowsPerPageOptions="[10, 20, 50]"
                filterDisplay="menu"
                :globalFilterFields="[
                    'code',
                    'nom_fr',
                    'nom_ar',
                    'diplome_fr',
                    'standardisation_ar',
                    'homologation_fr',
                    'duree_formation',
                ]"
                :loading="loading"
                scrollable
                scrollHeight="750px"
                removableSort
                :pt="{ table: { style: 'min-width: 50rem' } }"
                :totalRecords="specialites.total"
                @page="onPage($event)"
            >
                <template #empty>
                    <div v-if="!loading" class="text-center p-3">
                        Aucune spécialité trouvée.
                    </div>
                    <div v-else class="text-center p-3">
                        Chargement en cours...
                    </div>
                </template>

                <Column expander style="width: 3rem" />
                <Column
                    field="code"
                    header="Code"
                    sortable
                    style="min-width: 8rem"
                >
                    <template #body="{ data }">
                        <span class="font-medium">{{ data.code }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            placeholder="Filtrer par code"
                            class="p-column-filter"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column
                    field="nom_fr"
                    header="Nom (FR)"
                    sortable
                    style="min-width: 15rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.nom_fr }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            placeholder="Filtrer par nom (FR)"
                            class="p-column-filter"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column
                    field="nom_ar"
                    header="Nom (AR)"
                    sortable
                    style="min-width: 15rem"
                >
                    <template #body="{ data }">
                        <span class="arabic-normal">{{ data.nom_ar }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            placeholder="Filtrer par nom (AR)"
                            class="p-column-filter arabic-gras"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column
                    field="diplome_fr"
                    header="Diplôme"
                    sortable
                    style="min-width: 12rem"
                >
                    <!-- Cellule affichée dans le tableau -->
                    <template #body="slotProps">
                        <Tag
                            :value="slotProps.data.diplome_fr || '----'"
                            :severity="getSeverity(slotProps.data.diplome_fr)"
                            :outlined="true"
                        />
                    </template>

                    <!-- Cellule en mode édition (si vous utilisez l'édition inline/cellulaire) -->
                    <template #editor="{ data, field }">
                        <Dropdown
                            v-model="data[field]"
                            :options="uniqueDiplomeFrValues"
                            placeholder="Sélectionner un diplôme"
                        >
                            <template #option="{ option }">
                                <Tag
                                    :value="option"
                                    :severity="getSeverity(option)"
                                    :outlined="true"
                                />
                            </template>
                        </Dropdown>
                    </template>

                    <!-- Filtre colonne -->
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="uniqueDiplomeFrValues"
                            placeholder="Sélectionner un diplôme"
                            class="p-column-filter"
                            @change="filterCallback()"
                        >
                            <template #option="{ option }">
                                <Tag
                                    :value="option"
                                    :severity="getSeverity(option)"
                                    :outlined="true"
                                />
                            </template>
                        </Dropdown>
                    </template>
                </Column>

                <Column
                    field="standardisation_ar"
                    header="Standardisation (AR)"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="data.standardisation_ar || '----'"
                            :severity="getSeverity(data.standardisation_ar)"
                            :outlined="true"
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="['مقيس', 'غير مقيس', 'جديد']"
                            placeholder="Sélectionner une standardisation"
                            class="p-column-filter"
                            @change="filterCallback()"
                        >
                            <template #option="{ option }">
                                <Tag
                                    :value="option"
                                    :severity="getSeverity(option)"
                                    :outlined="true"
                                />
                            </template>
                        </Dropdown>
                    </template>
                </Column>
                <Column
                    field="homologation_fr"
                    header="Homologation"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="
                                getInfoSpecialite(data, 'homologation_fr') ||
                                '----'
                            "
                            :severity="
                                getSeverity(
                                    getInfoSpecialite(data, 'homologation_fr')
                                )
                            "
                            :outlined="true"
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="uniqueHomologationFrValues"
                            placeholder="Sélectionner une homologation"
                            class="p-column-filter"
                            @change="filterCallback()"
                        >
                            <template #option="{ option }">
                                <Tag
                                    :value="option"
                                    :severity="getSeverity(option)"
                                    :outlined="true"
                                />
                            </template>
                        </Dropdown>
                    </template>
                </Column>
                <Column
                    field="duree_formation"
                    header="Durée Formation"
                    sortable
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <Chip
                            :label="
                                getInfoSpecialite(data, 'duree_formation') ||
                                '----'
                            "
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText
                            v-model="filterModel.value"
                            type="text"
                            placeholder="Filtrer par durée"
                            class="p-column-filter"
                            @input="filterCallback()"
                        />
                    </template>
                </Column>
                <Column header="Actions" style="min-width: 10rem">
                    <template #body="{ data }">
                        <div class="flex gap-1">
                            <Button
                                icon="pi pi-eye"
                                class="p-button-rounded p-button-text p-button-sm"
                                @click="viewSpecialite(data.id)"
                                v-tooltip="'Voir détails'"
                            />
                            <Button
                                icon="pi pi-plus"
                                class="p-button-rounded p-button-text p-button-success p-button-sm"
                                @click="openProgrammeForm(data)"
                                v-tooltip="'Ajouter programme'"
                            />
                        </div>
                    </template>
                </Column>

                <!-- Expanded Row for Programmes -->
                <template #expansion="{ data }">
                    <div class="p-3 surface-50 border-round">
                        <DataTable
                            :value="data.programmes"
                            size="small"
                            :loading="loading"
                            dataKey="id"
                            v-model:expandedRows="expandedProgrammes"
                            class="p-datatable-sm"
                        >
                            <Column expander style="width: 3rem" />
                            <Column
                                field="version"
                                header="Version"
                                sortable
                                style="min-width: 10rem"
                            >
                                <template #body="{ data }">
                                    <Tag
                                        :value="data.version"
                                        severity="info"
                                        :outlined="true"
                                    />
                                </template>
                            </Column>
                            <Column
                                field="date_debut"
                                header="Date Début"
                                sortable
                                style="min-width: 12rem"
                            >
                                <template #body="{ data }">
                                    <Tag
                                        :value="
                                            formatDate(data.date_debut) || '-'
                                        "
                                        icon="pi pi-calendar"
                                        :outlined="true"
                                    />
                                </template>
                            </Column>
                            <Column
                                field="date_fin"
                                header="Date Fin"
                                sortable
                                style="min-width: 12rem"
                            >
                                <template #body="{ data }">
                                    <Tag
                                        :value="
                                            formatDate(data.date_fin) || '-'
                                        "
                                        icon="pi pi-calendar"
                                        :outlined="true"
                                    />
                                </template>
                            </Column>
                            <Column
                                field="statut"
                                header="Statut"
                                sortable
                                style="min-width: 10rem"
                            >
                                <template #body="{ data }">
                                    <Tag
                                        :value="data.statut || 'Actif'"
                                        :severity="getSeverity(data.statut)"
                                        :outlined="true"
                                    />
                                </template>
                            </Column>
                            <Column header="Actions" style="min-width: 15rem">
                                <template #body="{ data }">
                                    <div class="flex gap-1">
                                        <Button
                                            icon="pi pi-info-circle"
                                            class="p-button-rounded p-button-text p-button-info p-button-sm"
                                            @click="viewProgramme(data)"
                                            v-tooltip="'Détails programme'"
                                        />
                                        <Button
                                            icon="pi pi-pencil"
                                            class="p-button-rounded p-button-text p-button-sm"
                                            @click="editProgramme(data)"
                                            v-tooltip="'Modifier'"
                                        />
                                        <Button
                                            icon="pi pi-trash"
                                            class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                            @click="
                                                confirmDeleteProgramme(data)
                                            "
                                            v-tooltip="'Supprimer'"
                                        />
                                        <Button
                                            icon="pi pi-plus"
                                            class="p-button-rounded p-button-text p-button-success p-button-sm"
                                            @click="openModuleForm(data)"
                                            v-tooltip="'Ajouter module'"
                                        />
                                        <Button
                                            icon="pi pi-file"
                                            class="p-button-rounded p-button-text p-button-info p-button-sm"
                                            @click="
                                                openDocumentProgrammeForm(data)
                                            "
                                            v-tooltip="'Ajouter document'"
                                        />
                                        <Button
                                            icon="pi pi-file-import"
                                            class="p-button-rounded p-button-text p-button-warning p-button-sm"
                                            @click="
                                                openModuleImportDialog(data)
                                            "
                                            v-tooltip="'Importer XLS'"
                                        />
                                        <Button
                                            icon="pi pi-file-export"
                                            class="p-button-rounded p-button-text p-button-secondary p-button-sm"
                                            @click="exportModulesXLS(data)"
                                            v-tooltip="'Exporter XLS'"
                                        />
                                    </div>
                                </template>
                            </Column>

                            <!-- Expanded Row for Modules -->
                            <template #expansion="{ data }">
                                <div class="p-3 surface-100 border-round">
                                    <DataTable
                                        :value="data.modules"
                                        size="small"
                                        :loading="loading"
                                        dataKey="id"
                                        class="p-datatable-sm"
                                    >
                                        <Column
                                            field="code"
                                            header="Code"
                                            sortable
                                            style="min-width: 10rem"
                                        >
                                            <template #body="{ data }">
                                                <span>{{
                                                        data.code || '-'
                                                    }}</span>
                                            </template>
                                        </Column>
                                        <Column
                                            field="titre_module"
                                            header="Titre"
                                            sortable
                                            style="min-width: 15rem"
                                        >
                                            <template #body="{ data }">
                                                <span>{{
                                                        data.titre_module || '-'
                                                    }}</span>
                                            </template>
                                        </Column>
                                        <Column
                                            field="type_module_fr"
                                            header="Type"
                                            sortable
                                            style="min-width: 10rem"
                                        >
                                            <template #body="{ data }">
                                                <Tag
                                                    :value="
                                                        data.type_module_fr || '-'
                                                    "
                                                    :severity="
                                                        getSeverity(
                                                            data.type_module_fr
                                                        )
                                                    "
                                                    :outlined="true"
                                                />
                                            </template>
                                        </Column>
                                        <Column
                                            field="heures_theoriques"
                                            header="H. Théoriques"
                                            sortable
                                            style="min-width: 10rem"
                                        >
                                            <template #body="{ data }">
                                                <Chip
                                                    :label="
                                                        String(
                                                            data.heures_theoriques ||
                                                                0
                                                        )
                                                    "
                                                />
                                            </template>
                                        </Column>
                                        <Column
                                            field="heures_pratiques"
                                            header="H. Pratiques"
                                            sortable
                                            style="min-width: 10rem"
                                        >
                                            <template #body="{ data }">
                                                <Chip
                                                    :label="
                                                        String(
                                                            data.heures_pratiques ||
                                                                0
                                                        )
                                                    "
                                                />
                                            </template>
                                        </Column>
                                        <Column
                                            field="heures_evaluation"
                                            header="H. Évaluation"
                                            sortable
                                            style="min-width: 10rem"
                                        >
                                            <template #body="{ data }">
                                                <Chip
                                                    :label="
                                                        String(
                                                            data.heures_evaluation ||
                                                                0
                                                        )
                                                    "
                                                />
                                            </template>
                                        </Column>
                                        <Column
                                            field="heures_total"
                                            header="H. Total"
                                            sortable
                                            style="min-width: 10rem"
                                        >
                                            <template #body="{ data }">
                                                <Chip
                                                    :label="
                                                        String(
                                                            calculateTotalHours(
                                                                data
                                                            )
                                                        )
                                                    "
                                                />
                                            </template>
                                        </Column>
                                        <Column
                                            field="statut"
                                            header="Statut"
                                            sortable
                                            style="min-width: 10rem"
                                        >
                                            <template #body="{ data }">
                                                <Tag
                                                    :value="
                                                        data.statut || 'Actif'
                                                    "
                                                    :severity="
                                                        getSeverity(data.statut)
                                                    "
                                                    :outlined="true"
                                                />
                                            </template>
                                        </Column>
                                        <Column
                                            header="Actions"
                                            style="min-width: 10rem"
                                        >
                                            <template #body="{ data }">
                                                <div class="flex gap-1">
                                                    <Button
                                                        icon="pi pi-pencil"
                                                        class="p-button-rounded p-button-text p-button-sm"
                                                        @click="
                                                            editModule(data)
                                                        "
                                                        v-tooltip="'Modifier'"
                                                    />
                                                    <Button
                                                        icon="pi pi-trash"
                                                        class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                                        @click="
                                                            confirmDeleteModule(
                                                                data
                                                            )
                                                        "
                                                        v-tooltip="'Supprimer'"
                                                    />
                                                    <Button
                                                        icon="pi pi-file"
                                                        class="p-button-rounded p-button-text p-button-info p-button-sm"
                                                        @click="
                                                            openDocumentModuleForm(
                                                                data
                                                            )
                                                        "
                                                        v-tooltip="
                                                            'Ajouter document'
                                                        "
                                                    />
                                                </div>
                                            </template>
                                        </Column>
                                    </DataTable>
                                </div>
                            </template>
                        </DataTable>
                    </div>
                </template>
            </DataTable>

            <!-- Programme Form Dialog -->
            <Dialog
                v-model:visible="programmeFormVisible"
                :style="{ width: '50vw', maxWidth: '600px' }"
                :modal="true"
                :closable="true"
                :pt="{
                    root: { class: 'border-round shadow-4' },
                    header: {
                        class: 'surface-50 border-bottom-1 surface-border p-3',
                    },
                    content: { class: 'p-0' },
                    footer: {
                        class: 'surface-50 border-top-1 surface-border p-3',
                    },
                }"
            >
                <ProgrammeForm
                    :specialite="selectedSpecialite"
                    :programmeToEdit="programmeToEdit"
                    :visible="programmeFormVisible"
                    @update:visible="programmeFormVisible = $event"
                    @save="handleSaveProgramme"
                    @update="handleUpdateProgramme"
                    @close="closeProgrammeForm"
                />
            </Dialog>

            <!-- DocumentProgrammeSpecialite Form Dialog -->
            <Dialog
                v-model:visible="moduleFormVisible"
                :style="{ width: '50vw', maxWidth: '600px' }"
                :modal="true"
                :closable="true"
                :pt="{
                    root: { class: 'border-round shadow-4' },
                    header: {
                        class: 'surface-50 border-bottom-1 surface-border p-3',
                    },
                    content: { class: 'p-0' },
                    footer: {
                        class: 'surface-50 border-top-1 surface-border p-3',
                    },
                }"
            >
                <ModuleForm
                    :programme="selectedProgramme"
                    :moduleToEdit="moduleToEdit"
                    :visible="moduleFormVisible"
                    @update:visible="moduleFormVisible = $event"
                    @save="handleSaveModule"
                    @update="handleUpdateModule"
                    @close="closeModuleForm"
                />
            </Dialog>

            <!-- DocumentProgrammeSpecialite Import Dialog -->
            <Dialog
                v-model:visible="moduleImportDialogVisible"
                :style="{ width: '50vw', maxWidth: '600px' }"
                header="Importer des modules (XLS)"
                :modal="true"
                :closable="true"
                :pt="{
                    root: { class: 'border-round shadow-4' },
                    header: {
                        class: 'surface-50 border-bottom-1 surface-border p-3',
                    },
                    content: { class: 'p-3' },
                    footer: {
                        class: 'surface-50 border-top-1 surface-border p-3',
                    },
                }"
            >
                <div class="field">
                    <label for="moduleFile">Fichier XLS</label>
                    <FileUpload
                        id="moduleFile"
                        mode="basic"
                        accept=".xls,.xlsx"
                        :maxFileSize="2048000"
                        @select="onFileSelect"
                        chooseLabel="Sélectionner un fichier"
                        :auto="false"
                        :disabled="uploading"
                    />
                </div>
                <template #footer>
                    <Button
                        label="Annuler"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="closeModuleImportDialog"
                        :disabled="uploading"
                    />
                    <Button
                        label="Importer"
                        icon="pi pi-upload"
                        class="p-button-success"
                        @click="importModules"
                        :loading="uploading"
                    />
                </template>
            </Dialog>

            <!-- DocumentProgrammeSpecialite Import Errors Dialog -->
            <Dialog
                v-model:visible="moduleImportErrorsVisible"
                :style="{ width: '70vw' }"
                header="Erreurs d'importation des modules"
                :modal="true"
                :pt="{
                    root: { class: 'border-round shadow-4' },
                    header: {
                        class: 'surface-50 border-bottom-1 surface-border p-3',
                    },
                    content: { class: 'p-3' },
                    footer: {
                        class: 'surface-50 border-top-1 surface-border p-3',
                    },
                }"
            >
                <ModulesImportError
                    :errors="importErrors"
                    :visible="moduleImportErrorsVisible"
                    :programmeId="selectedProgramme?.id"
                    @update:visible="moduleImportErrorsVisible = $event"
                    @line-imported="handleLineImported"
                    @close="closeModuleImportErrors"
                />
            </Dialog>

            <!-- Document Programme Form Dialog -->
            <Dialog
                v-model:visible="documentProgrammeFormVisible"
                :style="{ width: '80vw', maxWidth: '1200px' }"
                :modal="true"
                :closable="true"
                :pt="{
                    root: { class: 'border-round shadow-4' },
                    header: {
                        class: 'surface-50 border-bottom-1 surface-border p-3',
                    },
                    content: { class: 'p-0' },
                    footer: {
                        class: 'surface-50 border-top-1 surface-border p-3',
                    },
                }"
            >
                <DocProgrammeForm
                    v-if="
                        documentProgrammeFormVisible &&
                        selectedProgramme &&
                        selectedSpecialite
                    "
                    :specialite="selectedSpecialite"
                    :programme="selectedProgramme"
                    :documentToEdit="documentProgrammeToEdit"
                    :visible="documentProgrammeFormVisible"
                    @update:visible="documentProgrammeFormVisible = $event"
                    @save="handleSaveDocumentProgramme"
                    @update="handleUpdateDocumentProgramme"
                    @close="closeDocumentProgrammeForm"
                    @refresh="fetchProgrammes(currentPage)"
                />
            </Dialog>

            <!-- Document DocumentProgrammeSpecialite Form Dialog -->
            <Dialog
                v-model:visible="documentModuleFormVisible"
                :style="{ width: '80vw', maxWidth: '1200px' }"
                :modal="true"
                :closable="true"
                :pt="{
                    root: { class: 'border-round shadow-4' },
                    header: {
                        class: 'surface-50 border-bottom-1 surface-border p-3',
                    },
                    content: { class: 'p-0' },
                    footer: {
                        class: 'surface-50 border-top-1 surface-border p-3',
                    },
                }"
            >
                <DocModuleForm
                    v-if="documentModuleFormVisible && selectedModule"
                    :module="selectedModule"
                    :documentToEdit="documentModuleToEdit"
                    :visible="documentModuleFormVisible"
                    @update:visible="documentModuleFormVisible = $event"
                    @save="handleSaveDocumentModule"
                    @update="handleUpdateDocumentModule"
                    @close="closeDocumentModuleForm"
                    @refresh="fetchProgrammes(currentPage)"
                />
            </Dialog>

            <!-- Profil Specialite Dialog -->
            <Dialog
                v-model:visible="profilSpecialiteDialogVisible"
                header="Détails de la spécialité"
                :style="{ width: '80vw', maxWidth: '1200px' }"
                :modal="true"
                :closable="true"
                :pt="{
                    root: { class: 'border-round shadow-4' },
                    header: {
                        class: 'surface-50 border-bottom-1 surface-border p-3',
                    },
                    content: { class: 'p-3' },
                    footer: {
                        class: 'surface-50 border-top-1 surface-border p-3',
                    },
                }"
            >
                <ProfilSpecialite
                    v-if="profilSpecialiteDialogVisible && selectedSpecialiteId"
                    :specialiteId="selectedSpecialiteId"
                    :anneeId="selectedAnnee"
                    :visible="profilSpecialiteDialogVisible"
                    @update:visible="profilSpecialiteDialogVisible = $event"
                    @close="closeProfilSpecialiteDialog"
                />
            </Dialog>

            <!-- Detail Programme Dialog -->
            <Dialog
                v-model:visible="detailProgrammeDialogVisible"
                header="Détails du programme"
                :style="{ width: '80vw', maxWidth: '1200px' }"
                :modal="true"
                :closable="true"
                :pt="{
                    root: { class: 'border-round shadow-4' },
                    header: {
                        class: 'surface-50 border-bottom-1 surface-border p-3',
                    },
                    content: { class: 'p-3' },
                    footer: {
                        class: 'surface-50 border-top-1 surface-border p-3',
                    },
                }"
            >
                <DetailProgramme
                    v-if="detailProgrammeDialogVisible && selectedProgrammeId"
                    :programmeId="selectedProgrammeId"
                    :anneeId="selectedAnnee"
                    :visible="detailProgrammeDialogVisible"
                    @update:visible="detailProgrammeDialogVisible = $event"
                    @close="closeDetailProgrammeDialog"
                />
            </Dialog>

            <!-- Delete Programme Confirmation Dialog -->
            <Dialog
                v-model:visible="deleteProgrammeDialog"
                header="Confirmer la suppression"
                :style="{ width: '450px' }"
                :modal="true"
                :pt="{
                    root: { class: 'border-round shadow-4' },
                    header: {
                        class: 'surface-50 border-bottom-1 surface-border p-3',
                    },
                    content: { class: 'p-3' },
                    footer: {
                        class: 'surface-50 border-top-1 surface-border p-3',
                    },
                }"
            >
                <div class="confirmation-content flex align-items-center gap-3">
                    <i
                        class="pi pi-exclamation-triangle text-4xl text-red-500"
                    />
                    <span
                    >Voulez-vous vraiment supprimer le programme
                        <b>{{ programmeToDelete?.version }}</b> ?</span
                    >
                </div>
                <template #footer>
                    <Button
                        label="Annuler"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="deleteProgrammeDialog = false"
                    />
                    <Button
                        label="Supprimer"
                        icon="pi pi-check"
                        class="p-button-danger"
                        @click="deleteProgramme"
                    />
                </template>
            </Dialog>

            <!-- Delete DocumentProgrammeSpecialite Confirmation Dialog -->
            <Dialog
                v-model:visible="deleteModuleDialog"
                header="Confirmer la suppression"
                :style="{ width: '450px' }"
                :modal="true"
                :pt="{
                    root: { class: 'border-round shadow-4' },
                    header: {
                        class: 'surface-50 border-bottom-1 surface-border p-3',
                    },
                    content: { class: 'p-3' },
                    footer: {
                        class: 'surface-50 border-top-1 surface-border p-3',
                    },
                }"
            >
                <div class="confirmation-content flex align-items-center gap-3">
                    <i
                        class="pi pi-exclamation-triangle text-4xl text-red-500"
                    />
                    <span
                    >Voulez-vous vraiment supprimer le module
                        <b>{{ moduleToDelete?.titre_module }}</b> ?</span
                    >
                </div>
                <template #footer>
                    <Button
                        label="Annuler"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="deleteModuleDialog = false"
                    />
                    <Button
                        label="Supprimer"
                        icon="pi pi-check"
                        class="p-button-danger"
                        @click="deleteModule"
                    />
                </template>
            </Dialog>

            <Toast position="top-right" />
        </div>
    </div>
</template>

<script>
import { ref, nextTick } from 'vue';
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Chip from 'primevue/chip';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Dropdown from 'primevue/dropdown';
import FileUpload from 'primevue/fileupload';
import ProgrammeForm from './ProgrammeForm.vue';
import ModuleForm from './ModuleForm.vue';
import DocProgrammeForm from './DocProgrammeForm.vue';
import DocModuleForm from './DocModuleForm.vue';
import ProfilSpecialite from './ProfilSpecialite.vue';
import DetailProgramme from './DetailProgramme.vue';
import ModulesImportError from '@/Components/Atfp/ImportError/ModulesImportError.vue';
import ExcelJS from 'exceljs';

export default {
    name: 'ProgrammesList',
    components: {
        Button,
        DataTable,
        Column,
        InputText,
        Tag,
        Chip,
        Dialog,
        Toast,
        Dropdown,
        FileUpload,
        ProgrammeForm,
        ModuleForm,
        DocProgrammeForm,
        DocModuleForm,
        ProfilSpecialite,
        DetailProgramme,
        ModulesImportError,
    },
    props: {
        specialiteId: {
            type: [Number, String],
            required: false,
            default: null,
        },
        specialiteData: {
            type: Object,
            default: null,
        },
        visible: {
            type: Boolean,
            default: false,
        },
    },
    setup() {
        const toast = useToast();
        const specialites = ref({ data: [], total: 0 });
        return { toast, specialites };
    },
    data() {
        return {
            filters: null,
            selectedAnnee: null,
            activeTab: 'tous',
            expandedSpecialites: [],
            expandedProgrammes: [],
            programmeFormVisible: false,
            moduleFormVisible: false,
            documentProgrammeFormVisible: false,
            documentModuleFormVisible: false,
            moduleImportDialogVisible: false,
            moduleImportErrorsVisible: false,
            deleteProgrammeDialog: false,
            deleteModuleDialog: false,
            profilSpecialiteDialogVisible: false,
            detailProgrammeDialogVisible: false,
            programmeToEdit: null,
            moduleToEdit: null,
            documentProgrammeToEdit: null,
            documentModuleToEdit: null,
            selectedSpecialite: null,
            selectedProgramme: null,
            selectedModule: null,
            programmeToDelete: null,
            moduleToDelete: null,
            selectedFile: null,
            importErrors: [],
            uploading: false,
            loading: false,
            annees: [],
            currentYearId: null,
            currentPage: 1,
            selectedSpecialiteId: null,
            selectedProgrammeId: null,
        };
    },
    computed: {
        uniqueDiplomeFrValues() {
            const diplomes = this.specialites.data
                .map((s) => s.diplome_fr)
                .filter(
                    (value, index, self) =>
                        value &&
                        self.indexOf(value) === index &&
                        value !== '----'
                );
            return diplomes.sort();
        },
        uniqueHomologationFrValues() {
            const homologations = this.specialites.data
                .flatMap(
                    (s) =>
                        s.infos_specialites?.map(
                            (info) => info.homologation_fr
                        ) || []
                )
                .filter(
                    (value, index, self) =>
                        value &&
                        self.indexOf(value) === index &&
                        value !== '----'
                );
            return homologations.sort();
        },
    },
    async created() {
        this.initFilters();
        await this.fetchAnnees();
        this.setCurrentYear();
        this.selectedAnnee = this.currentYearId;
        if (this.selectedAnnee) {
            await this.fetchProgrammes();
        } else {
            this.showError('Aucune année de formation valide trouvée.');
        }
    },
    methods: {
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
                nom_fr: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
                nom_ar: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
                diplome_fr: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
                standardisation_ar: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
                homologation_fr: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
                duree_formation: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.CONTAINS },
                    ],
                },
            };
        },
        async fetchProgrammes(page = 1) {
            if (!this.selectedAnnee) {
                this.specialites = { data: [], total: 0 };
                this.showInfo('Veuillez sélectionner une année de formation.');
                return;
            }

            // Vérifier que l'année sélectionnée a des dates valides
            const selectedAnneeData = this.annees.find(annee => annee.id === this.selectedAnnee);
            if (!selectedAnneeData || !selectedAnneeData.date_debut || !selectedAnneeData.date_fin) {
                this.specialites = { data: [], total: 0 };
                this.showError('L\'année sélectionnée n\'a pas de dates valides.');
                return;
            }

            this.loading = true;
            try {
                const params = { annee_id: this.selectedAnnee, page };
                if (this.specialiteId) {
                    params.specialite_id = this.specialiteId;
                }
                if (this.activeTab !== 'tous') {
                    params.standardisation = this.activeTab;
                }
                console.log('Fetching programmes with params:', params);
                const response = await axios.get('/api/programmes', { params });
                console.log('Programmes API Response:', response.data);
                this.specialites = {
                    data: response.data.data.map((specialite) => ({
                        ...specialite,
                        programmes: specialite.programmes.map((programme) => ({
                            ...programme,
                            specialite: {
                                id: specialite.id,
                                code: specialite.code,
                                nom_fr: specialite.nom_fr,
                                standardisation_ar: specialite.standardisation_ar,
                            },
                            modules: programme.modules || [],
                        })),
                    })),
                    total: response.data.total,
                };
                if (this.specialites.data.length === 0) {
                    this.showInfo('Aucun programme trouvé pour cette année et ce filtre.');
                }
            } catch (error) {
                const errorMessage =
                    error.response?.status === 500
                        ? "Erreur serveur : impossible de charger les programmes. Contactez l'administrateur."
                        : error.response?.data?.error ||
                        'Erreur lors du chargement des programmes.';
                this.showError(errorMessage);
                this.specialites = { data: [], total: 0 };
            } finally {
                this.loading = false;
            }
        },
        async onPage(event) {
            this.currentPage = event.page + 1;
            await this.fetchProgrammes(this.currentPage);
        },
        async fetchAnnees() {
            try {
                const response = await axios.get('/api/annees-formation');
                this.annees = response.data;
                console.log('Annees loaded:', this.annees);
            } catch (error) {
                this.showError('Erreur lors du chargement des années.');
                this.annees = [];
            }
        },
        setCurrentYear() {
            const now = new Date();
            let currentYear = this.annees.find((annee) => {
                const startDate = new Date(annee.date_debut);
                const endDate = new Date(annee.date_fin);
                return now >= startDate && now <= endDate;
            });

            if (!currentYear && this.annees.length > 0) {
                currentYear = this.annees.reduce((latest, current) => {
                    return new Date(current.date_fin) >
                    new Date(latest.date_fin)
                        ? current
                        : latest;
                });
            }

            this.currentYearId = currentYear ? currentYear.id : null;
            this.selectedAnnee = this.currentYearId;
        },
        setActiveTab(tab) {
            this.activeTab = tab;
            this.currentPage = 1;
            if (tab === 'tous') {
                this.filters.standardisation_ar.constraints[0].value = null;
            }
            this.fetchProgrammes();
        },
        clearFilter() {
            this.initFilters();
            this.fetchProgrammes();
        },
        clearAnneeFilter() {
            this.selectedAnnee = this.currentYearId;
            this.currentPage = 1;
            this.applyAnneeFilter();
        },
        applyAnneeFilter() {
            this.currentPage = 1;
            this.fetchProgrammes();
        },
        async refreshTable() {
            this.loading = true;
            try {
                await this.fetchProgrammes(this.currentPage);
                this.showSuccess('Tableau mis à jour.');
            } catch (error) {
                this.showError('Échec du chargement des programmes.');
            } finally {
                this.loading = false;
            }
        },
        getSeverity(value) {
            switch (value) {
                case 'Actif':
                case 'Homologuée':
                case 'Certificat de Compétence':
                case 'مقيس':
                case 'Spécifique':
                case 'Pratique':
                    return 'success';

                case 'Inactif':
                case 'Non Homologuée':
                case "Certificat de Fin d'Apprentissage":
                case 'غير مقيس':
                    return 'danger';

                case "Certificat d'Aptitude Professionnelle":
                case 'جديد':
                case 'Évaluation':
                case 'Brevet de Technicien Supérieur':
                case 'Théorique':
                    return 'warning';

                case 'Stage':
                case 'Brevet de Technicien Professionnel':
                case 'Certificat de Formation Professionnelle':
                case 'Général':
                case '----':
                    return null;

                default:
                    return null;
            }
        },

        getInfoSpecialite(data, field) {
            const info =
                data.infos_specialites?.find(
                    (info) => info.annee_formation_id === this.selectedAnnee
                ) || {};
            return info[field] || null;
        },
        calculateTotalHours(data) {
            const theoriques = parseInt(data.heures_theoriques) || 0;
            const pratiques = parseInt(data.heures_pratiques) || 0;
            const evaluation = parseInt(data.heures_evaluation) || 0;
            return theoriques + pratiques + evaluation;
        },
        openProgrammeForm(specialite = null) {
            this.selectedSpecialite = specialite;
            this.programmeToEdit = null;
            this.programmeFormVisible = true;
        },
        openModuleForm(programme) {
            if (!programme?.id) {
                this.showError('Programme invalide.');
                return;
            }
            this.selectedProgramme = programme;
            this.moduleToEdit = null;
            this.moduleFormVisible = true;
        },
        openModuleImportDialog(programme) {
            if (!programme?.id) {
                this.showError('Programme invalide.');
                return;
            }
            this.selectedProgramme = programme;
            this.selectedFile = null;
            this.importErrors = [];
            this.moduleImportDialogVisible = true;
        },
        closeModuleImportDialog() {
            this.moduleImportDialogVisible = false;
            this.selectedProgramme = null;
            this.selectedFile = null;
        },
        closeModuleImportErrors() {
            this.moduleImportErrorsVisible = false;
            this.importErrors = [];
            this.fetchProgrammes(this.currentPage);
        },
        closeProfilSpecialiteDialog() {
            this.profilSpecialiteDialogVisible = false;
            this.selectedSpecialiteId = null;
        },
        closeDetailProgrammeDialog() {
            this.detailProgrammeDialogVisible = false;
            this.selectedProgrammeId = null;
        },
        onFileSelect(event) {
            this.selectedFile = event.files[0];
        },
        async importModules() {
            if (!this.selectedFile) {
                this.showError('Veuillez sélectionner un fichier XLS.');
                return;
            }
            if (!this.selectedProgramme?.id) {
                this.showError('Aucun programme sélectionné.');
                return;
            }

            this.uploading = true;
            try {
                const formData = new FormData();
                formData.append('file', this.selectedFile);
                formData.append('programme_id', this.selectedProgramme.id);

                console.log('Envoi de la requête d\'importation', {
                    programme_id: this.selectedProgramme.id,
                    file_name: this.selectedFile.name,
                    file_size: this.selectedFile.size,
                });

                const response = await axios.post(
                    '/api/modules/importxls',
                    formData,
                    {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    }
                );

                console.log('Réponse de l\'importation', response.data);

                this.showSuccess(response.data.message);

                if (response.data.error_lines?.length > 0) {
                    this.importErrors = response.data.error_lines.map((line) => ({
                        ...line,
                        data: line.data.join(','),
                    }));
                    console.log('Erreurs d\'importation', this.importErrors);
                    this.moduleImportErrorsVisible = true;
                } else {
                    this.closeModuleImportDialog();
                    this.fetchProgrammes(this.currentPage);
                }
            } catch (error) {
                console.error('Erreur lors de l\'importation', error.response?.data);
                this.showError(
                    error.response?.data?.message ||
                    "Erreur lors de l'importation des modules."
                );
            } finally {
                this.uploading = false;
            }
        },

        handleLineImported(error) {
            this.importErrors = this.importErrors.filter(
                (e) => e.line !== error.line
            );
            if (this.importErrors.length === 0) {
                this.closeModuleImportErrors();
            }
        },
        async exportModulesXLS(programme) {
            try {
                const data = programme.modules.map((module) => ({
                    Code: module.code || '-',
                    'Titre Module': module.titre_module || '-',
                    'Type Module': module.type_module || '-',
                    'H. Théoriques': module.heures_theoriques || 0,
                    'H. Pratiques': module.heures_pratiques || 0,
                    'H. Évaluation': module.heures_evaluation || 0,
                    Statut: module.statut || 'Actif',
                    Description: module.description || '-',
                    Observation: module.observation || '-',
                }));

                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet('Modules');
                worksheet.columns = [
                    { header: 'Code', key: 'Code', width: 15 },
                    { header: 'Titre Module', key: 'Titre Module', width: 30 },
                    { header: 'Type Module', key: 'Type Module', width: 15 },
                    {
                        header: 'H. Théoriques',
                        key: 'H. Théoriques',
                        width: 12,
                    },
                    { header: 'H. Pratiques', key: 'H. Pratiques', width: 12 },
                    {
                        header: 'H. Évaluation',
                        key: 'H. Évaluation',
                        width: 12,
                    },
                    { header: 'Statut', key: 'Statut', width: 15 },
                    { header: 'Description', key: 'Description', width: 30 },
                    { header: 'Observation', key: 'Observation', width: 30 },
                ];
                data.forEach((row) => worksheet.addRow(row));
                const buffer = await workbook.xlsx.writeBuffer();
                const blob = new Blob([buffer], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `modules_${programme.version}.xlsx`;
                link.click();
                URL.revokeObjectURL(link.href);
                this.showSuccess('Exportation des modules réussie.');
            } catch (error) {
                this.showError("Erreur lors de l'exportation des modules.");
            }
        },
        async openDocumentProgrammeForm(programme) {
            if (!programme?.id) {
                this.showError('Programme invalide.');
                return;
            }
            this.selectedProgramme = programme;
            this.selectedSpecialite = programme.specialite;
            this.documentProgrammeToEdit = null;
            await nextTick();
            this.documentProgrammeFormVisible = true;
        },
        async openDocumentModuleForm(module) {
            if (!module?.id) {
                this.showError('DocumentProgrammeSpecialite invalide.');
                return;
            }
            this.selectedModule = module;
            this.documentModuleToEdit = null;
            await nextTick();
            this.documentModuleFormVisible = true;
        },
        closeProgrammeForm() {
            this.programmeFormVisible = false;
            this.programmeToEdit = null;
            this.selectedSpecialite = null;
        },
        closeModuleForm() {
            this.moduleFormVisible = false;
            this.moduleToEdit = null;
            this.selectedProgramme = null;
        },
        closeDocumentProgrammeForm() {
            this.documentProgrammeFormVisible = false;
            this.documentProgrammeToEdit = null;
            this.selectedProgramme = null;
            this.selectedSpecialite = null;
        },
        closeDocumentModuleForm() {
            this.documentModuleFormVisible = false;
            this.documentModuleToEdit = null;
            this.selectedModule = null;
        },
        editProgramme(programme) {
            if (!programme?.id) {
                this.showError('Programme invalide.');
                return;
            }
            this.programmeToEdit = { ...programme };
            this.selectedSpecialite = programme.specialite;
            this.programmeFormVisible = true;
        },
        viewProgramme(programme) {
            if (!programme?.id) {
                this.showError('Programme invalide.');
                return;
            }
            this.selectedProgrammeId = programme.id;
            this.detailProgrammeDialogVisible = true;
        },
        editModule(module) {
            if (!module?.id) {
                this.showError('DocumentProgrammeSpecialite invalide.');
                return;
            }
            this.moduleToEdit = { ...module };
            this.selectedProgramme = module.programme;
            this.moduleFormVisible = true;
        },
        confirmDeleteProgramme(programme) {
            if (!programme?.id) {
                this.showError('Programme invalide.');
                return;
            }
            this.programmeToDelete = programme;
            this.deleteProgrammeDialog = true;
        },
        confirmDeleteModule(module) {
            if (!module?.id) {
                this.showError('DocumentProgrammeSpecialite invalide.');
                return;
            }
            this.moduleToDelete = module;
            this.deleteModuleDialog = true;
        },
        async deleteProgramme() {
            try {
                await axios.delete(
                    `/api/programmes/${this.programmeToDelete.id}`
                );
                this.showSuccess('Programme supprimé avec succès');
                this.fetchProgrammes(this.currentPage);
            } catch (error) {
                this.showError(
                    error.response?.data?.error ||
                    'Erreur lors de la suppression du programme'
                );
            } finally {
                this.deleteProgrammeDialog = false;
                this.programmeToDelete = null;
            }
        },
        async deleteModule() {
            try {
                await axios.delete(`/api/modules/${this.moduleToDelete.id}`);
                this.showSuccess('DocumentProgrammeSpecialite supprimé avec succès');
                this.fetchProgrammes(this.currentPage);
            } catch (error) {
                this.showError(
                    error.response?.data?.error ||
                    'Erreur lors de la suppression du module'
                );
            } finally {
                this.deleteModuleDialog = false;
                this.moduleToDelete = null;
            }
        },
        async handleSaveProgramme(newProgramme) {
            try {
                await axios.post('/api/programmes', newProgramme);
                this.showSuccess('Programme créé avec succès');
                this.programmeFormVisible = false;
                this.fetchProgrammes(this.currentPage);
            } catch (error) {
                this.showError(
                    error.response?.data?.error ||
                    'Erreur lors de la création du programme'
                );
            }
        },
        async handleUpdateProgramme(updatedProgramme) {
            try {
                await axios.put(
                    `/api/programmes/${updatedProgramme.id}`,
                    updatedProgramme
                );
                this.showSuccess('Programme mis à jour avec succès');
                this.programmeFormVisible = false;
                this.fetchProgrammes(this.currentPage);
            } catch (error) {
                this.showError(
                    error.response?.data?.error ||
                    'Erreur lors de la mise à jour du programme'
                );
            }
        },
        async handleSaveModule(formData) {
            try {
                await axios.post('/api/modules', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                });
                this.showSuccess('DocumentProgrammeSpecialite créé avec succès');
                this.moduleFormVisible = false;
                this.fetchProgrammes(this.currentPage);
            } catch (error) {
                this.showError(
                    error.response?.data?.error ||
                    'Erreur lors de la création du module'
                );
            }
        },
        async handleUpdateModule(formData) {
            try {
                await axios.post(
                    `/api/modules/${formData.get('id')}`,
                    formData,
                    {
                        headers: { 'Content-Type': 'multipart/form-data' },
                        transformRequest: [
                            (data) => {
                                data.append('_method', 'PUT');
                                return data;
                            },
                        ],
                    }
                );
                this.showSuccess('DocumentProgrammeSpecialite mis à jour avec succès');
                this.moduleFormVisible = false;
                this.fetchProgrammes(this.currentPage);
            } catch (error) {
                this.showError(
                    error.response?.data?.error ||
                    'Erreur lors de la mise à jour du module'
                );
            }
        },
        async handleSaveDocumentProgramme(formData) {
            try {
                await axios.post('/api/documents-programmes', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                });
                this.showSuccess('Document ajouté avec succès');
                this.documentProgrammeFormVisible = false;
                this.fetchProgrammes(this.currentPage);
            } catch (error) {
                this.showError(
                    error.response?.data?.error ||
                    "Erreur lors de l'ajout du document"
                );
            }
        },
        async handleUpdateDocumentProgramme(formData) {
            try {
                await axios.post(
                    `/api/documents-programmes/${formData.get('id')}`,
                    formData,
                    {
                        headers: { 'Content-Type': 'multipart/form-data' },
                        transformRequest: [
                            (data) => {
                                data.append('_method', 'PUT');
                                return data;
                            },
                        ],
                    }
                );
                this.showSuccess('Document mis à jour avec succès');
                this.documentProgrammeFormVisible = false;
                this.fetchProgrammes(this.currentPage);
            } catch (error) {
                this.showError(
                    error.response?.data?.error ||
                    'Erreur lors de la mise à jour du document'
                );
            }
        },
        async handleSaveDocumentModule(formData) {
            try {
                await axios.post('/api/documents-modules', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                });
                this.showSuccess('Document ajouté avec succès');
                this.documentModuleFormVisible = false;
                this.fetchProgrammes(this.currentPage);
            } catch (error) {
                this.showError(
                    error.response?.data?.error ||
                    "Erreur lors de l'ajout du document"
                );
            }
        },
        async handleUpdateDocumentModule(formData) {
            try {
                await axios.post(
                    `/api/documents-modules/${formData.get('id')}`,
                    formData,
                    {
                        headers: { 'Content-Type': 'multipart/form-data' },
                        transformRequest: [
                            (data) => {
                                data.append('_method', 'PUT');
                                return data;
                            },
                        ],
                    }
                );
                this.showSuccess('Document mis à jour avec succès');
                this.documentModuleFormVisible = false;
                this.fetchProgrammes(this.currentPage);
            } catch (error) {
                this.showError(
                    error.response?.data?.error ||
                    'Erreur lors de la mise à jour du document'
                );
            }
        },
        viewSpecialite(specialiteId) {
            if (!specialiteId) {
                this.showError('Identifiant de spécialité invalide.');
                return;
            }
            const specialite = this.specialites.data.find(
                (s) => s.id === specialiteId
            );
            if (!specialite) {
                this.showError('Spécialité non trouvée.');
                return;
            }
            console.log(
                'Opening ProfilSpecialite for specialiteId:',
                specialiteId
            );
            this.selectedSpecialiteId = specialiteId;
            this.profilSpecialiteDialogVisible = true;
        },
        formatDate(date) {
            if (!date) return null;
            const d = new Date(date);
            return d.toISOString().split('T')[0];
        },
        async exportXLS() {
            try {
                const data = this.specialites.data.flatMap((specialite) => {
                    return specialite.programmes.flatMap((programme) => {
                        return [
                            {
                                'Code Spécialité': specialite.code,
                                'Nom (FR)': specialite.nom_fr,
                                'Nom (AR)': specialite.nom_ar,
                                Diplôme: specialite.diplome_fr || '-',
                                'Standardisation (AR)':
                                    specialite.standardisation_ar || '-',
                                Homologation:
                                    this.getInfoSpecialite(
                                        specialite,
                                        'homologation_fr'
                                    ) || '-',
                                'Durée Formation':
                                    this.getInfoSpecialite(
                                        specialite,
                                        'duree_formation'
                                    ) || '-',
                                'Version Programme': programme.version || '-',
                                'Date Début':
                                    this.formatDate(programme.date_debut) ||
                                    '-',
                                'Date Fin':
                                    this.formatDate(programme.date_fin) || '-',
                                'Statut Programme': programme.statut || 'Actif',
                                'Code Module': '',
                                'Titre Module': '',
                                'Type Module': '',
                                'H. Théoriques': '',
                                'H. Pratiques': '',
                                'H. Évaluation': '',
                                'H. Total': '',
                                'Statut Module': '',
                            },
                        ].concat(
                            programme.modules.map((module) => ({
                                'Code Spécialité': '',
                                'Nom (FR)': '',
                                'Nom (AR)': '',
                                Diplôme: '',
                                'Standardisation (AR)': '',
                                Homologation: '',
                                'Durée Formation': '',
                                'Version Programme': '',
                                'Date Début': '',
                                'Date Fin': '',
                                'Statut Programme': '',
                                'Code Module': module.code || '-',
                                'Titre Module': module.titre_module || '-',
                                'Type Module': module.type_module || '-',
                                'H. Théoriques': module.heures_theoriques || 0,
                                'H. Pratiques': module.heures_pratiques || 0,
                                'H. Évaluation': module.heures_evaluation || 0,
                                'H. Total': this.calculateTotalHours(module),
                                'Statut Module': module.statut || 'Actif',
                            }))
                        );
                    });
                });

                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet('Programmes');
                worksheet.columns = [
                    {
                        header: 'Code Spécialité',
                        key: 'Code Spécialité',
                        width: 15,
                    },
                    { header: 'Nom (FR)', key: 'Nom (FR)', width: 30 },
                    { header: 'Nom (AR)', key: 'Nom (AR)', width: 30 },
                    { header: 'Diplôme', key: 'Diplôme', width: 20 },
                    {
                        header: 'Standardisation (AR)',
                        key: 'Standardisation (AR)',
                        width: 15,
                    },
                    { header: 'Homologation', key: 'Homologation', width: 15 },
                    {
                        header: 'Durée Formation',
                        key: 'Durée Formation',
                        width: 15,
                    },
                    {
                        header: 'Version Programme',
                        key: 'Version Programme',
                        width: 20,
                    },
                    { header: 'Date Début', key: 'Date Début', width: 15 },
                    { header: 'Date Fin', key: 'Date Fin', width: 15 },
                    {
                        header: 'Statut Programme',
                        key: 'Statut Programme',
                        width: 15,
                    },
                    { header: 'Code Module', key: 'Code Module', width: 15 },
                    { header: 'Titre Module', key: 'Titre Module', width: 30 },
                    { header: 'Type Module', key: 'Type Module', width: 15 },
                    {
                        header: 'H. Théoriques',
                        key: 'H. Théoriques',
                        width: 12,
                    },
                    { header: 'H. Pratiques', key: 'H. Pratiques', width: 12 },
                    {
                        header: 'H. Évaluation',
                        key: 'H. Évaluation',
                        width: 12,
                    },
                    { header: 'H. Total', key: 'H. Total', width: 12 },
                    {
                        header: 'Statut Module',
                        key: 'Statut Module',
                        width: 15,
                    },
                ];
                data.forEach((row) => worksheet.addRow(row));
                const buffer = await workbook.xlsx.writeBuffer();
                const blob = new Blob([buffer], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'programmes.xlsx';
                link.click();
                URL.revokeObjectURL(link.href);
                this.showSuccess('Exportation réussie.');
            } catch (error) {
                this.showError("Erreur lors de l'exportation des programmes.");
            }
        },
        showSuccess(message) {
            this.toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: message,
                life: 3000,
            });
        },
        showError(message) {
            this.toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: message,
                life: 5000,
            });
        },
        showInfo(message) {
            this.toast.add({
                severity: 'info',
                summary: 'Information',
                detail: message,
                life: 5000,
            });
        },
    },
};
</script>

<style scoped>
@font-face {
    font-family: 'Montserrat-Arabic-Regular';
    src: url('/fonts/Montserrat-Arabic-Regular.otf') format('opentype');
    font-weight: normal;
    font-style: normal;
}

.confirmation-content {
    display: flex;
    align-items: center;
    padding: 1rem;
}

.p-datatable {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.p-datatable-sm .p-datatable-tbody > tr > td {
    padding: 0.5rem 1rem;
}

.p-datatable .p-column-header-t {
    justify-content: center;
}

.arabic-normal {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
    direction: rtl;
}

.arabic-gras-normal {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
    font-weight: bold;
    direction: rtl;
}

.button-height {
    height: 40px;
}

.annee-filter {
    display: flex;
    align-items: center;
    gap: 8px;
}

.search-field {
    display: flex;
    align-items: center;
}
</style>

