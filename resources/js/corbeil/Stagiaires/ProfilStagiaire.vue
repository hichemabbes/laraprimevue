<template>
    <div class="grid">
        <div class="col-12">
            <Card>
                <template #title>Profil Personnel</template>
                <template #content>
                    <TabView>
                        <TabPanel header="Informations Personnelles">
                            <div class="p-fluid grid">
                                <div class="field col-12 md:col-6">
                                    <label for="matricule">Matricule</label>
                                    <InputText
                                        id="matricule"
                                        v-model="personnel.matricule"
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
                                        v-model="personnel.nom_fr"
                                    />
                                </div>

                                <div class="field col-12 md:col-6">
                                    <label for="prenom_fr"
                                        >Prénom (Français)</label
                                    >
                                    <InputText
                                        id="prenom_fr"
                                        v-model="personnel.prenom_fr"
                                    />
                                </div>

                                <!-- Ajoutez tous les autres champs de la table personnels ici -->
                                <div class="field col-12 md:col-6">
                                    <label for="cin">CIN</label>
                                    <InputText
                                        id="cin"
                                        v-model="personnel.cin"
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
                                    />
                                </div>

                                <!-- Continuez avec tous les autres champs... -->

                                <div class="col-12 flex justify-content-end">
                                    <Button
                                        label="Enregistrer"
                                        icon="pi pi-save"
                                        class="p-button-success"
                                        @click="updatePersonnel"
                                    />
                                </div>
                            </div>
                        </TabPanel>

                        <TabPanel header="Informations Professionnelles">
                            <div class="p-fluid grid">
                                <div class="field col-12 md:col-6">
                                    <label for="fonction">Fonction</label>
                                    <InputText
                                        id="fonction"
                                        v-model="personnel.fonction"
                                    />
                                </div>

                                <!-- Ajoutez les autres champs professionnels ici -->
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
        const personnel = ref({
            // Initialisez avec tous les champs de la table personnels
            matricule: '',
            nom_fr: '',
            prenom_fr: '',
            // ... tous les autres champs
        });

        const user = ref({
            email: '',
        });

        const passwordForm = ref({
            current_password: '',
            new_password: '',
            new_password_confirmation: '',
        });

        const loadData = async () => {
            try {
                const response = await axios.get('/api/personnel-profil');
                personnel.value = response.data.profile;
                user.value = response.data.user;
            } catch (error) {
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les données du personnel',
                    life: 3000,
                });
            }
        };

        const updatePersonnel = async () => {
            try {
                await axios.put(
                    `/api/personnels/${personnel.value.id}`,
                    personnel.value
                );
                toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Profil mis à jour avec succès',
                    life: 3000,
                });
            } catch (error) {
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec de la mise à jour du profil',
                    life: 3000,
                });
            }
        };

        const updatePassword = async () => {
            // Implémentation similaire à celle du formateur
        };

        onMounted(() => {
            loadData();
        });

        return {
            personnel,
            user,
            passwordForm,
            updatePersonnel,
            updatePassword,
        };
    },
};
</script>
