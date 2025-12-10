<template>
    <Dialog
        v-model:visible="localVisible"
        header="Profil du Personnel"
        :modal="true"
        :style="{ width: '80vw', maxWidth: '1200px' }"
        :breakpoints="{ '960px': '90vw', '640px': '95vw' }"
        class="p-dialog surface-card border-round-lg shadow-4"
        :pt="{
            mask: { style: 'backdrop-filter: blur(4px)' },
            header: { class: 'surface-50 border-bottom-1 surface-border p-3' },
            content: { class: 'surface-ground p-4' },
            footer: { class: 'surface-50 border-top-1 surface-border p-3' }
        }"
        @update:visible="updateVisible"
    >
        <div class="surface-ground">
            <Toast position="top-right" />
            <div class="surface-card p-4 border-round shadow-2">
                <!-- Header -->
                <div class="flex flex-column md:flex-row md:align-items-center md:justify-content-between gap-3 mb-4">
                    <div class="flex align-items-center gap-3">
                        <i class="pi pi-user text-primary text-3xl"></i>
                        <div>
                            <span class="font-bold text-2xl text-900">
                                {{ formData.nom_fr && formData.prenom_fr ? `${formData.nom_fr} ${formData.prenom_fr}` : 'Profil du Personnel' }}
                            </span>
                            <div class="flex align-items-center gap-2 mt-1">
                                <Tag
                                    v-if="formData.matricule"
                                    :value="formData.matricule"
                                    icon="pi pi-tag"
                                    severity="info"
                                    rounded
                                />
                                <Tag
                                    v-if="formData.statut"
                                    :value="formData.statut"
                                    :severity="getSeverity(formData.statut)"
                                    rounded
                                />
                            </div>
                        </div>
                    </div>
                    <div class="action-buttons">
                        <Button
                            icon="pi pi-save"
                            label="Enregistrer"
                            severity="primary"
                            raised
                            :loading="saving"
                            @click="savePersonnel"
                        />
                        <Button
                            icon="pi pi-print"
                            label="Imprimer"
                            severity="secondary"
                            outlined
                            :disabled="!formData.nom_fr"
                            @click="printDocument"
                        />
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="flex flex-column align-items-center justify-content-center gap-3 p-6">
                    <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
                    <span class="text-600">Chargement des données...</span>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="p-4">
                    <Message severity="error" :closable="false">
                        <div class="flex align-items-center gap-2">
                            <i class="pi pi-exclamation-circle"></i>
                            <span>{{ error }}</span>
                        </div>
                    </Message>
                </div>

                <!-- Main Content -->
                <div v-else class="grid">
                    <!-- Photo and General Information -->
                    <div class="col-12">
                        <Card class="h-full">
                            <template #title>
                                <div class="flex align-items-center gap-2">
                                    <i class="pi pi-user text-primary"></i>
                                    <span>Informations Générales</span>
                                </div>
                            </template>
                            <template #content>
                                <div class="flex flex-column md:flex-row gap-5">
                                    <!-- Photo -->
                                    <div class="flex-shrink-0 relative">
                                        <div class="border-round-xl shadow-3 overflow-hidden" style="width: 180px; height: 180px; background: var(--surface-card);">
                                            <img
                                                v-if="formData.photo"
                                                :src="formData.photo"
                                                alt="Photo du personnel"
                                                class="w-full h-full object-contain p-3"
                                                @error="onImageError"
                                            />
                                            <div v-else class="flex align-items-center justify-content-center w-full h-full">
                                                <i class="pi pi-image text-5xl text-surface-300"></i>
                                            </div>
                                        </div>
                                        <Button
                                            icon="pi pi-camera"
                                            class="p-button-rounded p-button-sm logo-upload-button"
                                            severity="info"
                                            v-tooltip="'Changer la photo'"
                                            @click="showPhotoDialog = true"
                                        />
                                    </div>
                                    <!-- General Information -->
                                    <div class="flex-grow-1">
                                        <div class="grid">
                                            <div class="col-12 md:col-6">
                                                <div class="field">
                                                    <label class="block text-600 text-sm font-medium mb-2">Nom (FR)</label>
                                                    <InputText v-model="formData.nom_fr" class="w-full" />
                                                </div>
                                                <div class="field">
                                                    <label class="block text-600 text-sm font-medium mb-2">Prénom (FR)</label>
                                                    <InputText v-model="formData.prenom_fr" class="w-full" />
                                                </div>
                                                <div class="field">
                                                    <label class="block text-600 text-sm font-medium mb-2">Nom (AR)</label>
                                                    <InputText v-model="formData.nom_ar" class="w-full" dir="rtl" />
                                                </div>
                                                <div class="field">
                                                    <label class="block text-600 text-sm font-medium mb-2">Prénom (AR)</label>
                                                    <InputText v-model="formData.prenom_ar" class="w-full" dir="rtl" />
                                                </div>
                                                <div class="field">
                                                    <label class="block text-600 text-sm font-medium mb-2">Matricule</label>
                                                    <InputText v-model="formData.matricule" class="w-full" disabled />
                                                </div>
                                                <div class="field">
                                                    <label class="block text-600 text-sm font-medium mb-2">CIN</label>
                                                    <InputText v-model="formData.cin" class="w-full" />
                                                </div>
                                                <div class="field">
                                                    <label class="block text-600 text-sm font-medium mb-2">Date CIN</label>
                                                    <Calendar v-model="formData.date_cin" dateFormat="dd/mm/yy" class="w-full" />
                                                </div>
                                                <div class="field">
                                                    <label class="block text-600 text-sm font-medium mb-2">Date de Naissance</label>
                                                    <Calendar v-model="formData.date_naissance" dateFormat="dd/mm/yy" class="w-full" />
                                                </div>
                                            </div>
                                            <div class="col-12 md:col-6">
                                                <div class="field">
                                                    <label class="block text-600 text-sm font-medium mb-2">Nationalité (FR)</label>
                                                    <InputText v-model="formData.nationalite_fr" class="w-full" />
                                                </div>
                                                <div class="field">
                                                    <label class="block text-600 text-sm font-medium mb-2">Nationalité (AR)</label>
                                                    <InputText v-model="formData.nationalite_ar" class="w-full" dir="rtl" />
                                                </div>
                                                <div class="field">
                                                    <label class="block text-600 text-sm font-medium mb-2">Genre</label>
                                                    <Dropdown
                                                        v-model="formData.genre_fr"
                                                        :options="['Homme', 'Femme', 'Autre']"
                                                        placeholder="Sélectionner un genre"
                                                        class="w-full"
                                                    />
                                                </div>
                                                <div class="field">
                                                    <label class="block text-600 text-sm font-medium mb-2">État Civil</label>
                                                    <Dropdown
                                                        v-model="formData.etat_civil_fr"
                                                        :options="['Célibataire', 'Marié', 'Divorcé', 'Veuf']"
                                                        placeholder="Sélectionner un état civil"
                                                        class="w-full"
                                                    />
                                                </div>
                                                <div class="field">
                                                    <label class="block text-600 text-sm font-medium mb-2">Nombre d'Enfants</label>
                                                    <InputNumber v-model="formData.nombre_enfants" class="w-full" />
                                                </div>
                                                <div class="field">
                                                    <label class="block text-600 text-sm font-medium mb-2">Adresse (FR)</label>
                                                    <InputText v-model="formData.adresse_fr" class="w-full" />
                                                </div>
                                                <div class="field">
                                                    <label class="block text-600 text-sm font-medium mb-2">Téléphone 1</label>
                                                    <InputText v-model="formData.telephone_1" class="w-full" />
                                                </div>
                                                <div class="field">
                                                    <label class="block text-600 text-sm font-medium mb-2">Téléphone 2</label>
                                                    <InputText v-model="formData.telephone_2" class="w-full" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Card>
                    </div>

                    <!-- Professional Information -->
                    <div class="col-12">
                        <Card class="h-full">
                            <template #title>
                                <div class="flex align-items-center gap-2">
                                    <i class="pi pi-briefcase text-primary"></i>
                                    <span>Informations Professionnelles</span>
                                </div>
                            </template>
                            <template #content>
                                <div class="grid">
                                    <div class="col-12 md:col-6">
                                        <div class="field">
                                            <label class="block text-600 text-sm font-medium mb-2">Type d'Affectation</label>
                                            <Dropdown
                                                v-model="formData.type_affectation_fr"
                                                :options="typesAffectation.map(t => t.nom_fr)"
                                                placeholder="Sélectionner un type"
                                                class="w-full"
                                            />
                                        </div>
                                        <div class="field">
                                            <label class="block text-600 text-sm font-medium mb-2">Date de Recrutement</label>
                                            <Calendar v-model="formData.date_recrutement" dateFormat="dd/mm/yy" class="w-full" />
                                        </div>
                                        <div class="field">
                                            <label class="block text-600 text-sm font-medium mb-2">Qualité</label>
                                            <InputText v-model="formData.qualite_fr" class="w-full" />
                                        </div>
                                        <div class="field">
                                            <label class="block text-600 text-sm font-medium mb-2">Niveau d'Étude (FR)</label>
                                            <InputText v-model="formData.niveau_etude_fr" class="w-full" />
                                        </div>
                                    </div>
                                    <div class="col-12 md:col-6">
                                        <div class="field">
                                            <label class="block text-600 text-sm font-medium mb-2">Mission (FR)</label>
                                            <InputText v-model="formData.mission_fr" class="w-full" />
                                        </div>
                                        <div class="field">
                                            <label class="block text-600 text-sm font-medium mb-2">Centre</label>
                                            <Dropdown
                                                v-model="formData.centre_id"
                                                :options="centres"
                                                optionLabel="nom_fr"
                                                optionValue="id"
                                                placeholder="Sélectionner un centre"
                                                class="w-full"
                                            />
                                        </div>
                                        <div class="field">
                                            <label class="block text-600 text-sm font-medium mb-2">Rôle</label>
                                            <Dropdown
                                                v-model="formData.role_id"
                                                :options="roles"
                                                optionLabel="name"
                                                optionValue="id"
                                                placeholder="Sélectionner un rôle"
                                                class="w-full"
                                            />
                                        </div>
                                        <div class="field">
                                            <label class="block text-600 text-sm font-medium mb-2">Spécialité Diplôme (FR)</label>
                                            <InputText v-model="formData.specialite_diplome_fr" class="w-full" />
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Card>
                    </div>

                    <!-- TabView for Contracts, Documents, Regimes, and Functions -->
                    <div class="col-12">
                        <TabView>
                            <!-- Contracts Tab -->
                            <TabPanel header="Contrats">
                                <div class="mb-3">
                                    <Button label="Ajouter un Contrat" icon="pi pi-plus" severity="success" @click="showAddContractDialog = true" />
                                </div>
                                <DataTable
                                    :value="contrats"
                                    :paginator="true"
                                    :rows="10"
                                    class="p-datatable-sm"
                                    :loading="loading"
                                    responsiveLayout="scroll"
                                >
                                    <Column field="type_contrat_fr" header="Type"></Column>
                                    <Column field="num_contrat" header="Numéro"></Column>
                                    <Column field="num_decision" header="Numéro Décision"></Column>
                                    <Column field="date_debut" header="Date Début">
                                        <template #body="{ data }">
                                            {{ formatDate(data.date_debut) }}
                                        </template>
                                    </Column>
                                    <Column field="date_fin" header="Date Fin">
                                        <template #body="{ data }">
                                            {{ formatDate(data.date_fin) }}
                                        </template>
                                    </Column>
                                    <Column field="statut" header="Statut"></Column>
                                    <Column field="observation" header="Observation"></Column>
                                    <Column header="Actions">
                                        <template #body="{ data, index }">
                                            <Button
                                                icon="pi pi-pencil"
                                                class="p-button-rounded p-button-text p-button-sm"
                                                @click="editContract(data, index)"
                                            />
                                            <Button
                                                icon="pi pi-trash"
                                                class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                                @click="confirmDelete('contract', index)"
                                            />
                                        </template>
                                    </Column>
                                </DataTable>
                                <div v-if="!contrats.length && !loading" class="text-center p-3 text-600">
                                    <i class="pi pi-database mr-2"></i>
                                    Aucun contrat trouvé
                                </div>
                            </TabPanel>

                            <!-- Documents Tab -->
                            <TabPanel header="Documents Administratifs">
                                <div class="mb-3">
                                    <Button label="Ajouter un Document" icon="pi pi-plus" severity="success" @click="showAddDocumentDialog = true" />
                                </div>
                                <DataTable
                                    :value="documents"
                                    :paginator="true"
                                    :rows="10"
                                    class="p-datatable-sm"
                                    :loading="loading"
                                    responsiveLayout="scroll"
                                >
                                    <Column field="type_document_personnel_fr" header="Type"></Column>
                                    <Column field="date_depot" header="Date Dépôt">
                                        <template #body="{ data }">
                                            {{ formatDate(data.date_depot) }}
                                        </template>
                                    </Column>
                                    <Column field="validite_fr" header="Validité"></Column>
                                    <Column field="depot_fr" header="Mode Dépôt"></Column>
                                    <Column field="observation" header="Observation"></Column>
                                    <Column header="Actions">
                                        <template #body="{ data, index }">
                                            <Button
                                                icon="pi pi-pencil"
                                                class="p-button-rounded p-button-text p-button-sm"
                                                @click="editDocument(data, index)"
                                            />
                                            <Button
                                                icon="pi pi-trash"
                                                class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                                @click="confirmDelete('document', index)"
                                            />
                                            <Button
                                                v-if="data.chemin_fichier"
                                                icon="pi pi-download"
                                                class="p-button-rounded p-button-text p-button-sm"
                                                @click="downloadDocument(data.chemin_fichier)"
                                            />
                                        </template>
                                    </Column>
                                </DataTable>
                                <div v-if="!documents.length && !loading" class="text-center p-3 text-600">
                                    <i class="pi pi-database mr-2"></i>
                                    Aucun document trouvé
                                </div>
                            </TabPanel>

                            <!-- Regimes Tab -->
                            <TabPanel header="Régimes">
                                <div class="mb-3">
                                    <Button label="Ajouter un Régime" icon="pi pi-plus" severity="success" @click="showAddRegimeDialog = true" />
                                </div>
                                <DataTable
                                    :value="regimes"
                                    :paginator="true"
                                    :rows="10"
                                    class="p-datatable-sm"
                                    :loading="loading"
                                    responsiveLayout="scroll"
                                >
                                    <Column field="annee_formation_id" header="Année Formation">
                                        <template #body="{ data }">
                                            {{ getAnneeFormationNom(data.annee_formation_id) }}
                                        </template>
                                    </Column>
                                    <Column field="regime_horaire" header="Régime Horaire">
                                        <template #body="{ data }">
                                            {{ data.regime_horaire }} heures
                                        </template>
                                    </Column>
                                    <Column field="rabattement" header="Rabattement"></Column>
                                    <Column field="statut" header="Statut"></Column>
                                    <Column field="observation" header="Observation"></Column>
                                    <Column header="Actions">
                                        <template #body="{ data, index }">
                                            <Button
                                                icon="pi pi-pencil"
                                                class="p-button-rounded p-button-text p-button-sm"
                                                @click="editRegime(data, index)"
                                            />
                                            <Button
                                                icon="pi pi-trash"
                                                class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                                @click="confirmDelete('regime', index)"
                                            />
                                        </template>
                                    </Column>
                                </DataTable>
                                <div v-if="!regimes.length && !loading" class="text-center p-3 text-600">
                                    <i class="pi pi-database mr-2"></i>
                                    Aucun régime trouvé
                                </div>
                            </TabPanel>

                            <!-- Functions Tab -->
                            <TabPanel header="Fonctions">
                                <div class="mb-3">
                                    <Button label="Ajouter une Fonction" icon="pi pi-plus" severity="success" @click="showAddFunctionDialog = true" />
                                </div>
                                <DataTable
                                    :value="fonctions"
                                    :paginator="true"
                                    :rows="10"
                                    class="p-datatable-sm"
                                    :loading="loading"
                                    responsiveLayout="scroll"
                                >
                                    <Column field="fonction_fr" header="Fonction"></Column>
                                    <Column field="avantage_fonction_fr" header="Avantage"></Column>
                                    <Column field="taches_fr" header="Tâches"></Column>
                                    <Column field="date_debut" header="Date Début">
                                        <template #body="{ data }">
                                            {{ formatDate(data.date_debut) }}
                                        </template>
                                    </Column>
                                    <Column field="date_fin" header="Date Fin">
                                        <template #body="{ data }">
                                            {{ formatDate(data.date_fin) }}
                                        </template>
                                    </Column>
                                    <Column field="statut_fr" header="Statut"></Column>
                                    <Column field="observation" header="Observation"></Column>
                                    <Column header="Actions">
                                        <template #body="{ data, index }">
                                            <Button
                                                icon="pi pi-pencil"
                                                class="p-button-rounded p-button-text p-button-sm"
                                                @click="editFunction(data, index)"
                                            />
                                            <Button
                                                icon="pi pi-trash"
                                                class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                                @click="confirmDelete('function', index)"
                                            />
                                        </template>
                                    </Column>
                                </DataTable>
                                <div v-if="!fonctions.length && !loading" class="text-center p-3 text-600">
                                    <i class="pi pi-database mr-2"></i>
                                    Aucune fonction trouvée
                                </div>
                            </TabPanel>
                        </TabView>
                    </div>
                </div>
            </div>
        </div>

        <!-- Photo Upload Dialog -->
        <Dialog
            v-model:visible="showPhotoDialog"
            header="Changer la Photo"
            :modal="true"
            :style="{ width: '30rem' }"
            :breakpoints="{ '960px': '80vw', '640px': '90vw' }"
        >
            <div class="flex flex-column gap-3">
                <div class="field">
                    <label class="block text-600 text-sm font-medium mb-2">Téléverser une photo</label>
                    <FileUpload
                        mode="basic"
                        accept="image/*"
                        :maxFileSize="10000000"
                        chooseLabel="Sélectionner une image"
                        @select="onPhotoSelect"
                        auto
                    />
                </div>
                <div v-if="previewPhoto" class="flex justify-content-center">
                    <img :src="previewPhoto" alt="Aperçu de la photo" class="border-round shadow-2" style="max-width: 100%; max-height: 200px;" />
                </div>
            </div>
            <template #footer>
                <Button label="Annuler" icon="pi pi-times" severity="secondary" text @click="showPhotoDialog = false" />
                <Button label="Enregistrer" icon="pi pi-check" severity="primary" :loading="uploadingPhoto" @click="uploadPhoto" />
            </template>
        </Dialog>

        <!-- Add/Edit Contract Dialog -->
        <Dialog
            v-model:visible="showAddContractDialog"
            :header="editingContract ? 'Modifier un Contrat' : 'Ajouter un Contrat'"
            :modal="true"
            :style="{ width: '40rem' }"
            :breakpoints="{ '960px': '80vw', '640px': '90vw' }"
        >
            <div class="grid">
                <div class="col-12 md:col-6">
                    <div class="field">
                        <label class="block text-600 text-sm font-medium mb-2">Type (FR)</label>
                        <InputText v-model="newContract.type_contrat_fr" class="w-full" />
                    </div>
                    <div class="field">
                        <label class="block text-600 text-sm font-medium mb-2">Type (AR)</label>
                        <InputText v-model="newContract.type_contrat_ar" class="w-full" dir="rtl" />
                    </div>
                    <div class="field">
                        <label class="block text-600 text-sm font-medium mb-2">Numéro</label>
                        <InputText v-model="newContract.num_contrat" class="w-full" />
                    </div>
                    <div class="field">
                        <label class="block text-600 text-sm font-medium mb-2">Numéro Décision</label>
                        <InputText v-model="newContract.num_decision" class="w-full" />
                    </div>
                </div>
                <div class="col-12 md:col-6">
                    <div class="field">
                        <label class="block text-600 text-sm font-medium mb-2">Date Début</label>
                        <Calendar v-model="newContract.date_debut" dateFormat="dd/mm/yy" class="w-full" />
                    </div>
                    <div class="field">
                        <label class="block text-600 text-sm font-medium mb-2">Date Fin</label>
                        <Calendar v-model="newContract.date_fin" dateFormat="dd/mm/yy" class="w-full" />
                    </div>
                    <div class="field">
                        <label class="block text-600 text-sm font-medium mb-2">Statut</label>
                        <Dropdown
                            v-model="newContract.statut"
                            :options="['Actif', 'Terminé', 'Resilié']"
                            placeholder="Sélectionner un statut"
                            class="w-full"
                        />
                    </div>
                </div>
                <div class="col-12">
                    <div class="field">
                        <label class="block text-600 text-sm font-medium mb-2">Observation</label>
                        <Textarea v-model="newContract.observation" class="w-full" />
                    </div>
                </div>
            </div>
            <template #footer>
                <Button label="Annuler" icon="pi pi-times" severity="secondary" text @click="cancelEditContract" />
                <Button
                    :label="editingContract ? 'Mettre à jour' : 'Ajouter'"
                    icon="pi pi-check"
                    severity="primary"
                    :loading="savingContract"
                    @click="editingContract ? updateContract(editingContractIndex) : addContract"
                />
            </template>
        </Dialog>

        <!-- Add/Edit Document Dialog -->
        <Dialog
            v-model:visible="showAddDocumentDialog"
            :header="editingDocument ? 'Modifier un Document' : 'Ajouter un Document'"
            :modal="true"
            :style="{ width: '40rem' }"
            :breakpoints="{ '960px': '80vw', '640px': '90vw' }"
        >
            <DocumentPersonnelForm
                :document="newDocument"
                :personnelId="props.personnelId"
                @submit="editingDocument ? updateDocument(editingDocumentIndex) : addDocument"
                @cancel="cancelEditDocument"
            />
        </Dialog>

        <!-- Add/Edit Regime Dialog -->
        <Dialog
            v-model:visible="showAddRegimeDialog"
            :header="editingRegime ? 'Modifier un Régime' : 'Ajouter un Régime'"
            :modal="true"
            :style="{ width: '40rem' }"
            :breakpoints="{ '960px': '80vw', '640px': '90vw' }"
        >
            <div class="grid">
                <div class="col-12 md:col-6">
                    <div class="field">
                        <label class="block text-600 text-sm font-medium mb-2">Année Formation</label>
                        <Dropdown
                            v-model="newRegime.annee_formation_id"
                            :options="annees_formations"
                            optionLabel="nom_fr"
                            optionValue="id"
                            placeholder="Sélectionner une année"
                            class="w-full"
                        />
                    </div>
                    <div class="field">
                        <label class="block text-600 text-sm font-medium mb-2">Régime Horaire</label>
                        <InputNumber v-model="newRegime.regime_horaire" suffix=" heures" class="w-full" />
                    </div>
                </div>
                <div class="col-12 md:col-6">
                    <div class="field">
                        <label class="block text-600 text-sm font-medium mb-2">Rabattement</label>
                        <InputNumber v-model="newRegime.rabattement" class="w-full" />
                    </div>
                    <div class="field">
                        <label class="block text-600 text-sm font-medium mb-2">Statut</label>
                        <Dropdown
                            v-model="newRegime.statut"
                            :options="['Actif', 'Inactif']"
                            placeholder="Sélectionner un statut"
                            class="w-full"
                        />
                    </div>
                </div>
                <div class="col-12">
                    <div class="field">
                        <label class="block text-600 text-sm font-medium mb-2">Observation</label>
                        <Textarea v-model="newRegime.observation" class="w-full" />
                    </div>
                </div>
            </div>
            <template #footer>
                <Button label="Annuler" icon="pi pi-times" severity="secondary" text @click="cancelEditRegime" />
                <Button
                    :label="editingRegime ? 'Mettre à jour' : 'Ajouter'"
                    icon="pi pi-check"
                    severity="primary"
                    :loading="savingRegime"
                    @click="editingRegime ? updateRegime(editingRegimeIndex) : addRegime"
                />
            </template>
        </Dialog>

        <!-- Add/Edit Function Dialog -->
        <Dialog
            v-model:visible="showAddFunctionDialog"
            :header="editingFunction ? 'Modifier une Fonction' : 'Ajouter une Fonction'"
            :modal="true"
            :style="{ width: '40rem' }"
            :breakpoints="{ '960px': '80vw', '640px': '90vw' }"
        >
            <div class="grid">
                <div class="col-12 md:col-6">
                    <div class="field">
                        <label class="block text-600 text-sm font-medium mb-2">Fonction (FR)</label>
                        <InputText v-model="newFunction.fonction_fr" class="w-full" />
                    </div>
                    <div class="field">
                        <label class="block text-600 text-sm font-medium mb-2">Fonction (AR)</label>
                        <InputText v-model="newFunction.fonction_ar" class="w-full" dir="rtl" />
                    </div>
                    <div class="field">
                        <label class="block text-600 text-sm font-medium mb-2">Avantage (FR)</label>
                        <InputText v-model="newFunction.avantage_fonction_fr" class="w-full" />
                    </div>
                    <div class="field">
                        <label class="block text-600 text-sm font-medium mb-2">Tâches (FR)</label>
                        <Textarea v-model="newFunction.taches_fr" class="w-full" />
                    </div>
                </div>
                <div class="col-12 md:col-6">
                    <div class="field">
                        <label class="block text-600 text-sm font-medium mb-2">Date Début</label>
                        <Calendar v-model="newFunction.date_debut" dateFormat="dd/mm/yy" class="w-full" />
                    </div>
                    <div class="field">
                        <label class="block text-600 text-sm font-medium mb-2">Date Fin</label>
                        <Calendar v-model="newFunction.date_fin" dateFormat="dd/mm/yy" class="w-full" />
                    </div>
                    <div class="field">
                        <label class="block text-600 text-sm font-medium mb-2">Statut</label>
                        <Dropdown
                            v-model="newFunction.statut_fr"
                            :options="['Active', 'Inactive']"
                            placeholder="Sélectionner un statut"
                            class="w-full"
                        />
                    </div>
                </div>
                <div class="col-12">
                    <div class="field">
                        <label class="block text-600 text-sm font-medium mb-2">Observation</label>
                        <Textarea v-model="newFunction.observation" class="w-full" />
                    </div>
                </div>
            </div>
            <template #footer>
                <Button label="Annuler" icon="pi pi-times" severity="secondary" text @click="cancelEditFunction" />
                <Button
                    :label="editingFunction ? 'Mettre à jour' : 'Ajouter'"
                    icon="pi pi-check"
                    severity="primary"
                    :loading="savingFunction"
                    @click="editingFunction ? updateFunction(editingFunctionIndex) : addFunction"
                />
            </template>
        </Dialog>

        <!-- Confirmation Dialog for Deletion -->
        <Dialog
            v-model:visible="showConfirmDialog"
            header="Confirmer la suppression"
            :modal="true"
            :style="{ width: '30rem' }"
        >
            <div class="flex align-items-center gap-2">
                <i class="pi pi-exclamation-triangle text-warning text-2xl"></i>
                <span>Êtes-vous sûr de vouloir supprimer cet élément ?</span>
            </div>
            <template #footer>
                <Button label="Annuler" icon="pi pi-times" severity="secondary" text @click="showConfirmDialog = false" />
                <Button label="Supprimer" icon="pi pi-trash" severity="danger" @click="executeDelete" />
            </template>
        </Dialog>
    </Dialog>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import DocumentPersonnelForm from '@/Components/Centres/Utilisateurs/Personnels/DocumentPersonnelForm.vue';


const props = defineProps({
    visible: Boolean,
    personnelId: [Number, String]
});

const emit = defineEmits(['update:visible']);

const toast = useToast();
const loading = ref(false);
const error = ref(null);
const saving = ref(false);
const uploadingPhoto = ref(false);
const savingContract = ref(false);
const savingDocument = ref(false);
const savingRegime = ref(false);
const savingFunction = ref(false);
const showPhotoDialog = ref(false);
const showAddContractDialog = ref(false);
const showAddDocumentDialog = ref(false);
const showAddRegimeDialog = ref(false);
const showAddFunctionDialog = ref(false);
const showConfirmDialog = ref(false);
const previewPhoto = ref(null);
const editingContract = ref(null);
const editingContractIndex = ref(null);
const editingDocument = ref(null);
const editingDocumentIndex = ref(null);
const editingRegime = ref(null);
const editingRegimeIndex = ref(null);
const editingFunction = ref(null);
const editingFunctionIndex = ref(null);
const deleteType = ref(null);
const deleteIndex = ref(null);

const formData = ref({
    nom_fr: '',
    prenom_fr: '',
    nom_ar: '',
    prenom_ar: '',
    matricule: '',
    cin: '',
    date_cin: null,
    date_naissance: null,
    nationalite_fr: '',
    nationalite_ar: '',
    genre_fr: '',
    genre_ar: '',
    statut: '',
    qualite_fr: '',
    qualite_ar: '',
    etablissement_origine_fr: '',
    etablissement_origine_ar: '',
    mission_fr: '',
    mission_ar: '',
    observation_fr: '',
    photo: null,
    email: '',
    centre_id: null,
    role_id: null,
    type_affectation_fr: '',
    type_affectation_ar: '',
    centre_origine_fr: '',
    centre_origine_ar: '',
    date_debut: null,
    date_fin: null,
    etat_civil_fr: '',
    etat_civil_ar: '',
    nombre_enfants: null,
    niveau_etude_fr: '',
    niveau_etude_ar: '',
    specialite_diplome_fr: '',
    specialite_diplome_ar: '',
    code_niveau: '',
    date_recrutement: null,
    date_fin_service: null,
    adresse_fr: '',
    adresse_ar: '',
    gouvernorat_fr: '',
    gouvernorat_ar: '',
    delegation_fr: '',
    delegation_ar: '',
    telephone_1: '',
    telephone_2: '',
});

const contrats = ref([]);
const documents = ref([]);
const regimes = ref([]);
const fonctions = ref([]);
const centres = ref([]);
const roles = ref([]);
const annees_formations = ref([]);
const typesAffectation = ref([]);

const newContract = ref({
    type_contrat_fr: '',
    type_contrat_ar: '',
    num_contrat: '',
    num_decision: '',
    date_debut: null,
    date_fin: null,
    statut: '',
    observation: ''
});

const newDocument = ref({
    type_document_personnel_fr: '',
    type_document_personnel_ar: '',
    date_depot: null,
    validite_fr: '',
    validite_ar: '',
    depot_fr: '',
    depot_ar: '',
    observation: '',
    chemin_fichier: null
});

const newRegime = ref({
    annee_formation_id: null,
    regime_horaire: null,
    rabattement: null,
    statut: '',
    observation: ''
});

const newFunction = ref({
    fonction_fr: '',
    fonction_ar: '',
    avantage_fonction_fr: '',
    avantage_fonction_ar: '',
    taches_fr: '',
    taches_ar: '',
    date_debut: null,
    date_fin: null,
    statut_fr: '',
    statut_ar: '',
    observation: ''
});

const localVisible = computed({
    get: () => props.visible,
    set: (value) => emit('update:visible', value)
});

const getSeverity = (statut) => {
    switch (statut) {
        case 'Actif': return 'success';
        case 'Inactif': return 'warning';
        case 'Suspendu': return 'danger';
        default: return 'info';
    }
};

const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return isNaN(d.getTime()) ? '' : d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const getAnneeFormationNom = (id) => {
    const annee = annees_formations.value.find(a => a.id === id);
    return annee ? annee.nom_fr : '';
};

const fetchReferenceData = async () => {
    try {
        const headers = {};
        if (localStorage.getItem('isCentreRole') === '1') {
            headers['X-Is-Centre-Role'] = '1';
            headers['X-Centre-Id'] = localStorage.getItem('centreId');
        }
        const response = await axios.get('/api/profil-personnel/reference-data', { headers });
        centres.value = response.data.data.centres;
        roles.value = response.data.data.roles;
        annees_formations.value = response.data.data.annees_formations;
        typesAffectation.value = response.data.data.types_affectation;
    } catch (err) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Impossible de charger les données de référence.',
            life: 3000
        });
    }
};

const fetchPersonnelData = async () => {
    if (!props.personnelId) return;
    loading.value = true;
    error.value = null;
    try {
        const headers = {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
        };
        if (localStorage.getItem('isCentreRole') === '1') {
            headers['X-Is-Centre-Role'] = '1';
            headers['X-Centre-Id'] = localStorage.getItem('centreId');
        }
        const response = await axios.get(`/api/profil-personnel/${props.personnelId}`, { headers });
        Object.assign(formData.value, response.data.data);
        contrats.value = response.data.data.contrats_personnels || [];
        documents.value = response.data.data.documents_personnels || [];
        regimes.value = response.data.data.regimes_personnels || [];
        fonctions.value = response.data.data.fonctions_personnels || [];
    } catch (err) {
        error.value = err.response?.data?.message || 'Erreur lors du chargement des données du personnel.';
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: error.value,
            life: 3000
        });
    } finally {
        loading.value = false;
    }
};

const savePersonnel = async () => {
    saving.value = true;
    try {
        const headers = {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        };
        if (localStorage.getItem('isCentreRole') === '1') {
            headers['X-Is-Centre-Role'] = '1';
            headers['X-Centre-Id'] = localStorage.getItem('centreId');
        }
        const response = await axios.put(`/api/profil-personnel/${props.personnelId}`, formData.value, { headers });
        Object.assign(formData.value, response.data.data);
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Informations du personnel mises à jour avec succès.',
            life: 3000
        });
    } catch (err) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: err.response?.data?.message || 'Erreur lors de la mise à jour du personnel.',
            life: 3000
        });
    } finally {
        saving.value = false;
    }
};

const uploadPhoto = async () => {
    if (!previewPhoto.value) {
        toast.add({
            severity: 'warn',
            summary: 'Avertissement',
            detail: 'Veuillez sélectionner une photo.',
            life: 3000
        });
        return;
    }
    uploadingPhoto.value = true;
    try {
        const headers = {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'multipart/form-data'
        };
        if (localStorage.getItem('isCentreRole') === '1') {
            headers['X-Is-Centre-Role'] = '1';
            headers['X-Centre-Id'] = localStorage.getItem('centreId');
        }
        const formDataPhoto = new FormData();
        formDataPhoto.append('photo', await fetch(previewPhoto.value).then(res => res.blob()));
        const response = await axios.post(`/api/profil-personnel/${props.personnelId}/photo`, formDataPhoto, { headers });
        formData.value.photo = response.data.photo;
        showPhotoDialog.value = false;
        previewPhoto.value = null;
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Photo mise à jour avec succès.',
            life: 3000
        });
    } catch (err) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: err.response?.data?.message || 'Erreur lors du téléversement de la photo.',
            life: 3000
        });
    } finally {
        uploadingPhoto.value = false;
    }
};

const onPhotoSelect = (event) => {
    const file = event.files[0];
    if (!file) return;
    if (!['image/jpeg', 'image/png', 'image/jpg', 'image/gif'].includes(file.type)) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Type de fichier non pris en charge. Veuillez sélectionner une image (jpeg, png, jpg, gif).',
            life: 3000
        });
        return;
    }
    if (file.size > 10000000) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'La taille du fichier dépasse la limite de 10 Mo.',
            life: 3000
        });
        return;
    }
    const reader = new FileReader();
    reader.onload = (e) => {
        previewPhoto.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const confirmDelete = (type, index) => {
    deleteType.value = type;
    deleteIndex.value = index;
    showConfirmDialog.value = true;
};

const executeDelete = async () => {
    const index = deleteIndex.value;
    const type = deleteType.value;
    try {
        const headers = {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        };
        if (localStorage.getItem('isCentreRole') === '1') {
            headers['X-Is-Centre-Role'] = '1';
            headers['X-Centre-Id'] = localStorage.getItem('centreId');
        }
        if (type === 'contract') {
            await deleteContract(index, headers);
        } else if (type === 'document') {
            await deleteDocument(index, headers);
        } else if (type === 'regime') {
            await deleteRegime(index, headers);
        } else if (type === 'function') {
            await deleteFunction(index, headers);
        }
        showConfirmDialog.value = false;
    } catch (err) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: err.response?.data?.message || `Erreur lors de la suppression de ${type}.`,
            life: 3000
        });
    }
};

const editContract = (contract, index) => {
    editingContract.value = true;
    editingContractIndex.value = index;
    Object.assign(newContract.value, { ...contract });
    showAddContractDialog.value = true;
};

const cancelEditContract = () => {
    editingContract.value = null;
    editingContractIndex.value = null;
    newContract.value = {
        type_contrat_fr: '',
        type_contrat_ar: '',
        num_contrat: '',
        num_decision: '',
        date_debut: null,
        date_fin: null,
        statut: '',
        observation: ''
    };
    showAddContractDialog.value = false;
};

const addContract = async () => {
    savingContract.value = true;
    try {
        const headers = {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        };
        if (localStorage.getItem('isCentreRole') === '1') {
            headers['X-Is-Centre-Role'] = '1';
            headers['X-Centre-Id'] = localStorage.getItem('centreId');
        }
        const response = await axios.post(`/api/profil-personnel/${props.personnelId}/contrats`, newContract.value, { headers });
        contrats.value.push(response.data.data);
        cancelEditContract();
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Contrat ajouté avec succès.',
            life: 3000
        });
    } catch (err) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: err.response?.data?.message || 'Erreur lors de l\'ajout du contrat.',
            life: 3000
        });
    } finally {
        savingContract.value = false;
    }
};

const updateContract = async (index) => {
    const contract = contrats.value[index];
    savingContract.value = true;
    try {
        const headers = {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        };
        if (localStorage.getItem('isCentreRole') === '1') {
            headers['X-Is-Centre-Role'] = '1';
            headers['X-Centre-Id'] = localStorage.getItem('centreId');
        }
        const response = await axios.put(`/api/profil-personnel/${props.personnelId}/contrats/${contract.id}`, newContract.value, { headers });
        contrats.value[index] = response.data.data;
        cancelEditContract();
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Contrat mis à jour avec succès.',
            life: 3000
        });
    } catch (err) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: err.response?.data?.message || 'Erreur lors de la mise à jour du contrat.',
            life: 3000
        });
    } finally {
        savingContract.value = false;
    }
};

const deleteContract = async (index, headers) => {
    const contract = contrats.value[index];
    await axios.delete(`/api/profil-personnel/${props.personnelId}/contrats/${contract.id}`, { headers });
    contrats.value.splice(index, 1);
    toast.add({
        severity: 'success',
        summary: 'Succès',
        detail: 'Contrat supprimé avec succès.',
        life: 3000
    });
};

const editDocument = (document, index) => {
    editingDocument.value = true;
    editingDocumentIndex.value = index;
    Object.assign(newDocument.value, { ...document });
    showAddDocumentDialog.value = true;
};

const cancelEditDocument = () => {
    editingDocument.value = null;
    editingDocumentIndex.value = null;
    newDocument.value = {
        type_document_personnel_fr: '',
        type_document_personnel_ar: '',
        date_depot: null,
        validite_fr: '',
        validite_ar: '',
        depot_fr: '',
        depot_ar: '',
        observation: '',
        chemin_fichier: null
    };
    showAddDocumentDialog.value = false;
};

const addDocument = async () => {
    savingDocument.value = true;
    try {
        const headers = {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        };
        if (localStorage.getItem('isCentreRole') === '1') {
            headers['X-Is-Centre-Role'] = '1';
            headers['X-Centre-Id'] = localStorage.getItem('centreId');
        }
        const response = await axios.post(`/api/profil-personnel/${props.personnelId}/documents`, newDocument.value, { headers });
        documents.value.push(response.data.data);
        cancelEditDocument();
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Document ajouté avec succès.',
            life: 3000
        });
    } catch (err) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: err.response?.data?.message || 'Erreur lors de l\'ajout du document.',
            life: 3000
        });
    } finally {
        savingDocument.value = false;
    }
};

const updateDocument = async (index) => {
    const document = documents.value[index];
    savingDocument.value = true;
    try {
        const headers = {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        };
        if (localStorage.getItem('isCentreRole') === '1') {
            headers['X-Is-Centre-Role'] = '1';
            headers['X-Centre-Id'] = localStorage.getItem('centreId');
        }
        const response = await axios.put(`/api/profil-personnel/${props.personnelId}/documents/${document.id}`, newDocument.value, { headers });
        documents.value[index] = response.data.data;
        cancelEditDocument();
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Document mis à jour avec succès.',
            life: 3000
        });
    } catch (err) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: err.response?.data?.message || 'Erreur lors de la mise à jour du document.',
            life: 3000
        });
    } finally {
        savingDocument.value = false;
    }
};

const deleteDocument = async (index, headers) => {
    const document = documents.value[index];
    await axios.delete(`/api/profil-personnel/${props.personnelId}/documents/${document.id}`, { headers });
    documents.value.splice(index, 1);
    toast.add({
        severity: 'success',
        summary: 'Succès',
        detail: 'Document supprimé avec succès.',
        life: 3000
    });
};

const downloadDocument = async (cheminFichier) => {
    try {
        const headers = {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        };
        if (localStorage.getItem('isCentreRole') === '1') {
            headers['X-Is-Centre-Role'] = '1';
            headers['X-Centre-Id'] = localStorage.getItem('centreId');
        }
        const response = await axios.get(`/api/profil-personnel/download-document/${cheminFichier}`, {
            headers,
            responseType: 'blob'
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', cheminFichier.split('/').pop());
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Document téléchargé avec succès.',
            life: 3000
        });
    } catch (err) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: err.response?.data?.message || 'Erreur lors du téléchargement du document.',
            life: 3000
        });
    }
};

const editRegime = (regime, index) => {
    editingRegime.value = true;
    editingRegimeIndex.value = index;
    Object.assign(newRegime.value, { ...regime });
    showAddRegimeDialog.value = true;
};

const cancelEditRegime = () => {
    editingRegime.value = null;
    editingRegimeIndex.value = null;
    newRegime.value = {
        annee_formation_id: null,
        regime_horaire: null,
        rabattement: null,
        statut: '',
        observation: ''
    };
    showAddRegimeDialog.value = false;
};

const addRegime = async () => {
    savingRegime.value = true;
    try {
        const headers = {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        };
        if (localStorage.getItem('isCentreRole') === '1') {
            headers['X-Is-Centre-Role'] = '1';
            headers['X-Centre-Id'] = localStorage.getItem('centreId');
        }
        const response = await axios.post(`/api/profil-personnel/${props.personnelId}/regimes`, newRegime.value, { headers });
        regimes.value.push(response.data.data);
        cancelEditRegime();
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Régime ajouté avec succès.',
            life: 3000
        });
    } catch (err) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: err.response?.data?.message || 'Erreur lors de l\'ajout du régime.',
            life: 3000
        });
    } finally {
        savingRegime.value = false;
    }
};

const updateRegime = async (index) => {
    const regime = regimes.value[index];
    savingRegime.value = true;
    try {
        const headers = {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        };
        if (localStorage.getItem('isCentreRole') === '1') {
            headers['X-Is-Centre-Role'] = '1';
            headers['X-Centre-Id'] = localStorage.getItem('centreId');
        }
        const response = await axios.put(`/api/profil-personnel/${props.personnelId}/regimes/${regime.id}`, newRegime.value, { headers });
        regimes.value[index] = response.data.data;
        cancelEditRegime();
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Régime mis à jour avec succès.',
            life: 3000
        });
    } catch (err) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: err.response?.data?.message || 'Erreur lors de la mise à jour du régime.',
            life: 3000
        });
    } finally {
        savingRegime.value = false;
    }
};

const deleteRegime = async (index, headers) => {
    const regime = regimes.value[index];
    await axios.delete(`/api/profil-personnel/${props.personnelId}/regimes/${regime.id}`, { headers });
    regimes.value.splice(index, 1);
    toast.add({
        severity: 'success',
        summary: 'Succès',
        detail: 'Régime supprimé avec succès.',
        life: 3000
    });
};

const editFunction = (fonction, index) => {
    editingFunction.value = true;
    editingFunctionIndex.value = index;
    Object.assign(newFunction.value, { ...fonction });
    showAddFunctionDialog.value = true;
};

const cancelEditFunction = () => {
    editingFunction.value = null;
    editingFunctionIndex.value = null;
    newFunction.value = {
        fonction_fr: '',
        fonction_ar: '',
        avantage_fonction_fr: '',
        avantage_fonction_ar: '',
        taches_fr: '',
        taches_ar: '',
        date_debut: null,
        date_fin: null,
        statut_fr: '',
        statut_ar: '',
        observation: ''
    };
    showAddFunctionDialog.value = false;
};

const addFunction = async () => {
    savingFunction.value = true;
    try {
        const headers = {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        };
        if (localStorage.getItem('isCentreRole') === '1') {
            headers['X-Is-Centre-Role'] = '1';
            headers['X-Centre-Id'] = localStorage.getItem('centreId');
        }
        const response = await axios.post(`/api/profil-personnel/${props.personnelId}/fonctions`, newFunction.value, { headers });
        fonctions.value.push(response.data.data);
        cancelEditFunction();
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Fonction ajoutée avec succès.',
            life: 3000
        });
    } catch (err) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: err.response?.data?.message || 'Erreur lors de l\'ajout de la fonction.',
            life: 3000
        });
    } finally {
        savingFunction.value = false;
    }
};

const updateFunction = async (index) => {
    const fonction = fonctions.value[index];
    savingFunction.value = true;
    try {
        const headers = {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        };
        if (localStorage.getItem('isCentreRole') === '1') {
            headers['X-Is-Centre-Role'] = '1';
            headers['X-Centre-Id'] = localStorage.getItem('centreId');
        }
        const response = await axios.put(`/api/profil-personnel/${props.personnelId}/fonctions/${fonction.id}`, newFunction.value, { headers });
        fonctions.value[index] = response.data.data;
        cancelEditFunction();
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Fonction mise à jour avec succès.',
            life: 3000
        });
    } catch (err) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: err.response?.data?.message || 'Erreur lors de la mise à jour de la fonction.',
            life: 3000
        });
    } finally {
        savingFunction.value = false;
    }
};

const deleteFunction = async (index, headers) => {
    const fonction = fonctions.value[index];
    await axios.delete(`/api/profil-personnel/${props.personnelId}/fonctions/${fonction.id}`, { headers });
    fonctions.value.splice(index, 1);
    toast.add({
        severity: 'success',
        summary: 'Succès',
        detail: 'Fonction supprimée avec succès.',
        life: 3000
    });
};

const onImageError = () => {
    formData.value.photo = null;
};

const printDocument = () => {
    window.print();
};

const updateVisible = (value) => {
    if (!value) {
        formData.value = {
            nom_fr: '',
            prenom_fr: '',
            nom_ar: '',
            prenom_ar: '',
            matricule: '',
            cin: '',
            date_cin: null,
            date_naissance: null,
            nationalite_fr: '',
            nationalite_ar: '',
            genre_fr: '',
            genre_ar: '',
            statut: '',
            qualite_fr: '',
            qualite_ar: '',
            etablissement_origine_fr: '',
            etablissement_origine_ar: '',
            mission_fr: '',
            mission_ar: '',
            observation_fr: '',
            photo: null,
            email: '',
            centre_id: null,
            role_id: null,
            type_affectation_fr: '',
            type_affectation_ar: '',
            centre_origine_fr: '',
            centre_origine_ar: '',
            date_debut: null,
            date_fin: null,
            etat_civil_fr: '',
            etat_civil_ar: '',
            nombre_enfants: null,
            niveau_etude_fr: '',
            niveau_etude_ar: '',
            specialite_diplome_fr: '',
            specialite_diplome_ar: '',
            code_niveau: '',
            date_recrutement: null,
            date_fin_service: null,
            adresse_fr: '',
            adresse_ar: '',
            gouvernorat_fr: '',
            gouvernorat_ar: '',
            delegation_fr: '',
            delegation_ar: '',
            telephone_1: '',
            telephone_2: '',
        };
        contrats.value = [];
        documents.value = [];
        regimes.value = [];
        fonctions.value = [];
        error.value = null;
    }
    emit('update:visible', value);
};

onMounted(() => {
    fetchReferenceData();
    if (props.personnelId) {
        fetchPersonnelData();
    }
});
</script>

<style scoped>
.logo-upload-button {
    position: absolute;
    bottom: 10px;
    right: 10px;
}
.action-buttons {
    display: flex;
    gap: 10px;
}
@media print {
    .action-buttons,
    .p-button,
    .p-dialog-footer {
        display: none !important;
    }
    .p-dialog {
        box-shadow: none !important;
        border: none !important;
        width: 100% !important;
        max-width: none !important;
    }
    .p-card {
        box-shadow: none !important;
        border: 1px solid #e0e0e0 !important;
    }
}
</style>
