<template>
    <div class="grid">
        <div class="col-12 md:col-3 lg:col-2 menu-column">
            <Card class="shadow-2 border-round h-full">
                <template #content>
                    <div class="flex flex-column align-items-center gap-3">
                        <!-- Photo de profil -->
                        <div class="relative">
                            <Avatar
                                :image="
                                    profilePhoto ||
                                    'https://ui-avatars.com/api/?name=' +
                                        encodeURIComponent(
                                            `${personnel.nom_fr || 'Hichem'} ${personnel.prenom_fr || ''}`
                                        )
                                "
                                shape="circle"
                                size="xlarge"
                                class="border-2 border-primary shadow-3 profile-photo-large"
                            />
                            <Button
                                icon="pi pi-camera"
                                class="p-button-rounded p-button-sm absolute"
                                style="
                                    bottom: 0;
                                    right: 0;
                                    transform: translate(25%, 25%);
                                "
                                @click="triggerFileInput"
                            />
                            <input
                                type="file"
                                ref="fileInput"
                                accept="image/*"
                                class="hidden"
                                @change="uploadPhoto"
                            />
                        </div>

                        <!-- Nom et rôle sous la photo -->
                        <div class="text-center w-full">
                            <h3 class="text-xl font-bold mb-1">
                                {{
                                    `${personnel.nom_fr || ''} ${personnel.prenom_fr || ''}`
                                }}
                            </h3>
                            <Tag
                                :value="userRole || 'Non défini'"
                                severity="success"
                                class="mb-3"
                            />
                        </div>

                        <!-- Informations principales avec nom du centre en premier -->
                        <Divider />
                        <div class="text-left w-full">
                            <div class="mb-2">
                                <span class="font-semibold">Centre:</span>
                                {{ centreNom || '-' }}
                            </div>
                            <div class="mb-2">
                                <span class="font-semibold">Matricule:</span>
                                {{ personnel.matricule || '-' }}
                            </div>
                            <div class="mb-2">
                                <span class="font-semibold">CIN:</span>
                                {{ personnel.cin || '-' }}
                            </div>
                            <div class="mb-2">
                                <span class="font-semibold">Email:</span>
                                {{ userEmail || '-' }}
                            </div>
                            <div class="mb-2">
                                <span class="font-semibold">Téléphone:</span>
                                {{ personnel.telephone || '-' }}
                            </div>
                            <div>
                                <span class="font-semibold">Statut:</span>
                                <Tag
                                    :value="personnel.statut"
                                    :severity="
                                        getStatusSeverity(personnel.statut)
                                    "
                                    class="ml-1"
                                />
                            </div>
                        </div>

                        <!-- Bouton retour déplacé en bas -->
                        <Button
                            icon="pi pi-arrow-left"
                            label="Retour au dashboard"
                            class="w-full mt-4"
                            outlined
                            @click="$router.push({ name: 'dashboard' })"
                        />
                    </div>
                </template>
            </Card>
        </div>

        <div class="col-12 md:col-9 lg:col-10">
            <Card class="shadow-2 border-round">
                <template #header>
                    <div
                        class="flex justify-content-between align-items-center p-3"
                    >
                        <h2 class="text-2xl font-semibold text-primary m-0">
                            Profil Personnel
                        </h2>
                        <div class="flex gap-2">
                            <Button
                                icon="pi pi-user-edit"
                                :label="isEditing ? 'Annuler' : 'Modifier'"
                                size="small"
                                :severity="isEditing ? 'secondary' : 'info'"
                                @click="toggleEditMode"
                            />
                        </div>
                    </div>
                </template>
                <template #content>
                    <ScrollPanel
                        style="height: calc(100vh - 200px)"
                        class="custom-scrollbar"
                    >
                        <TabView>
                            <!-- Onglet Informations Personnelles -->
                            <TabPanel header="Informations Personnelles">
                                <div class="p-fluid grid">
                                    <div class="field col-12 md:col-6">
                                        <label for="matricule">Matricule</label>
                                        <InputText
                                            id="matricule"
                                            v-model="personnel.matricule"
                                            :disabled="
                                                !!personnel.id || !isEditing
                                            "
                                        />
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="email"
                                            >Email professionnel</label
                                        >
                                        <InputText
                                            id="email"
                                            v-model="userEmail"
                                            :disabled="!isEditing"
                                        />
                                    </div>

                                    <div class="col-12">
                                        <Divider align="left">
                                            <span class="text-lg font-semibold"
                                                >Noms</span
                                            >
                                        </Divider>
                                    </div>

                                    <div class="field col-12 md:col-6">
                                        <label for="nom_fr"
                                            >Nom (Français)</label
                                        >
                                        <InputText
                                            id="nom_fr"
                                            v-model="personnel.nom_fr"
                                            :disabled="!isEditing"
                                        />
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="prenom_fr"
                                            >Prénom (Français)</label
                                        >
                                        <InputText
                                            id="prenom_fr"
                                            v-model="personnel.prenom_fr"
                                            :disabled="!isEditing"
                                        />
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="nom_ar">Nom (Arabe)</label>
                                        <InputText
                                            id="nom_ar"
                                            v-model="personnel.nom_ar"
                                            :disabled="!isEditing"
                                            dir="rtl"
                                        />
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="prenom_ar"
                                            >Prénom (Arabe)</label
                                        >
                                        <InputText
                                            id="prenom_ar"
                                            v-model="personnel.prenom_ar"
                                            :disabled="!isEditing"
                                            dir="rtl"
                                        />
                                    </div>

                                    <div
                                        class="col-12 flex justify-content-end mt-3"
                                        v-if="isEditing"
                                    >
                                        <Button
                                            label="Enregistrer"
                                            icon="pi pi-save"
                                            class="p-button-success"
                                            @click="updatePersonnel"
                                            :loading="isLoading"
                                        />
                                    </div>
                                </div>
                            </TabPanel>

                            <!-- Nouvel Onglet État Civil -->
                            <TabPanel header="État Civil">
                                <div class="p-fluid grid">
                                    <div class="field col-12 md:col-6">
                                        <label for="cin">CIN</label>
                                        <InputText
                                            id="cin"
                                            v-model="personnel.cin"
                                            :disabled="!isEditing"
                                        />
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="date_naissance"
                                            >Date de naissance</label
                                        >
                                        <Calendar
                                            id="date_naissance"
                                            v-model="personnel.date_naissance"
                                            dateFormat="dd/mm/yy"
                                            showIcon
                                            :disabled="!isEditing"
                                        />
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="lieu_naissance_fr"
                                            >Lieu de naissance (FR)</label
                                        >
                                        <InputText
                                            id="lieu_naissance_fr"
                                            v-model="
                                                personnel.lieu_naissance_fr
                                            "
                                            :disabled="!isEditing"
                                        />
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="lieu_naissance_ar"
                                            >Lieu de naissance (AR)</label
                                        >
                                        <InputText
                                            id="lieu_naissance_ar"
                                            v-model="
                                                personnel.lieu_naissance_ar
                                            "
                                            :disabled="!isEditing"
                                            dir="rtl"
                                        />
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="sexe">Sexe</label>
                                        <Dropdown
                                            id="sexe"
                                            v-model="personnel.sexe"
                                            :options="sexeOptions"
                                            optionLabel="label"
                                            optionValue="value"
                                            :disabled="!isEditing"
                                        />
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="etat_civil"
                                            >État civil</label
                                        >
                                        <Dropdown
                                            id="etat_civil"
                                            v-model="personnel.etat_civil"
                                            :options="etatCivilOptions"
                                            optionLabel="label"
                                            optionValue="value"
                                            :disabled="!isEditing"
                                        />
                                    </div>

                                    <div
                                        class="col-12 flex justify-content-end mt-3"
                                        v-if="isEditing"
                                    >
                                        <Button
                                            label="Enregistrer"
                                            icon="pi pi-save"
                                            class="p-button-success"
                                            @click="updatePersonnel"
                                            :loading="isLoading"
                                        />
                                    </div>
                                </div>
                            </TabPanel>

                            <!-- Nouvel Onglet Coordonnées -->
                            <TabPanel header="Coordonnées">
                                <div class="p-fluid grid">
                                    <div class="field col-12">
                                        <label for="adresse_fr"
                                            >Adresse (FR)</label
                                        >
                                        <Textarea
                                            id="adresse_fr"
                                            v-model="personnel.adresse_fr"
                                            rows="2"
                                            :disabled="!isEditing"
                                        />
                                    </div>
                                    <div class="field col-12">
                                        <label for="adresse_ar"
                                            >Adresse (AR)</label
                                        >
                                        <Textarea
                                            id="adresse_ar"
                                            v-model="personnel.adresse_ar"
                                            rows="2"
                                            :disabled="!isEditing"
                                            dir="rtl"
                                        />
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="gouvernorat"
                                            >Gouvernorat</label
                                        >
                                        <InputText
                                            id="gouvernorat"
                                            v-model="personnel.gouvernorat"
                                            :disabled="!isEditing"
                                        />
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="delegation"
                                            >Délégation</label
                                        >
                                        <InputText
                                            id="delegation"
                                            v-model="personnel.delegation"
                                            :disabled="!isEditing"
                                        />
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="telephone">Téléphone</label>
                                        <InputText
                                            id="telephone"
                                            v-model="personnel.telephone"
                                            :disabled="!isEditing"
                                        />
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="telephone_urgence"
                                            >Téléphone d’urgence</label
                                        >
                                        <InputText
                                            id="telephone_urgence"
                                            v-model="
                                                personnel.telephone_urgence
                                            "
                                            :disabled="!isEditing"
                                        />
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="email"
                                            >Email personnel</label
                                        >
                                        <InputText
                                            id="email"
                                            v-model="personnel.email"
                                            :disabled="!isEditing"
                                        />
                                    </div>

                                    <div
                                        class="col-12 flex justify-content-end mt-3"
                                        v-if="isEditing"
                                    >
                                        <Button
                                            label="Enregistrer"
                                            icon="pi pi-save"
                                            class="p-button-success"
                                            @click="updatePersonnel"
                                            :loading="isLoading"
                                        />
                                    </div>
                                </div>
                            </TabPanel>

                            <!-- Onglet Informations Professionnelles -->
                            <TabPanel header="Informations Professionnelles">
                                <div class="p-fluid grid">
                                    <div class="col-12">
                                        <Divider align="left">
                                            <span class="text-lg font-semibold"
                                                >Poste et statut</span
                                            >
                                        </Divider>
                                    </div>

                                    <div class="field col-12 md:col-6">
                                        <label for="niveau_etude"
                                            >Niveau d'étude</label
                                        >
                                        <Dropdown
                                            id="niveau_etude"
                                            v-model="personnel.niveau_etude"
                                            :options="niveauEtudeOptions"
                                            :disabled="!isEditing"
                                        />
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="specialite"
                                            >Spécialité</label
                                        >
                                        <InputText
                                            id="specialite"
                                            v-model="personnel.specialite"
                                            :disabled="!isEditing"
                                        />
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="fonction">Fonction</label>
                                        <InputText
                                            id="fonction"
                                            v-model="personnel.fonction"
                                            :disabled="!isEditing"
                                        />
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="type_contrat"
                                            >Type de contrat</label
                                        >
                                        <Dropdown
                                            id="type_contrat"
                                            v-model="personnel.type_contrat"
                                            :options="typeContratOptions"
                                            :disabled="!isEditing"
                                        />
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="statut">Statut</label>
                                        <Dropdown
                                            id="statut"
                                            v-model="personnel.statut"
                                            :options="statutOptions"
                                            optionLabel="label"
                                            optionValue="value"
                                            :disabled="!isEditing"
                                        />
                                    </div>

                                    <div class="col-12">
                                        <Divider align="left">
                                            <span class="text-lg font-semibold"
                                                >Dates importantes</span
                                            >
                                        </Divider>
                                    </div>

                                    <div class="field col-12 md:col-6">
                                        <label for="date_embauche"
                                            >Date d’embauche</label
                                        >
                                        <Calendar
                                            id="date_embauche"
                                            v-model="personnel.date_embauche"
                                            dateFormat="dd/mm/yy"
                                            showIcon
                                            :disabled="!isEditing"
                                        />
                                    </div>

                                    <div class="col-12">
                                        <Divider align="left">
                                            <span class="text-lg font-semibold"
                                                >Rémunération</span
                                            >
                                        </Divider>
                                    </div>

                                    <div class="field col-12 md:col-6">
                                        <label for="salaire">Salaire</label>
                                        <InputText
                                            id="salaire"
                                            v-model="personnel.salaire"
                                            :disabled="!isEditing"
                                        />
                                    </div>

                                    <div class="col-12">
                                        <Divider align="left">
                                            <span class="text-lg font-semibold"
                                                >Coordonnées bancaires</span
                                            >
                                        </Divider>
                                    </div>

                                    <div class="field col-12 md:col-6">
                                        <label for="banque">Banque</label>
                                        <Dropdown
                                            id="banque"
                                            v-model="personnel.banque"
                                            :options="banqueOptions"
                                            :disabled="!isEditing"
                                        />
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="numero_compte"
                                            >Numéro de compte bancaire</label
                                        >
                                        <InputText
                                            id="numero_compte"
                                            v-model="personnel.numero_compte"
                                            :disabled="!isEditing"
                                        />
                                    </div>

                                    <div class="col-12">
                                        <Divider align="left">
                                            <span class="text-lg font-semibold"
                                                >Contact d’urgence</span
                                            >
                                        </Divider>
                                    </div>

                                    <div class="field col-12 md:col-6">
                                        <label for="nom_contact_urgence"
                                            >Nom du contact</label
                                        >
                                        <InputText
                                            id="nom_contact_urgence"
                                            v-model="
                                                personnel.nom_contact_urgence
                                            "
                                            :disabled="!isEditing"
                                        />
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="lien_contact_urgence"
                                            >Lien avec le contact</label
                                        >
                                        <InputText
                                            id="lien_contact_urgence"
                                            v-model="
                                                personnel.lien_contact_urgence
                                            "
                                            :disabled="!isEditing"
                                        />
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="telephone_contact_urgence"
                                            >Téléphone du contact</label
                                        >
                                        <InputText
                                            id="telephone_contact_urgence"
                                            v-model="
                                                personnel.telephone_contact_urgence
                                            "
                                            :disabled="!isEditing"
                                        />
                                    </div>

                                    <div class="col-12">
                                        <Divider align="left">
                                            <span class="text-lg font-semibold"
                                                >Observations</span
                                            >
                                        </Divider>
                                    </div>

                                    <div class="field col-12">
                                        <label for="observations"
                                            >Observations</label
                                        >
                                        <Textarea
                                            id="observations"
                                            v-model="personnel.observations"
                                            rows="3"
                                            :disabled="!isEditing"
                                        />
                                    </div>

                                    <div
                                        class="col-12 flex justify-content-end mt-3"
                                        v-if="isEditing"
                                    >
                                        <Button
                                            label="Enregistrer"
                                            icon="pi pi-save"
                                            class="p-button-success"
                                            @click="updatePersonnel"
                                            :loading="isLoading"
                                        />
                                    </div>
                                </div>
                            </TabPanel>

                            <!-- Onglet Sécurité -->
                            <TabPanel header="Sécurité">
                                <div class="p-fluid grid">
                                    <div class="col-12">
                                        <Divider align="left">
                                            <span class="text-lg font-semibold"
                                                >Modification du mot de
                                                passe</span
                                            >
                                        </Divider>
                                    </div>
                                    <div class="col-6">
                                        <Divider align="left">
                                            <span class="text-lg font-semibold"
                                                >Mot de passe actuel</span
                                            >
                                        </Divider>
                                    </div>
                                    <div class="field col-6 md:col-6">
                                        <label for="current_password"></label>
                                        <Password
                                            id="current_password"
                                            v-model="
                                                passwordForm.current_password
                                            "
                                            :feedback="false"
                                            toggleMask
                                            :disabled="!isEditing"
                                        />
                                        <small
                                            class="p-error"
                                            v-if="errors.current_password"
                                            >{{
                                                errors.current_password
                                            }}</small
                                        >
                                    </div>

                                    <div class="col-6">
                                        <Divider align="left">
                                            <span class="text-lg font-semibold"
                                                >Nouveau mot de passe</span
                                            >
                                        </Divider>
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label for="new_password"></label>
                                        <Password
                                            id="new_password"
                                            v-model="passwordForm.new_password"
                                            :feedback="true"
                                            toggleMask
                                            :disabled="!isEditing"
                                        />
                                        <small
                                            class="p-error"
                                            v-if="errors.new_password"
                                            >{{ errors.new_password }}</small
                                        >
                                        <div
                                            class="mt-2"
                                            v-if="passwordForm.new_password"
                                        >
                                            <ProgressBar
                                                :value="
                                                    passwordStrength.score * 25
                                                "
                                                :showValue="false"
                                                :class="passwordStrength.class"
                                            />
                                            <small
                                                >Force du mot de passe:
                                                {{
                                                    passwordStrength.text
                                                }}</small
                                            >
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <Divider align="left">
                                            <span class="text-lg font-semibold"
                                                >Confirmation nouveau mot de
                                                passe</span
                                            >
                                        </Divider>
                                    </div>
                                    <div class="field col-12 md:col-6">
                                        <label
                                            for="new_password_confirmation"
                                        ></label>
                                        <Password
                                            id="new_password_confirmation"
                                            v-model="
                                                passwordForm.new_password_confirmation
                                            "
                                            :feedback="false"
                                            toggleMask
                                            :disabled="!isEditing"
                                        />
                                        <small
                                            class="p-error"
                                            v-if="
                                                errors.new_password_confirmation
                                            "
                                            >{{
                                                errors.new_password_confirmation
                                            }}</small
                                        >
                                    </div>

                                    <div
                                        class="col-12 flex justify-content-end mt-3"
                                    >
                                        <Button
                                            label="Changer le mot de passe"
                                            icon="pi pi-key"
                                            class="p-button-warning"
                                            :loading="isLoadingPassword"
                                            :disabled="
                                                !isPasswordFormValid ||
                                                !isEditing
                                            "
                                            @click="updatePassword"
                                        />
                                    </div>
                                </div>
                            </TabPanel>
                        </TabView>
                    </ScrollPanel>
                </template>
            </Card>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useToast } from 'primevue/usetoast';
import { useRouter } from 'vue-router';
import axios from '@/axios.js';

// Components
import Card from 'primevue/card';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import Password from 'primevue/password';
import Avatar from 'primevue/avatar';
import Tag from 'primevue/tag';
import Divider from 'primevue/divider';
import ScrollPanel from 'primevue/scrollpanel';
import ProgressBar from 'primevue/progressbar';

const props = defineProps({
    user: Object,
    personnel: Object,
    centre: Object,
});

const toast = useToast();
const router = useRouter();
const isLoading = ref(false);
const isLoadingPassword = ref(false);
const isEditing = ref(false);
const profilePhoto = ref(null);
const userEmail = ref(props.user?.email || '');
const userRole = ref('');
const centreNom = ref('');
const fileInput = ref(null);

// Données du personnel
const personnel = ref({
    matricule: '',
    nom_fr: '',
    prenom_fr: '',
    nom_ar: '',
    prenom_ar: '',
    cin: '',
    date_naissance: null,
    lieu_naissance_fr: '',
    lieu_naissance_ar: '',
    sexe: '',
    etat_civil: '',
    adresse_fr: '',
    adresse_ar: '',
    gouvernorat: '',
    delegation: '',
    telephone: '',
    telephone_urgence: '',
    email: '',
    niveau_etude: '',
    specialite: '',
    fonction: '',
    type_contrat: '',
    date_embauche: null,
    salaire: '',
    banque: '',
    numero_compte: '',
    nom_contact_urgence: '',
    lien_contact_urgence: '',
    telephone_contact_urgence: '',
    statut: 'Actif',
    observations: '',
    is_directeur: false,
    photo: '',
});

// Formulaires
const passwordForm = ref({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

// Gestion des erreurs pour l'onglet Sécurité
const errors = ref({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

// Options des listes déroulantes
const sexeOptions = ref([
    { label: 'Masculin', value: 'M' },
    { label: 'Féminin', value: 'F' },
]);

const etatCivilOptions = ref([
    { label: 'Célibataire', value: 'celibataire' },
    { label: 'Marié(e)', value: 'marie' },
    { label: 'Divorcé(e)', value: 'divorce' },
    { label: 'Veuf/Veuve', value: 'veuf' },
]);

const statutOptions = ref([
    { label: 'Actif', value: 'Actif' },
    { label: 'En congé', value: 'En congé' },
    { label: 'Démissionnaire', value: 'Démissionnaire' },
    { label: 'Licencié', value: 'Licencié' },
    { label: 'Retraité', value: 'Retraité' },
]);

const niveauEtudeOptions = ref([
    'Primaire',
    'Secondaire',
    'Baccalauréat',
    'Bac +2',
    'Licence',
    'Master',
    'Doctorat',
    'Ingénieur',
]);

const typeContratOptions = ref([
    'CDI',
    'CDD',
    'Stage',
    'Freelance',
    'Temporaire',
]);

const banqueOptions = ref([
    'BNA',
    'BIAT',
    'ATB',
    'BT',
    'STB',
    'BH',
    'UBCI',
    'Amen Bank',
]);

// Récupérer le profil avec rôle et centre
const fetchProfile = async () => {
    try {
        const response = await axios.get('/profile/data');
        if (response.data.status === 'success') {
            const newUser = response.data.user;
            const newPersonnel = response.data.personnel || {};

            userEmail.value = newUser.email;
            personnel.value = {
                ...personnel.value,
                matricule: newPersonnel.matricule || '',
                nom_fr:
                    newPersonnel.nom_fr || newUser.name?.split(' ')[0] || '',
                prenom_fr:
                    newPersonnel.prenom_fr || newUser.name?.split(' ')[1] || '',
                nom_ar: newPersonnel.nom_ar || '',
                prenom_ar: newPersonnel.prenom_ar || '',
                cin: newPersonnel.cin || '',
                date_naissance: newPersonnel.date_naissance
                    ? new Date(newPersonnel.date_naissance)
                    : null,
                lieu_naissance_fr: newPersonnel.lieu_naissance_fr || '',
                lieu_naissance_ar: newPersonnel.lieu_naissance_ar || '',
                sexe: newPersonnel.sexe || '',
                etat_civil: newPersonnel.etat_civil || '',
                adresse_fr: newPersonnel.adresse_fr || '',
                adresse_ar: newPersonnel.adresse_ar || '',
                gouvernorat: newPersonnel.gouvernorat || '',
                delegation: newPersonnel.delegation || '',
                telephone: newPersonnel.telephone || '',
                telephone_urgence: newPersonnel.telephone_urgence || '',
                email: newPersonnel.email || '',
                niveau_etude: newPersonnel.niveau_etude || '',
                specialite: newPersonnel.specialite || '',
                fonction: newPersonnel.fonction || '',
                type_contrat: newPersonnel.type_contrat || '',
                date_embauche: newPersonnel.date_embauche
                    ? new Date(newPersonnel.date_embauche)
                    : null,
                salaire: newPersonnel.salaire || '',
                banque: newPersonnel.banque || '',
                numero_compte: newPersonnel.numero_compte || '',
                nom_contact_urgence: newPersonnel.nom_contact_urgence || '',
                lien_contact_urgence: newPersonnel.lien_contact_urgence || '',
                telephone_contact_urgence:
                    newPersonnel.telephone_contact_urgence || '',
                statut: newPersonnel.statut || 'Actif',
                observations: newPersonnel.observations || '',
                is_directeur: newPersonnel.is_directeur || false,
                photo: newPersonnel.photo || '',
            };
            profilePhoto.value = response.data.photo_url || null;

            // Récupérer le rôle
            const roleResponse = await axios.get(`/user/${newUser.id}/role`);
            userRole.value = roleResponse.data.role || 'Non défini';

            // Récupérer le centre
            const centreResponse = await axios.get(
                `/user/${newUser.id}/centre`
            );
            centreNom.value = centreResponse.data.nom_fr || '-';

            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Profil rafraîchi avec succès',
                life: 3000,
            });
        }
    } catch (error) {
        console.error('Erreur lors du rafraîchissement:', error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Impossible de rafraîchir le profil',
            life: 3000,
        });
    }
};

// Initialisation des données
watch(
    () => [props.user, props.personnel, props.centre],
    ([newUser, newPersonnel, newCentre]) => {
        if (newUser) {
            userEmail.value = newUser.email || '';
            personnel.value = { ...newPersonnel };
            profilePhoto.value = newPersonnel?.photo_url || null;
            centreNom.value = newCentre?.nom_fr || '-';
            fetchProfile(); // Charger rôle et centre au démarrage
        }
    },
    { immediate: true }
);

// Calcul de la force du mot de passe
const passwordStrength = computed(() => {
    const password = passwordForm.value.new_password;
    if (!password)
        return { score: 0, text: 'Faible', class: 'low-strength', error: '' };

    const hasMinLength = password.length >= 8;
    const hasNumber = /\d/.test(password);
    const hasUpper = /[A-Z]/.test(password);
    const hasLower = /[a-z]/.test(password);
    const hasSpecial = /[^A-Za-z0-9]/.test(password);

    let score = 0;
    let error = '';

    if (!hasMinLength) {
        error = 'Le mot de passe doit contenir au moins 8 caractères';
    } else {
        score += 1;
        if (hasNumber) score += 1;
        if (hasUpper) score += 1;
        if (hasLower) score += 1;
        if (hasSpecial) score += 1;
    }

    let text, pClass;
    if (score < 2) {
        text = 'Très faible';
        pClass = 'low-strength';
    } else if (score < 4) {
        text = 'Faible';
        pClass = 'medium-low-strength';
    } else if (score < 5) {
        text = 'Moyen';
        pClass = 'medium-strength';
    } else {
        text = 'Fort';
        pClass = 'high-strength';
    }

    return { score, text, class: pClass, error };
});

// Validation du formulaire de mot de passe
const isPasswordFormValid = computed(() => {
    errors.value = {
        current_password: '',
        new_password: '',
        new_password_confirmation: '',
    };

    let isValid = true;

    if (!passwordForm.value.current_password) {
        errors.value.current_password = 'Le mot de passe actuel est requis';
        isValid = false;
    }

    if (!passwordForm.value.new_password) {
        errors.value.new_password = 'Le nouveau mot de passe est requis';
        isValid = false;
    } else if (passwordStrength.value.error) {
        errors.value.new_password = passwordStrength.value.error;
        isValid = false;
    }

    if (!passwordForm.value.new_password_confirmation) {
        errors.value.new_password_confirmation =
            'La confirmation du mot de passe est requise';
        isValid = false;
    } else if (
        passwordForm.value.new_password !==
        passwordForm.value.new_password_confirmation
    ) {
        errors.value.new_password_confirmation =
            'Les mots de passe ne correspondent pas';
        isValid = false;
    }

    return isValid;
});

// Méthodes
const toggleEditMode = () => {
    isEditing.value = !isEditing.value;
    if (!isEditing.value) {
        fetchProfile();
        // Réinitialiser les erreurs et le formulaire de mot de passe
        errors.value = {
            current_password: '',
            new_password: '',
            new_password_confirmation: '',
        };
        passwordForm.value = {
            current_password: '',
            new_password: '',
            new_password_confirmation: '',
        };
    }
};

const updatePersonnel = async () => {
    isLoading.value = true;
    try {
        const dataToSend = {
            ...personnel.value,
            date_naissance: personnel.value.date_naissance
                ?.toISOString()
                .split('T')[0],
            date_embauche: personnel.value.date_embauche
                ?.toISOString()
                .split('T')[0],
            email: userEmail.value,
        };

        const response = await axios.put('/profile/update', dataToSend);

        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Profil mis à jour avec succès',
            life: 3000,
        });

        isEditing.value = false;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail:
                error.response?.data?.message ||
                'Erreur lors de la mise à jour',
            life: 3000,
        });
    } finally {
        isLoading.value = false;
    }
};

const updatePassword = async () => {
    if (!isPasswordFormValid.value) {
        return;
    }

    isLoadingPassword.value = true;
    try {
        await axios.post('/profile/update-password', passwordForm.value);

        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Mot de passe mis à jour avec succès',
            life: 3000,
        });

        passwordForm.value = {
            current_password: '',
            new_password: '',
            new_password_confirmation: '',
        };
        errors.value = {
            current_password: '',
            new_password: '',
            new_password_confirmation: '',
        };
        isEditing.value = false;
    } catch (error) {
        const errorMsg =
            error.response?.data?.message ||
            'Erreur lors de la mise à jour du mot de passe';
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: errorMsg,
            life: 3000,
        });

        // Gestion des erreurs spécifiques du serveur
        if (error.response?.data?.errors) {
            errors.value = {
                current_password:
                    error.response.data.errors.current_password?.[0] || '',
                new_password:
                    error.response.data.errors.new_password?.[0] || '',
                new_password_confirmation:
                    error.response.data.errors.new_password_confirmation?.[0] ||
                    '',
            };
        }
    } finally {
        isLoadingPassword.value = false;
    }
};

const triggerFileInput = () => {
    fileInput.value.click();
};

const uploadPhoto = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    try {
        const formData = new FormData();
        formData.append('photo', file);

        const response = await axios.post('/profile/upload-photo', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        profilePhoto.value = response.data.photo_url;
        personnel.value.photo = response.data.photo_url
            .split('/')
            .pop()
            .split('?')[0]; // Extrait le nom du fichier sans le timestamp
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Photo mise à jour',
            life: 3000,
        });

        await fetchProfile(); // Rafraîchir pour persister la photo
    } catch (error) {
        console.error('Erreur lors de l’upload:', error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: error.response?.data?.message || 'Échec du téléchargement',
            life: 3000,
        });
    }
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'Actif':
            return 'success';
        case 'En congé':
            return 'info';
        case 'Démissionnaire':
            return 'warning';
        case 'Licencié':
            return 'danger';
        case 'Retraité':
            return 'secondary';
        default:
            return 'info';
    }
};
</script>

<style scoped>
.field {
    margin-bottom: 1.25rem;
}

.shadow-2 {
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
}

.border-round {
    border-radius: 12px;
}

.relative {
    position: relative;
}

.profile-photo-large {
    width: 240px !important;
    height: 240px !important;
    object-fit: cover;
}

.hidden {
    display: none;
}

.menu-column {
    border-right: 1px solid #e5e7eb;
}

:deep(.p-tabview-nav) {
    border-bottom: 1px solid #e5e7eb;
}

:deep(.p-tabview-nav-link) {
    padding: 1rem 1.5rem;
    font-weight: 500;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

:deep(.low-strength) .p-progressbar-value {
    background-color: #ef4444;
}

:deep(.medium-low-strength) .p-progressbar-value {
    background-color: #f59e0b;
}

:deep(.medium-strength) .p-progressbar-value {
    background-color: #3b82f6;
}

:deep(.high-strength) .p-progressbar-value {
    background-color: #10b981;
}

@media (max-width: 960px) {
    .col-12.md\:col-3 {
        margin-bottom: 1.5rem;
    }
    .menu-column {
        border-right: none;
        border-bottom: 1px solid #e5e7eb;
    }
}
</style>
