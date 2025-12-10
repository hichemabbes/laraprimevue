<template>
    <Dialog
        :visible="visible"
        :header="
            programmeEtudeToEdit
                ? 'Modifier Programme d\'Étude'
                : 'Nouveau Programme d\'Étude'
        "
        :style="{ width: '600px' }"
        :modal="true"
        @update:visible="$emit('update:visible', $event)"
    >
        <div class="p-fluid">
            <div class="field mb-4">
                <label for="version" class="font-medium mb-2">Version</label>
                <InputText
                    id="version"
                    v-model="form.version"
                    placeholder="Entrer la version"
                    :class="{ 'p-invalid': errors.version }"
                />
                <small v-if="errors.version" class="p-error">{{
                    errors.version
                }}</small>
            </div>

            <div class="field mb-4">
                <label for="programme" class="font-medium mb-2"
                    >Programme</label
                >
                <Dropdown
                    id="programme"
                    v-model="form.programme_id"
                    :options="programmes"
                    optionLabel="version"
                    optionValue="id"
                    placeholder="Sélectionner un programme"
                    :class="{ 'p-invalid': errors.programme_id }"
                    showClear
                />
                <small v-if="errors.programme_id" class="p-error">{{
                    errors.programme_id
                }}</small>
            </div>

            <div class="field mb-4">
                <label for="memoire" class="font-medium mb-2">Mémoire</label>
                <Dropdown
                    id="memoire"
                    v-model="form.memoire_id"
                    :options="memoires"
                    optionLabel="reference"
                    optionValue="id"
                    placeholder="Sélectionner un mémoire"
                    :class="{ 'p-invalid': errors.memoire_id }"
                    showClear
                />
                <small v-if="errors.memoire_id" class="p-error">{{
                    errors.memoire_id
                }}</small>
            </div>

            <div class="field mb-4">
                <label for="date_debut" class="font-medium mb-2"
                    >Date de début</label
                >
                <Calendar
                    id="date_debut"
                    v-model="form.date_debut"
                    dateFormat="dd/mm/yy"
                    showIcon
                    :class="{ 'p-invalid': errors.date_debut }"
                />
                <small v-if="errors.date_debut" class="p-error">{{
                    errors.date_debut
                }}</small>
            </div>

            <div class="field mb-4">
                <label for="date_fin" class="font-medium mb-2"
                    >Date de fin</label
                >
                <Calendar
                    id="date_fin"
                    v-model="form.date_fin"
                    dateFormat="dd/mm/yy"
                    showIcon
                    :class="{ 'p-invalid': errors.date_fin }"
                />
                <small v-if="errors.date_fin" class="p-error">{{
                    errors.date_fin
                }}</small>
            </div>

            <div class="field mb-4">
                <label for="description" class="font-medium mb-2"
                    >Description</label
                >
                <Textarea
                    id="description"
                    v-model="form.description"
                    rows="4"
                    placeholder="Entrer une description"
                    :class="{ 'p-invalid': errors.description }"
                />
                <small v-if="errors.description" class="p-error">{{
                    errors.description
                }}</small>
            </div>

            <div class="field mb-4">
                <label for="statut" class="font-medium mb-2">Statut</label>
                <Dropdown
                    id="statut"
                    v-model="form.statut"
                    :options="statutOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Sélectionner un statut"
                    :class="{ 'p-invalid': errors.statut }"
                />
                <small v-if="errors.statut" class="p-error">{{
                    errors.statut
                }}</small>
            </div>
        </div>

        <template #footer>
            <Button
                label="Annuler"
                icon="pi pi-times"
                class="p-button-text"
                @click="$emit('update:visible', false)"
            />
            <Button
                :label="programmeEtudeToEdit ? 'Mettre à jour' : 'Créer'"
                icon="pi pi-check"
                class="p-button-success"
                :loading="submitting"
                @click="submitForm"
            />
        </template>
    </Dialog>
</template>

<script>
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';

export default {
    components: {
        Dialog,
        InputText,
        Dropdown,
        Calendar,
        Textarea,
        Button,
    },
    props: {
        visible: {
            type: Boolean,
            required: true,
        },
        specialiteId: {
            type: [Number, String],
            required: true,
        },
        programmeEtudeToEdit: {
            type: Object,
            default: null,
        },
    },
    setup() {
        return { toast: useToast() };
    },
    data() {
        return {
            form: {
                version: '',
                programme_id: null,
                memoire_id: null,
                date_debut: null,
                date_fin: null,
                description: '',
                statut: null,
                specialite_id: this.specialiteId,
            },
            programmes: [],
            memoires: [],
            statutOptions: [
                { label: 'Actif', value: 'Actif' },
                { label: 'Inactif', value: 'Inactif' },
                { label: 'En attente', value: 'En attente' },
                { label: 'Archivé', value: 'Archivé' },
            ],
            errors: {},
            submitting: false,
        };
    },
    watch: {
        visible(newVal) {
            if (newVal) {
                this.fetchData();
                if (this.programmeEtudeToEdit) {
                    this.populateForm();
                }
            } else {
                this.resetForm();
            }
        },
        specialiteId() {
            this.form.specialite_id = this.specialiteId;
        },
    },
    methods: {
        async fetchData() {
            try {
                const [programmesRes, memoiresRes] = await Promise.all([
                    axios.get(`/specialites/${this.specialiteId}/programmes`),
                    axios.get(`/diplomes/memoires`),
                ]);
                this.programmes = programmesRes.data;
                this.memoires = memoiresRes.data.flatMap((d) => d.memoires);
            } catch (error) {
                this.showError('Erreur lors du chargement des données');
            }
        },
        populateForm() {
            this.form = {
                version: this.programmeEtudeToEdit.version || '',
                programme_id: this.programmeEtudeToEdit.programme_id || null,
                memoire_id: this.programmeEtudeToEdit.memoire_id || null,
                date_debut: this.programmeEtudeToEdit.date_debut
                    ? new Date(this.programmeEtudeToEdit.date_debut)
                    : null,
                date_fin: this.programmeEtudeToEdit.date_fin
                    ? new Date(this.programmeEtudeToEdit.date_fin)
                    : null,
                description: this.programmeEtudeToEdit.description || '',
                statut: this.programmeEtudeToEdit.statut || null,
                specialite_id: this.specialiteId,
            };
        },
        validateForm() {
            this.errors = {};
            if (!this.form.version)
                this.errors.version = 'La version est requise';
            if (!this.form.programme_id && !this.form.memoire_id) {
                this.errors.programme_id =
                    'Au moins un programme ou un mémoire est requis';
                this.errors.memoire_id =
                    'Au moins un programme ou un mémoire est requis';
            }
            if (!this.form.date_debut)
                this.errors.date_debut = 'La date de début est requise';
            if (!this.form.statut) this.errors.statut = 'Le statut est requis';
            return Object.keys(this.errors).length === 0;
        },
        async submitForm() {
            if (!this.validateForm()) return;
            this.submitting = true;
            try {
                if (this.programmeEtudeToEdit) {
                    await this.$emit('update', {
                        id: this.programmeEtudeToEdit.id,
                        ...this.form,
                    });
                } else {
                    await this.$emit('save', this.form);
                }
                this.$emit('update:visible', false);
            } catch (error) {
                this.showError('Erreur lors de la soumission du formulaire');
            } finally {
                this.submitting = false;
            }
        },
        resetForm() {
            this.form = {
                version: '',
                programme_id: null,
                memoire_id: null,
                date_debut: null,
                date_fin: null,
                description: '',
                statut: null,
                specialite_id: this.specialiteId,
            };
            this.errors = {};
        },
        showSuccess(message) {
            this.toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: message,
                life: 3000,
            });
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
.p-fluid .field {
    margin-bottom: 1.5rem;
}
</style>
