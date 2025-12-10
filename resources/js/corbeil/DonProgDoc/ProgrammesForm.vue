<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        :pt="{
            root: 'border-none',
            mask: { style: 'backdrop-filter: blur(2px)' },
        }"
    >
        <template #container="{ closeCallback }">
            <div class="relative p-5 surface-card">
                <span
                    class="absolute p-button p-button-icon-only p-button-text"
                    style="top: 12px; right: 12px"
                    @click="close"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M18 6L6 18" />
                        <path d="M6 6l12 12" />
                    </svg>
                </span>

                <div class="p-5 surface-card">
                    <h5 class="text-center font-bold text-lg mt-3 mb-5">
                        {{
                            isEditing
                                ? 'Modifier un Programme'
                                : 'Ajouter un Programme'
                        }}
                    </h5>

                    <div class="flex flex-column gap-2">
                        <label for="version">Version</label>
                        <InputText
                            id="version"
                            v-model="newElement.version"
                            class="w-full"
                            @input="validateVersion"
                        />
                        <small v-if="errors.version" class="text-red-500">{{
                            errors.version
                        }}</small>
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="date_debut">Date Début</label>
                        <Calendar
                            id="date_debut"
                            v-model="newElement.date_debut"
                            dateFormat="yy-mm-dd"
                            class="w-full"
                            @input="validateDateDebut"
                        />
                        <small v-if="errors.date_debut" class="text-red-500">{{
                            errors.date_debut
                        }}</small>
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="date_fin">Date Fin</label>
                        <Calendar
                            id="date_fin"
                            v-model="newElement.date_fin"
                            dateFormat="yy-mm-dd"
                            class="w-full"
                            @input="validateDateFin"
                        />
                        <small v-if="errors.date_fin" class="text-red-500">{{
                            errors.date_fin
                        }}</small>
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="actif">Actif</label>
                        <Checkbox
                            id="actif"
                            v-model="newElement.actif"
                            :binary="true"
                            @change="validateActif"
                            :disabled="newElement.date_fin"
                        />
                        <small v-if="errors.actif" class="text-red-500">{{
                            errors.actif
                        }}</small>
                    </div>

                    <div class="flex gap-5 mt-4">
                        <Button
                            label="Annuler"
                            severity="danger"
                            outlined
                            @click="close"
                            class="w-full"
                        />
                        <Button
                            label="Enregistrer"
                            severity="success"
                            outlined
                            @click="save"
                            :loading="loading"
                            class="w-full"
                        />
                    </div>
                </div>
            </div>
        </template>
    </Dialog>
</template>

<script>
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import { format } from 'date-fns';

export default {
    props: {
        visible: {
            type: Boolean,
            required: true,
        },
        initialData: {
            type: Object,
            default: null,
        },
        specialiteId: {
            type: Number,
            required: true,
        },
    },
    emits: ['update:visible', 'save', 'update'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            newElement: {
                id: null,
                version: '',
                date_debut: null,
                date_fin: null,
                actif: false,
                specialite_id: this.specialiteId,
                observation: '',
            },
            errors: {
                version: '',
                date_debut: '',
                date_fin: '',
                actif: '',
            },
            loading: false,
        };
    },
    computed: {
        isEditing() {
            return !!this.initialData && !!this.initialData.id;
        },
    },
    watch: {
        initialData: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.newElement = {
                        ...newVal,
                        date_debut: newVal.date_debut
                            ? new Date(newVal.date_debut)
                            : null,
                        date_fin: newVal.date_fin
                            ? new Date(newVal.date_fin)
                            : null,
                        specialite_id: this.specialiteId,
                    };
                    this.clearErrors();
                } else {
                    this.resetForm();
                }
            },
        },
        'newElement.date_fin'(newVal) {
            if (newVal) {
                this.newElement.actif = false;
            }
        },
    },
    methods: {
        close() {
            this.$emit('update:visible', false);
            this.resetForm();
        },
        resetForm() {
            this.newElement = {
                id: null,
                version: '',
                date_debut: null,
                date_fin: null,
                actif: false,
                specialite_id: this.specialiteId,
                observation: '',
            };
            this.clearErrors();
        },
        clearErrors() {
            this.errors = {
                version: '',
                date_debut: '',
                date_fin: '',
                actif: '',
            };
        },
        validateVersion() {
            if (!this.newElement.version) {
                this.errors.version = 'La version est obligatoire.';
            } else {
                this.errors.version = '';
            }
        },
        validateDateDebut() {
            if (!this.newElement.date_debut) {
                this.errors.date_debut = 'La date de début est obligatoire.';
            } else if (
                this.newElement.date_fin &&
                new Date(this.newElement.date_debut) >
                    new Date(this.newElement.date_fin)
            ) {
                this.errors.date_debut =
                    'La date de début doit être antérieure à la date de fin.';
            } else {
                this.errors.date_debut = '';
            }
        },
        validateDateFin() {
            if (
                this.newElement.date_fin &&
                new Date(this.newElement.date_fin) <
                    new Date(this.newElement.date_debut)
            ) {
                this.errors.date_fin =
                    'La date de fin doit être postérieure à la date de début.';
            } else {
                this.errors.date_fin = '';
            }
        },
        validateActif() {
            if (this.newElement.date_fin && this.newElement.actif) {
                this.errors.actif =
                    'Un programme avec une date de fin ne peut pas être actif.';
                this.newElement.actif = false;
            } else {
                this.errors.actif = '';
            }
        },
        async save() {
            this.loading = true;
            this.validateVersion();
            this.validateDateDebut();
            this.validateDateFin();
            this.validateActif();

            if (Object.values(this.errors).some((error) => error !== '')) {
                this.loading = false;
                return;
            }

            const formattedData = {
                ...this.newElement,
                specialite_id: this.specialiteId,
                date_debut: this.newElement.date_debut
                    ? format(new Date(this.newElement.date_debut), 'yyyy-MM-dd')
                    : null,
                date_fin: this.newElement.date_fin
                    ? format(new Date(this.newElement.date_fin), 'yyyy-MM-dd')
                    : null,
            };

            try {
                if (this.isEditing) {
                    const response = await axios.put(
                        `/api/programmes/${this.newElement.id}`,
                        formattedData
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Programme modifié avec succès',
                        life: 3000,
                    });
                    this.$emit('update', response.data.data);
                } else {
                    const response = await axios.post(
                        '/api/programmes',
                        formattedData
                    );
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Programme ajouté avec succès',
                        life: 3000,
                    });
                    this.$emit('save', response.data.data);
                }
                this.close();
            } catch (error) {
                if (error.response && error.response.data.errors) {
                    Object.keys(error.response.data.errors).forEach((key) => {
                        this.errors[key] = error.response.data.errors[key][0];
                    });
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Échec de l'enregistrement du programme",
                        life: 3000,
                    });
                }
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>
