<template>
    <div class="surface-ground pt-2 px-1.5 pb-1.5" style="margin-top: 0">
        <!-- ProgressSpinner for importing -->
        <div v-if="isImporting" class="progress-overlay">
            <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="5" />
            <span class="progress-text">Importation en cours...</span>
        </div>
        <!-- Header section with navigation bar -->
        <div class="surface-card p-2 border-round border-1 surface-border" style="position: relative; top: -37px; box-shadow: none; margin-bottom: -33px;">
            <div class="flex justify-content-between align-items-center">
                <div class="flex gap-3">
                    <Button label="Accueil" icon="pi pi-home" class="p-button-text p-button-plain p-button-sm" @click="$router.push({ name: 'dashboard' })" />
                    <Button label="Personnels & Centres" icon="pi pi-users" class="p-button-text p-button-plain p-button-sm" />
                </div>
                <div class="flex gap-2">
                    <Button icon="pi pi-refresh" class="p-button-rounded p-button-text p-button-sm" @click="fetchPersonnels" v-tooltip="'Rafraîchir'" />
                    <div class="flex align-items-center gap-2">
                        <Button icon="pi pi-file-import" class="p-button-rounded p-button-text p-button-sm import-icon" @click="openImportDialog" v-tooltip="'Importer XLS'" />
                        <input type="file" ref="fileInput" style="display: none" accept=".xls,.xlsx" @change="onImport" />
                    </div>
                    <Button icon="pi pi-file-export" class="p-button-rounded p-button-text p-button-sm export-icon" @click="exportPersonnels" v-tooltip="'Exporter XLS'" />
                </div>
            </div>
        </div>
        <!-- Main content section -->
        <div class="surface-card p-4 pt-2 border-round shadow-2 border-1 surface-border">
            <!-- Table header with actions -->
            <div class="flex justify-content-between align-items-center mb-4">
                <div class="flex align-items-center gap-2">
                    <span class="p-input-icon-left search-field">
                        <InputText v-model="filters['global'].value" placeholder="Rechercher..." @input="fetchPersonnels" />
                    </span>
                    <Button icon="pi pi-filter-slash" class="p-button-outlined p-button-secondary" size="small" @click="clearFilter" v-tooltip="'Réinitialiser les filtres'" />
                    <Dropdown
                        v-model="selectedAnneeFormation"
                        :options="anneesFormations"
                        optionLabel="intitule"
                        optionValue="id"
                        placeholder="Sélectionner une année de formation"
                        class="p-column-filter"
                        @change="fetchPersonnels"
                        :showClear="true"
                        :loading="loadingAnnees"
                        :disabled="loadingAnnees || anneesFormations.length === 0"
                    />
                </div>
                <div class="flex gap-2">
                    <Button icon="pi pi-plus" label="" class="p-button-success p-button-sm p-button-rounded" @click="openForm" v-tooltip="'Ajouter Personnel'" />
                </div>
            </div>
            <!-- Main data table -->
            <DataTable
                v-model:filters="filters"
                v-model:selection="selectedPersonnels"
                :value="personnels"
                dataKey="id"
                size="small"
                stripedRows
                paginator
                :rows="20"
                :rowsPerPageOptions="[20, 50, 100]"
                :totalRecords="totalRecords"
                :lazy="true"
                filterDisplay="menu"
                :globalFilterFields="[
                    'nom_fr', 'nom_ar', 'prenom_fr', 'prenom_ar', 'matricule', 'cin', 'num_extrait_naissance',
                    'email', 'centre_nom_fr', 'centre_nom_ar', 'role_nom', 'type_affectation_fr', 'type_affectation_ar',
                    'etablissement_origine_fr', 'etablissement_origine_ar', 'mission_fr', 'mission_ar',
                    'centre_origine_fr', 'centre_origine_ar', 'adresse_fr', 'adresse_ar', 'gouvernorat_fr',
                    'gouvernorat_ar', 'delegation_fr', 'delegation_ar', 'telephone_1', 'telephone_2',
                    'etat_civil_fr', 'etat_civil_ar', 'niveau_etude_fr', 'niveau_etude_ar', 'specialite_diplome_fr',
                    'specialite_diplome_ar', 'code_niveau', 'qualite_fr', 'qualite_ar', 'statut', 'observation',
                    'observation_fr', 'observation_personnels', 'regime_horaire', 'rabattement'
                ]"
                :loading="loading"
                scrollable
                scrollHeight="700px"
                removableSort
                class="p-datatable-sm border-round-lg"
                :pt="{ table: { style: 'width: auto; table-layout: auto;' } }"
                @page="onPage"
                @sort="onSort"
                @filter="onFilter"
            >
                <template #empty>
                    <div class="text-center py-4">
                        <i class="pi pi-inbox text-4xl text-400 mb-2" />
                        <p class="text-600">Aucun personnel trouvé.</p>
                    </div>
                </template>
                <Column selectionMode="multiple" headerStyle="width: 3rem" frozen />
                <Column field="photo" header="Photo">
                    <template #body="{ data }">
                        <img
                            :src="data.photo && data.photo !== '' ? data.photo : '/images/default-avatar.png'"
                            alt="Photo du personnel"
                            class="logo-image"
                            @error="onImageError($event, data.photo)"
                        />
                    </template>
                </Column>
                <Column field="matricule" header="Matricule" sortable frozen>
                    <template #body="{ data }">
                        <span class="flex align-items-center gap-2 single-line">
                            <i class="pi pi-circle-fill" :class="data.statut === 'Actif' ? 'text-green-500' : 'text-red-500'" style="font-size: 0.5rem" />
                            <span class="font-medium">{{ data.matricule || 'Non défini' }}</span>
                        </span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par matricule" @input="filterCallback()" />
                    </template>
                </Column>
                <Column field="cin" header="CIN" sortable>
                    <template #body="{ data }">
                        <span class="single-line">{{ data.cin || 'Non défini' }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par CIN" @input="filterCallback()" />
                    </template>
                </Column>
                <Column field="num_extrait_naissance" header="N° Extrait Naissance" sortable>
                    <template #body="{ data }">
                        <span class="single-line">{{ data.num_extrait_naissance || 'Non défini' }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par n° extrait" @input="filterCallback()" />
                    </template>
                </Column>
                <Column field="nom_fr" header="Nom" sortable>
                    <template #body="{ data }">
                        <div class="flex flex-column">
                            <span class="font-medium single-line">{{ data.nom_fr }} {{ data.prenom_fr }}</span>
                            <span class="arabic-text text-blue-600 single-line">{{ data.nom_ar }} {{ data.prenom_ar }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par nom (FR)" @input="filterCallback()" />
                    </template>
                </Column>
                <Column field="email" header="Email" sortable>
                    <template #body="{ data }">
                        <span class="single-line">{{ data.email || 'Non défini' }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par email" @input="filterCallback()" />
                    </template>
                </Column>
                <Column field="centre_nom_fr" header="Centre" sortable>
                    <template #body="{ data }">
                        <div class="flex flex-column">
                            <span class="single-line">{{ data.centre_nom_fr || 'Non défini' }}</span>
                            <span class="arabic-text text-blue-600 single-line">{{ data.centre_nom_ar || 'غير محدد' }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par centre" @input="filterCallback()" />
                    </template>
                </Column>
                <Column field="role_nom" header="Rôle" sortable>
                    <template #body="{ data }">
                        <Tag :value="data.role_nom || 'Non défini'" :severity="data.role_nom && data.role_nom !== 'Non défini' ? 'warning' : 'secondary'" :icon="data.role_nom && data.role_nom !== 'Non défini' ? 'pi pi-user' : 'pi pi-info'" class="single-line" />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="roles"
                            optionLabel="name"
                            optionValue="name"
                            placeholder="Sélectionner un rôle"
                            class="p-column-filter"
                            @change="filterCallback()"
                            :showClear="true"
                            :loading="loadingRoles"
                            :disabled="loadingRoles || roles.length === 0"
                        />
                    </template>
                </Column>
                <Column field="type_affectation_fr" header="Type d'affectation" sortable>
                    <template #body="{ data }">
                        <div class="flex flex-column">
                            <Tag :value="data.type_affectation_fr || 'Non défini'" :severity="data.type_affectation_fr ? 'info' : 'secondary'" :icon="data.type_affectation_fr ? 'pi pi-tag' : 'pi pi-info'" class="single-line" />
                            <span class="arabic-text text-blue-600 single-line">{{ data.type_affectation_ar || 'غير محدد' }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par type d'affectation" @input="filterCallback()" />
                    </template>
                </Column>
                <Column field="etablissement_origine_fr" header="Établissement d'Origine" sortable>
                    <template #body="{ data }">
                        <div class="flex flex-column">
                            <span class="single-line">{{ data.etablissement_origine_fr || 'Non défini' }}</span>
                            <span class="arabic-text text-blue-600 single-line">{{ data.etablissement_origine_ar || 'غير محدد' }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par établissement" @input="filterCallback()" />
                    </template>
                </Column>
                <Column field="mission_fr" header="Mission" sortable>
                    <template #body="{ data }">
                        <div class="flex flex-column">
                            <span class="single-line">{{ data.mission_fr || 'Non défini' }}</span>
                            <span class="arabic-text text-blue-600 single-line">{{ data.mission_ar || 'غير محدد' }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par mission" @input="filterCallback()" />
                    </template>
                </Column>
                <Column field="centre_origine_fr" header="Centre d'Origine" sortable>
                    <template #body="{ data }">
                        <div class="flex flex-column">
                            <span class="single-line">{{ data.centre_origine_fr || 'Non défini' }}</span>
                            <span class="arabic-text text-blue-600 single-line">{{ data.centre_origine_ar || 'غير محدد' }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par centre d'origine" @input="filterCallback()" />
                    </template>
                </Column>
                <Column field="adresse_fr" header="Adresse" sortable>
                    <template #body="{ data }">
                        <div class="flex flex-column">
                            <span class="single-line">{{ data.adresse_fr || 'Non défini' }}</span>
                            <span class="arabic-text text-blue-600 single-line">{{ data.adresse_ar || 'غير محدد' }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par adresse" @input="filterCallback()" />
                    </template>
                </Column>
                <Column field="gouvernorat_fr" header="Gouvernorat" sortable>
                    <template #body="{ data }">
                        <div class="flex flex-column">
                            <span class="single-line">{{ data.gouvernorat_fr || 'Non défini' }}</span>
                            <span class="arabic-text text-blue-600 single-line">{{ data.gouvernorat_ar || 'غير محدد' }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par gouvernorat" @input="filterCallback()" />
                    </template>
                </Column>
                <Column field="delegation_fr" header="Délégation" sortable>
                    <template #body="{ data }">
                        <div class="flex flex-column">
                            <span class="single-line">{{ data.delegation_fr || 'Non défini' }}</span>
                            <span class="arabic-text text-blue-600 single-line">{{ data.delegation_ar || 'غير محدد' }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par délégation" @input="filterCallback()" />
                    </template>
                </Column>
                <Column field="telephone_1" header="Téléphone 1" sortable>
                    <template #body="{ data }">
                        <span class="single-line">{{ data.telephone_1 || 'Non défini' }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par téléphone 1" @input="filterCallback()" />
                    </template>
                </Column>
                <Column field="telephone_2" header="Téléphone 2" sortable>
                    <template #body="{ data }">
                        <span class="single-line">{{ data.telephone_2 || 'Non défini' }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par téléphone 2" @input="filterCallback()" />
                    </template>
                </Column>
                <Column field="etat_civil_fr" header="État Civil" sortable>
                    <template #body="{ data }">
                        <div class="flex flex-column">
                            <Tag :value="data.etat_civil_fr || 'Non défini'" :severity="getEtatCivilSeverity(data.etat_civil_fr)" :icon="getEtatCivilIcon(data.etat_civil_fr)" class="single-line" />
                            <span class="arabic-text text-blue-600 single-line">{{ data.etat_civil_ar || 'غير محدد' }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="['Célibataire', 'Marié', 'Divorcé', 'Veuf']"
                            placeholder="Sélectionner un état civil"
                            class="p-column-filter"
                            @change="filterCallback()"
                            :showClear="true"
                        />
                    </template>
                </Column>
                <Column field="nombre_enfants" header="Nombre d'Enfants" sortable>
                    <template #body="{ data }">
                        <span class="single-line">{{ data.nombre_enfants || 0 }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="number" class="p-column-filter" placeholder="Rechercher par nombre d'enfants" @input="filterCallback()" />
                    </template>
                </Column>
                <Column field="niveau_etude_fr" header="Niveau d'Étude" sortable>
                    <template #body="{ data }">
                        <div class="flex flex-column">
                            <span class="single-line">{{ data.niveau_etude_fr || 'Non défini' }}</span>
                            <span class="arabic-text text-blue-600 single-line">{{ data.niveau_etude_ar || 'غير محدد' }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par niveau d'étude" @input="filterCallback()" />
                    </template>
                </Column>
                <Column field="specialite_diplome_fr" header="Spécialité Diplôme" sortable>
                    <template #body="{ data }">
                        <div class="flex flex-column">
                            <span class="single-line">{{ data.specialite_diplome_fr || 'Non défini' }}</span>
                            <span class="arabic-text text-blue-600 single-line">{{ data.specialite_diplome_ar || 'غير محدد' }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par spécialité" @input="filterCallback()" />
                    </template>
                </Column>
                <Column field="code_niveau" header="Code Niveau" sortable>
                    <template #body="{ data }">
                        <span class="single-line">{{ data.code_niveau || 'Non défini' }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par code niveau" @input="filterCallback()" />
                    </template>
                </Column>
                <Column field="qualite_fr" header="Qualité" sortable>
                    <template #body="{ data }">
                        <div class="flex flex-column">
                            <Tag :value="data.qualite_fr || 'Non défini'" :severity="getQualiteSeverity(data.qualite_fr)" :icon="getQualiteIcon(data.qualite_fr)" class="single-line" />
                            <span class="arabic-text text-blue-600 single-line">{{ data.qualite_ar || 'غير محدد' }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="['Personnel (ATFP)', 'Personnel (Externe)']"
                            placeholder="Sélectionner une qualité"
                            class="p-column-filter"
                            @change="filterCallback()"
                            :showClear="true"
                        />
                    </template>
                </Column>
                <Column field="date_recrutement" header="Date de Recrutement" sortable>
                    <template #body="{ data }">
                        <Tag :value="formatDate(data.date_recrutement) || 'Non défini'" :icon="data.date_recrutement ? 'pi pi-calendar' : 'pi pi-info'" :severity="data.date_recrutement ? 'info' : 'secondary'" class="single-line" />
                    </template>
                </Column>
                <Column field="date_fin_service" header="Date Fin de Service" sortable>
                    <template #body="{ data }">
                        <Tag :value="formatDate(data.date_fin_service) || 'Non défini'" :icon="data.date_fin_service ? 'pi pi-calendar' : 'pi pi-info'" :severity="data.date_fin_service ? 'info' : 'secondary'" class="single-line" />
                    </template>
                </Column>
                <Column field="date_debut" header="Date Début Affectation" sortable>
                    <template #body="{ data }">
                        <Tag :value="formatDate(data.date_debut) || 'Non défini'" :icon="data.date_debut ? 'pi pi-calendar' : 'pi pi-info'" :severity="data.date_debut ? 'info' : 'secondary'" class="single-line" />
                    </template>
                </Column>
                <Column field="date_fin" header="Date Fin Affectation" sortable>
                    <template #body="{ data }">
                        <Tag :value="formatDate(data.date_fin) || 'Non défini'" :icon="data.date_fin ? 'pi pi-calendar' : 'pi pi-info'" :severity="data.date_fin ? 'info' : 'secondary'" class="single-line" />
                    </template>
                </Column>
                <Column field="regime_horaire" header="Régime Horaire" sortable>
                    <template #body="{ data }">
                        <span class="single-line">{{ data.regime_horaire || 'Non défini' }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par régime horaire" @input="filterCallback()" />
                    </template>
                </Column>
                <Column field="rabattement" header="Rabattement" sortable>
                    <template #body="{ data }">
                        <span class="single-line">{{ data.rabattement || 0 }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="number" class="p-column-filter" placeholder="Rechercher par rabattement" @input="filterCallback()" />
                    </template>
                </Column>
                <Column field="statut" header="Statut" sortable>
                    <template #body="{ data }">
                        <Tag :value="formatStatut(data.statut)" :severity="getSeverity(data.statut)" :icon="getStatutIcon(data.statut)" class="single-line" />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown
                            v-model="filterModel.value"
                            :options="['Actif', 'Inactif']"
                            placeholder="Sélectionner un statut"
                            class="p-column-filter"
                            @change="filterCallback()"
                            :showClear="true"
                        />
                    </template>
                </Column>
                <Column field="observation" header="Observations (Personnel)" sortable>
                    <template #body="{ data }">
                        <span class="single-line">{{ data.observation || data.observation_fr || 'Aucune' }}</span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" class="p-column-filter" placeholder="Rechercher par observations" @input="filterCallback()" />
                    </template>
                </Column>
                <Column header="Actions" :exportable="false" frozen>
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Button icon="pi pi-user" class="p-button-rounded p-button-text p-button-sm" @click="openProfil(data)" v-tooltip="'Profil complet'" />
                            <Button icon="pi pi-pencil" class="p-button-rounded p-button-text p-button-sm" @click="editPersonnel(data)" v-tooltip="'Modifier'" />
                            <Button icon="pi pi-file" class="p-button-rounded p-button-text p-button-sm p-button-success" @click="openDocumentForm(data)" v-tooltip="'Ajouter/Modifier Documents'" />
                            <Button icon="pi pi-briefcase" class="p-button-rounded p-button-text p-button-sm p-button-primary" @click="openContratForm(data)" v-tooltip="'Ajouter/Modifier Contrats'" />
                            <Button icon="pi pi-clock" class="p-button-rounded p-button-text p-button-sm regime-icon" @click="openRegimeForm(data)" v-tooltip="'Ajouter/Modifier Régime Horaire'" />
                            <Button icon="pi pi-id-card" class="p-button-rounded p-button-text p-button-sm fonction-icon" @click="openFonctionForm(data)" v-tooltip="'Ajouter/Modifier Fonction'" />
                            <Button icon="pi pi-trash" class="p-button-rounded p-button-text p-button-sm p-button-danger trash-icon" @click="confirmDeletePersonnel(data)" v-tooltip="'Supprimer'" />
                        </div>
                    </template>
                </Column>
            </DataTable>
            <!-- Personnel form modal -->
            <PersonnelCentreForm
                :visible="formVisible"
                :personnelToEdit="personnelToEdit"
                :isCentreRole="isCentreRole"
                @update:visible="formVisible = $event"
                @save="handleSavePersonnel"
                @close="closeForm"
            />
            <!-- Document form modal -->
            <DocumentPersonnelForm
                v-if="documentFormVisible && selectedPersonnelId"
                v-model:visible="documentFormVisible"
                :personnelId="selectedPersonnelId"
                @saved="fetchPersonnels"
            />
            <!-- Contract form modal -->
            <ContratPersonnelForm
                v-if="contratFormVisible && selectedPersonnelId"
                v-model:visible="contratFormVisible"
                :personnelId="selectedPersonnelId"
                @saved="fetchPersonnels"
            />
            <!-- Regime form modal -->
            <RegimePersonnelForm
                v-if="regimeFormVisible && selectedPersonnelId"
                v-model:visible="regimeFormVisible"
                :personnelId="selectedPersonnelId"
                :anneeFormationId="selectedAnneeFormation"
                :regimeToEdit="regimeToEdit"
                @saved="fetchPersonnels"
            />
            <!-- Fonction form modal -->
            <FonctionPersonnelForm
                v-if="fonctionFormVisible && selectedPersonnelId"
                v-model:visible="fonctionFormVisible"
                :personnelId="selectedPersonnelId"
                :isCentreRole="isCentreRole"
                :centreId="centreId"
                @fonction-saved="handleFonctionSaved"
            />
            <!-- Profile modal -->
            <ProfilPersonnel
                v-if="profilVisible && selectedPersonnelId"
                v-model:visible="profilVisible"
                :personnelId="selectedPersonnelId"
                @saved="fetchPersonnels"
            />
            <!-- Delete confirmation dialog -->
            <Dialog
                v-model:visible="deleteFormVisible"
                header="Confirmer la suppression"
                :modal="true"
                :style="{ width: '450px' }"
                :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
                class="p-dialog surface-card border-round-lg shadow-4"
                :pt="{
                    mask: { style: 'backdrop-filter: blur(4px)' },
                    header: { class: 'surface-50 border-bottom-1 surface-border p-3' },
                    content: { class: 'surface-ground p-4' },
                    footer: { class: 'surface-50 border-top-1 surface-border p-3' }
                }"
            >
                <div class="flex flex-column gap-3">
                    <div class="flex align-items-center gap-3">
                        <i class="pi pi-exclamation-triangle text-4xl text-red-500" />
                        <span>
                            Voulez-vous vraiment supprimer le personnel
                            <b>{{ personnelToDelete?.nom_fr }} {{ personnelToDelete?.prenom_fr }}</b>
                            du centre <b>{{ personnelToDelete?.centre_nom_fr }}</b> ?
                        </span>
                    </div>
                    <div class="field">
                        <label for="deletePassword" class="font-semibold">Mot de passe de confirmation</label>
                        <span class="p-inputgroup">
                            <InputText
                                id="deletePassword"
                                v-model="deletePassword"
                                :type="showPassword ? 'text' : 'password'"
                                class="w-full"
                                :class="{ 'p-invalid': passwordError }"
                                placeholder="Entrez le mot de passe"
                                @input="passwordError = ''"
                            />
                            <Button
                                :icon="showPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"
                                class="p-button-text"
                                @click="toggleShowPassword"
                                v-tooltip="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                            />
                        </span>
                        <small v-if="passwordError" class="p-error">{{ passwordError }}</small>
                    </div>
                </div>
                <template #footer>
                    <Button label="Annuler" icon="pi pi-times" class="p-button-text" @click="cancelDeletePersonnel" />
                    <Button label="Supprimer" icon="pi pi-check" class="p-button-danger" :disabled="loading || !deletePassword" @click="deletePersonnel" :loading="loading" />
                </template>
            </Dialog>
            <!-- Import dialog -->
            <Dialog
                v-model:visible="importDialogVisible"
                header="Importer des Personnels"
                :modal="true"
                :style="{ width: '450px' }"
                :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
                class="p-dialog surface-card border-round-lg shadow-4"
                :pt="{
                    mask: { style: 'backdrop-filter: blur(4px)' },
                    header: { class: 'surface-50 border-bottom-1 surface-border p-3' },
                    content: { class: 'surface-ground p-4' },
                    footer: { class: 'surface-50 border-top-1 surface-border p-3' }
                }"
            >
                <div class="flex flex-column gap-3">
                    <FileUpload
                        ref="fileUpload"
                        name="file"
                        accept=".xlsx,.xls"
                        :maxFileSize="10000000"
                        :customUpload="true"
                        chooseLabel="Choisir"
                        uploadLabel="Importer"
                        cancelLabel="Annuler"
                        @select="onFileSelect"
                        @uploader="onImport"
                        @clear="onFileClear"
                    >
                        <template #empty>
                            <p>Glissez-déposez un fichier Excel ici ou cliquez pour sélectionner (XLSX, XLS, max 10Mo).</p>
                        </template>
                    </FileUpload>
                    <small v-if="importError" class="p-error">{{ importError }}</small>
                </div>
                <template #footer>
                    <Button label="Annuler" icon="pi pi-times" class="p-button-text" @click="closeImportDialog" />
                </template>
            </Dialog>
            <!-- Notification Toast -->
            <Toast position="top-right" />
        </div>
    </div>
</template>

<script>
import { FilterMatchMode } from 'primevue/api';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Dropdown from 'primevue/dropdown';
import FileUpload from 'primevue/fileupload';
import ProgressSpinner from 'primevue/progressspinner';
import PersonnelCentreForm from '@/Components/Centres/Utilisateurs/Personnels/PersonnelForm.vue';
import DocumentPersonnelForm from '@/Components/Centres/Utilisateurs/Personnels/DocumentPersonnelForm.vue';
import ContratPersonnelForm from '@/Components/Centres/Utilisateurs/Personnels/ContratPersonnelForm.vue';
import RegimePersonnelForm from '@/Components/Centres/Utilisateurs/Personnels/RegimePersonnelForm.vue';
import FonctionPersonnelForm from '@/Components/Centres/Utilisateurs/Personnels/FonctionPersonnelForm.vue';
import ProfilPersonnel from '@/Components/Centres/Utilisateurs/Personnels/ProfilPersonnel.vue';

export default {
    name: 'PersonnelsCentresList',
    components: {
        Button,
        DataTable,
        Column,
        InputText,
        Tag,
        Dialog,
        Toast,
        Dropdown,
        FileUpload,
        ProgressSpinner,
        PersonnelCentreForm,
        DocumentPersonnelForm,
        ContratPersonnelForm,
        RegimePersonnelForm,
        FonctionPersonnelForm,
        ProfilPersonnel,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            isImporting: false,
            isCentreRole: false,
            centreId: null,
            userId: null,
            userInfo: null,
            loading: false,
            loadingRoles: false,
            loadingAnnees: false,
            showPassword: false,
            passwordError: '',
            deletePassword: '',
            importError: '',
            formVisible: false,
            documentFormVisible: false,
            contratFormVisible: false,
            regimeFormVisible: false,
            fonctionFormVisible: false,
            profilVisible: false,
            deleteFormVisible: false,
            importDialogVisible: false,
            selectedPersonnels: null,
            personnelToEdit: null,
            personnelToDelete: null,
            selectedPersonnelId: null,
            regimeToEdit: null,
            fonctionToEdit: null,
            personnels: [],
            roles: [],
            anneesFormations: [],
            selectedAnneeFormation: null,
            totalRecords: 0,
            filters: {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                matricule: { value: null, matchMode: FilterMatchMode.CONTAINS },
                cin: { value: null, matchMode: FilterMatchMode.CONTAINS },
                num_extrait_naissance: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                email: { value: null, matchMode: FilterMatchMode.CONTAINS },
                centre_nom_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                role_nom: { value: null, matchMode: FilterMatchMode.EQUALS },
                type_affectation_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                etablissement_origine_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                mission_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                centre_origine_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                adresse_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                gouvernorat_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                delegation_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                telephone_1: { value: null, matchMode: FilterMatchMode.CONTAINS },
                telephone_2: { value: null, matchMode: FilterMatchMode.CONTAINS },
                etat_civil_fr: { value: null, matchMode: FilterMatchMode.EQUALS },
                nombre_enfants: { value: null, matchMode: FilterMatchMode.EQUALS },
                niveau_etude_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                specialite_diplome_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code_niveau: { value: null, matchMode: FilterMatchMode.EQUALS },
                qualite_fr: { value: null, matchMode: FilterMatchMode.EQUALS },
                statut: { value: null, matchMode: FilterMatchMode.EQUALS },
                observation: { value: null, matchMode: FilterMatchMode.CONTAINS },
                regime_horaire: { value: null, matchMode: FilterMatchMode.CONTAINS },
                rabattement: { value: null, matchMode: FilterMatchMode.EQUALS },
            },
        };
    },
    async created() {
        await this.fetchUserInfo();
        await Promise.all([
            this.fetchFormData(),
            this.fetchAnneesFormations(),
            this.fetchPersonnels(),
        ]);
    },
    methods: {
        async fetchUserInfo() {
            try {
                this.isCentreRole = Boolean(Number(localStorage.getItem('isCentreRole')));
                this.centreId = Number(localStorage.getItem('centreId')) || 0;
                this.userId = Number(localStorage.getItem('user_id'));
                this.userInfo = {
                    nom_fr: localStorage.getItem('user_nom_fr'),
                    prenom_fr: localStorage.getItem('user_prenom_fr'),
                };
                console.log('fetchUserInfo - Valeurs assignées:', {
                    isCentreRole: this.isCentreRole,
                    centreId: this.centreId,
                    userId: this.userId,
                    userInfo: this.userInfo,
                });
                if (!this.userId) {
                    throw new Error('Utilisateur non authentifié ou user_id manquant dans localStorage');
                }
            } catch (error) {
                console.error('SError récupération info utilisateur:', error);
                this.handleApiError(error, 'Erreur lors de la récupération des informations utilisateur.');
                this.isCentreRole = false;
                this.centreId = 0;
                this.$router.push({ name: 'login' });
            }
        },
        getHeaders() {
            const headers = {
                Authorization: `Bearer ${localStorage.getItem('token')}`,
            };
            console.log('Headers générés dans getHeaders:', headers);
            return headers;
        },
        async fetchPersonnels(event = {}) {
            this.loading = true;
            try {
                const params = {
                    user_id: this.userId,
                    annee_formation_id: this.selectedAnneeFormation,
                    page: event.page ? event.page + 1 : 1,
                    rows: event.rows || 20,
                    sortField: event.sortField || null,
                    sortOrder: event.sortOrder || null,
                    filters: JSON.stringify(this.filters),
                };
                console.log('fetchPersonnels - Paramètres envoyés:', params);
                const response = await axios.get('/api/users', {
                    headers: this.getHeaders(),
                    params,
                });
                console.log('fetchPersonnels - Réponse reçue:', response.data);
                this.personnels = response.data.data || [];
                this.totalRecords = response.data.total || 0;
                this.userInfo = response.data.user_info || this.userInfo;
                console.log('fetchPersonnels - Données assignées:', {
                    personnels: this.personnels,
                    totalRecords: this.totalRecords,
                    userInfo: this.userInfo,
                });
            } catch (error) {
                console.error('Erreur lors de fetchPersonnels:', error);
                this.handleApiError(error, 'Erreur lors du chargement des utilisateurs.');
                this.personnels = [];
                this.totalRecords = 0;
            } finally {
                this.loading = false;
            }
        },
        async fetchFormData() {
            this.loadingRoles = true;
            try {
                const response = await axios.get('/api/users/form-data', {
                    headers: this.getHeaders(),
                });
                this.roles = Array.isArray(response.data.roles) ? response.data.roles : [];
                if (!this.roles.length) {
                    this.showWarn('Aucun rôle disponible pour le filtre. Veuillez vérifier les données des rôles.');
                }
            } catch (error) {
                console.error('Erreur lors de fetchFormData:', error);
                this.handleApiError(error, 'Erreur lors du chargement des rôles pour le filtre.');
                this.roles = [];
            } finally {
                this.loadingRoles = false;
            }
        },
        async fetchAnneesFormations() {
            this.loadingAnnees = true;
            try {
                const response = await axios.get('/api/users/annees-formations', {
                    headers: this.getHeaders(),
                });
                this.anneesFormations = Array.isArray(response.data) ? response.data : [];
                if (!this.anneesFormations.length) {
                    this.showWarn('Aucune année de formation disponible. Veuillez en créer une.');
                } else {
                    this.selectedAnneeFormation = this.anneesFormations.find(annee => annee.statut === 'En cours')?.id || null;
                }
            } catch (error) {
                console.error('Erreur lors de fetchAnneesFormations:', error);
                this.handleApiError(error, 'Erreur lors du chargement des années de formation.');
                this.anneesFormations = [];
            } finally {
                this.loadingAnnees = false;
            }
        },
        onPage(event) {
            this.fetchPersonnels(event);
        },
        onSort(event) {
            this.fetchPersonnels(event);
        },
        onFilter() {
            this.fetchPersonnels();
        },
        async openFonctionForm(personnel) {
            if (!personnel?.id) {
                this.showError('Aucun utilisateur sélectionné ou ID manquant.');
                return;
            }
            if (this.isCentreRole && !this.centreId) {
                this.showError('ID du centre manquant pour un rôle de centre. Veuillez vérifier les informations utilisateur.');
                return;
            }
            this.selectedPersonnelId = personnel.id;
            this.fonctionToEdit = null;
            this.fonctionFormVisible = true;
            console.log('openFonctionForm - personnelId:', this.selectedPersonnelId, 'isCentreRole:', this.isCentreRole, 'centreId:', this.centreId);
        },
        handleFonctionSaved(fonction) {
            console.log('handleFonctionSaved - fonction:', fonction);
            this.fonctionFormVisible = false;
            this.selectedPersonnelId = null;
            this.fonctionToEdit = null;
            this.fetchPersonnels();
            this.showSuccess(fonction ? 'Fonction enregistrée avec succès.' : 'Fonction supprimée avec succès.');
        },
        openProfil(personnel) {
            if (!personnel?.id) {
                this.showError('Aucun utilisateur sélectionné ou ID manquant.');
                return;
            }
            this.selectedPersonnelId = personnel.id;
            this.profilVisible = true;
        },
        openDocumentForm(personnel) {
            if (!personnel?.id) {
                this.showError('Aucun utilisateur sélectionné ou ID manquant.');
                return;
            }
            this.selectedPersonnelId = personnel.id;
            this.documentFormVisible = true;
        },
        openContratForm(personnel) {
            if (!personnel?.id) {
                this.showError('Aucun utilisateur sélectionné ou ID manquant.');
                return;
            }
            this.selectedPersonnelId = personnel.id;
            this.contratFormVisible = true;
        },
        async openRegimeForm(personnel) {
            if (!personnel?.id) {
                this.showError('Aucun utilisateur sélectionné ou ID manquant.');
                return;
            }
            this.selectedPersonnelId = personnel.id;
            this.loading = true;
            try {
                const response = await axios.get(`/api/users/${personnel.id}/regimes`, {
                    headers: this.getHeaders(),
                });
                this.regimeToEdit = response.data;
                this.regimeFormVisible = true;
            } catch (error) {
                console.error('Erreur lors de openRegimeForm:', error);
                this.handleApiError(error, 'Erreur lors de la récupération du régime horaire.');
                if (error.response?.status === 404) {
                    this.regimeToEdit = null;
                    this.regimeFormVisible = true;
                    this.showInfo('Aucun régime horaire trouvé. Mode création activé.');
                }
            } finally {
                this.loading = false;
            }
        },
        openForm() {
            this.personnelToEdit = null;
            this.formVisible = true;
        },
        closeForm() {
            this.formVisible = false;
            this.personnelToEdit = null;
        },
        editPersonnel(personnel) {
            this.personnelToEdit = { ...personnel };
            this.formVisible = true;
        },
        confirmDeletePersonnel(personnel) {
            this.personnelToDelete = personnel;
            this.deletePassword = '';
            this.passwordError = '';
            this.deleteFormVisible = true;
        },
        cancelDeletePersonnel() {
            this.deleteFormVisible = false;
            this.personnelToDelete = null;
            this.deletePassword = '';
            this.passwordError = '';
        },
        async deletePersonnel() {
            if (!this.personnelToDelete?.id) return;
            this.loading = true;
            try {
                const response = await axios.delete(`/api/users/${this.personnelToDelete.id}`, {
                    headers: this.getHeaders(),
                    data: { password: this.deletePassword.trim() },
                });
                this.personnels = this.personnels.filter(p => p.id !== this.personnelToDelete.id);
                this.totalRecords = Math.max(0, this.totalRecords - 1);
                this.showSuccess(response.data.message || 'Utilisateur supprimé avec succès.');
                this.deleteFormVisible = false;
                this.personnelToDelete = null;
                this.deletePassword = '';
                this.passwordError = '';
            } catch (error) {
                console.error('Erreur lors de deletePersonnel:', error);
                this.passwordError = error.response?.data?.error || 'Erreur lors de la suppression. Vérifiez le mot de passe.';
                this.showError(this.passwordError);
            } finally {
                this.loading = false;
            }
        },
        handleSavePersonnel(personnel) {
            this.personnels.push(personnel);
            this.totalRecords += 1;
            this.showSuccess('Utilisateur créé avec succès.');
            this.closeForm();
        },
        async exportPersonnels() {
            try {
                const response = await axios.get('/api/users/export', {
                    headers: this.getHeaders(),
                    responseType: 'blob',
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'users.xlsx');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                this.showSuccess('Exportation réussie.');
            } catch (error) {
                console.error('Erreur lors de exportPersonnels:', error);
                this.handleApiError(error, 'Erreur lors de l\'exportation.');
            }
        },
        openImportDialog() {
            this.importDialogVisible = true;
            this.importError = '';
            if (this.$refs.fileUpload) this.$refs.fileUpload.clear();
        },
        closeImportDialog() {
            this.importDialogVisible = false;
            this.importError = '';
            if (this.$refs.fileInput) this.$refs.fileInput.value = '';
        },
        onFileSelect(event) {
            this.importError = '';
            const file = event.files?.[0];
            if (!file) return;
            if (!file.name.match(/\.(xlsx|xls)$/)) {
                this.importError = 'Veuillez sélectionner un fichier Excel valide (XLSX, XLS)';
                this.showError(this.importError);
                return;
            }
            if (file.size > 10000000) {
                this.importError = 'La taille du fichier ne doit pas dépasser 10 Mo';
                this.showError(this.importError);
            }
        },
        async onImport(event) {
            this.isImporting = true;
            const file = event.files?.[0] || event.target.files?.[0];
            if (!file) return;
            const formData = new FormData();
            formData.append('file', file);
            try {
                const response = await axios.post('/api/users/import', formData, {
                    headers: { ...this.getHeaders(), 'Content-Type': 'multipart/form-data' },
                });
                this.showSuccess(response.data.message || 'Importation réussie.');
                this.fetchPersonnels();
                this.closeImportDialog();
            } catch (error) {
                console.error('Erreur lors de onImport:', error);
                this.importError = error.response?.data?.message || 'Erreur lors de l\'importation.';
                this.showError(this.importError);
            } finally {
                this.isImporting = false;
                if (this.$refs.fileInput) this.$refs.fileInput.value = '';
            }
        },
        onFileClear() {
            this.importError = '';
            this.showSuccess('Sélection du fichier annulée.');
        },
        clearFilter() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                matricule: { value: null, matchMode: FilterMatchMode.CONTAINS },
                cin: { value: null, matchMode: FilterMatchMode.CONTAINS },
                num_extrait_naissance: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                email: { value: null, matchMode: FilterMatchMode.CONTAINS },
                centre_nom_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                role_nom: { value: null, matchMode: FilterMatchMode.EQUALS },
                type_affectation_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                etablissement_origine_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                mission_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                centre_origine_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                adresse_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                gouvernorat_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                delegation_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                telephone_1: { value: null, matchMode: FilterMatchMode.CONTAINS },
                telephone_2: { value: null, matchMode: FilterMatchMode.CONTAINS },
                etat_civil_fr: { value: null, matchMode: FilterMatchMode.EQUALS },
                nombre_enfants: { value: null, matchMode: FilterMatchMode.EQUALS },
                niveau_etude_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                specialite_diplome_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code_niveau: { value: null, matchMode: FilterMatchMode.EQUALS },
                qualite_fr: { value: null, matchMode: FilterMatchMode.EQUALS },
                statut: { value: null, matchMode: FilterMatchMode.EQUALS },
                observation: { value: null, matchMode: FilterMatchMode.CONTAINS },
                regime_horaire: { value: null, matchMode: FilterMatchMode.CONTAINS },
                rabattement: { value: null, matchMode: FilterMatchMode.EQUALS },
            };
            this.fetchPersonnels();
        },
        formatStatut(statut) {
            return statut || 'Inactif';
        },
        getSeverity(statut) {
            return statut === 'Actif' ? 'success' : 'danger';
        },
        getStatutIcon(statut) {
            return statut === 'Actif' ? 'pi pi-check' : 'pi pi-times';
        },
        getQualiteSeverity(qualite) {
            const qualiteMap = {
                'Personnel (ATFP)': 'success',
                'Personnel (Externe)': 'info',
            };
            return qualiteMap[qualite] || 'secondary';
        },
        getQualiteIcon(qualite) {
            const qualiteIcons = {
                'Personnel (ATFP)': 'pi pi-user',
                'Personnel (Externe)': 'pi pi-users',
            };
            return qualiteIcons[qualite] || 'pi pi-info';
        },
        getEtatCivilSeverity(etatCivil) {
            const etatCivilSeverity = {
                'Célibataire': 'info',
                'Marié': 'success',
                'Divorcé': 'warning',
                'Veuf': 'danger',
            };
            return etatCivilSeverity[etatCivil] || 'secondary';
        },
        getEtatCivilIcon(etatCivil) {
            const etatCivilIcons = {
                'Célibataire': 'pi pi-user',
                'Marié': 'pi pi-heart',
                'Divorcé': 'pi pi-times',
                'Veuf': 'pi pi-ban',
            };
            return etatCivilIcons[etatCivil] || 'pi pi-info';
        },
        formatDate(date) {
            if (!date) return null;
            return new Date(date).toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            });
        },
        showSuccess(message) {
            this.toast.add({ severity: 'success', summary: 'Succès', detail: message, life: 3000 });
        },
        showError(message) {
            this.toast.add({ severity: 'error', summary: 'Erreur', detail: message, life: 5000 });
        },
        showInfo(message) {
            this.toast.add({ severity: 'info', summary: 'Information', detail: message, life: 3000 });
        },
        showWarn(message) {
            this.toast.add({ severity: 'warn', summary: 'Avertissement', detail: message, life: 5000 });
        },
        toggleShowPassword() {
            this.showPassword = !this.showPassword;
        },
        onImageError(event, photo) {
            if (photo && photo !== '') {
                this.showWarn("Impossible de charger l'image de la photo.");
            }
        },
        handleApiError(error, defaultMessage) {
            console.error('Erreur API:', {
                status: error.response?.status,
                data: error.response?.data,
                message: error.message,
            });
            this.showError(error.response?.data?.message || defaultMessage);
            if (error.response?.status === 401) {
                this.$router.push({ name: 'login' });
            }
        },
    },
};
</script>

<style scoped>
@font-face {
    font-family: 'NotoNaskhArabic';
    src: url('/fonts/NotoNaskhArabic-VariableFont_wght.ttf') format('truetype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}
.arabic-text {
    font-family: 'NotoNaskhArabic', sans-serif;
    direction: rtl;
    text-align: right;
}
.p-datatable {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
.p-datatable-sm .p-datatable-tbody > tr > td {
    padding: 0.5rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.p-datatable-sm .p-datatable-thead > tr > th {
    padding: 0.5rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.p-datatable .p-column-header-content {
    justify-content: center;
    padding: 0.5rem;
    white-space: nowrap;
}
.single-line {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.field {
    margin-bottom: 1rem;
}
.p-error {
    color: #dc3545;
    font-size: 0.875rem;
}
label {
    margin-bottom: 0.5rem;
    display: block;
    color: #495057;
}
@media (prefers-color-scheme: dark) {
    label {
        color: #1f2a44;
    }
}
.import-icon i {
    color: #28a745;
}
.export-icon i {
    color: #007bff;
}
.trash-icon i {
    color: #dc3545;
}
.regime-icon i {
    color: #6c757d;
}
.fonction-icon i {
    color: #17a2b8;
}
.p-tooltip-text {
    white-space: nowrap;
}
.progress-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}
.progress-text {
    margin-top: 10px;
    color: #ffffff;
    font-size: 1.2rem;
}
.logo-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 50%;
}
.search-field {
    min-width: 250px;
}
</style>
