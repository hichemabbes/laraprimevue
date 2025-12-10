<template>
    <Dialog
        :visible="visible"
        :header="
            personnelToEdit ? 'Modifier un personnel' : 'Ajouter un personnel'
        "
        :modal="true"
        :style="{ width: '900px' }"
        @update:visible="updateVisible"
    >
        <div class="p-fluid">
            <!-- Recherche d’un personnel existant -->
            <div class="field" v-if="!personnelToEdit">
                <label for="search_matricule"
                    >Rechercher par matricule ou CIN</label
                >
                <div class="p-inputgroup">
                    <InputText
                        id="search_matricule"
                        v-model="searchQuery"
                        placeholder="Entrez le matricule ou CIN"
                        @input="debounceSearch"
                    />
                    <Button
                        icon="pi pi-search"
                        @click="searchPersonnel"
                        :loading="searching"
                    />
                </div>
                <small v-if="searchResult" class="p-success">
                    Personnel trouvé : {{ searchResult.nom_fr }}
                    {{ searchResult.prenom_fr }} (Matricule:
                    {{ searchResult.matricule }})
                </small>
                <small v-if="searchError" class="p-error">{{
                    searchError
                }}</small>
            </div>

            <!-- Données statiques -->
            <div class="field" v-if="!searchResult || personnelToEdit">
                <label for="name"
                    >Nom <span class="text-red-500">*</span></label
                >
                <InputText
                    id="name"
                    v-model="form.name"
                    :class="{ 'p-invalid': errors.name }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.name" class="p-error">{{
                    errors.name[0]
                }}</small>
            </div>
            <div class="field" v-if="!searchResult || personnelToEdit">
                <label for="email"
                    >Email <span class="text-red-500">*</span></label
                >
                <InputText
                    id="email"
                    v-model="form.email"
                    :class="{ 'p-invalid': errors.email }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.email" class="p-error">{{
                    errors.email[0]
                }}</small>
            </div>
            <div class="field" v-if="!searchResult || personnelToEdit">
                <label for="role"
                    >Rôle <span class="text-red-500">*</span></label
                >
                <Dropdown
                    id="role"
                    v-model="form.role"
                    :options="roles"
                    optionLabel="name"
                    optionValue="name"
                    placeholder="Sélectionner un rôle"
                    :loading="rolesLoading"
                    :class="{ 'p-invalid': errors.role }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.role" class="p-error">{{
                    errors.role[0]
                }}</small>
            </div>
            <div class="field">
                <label for="matricule"
                    >Matricule <span class="text-red-500">*</span></label
                >
                <InputText
                    id="matricule"
                    v-model="form.matricule"
                    :class="{ 'p-invalid': errors.matricule }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.matricule" class="p-error">{{
                    errors.matricule[0]
                }}</small>
            </div>
            <div class="field">
                <label for="nom_fr">Nom (FR)</label>
                <InputText
                    id="nom_fr"
                    v-model="form.nom_fr"
                    :class="{ 'p-invalid': errors.nom_fr }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.nom_fr" class="p-error">{{
                    errors.nom_fr[0]
                }}</small>
            </div>
            <div class="field">
                <label for="prenom_fr">Prénom (FR)</label>
                <InputText
                    id="prenom_fr"
                    v-model="form.prenom_fr"
                    :class="{ 'p-invalid': errors.prenom_fr }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.prenom_fr" class="p-error">{{
                    errors.prenom_fr[0]
                }}</small>
            </div>
            <div class="field">
                <label for="nom_ar">Nom (AR)</label>
                <InputText
                    id="nom_ar"
                    v-model="form.nom_ar"
                    :class="{ 'p-invalid': errors.nom_ar }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.nom_ar" class="p-error">{{
                    errors.nom_ar[0]
                }}</small>
            </div>
            <div class="field">
                <label for="prenom_ar">Prénom (AR)</label>
                <InputText
                    id="prenom_ar"
                    v-model="form.prenom_ar"
                    :class="{ 'p-invalid': errors.prenom_ar }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.prenom_ar" class="p-error">{{
                    errors.prenom_ar[0]
                }}</small>
            </div>
            <div class="field">
                <label for="cin">CIN</label>
                <InputText
                    id="cin"
                    v-model="form.cin"
                    :class="{ 'p-invalid': errors.cin }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.cin" class="p-error">{{
                    errors.cin[0]
                }}</small>
            </div>
            <div class="field">
                <label for="date_naissance">Date de naissance</label>
                <Calendar
                    id="date_naissance"
                    v-model="form.date_naissance"
                    dateFormat="yy-mm-dd"
                    :class="{ 'p-invalid': errors.date_naissance }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.date_naissance" class="p-error">{{
                    errors.date_naissance[0]
                }}</small>
            </div>
            <div class="field">
                <label for="lieu_naissance_fr">Lieu de naissance (FR)</label>
                <InputText
                    id="lieu_naissance_fr"
                    v-model="form.lieu_naissance_fr"
                    :class="{ 'p-invalid': errors.lieu_naissance_fr }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.lieu_naissance_fr" class="p-error">{{
                    errors.lieu_naissance_fr[0]
                }}</small>
            </div>
            <div class="field">
                <label for="lieu_naissance_ar">Lieu de naissance (AR)</label>
                <InputText
                    id="lieu_naissance_ar"
                    v-model="form.lieu_naissance_ar"
                    :class="{ 'p-invalid': errors.lieu_naissance_ar }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.lieu_naissance_ar" class="p-error">{{
                    errors.lieu_naissance_ar[0]
                }}</small>
            </div>
            <div class="field">
                <label for="nationalite">Nationalité</label>
                <InputText
                    id="nationalite"
                    v-model="form.nationalite"
                    :class="{ 'p-invalid': errors.nationalite }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.nationalite" class="p-error">{{
                    errors.nationalite[0]
                }}</small>
            </div>
            <div class="field">
                <label for="sexe">Sexe</label>
                <Dropdown
                    id="sexe"
                    v-model="form.sexe"
                    :options="['Masculin', 'Féminin']"
                    placeholder="Sélectionner"
                    :class="{ 'p-invalid': errors.sexe }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.sexe" class="p-error">{{
                    errors.sexe[0]
                }}</small>
            </div>
            <div class="field">
                <label for="adresse_fr">Adresse (FR)</label>
                <InputText
                    id="adresse_fr"
                    v-model="form.adresse_fr"
                    :class="{ 'p-invalid': errors.adresse_fr }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.adresse_fr" class="p-error">{{
                    errors.adresse_fr[0]
                }}</small>
            </div>
            <div class="field">
                <label for="adresse_ar">Adresse (AR)</label>
                <InputText
                    id="adresse_ar"
                    v-model="form.adresse_ar"
                    :class="{ 'p-invalid': errors.adresse_ar }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.adresse_ar" class="p-error">{{
                    errors.adresse_ar[0]
                }}</small>
            </div>
            <div class="field">
                <label for="gouvernorat_fr">Gouvernorat (FR)</label>
                <InputText
                    id="gouvernorat_fr"
                    v-model="form.gouvernorat_fr"
                    :class="{ 'p-invalid': errors.gouvernorat_fr }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.gouvernorat_fr" class="p-error">{{
                    errors.gouvernorat_fr[0]
                }}</small>
            </div>
            <div class="field">
                <label for="gouvernorat_ar">Gouvernorat (AR)</label>
                <InputText
                    id="gouvernorat_ar"
                    v-model="form.gouvernorat_ar"
                    :class="{ 'p-invalid': errors.gouvernorat_ar }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.gouvernorat_ar" class="p-error">{{
                    errors.gouvernorat_ar[0]
                }}</small>
            </div>
            <div class="field">
                <label for="delegation_fr">Délégation (FR)</label>
                <InputText
                    id="delegation_fr"
                    v-model="form.delegation_fr"
                    :class="{ 'p-invalid': errors.delegation_fr }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.delegation_fr" class="p-error">{{
                    errors.delegation_fr[0]
                }}</small>
            </div>
            <div class="field">
                <label for="delegation_ar">Délégation (AR)</label>
                <InputText
                    id="delegation_ar"
                    v-model="form.delegation_ar"
                    :class="{ 'p-invalid': errors.delegation_ar }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.delegation_ar" class="p-error">{{
                    errors.delegation_ar[0]
                }}</small>
            </div>
            <div class="field">
                <label for="tele1">Téléphone 1</label>
                <InputText
                    id="tele1"
                    v-model="form.tele1"
                    :class="{ 'p-invalid': errors.tele1 }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.tele1" class="p-error">{{
                    errors.tele1[0]
                }}</small>
            </div>
            <div class="field">
                <label for="tele2">Téléphone 2</label>
                <InputText
                    id="tele2"
                    v-model="form.tele2"
                    :class="{ 'p-invalid': errors.tele2 }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.tele2" class="p-error">{{
                    errors.tele2[0]
                }}</small>
            </div>
            <div class="field">
                <label for="etat_civil">État civil</label>
                <Dropdown
                    id="etat_civil"
                    v-model="form.etat_civil"
                    :options="['Célibataire', 'Marié', 'Divorcé', 'Veuf']"
                    placeholder="Sélectionner"
                    :class="{ 'p-invalid': errors.etat_civil }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.etat_civil" class="p-error">{{
                    errors.etat_civil[0]
                }}</small>
            </div>
            <div class="field">
                <label for="fonction_principale">Fonction principale</label>
                <InputText
                    id="fonction_principale"
                    v-model="form.fonction_principale"
                    :class="{ 'p-invalid': errors.fonction_principale }"
                />
                <small v-if="errors.fonction_principale" class="p-error">{{
                    errors.fonction_principale[0]
                }}</small>
            </div>
            <div class="field">
                <label for="charge_par">Chargé par</label>
                <InputText
                    id="charge_par"
                    v-model="form.charge_par"
                    :class="{ 'p-invalid': errors.charge_par }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.charge_par" class="p-error">{{
                    errors.charge_par[0]
                }}</small>
            </div>
            <div class="field">
                <label for="statut_personnel">Statut du personnel</label>
                <Dropdown
                    id="statut_personnel"
                    v-model="form.statut_personnel"
                    :options="[
                        'Actif',
                        'En congé',
                        'Démissionnaire',
                        'Licencié',
                    ]"
                    placeholder="Sélectionner"
                    :class="{ 'p-invalid': errors.statut_personnel }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.statut_personnel" class="p-error">{{
                    errors.statut_personnel[0]
                }}</small>
            </div>
            <div class="field">
                <label for="observations">Observations</label>
                <Textarea
                    id="observations"
                    v-model="form.observations"
                    rows="3"
                    :class="{ 'p-invalid': errors.observations }"
                    :disabled="!!searchResult"
                />
                <small v-if="errors.observations" class="p-error">{{
                    errors.observations[0]
                }}</small>
            </div>

            <!-- Données dynamiques -->
            <div class="field" v-if="isCentral">
                <label for="centre_id"
                    >Centre <span class="text-red-500">*</span></label
                >
                <Dropdown
                    id="centre_id"
                    v-model="form.centre_id"
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
            <div class="field">
                <label for="poste">Poste</label>
                <InputText
                    id="poste"
                    v-model="form.poste"
                    :class="{ 'p-invalid': errors.poste }"
                />
                <small v-if="errors.poste" class="p-error">{{
                    errors.poste[0]
                }}</small>
            </div>
            <div class="field">
                <label for="type_affectation">Type d'affectation</label>
                <Dropdown
                    id="type_affectation"
                    v-model="form.type_affectation"
                    :options="['Principale', 'Intérim']"
                    placeholder="Sélectionner un type"
                    :class="{ 'p-invalid': errors.type_affectation }"
                />
                <small v-if="errors.type_affectation" class="p-error">{{
                    errors.type_affectation[0]
                }}</small>
            </div>
            <div class="field">
                <label for="affectation_statut"
                    >Statut de l’affectation
                    <span class="text-red-500">*</span></label
                >
                <Dropdown
                    id="affectation_statut"
                    v-model="form.affectation_statut"
                    :options="['Actif', 'Inactif']"
                    placeholder="Sélectionner un statut"
                    :class="{ 'p-invalid': errors.affectation_statut }"
                />
                <small v-if="errors.affectation_statut" class="p-error">{{
                    errors.affectation_statut[0]
                }}</small>
            </div>
            <div class="field">
                <label for="date_debut">Date de début</label>
                <Calendar
                    id="date_debut"
                    v-model="form.date_debut"
                    dateFormat="yy-mm-dd"
                    :class="{ 'p-invalid': errors.date_debut }"
                />
                <small v-if="errors.date_debut" class="p-error">{{
                    errors.date_debut[0]
                }}</small>
            </div>
            <div class="field">
                <label for="date_fin">Date de fin</label>
                <Calendar
                    id="date_fin"
                    v-model="form.date_fin"
                    dateFormat="yy-mm-dd"
                    :class="{ 'p-invalid': errors.date_fin }"
                />
                <small v-if="errors.date_fin" class="p-error">{{
                    errors.date_fin[0]
                }}</small>
            </div>
            <div class="field">
                <label for="code_niveau">Code niveau</label>
                <InputText
                    id="code_niveau"
                    v-model="form.code_niveau"
                    :class="{ 'p-invalid': errors.code_niveau }"
                />
                <small v-if="errors.code_niveau" class="p-error">{{
                    errors.code_niveau[0]
                }}</small>
            </div>
            <div class="field">
                <label for="regime_horaire">Régime horaire</label>
                <InputNumber
                    id="regime_horaire"
                    v-model="form.regime_horaire"
                    :class="{ 'p-invalid': errors.regime_horaire }"
                />
                <small v-if="errors.regime_horaire" class="p-error">{{
                    errors.regime_horaire[0]
                }}</small>
            </div>
            <div class="field">
                <label for="rabattement">Rabattement</label>
                <InputNumber
                    id="rabattement"
                    v-model="form.rabattement"
                    mode="decimal"
                    :minFractionDigits="2"
                    :maxFractionDigits="2"
                    :class="{ 'p-invalid': errors.rabattement }"
                />
                <small v-if="errors.rabattement" class="p-error">{{
                    errors.rabattement[0]
                }}</small>
            </div>
            <div class="field">
                <label for="salaire_base">Salaire de base</label>
                <InputNumber
                    id="salaire_base"
                    v-model="form.salaire_base"
                    mode="decimal"
                    :minFractionDigits="2"
                    :maxFractionDigits="2"
                    :class="{ 'p-invalid': errors.salaire_base }"
                />
                <small v-if="errors.salaire_base" class="p-error">{{
                    errors.salaire_base[0]
                }}</small>
            </div>
            <div class="field">
                <label for="grade">Grade</label>
                <InputText
                    id="grade"
                    v-model="form.grade"
                    :class="{ 'p-invalid': errors.grade }"
                />
                <small v-if="errors.grade" class="p-error">{{
                    errors.grade[0]
                }}</small>
            </div>
            <div class="field">
                <label for="categorie">Catégorie</label>
                <InputText
                    id="categorie"
                    v-model="form.categorie"
                    :class="{ 'p-invalid': errors.categorie }"
                />
                <small v-if="errors.categorie" class="p-error">{{
                    errors.categorie[0]
                }}</small>
            </div>
            <div class="field">
                <label for="echelle">Échelle</label>
                <InputText
                    id="echelle"
                    v-model="form.echelle"
                    :class="{ 'p-invalid': errors.echelle }"
                />
                <small v-if="errors.echelle" class="p-error">{{
                    errors.echelle[0]
                }}</small>
            </div>
            <div class="field">
                <label for="echelon">Échelon</label>
                <InputText
                    id="echelon"
                    v-model="form.echelon"
                    :class="{ 'p-invalid': errors.echelon }"
                />
                <small v-if="errors.echelon" class="p-error">{{
                    errors.echelon[0]
                }}</small>
            </div>
            <div class="field">
                <label for="banque">Banque</label>
                <InputText
                    id="banque"
                    v-model="form.banque"
                    :class="{ 'p-invalid': errors.banque }"
                />
                <small v-if="errors.banque" class="p-error">{{
                    errors.banque[0]
                }}</small>
            </div>
            <div class="field">
                <label for="numero_compte">Numéro de compte</label>
                <InputText
                    id="numero_compte"
                    v-model="form.numero_compte"
                    :class="{ 'p-invalid': errors.numero_compte }"
                />
                <small v-if="errors.numero_compte" class="p-error">{{
                    errors.numero_compte[0]
                }}</small>
            </div>
            <div class="field">
                <label for="niveau_etude">Niveau d'étude</label>
                <InputText
                    id="niveau_etude"
                    v-model="form.niveau_etude"
                    :class="{ 'p-invalid': errors.niveau_etude }"
                />
                <small v-if="errors.niveau_etude" class="p-error">{{
                    errors.niveau_etude[0]
                }}</small>
            </div>
            <div class="field">
                <label for="specialite_diplome_fr"
                    >Spécialité diplôme (FR)</label
                >
                <InputText
                    id="specialite_diplome_fr"
                    v-model="form.specialite_diplome_fr"
                    :class="{ 'p-invalid': errors.specialite_diplome_fr }"
                />
                <small v-if="errors.specialite_diplome_fr" class="p-error">{{
                    errors.specialite_diplome_fr[0]
                }}</small>
            </div>
            <div class="field">
                <label for="specialite_diplome_ar"
                    >Spécialité diplôme (AR)</label
                >
                <InputText
                    id="specialite_diplome_ar"
                    v-model="form.specialite_diplome_ar"
                    :class="{ 'p-invalid': errors.specialite_diplome_ar }"
                />
                <small v-if="errors.specialite_diplome_ar" class="p-error">{{
                    errors.specialite_diplome_ar[0]
                }}</small>
            </div>
            <div class="field">
                <label for="code_secteur">Code secteur</label>
                <InputText
                    id="code_secteur"
                    v-model="form.code_secteur"
                    :class="{ 'p-invalid': errors.code_secteur }"
                />
                <small v-if="errors.code_secteur" class="p-error">{{
                    errors.code_secteur[0]
                }}</small>
            </div>
            <div class="field">
                <label for="code_sous_secteur">Code sous-secteur</label>
                <InputText
                    id="code_sous_secteur"
                    v-model="form.code_sous_secteur"
                    :class="{ 'p-invalid': errors.code_sous_secteur }"
                />
                <small v-if="errors.code_sous_secteur" class="p-error">{{
                    errors.code_sous_secteur[0]
                }}</small>
            </div>
            <div class="field">
                <label for="type">Type</label>
                <Dropdown
                    id="type"
                    v-model="form.type"
                    :options="['Titulaire', 'Contractuel']"
                    placeholder="Sélectionner"
                    :class="{ 'p-invalid': errors.type }"
                />
                <small v-if="errors.type" class="p-error">{{
                    errors.type[0]
                }}</small>
            </div>
            <div class="field">
                <label for="date_embauche">Date d'embauche</label>
                <Calendar
                    id="date_embauche"
                    v-model="form.date_embauche"
                    dateFormat="yy-mm-dd"
                    :class="{ 'p-invalid': errors.date_embauche }"
                />
                <small v-if="errors.date_embauche" class="p-error">{{
                    errors.date_embauche[0]
                }}</small>
            </div>
            <div class="field">
                <label for="type_contrat">Type de contrat</label>
                <InputText
                    id="type_contrat"
                    v-model="form.type_contrat"
                    :class="{ 'p-invalid': errors.type_contrat }"
                />
                <small v-if="errors.type_contrat" class="p-error">{{
                    errors.type_contrat[0]
                }}</small>
            </div>
            <div class="field">
                <label for="num_contrat">Numéro de contrat</label>
                <InputText
                    id="num_contrat"
                    v-model="form.num_contrat"
                    :class="{ 'p-invalid': errors.num_contrat }"
                />
                <small v-if="errors.num_contrat" class="p-error">{{
                    errors.num_contrat[0]
                }}</small>
            </div>
            <div class="field">
                <label for="num_autorisation_contrat"
                    >Numéro d'autorisation contrat</label
                >
                <InputText
                    id="num_autorisation_contrat"
                    v-model="form.num_autorisation_contrat"
                    :class="{ 'p-invalid': errors.num_autorisation_contrat }"
                />
                <small v-if="errors.num_autorisation_contrat" class="p-error">{{
                    errors.num_autorisation_contrat[0]
                }}</small>
            </div>
            <div class="field">
                <label for="date_debut_contrat">Date début contrat</label>
                <Calendar
                    id="date_debut_contrat"
                    v-model="form.date_debut_contrat"
                    dateFormat="yy-mm-dd"
                    :class="{ 'p-invalid': errors.date_debut_contrat }"
                />
                <small v-if="errors.date_debut_contrat" class="p-error">{{
                    errors.date_debut_contrat[0]
                }}</small>
            </div>
            <div class="field">
                <label for="date_fin_contrat">Date fin contrat</label>
                <Calendar
                    id="date_fin_contrat"
                    v-model="form.date_fin_contrat"
                    dateFormat="yy-mm-dd"
                    :class="{ 'p-invalid': errors.date_fin_contrat }"
                />
                <small v-if="errors.date_fin_contrat" class="p-error">{{
                    errors.date_fin_contrat[0]
                }}</small>
            </div>
        </div>

        <div class="p-dialog-footer">
            <Button
                label="Annuler"
                icon="pi pi-times"
                text
                @click="closeForm"
            />
            <Button
                :label="personnelToEdit ? 'Mettre à jour' : 'Créer'"
                icon="pi pi-check"
                :loading="saving"
                @click="submitForm"
            />
        </div>
    </Dialog>
</template>

<script>
import { useToast } from 'primevue/usetoast';
import axios from '@/axios.js';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import InputNumber from 'primevue/inputnumber';
import debounce from 'lodash/debounce';

export default {
    components: {
        Button,
        InputText,
        Dialog,
        Calendar,
        Dropdown,
        Textarea,
        InputNumber,
    },
    props: {
        visible: {
            type: Boolean,
            default: false,
        },
        personnelToEdit: {
            type: Object,
            default: null,
        },
        centres: {
            type: Array,
            default: () => [],
        },
        roles: {
            type: Array,
            default: () => [],
        },
        centresLoading: {
            type: Boolean,
            default: false,
        },
        rolesLoading: {
            type: Boolean,
            default: false,
        },
        isCentral: {
            type: Boolean,
            default: false,
        },
        userCentreId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            form: this.getDefaultForm(),
            saving: false,
            errors: {},
            searchQuery: '',
            searchResult: null,
            searchError: null,
            searching: false,
        };
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    watch: {
        personnelToEdit(newVal) {
            if (newVal) {
                this.loadPersonnelData(newVal);
            } else {
                this.resetForm();
            }
        },
    },
    methods: {
        getDefaultForm() {
            return {
                name: '',
                email: '',
                role: null,
                matricule: null,
                nom_fr: null,
                prenom_fr: null,
                nom_ar: null,
                prenom_ar: null,
                cin: null,
                date_naissance: null,
                lieu_naissance_fr: null,
                lieu_naissance_ar: null,
                nationalite: 'Tunisienne',
                sexe: null,
                adresse_fr: null,
                adresse_ar: null,
                gouvernorat_fr: null,
                gouvernorat_ar: null,
                delegation_fr: null,
                delegation_ar: null,
                tele1: null,
                tele2: null,
                etat_civil: null,
                fonction_principale: null,
                charge_par: null,
                statut_personnel: 'Actif',
                observations: null,
                centre_id: this.isCentral ? null : this.userCentreId,
                poste: null,
                type_affectation: 'Principale',
                affectation_statut: 'Actif',
                date_debut: null,
                date_fin: null,
                code_niveau: null,
                regime_horaire: null,
                rabattement: null,
                salaire_base: null,
                grade: null,
                categorie: null,
                echelle: null,
                echelon: null,
                banque: null,
                numero_compte: null,
                niveau_etude: null,
                specialite_diplome_fr: null,
                specialite_diplome_ar: null,
                code_secteur: null,
                code_sous_secteur: null,
                type: 'Titulaire',
                date_embauche: null,
                type_contrat: null,
                num_contrat: null,
                num_autorisation_contrat: null,
                date_debut_contrat: null,
                date_fin_contrat: null,
                existing_personnel_id: null,
            };
        },
        loadPersonnelData(personnel) {
            const activeCentre =
                personnel.personnel_centres?.find(
                    (pc) => pc.statut === 'Actif'
                ) || personnel.personnel_centres?.[0];
            this.form = {
                name: personnel.user?.name || '',
                email: personnel.user?.email || '',
                role: personnel.user?.roles[0]?.name || null,
                matricule: personnel.matricule || null,
                nom_fr: personnel.nom_fr || null,
                prenom_fr: personnel.prenom_fr || null,
                nom_ar: personnel.nom_ar || null,
                prenom_ar: personnel.prenom_ar || null,
                cin: personnel.cin || null,
                date_naissance: personnel.date_naissance
                    ? new Date(personnel.date_naissance)
                    : null,
                lieu_naissance_fr: personnel.lieu_naissance_fr || null,
                lieu_naissance_ar: personnel.lieu_naissance_ar || null,
                nationalite: personnel.nationalite || 'Tunisienne',
                sexe: personnel.sexe || null,
                adresse_fr: personnel.adresse_fr || null,
                adresse_ar: personnel.adresse_ar || null,
                gouvernorat_fr: personnel.gouvernorat_fr || null,
                gouvernorat_ar: personnel.gouvernorat_ar || null,
                delegation_fr: personnel.delegation_fr || null,
                delegation_ar: personnel.delegation_ar || null,
                tele1: personnel.tele1 || null,
                tele2: personnel.tele2 || null,
                etat_civil: personnel.etat_civil || null,
                fonction_principale: personnel.fonction_principale || null,
                charge_par: personnel.charge_par || null,
                statut_personnel: personnel.statut || 'Actif',
                observations: personnel.observations || null,
                centre_id: activeCentre
                    ? activeCentre.centre_id
                    : this.isCentral
                      ? null
                      : this.userCentreId,
                poste: activeCentre?.poste || null,
                type_affectation:
                    activeCentre?.type_affectation || 'Principale',
                affectation_statut: activeCentre?.statut || 'Inactif',
                date_debut: activeCentre?.date_debut
                    ? new Date(activeCentre.date_debut)
                    : null,
                date_fin: activeCentre?.date_fin
                    ? new Date(activeCentre.date_fin)
                    : null,
                code_niveau: activeCentre?.code_niveau || null,
                regime_horaire: activeCentre?.regime_horaire || null,
                rabattement: activeCentre?.rabattement || null,
                salaire_base: activeCentre?.salaire_base || null,
                grade: activeCentre?.grade || null,
                categorie: activeCentre?.categorie || null,
                echelle: activeCentre?.echelle || null,
                echelon: activeCentre?.echelon || null,
                banque: activeCentre?.banque || null,
                numero_compte: activeCentre?.numero_compte || null,
                niveau_etude: activeCentre?.niveau_etude || null,
                specialite_diplome_fr:
                    activeCentre?.specialite_diplome_fr || null,
                specialite_diplome_ar:
                    activeCentre?.specialite_diplome_ar || null,
                code_secteur: activeCentre?.code_secteur || null,
                code_sous_secteur: activeCentre?.code_sous_secteur || null,
                type: activeCentre?.type || 'Titulaire',
                date_embauche: activeCentre?.date_embauche
                    ? new Date(activeCentre.date_embauche)
                    : null,
                type_contrat: activeCentre?.type_contrat || null,
                num_contrat: activeCentre?.num_contrat || null,
                num_autorisation_contrat:
                    activeCentre?.num_autorisation_contrat || null,
                date_debut_contrat: activeCentre?.date_debut_contrat
                    ? new Date(activeCentre.date_debut_contrat)
                    : null,
                date_fin_contrat: activeCentre?.date_fin_contrat
                    ? new Date(activeCentre.date_fin_contrat)
                    : null,
                existing_personnel_id: personnel.id,
            };
            this.searchResult = null;
        },
        resetForm() {
            this.form = this.getDefaultForm();
            this.errors = {};
            this.searchResult = null;
            this.searchError = null;
            this.searchQuery = '';
        },
        closeForm() {
            this.updateVisible(false);
        },
        updateVisible(value) {
            this.$emit('update:visible', value);
        },
        debounceSearch: debounce(function () {
            this.searchPersonnel();
        }, 500),
        async searchPersonnel() {
            if (!this.searchQuery) return;
            this.searching = true;
            this.searchError = null;
            this.searchResult = null;

            try {
                const userId = localStorage.getItem('user_id');
                const response = await axios.get('/personnels/search', {
                    params: {
                        matricule: this.searchQuery,
                        cin: this.searchQuery,
                    },
                    headers: {
                        'X-User-ID': userId,
                    },
                });
                if (response.data.status === 'success') {
                    this.searchResult = response.data.personnel;
                    const activeCentre =
                        this.searchResult.personnel_centres?.find(
                            (pc) => pc.statut === 'Actif'
                        );
                    this.form = {
                        ...this.form,
                        matricule: this.searchResult.matricule,
                        nom_fr: this.searchResult.nom_fr,
                        prenom_fr: this.searchResult.prenom_fr,
                        nom_ar: this.searchResult.nom_ar,
                        prenom_ar: this.searchResult.prenom_ar,
                        cin: this.searchResult.cin,
                        date_naissance: this.searchResult.date_naissance
                            ? new Date(this.searchResult.date_naissance)
                            : null,
                        lieu_naissance_fr: this.searchResult.lieu_naissance_fr,
                        lieu_naissance_ar: this.searchResult.lieu_naissance_ar,
                        nationalite: this.searchResult.nationalite,
                        sexe: this.searchResult.sexe,
                        adresse_fr: this.searchResult.adresse_fr,
                        adresse_ar: this.searchResult.adresse_ar,
                        gouvernorat_fr: this.searchResult.gouvernorat_fr,
                        gouvernorat_ar: this.searchResult.gouvernorat_ar,
                        delegation_fr: this.searchResult.delegation_fr,
                        delegation_ar: this.searchResult.delegation_ar,
                        tele1: this.searchResult.tele1,
                        tele2: this.searchResult.tele2,
                        etat_civil: this.searchResult.etat_civil,
                        fonction_principale:
                            this.searchResult.fonction_principale,
                        charge_par: this.searchResult.charge_par,
                        statut_personnel: this.searchResult.statut,
                        observations: this.searchResult.observations,
                        name: this.searchResult.user?.name || '',
                        email: this.searchResult.user?.email || '',
                        role: this.searchResult.user?.roles[0]?.name || null,
                        existing_personnel_id: this.searchResult.id,
                    };
                    if (
                        activeCentre &&
                        activeCentre.centre_id !== this.userCentreId &&
                        !this.isCentral
                    ) {
                        this.searchError = `Ce personnel est déjà actif au centre ${activeCentre.centre?.nom_fr}. Terminez sa mission avant de l'affecter ici.`;
                        this.searchResult = null;
                    }
                }
            } catch (error) {
                this.searchError =
                    error.response?.data?.message || 'Personnel non trouvé';
            } finally {
                this.searching = false;
            }
        },
        async submitForm() {
            this.errors = {};
            try {
                this.saving = true;
                const userId = localStorage.getItem('user_id');
                if (!userId) {
                    throw new Error(
                        'Utilisateur non identifié. Veuillez vous reconnecter.'
                    );
                }

                if (!this.form.matricule || !this.form.affectation_statut) {
                    throw new Error(
                        'Les champs obligatoires doivent être remplis.'
                    );
                }
                if (
                    !this.searchResult &&
                    (!this.form.name || !this.form.email || !this.form.role)
                ) {
                    throw new Error(
                        'Les champs Nom, Email et Rôle sont obligatoires pour un nouveau personnel.'
                    );
                }

                const formData = { ...this.form };
                if (!this.isCentral && this.userCentreId) {
                    formData.centre_id = this.userCentreId;
                }

                // Formatage des dates pour l'envoi au backend
                [
                    'date_naissance',
                    'date_debut',
                    'date_fin',
                    'date_embauche',
                    'date_debut_contrat',
                    'date_fin_contrat',
                ].forEach((key) => {
                    if (formData[key] instanceof Date) {
                        formData[key] = formData[key]
                            .toISOString()
                            .split('T')[0];
                    }
                });

                Object.keys(formData).forEach((key) => {
                    if (formData[key] === null || formData[key] === '') {
                        delete formData[key];
                    }
                });
                if (formData.email)
                    formData.email = formData.email.toLowerCase();

                let response;
                if (this.personnelToEdit) {
                    response = await axios.put(
                        `/personnels/${this.personnelToEdit.id}`,
                        formData
                    );
                    this.$emit('personnel-updated', response.data.personnel);
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Personnel mis à jour.',
                        life: 3000,
                    });
                } else {
                    response = await axios.post('/personnels', formData);
                    if (response.data.status === 'error') {
                        throw new Error(response.data.message);
                    }
                    this.$emit('personnel-created', response.data.personnel);
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail:
                            `Personnel ${this.searchResult ? 'affecté' : 'créé'} !` +
                            (response.data.generated_password
                                ? ` Mot de passe : ${response.data.generated_password}`
                                : ''),
                        life: 5000,
                    });
                }
                this.closeForm();
            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors || {};
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail:
                            error.response?.data?.message ||
                            error.message ||
                            "Erreur lors de l'opération.",
                        life: 3000,
                    });
                }
            } finally {
                this.saving = false;
            }
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
.p-success {
    color: #28a745;
}
.text-red-500 {
    color: #dc3545;
}
</style>
