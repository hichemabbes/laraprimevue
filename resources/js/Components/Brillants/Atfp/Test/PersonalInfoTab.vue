<template>
    <Card>
        <template #title>Informations personnelles</template>
        <template #content>
            <form @submit.prevent="$emit('update')" class="grid">
                <!-- French Name -->
                <div class="col-12 lg:col-6">
                    <div class="field mb-3">
                        <label for="nom_fr" class="font-semibold"
                            >Nom (FR)*</label
                        >
                        <InputText
                            id="nom_fr"
                            v-model="form.nom_fr"
                            class="w-full"
                            :class="{ 'p-invalid': formErrors.nom_fr }"
                            @input="validateField('nom_fr')"
                        />
                        <small v-if="formErrors.nom_fr" class="p-error">{{
                            formErrors.nom_fr
                        }}</small>
                    </div>
                </div>

                <div class="col-12 lg:col-6">
                    <div class="field mb-3">
                        <label for="prenom_fr" class="font-semibold"
                            >Prénom (FR)*</label
                        >
                        <InputText
                            id="prenom_fr"
                            v-model="form.prenom_fr"
                            class="w-full"
                            :class="{ 'p-invalid': formErrors.prenom_fr }"
                            @input="validateField('prenom_fr')"
                        />
                        <small v-if="formErrors.prenom_fr" class="p-error">{{
                            formErrors.prenom_fr
                        }}</small>
                    </div>
                </div>

                <!-- Arabic Name -->
                <div class="col-12 lg:col-6">
                    <div class="field mb-3">
                        <label for="nom_ar" class="font-semibold"
                            >Nom (AR)</label
                        >
                        <InputText
                            id="nom_ar"
                            v-model="form.nom_ar"
                            class="w-full arabic-font"
                            dir="rtl"
                            :class="{ 'p-invalid': formErrors.nom_ar }"
                        />
                        <small v-if="formErrors.nom_ar" class="p-error">{{
                            formErrors.nom_ar
                        }}</small>
                    </div>
                </div>

                <div class="col-12 lg:col-6">
                    <div class="field mb-3">
                        <label for="prenom_ar" class="font-semibold"
                            >Prénom (AR)</label
                        >
                        <InputText
                            id="prenom_ar"
                            v-model="form.prenom_ar"
                            class="w-full arabic-font"
                            dir="rtl"
                            :class="{ 'p-invalid': formErrors.prenom_ar }"
                        />
                        <small v-if="formErrors.prenom_ar" class="p-error">{{
                            formErrors.prenom_ar
                        }}</small>
                    </div>
                </div>

                <!-- Identification -->
                <div class="col-12 lg:col-6">
                    <div class="field mb-3">
                        <label for="matricule" class="font-semibold"
                            >Matricule</label
                        >
                        <InputText
                            id="matricule"
                            v-model="form.matricule"
                            class="w-full"
                            :class="{ 'p-invalid': formErrors.matricule }"
                        />
                        <small v-if="formErrors.matricule" class="p-error">{{
                            formErrors.matricule
                        }}</small>
                    </div>
                </div>

                <div class="col-12 lg:col-6">
                    <div class="field mb-3">
                        <label for="cin" class="font-semibold">CIN*</label>
                        <InputText
                            id="cin"
                            v-model="form.cin"
                            class="w-full"
                            :class="{ 'p-invalid': formErrors.cin }"
                            @input="validateField('cin')"
                        />
                        <small v-if="formErrors.cin" class="p-error">{{
                            formErrors.cin
                        }}</small>
                    </div>
                </div>

                <!-- Birth Info -->
                <div class="col-12 lg:col-6">
                    <div class="field mb-3">
                        <label for="date_naissance" class="font-semibold"
                            >Date de naissance*</label
                        >
                        <Calendar
                            id="date_naissance"
                            v-model="form.date_naissance"
                            dateFormat="dd/mm/yy"
                            showIcon
                            class="w-full"
                            :class="{ 'p-invalid': formErrors.date_naissance }"
                            @date-select="validateField('date_naissance')"
                        />
                        <small
                            v-if="formErrors.date_naissance"
                            class="p-error"
                            >{{ formErrors.date_naissance }}</small
                        >
                    </div>
                </div>

                <div class="col-12 lg:col-6">
                    <div class="field mb-3">
                        <label for="genre_fr" class="font-semibold"
                            >Genre*</label
                        >
                        <Dropdown
                            id="genre_fr"
                            v-model="form.genre_id"
                            :options="genres"
                            optionLabel="nom"
                            optionValue="id"
                            placeholder="Sélectionner"
                            class="w-full"
                            :class="{ 'p-invalid': formErrors.genre_id }"
                            @change="validateField('genre_id')"
                        />
                        <small v-if="formErrors.genre_id" class="p-error">{{
                            formErrors.genre_id
                        }}</small>
                    </div>
                </div>

                <!-- Nationality -->
                <div class="col-12 lg:col-6">
                    <div class="field mb-3">
                        <label for="nationalite_fr" class="font-semibold"
                            >Nationalité (FR)</label
                        >
                        <InputText
                            id="nationalite_fr"
                            v-model="form.nationalite_fr"
                            class="w-full"
                            :class="{ 'p-invalid': formErrors.nationalite_fr }"
                        />
                        <small
                            v-if="formErrors.nationalite_fr"
                            class="p-error"
                            >{{ formErrors.nationalite_fr }}</small
                        >
                    </div>
                </div>

                <div class="col-12 lg:col-6">
                    <div class="field mb-3">
                        <label for="nationalite_ar" class="font-semibold"
                            >Nationalité (AR)</label
                        >
                        <InputText
                            id="nationalite_ar"
                            v-model="form.nationalite_ar"
                            class="w-full arabic-font"
                            dir="rtl"
                            :class="{ 'p-invalid': formErrors.nationalite_ar }"
                        />
                        <small
                            v-if="formErrors.nationalite_ar"
                            class="p-error"
                            >{{ formErrors.nationalite_ar }}</small
                        >
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="col-12">
                    <div class="flex justify-content-end gap-2">
                        <Button
                            label="Réinitialiser"
                            icon="pi pi-refresh"
                            class="p-button-outlined p-button-secondary"
                            @click="$emit('reset')"
                        />
                        <Button
                            type="submit"
                            label="Enregistrer"
                            icon="pi pi-check"
                            :loading="saving"
                        />
                    </div>
                </div>
            </form>
        </template>
    </Card>
</template>

<script>
import { ref } from 'vue';

export default {
    props: {
        user: Object,
        form: Object,
        formErrors: Object,
        genres: Array,
        saving: Boolean,
    },
    emits: ['update', 'reset'],
    setup(props) {
        const validateField = (field) => {
            const errors = {};

            if (field === 'nom_fr' && !props.form.nom_fr) {
                errors.nom_fr = 'Le nom français est obligatoire';
            }

            if (field === 'prenom_fr' && !props.form.prenom_fr) {
                errors.nom_fr = 'Le prénom français est obligatoire';
            }

            if (field === 'cin' && !props.form.cin) {
                errors.cin = 'Le CIN est obligatoire';
            }

            if (field === 'date_naissance' && !props.form.date_naissance) {
                errors.date_naissance = 'La date de naissance est obligatoire';
            }

            if (field === 'genre_id' && !props.form.genre_id) {
                errors.genre_id = 'Le genre est obligatoire';
            }

            return errors;
        };

        return {
            validateField,
        };
    },
};
</script>

<style scoped>
.arabic-font {
    font-family: 'Amiri', serif;
}

.field {
    margin-bottom: 1.25rem;
}

.field label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--surface-700);
}
</style>
