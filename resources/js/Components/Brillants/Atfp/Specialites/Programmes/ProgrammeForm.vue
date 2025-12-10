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
                    isEditing ? 'Modifier Programme' : 'Ajouter Programme'
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

            <!-- Date Debut and Date Fin Fields -->
            <div class="field">
                <div class="flex gap-3">
                    <div class="flex-1">
                        <label
                            for="date_debut"
                            class="block font-medium mb-2 text-sm"
                            >Date Début</label
                        >
                        <Calendar
                            id="date_debut"
                            v-model="newProgramme.date_debut"
                            dateFormat="yy-mm-dd"
                            class="w-full"
                            :class="{ 'p-invalid': errors.date_debut }"
                            @update:modelValue="validateDates"
                        />
                        <small v-if="errors.date_debut" class="p-error">{{
                            errors.date_debut
                        }}</small>
                    </div>
                    <div class="flex-1">
                        <label
                            for="date_fin"
                            class="block font-medium mb-2 text-sm"
                            >Date Fin</label
                        >
                        <Calendar
                            id="date_fin"
                            v-model="newProgramme.date_fin"
                            dateFormat="yy-mm-dd"
                            class="w-full"
                            :class="{ 'p-invalid': errors.date_fin }"
                            @update:modelValue="validateDates"
                        />
                        <small v-if="errors.date_fin" class="p-error">{{
                            errors.date_fin
                        }}</small>
                    </div>
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
                <label class="block font-medium mb-2">Statut</label>
                <div class="flex align-items-center gap-4">
                    <div class="flex align-items-center">
                        <RadioButton
                            inputId="statut_actif"
                            v-model="newProgramme.statut"
                            value="Actif"
                            name="statut"
                        />
                        <label for="statut_actif" class="ml-2">Actif</label>
                    </div>
                    <div class="flex align-items-center">
                        <RadioButton
                            inputId="statut_inactif"
                            v-model="newProgramme.statut"
                            value="Inactif"
                            name="statut"
                        />
                        <label for="statut_inactif" class="ml-2">Inactif</label>
                    </div>
                </div>
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
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';
import Calendar from 'primevue/calendar';
import RadioButton from 'primevue/radiobutton';
import axios from 'axios';

export default {
    components: {
        InputText,
        Button,
        Dialog,
        Textarea,
        Toast,
        Calendar,
        RadioButton,
    },
    props: {
        visible: { type: Boolean, required: true },
        programmeToEdit: { type: Object, default: null },
        specialite: { type: Object, default: null },
    },
    emits: ['update:visible', 'close', 'save', 'update'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            newProgramme: {
                id: null,
                specialite_id: null,
                version: '',
                date_debut: null,
                date_fin: null,
                description: '',
                observation: '',
                statut: 'Actif',
            },
            errors: {
                version: '',
                date_debut: '',
                date_fin: '',
            },
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
                        id: newVal.id || null,
                        specialite_id:
                            newVal.specialite_id || this.specialite?.id || null,
                        version: newVal.version || '',
                        date_debut: newVal.date_debut
                            ? new Date(newVal.date_debut)
                            : null,
                        date_fin: newVal.date_fin
                            ? new Date(newVal.date_fin)
                            : null,
                        description: newVal.description || '',
                        observation: newVal.observation || '',
                        statut: newVal.statut || 'Actif',
                    };
                } else {
                    this.resetForm();
                }
            },
        },
        specialite: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.newProgramme.specialite_id = newVal.id;
                }
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
            this.newProgramme = {
                id: null,
                specialite_id: this.specialite?.id || null,
                version: '',
                date_debut: null,
                date_fin: null,
                description: '',
                observation: '',
                statut: 'Actif',
            };
            this.errors = {
                version: '',
                date_debut: '',
                date_fin: '',
            };
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
        validateDates() {
            this.errors.date_debut = '';
            this.errors.date_fin = '';
            if (this.newProgramme.date_fin && this.newProgramme.date_debut) {
                const debut = new Date(this.newProgramme.date_debut);
                const fin = new Date(this.newProgramme.date_fin);
                if (fin < debut) {
                    this.errors.date_fin =
                        'La date de fin doit être postérieure ou égale à la date de début';
                }
            }
        },
        async saveProgramme() {
            this.validateVersion();
            this.validateDates();

            if (!this.newProgramme.specialite_id) {
                this.showError('Spécialité requise. Veuillez sélectionner une spécialité.');
                return;
            }

            if (Object.values(this.errors).some((error) => error !== '')) {
                this.showError('Veuillez corriger les erreurs dans le formulaire');
                return;
            }

            this.isSaving = true;
            const payload = {
                ...this.newProgramme,
                date_debut: this.newProgramme.date_debut
                    ? this.formatDate(this.newProgramme.date_debut)
                    : null,
                date_fin: this.newProgramme.date_fin
                    ? this.formatDate(this.newProgramme.date_fin)
                    : null,
            };
            console.log('Payload being sent:', payload);
            try {
                this.$emit('save', payload);
            } catch (error) {
                this.showError(
                    error.response?.data?.error ||
                    "Erreur lors de l'enregistrement du programme"
                );
            } finally {
                this.isSaving = false;
            }
        },
        async updateProgramme() {
            this.validateVersion();
            this.validateDates();

            if (Object.values(this.errors).some((error) => error !== '')) {
                this.showError(
                    'Veuillez corriger les erreurs dans le formulaire'
                );
                return;
            }

            this.isSaving = true;
            const payload = {
                ...this.newProgramme,
                date_debut: this.newProgramme.date_debut
                    ? this.formatDate(this.newProgramme.date_debut)
                    : null,
                date_fin: this.newProgramme.date_fin
                    ? this.formatDate(this.newProgramme.date_fin)
                    : null,
            };
            try {
                this.$emit('update', payload);
            } catch (error) {
                this.showError(
                    error.response?.data?.error ||
                        'Erreur lors de la mise à jour du programme'
                );
            } finally {
                this.isSaving = false;
            }
        },
        formatDate(date) {
            if (!date) return null;
            const d = new Date(date);
            return d.toISOString().split('T')[0];
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
.p-calendar,
.p-inputtext,
.p-inputtextarea,
.p-radiobutton {
    width: 100%;
}
</style>
