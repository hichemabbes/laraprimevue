<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :modal="true"
        :style="{ width: '550px' }"
        :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
        :pt="{
            root: 'border-none',
            mask: { style: 'backdrop-filter: blur(2px)' },
            header: { class: 'surface-50 border-bottom-1 surface-border' },
            content: { class: 'surface-50 py-0' },
            footer: { class: 'surface-50 border-top-1 surface-border' },
        }"
    >
        <template #header>
            <div class="flex align-items-center gap-2">
                <i
                    :class="isEditing ? 'pi pi-pencil' : 'pi pi-plus'"
                    class="text-primary"
                ></i>
                <span class="font-bold text-lg">{{
                    isEditing
                        ? 'Modifier un Module Général'
                        : 'Ajouter un Module Général'
                }}</span>
            </div>
        </template>

        <div class="flex flex-column gap-4">
            <!-- Code Field -->
            <div class="field">
                <label for="code" class="block font-medium mb-2"
                    >Code <span class="text-red-500">*</span></label
                >
                <InputText
                    id="code"
                    v-model="newModule.code"
                    class="w-full"
                    :class="{ 'p-invalid': errors.code }"
                    @input="validateCode"
                />
                <small v-if="errors.code" class="p-error">{{
                    errors.code
                }}</small>
            </div>

            <!-- Titre DocumentProgrammeSpecialite Field -->
            <div class="field">
                <label for="titre_module" class="block font-medium mb-2"
                    >Titre Module <span class="text-red-500">*</span></label
                >
                <InputText
                    id="titre_module"
                    v-model="newModule.titre_module"
                    class="w-full"
                    :class="{ 'p-invalid': errors.titre_module }"
                    @input="validateTitreModule"
                />
                <small v-if="errors.titre_module" class="p-error">{{
                    errors.titre_module
                }}</small>
            </div>

            <!-- Heures Fields -->
            <div class="grid">
                <div class="col-12 md:col-4 field">
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
                <div class="col-12 md:col-4 field">
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
                <div class="col-12 md:col-4 field">
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

            <!-- Description Field -->
            <div class="field">
                <label for="description" class="block font-medium mb-2"
                    >Description</label
                >
                <Textarea
                    id="description"
                    v-model="newModule.description"
                    class="w-full"
                    rows="4"
                    autoResize
                />
            </div>

            <!-- Observation Field -->
            <div class="field">
                <label for="observation" class="block font-medium mb-2"
                    >Observation</label
                >
                <Textarea
                    id="observation"
                    v-model="newModule.observation"
                    class="w-full"
                    rows="4"
                    autoResize
                />
            </div>

            <!-- Statut Field -->
            <div class="field">
                <label for="statut" class="block font-medium mb-2"
                    >Statut</label
                >
                <Dropdown
                    id="statut"
                    v-model="newModule.statut"
                    :options="statutOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Sélectionner un statut"
                    class="w-full"
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
                    :label="isEditing ? 'Modifier' : 'Enregistrer'"
                    :icon="isEditing ? 'pi pi-check' : 'pi pi-save'"
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
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';

export default {
    components: {
        InputText,
        InputNumber,
        Dropdown,
        Button,
        Dialog,
        Textarea,
        Toast,
    },
    props: {
        visible: { type: Boolean, required: true },
        moduleToEdit: { type: Object, default: null },
    },
    emits: ['update:visible', 'close', 'save', 'update'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            newModule: {
                code: '',
                titre_module: '',
                heures_theoriques: null,
                heures_pratiques: null,
                heures_evaluation: null,
                description: '',
                observation: '',
                statut: '',
            },
            errors: {
                code: '',
                titre_module: '',
            },
            statutOptions: [
                { label: 'Actif', value: 'Actif' },
                { label: 'Inactif', value: 'Inactif' },
                { label: 'En attente', value: 'En attente' },
                { label: 'Archivé', value: 'Archivé' },
            ],
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
                    this.newModule = { ...newVal };
                } else {
                    this.resetForm();
                }
            },
        },
        visible(newVal) {
            if (!newVal) {
                this.resetForm();
            }
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
                code: '',
                titre_module: '',
                heures_theoriques: null,
                heures_pratiques: null,
                heures_evaluation: null,
                description: '',
                observation: '',
                statut: '',
            };
            this.errors = {
                code: '',
                titre_module: '',
            };
        },
        validateCode() {
            if (!this.newModule.code) {
                this.errors.code = 'Le code est obligatoire';
            } else if (this.newModule.code.length > 100) {
                this.errors.code =
                    'Le code ne doit pas dépasser 100 caractères';
            } else {
                this.errors.code = '';
            }
        },
        validateTitreModule() {
            if (!this.newModule.titre_module) {
                this.errors.titre_module = 'Le titre du module est obligatoire';
            } else {
                this.errors.titre_module = '';
            }
        },
        async saveModule() {
            this.validateCode();
            this.validateTitreModule();

            if (Object.values(this.errors).some((error) => error !== '')) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Veuillez corriger les erreurs dans le formulaire',
                    life: 5000,
                });
                return;
            }

            this.isSaving = true;
            this.$emit('save', this.newModule);
            this.isSaving = false;
        },
        async updateModule() {
            this.validateCode();
            this.validateTitreModule();

            if (Object.values(this.errors).some((error) => error !== '')) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Veuillez corriger les erreurs dans le formulaire',
                    life: 5000,
                });
                return;
            }

            this.isSaving = true;
            this.$emit('update', this.newModule);
            this.isSaving = false;
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
.p-dropdown,
.p-inputtextarea {
    width: 100%;
}
</style>
