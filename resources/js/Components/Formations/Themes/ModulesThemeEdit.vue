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
                <span class="font-bold text-2xl">Modifier le Module (Thème)</span>
            </div>
        </template>

        <form @submit.prevent="submitForm">
            <TabView v-model:activeIndex="activeTabIndex">
                <!-- Infos générales -->
                <TabPanel header="Informations Générales">
                    <div class="grid p-fluid">
                        <div class="col-12 md:col-4 field">
                            <label>Code</label>
                            <InputText
                                v-model="form.code"
                                :class="{ 'p-invalid': errors.code }"
                            />
                            <small v-if="errors.code" class="p-error">
                                {{ errors.code[0] }}
                            </small>
                        </div>

                        <div class="col-12 md:col-4 field">
                            <label>Type module (FR)</label>
                            <InputText v-model="form.type_module_fr" />
                        </div>

                        <div class="col-12 md:col-4 field">
                            <label class="font-arabic">نوع الوحدة (العربية)</label>
                            <InputText v-model="form.type_module_ar" dir="rtl" />
                        </div>

                        <div class="col-12 md:col-4 field">
                            <label>Heures théoriques</label>
                            <InputNumber
                                v-model="form.heures_theoriques"
                                :min="0"
                                :showButtons="true"
                            />
                        </div>

                        <div class="col-12 md:col-4 field">
                            <label>Heures pratiques</label>
                            <InputNumber
                                v-model="form.heures_pratiques"
                                :min="0"
                                :showButtons="true"
                            />
                        </div>

                        <div class="col-12 md:col-4 field">
                            <label>Heures évaluation</label>
                            <InputNumber
                                v-model="form.heures_evaluation"
                                :min="0"
                                :showButtons="true"
                            />
                        </div>

                        <div class="col-12 md:col-4 field">
                            <label>Statut</label>
                            <Dropdown
                                v-model="form.statut"
                                :options="['Actif', 'Inactif']"
                            />
                        </div>
                    </div>
                </TabPanel>

                <!-- Titres & descriptions -->
                <TabPanel header="Titres & Descriptions">
                    <div class="grid p-fluid">
                        <div class="col-12 md:col-6 field">
                            <label>Titre module (FR)</label>
                            <Textarea
                                v-model="form.titre_module_fr"
                                rows="3"
                                autoResize
                            />
                        </div>
                        <div class="col-12 md:col-6 field">
                            <label class="font-arabic">عنوان الوحدة (العربية)</label>
                            <Textarea
                                v-model="form.titre_module_ar"
                                rows="3"
                                autoResize
                                dir="rtl"
                            />
                        </div>

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
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import { useToast } from 'primevue/usetoast';

export default {
    props: {
        visible: Boolean,
        programmeId: [Number, String, null],
        moduleId: [Number, String, null],
        moduleData: Object,
    },
    emits: ['update:visible', 'update'],
    components: {
        Dialog,
        InputText,
        Textarea,
        Dropdown,
        InputNumber,
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
                programme_theme_id: null,
                code: '',
                titre_module_fr: '',
                titre_module_ar: '',
                type_module_fr: '',
                type_module_ar: '',
                heures_theoriques: 0,
                heures_pratiques: 0,
                heures_evaluation: 0,
                description_fr: '',
                description_ar: '',
                observation_fr: '',
                observation_ar: '',
                statut: 'Actif',
            },
            errors: {},
            saving: false,
            activeTabIndex: 0,
        };
    },
    watch: {
        moduleData: {
            immediate: true,
            handler(val) {
                if (!val) return;
                this.form = {
                    programme_theme_id: val.programme_theme_id || this.programmeId || null,
                    code: val.code || '',
                    titre_module_fr: val.titre_module_fr || '',
                    titre_module_ar: val.titre_module_ar || '',
                    type_module_fr: val.type_module_fr || '',
                    type_module_ar: val.type_module_ar || '',
                    heures_theoriques: val.heures_theoriques || 0,
                    heures_pratiques: val.heures_pratiques || 0,
                    heures_evaluation: val.heures_evaluation || 0,
                    description_fr: val.description_fr || '',
                    description_ar: val.description_ar || '',
                    observation_fr: val.observation_fr || '',
                    observation_ar: val.observation_ar || '',
                    statut: val.statut || 'Actif',
                };
            },
        },
        visible(newVal) {
            if (!newVal) {
                this.errors = {};
                this.activeTabIndex = 0;
            }
        },
    },
    methods: {
        async submitForm() {
            if (!this.moduleId) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Module non défini.',
                    life: 4000,
                });
                return;
            }
            this.saving = true;
            this.errors = {};
            try {
                const payload = {
                    ...this.form,
                    programme_theme_id: this.form.programme_theme_id || this.programmeId,
                };
                const res = await axios.put(
                    `/api/modules-themes/${this.moduleId}`,
                    payload
                );
                this.$emit('update', res.data);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Module mis à jour avec succès.',
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
                        detail: 'Erreur lors de la mise à jour du module.',
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
