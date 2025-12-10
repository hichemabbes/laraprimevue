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
                <i class="pi pi-plus text-primary text-2xl"></i>
                <span class="font-bold text-2xl">Ajouter un Module (Thème)</span>
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
                                placeholder="Ex: M01"
                                :class="{ 'p-invalid': errors.code }"
                            />
                            <small v-if="errors.code" class="p-error">
                                {{ errors.code[0] }}
                            </small>
                        </div>

                        <div class="col-12 md:col-4 field">
                            <label>Type module (FR)</label>
                            <InputText
                                v-model="form.type_module_fr"
                                placeholder="Ex: Théorique, Pratique"
                            />
                        </div>

                        <div class="col-12 md:col-4 field">
                            <label class="font-arabic">نوع الوحدة (العربية)</label>
                            <InputText
                                dir="rtl"
                                v-model="form.type_module_ar"
                            />
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
                                placeholder="Sélectionner un statut"
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
                label="Enregistrer"
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
    },
    emits: ['update:visible', 'save'],
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
        programmeId: {
            immediate: true,
            handler(val) {
                this.form.programme_theme_id = val || null;
            },
        },
        visible(newVal) {
            if (!newVal) {
                this.resetForm();
            }
        },
    },
    methods: {
        resetForm() {
            this.form = {
                programme_theme_id: this.programmeId || null,
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
            };
            this.errors = {};
            this.activeTabIndex = 0;
        },
        async submitForm() {
            if (!this.programmeId) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Programme non défini.',
                    life: 4000,
                });
                return;
            }

            this.saving = true;
            this.errors = {};
            try {
                const payload = {
                    ...this.form,
                    programme_theme_id: this.programmeId,
                };
                const res = await axios.post('/api/modules-themes', payload);

                this.$emit('save', res.data);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Module ajouté avec succès.',
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
                        detail: 'Erreur lors de la création du module.',
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
