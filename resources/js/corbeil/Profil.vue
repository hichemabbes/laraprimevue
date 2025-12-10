<template>
    <div class="card p-4">
        <div class="flex justify-content-between align-items-center mb-4">
            <h2>Profil Utilisateur</h2>
        </div>

        <div class="flex align-items-center mb-4">
            <div class="relative">
                <Avatar
                    :image="
                        profilePhoto ||
                        'https://ui-avatars.com/api/?name=' +
                            encodeURIComponent(
                                `${personnel.nom_fr || 'Utilisateur'} ${personnel.prenom_fr || ''}`
                            )
                    "
                    shape="circle"
                    size="xlarge"
                    class="border-2 border-primary shadow-3 profile-photo-large"
                />
                <Button
                    icon="pi pi-camera"
                    class="p-button-rounded p-button-text absolute bottom-0 right-0"
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
            <div class="ml-4">
                <h3>{{ personnel.nom_fr }} {{ personnel.prenom_fr }}</h3>
                <p>{{ user.email }}</p>
                <Tag :value="role" severity="info" />
            </div>
        </div>

        <TabView>
            <TabPanel header="Informations Personnelles">
                <div class="p-fluid grid">
                    <div class="col-12 md:col-6">
                        <div class="field">
                            <label for="nom_fr">Nom (FR)</label>
                            <InputText id="nom_fr" v-model="personnel.nom_fr" />
                        </div>
                    </div>
                    <div class="col-12 md:col-6">
                        <div class="field">
                            <label for="prenom_fr">Prénom (FR)</label>
                            <InputText
                                id="prenom_fr"
                                v-model="personnel.prenom_fr"
                            />
                        </div>
                    </div>
                    <div class="col-12 md:col-6">
                        <div class="field">
                            <label for="telephone">Téléphone</label>
                            <InputText
                                id="telephone"
                                v-model="personnel.telephone"
                            />
                        </div>
                    </div>
                    <div class="col-12 md:col-6">
                        <div class="field">
                            <label for="email">Email</label>
                            <InputText id="email" v-model="user.email" />
                        </div>
                    </div>
                </div>
                <Button
                    label="Enregistrer"
                    icon="pi pi-check"
                    @click="updateProfile"
                    :loading="saving"
                />
            </TabPanel>
            <TabPanel header="Changer Mot de Passe">
                <div class="p-fluid grid">
                    <div class="col-12 md:col-6">
                        <div class="field">
                            <label for="current_password"
                                >Mot de passe actuel</label
                            >
                            <InputText
                                id="current_password"
                                type="password"
                                v-model="password.current_password"
                            />
                        </div>
                    </div>
                    <div class="col-12 md:col-6">
                        <div class="field">
                            <label for="new_password"
                                >Nouveau mot de passe</label
                            >
                            <InputText
                                id="new_password"
                                type="password"
                                v-model="password.new_password"
                            />
                        </div>
                    </div>
                    <div class="col-12 md:col-6">
                        <div class="field">
                            <label for="new_password_confirmation"
                                >Confirmer nouveau mot de passe</label
                            >
                            <InputText
                                id="new_password_confirmation"
                                type="password"
                                v-model="password.new_password_confirmation"
                            />
                        </div>
                    </div>
                </div>
                <Button
                    label="Changer Mot de Passe"
                    icon="pi pi-lock"
                    @click="updatePassword"
                    :loading="savingPassword"
                />
            </TabPanel>
        </TabView>

        <Toast />
    </div>
</template>

<script>
import { useToast } from 'primevue/usetoast';
import axios from '@/axios.js';
import Avatar from 'primevue/avatar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Toast from 'primevue/toast';

export default {
    components: {
        Avatar,
        Button,
        InputText,
        Tag,
        TabView,
        TabPanel,
        Toast,
    },
    data() {
        return {
            user: {},
            personnel: {},
            profilePhoto: null,
            role: null,
            centre: null,
            saving: false,
            savingPassword: false,
            password: {
                current_password: '',
                new_password: '',
                new_password_confirmation: '',
            },
        };
    },
    setup() {
        return { toast: useToast() };
    },
    async created() {
        await this.fetchProfile();
    },
    methods: {
        async fetchProfile() {
            try {
                const userId = localStorage.getItem('user_id');
                const response = await axios.get('/profile/data', {
                    headers: { 'X-User-ID': userId },
                });
                this.user = response.data.user;
                this.personnel = response.data.personnel || {};
                this.profilePhoto = response.data.photo_url;
                this.role = (await axios.get(`/user/${userId}/role`)).data.role;
                this.centre = (
                    await axios.get(`/user/${userId}/centre`)
                ).data.nom_fr;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors du chargement du profil',
                    life: 3000,
                });
            }
        },
        triggerFileInput() {
            this.$refs.fileInput.click();
        },
        async uploadPhoto(event) {
            const file = event.target.files[0];
            if (!file) return;

            try {
                const formData = new FormData();
                formData.append('photo', file);

                const response = await axios.post(
                    '/profile/upload-photo',
                    formData,
                    {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    }
                );

                this.profilePhoto = response.data.photo_url;
                this.personnel.photo = response.data.photo_url.split('/').pop();
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Photo mise à jour',
                    life: 3000,
                });

                await this.fetchProfile(); // Recharger pour persistance
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Échec du téléchargement',
                    life: 3000,
                });
            }
        },
        async updateProfile() {
            this.saving = true;
            try {
                const response = await axios.put('/profile/update', {
                    nom_fr: this.personnel.nom_fr,
                    prenom_fr: this.personnel.prenom_fr,
                    telephone: this.personnel.telephone,
                    email: this.user.email,
                });
                this.personnel = response.data.personnel;
                this.profilePhoto = response.data.photo_url;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Profil mis à jour',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors de la mise à jour',
                    life: 3000,
                });
            } finally {
                this.saving = false;
            }
        },
        async updatePassword() {
            this.savingPassword = true;
            try {
                await axios.post('/profile/update-password', this.password);
                this.password = {
                    current_password: '',
                    new_password: '',
                    new_password_confirmation: '',
                };
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Mot de passe mis à jour',
                    life: 3000,
                });
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors du changement de mot de passe',
                    life: 3000,
                });
            } finally {
                this.savingPassword = false;
            }
        },
    },
};
</script>

<style scoped>
.profile-photo-large {
    width: 100px;
    height: 100px;
}
.relative {
    position: relative;
}
.absolute {
    position: absolute;
}
.hidden {
    display: none;
}
</style>
