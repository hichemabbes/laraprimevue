<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        :pt="{
            root: 'border-none',
            mask: { style: 'backdrop-filter: blur(2px)' },
        }"
        :style="{ width: '450px' }"
    >
        <template #container="{ closeCallback }">
            <div class="relative p-5 surface-card">
                <span
                    class="absolute cursor-pointer"
                    style="
                        top: 12px;
                        right: 12px;
                        width: 32px;
                        height: 32px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    "
                    @click="close"
                >
                    <i class="pi pi-times" style="font-size: 1.2rem"></i>
                </span>

                <div class="flex flex-column gap-4">
                    <h5 class="text-center font-bold text-lg mt-3">
                        {{
                            isEditing
                                ? 'Modification des données'
                                : 'Ajout des données'
                        }}
                    </h5>

                    <div class="flex flex-column gap-2">
                        <label for="homologation" class="font-semibold"
                            >Homologation</label
                        >
                        <Dropdown
                            id="homologation"
                            v-model="newElement.homologation"
                            :options="['Homologuée', 'Non Homologuée']"
                            placeholder="Sélectionner"
                            class="w-full"
                        />
                        <small
                            v-if="errors.homologation"
                            class="text-red-500"
                            >{{ errors.homologation }}</small
                        >
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="heures_et" class="font-semibold"
                            >Heures Techniques</label
                        >
                        <InputText
                            id="heures_et"
                            type="number"
                            v-model="newElement.heures_et"
                            class="w-full"
                            @input="validateHeuresEt"
                        />
                        <small v-if="errors.heures_et" class="text-red-500">{{
                            errors.heures_et
                        }}</small>
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="heures_eg" class="font-semibold"
                            >Heures Généraux</label
                        >
                        <InputText
                            id="heures_eg"
                            type="number"
                            v-model="newElement.heures_eg"
                            class="w-full"
                            @input="validateHeuresEg"
                        />
                        <small v-if="errors.heures_eg" class="text-red-500">{{
                            errors.heures_eg
                        }}</small>
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="duree_formation" class="font-semibold"
                            >Durée de Formation</label
                        >
                        <InputNumber
                            id="duree_formation"
                            v-model="newElement.duree_formation"
                            mode="decimal"
                            :minFractionDigits="1"
                            :maxFractionDigits="1"
                            class="w-full"
                        />
                        <small
                            v-if="errors.duree_formation"
                            class="text-red-500"
                            >{{ errors.duree_formation }}</small
                        >
                    </div>

                    <div class="flex flex-column gap-2">
                        <label for="observation" class="font-semibold"
                            >Observation</label
                        >
                        <Textarea
                            id="observation"
                            v-model="newElement.observation"
                            rows="3"
                            class="w-full"
                        />
                        <small v-if="errors.observation" class="text-red-500">{{
                            errors.observation
                        }}</small>
                    </div>

                    <div class="flex gap-3 justify-content-center">
                        <Button
                            label="Annuler"
                            severity="danger"
                            outlined
                            @click="close"
                            class="w-full"
                        />
                        <Button
                            :label="isEditing ? 'Modifier' : 'Enregistrer'"
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
    <Toast />
</template>

<script>
import axios from 'axios';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Toast from 'primevue/toast';
import { useToast as usePrimeToast } from 'primevue/usetoast';

export default {
    props: {
        visible: {
            type: Boolean,
            required: true,
        },
        elementToEdit: {
            type: Object,
            default: null,
        },
    },
    emits: ['update:visible', 'saved'],
    components: {
        Dialog,
        Dropdown,
        InputText,
        InputNumber,
        Textarea,
        Button,
        Toast,
    },
    setup() {
        const toast = usePrimeToast();
        return { toast };
    },
    data() {
        return {
            newElement: {
                homologation: null,
                heures_et: null,
                heures_eg: null,
                duree_formation: null,
                observation: null,
            },
            errors: {},
            loading: false,
            isEditing: false,
        };
    },
    watch: {
        elementToEdit: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.isEditing = true;
                    this.newElement = { ...newVal };
                } else {
                    this.isEditing = false;
                    this.resetForm();
                }
            },
        },
    },
    methods: {
        close() {
            this.$emit('update:visible', false);
            this.resetForm();
        },
        resetForm() {
            this.newElement = {
                homologation: null,
                heures_et: null,
                heures_eg: null,
                duree_formation: null,
                observation: null,
            };
            this.errors = {};
        },
        validateHomologation() {
            if (!this.newElement.homologation) {
                this.errors.homologation = 'Ce champ est requis.';
            } else {
                delete this.errors.homologation;
            }
        },
        validateHeuresEt() {
            if (!this.newElement.heures_et || this.newElement.heures_et <= 0) {
                this.errors.heures_et = 'Veuillez entrer une valeur positive.';
            } else {
                delete this.errors.heures_et;
            }
        },
        validateHeuresEg() {
            if (!this.newElement.heures_eg || this.newElement.heures_eg <= 0) {
                this.errors.heures_eg = 'Veuillez entrer une valeur positive.';
            } else {
                delete this.errors.heures_eg;
            }
        },
        async save() {
            this.loading = true;

            this.validateHomologation();
            this.validateHeuresEt();
            this.validateHeuresEg();

            if (Object.keys(this.errors).length > 0) {
                this.loading = false;
                return;
            }

            try {
                let response;
                if (this.isEditing) {
                    response = await axios.put(
                        `/api/informations-specialites/${this.newElement.id}`,
                        this.newElement
                    );
                } else {
                    response = await axios.post(
                        '/api/informations-specialites',
                        this.newElement
                    );
                }

                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: this.isEditing
                        ? 'Données modifiées avec succès.'
                        : 'Données ajoutées avec succès.',
                    life: 3000,
                });
                this.$emit('saved', response.data);
                this.close();
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Une erreur est survenue lors de l'enregistrement.",
                    life: 3000,
                });
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>
