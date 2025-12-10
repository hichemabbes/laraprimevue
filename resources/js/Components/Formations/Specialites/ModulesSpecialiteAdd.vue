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
                <span class="font-bold text-2xl">Ajouter un Module (Spécialité)</span>
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
                                v-model="form.type_module_ar"
                                dir="rtl"
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

                <!-- Documents -->
                <TabPanel header="Documents">
                    <div class="grid p-fluid">
                        <div class="col-12 field">
                            <small class="text-600">
                                Vous pouvez ajouter plusieurs documents (PDF) liés à ce module.
                            </small>
                        </div>

                        <!-- Formulaire document courant -->
                        <div class="col-12 md:col-6 field">
                            <label>Titre document (FR)</label>
                            <InputText
                                v-model="docDraft.titre_fr"
                                placeholder="Titre du document"
                            />
                        </div>

                        <div class="col-12 md:col-6 field">
                            <label class="font-arabic">عنوان الوثيقة (العربية)</label>
                            <InputText
                                v-model="docDraft.titre_ar"
                                dir="rtl"
                            />
                        </div>

                        <div class="col-12 md:col-6 field">
                            <label>Type document (FR)</label>
                            <InputText
                                v-model="docDraft.type_document_fr"
                                placeholder="Ex: Support cours, Exercice..."
                            />
                        </div>

                        <div class="col-12 md:col-6 field">
                            <label>Fichier (PDF)</label>
                            <input
                                :key="docInputKey"
                                type="file"
                                accept="application/pdf"
                                @change="onDocFileChange"
                            />
                        </div>

                        <div class="col-12 field">
                            <label>Description (FR)</label>
                            <Textarea
                                v-model="docDraft.description_fr"
                                rows="3"
                                autoResize
                            />
                        </div>

                        <div class="col-12 field">
                            <label class="font-arabic">الوصف (العربية)</label>
                            <Textarea
                                v-model="docDraft.description_ar"
                                rows="3"
                                autoResize
                                dir="rtl"
                            />
                        </div>

                        <div class="col-12 field">
                            <Button
                                label="Ajouter le document"
                                icon="pi pi-plus"
                                class="p-button-text p-button-sm p-button-success"
                                @click="addDocumentDraft"
                            />
                            <small v-if="docError" class="p-error ml-2">{{ docError }}</small>
                        </div>

                        <!-- Liste docs -->
                        <div class="col-12" v-if="documents.length">
                            <h4 class="mt-3 mb-2">Documents à enregistrer</h4>
                            <DataTable
                                :value="documents"
                                dataKey="tempId"
                                size="small"
                                class="p-datatable-sm border-1 surface-border"
                            >
                                <Column field="titre_fr" header="Titre (FR)" />
                                <Column field="type_document_fr" header="Type" />
                                <Column header="Fichier">
                                    <template #body="{ data }">
                                        <span>{{ data.fichier ? data.fichier.name : '-' }}</span>
                                    </template>
                                </Column>
                                <Column header="Actions" style="min-width: 6rem">
                                    <template #body="{ data }">
                                        <Button
                                            icon="pi pi-trash"
                                            class="p-button-text p-button-rounded p-button-danger"
                                            @click="removeDocument(data.tempId)"
                                            v-tooltip="'Supprimer ce document'"
                                        />
                                    </template>
                                </Column>
                            </DataTable>
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
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
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
        DataTable,
        Column,
    },
    setup() {
        return { toast: useToast() };
    },
    data() {
        return {
            form: {
                programme_specialite_id: null,
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
            // docs
            documents: [],
            docDraft: {
                titre_fr: '',
                titre_ar: '',
                type_document_fr: '',
                type_document_ar: '',
                description_fr: '',
                description_ar: '',
                observation_fr: '',
                observation_ar: '',
                fichier: null,
                statut: 'Actif',
            },
            docError: '',
            docInputKey: 0,
            docTempIdCounter: 1,
        };
    },
    watch: {
        programmeId: {
            immediate: true,
            handler(val) {
                this.form.programme_specialite_id = val || null;
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
                programme_specialite_id: this.programmeId || null,
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
            this.documents = [];
            this.docDraft = {
                titre_fr: '',
                titre_ar: '',
                type_document_fr: '',
                type_document_ar: '',
                description_fr: '',
                description_ar: '',
                observation_fr: '',
                observation_ar: '',
                fichier: null,
                statut: 'Actif',
            };
            this.docInputKey++;
        },
        onDocFileChange(event) {
            const file = event.target.files[0];
            this.docDraft.fichier = file || null;
        },
        addDocumentDraft() {
            this.docError = '';
            if (!this.docDraft.titre_fr) {
                this.docError = 'Le titre (FR) est obligatoire.';
                return;
            }
            if (!this.docDraft.fichier) {
                this.docError = 'Le fichier PDF est obligatoire.';
                return;
            }
            const newDoc = {
                ...this.docDraft,
                tempId: this.docTempIdCounter++,
            };
            this.documents.push(newDoc);
            this.docDraft = {
                titre_fr: '',
                titre_ar: '',
                type_document_fr: this.docDraft.type_document_fr,
                type_document_ar: '',
                description_fr: '',
                description_ar: '',
                observation_fr: '',
                observation_ar: '',
                fichier: null,
                statut: 'Actif',
            };
            this.docInputKey++;
        },
        removeDocument(tempId) {
            this.documents = this.documents.filter((d) => d.tempId !== tempId);
        },
        async uploadModuleDocuments(moduleId) {
            if (!this.documents.length) return;
            for (const doc of this.documents) {
                try {
                    const formData = new FormData();
                    formData.append('module_specialite_id', moduleId);
                    formData.append('titre_fr', doc.titre_fr || '');
                    formData.append('titre_ar', doc.titre_ar || '');
                    formData.append('type_document_fr', doc.type_document_fr || '');
                    formData.append('type_document_ar', doc.type_document_ar || '');
                    formData.append('description_fr', doc.description_fr || '');
                    formData.append('description_ar', doc.description_ar || '');
                    formData.append('observation_fr', doc.observation_fr || '');
                    formData.append('observation_ar', doc.observation_ar || '');
                    formData.append('statut', doc.statut || 'Actif');
                    if (doc.fichier) {
                        formData.append('fichier', doc.fichier);
                    }

                    await axios.post(
                        '/api/documents-modules-specialites',
                        formData,
                        {
                            headers: { 'Content-Type': 'multipart/form-data' },
                        }
                    );
                } catch (e) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur doc',
                        detail: `Erreur lors de l'enregistrement du document "${doc.titre_fr}".`,
                        life: 4000,
                    });
                }
            }
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
                    programme_specialite_id: this.programmeId,
                };
                const res = await axios.post('/api/modules-specialites', payload);
                const module = res.data;

                await this.uploadModuleDocuments(module.id);

                this.$emit('save', module);
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
