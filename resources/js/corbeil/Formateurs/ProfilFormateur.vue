<template>
    <div class="grid">
        <div class="col-12">
            <Card>
                <template #title>Profil Formateur</template>
                <template #content>
                    <TabView>
                        <TabPanel header="Informations Personnelles">
                            <div class="p-fluid grid">
                                <div class="field col-12 md:col-6">
                                    <label for="matricule">Matricule</label>
                                    <InputText
                                        id="matricule"
                                        v-model="formateur.matricule"
                                        disabled
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="email">Email</label>
                                    <InputText
                                        id="email"
                                        v-model="user.email"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="nom_fr">Nom (Français)</label>
                                    <InputText
                                        id="nom_fr"
                                        v-model="formateur.nom_fr"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="prenom_fr"
                                        >Prénom (Français)</label
                                    >
                                    <InputText
                                        id="prenom_fr"
                                        v-model="formateur.prenom_fr"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="nom_ar">Nom (Arabe)</label>
                                    <InputText
                                        id="nom_ar"
                                        v-model="formateur.nom_ar"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="prenom_ar"
                                        >Prénom (Arabe)</label
                                    >
                                    <InputText
                                        id="prenom_ar"
                                        v-model="formateur.prenom_ar"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="cin">CIN</label>
                                    <InputText
                                        id="cin"
                                        v-model="formateur.cin"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="date_naissance"
                                        >Date de naissance</label
                                    >
                                    <Calendar
                                        id="date_naissance"
                                        v-model="formateur.date_naissance"
                                        dateFormat="dd/mm/yy"
                                        showIcon
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="lieu_naissance"
                                        >Lieu de naissance</label
                                    >
                                    <InputText
                                        id="lieu_naissance"
                                        v-model="formateur.lieu_naissance"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="tele1">Téléphone 1</label>
                                    <InputText
                                        id="tele1"
                                        v-model="formateur.tele1"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="tele2">Téléphone 2</label>
                                    <InputText
                                        id="tele2"
                                        v-model="formateur.tele2"
                                    />
                                </div>

                                <div class="field col-12">
                                    <label for="adresse_fr"
                                        >Adresse (Français)</label
                                    >
                                    <Textarea
                                        id="adresse_fr"
                                        v-model="formateur.adresse_fr"
                                        rows="3"
                                    />
                                </div>

                                <div class="field col-12">
                                    <label for="adresse_ar"
                                        >Adresse (Arabe)</label
                                    >
                                    <Textarea
                                        id="adresse_ar"
                                        v-model="formateur.adresse_ar"
                                        rows="3"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="gouvernorat">Gouvernorat</label>
                                    <InputText
                                        id="gouvernorat"
                                        v-model="formateur.gouvernorat"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="delegation">Délégation</label>
                                    <InputText
                                        id="delegation"
                                        v-model="formateur.delegation"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="etat_civil">État civil</label>
                                    <Dropdown
                                        id="etat_civil"
                                        v-model="formateur.etat_civil"
                                        :options="etatCivilOptions"
                                        optionLabel="label"
                                        optionValue="value"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="statut">Statut</label>
                                    <Dropdown
                                        id="statut"
                                        v-model="formateur.statut"
                                        :options="statutOptions"
                                        optionLabel="label"
                                        optionValue="value"
                                    />
                                </div>

                                <div class="col-12 flex justify-content-end">
                                    <Button
                                        label="Enregistrer"
                                        icon="pi pi-save"
                                        class="p-button-success"
                                        @click="updateFormateur"
                                    />
                                </div>
                            </div>
                        </TabPanel>

                        <TabPanel header="Informations Professionnelles">
                            <div class="p-fluid grid">
                                <div class="field col-12 md:col-6">
                                    <label for="niveau_etude"
                                        >Niveau d'étude</label
                                    >
                                    <InputText
                                        id="niveau_etude"
                                        v-model="formateur.niveau_etude"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="specialite_diplome_fr"
                                        >Spécialité diplôme (FR)</label
                                    >
                                    <InputText
                                        id="specialite_diplome_fr"
                                        v-model="
                                            formateur.specialite_diplome_fr
                                        "
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="specialite_diplome_ar"
                                        >Spécialité diplôme (AR)</label
                                    >
                                    <InputText
                                        id="specialite_diplome_ar"
                                        v-model="
                                            formateur.specialite_diplome_ar
                                        "
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="matiere_enseigne_fr"
                                        >Matière enseignée (FR)</label
                                    >
                                    <InputText
                                        id="matiere_enseigne_fr"
                                        v-model="formateur.matiere_enseigne_fr"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="matiere_enseigne_ar"
                                        >Matière enseignée (AR)</label
                                    >
                                    <InputText
                                        id="matiere_enseigne_ar"
                                        v-model="formateur.matiere_enseigne_ar"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="date_prise_fonction"
                                        >Date prise de fonction</label
                                    >
                                    <Calendar
                                        id="date_prise_fonction"
                                        v-model="formateur.date_prise_fonction"
                                        dateFormat="dd/mm/yy"
                                        showIcon
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="date_fin_mission"
                                        >Date fin de mission</label
                                    >
                                    <Calendar
                                        id="date_fin_mission"
                                        v-model="formateur.date_fin_mission"
                                        dateFormat="dd/mm/yy"
                                        showIcon
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="code_niveau">Code niveau</label>
                                    <InputText
                                        id="code_niveau"
                                        v-model="formateur.code_niveau"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="charge_horaire"
                                        >Charge horaire</label
                                    >
                                    <InputNumber
                                        id="charge_horaire"
                                        v-model="formateur.charge_horaire"
                                        mode="decimal"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="rabattement">Rabattement</label>
                                    <InputNumber
                                        id="rabattement"
                                        v-model="formateur.rabattement"
                                        mode="decimal"
                                        :minFractionDigits="2"
                                        :maxFractionDigits="2"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="code_secteur"
                                        >Code secteur</label
                                    >
                                    <InputText
                                        id="code_secteur"
                                        v-model="formateur.code_secteur"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="code_sous_secteur"
                                        >Code sous-secteur</label
                                    >
                                    <InputText
                                        id="code_sous_secteur"
                                        v-model="formateur.code_sous_secteur"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="formateur_conseiller"
                                        >Formateur/Conseiller</label
                                    >
                                    <InputText
                                        id="formateur_conseiller"
                                        v-model="formateur.formateur_conseiller"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="type">Type</label>
                                    <InputText
                                        id="type"
                                        v-model="formateur.type"
                                    />
                                </div>

                                <div class="col-12 flex justify-content-end">
                                    <Button
                                        label="Enregistrer"
                                        icon="pi pi-save"
                                        class="p-button-success"
                                        @click="updateFormateur"
                                    />
                                </div>
                            </div>
                        </TabPanel>

                        <TabPanel header="Mot de passe">
                            <div class="p-fluid grid">
                                <div class="field col-12 md:col-6">
                                    <label for="current_password"
                                        >Mot de passe actuel</label
                                    >
                                    <Password
                                        id="current_password"
                                        v-model="passwordForm.current_password"
                                        :feedback="false"
                                        toggleMask
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="new_password"
                                        >Nouveau mot de passe</label
                                    >
                                    <Password
                                        id="new_password"
                                        v-model="passwordForm.new_password"
                                        :feedback="true"
                                        toggleMask
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="new_password_confirmation"
                                        >Confirmation nouveau mot de
                                        passe</label
                                    >
                                    <Password
                                        id="new_password_confirmation"
                                        v-model="
                                            passwordForm.new_password_confirmation
                                        "
                                        :feedback="false"
                                        toggleMask
                                    />
                                </div>

                                <div class="col-12 flex justify-content-end">
                                    <Button
                                        label="Changer le mot de passe"
                                        icon="pi pi-key"
                                        class="p-button-warning"
                                        @click="updatePassword"
                                    />
                                </div>
                            </div>
                        </TabPanel>
                    </TabView>
                </template>
            </Card>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

export default {
    setup() {
        const toast = useToast();
        const formateur = ref({
            user_id: null,
            matricule: '',
            nom_fr: '',
            prenom_fr: '',
            nom_ar: '',
            prenom_ar: '',
            niveau_etude: '',
            specialite_diplome_fr: '',
            specialite_diplome_ar: '',
            matiere_enseigne_fr: '',
            matiere_enseigne_ar: '',
            date_prise_fonction: null,
            date_fin_mission: null,
            code_niveau: '',
            charge_horaire: null,
            rabattement: null,
            code_secteur: '',
            code_sous_secteur: '',
            cin: '',
            date_naissance: null,
            lieu_naissance: '',
            adresse_fr: '',
            adresse_ar: '',
            gouvernorat: '',
            delegation: '',
            tele1: '',
            tele2: '',
            mail: '',
            etat_civil: '',
            formateur_conseiller: '',
            statut: 'actif',
            type: '',
        });

        const user = ref({
            id: null,
            name: '',
            email: '',
        });

        const passwordForm = ref({
            current_password: '',
            new_password: '',
            new_password_confirmation: '',
        });

        const etatCivilOptions = ref([
            { label: 'Célibataire', value: 'celibataire' },
            { label: 'Marié(e)', value: 'marie' },
            { label: 'Divorcé(e)', value: 'divorce' },
            { label: 'Veuf/Veuve', value: 'veuf' },
        ]);

        const statutOptions = ref([
            { label: 'Actif', value: 'actif' },
            { label: 'Inactif', value: 'inactif' },
            { label: 'En congé', value: 'en_conge' },
        ]);

        const loadFormateurData = async () => {
            try {
                // Récupérer l'ID du formateur connecté (à adapter selon votre système d'authentification)
                const response = await axios.get('/api/formateur-profil');
                formateur.value = response.data.formateur;
                user.value = response.data.user;

                // Convertir les dates string en objets Date pour le calendrier
                if (formateur.value.date_naissance) {
                    formateur.value.date_naissance = new Date(
                        formateur.value.date_naissance
                    );
                }
                if (formateur.value.date_prise_fonction) {
                    formateur.value.date_prise_fonction = new Date(
                        formateur.value.date_prise_fonction
                    );
                }
                if (formateur.value.date_fin_mission) {
                    formateur.value.date_fin_mission = new Date(
                        formateur.value.date_fin_mission
                    );
                }
            } catch (error) {
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les données du formateur',
                    life: 3000,
                });
            }
        };

        const updateFormateur = async () => {
            try {
                // Préparer les données pour l'envoi
                const formateurData = {
                    ...formateur.value,
                    date_naissance: formateur.value.date_naissance
                        ? formateur.value.date_naissance
                              .toISOString()
                              .split('T')[0]
                        : null,
                    date_prise_fonction: formateur.value.date_prise_fonction
                        ? formateur.value.date_prise_fonction
                              .toISOString()
                              .split('T')[0]
                        : null,
                    date_fin_mission: formateur.value.date_fin_mission
                        ? formateur.value.date_fin_mission
                              .toISOString()
                              .split('T')[0]
                        : null,
                };

                const userData = {
                    email: user.value.email,
                    name: formateur.value.nom_fr, // Synchroniser le nom avec le nom_fr
                };

                await axios.put(`/api/formateurs/${formateur.value.id}`, {
                    ...formateurData,
                    ...userData,
                });

                toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Profil mis à jour avec succès',
                    life: 3000,
                });
            } catch (error) {
                let errorMessage = 'Erreur lors de la mise à jour du profil';
                if (
                    error.response &&
                    error.response.data &&
                    error.response.data.message
                ) {
                    errorMessage = error.response.data.message;
                }
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: errorMessage,
                    life: 3000,
                });
            }
        };

        const updatePassword = async () => {
            if (
                passwordForm.value.new_password !==
                passwordForm.value.new_password_confirmation
            ) {
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Les mots de passe ne correspondent pas',
                    life: 3000,
                });
                return;
            }

            try {
                await axios.post('/api/update-password', passwordForm.value);

                toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Mot de passe mis à jour avec succès',
                    life: 3000,
                });

                // Réinitialiser le formulaire
                passwordForm.value = {
                    current_password: '',
                    new_password: '',
                    new_password_confirmation: '',
                };
            } catch (error) {
                let errorMessage =
                    'Erreur lors de la mise à jour du mot de passe';
                if (
                    error.response &&
                    error.response.data &&
                    error.response.data.message
                ) {
                    errorMessage = error.response.data.message;
                }
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: errorMessage,
                    life: 3000,
                });
            }
        };

        onMounted(() => {
            loadFormateurData();
        });

        return {
            formateur,
            user,
            passwordForm,
            etatCivilOptions,
            statutOptions,
            updateFormateur,
            updatePassword,
        };
    },
};
</script>

<style scoped>
.p-field {
    margin-bottom: 1.5rem;
}
</style>
