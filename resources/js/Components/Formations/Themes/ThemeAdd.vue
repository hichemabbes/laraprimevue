<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :modal="true"
        :style="{ width: '80vw', maxWidth: '900px' }"
        class="p-fluid"
    >
        <template #header>
            <div class="flex align-items-center gap-2">
                <i class="pi pi-list text-primary text-2xl"></i>
                <span class="font-bold text-2xl">Ajouter un Thème</span>
            </div>
        </template>

        <form @submit.prevent="submitForm">
            <TabView v-model:activeIndex="activeTabIndex">
                <TabPanel header="Informations Générales">
                    <div class="grid p-fluid">

                        <div class="col-12 md:col-6 field">
                            <label>Nom (FR) *</label>
                            <InputText v-model="form.nom_fr" :class="{ 'p-invalid': errors.nom_fr }" />
                            <small v-if="errors.nom_fr" class="p-error">{{ errors.nom_fr[0] }}</small>
                        </div>

                        <div class="col-12 md:col-6 field">
                            <label class="font-arabic">الاسم (العربية) *</label>
                            <InputText dir="rtl" v-model="form.nom_ar" :class="{ 'p-invalid': errors.nom_ar }" />
                            <small v-if="errors.nom_ar" class="p-error">{{ errors.nom_ar[0] }}</small>
                        </div>

                        <div class="col-12 md:col-6 field">
                            <label>Spécialité liée *</label>
                            <Dropdown
                                v-model="form.specialite_id"
                                :options="specialites"
                                optionLabel="nom_fr"
                                optionValue="id"
                                placeholder="Sélectionner une spécialité"
                                :class="{ 'p-invalid': errors.specialite_id }"
                            />
                            <small v-if="errors.specialite_id" class="p-error">{{ errors.specialite_id[0] }}</small>
                        </div>

                        <div class="col-12 md:col-6 field">
                            <label>Statut *</label>
                            <Dropdown
                                v-model="form.statut"
                                :options="['Actif','Inactif']"
                                placeholder="Statut"
                            />
                        </div>
                    </div>
                </TabPanel>

                <TabPanel header="Descriptions">
                    <div class="grid p-fluid">
                        <div class="col-12 field">
                            <label>Description (FR)</label>
                            <Textarea rows="4" autoResize v-model="form.description_fr" />
                        </div>

                        <div class="col-12 field">
                            <label class="font-arabic">الوصف (العربية)</label>
                            <Textarea rows="4" autoResize dir="rtl" v-model="form.description_ar" />
                        </div>
                    </div>
                </TabPanel>
            </TabView>
        </form>

        <template #footer>
            <Button
                label="Annuler"
                class="p-button-text"
                icon="pi pi-times"
                @click="$emit('update:visible', false)"
            />
            <Button
                label="Enregistrer"
                icon="pi pi-check"
                class="p-button-success"
                @click="submitForm"
                :loading="saving"
            />
        </template>
    </Dialog>
</template>

<script>
import axios from 'axios';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

export default {
    props: { visible: Boolean },
    emits: ['update:visible', 'save'],
    components: { InputText, Dropdown, Textarea, TabView, TabPanel, Button },
    setup() {
        return { toast: useToast() };
    },
    data() {
        return {
            form: {
                nom_fr: '',
                nom_ar: '',
                specialite_id: null,
                description_fr: '',
                description_ar: '',
                statut: 'Actif',
            },
            errors: {},
            activeTabIndex: 0,
            saving: false,
            specialites: [],
        };
    },
    created() {
        this.fetchSpecialites();
    },
    methods: {
        async fetchSpecialites() {
            const res = await axios.get('/api/specialites');
            this.specialites = res.data;
        },
        async submitForm() {
            this.saving = true;
            this.errors = {};

            try {
                const res = await axios.post('/api/themes', this.form);
                this.$emit('save', res.data);

                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Thème ajouté.',
                });

                this.$emit('update:visible', false);
            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors;
                }
            } finally {
                this.saving = false;
            }
        },
    },
};
</script>

<style scoped>
.font-arabic {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
}
</style>
