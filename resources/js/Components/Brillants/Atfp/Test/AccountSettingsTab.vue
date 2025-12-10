<template>
    <Card>
        <template #title>Paramètres du compte</template>
        <template #content>
            <!-- Account Info Form -->
            <form @submit.prevent="$emit('update')" class="grid">
                <div class="col-12 lg:col-6">
                    <div class="field mb-3">
                        <label for="email" class="font-semibold">Email*</label>
                        <InputText
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="w-full"
                            :class="{ 'p-invalid': formErrors.email }"
                            @input="validateEmail"
                        />
                        <small v-if="formErrors.email" class="p-error">{{
                            formErrors.email
                        }}</small>
                    </div>
                </div>

                <div class="col-12 lg:col-6">
                    <div class="field mb-3">
                        <label for="statut" class="font-semibold"
                            >Statut*</label
                        >
                        <Dropdown
                            id="statut"
                            v-model="form.statut"
                            :options="['Actif', 'Inactif']"
                            placeholder="Sélectionner"
                            class="w-full"
                            :class="{ 'p-invalid': formErrors.statut }"
                            @change="validateField('statut')"
                        />
                        <small v-if="formErrors.statut" class="p-error">{{
                            formErrors.statut
                        }}</small>
                    </div>
                </div>

                <div class="col-12 lg:col-6">
                    <div class="field mb-3">
                        <label for="role" class="font-semibold">Rôle*</label>
                        <Dropdown
                            id="role"
                            v-model="form.role"
                            :options="roles"
                            optionLabel="name"
                            optionValue="name"
                            placeholder="Sélectionner"
                            class="w-full"
                            :class="{ 'p-invalid': formErrors.role }"
                            @change="validateRole"
                        />
                        <small v-if="formErrors.role" class="p-error">{{
                            formErrors.role
                        }}</small>
                    </div>
                </div>

                <div class="col-12 lg:col-6" v-if="isCentreRole">
                    <div class="field mb-3">
                        <label for="centre_id" class="font-semibold"
                            >Centre*</label
                        >
                        <Dropdown
                            id="centre_id"
                            v-model="form.centre_id"
                            :options="centres"
                            optionLabel="nom_fr"
                            optionValue="id"
                            placeholder="Sélectionner"
                            class="w-full"
                            :class="{ 'p-invalid': formErrors.centre_id }"
                            @change="validateField('centre_id')"
                        />
                        <small v-if="formErrors.centre_id" class="p-error">{{
                            formErrors.centre_id
                        }}</small>
                    </div>
                </div>

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

            <!-- Password Change Form -->
            <Divider />
            <h4 class="mb-4">Changer le mot de passe</h4>

            <form @submit.prevent="$emit('update-password')" class="grid">
                <div class="col-12">
                    <div class="field mb-3">
                        <label for="current_password" class="font-semibold"
                            >Mot de passe actuel*</label
                        >
                        <Password
                            id="current_password"
                            v-model="passwordForm.current_password"
                            toggleMask
                            class="w-full"
                            :class="{
                                'p-invalid': passwordErrors.current_password,
                            }"
                            :feedback="false"
                            @input="validatePassword"
                        />
                        <small
                            v-if="passwordErrors.current_password"
                            class="p-error"
                            >{{ passwordErrors.current_password }}</small
                        >
                    </div>
                </div>

                <div class="col-12 lg:col-6">
                    <div class="field mb-3">
                        <label for="new_password" class="font-semibold"
                            >Nouveau mot de passe*</label
                        >
                        <Password
                            id="new_password"
                            v-model="passwordForm.new_password"
                            toggleMask
                            class="w-full"
                            :class="{
                                'p-invalid': passwordErrors.new_password,
                            }"
                            @input="validatePassword"
                        />
                        <small
                            v-if="passwordErrors.new_password"
                            class="p-error"
                            >{{ passwordErrors.new_password }}</small
                        >
                    </div>
                </div>

                <div class="col-12 lg:col-6">
                    <div class="field mb-3">
                        <label
                            for="new_password_confirmation"
                            class="font-semibold"
                            >Confirmer*</label
                        >
                        <Password
                            id="new_password_confirmation"
                            v-model="passwordForm.new_password_confirmation"
                            toggleMask
                            class="w-full"
                            :class="{
                                'p-invalid':
                                    passwordErrors.new_password_confirmation,
                            }"
                            :feedback="false"
                            @input="validatePassword"
                        />
                        <small
                            v-if="passwordErrors.new_password_confirmation"
                            class="p-error"
                            >{{
                                passwordErrors.new_password_confirmation
                            }}</small
                        >
                    </div>
                </div>

                <div class="col-12">
                    <div class="flex justify-content-end gap-2">
                        <Button
                            label="Réinitialiser"
                            icon="pi pi-refresh"
                            class="p-button-outlined p-button-secondary"
                            @click="$emit('reset-password')"
                        />
                        <Button
                            type="submit"
                            label="Modifier"
                            icon="pi pi-key"
                            :loading="changingPassword"
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
        roles: Array,
        centres: Array,
        isCentreRole: Boolean,
        passwordForm: Object,
        passwordErrors: Object,
        saving: Boolean,
        changingPassword: Boolean,
    },
    emits: ['update', 'update-password', 'reset', 'reset-password'],
    setup(props) {
        const validateEmail = () => {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!props.form.email) {
                return { email: "L'email est obligatoire" };
            } else if (!emailRegex.test(props.form.email)) {
                return { email: "L'email n'est pas valide" };
            }
            return { email: '' };
        };

        const validateRole = () => {
            if (!props.form.role) {
                return { role: 'Le rôle est obligatoire' };
            }
            return { role: '' };
        };

        const validateField = (field) => {
            if (!props.form[field]) {
                return { [field]: 'Ce champ est obligatoire' };
            }
            return { [field]: '' };
        };

        const validatePassword = () => {
            const errors = {};

            if (!props.passwordForm.current_password) {
                errors.current_password =
                    'Le mot de passe actuel est obligatoire';
            }

            if (!props.passwordForm.new_password) {
                errors.new_password = 'Le nouveau mot de passe est obligatoire';
            } else if (props.passwordForm.new_password.length < 8) {
                errors.new_password =
                    'Le mot de passe doit contenir au moins 8 caractères';
            }

            if (!props.passwordForm.new_password_confirmation) {
                errors.new_password_confirmation =
                    'La confirmation est obligatoire';
            } else if (
                props.passwordForm.new_password !==
                props.passwordForm.new_password_confirmation
            ) {
                errors.new_password_confirmation =
                    'Les mots de passe ne correspondent pas';
            }

            return errors;
        };

        return {
            validateEmail,
            validateRole,
            validateField,
            validatePassword,
        };
    },
};
</script>

<style scoped>
.field {
    margin-bottom: 1.25rem;
}

.field label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--surface-700);
}

:deep(.p-password-input) {
    width: 100%;
}
</style>
