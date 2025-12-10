<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :modal="true"
        :style="{ width: '600px' }"
        :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
        :pt="{
            root: 'border-none',
            mask: { style: 'backdrop-filter: blur(2px)' },
            header: {
                class: 'surface-50 border-bottom-1 surface-border compact-header',
            },
            content: { class: 'surface-50 py-0' },
            footer: { class: 'surface-50 border-top-1 surface-border' },
        }"
    >
        <template #header>
            <div class="flex align-items-center justify-content-between w-full">
                <div class="flex align-items-center gap-2">
                    <i
                        :class="isEditing ? 'pi pi-pencil' : 'pi pi-plus'"
                        class="text-primary"
                    ></i>
                    <span class="font-bold text-lg">{{
                            isEditing ? 'Modifier Module' : 'Ajouter Module'
                        }}</span>
                </div>
            </div>
        </template>

        <div class="p-fluid formgrid grid p-5 gap-3">
            <!-- Code et Type sur la même ligne -->
            <div class="flex flex-wrap gap-3 w-full">
                <div class="field flex-1">
                    <label for="code" class="block font-medium mb-2"
                    >Code <span class="text-red-500">*</span></label
                    >
                    <InputText
                        id="code"
                        v-model="newModule.code"
                        placeholder="Ex: MOD-001"
                        class="w-full"
                        :class="{ 'p-invalid': submitted && !newModule.code }"
                        @input="validateCode"
                    />
                    <small v-if="submitted && !newModule.code" class="p-error"
                    >Code requis</small
                    >
                </div>
                <div class="field flex-1">
                    <label for="type_module_fr" class="block font-medium mb-2"
                    >Type <span class="text-red-500">*</span></label
                    >
                    <Dropdown
                        id="type_module_fr"
                        v-model="newModule.type_module_fr"
                        :options="['Spécifique', 'Général', 'Stage']"
                        placeholder="Sélectionner un type"
                        class="w-full"
                        :class="{
                            'p-invalid': submitted && !newModule.type_module_fr,
                        }"
                    />
                    <small
                        v-if="submitted && !newModule.type_module_fr"
                        class="p-error"
                    >Type requis</small
                    >
                </div>
            </div>

            <!-- Titre -->
            <div class="field col-12">
                <label for="titre_module" class="block font-medium mb-2"
                >Titre <span class="text-red-500">*</span></label
                >
                <InputText
                    id="titre_module"
                    v-model="newModule.titre_module"
                    placeholder="Ex: Introduction à la programmation"
                    class="w-full"
                    :class="{ 'p-invalid': submitted && !newModule.titre_module }"
                    @input="validateTitre"
                />
                <small
                    v-if="submitted && !newModule.titre_module"
                    class="p-error"
                >Titre requis</small
                >
            </div>

            <!-- Heures sur la même ligne -->
            <div class="flex flex-wrap gap-3 w-full">
                <div class="field flex-1">
                    <label
                        for="heures_theoriques"
                        class="block font-medium mb-2"
                    >H. Théoriques</label
                    >
                    <InputNumber
                        id="heures_theoriques"
                        v-model="newModule.heures_theoriques"
                        class="w-full"
                        :min="0"
                    />
                </div>
                <div class="field flex-1">
                    <label for="heures_pratiques" class="block font-medium mb-2"
                    >H. Pratiques</label
                    >
                    <InputNumber
                        id="heures_pratiques"
                        v-model="newModule.heures_pratiques"
                        class="w-full"
                        :min="0"
                    />
                </div>
                <div class="field flex-1">
                    <label
                        for="heures_evaluation"
                        class="block font-medium mb-2"
                    >H. Évaluation</label
                    >
                    <InputNumber
                        id="heures_evaluation"
                        v-model="newModule.heures_evaluation"
                        class="w-full"
                        :min="0"
                    />
                </div>
            </div>

            <!-- Statut avec RadioButton -->
            <div class="field col-12">
                <label class="block font-medium mb-2"
                >Statut <span class="text-red-500">*</span></label
                >
                <div class="flex flex-wrap gap-3">
                    <div
                        v-for="option in statutOptions"
                        :key="option.value"
                        class="flex align-items-center"
                    >
                        <RadioButton
                            v-model="newModule.statut"
                            :inputId="'statut_' + option.value.toLowerCase()"
                            name="statut"
                            :value="option.value"
                            :class="{
                                'p-invalid': submitted && !newModule.statut,
                            }"
                        />
                        <label
                            :for="'statut_' + option.value.toLowerCase()"
                            class="ml-2"
                        >{{ option.label }}</label
                        >
                    </div>
                </div>
                <small v-if="submitted && !newModule.statut" class="p-error"
                >Statut requis</small
                >
            </div>

            <!-- Description -->
            <div class="field col-12">
                <label for="description" class="block font-medium mb-2"
                >Description</label
                >
                <Textarea
                    id="description"
                    v-model="newModule.description"
                    rows="3"
                    placeholder="Saisissez la description..."
                    class="w-full"
                    autoResize
                />
            </div>

            <!-- Observation -->
            <div class="field col-12">
                <label for="observation" class="block font-medium mb-2"
                >Observation</label
                >
                <Textarea
                    id="observation"
                    v-model="newModule.observation"
                    rows="3"
                    placeholder="Saisissez vos observations..."
                    class="w-full"
                    autoResize
                />
            </div>
        </div>

        <template #footer>
            <div class="flex justify-content-end gap-2">
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="close"
                />
                <Button
                    :label="isEditing ? 'Mettre à jour' : 'Enregistrer'"
                    :icon="isEditing ? 'pi pi-check' : 'pi pi-save'"
                    class="p-button-success"
                    :loading="isSaving"
                    @click="isEditing ? updateModule() : saveModule()"
                />
            </div>
        </template>
    </Dialog>

    <Toast position="top-right" />
</template>

<script>
import { useToast } from 'primevue/usetoast';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';
import Dropdown from 'primevue/dropdown';
import RadioButton from 'primevue/radiobutton';

export default {
    components: {
        InputText,
        InputNumber,
        Button,
        Dialog,
        Textarea,
        Toast,
        Dropdown,
        RadioButton,
    },
    props: {
        visible: { type: Boolean, required: true },
        moduleToEdit: { type: Object, default: null },
        programme: { type: Object, default: null },
    },
    emits: ['update:visible', 'close', 'save', 'update'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            newModule: {
                id: null,
                programme_id: null,
                code: '',
                titre_module: '',
                type_module_fr: '',
                type_module_ar: '',
                heures_theoriques: null,
                heures_pratiques: null,
                heures_evaluation: null,
                description: '',
                observation: '',
                statut: 'Actif',
            },
            statutOptions: [
                { label: 'Actif', value: 'Actif' },
                { label: 'Inactif', value: 'Inactif' },
            ],
            errors: {
                code: '',
                titre_module: '',
                type_module_fr: '',
            },
            submitted: false,
            isSaving: false,
        };
    },
    computed: {
        isEditing() {
            return !!this.moduleToEdit;
        },
    },
    watch: {
        moduleToEdit: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.newModule = {
                        id: newVal.id || null,
                        programme_id:
                            newVal.programme_id || this.programme?.id || null,
                        code: newVal.code || '',
                        titre_module: newVal.titre_module || '',
                        type_module_fr: newVal.type_module_fr || '',
                        type_module_ar: newVal.type_module_ar || '',
                        heures_theoriques: newVal.heures_theoriques ?? null,
                        heures_pratiques: newVal.heures_pratiques ?? null,
                        heures_evaluation: newVal.heures_evaluation ?? null,
                        description: newVal.description || '',
                        observation: newVal.observation || '',
                        statut: newVal.statut || 'Actif',
                    };
                } else {
                    this.resetForm();
                }
            },
        },
        programme: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.newModule.programme_id = newVal.id;
                }
            },
        },
        'newModule.type_module_fr': {
            handler(newVal) {
                this.newModule.type_module_ar = this.mapTypeModuleAr(newVal);
            },
        },
    },
    methods: {
        close() {
            this.resetForm();
            this.$emit('update:visible', false);
            this.$emit('close');
        },
        resetForm() {
            this.newModule = {
                id: null,
                programme_id: this.programme?.id || null,
                code: '',
                titre_module: '',
                type_module_fr: '',
                type_module_ar: '',
                heures_theoriques: null,
                heures_pratiques: null,
                heures_evaluation: null,
                description: '',
                observation: '',
                statut: 'Actif',
            };
            this.errors = {
                code: '',
                titre_module: '',
                type_module_fr: '',
            };
            this.submitted = false;
        },
        validateCode() {
            if (!this.newModule.code) {
                this.errors.code = 'Le code est obligatoire';
            } else if (this.newModule.code.length > 100) {
                this.errors.code = 'Le code ne doit pas dépasser 100 caractères';
            } else {
                this.errors.code = '';
            }
        },
        validateTitre() {
            if (!this.newModule.titre_module) {
                this.errors.titre_module = 'Le titre est obligatoire';
            } else {
                this.errors.titre_module = '';
            }
        },
        validateTypeModuleFr() {
            if (!this.newModule.type_module_fr) {
                this.errors.type_module_fr = 'Le type est obligatoire';
            } else {
                this.errors.type_module_fr = '';
            }
        },
        mapTypeModuleAr(type_module_fr) {
            const mapping = {
                Spécifique: 'تقني',
                Général: 'عام',
                Stage: 'تربص',
            };
            return mapping[type_module_fr] || '';
        },
        async saveModule() {
            this.submitted = true;
            this.validateCode();
            this.validateTitre();
            this.validateTypeModuleFr();

            if (
                !this.newModule.programme_id ||
                !this.newModule.code ||
                !this.newModule.titre_module ||
                !this.newModule.type_module_fr ||
                !this.newModule.statut
            ) {
                this.showError('Veuillez remplir tous les champs obligatoires.');
                return;
            }

            if (Object.values(this.errors).some((error) => error !== '')) {
                this.showError('Veuillez corriger les erreurs dans le formulaire');
                return;
            }

            this.isSaving = true;
            const formData = new FormData();
            formData.append('programme_id', this.newModule.programme_id);
            formData.append('code', this.newModule.code);
            formData.append('titre_module', this.newModule.titre_module);
            formData.append('type_module_fr', this.newModule.type_module_fr);
            formData.append('type_module_ar', this.newModule.type_module_ar || '');
            formData.append(
                'heures_theoriques',
                this.newModule.heures_theoriques !== null && this.newModule.heures_theoriques !== ''
                    ? this.newModule.heures_theoriques
                    : null
            );
            formData.append(
                'heures_pratiques',
                this.newModule.heures_pratiques !== null && this.newModule.heures_pratiques !== ''
                    ? this.newModule.heures_pratiques
                    : null
            );
            formData.append(
                'heures_evaluation',
                this.newModule.heures_evaluation !== null && this.newModule.heures_evaluation !== ''
                    ? this.newModule.heures_evaluation
                    : null
            );
            formData.append('description', this.newModule.description || '');
            formData.append('observation', this.newModule.observation || '');
            formData.append('statut', this.newModule.statut);

            console.log('Données envoyées:', Object.fromEntries(formData));

            try {
                await this.$emit('save', formData);
                this.close();
            } catch (error) {
                console.error('Erreur serveur:', error.response?.data);
                const errorMessage = error.response?.data?.details
                    ? Object.values(error.response.data.details).flat().join(', ')
                    : error.response?.data?.error || "Erreur lors de l'enregistrement du module";
                this.showError(errorMessage);
            } finally {
                this.isSaving = false;
            }
        },
        async updateModule() {
            this.submitted = true;
            this.validateCode();
            this.validateTitre();
            this.validateTypeModuleFr();

            if (
                !this.newModule.code ||
                !this.newModule.titre_module ||
                !this.newModule.type_module_fr ||
                !this.newModule.statut
            ) {
                this.showError('Veuillez remplir tous les champs obligatoires.');
                return;
            }

            if (Object.values(this.errors).some((error) => error !== '')) {
                this.showError('Veuillez corriger les erreurs dans le formulaire');
                return;
            }

            this.isSaving = true;
            const formData = new FormData();
            formData.append('id', this.newModule.id || '');
            formData.append('programme_id', this.newModule.programme_id || '');
            formData.append('code', this.newModule.code || '');
            formData.append('titre_module', this.newModule.titre_module || '');
            console.log('type_module_fr avant envoi:', this.newModule.type_module_fr); // Log ajouté
            formData.append('type_module_fr', this.newModule.type_module_fr || '');
            formData.append('type_module_ar', this.newModule.type_module_ar || '');
            formData.append(
                'heures_theoriques',
                this.newModule.heures_theoriques ?? ''
            );
            formData.append(
                'heures_pratiques',
                this.newModule.heures_pratiques ?? ''
            );
            formData.append(
                'heures_evaluation',
                this.newModule.heures_evaluation ?? ''
            );
            formData.append('description', this.newModule.description || '');
            formData.append('observation', this.newModule.observation || '');
            formData.append('statut', this.newModule.statut || 'Actif');
            formData.append('_method', 'PUT');

            try {
                this.$emit('update', formData);
                this.close();
            } catch (error) {
                this.showError(
                    error.response?.data?.error ||
                    'Erreur lors de la mise à jour du module'
                );
            } finally {
                this.isSaving = false;
            }
        },
        showError(message) {
            this.toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: message,
                life: 5000,
            });
        },
    },
};
</script>

<style scoped>
.field {
    margin-bottom: 1rem;
}
.p-dialog .p-dialog-content {
    padding: 1.5rem;
}
.p-inputtext,
.p-inputnumber,
.p-inputtextarea,
.p-dropdown,
.p-radiobutton {
    width: 100%;
}
.compact-header {
    padding: 0.5rem !important;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
</style>
