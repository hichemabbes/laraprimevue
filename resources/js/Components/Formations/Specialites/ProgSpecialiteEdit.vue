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
                <span class="font-bold text-2xl">Modifier le Programme (Spécialité)</span>
            </div>
        </template>

        <form @submit.prevent="submitForm">
            <TabView v-model:activeIndex="activeTabIndex">
                <!-- Infos générales -->
                <TabPanel header="Informations Générales">
                    <div class="grid p-fluid">
                        <div class="col-12 md:col-6 field">
                            <label>Spécialité *</label>
                            <Dropdown
                                v-model="form.specialite_id"
                                :options="specialites"
                                optionLabel="nom_fr"
                                optionValue="id"
                                placeholder="Sélectionner une spécialité"
                                :class="{ 'p-invalid': errors.specialite_id }"
                            />
                            <small v-if="errors.specialite_id" class="p-error">
                                {{ errors.specialite_id[0] }}
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

                <!-- Documents (ajout de nouveaux docs) -->
                <TabPanel header="Documents">
                    <div class="grid p-fluid">
                        <div class="col-12 field">
                            <small class="text-600">
                                Vous pouvez ajouter de nouveaux documents (PDF) pour ce programme.
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
                                placeholder="Ex: Programme officiel, Référentiel..."
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

                        <!-- Liste des nouveaux documents à créer -->
                        <div class="col-12" v-if="documents.length">
                            <h4 class="mt-3 mb-2">Nouveaux documents à enregistrer</h4>
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
                specialite_id: null,
                version: '',
                date_debut: null,
                date_fin: null,
                description_fr: '',
                description_ar: '',
                observation_fr: '',
                observation_ar: '',
                statut: 'Actif',
            },
            specialites: [],
            errors: {},
            saving: false,
            activeTabIndex: 0,
            // nouveaux docs
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
        programmeData: {
            immediate: true,
            handler(newVal) {
                if (!newVal) return;
                this.form = {
                    specialite_id: newVal.specialite_id ?? newVal.specialite?.id ?? null,
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
        visible(newVal) {
            if (!newVal) {
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
            }
        },
    },
    created() {
        this.fetchSpecialites();
    },
    methods: {
        async fetchSpecialites() {
            try {
                const res = await axios.get('/api/specialites');
                this.specialites = res.data || [];
            } catch (e) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les spécialités.',
                    life: 4000,
                });
            }
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
        async uploadProgrammeDocuments(programmeId) {
            if (!this.documents.length) return;
            for (const doc of this.documents) {
                try {
                    const formData = new FormData();
                    formData.append('programme_specialite_id', programmeId);
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
                        '/api/documents-programmes-specialites',
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
                    date_debut: this.form.date_debut
                        ? this.form.date_debut.toISOString().slice(0, 10)
                        : null,
                    date_fin: this.form.date_fin
                        ? this.form.date_fin.toISOString().slice(0, 10)
                        : null,
                };

                const res = await axios.put(
                    `/api/programmes-specialites/${this.programmeId}`,
                    payload
                );
                const programme = res.data;

                await this.uploadProgrammeDocuments(programme.id);

                this.$emit('update', programme);
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
