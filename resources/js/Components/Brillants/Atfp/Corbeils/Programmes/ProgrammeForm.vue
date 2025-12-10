<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :modal="true"
        :style="{ width: '450px' }"
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
                    isEditing ? 'Modifier un Programme' : 'Ajouter un Programme'
                }}</span>
            </div>
        </template>

        <div class="flex flex-column gap-4">
            <!-- Version Field -->
            <div class="field">
                <label for="version" class="block font-medium mb-2"
                    >Version <span class="text-red-500">*</span></label
                >
                <InputText
                    id="version"
                    v-model="newProgramme.version"
                    class="w-full"
                    :class="{ 'p-invalid': errors.version }"
                    @input="validateVersion"
                />
                <small v-if="errors.version" class="p-error">{{
                    errors.version
                }}</small>
            </div>

            <!-- Date Fields -->
            <div class="grid">
                <div class="col-12 md:col-6 field">
                    <label for="date_debut" class="block font-medium mb-2"
                        >Date Début <span class="text-red-500">*</span></label
                    >
                    <Calendar
                        id="date_debut"
                        v-model="newProgramme.date_debut"
                        dateFormat="dd/mm/yy"
                        class="w-full"
                        :class="{ 'p-invalid': errors.date_debut }"
                        showIcon
                        @date-select="validateDateDebut"
                    />
                    <small v-if="errors.date_debut" class="p-error">{{
                        errors.date_debut
                    }}</small>
                </div>
                <div class="col-12 md:col-6 field">
                    <label for="date_fin" class="block font-medium mb-2"
                        >Date Fin <span class="text-red-500">*</span></label
                    >
                    <Calendar
                        id="date_fin"
                        v-model="newProgramme.date_fin"
                        dateFormat="dd/mm/yy"
                        class="w-full"
                        :class="{ 'p-invalid': errors.date_fin }"
                        showIcon
                        @date-select="validateDateFin"
                    />
                    <small v-if="errors.date_fin" class="p-error">{{
                        errors.date_fin
                    }}</small>
                </div>
            </div>

            <!-- Description Field -->
            <div class="field">
                <label for="description" class="block font-medium mb-2"
                    >Description</label
                >
                <Textarea
                    id="description"
                    v-model="newProgramme.description"
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
                    v-model="newProgramme.observation"
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
                    v-model="newProgramme.statut"
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
                    @click="isEditing ? updateProgramme() : saveProgramme()"
                />
            </div>
        </template>
    </Dialog>

    <Toast position="top-right" />
</template>

<script>
import { useToast } from 'primevue/usetoast';
import InputText from 'primevue/inputtext';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Toast from 'primevue/toast';

export default {
    components: {
        InputText,
        Calendar,
        Button,
        Dialog,
        Textarea,
        Dropdown,
        Toast,
    },
    props: {
        visible: { type: Boolean, required: true },
        specialiteId: { type: Number, required: true },
        programmeToEdit: { type: Object, default: null },
    },
    emits: ['update:visible', 'close', 'save', 'update'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            newProgramme: {
                specialite_id: null,
                version: '',
                date_debut: null,
                date_fin: null,
                description: '',
                observation: '',
                statut: '',
            },
            errors: {
                version: '',
                date_debut: '',
                date_fin: '',
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
            return !!this.programmeToEdit;
        },
    },
    watch: {
        programmeToEdit: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.newProgramme = {
                        ...newVal,
                        date_debut: newVal.date_debut
                            ? new Date(newVal.date_debut)
                            : null,
                        date_fin: newVal.date_fin
                            ? new Date(newVal.date_fin)
                            : null,
                    };
                } else {
                    this.resetForm();
                }
            },
        },
        specialiteId: {
            immediate: true,
            handler(newVal) {
                this.newProgramme.specialite_id = newVal;
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
            this.newProgramme = {
                specialite_id: this.specialiteId,
                version: '',
                date_debut: null,
                date_fin: null,
                description: '',
                observation: '',
                statut: '',
            };
            this.errors = {
                version: '',
                date_debut: '',
                date_fin: '',
            };
        },
        formatDate(date) {
            if (!date || !(date instanceof Date)) return null;
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        validateVersion() {
            if (!this.newProgramme.version) {
                this.errors.version = 'La version est obligatoire';
            } else if (this.newProgramme.version.length > 100) {
                this.errors.version =
                    'La version ne doit pas dépasser 100 caractères';
            } else {
                this.errors.version = '';
            }
        },
        validateDateDebut() {
            if (!this.newProgramme.date_debut) {
                this.errors.date_debut = 'La date de début est obligatoire';
            } else {
                this.errors.date_debut = '';
            }
        },
        validateDateFin() {
            if (!this.newProgramme.date_fin) {
                this.errors.date_fin = 'La date de fin est obligatoire';
            } else if (
                this.newProgramme.date_debut &&
                this.newProgramme.date_fin &&
                this.newProgramme.date_fin <= this.newProgramme.date_debut
            ) {
                this.errors.date_fin =
                    'La date de fin doit être postérieure à la date de début';
            } else {
                this.errors.date_fin = '';
            }
        },
        async saveProgramme() {
            this.validateVersion();
            this.validateDateDebut();
            this.validateDateFin();

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

            const payload = {
                ...this.newProgramme,
                date_debut: this.formatDate(this.newProgramme.date_debut),
                date_fin: this.formatDate(this.newProgramme.date_fin),
            };

            this.$emit('save', payload);
            this.isSaving = false;
        },
        async updateProgramme() {
            this.validateVersion();
            this.validateDateDebut();
            this.validateDateFin();

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

            const payload = {
                ...this.newProgramme,
                date_debut: this.formatDate(this.newProgramme.date_debut),
                date_fin: this.formatDate(this.newProgramme.date_fin),
            };

            this.$emit('update', payload);
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

.p-calendar,
.p-inputtext,
.p-inputtextarea,
.p-dropdown {
    width: 100%;
}
</style>
