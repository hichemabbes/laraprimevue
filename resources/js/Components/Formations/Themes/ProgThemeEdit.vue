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
                <span class="font-bold text-2xl">Modifier le Programme (Thème)</span>
            </div>
        </template>

        <form @submit.prevent="submitForm">
            <TabView v-model:activeIndex="activeTabIndex">
                <!-- Infos générales -->
                <TabPanel header="Informations Générales">
                    <div class="grid p-fluid">
                        <div class="col-12 md:col-6 field">
                            <label>Thème *</label>
                            <Dropdown
                                v-model="form.theme_id"
                                :options="themes"
                                optionLabel="nom_fr"
                                optionValue="id"
                                placeholder="Sélectionner un thème"
                                :class="{ 'p-invalid': errors.theme_id }"
                            />
                            <small v-if="errors.theme_id" class="p-error">
                                {{ errors.theme_id[0] }}
                            </small>
                        </div>

                        <div class="col-12 md:col-6 field">
                            <label>Version</label>
                            <InputText
                                v-model="form.version"
                                :class="{ 'p-invalid': errors.version }"
                            />
                            <small v-if="errors.version" class="p-error">
                                {{ errors.version[0] }}
                            </small>
                        </div>

                        <div class="col-12 md:col-6 field">
                            <label>Date début</label>
                            <Calendar
                                v-model="form.date_debut"
                                dateFormat="dd/mm/yy"
                                showIcon
                            />
                        </div>

                        <div class="col-12 md:col-6 field">
                            <label>Date fin</label>
                            <Calendar
                                v-model="form.date_fin"
                                dateFormat="dd/mm/yy"
                                showIcon
                            />
                        </div>

                        <div class="col-12 md:col-6 field">
                            <label>Statut</label>
                            <Dropdown
                                v-model="form.statut"
                                :options="['Actif', 'Inactif']"
                                placeholder="Sélectionner un statut"
                            />
                        </div>
                    </div>
                </TabPanel>

                <!-- Descriptions -->
                <TabPanel header="Descriptions">
                    <div class="grid p-fluid">
                        <div class="col-12 field">
                            <label>Description (FR)</label>
                            <Textarea
                                v-model="form.description_fr"
                                rows="4"
                                autoResize
                            />
                        </div>

                        <div class="col-12 field">
                            <label class="font-arabic">الوصف (العربية)</label>
                            <Textarea
                                v-model="form.description_ar"
                                rows="4"
                                autoResize
                                dir="rtl"
                            />
                        </div>

                        <div class="col-12 field">
                            <label>Observation (FR)</label>
                            <Textarea
                                v-model="form.observation_fr"
                                rows="3"
                                autoResize
                            />
                        </div>

                        <div class="col-12 field">
                            <label class="font-arabic">ملاحظات (العربية)</label>
                            <Textarea
                                v-model="form.observation_ar"
                                rows="3"
                                autoResize
                                dir="rtl"
                            />
                        </div>
                    </div>
                </TabPanel>
            </TabView>
        </form>

        <template #footer>
            <Button
                label="Annuler"
                icon="pi pi-times"
                class="p-button-text"
                @click="$emit('update:visible', false)"
            />
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
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import { useToast } from 'primevue/usetoast';

export default {
    props: {
        visible: Boolean,
        programmeId: [Number, String],
        programmeData: Object,
    },
    emits: ['update:visible', 'update'],
    components: {
        Dialog,
        InputText,
        Dropdown,
        Textarea,
        Calendar,
        Button,
        TabView,
        TabPanel,
    },
    setup() {
        return { toast: useToast() };
    },
    data() {
        return {
            form: {
                theme_id: null,
                version: '',
                date_debut: null,
                date_fin: null,
                description_fr: '',
                description_ar: '',
                observation_fr: '',
                observation_ar: '',
                statut: 'Actif',
            },
            themes: [],
            errors: {},
            saving: false,
            activeTabIndex: 0,
        };
    },
    watch: {
        programmeData: {
            immediate: true,
            handler(newVal) {
                if (!newVal) return;
                this.form = {
                    theme_id: newVal.theme_id ?? newVal.theme?.id ?? null,
                    version: newVal.version || '',
                    date_debut: newVal.date_debut ? new Date(newVal.date_debut) : null,
                    date_fin: newVal.date_fin ? new Date(newVal.date_fin) : null,
                    description_fr: newVal.description_fr || '',
                    description_ar: newVal.description_ar || '',
                    observation_fr: newVal.observation_fr || '',
                    observation_ar: newVal.observation_ar || '',
                    statut: newVal.statut || 'Actif',
                };
            },
        },
    },
    created() {
        this.fetchThemes();
    },
    methods: {
        async fetchThemes() {
            try {
                const res = await axios.get('/api/themes');
                this.themes = res.data || [];
            } catch (e) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les thèmes.',
                    life: 4000,
                });
            }
        },
        async submitForm() {
            this.saving = true;
            this.errors = {};
            try {
                const payload = {
                    ...this.form,
                    date_debut: this.form.date_debut
                        ? this.form.date_debut.toISOString().slice(0, 10)
                        : null,
                    date_fin: this.form.date_fin
                        ? this.form.date_fin.toISOString().slice(0, 10)
                        : null,
                };

                const res = await axios.put(
                    `/api/programmes-themes/${this.programmeId}`,
                    payload
                );

                this.$emit('update', res.data);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Programme mis à jour avec succès.',
                    life: 3000,
                });
                this.$emit('update:visible', false);
            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors || {};
                    this.toast.add({
                        severity: 'error',
                        summary: 'Validation',
                        detail: 'Veuillez corriger les erreurs.',
                        life: 4000,
                    });
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Erreur lors de la mise à jour du programme.',
                        life: 4000,
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
.font-arabic {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
}
</style>
