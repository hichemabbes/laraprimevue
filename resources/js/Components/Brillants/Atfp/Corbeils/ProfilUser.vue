<template>
    <main-layout>
        <div class="surface-ground pt-2 px-1.5 pb-1.5" style="margin-top: 0">
            <!-- Header Section with Navbar -->
            <div
                class="surface-card p-2 border-round border-1 surface-border"
                style="
                    position: relative;
                    top: -34px;
                    box-shadow: none;
                    margin-bottom: -32px;
                "
            >
                <div class="flex justify-content-between align-items-center">
                    <div class="flex gap-3">
                        <Button
                            label="Accueil"
                            icon="pi pi-home"
                            class="p-button-text p-button-plain"
                            @click="$router.push({ name: 'dashboard' })"
                        />
                        <Button
                            label="Profil"
                            icon="pi pi-user"
                            class="p-button-text p-button-plain"
                            disabled
                        />
                    </div>
                    <div class="flex gap-2">
                        <Button
                            icon="pi pi-refresh"
                            class="p-button-text p-button-plain"
                            v-tooltip="'Rafraîchir'"
                            @click="refreshProfile"
                        />
                        <Button
                            icon="pi pi-print"
                            class="p-button-text p-button-plain"
                            v-tooltip="'Imprimer'"
                            @click="printProfile"
                        />
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div
                v-if="loading"
                class="flex justify-content-center align-items-center h-20rem"
            >
                <ProgressSpinner style="width: 50px; height: 50px" />
            </div>

            <!-- Error State -->
            <div
                v-else-if="error"
                class="surface-card p-4 border-round shadow-2 border-1 surface-border"
            >
                <Message severity="error" :closable="false">{{
                    error
                }}</Message>
            </div>

            <!-- Main Content Section -->
            <div
                v-else
                class="profile-container surface-card p-4 border-round shadow-2 border-1 surface-border"
            >
                <div class="grid">
                    <!-- Left Column: Basic Info -->
                    <div class="col-12 lg:col-3">
                        <!-- Profile Card -->
                        <Card class="profile-card mb-4">
                            <template #header>
                                <div
                                    class="flex justify-content-center p-3 relative"
                                >
                                    <Avatar
                                        :image="
                                            user.photo || '/default-avatar.png'
                                        "
                                        shape="circle"
                                        class="custom-avatar"
                                    />
                                    <Button
                                        icon="pi pi-camera"
                                        class="p-button-rounded p-button-sm photo-upload-button"
                                        severity="info"
                                        v-tooltip="'Changer la photo'"
                                        @click="showAvatarDialog = true"
                                    />
                                </div>
                            </template>
                            <template #title>
                                {{ user.nom_fr || 'Non défini' }}
                                {{ user.prenom_fr || '' }}
                            </template>
                            <template #content>
                                <div class="field mb-3">
                                    <Tag
                                        :value="
                                            user.roles &&
                                            Array.isArray(user.roles)
                                                ? user.roles
                                                      .map((role) => role.name)
                                                      .join(', ')
                                                : 'Aucun rôle'
                                        "
                                        severity="info"
                                    />
                                </div>
                                <div class="field mb-3">
                                    <label class="font-semibold"
                                        >Matricule</label
                                    >
                                    <div>
                                        {{ user.matricule || 'Non défini' }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="font-semibold">Centre</label>
                                    <div>
                                        <Tag
                                            :value="
                                                user.centres &&
                                                Array.isArray(user.centres) &&
                                                user.centres.length > 0
                                                    ? user.centres[0].nom_fr
                                                    : 'Aucun centre'
                                            "
                                            severity="success"
                                        />
                                    </div>
                                </div>
                            </template>
                        </Card>

                        <!-- Contact Info -->
                        <Card class="mb-4">
                            <template #title>Informations de contact</template>
                            <template #content>
                                <div class="grid">
                                    <div class="col-12 lg:col-6">
                                        <div class="field mb-3">
                                            <label class="font-semibold"
                                                >Email</label
                                            >
                                            <div
                                                class="flex align-items-center gap-2"
                                            >
                                                <i class="pi pi-envelope"></i>
                                                <a
                                                    :href="`mailto:${user.email}`"
                                                    >{{
                                                        user.email ||
                                                        'Non défini'
                                                    }}</a
                                                >
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 lg:col-6">
                                        <div class="field mb-3">
                                            <label class="font-semibold"
                                                >CIN</label
                                            >
                                            <div
                                                class="flex align-items-center gap-2"
                                            >
                                                <i class="pi pi-id-card"></i>
                                                <span>{{
                                                    user.cin || 'Non défini'
                                                }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="field mb-3">
                                            <label class="font-semibold"
                                                >Date de naissance</label
                                            >
                                            <div
                                                class="flex align-items-center gap-2"
                                            >
                                                <i class="pi pi-calendar"></i>
                                                <span>{{
                                                    formatDate(
                                                        user.date_naissance
                                                    )
                                                }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 lg:col-6">
                                        <div class="field mb-3">
                                            <label class="font-semibold"
                                                >Nationalité (FR)</label
                                            >
                                            <div
                                                class="flex align-items-center gap-2"
                                            >
                                                <i class="pi pi-flag"></i>
                                                <span>{{
                                                    user.nationalite_fr ||
                                                    'Tunisienne'
                                                }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 lg:col-6">
                                        <div class="field">
                                            <label class="font-semibold"
                                                >Nationalité (AR)</label
                                            >
                                            <div
                                                class="flex align-items-center gap-2 arabic-font"
                                            >
                                                <i class="pi pi-flag"></i>
                                                <span>{{
                                                    user.nationalite_ar ||
                                                    'تونسية'
                                                }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Card>
                    </div>

                    <!-- Right Column: Detailed Info -->
                    <div class="col-12 lg:col-9">
                        <TabView class="profile-tabs">
                            <TabPanel header="Informations personnelles">
                                <Card>
                                    <template #title
                                        >Informations personnelles</template
                                    >
                                    <template #content>
                                        <form
                                            @submit.prevent="updateProfile"
                                            class="grid"
                                        >
                                            <div class="col-12 lg:col-6">
                                                <div class="field mb-3">
                                                    <label
                                                        for="nom_fr"
                                                        class="font-semibold"
                                                        >Nom (FR)</label
                                                    >
                                                    <InputText
                                                        id="nom_fr"
                                                        v-model="form.nom_fr"
                                                        class="w-full"
                                                        :class="{
                                                            'p-invalid':
                                                                formErrors.nom_fr,
                                                        }"
                                                        @input="validateNomFr"
                                                    />
                                                    <small
                                                        v-if="formErrors.nom_fr"
                                                        class="p-error"
                                                        >{{
                                                            formErrors.nom_fr
                                                        }}</small
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-12 lg:col-6">
                                                <div class="field mb-3">
                                                    <label
                                                        for="prenom_fr"
                                                        class="font-semibold"
                                                        >Prénom (FR)</label
                                                    >
                                                    <InputText
                                                        id="prenom_fr"
                                                        v-model="form.prenom_fr"
                                                        class="w-full"
                                                        :class="{
                                                            'p-invalid':
                                                                formErrors.prenom_fr,
                                                        }"
                                                        @input="
                                                            validatePrenomFr
                                                        "
                                                    />
                                                    <small
                                                        v-if="
                                                            formErrors.prenom_fr
                                                        "
                                                        class="p-error"
                                                        >{{
                                                            formErrors.prenom_fr
                                                        }}</small
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-12 lg:col-6">
                                                <div class="field mb-3">
                                                    <label
                                                        for="nom_ar"
                                                        class="font-semibold"
                                                        >Nom (AR)</label
                                                    >
                                                    <InputText
                                                        id="nom_ar"
                                                        v-model="form.nom_ar"
                                                        class="w-full arabic-font"
                                                        dir="rtl"
                                                        :class="{
                                                            'p-invalid':
                                                                formErrors.nom_ar,
                                                        }"
                                                    />
                                                    <small
                                                        v-if="formErrors.nom_ar"
                                                        class="p-error"
                                                        >{{
                                                            formErrors.nom_ar
                                                        }}</small
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-12 lg:col-6">
                                                <div class="field mb-3">
                                                    <label
                                                        for="prenom_ar"
                                                        class="font-semibold"
                                                        >Prénom (AR)</label
                                                    >
                                                    <InputText
                                                        id="prenom_ar"
                                                        v-model="form.prenom_ar"
                                                        class="w-full arabic-font"
                                                        dir="rtl"
                                                        :class="{
                                                            'p-invalid':
                                                                formErrors.prenom_ar,
                                                        }"
                                                    />
                                                    <small
                                                        v-if="
                                                            formErrors.prenom_ar
                                                        "
                                                        class="p-error"
                                                        >{{
                                                            formErrors.prenom_ar
                                                        }}</small
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-12 lg:col-6">
                                                <div class="field mb-3">
                                                    <label
                                                        for="matricule"
                                                        class="font-semibold"
                                                        >Matricule</label
                                                    >
                                                    <InputText
                                                        id="matricule"
                                                        v-model="form.matricule"
                                                        class="w-full"
                                                        :class="{
                                                            'p-invalid':
                                                                formErrors.matricule,
                                                        }"
                                                    />
                                                    <small
                                                        v-if="
                                                            formErrors.matricule
                                                        "
                                                        class="p-error"
                                                        >{{
                                                            formErrors.matricule
                                                        }}</small
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-12 lg:col-6">
                                                <div class="field mb-3">
                                                    <label
                                                        for="cin"
                                                        class="font-semibold"
                                                        >CIN</label
                                                    >
                                                    <InputText
                                                        id="cin"
                                                        v-model="form.cin"
                                                        class="w-full"
                                                        :class="{
                                                            'p-invalid':
                                                                formErrors.cin,
                                                        }"
                                                    />
                                                    <small
                                                        v-if="formErrors.cin"
                                                        class="p-error"
                                                        >{{
                                                            formErrors.cin
                                                        }}</small
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-12 lg:col-6">
                                                <div class="field mb-3">
                                                    <label
                                                        for="date_naissance"
                                                        class="font-semibold"
                                                        >Date de
                                                        naissance</label
                                                    >
                                                    <Calendar
                                                        id="date_naissance"
                                                        v-model="
                                                            form.date_naissance
                                                        "
                                                        dateFormat="dd/mm/yy"
                                                        showIcon
                                                        class="w-full"
                                                        :class="{
                                                            'p-invalid':
                                                                formErrors.date_naissance,
                                                        }"
                                                    />
                                                    <small
                                                        v-if="
                                                            formErrors.date_naissance
                                                        "
                                                        class="p-error"
                                                        >{{
                                                            formErrors.date_naissance
                                                        }}</small
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-12 lg:col-6">
                                                <div class="field mb-3">
                                                    <label
                                                        for="genre_fr"
                                                        class="font-semibold"
                                                        >Genre</label
                                                    >
                                                    <Dropdown
                                                        id="genre_fr"
                                                        v-model="form.genre_id"
                                                        :options="genres"
                                                        optionLabel="nom"
                                                        optionValue="id"
                                                        placeholder="Sélectionner"
                                                        class="w-full"
                                                        :class="{
                                                            'p-invalid':
                                                                formErrors.genre_fr,
                                                        }"
                                                    />
                                                    <small
                                                        v-if="
                                                            formErrors.genre_fr
                                                        "
                                                        class="p-error"
                                                        >{{
                                                            formErrors.genre_fr
                                                        }}</small
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-12 lg:col-6">
                                                <div class="field mb-3">
                                                    <label
                                                        for="nationalite_fr"
                                                        class="font-semibold"
                                                        >Nationalité (FR)</label
                                                    >
                                                    <InputText
                                                        id="nationalite_fr"
                                                        v-model="
                                                            form.nationalite_fr
                                                        "
                                                        class="w-full"
                                                        :class="{
                                                            'p-invalid':
                                                                formErrors.nationalite_fr,
                                                        }"
                                                    />
                                                    <small
                                                        v-if="
                                                            formErrors.nationalite_fr
                                                        "
                                                        class="p-error"
                                                        >{{
                                                            formErrors.nationalite_fr
                                                        }}</small
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-12 lg:col-6">
                                                <div class="field mb-3">
                                                    <label
                                                        for="nationalite_ar"
                                                        class="font-semibold"
                                                        >Nationalité (AR)</label
                                                    >
                                                    <InputText
                                                        id="nationalite_ar"
                                                        v-model="
                                                            form.nationalite_ar
                                                        "
                                                        class="w-full arabic-font"
                                                        dir="rtl"
                                                        :class="{
                                                            'p-invalid':
                                                                formErrors.nationalite_ar,
                                                        }"
                                                    />
                                                    <small
                                                        v-if="
                                                            formErrors.nationalite_ar
                                                        "
                                                        class="p-error"
                                                        >{{
                                                            formErrors.nationalite_ar
                                                        }}</small
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div
                                                    class="flex justify-content-end gap-2"
                                                >
                                                    <Button
                                                        label="Réinitialiser"
                                                        icon="pi pi-refresh"
                                                        class="p-button-outlined p-button-secondary"
                                                        @click="resetForm"
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
                            </TabPanel>
                            <TabPanel header="Paramètres du compte">
                                <Card>
                                    <template #title
                                        >Paramètres du compte</template
                                    >
                                    <template #content>
                                        <form
                                            @submit.prevent="updateProfile"
                                            class="grid"
                                        >
                                            <div class="col-12 lg:col-6">
                                                <div class="field mb-3">
                                                    <label
                                                        for="email"
                                                        class="font-semibold"
                                                        >Email</label
                                                    >
                                                    <InputText
                                                        id="email"
                                                        v-model="form.email"
                                                        type="email"
                                                        class="w-full"
                                                        :class="{
                                                            'p-invalid':
                                                                formErrors.email,
                                                        }"
                                                        @input="validateEmail"
                                                    />
                                                    <small
                                                        v-if="formErrors.email"
                                                        class="p-error"
                                                        >{{
                                                            formErrors.email
                                                        }}</small
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-12 lg:col-6">
                                                <div class="field mb-3">
                                                    <label
                                                        for="statut"
                                                        class="font-semibold"
                                                        >Statut</label
                                                    >
                                                    <Dropdown
                                                        id="statut"
                                                        v-model="form.statut"
                                                        :options="[
                                                            'Actif',
                                                            'Inactif',
                                                        ]"
                                                        placeholder="Sélectionner"
                                                        class="w-full"
                                                        :class="{
                                                            'p-invalid':
                                                                formErrors.statut,
                                                        }"
                                                    />
                                                    <small
                                                        v-if="formErrors.statut"
                                                        class="p-error"
                                                        >{{
                                                            formErrors.statut
                                                        }}</small
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-12 lg:col-6">
                                                <div class="field mb-3">
                                                    <label
                                                        for="role"
                                                        class="font-semibold"
                                                        >Rôle</label
                                                    >
                                                    <Dropdown
                                                        id="role"
                                                        v-model="form.role"
                                                        :options="roles"
                                                        optionLabel="name"
                                                        optionValue="name"
                                                        placeholder="Sélectionner"
                                                        class="w-full"
                                                        :class="{
                                                            'p-invalid':
                                                                formErrors.role,
                                                        }"
                                                        @change="validateRole"
                                                    />
                                                    <small
                                                        v-if="formErrors.role"
                                                        class="p-error"
                                                        >{{
                                                            formErrors.role
                                                        }}</small
                                                    >
                                                </div>
                                            </div>
                                            <div
                                                class="col-12 lg:col-6"
                                                v-if="isCentreRole"
                                            >
                                                <div class="field mb-3">
                                                    <label
                                                        for="centre_id"
                                                        class="font-semibold"
                                                        >Centre</label
                                                    >
                                                    <Dropdown
                                                        id="centre_id"
                                                        v-model="form.centre_id"
                                                        :options="centres"
                                                        optionLabel="nom_fr"
                                                        optionValue="id"
                                                        placeholder="Sélectionner"
                                                        class="w-full"
                                                        :class="{
                                                            'p-invalid':
                                                                formErrors.centre_id,
                                                        }"
                                                    />
                                                    <small
                                                        v-if="
                                                            formErrors.centre_id
                                                        "
                                                        class="p-error"
                                                        >{{
                                                            formErrors.centre_id
                                                        }}</small
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div
                                                    class="flex justify-content-end gap-2"
                                                >
                                                    <Button
                                                        label="Réinitialiser"
                                                        icon="pi pi-refresh"
                                                        class="p-button-outlined p-button-secondary"
                                                        @click="resetForm"
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
                                        <div class="mt-4">
                                            <h4 class="mb-3">
                                                Changer le mot de passe
                                            </h4>
                                            <form
                                                @submit.prevent="updatePassword"
                                                class="grid"
                                            >
                                                <div class="col-12">
                                                    <div class="field mb-3">
                                                        <label
                                                            for="current_password"
                                                            class="font-semibold"
                                                            >Mot de passe
                                                            actuel</label
                                                        >
                                                        <Password
                                                            id="current_password"
                                                            v-model="
                                                                passwordForm.current_password
                                                            "
                                                            toggleMask
                                                            class="w-full"
                                                            :class="{
                                                                'p-invalid':
                                                                    passwordErrors.current_password,
                                                            }"
                                                            :feedback="false"
                                                        />
                                                        <small
                                                            v-if="
                                                                passwordErrors.current_password
                                                            "
                                                            class="p-error"
                                                            >{{
                                                                passwordErrors.current_password
                                                            }}</small
                                                        >
                                                    </div>
                                                </div>
                                                <div class="col-12 lg:col-6">
                                                    <div class="field mb-3">
                                                        <label
                                                            for="new_password"
                                                            class="font-semibold"
                                                            >Nouveau mot de
                                                            passe</label
                                                        >
                                                        <Password
                                                            id="new_password"
                                                            v-model="
                                                                passwordForm.new_password
                                                            "
                                                            toggleMask
                                                            class="w-full"
                                                            :class="{
                                                                'p-invalid':
                                                                    passwordErrors.new_password,
                                                            }"
                                                            @input="
                                                                validatePassword
                                                            "
                                                        />
                                                        <small
                                                            v-if="
                                                                passwordErrors.new_password
                                                            "
                                                            class="p-error"
                                                            >{{
                                                                passwordErrors.new_password
                                                            }}</small
                                                        >
                                                    </div>
                                                </div>
                                                <div class="col-12 lg:col-6">
                                                    <div class="field mb-3">
                                                        <label
                                                            for="new_password_confirmation"
                                                            class="font-semibold"
                                                            >Confirmer</label
                                                        >
                                                        <Password
                                                            id="new_password_confirmation"
                                                            v-model="
                                                                passwordForm.new_password_confirmation
                                                            "
                                                            toggleMask
                                                            class="w-full"
                                                            :class="{
                                                                'p-invalid':
                                                                    passwordErrors.new_password_confirmation,
                                                            }"
                                                            :feedback="false"
                                                            @input="
                                                                validatePassword
                                                            "
                                                        />
                                                        <small
                                                            v-if="
                                                                passwordErrors.new_password_confirmation
                                                            "
                                                            class="p-error"
                                                            >{{
                                                                passwordErrors.new_password_confirmation
                                                            }}</small
                                                        >
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div
                                                        class="flex justify-content-end gap-2"
                                                    >
                                                        <Button
                                                            label="Réinitialiser"
                                                            icon="pi pi-refresh"
                                                            class="p-button-outlined p-button-secondary"
                                                            @click="
                                                                resetPasswordForm
                                                            "
                                                        />
                                                        <Button
                                                            type="submit"
                                                            label="Modifier"
                                                            icon="pi pi-key"
                                                            :loading="
                                                                changingPassword
                                                            "
                                                        />
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </template>
                                </Card>
                            </TabPanel>
                            <TabPanel header="Activité">
                                <Card>
                                    <template #title>Activité</template>
                                    <template #content>
                                        <div class="grid">
                                            <div class="col-12 lg:col-6">
                                                <div class="activity-card">
                                                    <div class="activity-icon">
                                                        <i
                                                            class="pi pi-sign-in"
                                                        ></i>
                                                    </div>
                                                    <div
                                                        class="activity-content"
                                                    >
                                                        <h5>
                                                            Dernière connexion
                                                        </h5>
                                                        <p>
                                                            {{
                                                                formatDateTime(
                                                                    user.last_login_at
                                                                )
                                                            }}
                                                        </p>
                                                        <small
                                                            class="text-color-secondary"
                                                            >Adresse IP:
                                                            {{
                                                                user.last_login_ip ||
                                                                'Inconnue'
                                                            }}</small
                                                        >
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 lg:col-6">
                                                <div class="activity-card">
                                                    <div class="activity-icon">
                                                        <i
                                                            class="pi pi-calendar-plus"
                                                        ></i>
                                                    </div>
                                                    <div
                                                        class="activity-content"
                                                    >
                                                        <h5>
                                                            Date de création
                                                        </h5>
                                                        <p>
                                                            {{
                                                                formatDateTime(
                                                                    user.created_at
                                                                )
                                                            }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 lg:col-6">
                                                <div class="activity-card">
                                                    <div class="activity-icon">
                                                        <i
                                                            class="pi pi-calendar-edit"
                                                        ></i>
                                                    </div>
                                                    <div
                                                        class="activity-content"
                                                    >
                                                        <h5>
                                                            Dernière mise à jour
                                                        </h5>
                                                        <p>
                                                            {{
                                                                formatDateTime(
                                                                    user.updated_at
                                                                )
                                                            }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 lg:col-6">
                                                <div class="activity-card">
                                                    <div class="activity-icon">
                                                        <i
                                                            class="pi pi-history"
                                                        ></i>
                                                    </div>
                                                    <div
                                                        class="activity-content"
                                                    >
                                                        <h5>
                                                            Activité récente
                                                        </h5>
                                                        <p>
                                                            {{
                                                                recentActivity ||
                                                                'Aucune activité récente'
                                                            }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <Divider />
                                        <h5 class="mb-3">
                                            Historique des connexions
                                        </h5>
                                        <div
                                            v-if="loadingHistory"
                                            class="flex justify-content-center"
                                        >
                                            <ProgressSpinner
                                                style="
                                                    width: 50px;
                                                    height: 50px;
                                                "
                                            />
                                        </div>
                                        <div
                                            v-else-if="
                                                !Array.isArray(loginHistory) ||
                                                loginHistory.length === 0
                                            "
                                        >
                                            <Message
                                                severity="info"
                                                :closable="false"
                                                >Aucun historique de connexion
                                                disponible</Message
                                            >
                                        </div>
                                        <DataTable
                                            v-else
                                            :value="loginHistory"
                                            :paginator="true"
                                            :rows="5"
                                            :rowsPerPageOptions="[5, 10, 20]"
                                            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                                            currentPageReportTemplate="Affichage de {first} à {last} sur {totalRecords} entrées"
                                            responsiveLayout="scroll"
                                        >
                                            <Column
                                                field="date"
                                                header="Date"
                                                sortable
                                            >
                                                <template #body="{ data }">
                                                    {{
                                                        formatDateTime(
                                                            data.date
                                                        )
                                                    }}
                                                </template>
                                            </Column>
                                            <Column
                                                field="ip"
                                                header="Adresse IP"
                                                sortable
                                            ></Column>
                                            <Column
                                                field="device"
                                                header="Appareil"
                                                sortable
                                            ></Column>
                                            <Column
                                                field="location"
                                                header="Localisation"
                                                sortable
                                            ></Column>
                                        </DataTable>
                                    </template>
                                </Card>
                            </TabPanel>
                        </TabView>
                    </div>
                </div>
            </div>

            <!-- Avatar Upload Dialog -->
            <ErrorBoundary>
                <Dialog
                    v-model:visible="showAvatarDialog"
                    header="Changer la Photo de Profil"
                    :modal="true"
                    :style="{ width: '30rem' }"
                >
                    <div class="flex flex-column align-items-center gap-3">
                        <div v-if="previewImage" class="cropper-container">
                            <Cropper
                                :src="previewImage"
                                :stencil-props="{
                                    aspectRatio: 1,
                                    movable: true,
                                    resizable: true,
                                    resizeHandlers: {
                                        enabled: true,
                                        positions: ['bottom-right'],
                                    },
                                    style: {
                                        border: '2px solid #3b82f6',
                                        borderRadius: '50%',
                                        background: 'transparent',
                                    },
                                    initialSize: { width: 200, height: 200 },
                                }"
                                :canvas="{ minWidth: 100, minHeight: 100 }"
                                @change="onCropChange"
                                @ready="onCropperReady"
                            />
                        </div>
                        <div
                            v-else
                            class="flex flex-column align-items-center gap-2"
                        >
                            <Avatar
                                :image="user.photo || '/default-avatar.png'"
                                shape="circle"
                                class="custom-avatar"
                            />
                            <small class="text-error" v-if="imageError">{{
                                imageError
                            }}</small>
                        </div>
                        <FileUpload
                            mode="basic"
                            accept="image/*"
                            :maxFileSize="2000000"
                            chooseLabel="Choisir une Image"
                            @select="onAvatarSelect"
                            class="w-full"
                        />
                        <small class="text-500"
                            >Taille max: 2MB (JPG, PNG)</small
                        >
                    </div>
                    <template #footer>
                        <Button
                            label="Annuler"
                            icon="pi pi-times"
                            text
                            @click="cancelAvatarUpload"
                        />
                        <Button
                            label="Enregistrer"
                            icon="pi pi-check"
                            :disabled="!selectedFile"
                            :loading="uploadingAvatar"
                            @click="uploadAvatar"
                        />
                    </template>
                </Dialog>
            </ErrorBoundary>

            <!-- Print Dialog -->
            <Dialog
                v-model:visible="showPrintDialog"
                header="Aperçu d'Impression"
                :modal="true"
                :style="{ width: '80vw', maxWidth: '1000px' }"
            >
                <div id="print-content" class="p-4 bg-white">
                    <div
                        class="flex justify-content-between align-items-center mb-4"
                    >
                        <img src="/logo.png" alt="Logo" class="print-logo" />
                        <div class="text-right">
                            <h3 class="m-0">
                                Agence Tunisienne de la Formation
                                Professionnelle
                            </h3>
                            <p class="arabic-font m-0">
                                الوكالة التونسية للتكوين المهني
                            </p>
                        </div>
                    </div>
                    <h2 class="text-center mb-2">Profil Utilisateur</h2>
                    <p class="text-center text-600 mb-4">
                        {{ formatDate(new Date()) }}
                    </p>
                    <div class="grid">
                        <div class="col-12 md:col-4">
                            <h3>Informations de Base</h3>
                            <div
                                class="flex flex-column align-items-center gap-2 mb-4"
                            >
                                <Avatar
                                    :image="user.photo || '/default-avatar.png'"
                                    shape="circle"
                                    style="width: 180px; height: 180px"
                                />
                                <h4 class="m-0">
                                    {{ user.nom_fr || 'Non défini' }}
                                    {{ user.prenom_fr || '' }}
                                </h4>
                                <Tag
                                    :value="
                                        user.roles && Array.isArray(user.roles)
                                            ? user.roles
                                                  .map((role) => role.name)
                                                  .join(', ')
                                            : 'Aucun rôle'
                                    "
                                    severity="info"
                                />
                                <Tag
                                    :value="user.statut || 'Inconnu'"
                                    :severity="
                                        user.statut === 'Actif'
                                            ? 'success'
                                            : 'danger'
                                    "
                                />
                            </div>
                            <table class="print-table">
                                <tr>
                                    <th>Matricule</th>
                                    <td>
                                        {{ user.matricule || 'Non défini' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>CIN</th>
                                    <td>{{ user.cin || 'Non défini' }}</td>
                                </tr>
                                <tr>
                                    <th>Date de Naissance</th>
                                    <td>
                                        {{ formatDate(user.date_naissance) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Genre</th>
                                    <td>{{ user.genre_fr || 'Non défini' }}</td>
                                </tr>
                                <tr>
                                    <th>Nationalité</th>
                                    <td>
                                        {{
                                            user.nationalite_fr || 'Tunisienne'
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Centre</th>
                                    <td>
                                        {{
                                            user.centres &&
                                            Array.isArray(user.centres) &&
                                            user.centres.length > 0
                                                ? user.centres[0].nom_fr
                                                : 'Aucun centre'
                                        }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-12 md:col-8">
                            <h3>Détails</h3>
                            <table class="print-table">
                                <tr>
                                    <th>Nom (FR)</th>
                                    <td>{{ user.nom_fr || 'Non défini' }}</td>
                                </tr>
                                <tr>
                                    <th>Prénom (FR)</th>
                                    <td>
                                        {{ user.prenom_fr || 'Non défini' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Nom (AR)</th>
                                    <td class="arabic-font">
                                        {{ user.nom_ar || 'غير محدد' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Prénom (AR)</th>
                                    <td class="arabic-font">
                                        {{ user.prenom_ar || 'غير محدد' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ user.email || 'Non défini' }}</td>
                                </tr>
                                <tr>
                                    <th>Statut</th>
                                    <td>{{ user.statut || 'Inconnu' }}</td>
                                </tr>
                            </table>
                            <h3>Activité</h3>
                            <table class="print-table">
                                <tr>
                                    <th>Dernière Connexion</th>
                                    <td>
                                        {{ formatDateTime(user.last_login_at) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Adresse IP</th>
                                    <td>
                                        {{ user.last_login_ip || 'Inconnue' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Date de Création</th>
                                    <td>
                                        {{ formatDateTime(user.created_at) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Dernière Mise à Jour</th>
                                    <td>
                                        {{ formatDateTime(user.updated_at) }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <template #footer>
                    <Button
                        label="Imprimer"
                        icon="pi pi-print"
                        @click="printProfileContent"
                    />
                    <Button
                        label="Fermer"
                        icon="pi pi-times"
                        text
                        @click="showPrintDialog = false"
                    />
                </template>
            </Dialog>
        </div>
    </main-layout>
</template>

<script>
import { ref } from 'vue';
import { useToast } from 'primevue/usetoast';
import axios from '@/axios';
import Card from 'primevue/card';
import Avatar from 'primevue/avatar';
import Button from 'primevue/button';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import InputText from 'primevue/inputtext';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
import Password from 'primevue/password';
import ProgressSpinner from 'primevue/progressspinner';
import Message from 'primevue/message';
import Dialog from 'primevue/dialog';
import FileUpload from 'primevue/fileupload';
import Tag from 'primevue/tag';
import Divider from 'primevue/divider';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { Cropper } from 'vue-advanced-cropper';
import ErrorBoundary from '@/Components/Atfp/Utilisateurs/PersonnelsCentres/ErrorBoundary.vue';
import MainLayout from '@/layout/MainLayout.vue';
import 'vue-advanced-cropper/dist/style.css';

export default {
    components: {
        Card,
        Avatar,
        Button,
        TabView,
        TabPanel,
        InputText,
        Calendar,
        Dropdown,
        Password,
        ProgressSpinner,
        Message,
        Dialog,
        FileUpload,
        Tag,
        Divider,
        DataTable,
        Column,
        Cropper,
        ErrorBoundary,
        MainLayout,
    },
    data() {
        return {
            user: null,
            loading: true,
            error: null,
            saving: false,
            changingPassword: false,
            uploadingAvatar: false,
            showAvatarDialog: false,
            showPrintDialog: false,
            selectedFile: null,
            previewImage: null,
            croppedImage: null,
            imageError: null,
            loginHistory: [],
            loadingHistory: false,
            recentActivity: null,
            form: {
                id: null,
                nom_fr: '',
                prenom_fr: '',
                nom_ar: '',
                prenom_ar: '',
                matricule: '',
                cin: '',
                date_naissance: null,
                genre_id: null,
                genre_fr: null,
                email: '',
                nationalite_fr: 'Tunisienne',
                nationalite_ar: 'تونسية',
                statut: '',
                role: null,
                centre_id: null,
            },
            passwordForm: {
                current_password: '',
                new_password: '',
                new_password_confirmation: '',
            },
            formErrors: {},
            passwordErrors: {},
            genres: [
                { id: 1, nom: 'Homme' },
                { id: 2, nom: 'Femme' },
                { id: 3, nom: 'Autre' },
            ],
            roles: [],
            centres: [],
            isCentreRole: false,
        };
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    created() {
        this.fetchUser();
        this.fetchLoginHistory();
        this.fetchRecentActivity();
    },
    methods: {
        async fetchUser() {
            try {
                this.loading = true;
                const userId = this.$route.params.id;
                if (!userId) {
                    throw new Error('ID utilisateur non défini');
                }

                const [userResponse, rolesResponse, centresResponse] =
                    await Promise.all([
                        axios.get(`/users/${userId}`),
                        axios.get('/roles'),
                        axios.get('/centres'),
                    ]);

                this.user = userResponse.data || {};
                this.user.roles = Array.isArray(this.user.roles)
                    ? this.user.roles
                    : [];
                this.user.centres = Array.isArray(this.user.centres)
                    ? this.user.centres
                    : [];
                this.roles = rolesResponse.data || [];
                this.centres = centresResponse.data || [];

                this.form = {
                    id: this.user.id || null,
                    nom_fr: this.user.nom_fr || '',
                    prenom_fr: this.user.prenom_fr || '',
                    nom_ar: this.user.nom_ar || '',
                    prenom_ar: this.user.prenom_ar || '',
                    matricule: this.user.matricule || '',
                    cin: this.user.cin || '',
                    date_naissance: this.user.date_naissance
                        ? new Date(this.user.date_naissance)
                        : null,
                    genre_id: this.user.genre_fr
                        ? this.genres.find((g) => g.nom === this.user.genre_fr)
                              ?.id || null
                        : null,
                    genre_fr: this.user.genre_fr || null,
                    email: this.user.email || '',
                    nationalite_fr: this.user.nationalite_fr || 'Tunisienne',
                    nationalite_ar: this.user.nationalite_ar || 'تونسية',
                    statut: this.user.statut || '',
                    role:
                        this.user.roles.length > 0
                            ? this.user.roles[0].name
                            : null,
                    centre_id:
                        this.user.centres.length > 0
                            ? this.user.centres[0].id
                            : null,
                };

                this.checkIfCentreRole();

                if (!this.user.nom_fr) {
                    this.toast.add({
                        severity: 'warn',
                        summary: 'Attention',
                        detail: 'Certaines données utilisateur sont manquantes.',
                        life: 5000,
                    });
                }
            } catch (err) {
                console.error('Erreur lors de fetchUser:', err);
                this.error =
                    err.response?.data?.message ||
                    'Erreur lors du chargement du profil';
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: this.error,
                    life: 5000,
                });
                this.$router.push('/users');
            } finally {
                this.loading = false;
            }
        },
        async fetchLoginHistory() {
            try {
                this.loadingHistory = true;
                const userId = this.$route.params.id;
                const response = await axios.get(
                    `/users/${userId}/login-history`
                );
                // Ensure loginHistory is an array of objects
                this.loginHistory = Array.isArray(response.data)
                    ? response.data.map((item) => ({
                          date: item.date || '',
                          ip: item.ip || '',
                          device: item.device || '',
                          location: item.location || '',
                      }))
                    : [];
            } catch (err) {
                console.error('Erreur lors de fetchLoginHistory:', err);
                this.loginHistory = []; // Fallback to empty array
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        err.response?.data?.message ||
                        "Erreur lors du chargement de l'historique des connexions",
                    life: 5000,
                });
            } finally {
                this.loadingHistory = false;
            }
        },
        async fetchRecentActivity() {
            try {
                const userId = this.$route.params.id;
                const response = await axios.get(
                    `/users/${userId}/recent-activity`
                );
                this.recentActivity =
                    response.data.activity || 'Aucune activité récente';
            } catch (err) {
                console.error('Erreur lors de fetchRecentActivity:', err);
                this.recentActivity = 'Aucune activité récente';
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        err.response?.data?.message ||
                        "Erreur lors du chargement de l'activité récente",
                    life: 5000,
                });
            }
        },
        validateNomFr() {
            if (!this.form.nom_fr) {
                this.formErrors.nom_fr = 'Le nom (FR) est obligatoire';
            } else {
                this.formErrors.nom_fr = '';
            }
        },
        validatePrenomFr() {
            if (!this.form.prenom_fr) {
                this.formErrors.prenom_fr = 'Le prénom (FR) est obligatoire';
            } else {
                this.formErrors.prenom_fr = '';
            }
        },
        validateEmail() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!this.form.email) {
                this.formErrors.email = "L'email est obligatoire";
            } else if (!emailRegex.test(this.form.email)) {
                this.formErrors.email = "L'email n'est pas valide";
            } else {
                this.formErrors.email = '';
            }
        },
        validateRole() {
            if (!this.form.role) {
                this.formErrors.role = 'Le rôle est obligatoire';
            } else {
                this.formErrors.role = '';
                this.checkIfCentreRole();
            }
        },
        validatePassword() {
            if (!this.passwordForm.new_password) {
                this.passwordErrors.new_password =
                    'Le nouveau mot de passe est obligatoire';
            } else {
                this.passwordErrors.new_password = '';
            }
            if (!this.passwordForm.new_password_confirmation) {
                this.passwordErrors.new_password_confirmation =
                    'La confirmation du mot de passe est obligatoire';
            } else if (
                this.passwordForm.new_password !==
                this.passwordForm.new_password_confirmation
            ) {
                this.passwordErrors.new_password_confirmation =
                    'Les mots de passe ne correspondent pas';
            } else {
                this.passwordErrors.new_password_confirmation = '';
            }
        },
        checkIfCentreRole() {
            const selectedRole = this.roles.find(
                (role) => role.name === this.form.role
            );
            this.isCentreRole = !!selectedRole?.is_centre_role;
            if (!this.isCentreRole) {
                this.form.centre_id = null;
            }
        },
        formatDateForPayload(date) {
            if (!date || !(date instanceof Date)) return null;
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        async updateProfile() {
            this.validateNomFr();
            this.validatePrenomFr();
            this.validateEmail();
            this.validateRole();

            if (Object.values(this.formErrors).some((error) => error)) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Veuillez corriger les erreurs dans le formulaire',
                    life: 5000,
                });
                return;
            }

            try {
                this.saving = true;
                this.formErrors = {};
                const payload = {
                    id: this.form.id || null,
                    nom_fr: this.form.nom_fr || null,
                    prenom_fr: this.form.prenom_fr || null,
                    nom_ar: this.form.nom_ar || null,
                    prenom_ar: this.form.prenom_ar || null,
                    matricule: this.form.matricule || null,
                    cin: this.form.cin || null,
                    date_naissance: this.form.date_naissance
                        ? this.formatDateForPayload(this.form.date_naissance)
                        : null,
                    genre_fr:
                        this.genres.find((g) => g.id === this.form.genre_id)
                            ?.nom || null,
                    email: this.form.email.toLowerCase() || null,
                    nationalite_fr: this.form.nationalite_fr || 'Tunisienne',
                    nationalite_ar: this.form.nationalite_ar || 'تونسية',
                    statut: this.form.statut || null,
                    role: this.form.role || null,
                    centre_id: this.form.centre_id || null,
                    photo: this.user.photo || null,
                };

                const response = await axios.post(
                    `/users/update/${this.$route.params.id}`,
                    payload
                );
                this.user = { ...this.user, ...response.data.user };
                this.user.roles = Array.isArray(this.user.roles)
                    ? this.user.roles
                    : [];
                this.user.centres = Array.isArray(this.user.centres)
                    ? this.user.centres
                    : [];
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Profil mis à jour',
                    life: 3000,
                });
                await this.fetchUser();
            } catch (err) {
                console.error('Erreur lors de updateProfile:', err);
                if (err.response?.status === 422) {
                    this.formErrors = err.response.data.errors || {};
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail:
                            err.response?.data?.message ||
                            'Erreur lors de la mise à jour',
                        life: 5000,
                    });
                }
            } finally {
                this.saving = false;
            }
        },
        async updatePassword() {
            this.validatePassword();

            if (Object.values(this.passwordErrors).some((error) => error)) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Veuillez corriger les erreurs dans le formulaire',
                    life: 5000,
                });
                return;
            }

            try {
                this.changingPassword = true;
                this.passwordErrors = {};
                await axios.post(
                    `/users/update-password/${this.$route.params.id}`,
                    this.passwordForm
                );
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Mot de passe mis à jour',
                    life: 3000,
                });
                this.resetPasswordForm();
            } catch (err) {
                console.error('Erreur lors de updatePassword:', err);
                if (err.response?.status === 422) {
                    this.passwordErrors = err.response.data.errors || {};
                    const errorMessages = Object.values(
                        err.response.data.errors
                    )
                        .flat()
                        .join(', ');
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: errorMessages,
                        life: 5000,
                    });
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail:
                            err.response?.data?.message ||
                            'Erreur lors de la mise à jour du mot de passe',
                        life: 5000,
                    });
                }
            } finally {
                this.changingPassword = false;
            }
        },
        onAvatarSelect(event) {
            try {
                this.imageError = null;
                const file = event.files[0];
                if (!file) {
                    throw new Error('Aucun fichier sélectionné');
                }
                if (file.size > 2000000) {
                    this.imageError =
                        "La taille de l'image ne doit pas dépasser 2 Mo";
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: this.imageError,
                        life: 5000,
                    });
                    return;
                }
                if (!file.type.match('image/(jpg|jpeg|png)')) {
                    this.imageError =
                        'Veuillez sélectionner une image valide (JPG, PNG)';
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: this.imageError,
                        life: 5000,
                    });
                    return;
                }
                this.selectedFile = file;
                this.previewImage = URL.createObjectURL(file);
                console.log('Fichier sélectionné:', {
                    name: file.name,
                    size: file.size,
                    type: file.type,
                    previewImage: this.previewImage,
                });
            } catch (err) {
                console.error('Erreur dans onAvatarSelect:', err);
                this.imageError =
                    err.message || "Erreur lors de la sélection de l'image";
                this.previewImage = null;
                this.selectedFile = null;
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: this.imageError,
                    life: 5000,
                });
            }
        },
        onCropperReady() {
            console.log('Cropper prêt : le composant est chargé et rendu');
        },
        onCropChange({ coordinates, canvas }) {
            try {
                this.croppedImage = canvas.toDataURL('image/png');
                console.log('Image rognée:', {
                    coordinates,
                    dataUrlLength: this.croppedImage.length,
                });
            } catch (err) {
                console.error('Erreur dans onCropChange:', err);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Erreur lors du recadrage de l'image",
                    life: 5000,
                });
            }
        },
        async uploadAvatar() {
            try {
                this.uploadingAvatar = true;
                const base64Image =
                    this.croppedImage ||
                    (this.selectedFile
                        ? await this.toBase64(this.selectedFile)
                        : null);

                if (!base64Image) {
                    throw new Error('Aucune image sélectionnée');
                }

                console.log("Envoi de l'image Base64:", {
                    length: base64Image.length,
                });

                const response = await axios.post(
                    `/users/${this.$route.params.id}/avatar`,
                    {
                        photo: base64Image,
                    }
                );

                this.user.photo = response.data.photo || this.user.photo;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Photo mise à jour',
                    life: 3000,
                });
                this.showAvatarDialog = false;
                await this.fetchUser();
            } catch (err) {
                console.error('Erreur lors de uploadAvatar:', err);
                let errorMessage = "Erreur lors de l'upload de la photo";
                if (err.response?.status === 422) {
                    const errors = err.response.data.errors?.photo || [
                        'Erreur de validation',
                    ];
                    errorMessage = errors.join(', ');
                } else if (err.response?.status === 500) {
                    errorMessage =
                        err.response.data.error || 'Erreur serveur interne';
                } else if (err.message) {
                    errorMessage = err.message;
                }
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: errorMessage,
                    life: 5000,
                });
            } finally {
                this.uploadingAvatar = false;
                this.selectedFile = null;
                this.previewImage = null;
                this.croppedImage = null;
                this.imageError = null;
            }
        },
        toBase64(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = (error) => reject(error);
                reader.readAsDataURL(file);
            });
        },
        cancelAvatarUpload() {
            this.showAvatarDialog = false;
            this.selectedFile = null;
            this.previewImage = null;
            this.croppedImage = null;
            this.imageError = null;
        },
        resetForm() {
            this.form = {
                id: this.user.id || null,
                nom_fr: this.user.nom_fr || '',
                prenom_fr: this.user.prenom_fr || '',
                nom_ar: this.user.nom_ar || '',
                prenom_ar: this.user.prenom_ar || '',
                matricule: this.user.matricule || '',
                cin: this.user.cin || '',
                date_naissance: this.user.date_naissance
                    ? new Date(this.user.date_naissance)
                    : null,
                genre_id: this.user.genre_fr
                    ? this.genres.find((g) => g.nom === this.user.genre_fr)
                          ?.id || null
                    : null,
                genre_fr: this.user.genre_fr || null,
                email: this.user.email || '',
                nationalite_fr: this.user.nationalite_fr || 'Tunisienne',
                nationalite_ar: this.user.nationalite_ar || 'تونسية',
                statut: this.user.statut || '',
                role:
                    this.user.roles.length > 0 ? this.user.roles[0].name : null,
                centre_id:
                    this.user.centres.length > 0
                        ? this.user.centres[0].id
                        : null,
            };
            this.formErrors = {};
            this.checkIfCentreRole();
        },
        resetPasswordForm() {
            this.passwordForm = {
                current_password: '',
                new_password: '',
                new_password_confirmation: '',
            };
            this.passwordErrors = {};
        },
        refreshProfile() {
            this.fetchUser();
            this.fetchLoginHistory();
            this.fetchRecentActivity();
            this.toast.add({
                severity: 'info',
                summary: 'Rafraîchi',
                detail: 'Profil actualisé',
                life: 3000,
            });
        },
        printProfile() {
            this.showPrintDialog = true;
            this.$nextTick(() => {
                const printContent = document.getElementById('print-content');
                if (printContent) {
                    printContent.scrollIntoView({ behavior: 'smooth' });
                }
            });
        },
        printProfileContent() {
            try {
                const printContent =
                    document.getElementById('print-content').innerHTML;
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html lang="fr">
                        <head>
                            <title>Profil Utilisateur</title>
                            <style>
                                body { font-family: Arial, sans-serif; margin: 20px; font-size: 16px; }
                                .print-table { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; }
                                .print-table th, .print-table td { border: 1px solid #dee2e6; padding: 0.75rem; font-size: 14px; }
                                .print-table th { font-weight: 600; background: #f8f9fa; }
                                .arabic-font { font-family: 'Amiri', sans-serif; font-size: 16px; }
                                h2 { font-size: 24px; margin: 0.5rem 0; text-align: center; }
                                h3 { font-size: 20px; margin: 0.5rem 0; }
                                h4 { font-size: 18px; margin: 0.5rem 0; }
                                .text-center { text-align: center; }
                                .text-right { text-align: right; }
                                .print-logo { maxවිත්‍රන්ස්ලින්ක්://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap
                                .print-logo { max-height: 60px; }
                            </style>
                        </head>
                        <body>
                            ${printContent}
                        </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            } catch (err) {
                console.error('Erreur dans printProfileContent:', err);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Erreur lors de l'impression",
                    life: 5000,
                });
            }
        },
        formatDate(date) {
            if (!date) return 'Non défini';
            return new Date(date).toLocaleDateString('fr-FR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
        },
        formatDateTime(date) {
            if (!date) return 'Jamais';
            return new Date(date).toLocaleString('fr-FR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });
        },
    },
};
</script>

<style scoped>
.profile-container {
    width: 100%;
    padding: 1.5rem;
    box-sizing: border-box;
}

.profile-card {
    :deep(.p-card-header) {
        padding: 2rem 2rem 0 2rem;
    }

    :deep(.p-card-title) {
        font-size: 1.5rem;
        text-align: center;
        margin-bottom: 0.5rem;
    }

    :deep(.p-card-subtitle) {
        text-align: center;
        margin-bottom: 1.5rem;
        color: var(--primary-color);
        font-weight: 600;
    }
}

.profile-tabs {
    :deep(.p-tabview-nav) {
        background: transparent;
        border: none;
        margin-bottom: 1rem;
    }

    :deep(.p-tabview-nav-link) {
        border-radius: 8px;
        transition: all 0.3s ease;
        padding: 0.75rem 1.5rem;
    }

    :deep(.p-tabview-nav-link:hover) {
        background: var(--surface-hover);
    }

    :deep(.p-tabview-panels) {
        background: transparent;
        padding: 0;
    }

    :deep(.p-card-title) {
        text-align: left !important;
    }
}

.field {
    margin-bottom: 1rem;
}

.field label {
    display: block;
    margin-bottom: 0.25rem;
    color: var(--surface-600);
    font-weight: 600;
    text-align: left;
}

.field div {
    padding: 0.75rem;
    background: var(--surface-50);
    border-radius: 6px;
    border: 1px solid var(--surface-200);
    transition: border-color 0.3s ease;
}

.field div:hover {
    border-color: var(--primary-color);
}

.custom-avatar {
    width: 180px !important;
    height: 180px !important;
}

.arabic-font {
    font-family: 'Amiri', sans-serif;
    direction: rtl;
}

.cropper-container {
    width: 300px;
    height: 300px;
    position: relative;
    overflow: hidden;
    background: transparent;
}

/* Style pour afficher uniquement la poignée en bas à droite */
:deep(.cropper-handler) {
    display: none !important;
}

:deep(.cropper-handler.bottom-right) {
    display: block !important;
    background: #3b82f6 !important;
    border: 2px solid #ffffff !important;
    width: 16px !important;
    height: 16px !important;
    border-radius: 50%;
    cursor: se-resize;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
}

/* Supprimer tout cadre ou fond indésirable autour du cropper */
:deep(.cropper-viewport) {
    background: transparent !important;
    border: none !important;
}

:deep(.cropper-face) {
    background: transparent !important;
}

.text-error {
    color: var(--red-500);
}

.activity-card {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-radius: 8px;
    background: var(--surface-50);
    transition: all 0.2s;
    height: 100%;
}

.activity-card:hover {
    background: var(--surface-100);
    transform: translateY(-2px);
}

.activity-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--primary-color);
    color: white;
    font-size: 1.25rem;
    margin-right: 1rem;
}

.activity-content {
    flex: 1;
}

.activity-content h5 {
    margin: 0 0 0.25rem 0;
    font-size: 1rem;
    color: var(--surface-700);
}

.activity-content p {
    margin: 0;
    font-size: 0.875rem;
    color: var(--surface-600);
}

.activity-content small {
    font-size: 0.75rem;
}

@media print {
    .print-table {
        page-break-inside: avoid;
    }
}

@media (max-width: 960px) {
    .profile-container {
        padding: 1rem;
    }

    .custom-avatar {
        width: 120px !important;
        height: 120px !important;
    }

    .cropper-container {
        width: 250px;
        height: 250px;
    }
}

@media (max-width: 768px) {
    .profile-container {
        padding: 0.75rem;
    }

    .field div {
        font-size: 0.875rem;
    }

    .profile-card :deep(.p-card-title) {
        font-size: 1.25rem;
    }

    .profile-tabs :deep(.p-tabview-nav-link) {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }

    .custom-avatar {
        width: 100px !important;
        height: 100px !important;
    }

    .cropper-container {
        width: 200px;
        height: 200px;
    }
}
</style>
