<template>
    <div>
        <!-- Formulaire pour Ajouter/Modifier un Contrat -->
        <Dialog
            :visible="visible"
            @update:visible="handleVisibleUpdate"
            :modal="true"
            :focusOnShow="false"
            :style="{ width: '90vw', maxWidth: '1200px' }"
            :breakpoints="{ '960px': '98vw', '640px': '100vw' }"
            class="p-fluid"
            :pt="{
                root: 'border-round-xl shadow-5',
                mask: { style: 'backdrop-filter: blur(4px)' },
                header: { class: 'surface-50 border-bottom-1 surface-border p-4' },
                content: { class: 'surface-ground p-4' },
                footer: { class: 'surface-50 border-top-1 surface-border p-4' }
            }"
        >
            <template #header>
                <div class="flex align-items-center gap-2">
                    <i class="pi pi-file text-primary text-2xl"></i>
                    <span class="font-bold text-2xl">
                        {{ isEditMode ? 'Modifier le Contrat' : 'Ajouter un Contrat' }}
                    </span>
                </div>
            </template>
            <div v-if="loading" class="flex flex-column align-items-center justify-content-center gap-3 p-6">
                <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
                <span class="text-color-secondary">Chargement des données...</span>
            </div>
            <div v-else class="surface-card p-4 shadow-2 border-round">
                <TabView ref="tabview" v-model:activeIndex="activeTab">
                    <TabPanel header="Détails du Contrat">
                        <form @submit.prevent="submitForm">
                            <div class="grid p-fluid">
                                <div class="col-12 field">
                                    <span class="font-bold text-lg text-blue-700">
                                        {{ isEditMode ? `Modification du contrat de ${personnelFullName}` : '' }}
                                    </span>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="type_contrat_fr" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Type de Contrat (Français) <span class="text-red-500">*</span>
                                    </label>
                                    <Dropdown
                                        id="type_contrat_fr"
                                        v-model="form.type_contrat_fr"
                                        :options="typeContracts"
                                        optionLabel="nom_fr"
                                        optionValue="valeur"
                                        placeholder="Sélectionner un type de contrat"
                                        :class="{ 'p-invalid': errors.type_contrat_fr }"
                                        :loading="loadingLists"
                                        style="font-size: 0.875rem; border: 2px solid blue;"
                                        :key="dropdownKey"
                                    />
                                    <small v-if="errors.type_contrat_fr" class="p-error" style="font-size: 0.75rem">{{ errors.type_contrat_fr }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="type_contrat_ar" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Type de Contrat (Arabe)
                                    </label>
                                    <InputText
                                        id="type_contrat_ar"
                                        v-model="form.type_contrat_ar"
                                        :class="{ 'p-invalid': errors.type_contrat_ar }"
                                        placeholder="Type de contrat en arabe"
                                        style="font-size: 0.875rem"
                                        dir="rtl"
                                    />
                                    <small v-if="errors.type_contrat_ar" class="p-error" style="font-size: 0.75rem">{{ errors.type_contrat_ar }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="num_contrat" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Numéro du Contrat <span class="text-red-500">*</span>
                                    </label>
                                    <InputText
                                        id="num_contrat"
                                        v-model="form.num_contrat"
                                        :class="{ 'p-invalid': errors.num_contrat }"
                                        placeholder="Entrez le numéro du contrat"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="errors.num_contrat" class="p-error" style="font-size: 0.75rem">{{ errors.num_contrat }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="num_decision" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Numéro de Décision
                                    </label>
                                    <InputText
                                        id="num_decision"
                                        v-model="form.num_decision"
                                        :class="{ 'p-invalid': errors.num_decision }"
                                        placeholder="Entrez le numéro de décision"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="errors.num_decision" class="p-error" style="font-size: 0.75rem">{{ errors.num_decision }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="date_decision" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Date de Décision
                                    </label>
                                    <Calendar
                                        id="date_decision"
                                        v-model="form.date_decision"
                                        :class="{ 'p-invalid': errors.date_decision }"
                                        dateFormat="yy-mm-dd"
                                        showIcon
                                        placeholder="Sélectionner une date"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="errors.date_decision" class="p-error" style="font-size: 0.75rem">{{ errors.date_decision }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="date_debut" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Date de Début <span class="text-red-500">*</span>
                                    </label>
                                    <Calendar
                                        id="date_debut"
                                        v-model="form.date_debut"
                                        :class="{ 'p-invalid': errors.date_debut }"
                                        dateFormat="yy-mm-dd"
                                        showIcon
                                        placeholder="Sélectionner une date"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="errors.date_debut" class="p-error" style="font-size: 0.75rem">{{ errors.date_debut }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="date_fin" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Date de Fin
                                    </label>
                                    <Calendar
                                        id="date_fin"
                                        v-model="form.date_fin"
                                        :class="{ 'p-invalid': errors.date_fin }"
                                        dateFormat="yy-mm-dd"
                                        showIcon
                                        placeholder="Sélectionner une date"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="errors.date_fin" class="p-error" style="font-size: 0.75rem">{{ errors.date_fin }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="heures_travail" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Heures de Travail
                                    </label>
                                    <InputNumber
                                        id="heures_travail"
                                        v-model="form.heures_travail"
                                        :class="{ 'p-invalid': errors.heures_travail }"
                                        placeholder="Entrez les heures de travail"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="errors.heures_travail" class="p-error" style="font-size: 0.75rem">{{ errors.heures_travail }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="date_resiliation" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Date de Résiliation
                                    </label>
                                    <Calendar
                                        id="date_resiliation"
                                        v-model="form.date_resiliation"
                                        :class="{ 'p-invalid': errors.date_resiliation }"
                                        dateFormat="yy-mm-dd"
                                        showIcon
                                        placeholder="Sélectionner une date"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="errors.date_resiliation" class="p-error" style="font-size: 0.75rem">{{ errors.date_resiliation }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="motif_resiliation" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Motif de Résiliation
                                    </label>
                                    <InputText
                                        id="motif_resiliation"
                                        v-model="form.motif_resiliation"
                                        :class="{ 'p-invalid': errors.motif_resiliation }"
                                        placeholder="Entrez le motif de résiliation"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="errors.motif_resiliation" class="p-error" style="font-size: 0.75rem">{{ errors.motif_resiliation }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="statut" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Statut
                                    </label>
                                    <Dropdown
                                        id="statut"
                                        v-model="form.statut"
                                        :options="statutOptions"
                                        optionLabel="label"
                                        optionValue="value"
                                        placeholder="Sélectionner un statut"
                                        :class="{ 'p-invalid': errors.statut }"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="errors.statut" class="p-error" style="font-size: 0.75rem">{{ errors.statut }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="document_path" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Fichier
                                    </label>
                                    <FileUpload
                                        ref="fileUploadRef"
                                        name="document_path"
                                        accept="application/pdf,image/png,image/jpeg"
                                        :maxFileSize="5000000"
                                        :customUpload="true"
                                        chooseLabel="Choisir"
                                        :showUploadButton="false"
                                        cancelLabel="Annuler"
                                        @select="handleFileSelect"
                                        @clear="handleClear"
                                        :class="{ 'p-invalid': errors.document_path }"
                                        style="font-size: 0.875rem"
                                    >
                                        <template #empty>
                                            <p>
                                                Glissez-déposez un fichier ici ou cliquez pour sélectionner (PDF, PNG, JPEG, max 5Mo).
                                            </p>
                                        </template>
                                    </FileUpload>
                                    <small v-if="errors.document_path" class="p-error" style="font-size: 0.75rem">{{ errors.document_path }}</small>
                                </div>
                            </div>
                        </form>
                    </TabPanel>
                    <TabPanel header="Observation">
                        <div class="grid p-fluid">
                            <div class="col-12 field">
                                <label for="observation" class="block font-medium mb-2" style="font-size: 0.875rem">
                                    Observation
                                </label>
                                <Textarea
                                    id="observation"
                                    v-model="form.observation"
                                    rows="4"
                                    :class="{ 'p-invalid': errors.observation }"
                                    placeholder="Entrez vos observations"
                                    style="width: 100%; font-size: 0.875rem"
                                />
                                <small v-if="errors.observation" class="p-error" style="font-size: 0.75rem">{{ errors.observation }}</small>
                            </div>
                        </div>
                    </TabPanel>
                    <TabPanel header="Liste des Contrats">
                        <div class="flex justify-content-between align-items-center mb-4">
                            <div class="flex align-items-center gap-2">
                                <span class="font-bold text-lg">
                                    Liste des contrats de <span class="text-primary">{{ personnelFullName }}</span>
                                </span>
                            </div>
                            <div class="flex align-items-center gap-2">
                                <span class="p-input-icon-left search-field">
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
                        </div>
                        <DataTable
                            v-model:filters="filters"
                            :value="contracts"
                            :loading="loadingContracts"
                            dataKey="id"
                            size="small"
                            stripedRows
                            paginator
                            :rows="10"
                            :rowsPerPageOptions="[10, 20, 50]"
                            filterDisplay="menu"
                            :globalFilterFields="[
                                'type_contrat_fr',
                                'type_contrat_ar',
                                'num_contrat',
                                'date_debut',
                                'date_fin',
                                'statut'
                            ]"
                            scrollable
                            scrollHeight="600px"
                            removableSort
                            class="p-datatable-sm border-round-lg"
                        >
                            <template #empty>
                                <div class="text-center py-4">
                                    <i class="pi pi-inbox text-4xl text-400 mb-2" />
                                    <p class="text-600">Aucun contrat trouvé</p>
                                </div>
                            </template>
                            <Column
                                field="type_contrat_fr"
                                header="Type (Français)"
                                sortable
                                style="min-width: 12rem"
                            >
                                <template #body="{ data }">
                                    <span class="font-medium">{{ data.type_contrat_fr }}</span>
                                </template>
                                <template #filter="{ filterModel, filterCallback }">
                                    <InputText
                                        v-model="filterModel.value"
                                        type="text"
                                        class="p-column-filter"
                                        placeholder="Rechercher par type (Français)"
                                        @input="filterCallback"
                                    />
                                </template>
                            </Column>
                            <Column
                                field="type_contrat_ar"
                                header="Type (Arabe)"
                                sortable
                                style="min-width: 12rem"
                                class="arabic-text"
                            >
                                <template #body="{ data }">
                                    <div class="flex align-items-center gap-2">
                                        <i class="pi pi-tag text-primary-500" />
                                        {{ data.type_contrat_ar || 'غير محدد' }}
                                    </div>
                                </template>
                                <template #filter="{ filterModel, filterCallback }">
                                    <InputText
                                        v-model="filterModel.value"
                                        type="text"
                                        class="p-column-filter arabic-text"
                                        placeholder="البحث حسب النوع (العربية)"
                                        @input="filterCallback"
                                    />
                                </template>
                            </Column>
                            <Column
                                field="num_contrat"
                                header="Numéro du Contrat"
                                sortable
                                style="min-width: 10rem"
                            >
                                <template #body="{ data }">
                                    <span>{{ data.num_contrat }}</span>
                                </template>
                            </Column>
                            <Column
                                field="date_debut"
                                header="Date de Début"
                                sortable
                                style="min-width: 10rem"
                            >
                                <template #body="{ data }">
                                    <Tag
                                        :value="formatDate(data.date_debut)"
                                        icon="pi pi-calendar"
                                    />
                                </template>
                            </Column>
                            <Column
                                field="date_fin"
                                header="Date de Fin"
                                sortable
                                style="min-width: 10rem"
                            >
                                <template #body="{ data }">
                                    <Tag
                                        :value="formatDate(data.date_fin) || 'Non spécifiée'"
                                        icon="pi pi-calendar"
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
                                        :value="data.statut"
                                        :severity="getSeverity(data.statut)"
                                        :icon="getStatutIcon(data.statut)"
                                    />
                                </template>
                            </Column>
                            <Column header="Actions" style="min-width: 10rem">
                                <template #body="slotProps">
                                    <Button
                                        icon="pi pi-pencil"
                                        class="p-button-rounded p-button-success p-button-text"
                                        @click="openEditDialog(slotProps.data)"
                                        v-tooltip="'Modifier'"
                                    />
                                    <Button
                                        icon="pi pi-trash"
                                        class="p-button-rounded p-button-danger p-button-text"
                                        @click="deleteContract(slotProps.data)"
                                        v-tooltip="'Supprimer'"
                                    />
                                    <Button
                                        icon="pi pi-download"
                                        class="p-button-rounded p-button-info p-button-text"
                                        @click="downloadContract(slotProps.data.id)"
                                        v-tooltip="'Télécharger'"
                                    />
                                </template>
                            </Column>
                        </DataTable>
                    </TabPanel>
                </TabView>
            </div>
            <template #footer>
                <div class="flex justify-content-end gap-3">
                    <Button
                        label="Annuler"
                        icon="pi pi-times-circle"
                        class="p-button-outlined p-button-secondary p-button-sm"
                        @click="handleCancel"
                        style="font-size: 0.875rem"
                    />
                    <Button
                        :label="isEditMode ? 'Modifier' : 'Enregistrer'"
                        icon="pi pi-check-circle"
                        class="p-button-primary p-button-sm"
                        @click="submitForm"
                        :loading="submitting"
                        style="font-size: 0.875rem"
                    />
                </div>
            </template>
        </Dialog>

        <!-- Formulaire pour Modifier un Contrat -->
        <Dialog
            :visible="editDialogVisible"
            @update:visible="closeEditDialog"
            :modal="true"
            :focusOnShow="false"
            :style="{ width: '90vw', maxWidth: '1200px' }"
            :breakpoints="{ '960px': '98vw', '640px': '100vw' }"
            class="p-fluid"
            :pt="{
                root: 'border-round-xl shadow-5',
                mask: { style: 'backdrop-filter: blur(4px)' },
                header: { class: 'surface-50 border-bottom-1 surface-border p-4' },
                content: { class: 'surface-ground p-4' },
                footer: { class: 'surface-50 border-top-1 surface-border p-4' }
            }"
        >
            <template #header>
                <div class="flex align-items-center gap-2">
                    <i class="pi pi-file-edit text-primary text-2xl"></i>
                    <span class="font-bold text-2xl">Modifier le Contrat</span>
                </div>
            </template>
            <div v-if="loading" class="flex flex-column align-items-center justify-content-center gap-3 p-6">
                <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
                <span class="text-color-secondary">Chargement des données...</span>
            </div>
            <div v-else class="surface-card p-4 shadow-2 border-round">
                <TabView>
                    <TabPanel header="Détails du Contrat">
                        <form @submit.prevent="submitEditForm">
                            <div class="grid p-fluid">
                                <div class="col-12 field">
                                    <span class="font-bold text-lg text-blue-700">
                                        Modification du contrat de {{ personnelFullName }}
                                    </span>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="edit_type_contrat_fr" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Type de Contrat (Français) <span class="text-red-500">*</span>
                                    </label>
                                    <Dropdown
                                        id="edit_type_contrat_fr"
                                        v-model="editForm.type_contrat_fr"
                                        :options="typeContracts"
                                        optionLabel="nom_fr"
                                        optionValue="valeur"
                                        placeholder="Sélectionner un type de contrat"
                                        :class="{ 'p-invalid': editErrors.type_contrat_fr }"
                                        :loading="loadingLists"
                                        style="font-size: 0.875rem; border: 2px solid blue;"
                                        :key="dropdownKey"
                                    />
                                    <small v-if="editErrors.type_contrat_fr" class="p-error" style="font-size: 0.75rem">{{ editErrors.type_contrat_fr }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="edit_type_contrat_ar" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Type de Contrat (Arabe)
                                    </label>
                                    <InputText
                                        id="edit_type_contrat_ar"
                                        v-model="editForm.type_contrat_ar"
                                        :class="{ 'p-invalid': editErrors.type_contrat_ar }"
                                        placeholder="Type de contrat en arabe"
                                        style="font-size: 0.875rem"
                                        dir="rtl"
                                    />
                                    <small v-if="editErrors.type_contrat_ar" class="p-error" style="font-size: 0.75rem">{{ editErrors.type_contrat_ar }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="edit_num_contrat" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Numéro du Contrat <span class="text-red-500">*</span>
                                    </label>
                                    <InputText
                                        id="edit_num_contrat"
                                        v-model="editForm.num_contrat"
                                        :class="{ 'p-invalid': editErrors.num_contrat }"
                                        placeholder="Entrez le numéro du contrat"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="editErrors.num_contrat" class="p-error" style="font-size: 0.75rem">{{ editErrors.num_contrat }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="edit_num_decision" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Numéro de Décision
                                    </label>
                                    <InputText
                                        id="edit_num_decision"
                                        v-model="editForm.num_decision"
                                        :class="{ 'p-invalid': editErrors.num_decision }"
                                        placeholder="Entrez le numéro de décision"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="editErrors.num_decision" class="p-error" style="font-size: 0.75rem">{{ editErrors.num_decision }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="edit_date_decision" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Date de Décision
                                    </label>
                                    <Calendar
                                        id="edit_date_decision"
                                        v-model="editForm.date_decision"
                                        :class="{ 'p-invalid': editErrors.date_decision }"
                                        dateFormat="yy-mm-dd"
                                        showIcon
                                        placeholder="Sélectionner une date"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="editErrors.date_decision" class="p-error" style="font-size: 0.75rem">{{ editErrors.date_decision }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="edit_date_debut" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Date de Début <span class="text-red-500">*</span>
                                    </label>
                                    <Calendar
                                        id="edit_date_debut"
                                        v-model="editForm.date_debut"
                                        :class="{ 'p-invalid': editErrors.date_debut }"
                                        dateFormat="yy-mm-dd"
                                        showIcon
                                        placeholder="Sélectionner une date"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="editErrors.date_debut" class="p-error" style="font-size: 0.75rem">{{ editErrors.date_debut }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="edit_date_fin" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Date de Fin
                                    </label>
                                    <Calendar
                                        id="edit_date_fin"
                                        v-model="editForm.date_fin"
                                        :class="{ 'p-invalid': editErrors.date_fin }"
                                        dateFormat="yy-mm-dd"
                                        showIcon
                                        placeholder="Sélectionner une date"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="editErrors.date_fin" class="p-error" style="font-size: 0.75rem">{{ editErrors.date_fin }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="edit_heures_travail" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Heures de Travail
                                    </label>
                                    <InputNumber
                                        id="edit_heures_travail"
                                        v-model="editForm.heures_travail"
                                        :class="{ 'p-invalid': editErrors.heures_travail }"
                                        placeholder="Entrez les heures de travail"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="editErrors.heures_travail" class="p-error" style="font-size: 0.75rem">{{ editErrors.heures_travail }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="edit_date_resiliation" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Date de Résiliation
                                    </label>
                                    <Calendar
                                        id="edit_date_resiliation"
                                        v-model="editForm.date_resiliation"
                                        :class="{ 'p-invalid': editErrors.date_resiliation }"
                                        dateFormat="yy-mm-dd"
                                        showIcon
                                        placeholder="Sélectionner une date"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="editErrors.date_resiliation" class="p-error" style="font-size: 0.75rem">{{ editErrors.date_resiliation }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="edit_motif_resiliation" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Motif de Résiliation
                                    </label>
                                    <InputText
                                        id="edit_motif_resiliation"
                                        v-model="editForm.motif_resiliation"
                                        :class="{ 'p-invalid': editErrors.motif_resiliation }"
                                        placeholder="Entrez le motif de résiliation"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="editErrors.motif_resiliation" class="p-error" style="font-size: 0.75rem">{{ editErrors.motif_resiliation }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="edit_statut" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Statut
                                    </label>
                                    <Dropdown
                                        id="edit_statut"
                                        v-model="editForm.statut"
                                        :options="statutOptions"
                                        optionLabel="label"
                                        optionValue="value"
                                        placeholder="Sélectionner un statut"
                                        :class="{ 'p-invalid': editErrors.statut }"
                                        style="font-size: 0.875rem"
                                    />
                                    <small v-if="editErrors.statut" class="p-error" style="font-size: 0.75rem">{{ editErrors.statut }}</small>
                                </div>
                                <div class="col-12 md:col-6 field">
                                    <label for="edit_document_path" class="block font-medium mb-2" style="font-size: 0.875rem">
                                        Fichier
                                    </label>
                                    <FileUpload
                                        ref="editFileUploadRef"
                                        name="edit_document_path"
                                        accept="application/pdf,image/png,image/jpeg"
                                        :maxFileSize="5000000"
                                        :customUpload="true"
                                        chooseLabel="Choisir"
                                        :showUploadButton="false"
                                        cancelLabel="Annuler"
                                        @select="handleEditFileSelect"
                                        @clear="handleEditClear"
                                        :class="{ 'p-invalid': editErrors.document_path }"
                                        style="font-size: 0.875rem"
                                    >
                                        <template #empty>
                                            <p>
                                                Glissez-déposez un fichier ici ou cliquez pour sélectionner (PDF, PNG, JPEG, max 5Mo).
                                            </p>
                                        </template>
                                    </FileUpload>
                                    <small v-if="editErrors.document_path" class="p-error" style="font-size: 0.75rem">{{ editErrors.document_path }}</small>
                                </div>
                            </div>
                        </form>
                    </TabPanel>
                    <TabPanel header="Observation">
                        <div class="grid p-fluid">
                            <div class="col-12 field">
                                <label for="edit_observation" class="block font-medium mb-2" style="font-size: 0.875rem">
                                    Observation
                                </label>
                                <Textarea
                                    id="edit_observation"
                                    v-model="editForm.observation"
                                    rows="4"
                                    :class="{ 'p-invalid': editErrors.observation }"
                                    placeholder="Entrez vos observations"
                                    style="width: 100%; font-size: 0.875rem"
                                />
                                <small v-if="editErrors.observation" class="p-error" style="font-size: 0.75rem">{{ editErrors.observation }}</small>
                            </div>
                        </div>
                    </TabPanel>
                </TabView>
            </div>
            <template #footer>
                <div class="flex justify-content-end gap-3">
                    <Button
                        label="Annuler"
                        icon="pi pi-times-circle"
                        class="p-button-outlined p-button-secondary p-button-sm"
                        @click="closeEditDialog"
                        style="font-size: 0.875rem"
                    />
                    <Button
                        label="Modifier"
                        icon="pi pi-check-circle"
                        class="p-button-primary p-button-sm"
                        @click="submitEditForm"
                        :loading="submitting"
                        style="font-size: 0.875rem"
                    />
                </div>
            </template>
        </Dialog>
        <!-- Toast Notification -->
        <Toast position="top-right" />
    </div>
</template>

<script>
import axios from '@/axios.js';
import Button from 'primevue/button';
import Calendar from 'primevue/calendar';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import ProgressSpinner from 'primevue/progressspinner';
import Textarea from 'primevue/textarea';
import FileUpload from 'primevue/fileupload';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';
import { FilterMatchMode } from 'primevue/api';

export default {
    name: 'ContratPersonnelForm',
    components: {
        Button,
        Calendar,
        Dialog,
        Dropdown,
        InputText,
        InputNumber,
        ProgressSpinner,
        Textarea,
        FileUpload,
        TabView,
        TabPanel,
        DataTable,
        Column,
        Tag,
        Toast
    },
    props: {
        visible: { type: Boolean, default: false },
        contratToEdit: { type: Object, default: null },
        personnelId: { type: Number, required: true },
        isCentreRole: { type: Boolean, default: false }
    },
    data() {
        return {
            form: {
                id: null,
                personnel_id: this.personnelId,
                type_contrat_fr: null,
                type_contrat_ar: '',
                num_contrat: '',
                num_decision: '',
                date_decision: null,
                date_debut: null,
                date_fin: null,
                heures_travail: null,
                date_resiliation: null,
                motif_resiliation: '',
                document_path: null,
                observation: '',
                statut: 'Actif'
            },
            editForm: {
                id: null,
                personnel_id: this.personnelId,
                type_contrat_fr: null,
                type_contrat_ar: '',
                num_contrat: '',
                num_decision: '',
                date_decision: null,
                date_debut: null,
                date_fin: null,
                heures_travail: null,
                date_resiliation: null,
                motif_resiliation: '',
                document_path: null,
                observation: '',
                statut: 'Actif'
            },
            errors: {},
            editErrors: {},
            filters: null,
            loading: false,
            loadingLists: false,
            loadingContracts: false,
            submitting: false,
            editDialogVisible: false,
            contracts: [],
            typeContracts: [],
            dropdownKey: 0,
            statutOptions: [
                { label: 'Actif', value: 'Actif' },
                { label: 'Terminé', value: 'Terminé' },
                { label: 'Résilié', value: 'Résilié' }
            ],
            fileUploadRef: null,
            editFileUploadRef: null,
            isClearing: false,
            isEditClearing: false,
            personnelFullName: 'Nom non disponible',
            activeTab: 0
        };
    },
    computed: {
        isEditMode() {
            return !!this.form.id;
        }
    },
    watch: {
        visible(newVisible) {
            if (newVisible) {
                this.initializeForm();
                this.fetchContracts();
                this.fetchPersonnelName();
            }
        },
        contratToEdit: {
            handler() {
                if (this.visible) {
                    this.initializeForm();
                }
            },
            deep: true
        },
        'form.type_contrat_fr'(newTypeFr) {
            if (newTypeFr) {
                const selectedType = this.typeContracts.find(opt => opt.valeur === newTypeFr);
                this.form.type_contrat_ar = selectedType ? selectedType.nom_ar : '';
            } else {
                this.form.type_contrat_ar = '';
            }
        },
        'editForm.type_contrat_fr'(newTypeFr) {
            if (newTypeFr) {
                const selectedType = this.typeContracts.find(opt => opt.valeur === newTypeFr);
                this.editForm.type_contrat_ar = selectedType ? selectedType.nom_ar : '';
            } else {
                this.editForm.type_contrat_ar = '';
            }
        },
        typeContracts(newValue) {
            this.dropdownKey += 1;
        }
    },
    methods: {
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                type_contrat_fr: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                type_contrat_ar: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                num_contrat: { value: null, matchMode: FilterMatchMode.CONTAINS },
                date_debut: { value: null, matchMode: FilterMatchMode.CONTAINS },
                date_fin: { value: null, matchMode: FilterMatchMode.CONTAINS },
                statut: { value: null, matchMode: FilterMatchMode.EQUALS }
            };
        },
        clearFilter() {
            this.initFilters();
        },
        getSeverity(statut) {
            const statusSeverity = {
                'Actif': 'success',
                'Terminé': 'info',
                'Résilié': 'danger'
            };
            return statusSeverity[statut] || 'info';
        },
        getStatutIcon(statut) {
            const statusIcons = {
                'Actif': 'pi pi-check',
                'Terminé': 'pi pi-flag',
                'Résilié': 'pi pi-times'
            };
            return statusIcons[statut] || 'pi pi-info';
        },
        async fetchContractTypes() {
            this.loadingLists = true;
            try {
                const token = localStorage.getItem('token');
                const centreId = localStorage.getItem('centreId');
                if (!token || !centreId) {
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Token ou Centre ID manquant. Veuillez vous reconnecter.',
                        life: 5000
                    });
                    return;
                }
                const headers = {
                    Authorization: `Bearer ${token}`,
                    'X-Centre-Id': centreId,
                    'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                };
                const response = await axios.get('/api/contrats-personnels/contract-types', { headers });
                if (response.data.contract_types && response.data.contract_types.length > 0) {
                    this.typeContracts = response.data.contract_types;
                } else {
                    console.warn('No contract types returned from API, using fallback data');
                    this.typeContracts = [
                        { nom_fr: 'Contrat à Durée Indéterminée (CDI)', nom_ar: 'عقد لمدة غير محدوة', valeur: 'cdi' },
                        { nom_fr: 'Contrat à Durée Déterminée (CDD)', nom_ar: 'عقد لمدة محدوة', valeur: 'cdd' },
                        { nom_fr: 'Contrat de Vacation', nom_ar: 'عقد عمل مؤقت', valeur: 'vacation' },
                        { nom_fr: 'Contrat d\'Intérim', nom_ar: 'عقد نيابة', valeur: 'interim' }
                    ];
                }
            } catch (error) {
                console.error('Error fetching contract types:', error);
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Impossible de charger les types de contrats.',
                    life: 5000
                });
            } finally {
                this.loadingLists = false;
            }
        },
        async fetchPersonnelName() {
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const centreId = localStorage.getItem('centreId');
                if (!token || !centreId) {
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Token ou Centre ID manquant. Veuillez vous reconnecter.',
                        life: 5000
                    });
                    return;
                }
                const headers = {
                    Authorization: `Bearer ${token}`,
                    'X-Centre-Id': centreId,
                    'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                };
                const response = await axios.get(`/api/personnels/${this.personnelId}/user`, { headers });
                const { nom_fr, prenom_fr } = response.data;
                this.personnelFullName = `${nom_fr || ''} ${prenom_fr || ''}`.trim() || 'Nom non disponible';
            } catch (error) {
                console.error('Error fetching personnel name:', error);
                this.personnelFullName = 'Nom non disponible';
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Impossible de charger le nom du personnel.',
                    life: 5000
                });
            } finally {
                this.loading = false;
            }
        },
        async fetchContracts() {
            this.loadingContracts = true;
            try {
                const token = localStorage.getItem('token');
                const centreId = localStorage.getItem('centreId');
                if (!token || !centreId) {
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Token ou Centre ID manquant. Veuillez vous reconnecter.',
                        life: 5000
                    });
                    return;
                }
                const headers = {
                    Authorization: `Bearer ${token}`,
                    'X-Centre-Id': centreId,
                    'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                };
                const response = await axios.get('/api/contrats-personnels', {
                    headers,
                    params: { personnel_id: this.personnelId }
                });
                this.contracts = response.data;
            } catch (error) {
                console.error('Error fetching contracts:', error);
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Impossible de charger les contrats.',
                    life: 5000
                });
            } finally {
                this.loadingContracts = false;
            }
        },
        async initializeForm() {
            this.loading = true;
            await this.fetchContractTypes();
            if (this.contratToEdit && this.contratToEdit.id) {
                this.form = {
                    id: this.contratToEdit.id,
                    personnel_id: this.personnelId,
                    type_contrat_fr: this.contratToEdit.type_contrat_fr || null,
                    type_contrat_ar: this.contratToEdit.type_contrat_ar || '',
                    num_contrat: this.contratToEdit.num_contrat || '',
                    num_decision: this.contratToEdit.num_decision || '',
                    date_decision: this.contratToEdit.date_decision ? new Date(this.contratToEdit.date_decision) : null,
                    date_debut: this.contratToEdit.date_debut ? new Date(this.contratToEdit.date_debut) : null,
                    date_fin: this.contratToEdit.date_fin ? new Date(this.contratToEdit.date_fin) : null,
                    heures_travail: this.contratToEdit.heures_travail || null,
                    date_resiliation: this.contratToEdit.date_resiliation ? new Date(this.contratToEdit.date_resiliation) : null,
                    motif_resiliation: this.contratToEdit.motif_resiliation || '',
                    document_path: this.contratToEdit.document_path || null,
                    observation: this.contratToEdit.observation || '',
                    statut: this.contratToEdit.statut || 'Actif'
                };
            } else {
                this.resetForm();
            }
            this.loading = false;
        },
        async initializeEditForm(contrat) {
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const centreId = localStorage.getItem('centreId');
                if (!token || !centreId) {
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Token ou Centre ID manquant. Veuillez vous reconnecter.',
                        life: 5000
                    });
                    return;
                }
                const headers = {
                    Authorization: `Bearer ${token}`,
                    'X-Centre-Id': centreId,
                    'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                };
                const response = await axios.get(`/api/contrats-personnels/${contrat.id}`, { headers });
                const data = response.data;
                this.editForm = {
                    id: data.id,
                    personnel_id: this.personnelId,
                    type_contrat_fr: data.type_contrat_fr || null,
                    type_contrat_ar: data.type_contrat_ar || '',
                    num_contrat: data.num_contrat || '',
                    num_decision: data.num_decision || '',
                    date_decision: data.date_decision ? new Date(data.date_decision) : null,
                    date_debut: data.date_debut ? new Date(data.date_debut) : null,
                    date_fin: data.date_fin ? new Date(data.date_fin) : null,
                    heures_travail: data.heures_travail || null,
                    date_resiliation: data.date_resiliation ? new Date(data.date_resiliation) : null,
                    motif_resiliation: data.motif_resiliation || '',
                    document_path: data.document_path || null,
                    observation: data.observation || '',
                    statut: data.statut || 'Actif'
                };
            } catch (error) {
                console.error('Erreur lors du chargement des données du contrat :', error);
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Impossible de charger les données du contrat.',
                    life: 5000
                });
            } finally {
                this.loading = false;
            }
        },
        resetForm() {
            this.form = {
                id: null,
                personnel_id: this.personnelId,
                type_contrat_fr: null,
                type_contrat_ar: '',
                num_contrat: '',
                num_decision: '',
                date_decision: null,
                date_debut: null,
                date_fin: null,
                heures_travail: null,
                date_resiliation: null,
                motif_resiliation: '',
                document_path: null,
                observation: '',
                statut: 'Actif'
            };
            this.errors = {};
            if (this.$refs.fileUploadRef && !this.isClearing) {
                this.isClearing = true;
                this.$refs.fileUploadRef.clear();
                this.isClearing = false;
            }
        },
        resetEditForm() {
            this.editForm = {
                id: null,
                personnel_id: this.personnelId,
                type_contrat_fr: null,
                type_contrat_ar: '',
                num_contrat: '',
                num_decision: '',
                date_decision: null,
                date_debut: null,
                date_fin: null,
                heures_travail: null,
                date_resiliation: null,
                motif_resiliation: '',
                document_path: null,
                observation: '',
                statut: 'Actif'
            };
            this.editErrors = {};
            if (this.$refs.editFileUploadRef && !this.isEditClearing) {
                this.isEditClearing = true;
                this.$refs.editFileUploadRef.clear();
                this.isEditClearing = false;
            }
        },
        handleFileSelect(event) {
            if (!event.files || !event.files[0]) return;
            const file = event.files[0];
            if (!['application/pdf', 'image/png', 'image/jpeg'].includes(file.type)) {
                this.errors.document_path = 'Seuls les formats PDF, PNG et JPEG sont acceptés';
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Format de fichier non supporté.',
                    life: 5000
                });
                return;
            }
            if (file.size > 5000000) {
                this.errors.document_path = 'Le fichier doit être inférieur à 5 Mo';
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'La taille du fichier dépasse 5 Mo.',
                    life: 5000
                });
                return;
            }
            const reader = new FileReader();
            reader.onload = () => {
                const base64Size = (reader.result.length * 3 / 4) / 1024 / 1024;
                if (base64Size > 5) {
                    this.errors.document_path = 'Le fichier encodé dépasse 5 Mo';
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'La taille du fichier encodé dépasse 5 Mo.',
                        life: 5000
                    });
                    return;
                }
                this.form.document_path = reader.result;
                this.errors.document_path = null;
            };
            reader.readAsDataURL(file);
        },
        handleEditFileSelect(event) {
            if (!event.files || !event.files[0]) return;
            const file = event.files[0];
            if (!['application/pdf', 'image/png', 'image/jpeg'].includes(file.type)) {
                this.editErrors.document_path = 'Seuls les formats PDF, PNG et JPEG sont acceptés';
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Format de fichier non supporté.',
                    life: 5000
                });
                return;
            }
            if (file.size > 5000000) {
                this.editErrors.document_path = 'Le fichier doit être inférieur à 5 Mo';
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'La taille du fichier dépasse 5 Mo.',
                    life: 5000
                });
                return;
            }
            const reader = new FileReader();
            reader.onload = () => {
                const base64Size = (reader.result.length * 3 / 4) / 1024 / 1024;
                if (base64Size > 5) {
                    this.editErrors.document_path = 'Le fichier encodé dépasse 5 Mo';
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'La taille du fichier encodé dépasse 5 Mo.',
                        life: 5000
                    });
                    return;
                }
                this.editForm.document_path = reader.result;
                this.editErrors.document_path = null;
            };
            reader.readAsDataURL(file);
        },
        handleClear() {
            if (!this.isClearing) {
                this.isClearing = true;
                this.form.document_path = null;
                this.errors.document_path = null;
                if (this.$refs.fileUploadRef) {
                    this.$refs.fileUploadRef.clear();
                }
                this.isClearing = false;
            }
        },
        handleEditClear() {
            if (!this.isEditClearing) {
                this.isEditClearing = true;
                this.editForm.document_path = null;
                this.editErrors.document_path = null;
                if (this.$refs.editFileUploadRef) {
                    this.$refs.editFileUploadRef.clear();
                }
                this.isEditClearing = false;
            }
        },
        async submitForm() {
            this.errors = {};
            if (!this.validateForm()) {
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Veuillez corriger les erreurs dans le formulaire.',
                    life: 5000
                });
                return;
            }
            this.submitting = true;
            try {
                const token = localStorage.getItem('token');
                const centreId = localStorage.getItem('centreId');
                if (!token || !centreId) {
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Token ou Centre ID manquant. Veuillez vous reconnecter.',
                        life: 5000
                    });
                    return;
                }
                const headers = {
                    Authorization: `Bearer ${token}`,
                    'X-Centre-Id': centreId,
                    'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                };
                const payload = {
                    ...this.form,
                    date_debut: this.form.date_debut ? this.formatDateForApi(this.form.date_debut) : null,
                    date_fin: this.form.date_fin ? this.formatDateForApi(this.form.date_fin) : null,
                    date_decision: this.form.date_decision ? this.formatDateForApi(this.form.date_decision) : null,
                    date_resiliation: this.form.date_resiliation ? this.formatDateForApi(this.form.date_resiliation) : null
                };
                const response = await axios.post('/api/contrats-personnels', payload, { headers });
                this.$toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Contrat créé avec succès.',
                    life: 3000
                });
                this.resetForm();
                this.fetchContracts();
                this.$emit('update:visible', false);
            } catch (error) {
                console.error('Erreur lors de la création du contrat :', error);
                this.handleApiError(error, 'créer');
            } finally {
                this.submitting = false;
            }
        },
        async submitEditForm() {
            this.editErrors = {};
            if (!this.validateEditForm()) {
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Veuillez corriger les erreurs dans le formulaire.',
                    life: 5000
                });
                return;
            }
            this.submitting = true;
            try {
                const token = localStorage.getItem('token');
                const centreId = localStorage.getItem('centreId');
                if (!token || !centreId) {
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Token ou Centre ID manquant. Veuillez vous reconnecter.',
                        life: 5000
                    });
                    return;
                }
                const headers = {
                    Authorization: `Bearer ${token}`,
                    'X-Centre-Id': centreId,
                    'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                };
                const payload = {
                    ...this.editForm,
                    date_debut: this.editForm.date_debut ? this.formatDateForApi(this.editForm.date_debut) : null,
                    date_fin: this.editForm.date_fin ? this.formatDateForApi(this.editForm.date_fin) : null,
                    date_decision: this.editForm.date_decision ? this.formatDateForApi(this.editForm.date_decision) : null,
                    date_resiliation: this.editForm.date_resiliation ? this.formatDateForApi(this.editForm.date_resiliation) : null
                };
                const response = await axios.patch(`/api/contrats-personnels/${this.editForm.id}`, payload, { headers });
                this.$toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Contrat modifié avec succès.',
                    life: 3000
                });
                this.closeEditDialog();
                this.fetchContracts();
            } catch (error) {
                console.error('Erreur lors de la modification du contrat :', error);
                this.handleApiError(error, 'modifier');
            } finally {
                this.submitting = false;
            }
        },
        async deleteContract(contrat) {
            this.$confirm.require({
                message: `Êtes-vous sûr de vouloir supprimer le contrat "${contrat.type_contrat_fr}" ?`,
                header: 'Confirmation de suppression',
                icon: 'pi pi-exclamation-triangle',
                acceptLabel: 'Oui, supprimer',
                rejectLabel: 'Annuler',
                accept: async () => {
                    try {
                        const token = localStorage.getItem('token');
                        const centreId = localStorage.getItem('centreId');
                        if (!token || !centreId) {
                            this.$toast.add({
                                severity: 'error',
                                summary: 'Erreur',
                                detail: 'Token ou Centre ID manquant. Veuillez vous reconnecter.',
                                life: 5000
                            });
                            return;
                        }
                        const headers = {
                            Authorization: `Bearer ${token}`,
                            'X-Centre-Id': centreId,
                            'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                        };
                        await axios.delete(`/api/contrats-personnels/${contrat.id}`, { headers });
                        this.$toast.add({
                            severity: 'success',
                            summary: 'Succès',
                            detail: 'Contrat supprimé avec succès.',
                            life: 3000
                        });
                        this.fetchContracts();
                    } catch (error) {
                        console.error('Erreur lors de la suppression du contrat :', error);
                        this.$toast.add({
                            severity: 'error',
                            summary: 'Erreur',
                            detail: error.response?.data?.message || 'Impossible de supprimer le contrat.',
                            life: 5000
                        });
                    }
                }
            });
        },
        async downloadContract(contratId) {
            try {
                const token = localStorage.getItem('token');
                const centreId = localStorage.getItem('centreId');
                if (!token || !centreId) {
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Token ou Centre ID manquant. Veuillez vous reconnecter.',
                        life: 5000
                    });
                    return;
                }
                const headers = {
                    Authorization: `Bearer ${token}`,
                    'X-Centre-Id': centreId,
                    'X-Is-Centre-Role': this.isCentreRole ? 1 : 0
                };
                const response = await axios.get(`/api/contrats-personnels/${contratId}/download`, {
                    headers,
                    responseType: 'blob'
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                const contentDisposition = response.headers['content-disposition'];
                let fileName = `contrat_${contratId}`;
                if (contentDisposition) {
                    const fileNameMatch = contentDisposition.match(/filename="(.+)"/);
                    if (fileNameMatch && fileNameMatch[1]) {
                        fileName = fileNameMatch[1];
                    }
                }
                link.setAttribute('download', fileName);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
                this.$toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Fichier téléchargé avec succès.',
                    life: 3000
                });
            } catch (error) {
                console.error('Erreur lors du téléchargement du contrat :', error);
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Impossible de télécharger le contrat.',
                    life: 5000
                });
            }
        },
        validateForm() {
            this.errors = {};
            let isValid = true;
            if (!this.form.type_contrat_fr) {
                this.errors.type_contrat_fr = 'Le type de contrat (Français) est requis.';
                isValid = false;
            }
            if (!this.form.num_contrat) {
                this.errors.num_contrat = 'Le numéro du contrat est requis.';
                isValid = false;
            }
            if (!this.form.date_debut) {
                this.errors.date_debut = 'La date de début est requise.';
                isValid = false;
            }
            if (this.form.date_fin && this.form.date_debut && new Date(this.form.date_fin) < new Date(this.form.date_debut)) {
                this.errors.date_fin = 'La date de fin doit être postérieure ou égale à la date de début.';
                isValid = false;
            }
            return isValid;
        },
        validateEditForm() {
            this.editErrors = {};
            let isValid = true;
            if (!this.editForm.type_contrat_fr) {
                this.editErrors.type_contrat_fr = 'Le type de contrat (Français) est requis.';
                isValid = false;
            }
            if (!this.editForm.num_contrat) {
                this.editErrors.num_contrat = 'Le numéro du contrat est requis.';
                isValid = false;
            }
            if (!this.editForm.date_debut) {
                this.editErrors.date_debut = 'La date de début est requise.';
                isValid = false;
            }
            if (this.editForm.date_fin && this.editForm.date_debut && new Date(this.editForm.date_fin) < new Date(this.editForm.date_debut)) {
                this.editErrors.date_fin = 'La date de fin doit être postérieure ou égale à la date de début.';
                isValid = false;
            }
            return isValid;
        },
        formatDate(date) {
            if (!date) return '';
            const d = new Date(date);
            return d.toLocaleDateString('fr-FR', { year: 'numeric', month: '2-digit', day: '2-digit' });
        },
        formatDateForApi(date) {
            if (!date) return null;
            const d = new Date(date);
            return d.toISOString().split('T')[0];
        },
        handleApiError(error, action) {
            if (error.response && error.response.status === 422) {
                this.errors = error.response.data.errors || {};
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: `Veuillez corriger les erreurs dans le formulaire pour ${action} le contrat.`,
                    life: 5000
                });
            } else {
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || `Impossible de ${action} le contrat.`,
                    life: 5000
                });
            }
        },
        openEditDialog(contrat) {
            this.initializeEditForm(contrat);
            this.editDialogVisible = true;
        },
        closeEditDialog() {
            this.editDialogVisible = false;
            this.resetEditForm();
        },
        handleVisibleUpdate(newVisible) {
            this.$emit('update:visible', newVisible);
            if (!newVisible) {
                this.resetForm();
            }
        },
        handleCancel() {
            this.$emit('update:visible', false);
            this.resetForm();
        }
    },
    mounted() {
        this.initFilters();
        if (this.visible) {
            this.initializeForm();
            this.fetchContracts();
            this.fetchPersonnelName();
        }
    }
};
</script>

<style scoped>
.arabic-text {
    direction: rtl;
    text-align: right;
}
.p-datatable-sm .p-datatable-tbody > tr > td {
    padding: 0.5rem;
}
.p-button-sm {
    padding: 0.5rem 1rem;
}
</style>
