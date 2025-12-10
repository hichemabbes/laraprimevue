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
                <i class="pi pi-pencil text-primary text-2xl"></i>
                <span class="font-bold text-2xl">Modifier le Thème</span>
            </div>
        </template>

        <form @submit.prevent="submitForm">
            <TabView v-model:activeIndex="activeTabIndex">

                <TabPanel header="Informations Générales">
                    <div class="grid p-fluid">

                        <div class="col-12 md:col-6 field">
                            <label>Nom (FR) *</label>
                            <InputText v-model="form.nom_fr" :class="{ 'p-invalid': errors.nom_fr }" />
                        </div>

                        <div class="col-12 md:col-6 field">
                            <label class="font-arabic">الاسم (العربية) *</label>
                            <InputText dir="rtl" v-model="form.nom_ar" :class="{ 'p-invalid': errors.nom_ar }" />
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
                        </div>

                        <div class="col-12 md:col-6 field">
                            <label>Statut *</label>
                            <Dropdown :options="['Actif', 'Inactif']" v-model="form.statut" />
                        </div>
                    </div>
                </TabPanel>

                <TabPanel header="Descriptions">
                    <div class="grid p-fluid">

                        <div class="col-12 field">
                            <label>Description (FR)</label>
                            <Textarea rows="5" autoResize v-model="form.description_fr" />
                        </div>

                        <div class="col-12 field">
                            <label class="font-arabic">الوصف (العربية)</label>
                            <Textarea rows="5" autoResize dir="rtl" v-model="form.description_ar" />
                        </div>

                    </div>
                </TabPanel>

            </TabView>
        </form>

        <template #footer>
            <Button label="Annuler" class="p-button-text" @click="$emit('update:visible', false)" />
            <Button
                label="Mettre à jour"
                icon="pi pi-check"
                class="p-button-success"
                :loading="saving"
                @click="submitForm"
            />
        </template>
    </Dialog>
</template>

<script>
import axios from 'axios';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import { useToast } from 'primevue/usetoast';

export default {
    props: {
        visible: Boolean,
        themeId: Number,
        themeData: Object,
    },
    emits: ['update:visible', 'update'],
    components: { InputText, Textarea, Dropdown, Button, TabView, TabPanel },
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
            specialites: [],
            errors: {},
            activeTabIndex: 0,
            saving: false,
        };
    },
    watch: {
        themeData: {
            immediate: true,
            handler(val) {
                if (val) {
                    this.form = {
                        nom_fr: val.nom_fr,
                        nom_ar: val.nom_ar,
                        specialite_id: val.specialite_id,
                        description_fr: val.description_fr,
                        description_ar: val.description_ar,
                        statut: val.statut,
                    };
                }
            },
        },
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
                const res = await axios.put(`/api/themes/${this.themeId}`, this.form);
                this.$emit('update', res.data);

                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Thème mis à jour.',
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
