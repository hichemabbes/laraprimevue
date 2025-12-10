<template>
    <Dialog
        :visible="visible"
        :header="
            typeToEdit ? 'Modifier une catégorie' : 'Ajouter une catégorie'
        "
        :modal="true"
        :style="{ width: '450px' }"
        :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
        @update:visible="$emit('update:visible', $event)"
        :pt="{
            root: 'border-none',
            mask: { style: 'backdrop-filter: blur(2px)' },
            header: { class: 'surface-50 border-bottom-1 surface-border' },
            content: { class: 'surface-50 py-0' },
            footer: { class: 'surface-50 border-top-1 surface-border' },
        }"
    >
        <div class="p-fluid">
            <div class="field">
                <label for="nom_fr" class="font-semibold">Nom (FR)*</label>
                <InputText
                    id="nom_fr"
                    v-model="type.nom_fr"
                    :class="{ 'p-invalid': errors.nom_fr }"
                    class="w-full"
                />
                <small v-if="errors.nom_fr" class="p-error">{{
                    errors.nom_fr
                }}</small>
            </div>
            <div class="field">
                <label for="nom_ar" class="font-semibold">Nom (AR)*</label>
                <InputText
                    id="nom_ar"
                    v-model="type.nom_ar"
                    :class="{ 'p-invalid': errors.nom_ar }"
                    class="w-full"
                />
                <small v-if="errors.nom_ar" class="p-error">{{
                    errors.nom_ar
                }}</small>
            </div>
            <div class="field">
                <label for="description_fr" class="font-semibold"
                    >Description (FR)</label
                >
                <Textarea
                    id="description_fr"
                    v-model="type.description_fr"
                    rows="3"
                    class="w-full"
                    :class="{ 'p-invalid': errors.description_fr }"
                />
                <small v-if="errors.description_fr" class="p-error">{{
                    errors.description_fr
                }}</small>
            </div>
            <div class="field">
                <label for="description_ar" class="font-semibold"
                    >Description (AR)</label
                >
                <Textarea
                    id="description_ar"
                    v-model="type.description_ar"
                    rows="3"
                    class="w-full"
                    :class="{ 'p-invalid': errors.description_ar }"
                />
                <small v-if="errors.description_ar" class="p-error">{{
                    errors.description_ar
                }}</small>
            </div>
            <div class="field">
                <label for="est_predefini" class="font-semibold"
                    >Prédéfini</label
                >
                <InputSwitch id="est_predefini" v-model="type.est_predefini" />
            </div>
            <div class="field">
                <label for="ordre" class="font-semibold">Ordre</label>
                <InputNumber
                    id="ordre"
                    v-model="type.ordre"
                    :min="0"
                    class="w-full"
                    :class="{ 'p-invalid': errors.ordre }"
                />
                <small v-if="errors.ordre" class="p-error">{{
                    errors.ordre
                }}</small>
            </div>
        </div>
        <template #footer>
            <Button
                label="Annuler"
                icon="pi pi-times"
                class="p-button-text"
                @click="close"
            />
            <Button
                :label="typeToEdit ? 'Mettre à jour' : 'Créer'"
                icon="pi pi-check"
                class="p-button-success"
                :loading="saving"
                @click="typeToEdit ? update() : save()"
            />
        </template>
    </Dialog>

    <Toast />
</template>

<script>
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InputSwitch from 'primevue/inputswitch';
import InputNumber from 'primevue/inputnumber';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';

export default {
    components: {
        Button,
        InputText,
        Textarea,
        InputSwitch,
        InputNumber,
        Dialog,
        Toast,
    },
    props: {
        visible: Boolean,
        typeToEdit: Object,
    },
    emits: ['update:visible', 'save', 'update', 'close'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            type: {
                nom_fr: '',
                nom_ar: '',
                description_fr: '',
                description_ar: '',
                est_predefini: false,
                ordre: 0,
            },
            errors: {},
            saving: false,
        };
    },
    watch: {
        typeToEdit(newType) {
            if (newType) {
                this.type = { ...newType };
            } else {
                this.resetForm();
            }
        },
    },
    methods: {
        save() {
            this.errors = {};
            if (!this.type.nom_fr) {
                this.errors.nom_fr = 'Le nom (FR) est requis';
                return;
            }
            if (this.type.nom_fr.length > 50) {
                this.errors.nom_fr =
                    'Le nom (FR) ne doit pas dépasser 50 caractères';
                return;
            }
            if (!this.type.nom_ar) {
                this.errors.nom_ar = 'Le nom (AR) est requis';
                return;
            }
            if (this.type.nom_ar.length > 50) {
                this.errors.nom_ar =
                    'Le nom (AR) ne doit pas dépasser 50 caractères';
                return;
            }
            if (
                this.type.description_fr &&
                this.type.description_fr.length > 255
            ) {
                this.errors.description_fr =
                    'La description (FR) ne doit pas dépasser 255 caractères';
                return;
            }
            if (
                this.type.description_ar &&
                this.type.description_ar.length > 255
            ) {
                this.errors.description_ar =
                    'La description (AR) ne doit pas dépasser 255 caractères';
                return;
            }
            if (this.type.ordre < 0) {
                this.errors.ordre = "L'ordre doit être positif";
                return;
            }

            this.saving = true;
            this.$emit('save', this.type);
            this.saving = false;
        },
        update() {
            this.errors = {};
            if (!this.type.nom_fr) {
                this.errors.nom_fr = 'Le nom (FR) est requis';
                return;
            }
            if (this.type.nom_fr.length > 50) {
                this.errors.nom_fr =
                    'Le nom (FR) ne doit pas dépasser 50 caractères';
                return;
            }
            if (!this.type.nom_ar) {
                this.errors.nom_ar = 'Le nom (AR) est requis';
                return;
            }
            if (this.type.nom_ar.length > 50) {
                this.errors.nom_ar =
                    'Le nom (AR) ne doit pas dépasser 50 caractères';
                return;
            }
            if (
                this.type.description_fr &&
                this.type.description_fr.length > 255
            ) {
                this.errors.description_fr =
                    'La description (FR) ne doit pas dépasser 255 caractères';
                return;
            }
            if (
                this.type.description_ar &&
                this.type.description_ar.length > 255
            ) {
                this.errors.description_ar =
                    'La description (AR) ne doit pas dépasser 255 caractères';
                return;
            }
            if (this.type.ordre < 0) {
                this.errors.ordre = "L'ordre doit être positif";
                return;
            }

            this.saving = true;
            this.$emit('update', this.type);
            this.saving = false;
        },
        close() {
            this.$emit('close');
            this.$emit('update:visible', false);
            this.resetForm();
        },
        resetForm() {
            this.type = {
                nom_fr: '',
                nom_ar: '',
                description_fr: '',
                description_ar: '',
                est_predefini: false,
                ordre: 0,
            };
            this.errors = {};
        },
    },
};
</script>

<style scoped>
.field {
    margin-bottom: 1.5rem;
}

.p-error {
    color: #dc3545;
    font-size: 0.875rem;
}

label {
    margin-bottom: 0.5rem;
    display: block;
    color: #495057;
}

:deep(.p-inputtext),
:deep(.p-textarea),
:deep(.p-inputnumber .p-inputtext) {
    border-radius: 4px;
    border: 1px solid #ced4da;
    padding: 0.5rem;
}

:deep(.p-inputtext:focus),
:deep(.p-textarea:focus),
:deep(.p-inputnumber .p-inputtext:focus) {
    border-color: #6366f1;
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
}

:deep(.p-button-success) {
    background-color: #10b981;
    border-color: #10b981;
}

:deep(.p-button-success:hover) {
    background-color: #059669;
    border-color: #059669;
}
</style>
