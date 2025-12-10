<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :modal="true"
        :focusOnShow="false"
        :style="{ width: '80vw', maxWidth: '1200px' }"
        :breakpoints="{ '960px': '95vw', '640px': '100vw' }"
        class="p-fluid"
        :pt="{
            root: 'border-round-xl shadow-5',
            mask: { style: 'backdrop-filter: blur(4px)' },
            header: { class: 'surface-50 border-bottom-1 surface-border p-4' },
            content: { class: 'surface-ground p-4' },
            footer: { class: 'surface-50 border-top-1 surface-border p-4' },
        }"
    >
        <!-- Header -->
        <template #header>
            <div class="flex align-items-center gap-2">
                <i :class="isEditing ? 'pi pi-pencil' : 'pi pi-plus'" class="text-primary text-2xl"></i>
                <span class="font-bold text-2xl">
                    {{ isEditing ? 'Modifier un Utilisateur' : 'Ajouter un Utilisateur' }}
                </span>
            </div>
        </template>

        <!-- Loading State -->
        <div v-if="loading" class="flex flex-column align-items-center justify-content-center gap-3 p-6">
            <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
            <span class="text-color-secondary">Chargement des données...</span>
        </div>

        <!-- Form -->
        <div v-else class="surface-card p-4 shadow-2 border-round">
            <form @submit.prevent="submitForm">
                <TabView ref="tabview" v-model:activeIndex="activeTabIndex">
                    <!-- IU -->
                    <TabPanel header="IU">
                        <div v-if="isEditing" class="flex align-items-center gap-3 mt-4 mb-4">
                            <img
                                v-if="form.photo && isValidPhoto(form.photo)"
                                :src="getPhotoUrl(form.photo)"
                                alt="Photo de l'utilisateur"
                                class="border-round shadow-2"
                                style="width: 100px; height: 100px; object-fit: contain;"
                            />
                            <div v-else class="flex align-items-center justify-content-center border-round shadow-2"
                                 style="width: 100px; height: 100px; background: var(--surface-100);">
                                <i class="pi pi-user text-3xl text-surface-500"></i>
                            </div>
                            <div class="flex flex-column">
                                <span class="font-bold text-lg">{{ form.prenom_fr }} {{ form.nom_fr }}</span>
                                <div class="flex gap-2 mt-2">
                                <span
                                    v-for="role in userRolesDisplay"
                                    :key="role.id"
                                    class="p-chip p-component custom-role-chip"
                                >
                                 {{ role.name }}
                                     </span>

                                </div>
                            </div>
                        </div>
                        <div class="grid p-fluid" :class="{ 'mt-4': !isEditing }">
                            <div class="col-12 md:col-6 field">
                                <label for="matricule" class="block font-medium mb-2">
                                    Matricule <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="matricule"
                                    v-model.trim="form.matricule"
                                    :disabled="isEditing"
                                    class="invalid"
                                    placeholder="Entrez le matricule"
                                    @input="debouncedValidateMatricule"
                                    :autofocus="false"
                                />
                                <small v-if="errors.matricule?.length" class="p-error">
                                    {{ errors.matricule[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-3 field">
                                <label for="cin" class="block font-medium mb-2">
                                    CIN <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="cin"
                                    v-model.trim="form.cin"
                                    class="invalid"
                                    placeholder="Entrez le CIN"
                                    @input="debouncedValidateCin"
                                />
                                <small v-if="errors.cin?.length" class="p-error">
                                    {{ errors.cin[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-3 field">
                                <label for="date_cin" class="block font-medium mb-2">
                                    Date CIN
                                </label>
                                <Calendar
                                    id="date_cin"
                                    v-model="form.date_cin"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    :class="{ 'p-invalid': errors.date_cin?.length }"
                                    @update:modelValue="validateDateCin"
                                />
                                <small v-if="errors.date_cin?.length" class="p-error">
                                    {{ errors.date_cin[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="lieu_cin_fr" class="block font-medium mb-2">
                                    Lieu CIN (Français)
                                </label>
                                <InputText
                                    id="lieu_cin_fr"
                                    v-model="form.lieu_cin_fr"
                                    :class="{ 'p-invalid': errors.lieu_cin_fr?.length }"
                                    placeholder="Entrez le lieu en français"
                                    @input="validateLieuCinFr"
                                />
                                <small v-if="errors.lieu_cin_fr?.length" class="p-error">
                                    {{ errors.lieu_cin_fr[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="lieu_cin_ar" class="block font-medium mb-2 text-right font-arabic">
                                    مكان إصدار البطاقة (العربية)
                                </label>
                                <InputText
                                    id="lieu_cin_ar"
                                    v-model="form.lieu_cin_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.lieu_cin_ar?.length }"
                                    placeholder="أدخل مكان الإصدار بالعربية"
                                    @input="validateLieuCinAr"
                                />
                                <small v-if="errors.lieu_cin_ar?.length" class="p-error">
                                    {{ errors.lieu_cin_ar[0] }}
                                </small>
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Informations Personnel -->
                    <TabPanel header="Informations Personnel">
                        <div class="grid p-fluid mt-4">
                            <div class="col-12 md:col-6 field">
                                <label for="prenom_fr" class="block font-medium mb-2">
                                    Prénom (Français) <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="prenom_fr"
                                    v-model="form.prenom_fr"
                                    class="invalid"
                                    placeholder="Entrez le prénom en français"
                                    @input="validatePrenomFr"
                                />
                                <small v-if="errors.prenom_fr?.length" class="p-error">
                                    {{ errors.prenom_fr[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="nom_fr" class="block font-medium mb-2">
                                    Nom (Français) <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="nom_fr"
                                    v-model="form.nom_fr"
                                    class="invalid"
                                    placeholder="Entrez le nom en français"
                                    @input="validateNomFr"
                                />
                                <small v-if="errors.nom_fr?.length" class="p-error">
                                    {{ errors.nom_fr[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="nom_ar" class="block font-medium mb-2 text-right font-arabic">
                                    اللقب (العربية) <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="nom_ar"
                                    v-model="form.nom_ar"
                                    dir="rtl"
                                    class="invalid"
                                    placeholder="أدخل اللقب بالعربية"
                                    @input="validateNomAr"
                                />
                                <small v-if="errors.nom_ar?.length" class="p-error">
                                    {{ errors.nom_ar[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="prenom_ar" class="block font-medium mb-2 text-right font-arabic">
                                    الاسم (العربية) <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="prenom_ar"
                                    v-model="form.prenom_ar"
                                    dir="rtl"
                                    class="invalid"
                                    placeholder="أدخل الاسم بالعربية"
                                    @input="validatePrenomAr"
                                />
                                <small v-if="errors.prenom_ar?.length" class="p-error">
                                    {{ errors.prenom_ar[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-4 field">
                                <label for="etat_civil_fr" class="block font-medium mb-2">
                                    État Civil (Français)
                                </label>
                                <Dropdown
                                    id="etat_civil_fr"
                                    v-model="form.etat_civil_fr"
                                    :options="etatCivilOptions"
                                    optionLabel="label_fr"
                                    optionValue="value_fr"
                                    placeholder="Sélectionner l'état civil"
                                    class="invalid"
                                    @change="onEtatCivilChange"
                                />
                            </div>
                            <div class="col-12 md:col-4 field">
                                <label for="genre_fr" class="block font-medium mb-2">
                                    Genre (Français) <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    id="genre_fr"
                                    v-model="form.genre_fr"
                                    :options="genreOptions"
                                    optionLabel="label_fr"
                                    optionValue="value_fr"
                                    placeholder="Sélectionner un genre"
                                    class="invalid"
                                    @change="onGenreChange"
                                />
                                <small v-if="errors.genre_fr?.length" class="p-error">
                                    {{ errors.genre_fr[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-4 field">
                                <label for="date_naissance" class="block font-medium mb-2">
                                    Date Naissance
                                </label>
                                <Calendar
                                    id="date_naissance"
                                    v-model="form.date_naissance"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    :class="{ 'p-invalid': errors.date_naissance?.length }"
                                    @update:modelValue="validateDateNaissance"
                                />
                                <small v-if="errors.date_naissance?.length" class="p-error">
                                    {{ errors.date_naissance[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="lieu_naissance_fr" class="block font-medium mb-2">
                                    Lieu Naissance (Français)
                                </label>
                                <InputText
                                    id="lieu_naissance_fr"
                                    v-model="form.lieu_naissance_fr"
                                    :class="{ 'p-invalid': errors.lieu_naissance_fr?.length }"
                                    placeholder="Entrez le lieu de naissance en français"
                                    @input="validateLieuNaissanceFr"
                                />
                                <small v-if="errors.lieu_naissance_fr?.length" class="p-error">
                                    {{ errors.lieu_naissance_fr[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="lieu_naissance_ar" class="block font-medium mb-2 text-right font-arabic">
                                    مكان الولادة (العربية)
                                </label>
                                <InputText
                                    id="lieu_naissance_ar"
                                    v-model="form.lieu_naissance_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.lieu_naissance_ar?.length }"
                                    placeholder="أدخل مكان الولادة بالعربية"
                                    @input="validateLieuNaissanceAr"
                                />
                                <small v-if="errors.lieu_naissance_ar?.length" class="p-error">
                                    {{ errors.lieu_naissance_ar[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="nationalite_fr" class="block font-medium mb-2">
                                    Nationalité (Français)
                                </label>
                                <InputText
                                    id="nationalite_fr"
                                    v-model="form.nationalite_fr"
                                    placeholder="Entrez la nationalité en français"
                                    @input="validateNationaliteFr"
                                />
                                <small v-if="errors.nationalite_fr?.length" class="p-error">
                                    {{ errors.nationalite_fr[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="nationalite_ar" class="block font-medium mb-2 text-right font-arabic">
                                    الجنسية (العربية)
                                </label>
                                <InputText
                                    id="nationalite_ar"
                                    v-model="form.nationalite_ar"
                                    dir="rtl"
                                    placeholder="أدخل الجنسية بالعربية"
                                    @input="validateNationaliteAr"
                                />
                                <small v-if="errors.nationalite_ar?.length" class="p-error">
                                    {{ errors.nationalite_ar[0] }}
                                </small>
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Statut -->
                    <TabPanel v-if="isEditing" header="Statut">
                        <div class="grid p-fluid mt-4">
                            <div class="col-12 md:col-6 field">
                                <label for="statut" class="block font-medium mb-2">
                                    Statut <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    id="statut"
                                    v-model="form.statut"
                                    :options="statutOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Sélectionner un statut"
                                    class="invalid"
                                    @change="validateStatut"
                                />
                                <small v-if="errors.statut?.length" class="p-error">
                                    {{ errors.statut[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="cause_inactivite_fr" class="block font-medium mb-2">
                                    Cause d'inactivité (Français)
                                </label>
                                <Dropdown
                                    id="cause_inactivite_fr"
                                    v-model="form.cause_inactivite_fr"
                                    :options="causeInactiviteOptions"
                                    optionLabel="label_fr"
                                    optionValue="value_fr"
                                    placeholder="Sélectionner une cause"
                                    :class="{ 'p-invalid': errors.cause_inactivite_fr?.length }"
                                    :disabled="form.statut !== 'Inactif'"
                                    @change="onCauseInactiviteChange"
                                />
                                <small v-if="errors.cause_inactivite_fr?.length" class="p-error">
                                    {{ errors.cause_inactivite_fr[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="date_recrutement" class="block font-medium mb-2">
                                    Date de recrutement
                                </label>
                                <Calendar
                                    id="date_recrutement"
                                    v-model="form.date_recrutement"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    :class="{ 'p-invalid': errors.date_recrutement?.length }"
                                    @update:modelValue="validateDateRecrutement"
                                />
                                <small v-if="errors.date_recrutement?.length" class="p-error">
                                    {{ errors.date_recrutement[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="date_fin_service" class="block font-medium mb-2">
                                    Date de fin de service
                                </label>
                                <Calendar
                                    id="date_fin_service"
                                    v-model="form.date_fin_service"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    :class="{ 'p-invalid': errors.date_fin_service?.length }"
                                    @update:modelValue="validateDateFinService"
                                />
                                <small v-if="errors.date_fin_service?.length" class="p-error">
                                    {{ errors.date_fin_service[0] }}
                                </small>
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Contact -->
                    <TabPanel header="Contact">
                        <div class="grid p-fluid mt-4">
                            <div class="col-12 md:col-6 field">
                                <label for="adresse_fr" class="block font-medium mb-2">
                                    Adresse (Français)
                                </label>
                                <InputText
                                    id="adresse_fr"
                                    v-model="form.adresse_fr"
                                    :class="{ 'p-invalid': errors.adresse_fr?.length }"
                                    placeholder="Entrez l'adresse en français"
                                    @input="validateAdresseFr"
                                />
                                <small v-if="errors.adresse_fr?.length" class="p-error">
                                    {{ errors.adresse_fr[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="adresse_ar" class="block font-medium mb-2 text-right font-arabic">
                                    العنوان (العربية)
                                </label>
                                <InputText
                                    id="adresse_ar"
                                    v-model="form.adresse_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.adresse_ar?.length }"
                                    placeholder="أدخل العنوان بالعربية"
                                    @input="validateAdresseAr"
                                />
                                <small v-if="errors.adresse_ar?.length" class="p-error">
                                    {{ errors.adresse_ar[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-4 field">
                                <label for="gouvernorat_fr" class="block font-medium mb-2">
                                    Gouvernorat (Français)
                                </label>
                                <Dropdown
                                    id="gouvernorat_fr"
                                    v-model="form.gouvernorat_fr"
                                    :options="gouvernoratOptions"
                                    optionLabel="nom_fr"
                                    optionValue="nom_fr"
                                    placeholder="Sélectionner un gouvernorat"
                                    :class="{ 'p-invalid': errors.gouvernorat_fr?.length }"
                                    :loading="loadingLists"
                                    @change="onGouvernoratChange"
                                />
                                <small v-if="errors.gouvernorat_fr?.length" class="p-error">
                                    {{ errors.gouvernorat_fr[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-4 field">
                                <label for="delegation_fr" class="block font-medium mb-2">
                                    Délégation (Français)
                                </label>
                                <InputText
                                    id="delegation_fr"
                                    v-model="form.delegation_fr"
                                    :class="{ 'p-invalid': errors.delegation_fr?.length }"
                                    placeholder="Entrez la délégation en français"
                                    @input="validateDelegationFr"
                                />
                                <small v-if="errors.delegation_fr?.length" class="p-error">
                                    {{ errors.delegation_fr[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-4 field">
                                <label for="delegation_ar" class="block font-medium mb-2 text-right font-arabic">
                                    المعتمدية (العربية)
                                </label>
                                <InputText
                                    id="delegation_ar"
                                    v-model="form.delegation_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.delegation_ar?.length }"
                                    placeholder="أدخل المعتمدية بالعربية"
                                    @input="validateDelegationAr"
                                />
                                <small v-if="errors.delegation_ar?.length" class="p-error">
                                    {{ errors.delegation_ar[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="telephone_1" class="block font-medium mb-2">
                                    Téléphone 1
                                </label>
                                <InputText
                                    id="telephone_1"
                                    v-model="form.telephone_1"
                                    :class="{ 'p-invalid': errors.telephone_1?.length }"
                                    placeholder="Entrez le numéro de téléphone"
                                    @input="validateTelephone1"
                                />
                                <small v-if="errors.telephone_1?.length" class="p-error">
                                    {{ errors.telephone_1[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="telephone_2" class="block font-medium mb-2">
                                    Téléphone 2
                                </label>
                                <InputText
                                    id="telephone_2"
                                    v-model="form.telephone_2"
                                    :class="{ 'p-invalid': errors.telephone_2?.length }"
                                    placeholder="Entrez un autre numéro de téléphone"
                                    @input="validateTelephone2"
                                />
                                <small v-if="errors.telephone_2?.length" class="p-error">
                                    {{ errors.telephone_2[0] }}
                                </small>
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Rôles -->
                    <TabPanel header="Rôles">
                        <div class="grid p-fluid mt-4">
                            <div class="col-12 field">
                                <label for="roles" class="block font-medium mb-2">
                                    Rôles <span class="text-red-500">*</span>
                                </label>
                                <MultiSelect
                                    id="roles"
                                    v-model="form.roles"
                                    :options="availableRoles"
                                    optionLabel="name"
                                    optionValue="id"
                                    placeholder="Sélectionner des rôles"
                                    display="chip"
                                    class="invalid"
                                    :loading="loadingLists"
                                    @change="validateRoles"
                                />
                                <small v-if="errors.roles?.length" class="p-error">
                                    {{ errors.roles[0] }}
                                </small>
                                <small v-if="!availableRoles.length && !loadingLists" class="p-error">
                                    Aucun rôle disponible. Veuillez contacter l'administrateur.
                                </small>
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Connexion -->
                    <TabPanel header="Connexion">
                        <div class="grid p-fluid mt-4">
                            <div class="col-12 md:col-6 field">
                                <label for="email" class="block font-medium mb-2">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="email"
                                    v-model="form.email"
                                    class="invalid"
                                    placeholder="Entrez l'adresse email"
                                    @input="debouncedValidateEmail"
                                />
                                <small v-if="errors.email?.length" class="p-error">
                                    {{ errors.email[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="password" class="block font-medium mb-2">
                                    {{ isEditing ? 'Nouveau mot de passe' : 'Mot de passe' }}
                                </label>
                                <Password
                                    id="password"
                                    v-model="form.password"
                                    class="invalid"
                                    toggleMask
                                    :feedback="false"
                                    placeholder="Entrez le mot de passe"
                                    @input="validatePassword"
                                />
                                <small v-if="errors.password?.length" class="p-error">
                                    {{ errors.password[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="password_confirmation" class="block font-medium mb-2">
                                    {{ isEditing ? 'Confirmer nouveau mot de passe' : 'Confirmer mot de passe' }}
                                </label>
                                <Password
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    class="invalid"
                                    toggleMask
                                    :feedback="false"
                                    placeholder="Confirmez le mot de passe"
                                    @input="validatePassword"
                                />
                                <small v-if="errors.password_confirmation?.length" class="p-error">
                                    {{ errors.password_confirmation[0] }}
                                </small>
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Photo -->
                    <TabPanel header="Photo">
                        <div class="grid p-fluid mt-4">
                            <div class="col-12 md:col-6 field">
                                <label for="photo" class="block font-medium mb-2">
                                    Photo
                                </label>
                                <FileUpload
                                    ref="fileUpload"
                                    name="photo"
                                    accept="image/png,image/jpeg,image/jpg,image/gif,image/bmp,image/webp"
                                    :maxFileSize="2000000"
                                    :customUpload="true"
                                    chooseLabel="Choisir"
                                    cancelLabel="Annuler"
                                    @select="onFileSelect"
                                    @clear="onFileClear"
                                    :class="{ 'p-invalid': errors.photo?.length }"
                                >
                                    <template #empty>
                                        <p>
                                            Glissez-déposez une image ici ou cliquez pour sélectionner (PNG, JPG, JPEG, GIF, BMP, WEBP, max 2Mo).
                                        </p>
                                    </template>
                                </FileUpload>
                                <small v-if="errors.photo?.length" class="p-error">
                                    {{ errors.photo[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-2">
                                    Aperçu et Recadrage de la Photo
                                </label>
                                <div v-if="showCropper" class="cropper-container">
                                    <div v-if="!previewImage" class="flex flex-column align-items-center gap-2">
                                        <i class="pi pi-exclamation-triangle text-5xl text-red-500"></i>
                                        <p class="text-error">Aucune image à recadrer. Veuillez sélectionner une image valide.</p>
                                    </div>
                                    <Cropper
                                        :src="previewImage"
                                        :stencil-props="{
                                            movable: true,
                                            resizable: true,
                                            resizeHandlers: {
                                                enabled: true,
                                                positions: ['top-left', 'top-right', 'bottom-left', 'bottom-right'],
                                            },
                                            style: {
                                                border: '2px solid #3b82f6',
                                                borderRadius: '0',
                                                background: 'transparent',
                                            },
                                            initialSize: {
                                                width: 200,
                                                height: 150,
                                            },
                                        }"
                                        :canvas="{
                                            minWidth: 100,
                                            minHeight: 100,
                                            maxWidth: 200,
                                            maxHeight: 200,
                                        }"
                                        @change="onCropChange"
                                        @ready="onCropperReady"
                                    />
                                    <div v-if="previewImage" class="flex gap-2 mt-3">
                                        <Button
                                            label="Confirmer"
                                            icon="pi pi-check"
                                            class="p-button-success"
                                            @click="confirmCrop"
                                        />
                                        <Button
                                            label="Annuler"
                                            icon="pi pi-times"
                                            class="p-button-secondary"
                                            @click="cancelCrop"
                                        />
                                    </div>
                                </div>
                                <div v-else-if="photoPreview || (form.photo && isValidPhoto(form.photo))"
                                     class="flex align-items-center gap-3">
                                    <img
                                        :src="photoPreview || getPhotoUrl(form.photo)"
                                        alt="Aperçu de la photo"
                                        class="border-round shadow-2"
                                        style="max-width: 200px; max-height: 200px; object-fit: contain;"
                                    />
                                    <div class="flex flex-column gap-2">
                                        <Button
                                            label="Recadrer"
                                            icon="pi pi-crop"
                                            class="p-button-info p-button-outlined"
                                            @click="startCropping"
                                        />
                                        <Button
                                            label="Supprimer"
                                            icon="pi pi-trash"
                                            class="p-button-danger p-button-outlined"
                                            @click="deletePhoto"
                                        />
                                    </div>
                                </div>
                                <div v-else class="flex flex-column align-items-center gap-2">
                                    <i class="pi pi-image text-5xl text-surface-300"></i>
                                    <small class="text-error" v-if="imageError">{{ imageError }}</small>
                                    <p class="text-500">Aucune image sélectionnée ou existante.</p>
                                </div>
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Observation -->
                    <TabPanel header="Observations">
                        <div class="grid p-fluid mt-4">
                            <div class="col-12 field">
                                <label for="observation_fr" class="block font-medium mb-2">
                                    Observation (Français)
                                </label>
                                <Textarea
                                    id="observation_fr"
                                    v-model="form.observation_fr"
                                    rows="4"
                                    :class="{ 'p-invalid': errors.observation_fr?.length }"
                                    placeholder="Entrez l'observation en français"
                                    @input="validateObservationFr"
                                />
                                <small v-if="errors.observation_fr?.length" class="p-error">
                                    {{ errors.observation_fr[0] }}
                                </small>
                            </div>
                            <div class="col-12 field">
                                <label for="observation_ar" class="block font-medium mb-2 text-right font-arabic">
                                    الملاحظات (العربية)
                                </label>
                                <Textarea
                                    id="observation_ar"
                                    v-model="form.observation_ar"
                                    rows="4"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.observation_ar?.length }"
                                    placeholder="أدخل الملاحظات بالعربية"
                                    @input="validateObservationAr"
                                />
                                <small v-if="errors.observation_ar?.length" class="p-error">
                                    {{ errors.observation_ar[0] }}
                                </small>
                            </div>
                        </div>
                    </TabPanel>
                </TabView>
            </form>
        </div>

        <!-- Footer -->
        <template #footer>
            <div class="flex justify-content-end gap-2">
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-outlined p-button-secondary"
                    @click="close"
                />
                <Button
                    :label="isEditing ? 'Modifier' : 'Enregistrer'"
                    icon="pi pi-check"
                    class="p-button-primary"
                    :loading="isSaving"
                    :disabled="!isFormValid || isSaving"
                    @click="submitForm"
                />
            </div>
        </template>

        <Toast position="top-right" />
    </Dialog>
</template>

<script>
import { ref, computed, watch } from 'vue';
import { useToast } from 'primevue/usetoast';
import axios from '@/axios.js';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import MultiSelect from 'primevue/multiselect';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import ProgressSpinner from 'primevue/progressspinner';
import FileUpload from 'primevue/fileupload';
import Calendar from 'primevue/calendar';
import Textarea from 'primevue/textarea';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';
import { debounce } from 'lodash';

export default {
    components: {
        InputText,
        Dropdown,
        MultiSelect,
        Password,
        Button,
        Dialog,
        Toast,
        ProgressSpinner,
        FileUpload,
        Calendar,
        Textarea,
        TabView,
        TabPanel,
        Cropper,
    },
    props: {
        visible: { type: Boolean, required: true },
        userToEdit: { type: [Object, null], default: null },
        userId: { type: [Number, String, null], default: null },
    },
    emits: ['update:visible', 'save', 'update', 'close'],
    setup(props, { emit }) {
        const toast = useToast();
        const form = ref({
            id: null,
            nom_fr: '',
            prenom_fr: '',
            nom_ar: '',
            prenom_ar: '',
            cin: '',
            matricule: '',
            date_cin: null,
            lieu_cin_fr: '',
            lieu_cin_ar: '',
            date_naissance: null,
            lieu_naissance_fr: '',
            lieu_naissance_ar: '',
            nationalite_fr: 'Tunisienne',
            nationalite_ar: 'تونسية',
            genre_fr: 'Homme',
            genre_ar: 'ذكر',
            etat_civil_fr: '',
            etat_civil_ar: '',
            adresse_fr: '',
            adresse_ar: '',
            gouvernorat_fr: '',
            gouvernorat_ar: '',
            delegation_fr: '',
            delegation_ar: '',
            telephone_1: '',
            telephone_2: '',
            statut: 'Actif',
            cause_inactivite_fr: '',
            cause_inactivite_ar: '',
            observation_fr: '',
            observation_ar: '',
            photo: null,
            email: '',
            password: '',
            password_confirmation: '',
            roles: [],
            ajouter_par: null,
            date_recrutement: null,
            date_fin_service: null,
        });
        const errors = ref({});
        const isSaving = ref(false);
        const loading = ref(false);
        const loadingLists = ref(false);
        const availableRoles = ref([]);
        const lists = ref({});
        const showCropper = ref(false);
        const photoPreview = ref(null);
        const selectedFile = ref(null);
        const previewImage = ref(null);
        const croppedImage = ref(null);
        const imageError = ref(null);
        const fileUpload = ref(null);
        const tabview = ref(null);
        const activeTabIndex = ref(0);

        const isEditing = computed(() => !!props.userId || !!props.userToEdit?.id);
        const isFormValid = computed(() => {
            const requiredFieldsValid =
                form.value.nom_fr?.trim() &&
                form.value.prenom_fr?.trim() &&
                form.value.nom_ar?.trim() &&
                form.value.prenom_ar?.trim() &&
                form.value.cin?.trim() &&
                form.value.matricule?.trim() &&
                form.value.email?.trim() &&
                form.value.genre_fr &&
                form.value.statut &&
                form.value.roles.length > 0;

            const passwordValid = isEditing.value
                ? !form.value.password || (form.value.password && form.value.password === form.value.password_confirmation)
                : (!form.value.password || (form.value.password?.length >= 8 && form.value.password === form.value.password_confirmation));

            const noErrors = Object.values(errors.value).every(error => !error?.length);

            return !isSaving.value && requiredFieldsValid && passwordValid && noErrors;
        });

        const userRolesDisplay = computed(() => {
            return availableRoles.value.filter(role => form.value.roles.includes(role.id));
        });

        const gouvernoratOptions = computed(() => lists.value['Gouvernorats'] || []);

        const statutOptions = ref([
            { label: 'Actif', value: 'Actif' },
            { label: 'Inactif', value: 'Inactif' },
        ]);

        const causeInactiviteOptions = ref([
            { label_fr: 'Mise à la retraite', value_fr: 'Mise à la retraite', value_ar: 'الإحالة على التقاعد' },
            { label_fr: 'Dispense / Exemption', value_fr: 'Dispense / Exemption', value_ar: 'الإعفاء' },
            { label_fr: 'Révocation', value_fr: 'Révocation', value_ar: 'العزل' },
            { label_fr: 'Perte de la nationalité tunisienne', value_fr: 'Perte de la nationalité tunisienne', value_ar: 'فقدان الجنسية التونسية' },
            { label_fr: 'Perte des droits civils', value_fr: 'Perte des droits civils', value_ar: 'فقدان الحقوق المدنية' },
            { label_fr: 'Non-retour de l’agent après une période de détachement', value_fr: 'Non-retour de l’agent après une période de détachement', value_ar: 'عدم رجوع العون إثر فترة إلحاق' },
            { label_fr: 'Non-prise de fonction', value_fr: 'Non-prise de fonction', value_ar: 'عدم مباشرة' },
            { label_fr: 'Non-retour de l’agent après avoir accompli le service militaire', value_fr: 'Non-retour de l’agent après avoir accompli le service militaire', value_ar: 'عدم رجوع العون بعد قيامه بالخدمة الوطنية' },
            { label_fr: 'Non-retour de l’agent à son poste après la fin d’un congé pour création d’entreprise, après un avertissement', value_fr: 'Non-retour de l’agent à son poste après la fin d’un congé pour création d’entreprise, après un avertissement', value_ar: 'عدم رجوع العون إلى عمله إثر انتهاء عطلة لبعث مؤسسة، وبعد التنبيه عليه' },
        ]);

        const etatCivilOptions = ref([
            { label_fr: 'Célibataire', value_fr: 'Célibataire' },
            { label_fr: 'Marié', value_fr: 'Marié' },
            { label_fr: 'Divorcé', value_fr: 'Divorcé' },
            { label_fr: 'Veuf', value_fr: 'Veuf' },
        ]);

        const genreOptions = ref([
            { label_fr: 'Homme', value_fr: 'Homme' },
            { label_fr: 'Femme', value_fr: 'Femme' },
        ]);

        function isValidPhoto(photo) {
            if (!photo) return false;
            return photo.startsWith('data:image/') || photo.startsWith('blob:');
        }

        function getPhotoUrl(photo) {
            if (!photo) return '';
            if (photo.startsWith('data:image/') || photo.startsWith('blob:')) {
                return photo;
            }
            return `${window.location.origin}/storage/${photo}`;
        }

        // Fonctions de gestion de la photo
        function onFileSelect(event) {
            imageError.value = null;
            if (event.files?.length > 0) {
                const file = event.files[0];
                if (file.size > 2000000) {
                    imageError.value = "La taille de l'image ne doit pas dépasser 2 Mo";
                    toast.add({ severity: 'error', summary: 'Erreur', detail: imageError.value, life: 3000 });
                    return;
                }
                if (!file.type.match('image/(jpg|jpeg|png|gif|bmp|webp)')) {
                    imageError.value = 'Veuillez sélectionner une image valide (JPG, JPEG, PNG, GIF, BMP, WEBP)';
                    toast.add({ severity: 'error', summary: 'Erreur', detail: imageError.value, life: 3000 });
                    return;
                }
                selectedFile.value = file;
                previewImage.value = URL.createObjectURL(file);
                showCropper.value = true;
                photoPreview.value = null;
                form.value.photo = null;
            }
        }

        function onFileClear() {
            selectedFile.value = null;
            previewImage.value = null;
            croppedImage.value = null;
            photoPreview.value = null;
            showCropper.value = false;
            imageError.value = null;
            form.value.photo = null;
            errors.value.photo = [];
        }

        function onCropChange({ canvas }) {
            croppedImage.value = canvas.toDataURL('image/png');
        }

        function onCropperReady() {
            // Initialisation du cropper
        }

        function confirmCrop() {
            if (croppedImage.value) {
                form.value.photo = croppedImage.value;
                photoPreview.value = croppedImage.value;
                previewImage.value = null;
                showCropper.value = false;
                selectedFile.value = null;
                errors.value.photo = [];
                toast.add({
                    severity: 'success',
                    summary: 'Recadrage confirmé',
                    detail: 'Image recadrée avec succès.',
                    life: 3000,
                });
            } else {
                toast.add({
                    severity: 'warn',
                    summary: 'Aucun recadrage',
                    detail: 'Veuillez recadrer l\'image avant de confirmer.',
                    life: 3000,
                });
            }
        }

        function cancelCrop() {
            previewImage.value = null;
            croppedImage.value = null;
            showCropper.value = false;
            selectedFile.value = null;
            photoPreview.value = form.value.photo ? getPhotoUrl(form.value.photo) : null;
            errors.value.photo = [];
            toast.add({
                severity: 'info',
                summary: 'Recadrage annulé',
                detail: 'Recadrage de l\'image annulé.',
                life: 3000,
            });
        }

        function startCropping() {
            if (!form.value.photo && !photoPreview.value) {
                imageError.value = 'Aucune image sélectionnée.';
                toast.add({ severity: 'error', summary: 'Erreur', detail: imageError.value, life: 3000 });
                return;
            }
            previewImage.value = photoPreview.value || getPhotoUrl(form.value.photo);
            showCropper.value = true;
            selectedFile.value = null;
            croppedImage.value = null;
        }

        function deletePhoto() {
            form.value.photo = null;
            photoPreview.value = null;
            selectedFile.value = null;
            previewImage.value = null;
            croppedImage.value = null;
            imageError.value = null;
            showCropper.value = false;
            errors.value.photo = [];
            if (fileUpload.value) fileUpload.value.clear();
            toast.add({
                severity: 'info',
                summary: 'Supprimé',
                detail: 'La photo a été supprimée.',
                life: 3000,
            });
        }

        function validatePhoto() {
            errors.value.photo = [];
            if (form.value.photo) {
                if (!form.value.photo.startsWith('data:image/')) {
                    errors.value.photo = ['Le format de la photo doit être PNG, JPEG, JPG, GIF, BMP ou WEBP.'];
                } else {
                    const base64String = form.value.photo.replace(/^data:image\/(png|jpeg|jpg|gif|bmp|webp);base64,/, '');
                    const decoded = window.atob(base64String);
                    const sizeInBytes = decoded.length;
                    if (sizeInBytes > 2 * 1024 * 1024) {
                        errors.value.photo = ['La photo ne doit pas dépasser 2 Mo.'];
                    }
                }
            }
        }

        // Fonctions de validation
        function validateNomFr() {
            errors.value.nom_fr = form.value.nom_fr?.trim() && form.value.nom_fr.length <= 255
                ? []
                : form.value.nom_fr?.length > 255
                    ? ['Le nom en français ne doit pas dépasser 255 caractères.']
                    : ['Le nom en français est requis.'];
        }

        function validatePrenomFr() {
            errors.value.prenom_fr = form.value.prenom_fr?.trim() && form.value.prenom_fr.length <= 255
                ? []
                : form.value.prenom_fr?.length > 255
                    ? ['Le prénom en français ne doit pas dépasser 255 caractères.']
                    : ['Le prénom en français est requis.'];
        }

        function validateNomAr() {
            errors.value.nom_ar = form.value.nom_ar?.trim() && form.value.nom_ar.length <= 255
                ? []
                : form.value.nom_ar?.length > 255
                    ? ['اللقب بالعربية لا يجب أن يتجاوز 255 حرفًا.']
                    : ['اللقب بالعربية مطلوب.'];
        }

        function validatePrenomAr() {
            errors.value.prenom_ar = form.value.prenom_ar?.trim() && form.value.prenom_ar.length <= 255
                ? []
                : form.value.prenom_ar?.length > 255
                    ? ['الاسم بالعربية لا يجب أن يتجاوز 255 حرفًا.']
                    : ['الاسم بالعربية مطلوب.'];
        }

        function validateLieuCinFr() {
            errors.value.lieu_cin_fr = form.value.lieu_cin_fr && form.value.lieu_cin_fr.length > 255
                ? ['Le lieu CIN en français ne doit pas dépasser 255 caractères.']
                : [];
        }

        function validateLieuCinAr() {
            errors.value.lieu_cin_ar = form.value.lieu_cin_ar && form.value.lieu_cin_ar.length > 255
                ? ['مكان إصدار البطاقة بالعربية لا يجب أن يتجاوز 255 حرفًا.']
                : [];
        }

        function validateLieuNaissanceFr() {
            errors.value.lieu_naissance_fr = form.value.lieu_naissance_fr && form.value.lieu_naissance_fr.length > 255
                ? ['Le lieu de naissance en français ne doit pas dépasser 255 caractères.']
                : [];
        }

        function validateLieuNaissanceAr() {
            errors.value.lieu_naissance_ar = form.value.lieu_naissance_ar && form.value.lieu_naissance_ar.length > 255
                ? ['مكان الولادة بالعربية لا يجب أن يتجاوز 255 حرفًا.']
                : [];
        }

        function validateNationaliteFr() {
            errors.value.nationalite_fr = form.value.nationalite_fr && form.value.nationalite_fr.length > 255
                ? ['La nationalité en français ne doit pas dépasser 255 caractères.']
                : [];
        }

        function validateNationaliteAr() {
            errors.value.nationalite_ar = form.value.nationalite_ar && form.value.nationalite_ar.length > 255
                ? ['الجنسية بالعربية لا يجب أن تتجاوز 255 حرفًا.']
                : [];
        }

        function validateEtatCivilFr() {
            errors.value.etat_civil_fr = form.value.etat_civil_fr && !['Célibataire', 'Marié', 'Divorcé', 'Veuf'].includes(form.value.etat_civil_fr)
                ? ['L\'état civil en français doit être Célibataire, Marié, Divorcé ou Veuf.']
                : [];
        }

        function validateEtatCivilAr() {
            errors.value.etat_civil_ar = form.value.etat_civil_ar && !['أعزب(اء)', 'متزوج(ة)', 'مطلق(ة)', 'أرمل(ة)'].includes(form.value.etat_civil_ar)
                ? ['الحالة المدنية بالعربية يجب أن تكون أعزب(اء)، متزوج(ة)، مطلق(ة) أو أرمل(ة).']
                : [];
        }

        function validateAdresseFr() {
            errors.value.adresse_fr = form.value.adresse_fr && form.value.adresse_fr.length > 255
                ? ['L\'adresse en français ne doit pas dépasser 255 caractères.']
                : [];
        }

        function validateAdresseAr() {
            errors.value.adresse_ar = form.value.adresse_ar && form.value.adresse_ar.length > 255
                ? ['العنوان بالعربية لا يجب أن يتجاوز 255 حرفًا.']
                : [];
        }

        function validateGouvernoratFr() {
            errors.value.gouvernorat_fr = form.value.gouvernorat_fr && form.value.gouvernorat_fr.length > 255
                ? ['Le gouvernorat ne doit pas dépasser 255 caractères.']
                : [];
        }

        function validateGouvernoratAr() {
            errors.value.gouvernorat_ar = form.value.gouvernorat_ar && form.value.gouvernorat_ar.length > 255
                ? ['المحافظة بالعربية لا يجب أن تتجاوز 255 حرفًا.']
                : [];
        }

        function validateDelegationFr() {
            errors.value.delegation_fr = form.value.delegation_fr && form.value.delegation_fr.length > 255
                ? ['La délégation en français ne doit pas dépasser 255 caractères.']
                : [];
        }

        function validateDelegationAr() {
            errors.value.delegation_ar = form.value.delegation_ar && form.value.delegation_ar.length > 255
                ? ['المعتمدية لا يجب أن تتجاوز 255 حرفًا.']
                : [];
        }

        function validateTelephone1() {
            errors.value.telephone_1 = form.value.telephone_1 && !/^\+?\d{8,15}$/.test(form.value.telephone_1)
                ? ['Le téléphone 1 doit contenir entre 8 et 15 chiffres.']
                : [];
        }

        function validateTelephone2() {
            errors.value.telephone_2 = form.value.telephone_2 && !/^\+?\d{8,15}$/.test(form.value.telephone_2)
                ? ['Le téléphone 2 doit contenir entre 8 et 15 chiffres.']
                : [];
        }

        function validateStatut() {
            errors.value.statut = form.value.statut && !['Actif', 'Inactif'].includes(form.value.statut)
                ? ['Le statut doit être Actif ou Inactif.']
                : form.value.statut ? [] : ['Le statut est requis.'];
            if (form.value.statut === 'Actif') {
                form.value.cause_inactivite_fr = '';
                form.value.cause_inactivite_ar = '';
                errors.value.cause_inactivite_fr = [];
                errors.value.cause_inactivite_ar = [];
            }
        }

        function validateCauseInactiviteFr() {
            errors.value.cause_inactivite_fr = [];
            if (form.value.statut === 'Inactif' && form.value.cause_inactivite_fr && !causeInactiviteOptions.value.some(option => option.value_fr === form.value.cause_inactivite_fr)) {
                errors.value.cause_inactivite_fr = ['La cause d\'inactivité en français doit être une valeur valide.'];
            }
        }

        function validateGenreFr() {
            const genreMap = {
                'Homme': 'ذكر',
                'Femme': 'أنثى',
            };
            errors.value.genre_fr = form.value.genre_fr && ['Homme', 'Femme'].includes(form.value.genre_fr)
                ? []
                : ['Le genre doit être Homme ou Femme.'];
            errors.value.genre_ar = form.value.genre_ar && form.value.genre_ar === genreMap[form.value.genre_fr]
                ? []
                : ['Le genre en arabe doit correspondre au genre en français.'];
        }

        function validateDateNaissance() {
            errors.value.date_naissance = form.value.date_naissance && new Date(form.value.date_naissance) > new Date()
                ? ['La date de naissance ne peut pas être dans le futur.']
                : [];
        }

        function validateDateCin() {
            errors.value.date_cin = form.value.date_cin && new Date(form.value.date_cin) > new Date()
                ? ['La date CIN ne peut pas être dans le futur.']
                : [];
        }

        function validateDateRecrutement() {
            errors.value.date_recrutement = [];
            if (form.value.date_recrutement) {
                const today = new Date();
                const recrutementDate = new Date(form.value.date_recrutement);
                if (recrutementDate > today) {
                    errors.value.date_recrutement = ['La date de recrutement ne peut pas être dans le futur.'];
                }
                if (form.value.date_fin_service) {
                    const finServiceDate = new Date(form.value.date_fin_service);
                    if (finServiceDate < recrutementDate) {
                        errors.value.date_fin_service = ['La date de fin de service doit être postérieure ou égale à la date de recrutement.'];
                    }
                }
            }
        }

        function validateDateFinService() {
            errors.value.date_fin_service = [];
            if (form.value.date_fin_service) {
                const finServiceDate = new Date(form.value.date_fin_service);
                if (form.value.date_recrutement) {
                    const recrutementDate = new Date(form.value.date_recrutement);
                    if (finServiceDate < recrutementDate) {
                        errors.value.date_fin_service = ['La date de fin de service doit être postérieure ou égale à la date de recrutement.'];
                    }
                }
            }
        }

        function validateObservationFr() {
            errors.value.observation_fr = form.value.observation_fr && form.value.observation_fr.length > 1000
                ? ['L\'observation en français ne doit pas dépasser 1000 caractères.']
                : [];
        }

        function validateObservationAr() {
            errors.value.observation_ar = form.value.observation_ar && form.value.observation_ar.length > 1000
                ? ['الملاحظات بالعربية لا يجب أن تتجاوز 1000 حرف.']
                : [];
        }

        function validateRoles() {
            errors.value.roles = form.value.roles.length === 0
                ? ['Au moins un rôle est requis.']
                : [];
        }

        function validatePassword() {
            if (!isEditing.value) {
                errors.value.password = form.value.password && form.value.password.length < 8
                    ? ['Le mot de passe doit contenir au moins 8 caractères.']
                    : [];

                errors.value.password_confirmation =
                    form.value.password && form.value.password_confirmation && form.value.password !== form.value.password_confirmation
                        ? ['Les mots de passe ne correspondent pas.']
                        : [];
            } else {
                errors.value.password = form.value.password && form.value.password.length < 8
                    ? ['Le mot de passe doit contenir au moins 8 caractères.']
                    : [];

                errors.value.password_confirmation =
                    form.value.password && form.value.password_confirmation && form.value.password !== form.value.password_confirmation
                        ? ['Les mots de passe ne correspondent pas.']
                        : form.value.password && !form.value.password_confirmation
                            ? ['La confirmation du mot de passe est requise.']
                            : [];
            }
        }

        const debouncedValidateMatricule = debounce(async () => {
            if (!form.value.matricule?.trim()) {
                errors.value.matricule = ['Le matricule est requis.'];
                return;
            }
            if (form.value.matricule.length > 20) {
                errors.value.matricule = ['Le matricule ne doit pas dépasser 20 caractères.']
                return;
            }
            if (isEditing.value && form.value.matricule === props.userToEdit?.matricule) {
                errors.value.matricule = [];
                return;
            }
            try {
                const response = await axios.post('/api/personnels_atfp/check-matricule', {
                    matricule: form.value.matricule,
                }, {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });
                errors.value.matricule = response.data.exists ? ['Ce matricule existe déjà.'] : [];
            } catch (error) {
                errors.value.matricule = ['Erreur lors de la validation du matricule.'];
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Erreur lors de la validation du matricule.',
                    life: 5000,
                });
            }
        }, 500);

        const debouncedValidateCin = debounce(async () => {
            if (!form.value.cin?.trim()) {
                errors.value.cin = ['Le CIN est requis.'];
                return;
            }
            if (!/^\d{8}$/.test(form.value.cin)) {
                errors.value.cin = ['Le CIN doit contenir exactement 8 chiffres.'];
                return;
            }
            if (isEditing.value && form.value.cin === props.userToEdit?.cin) {
                errors.value.cin = [];
                return;
            }
            try {
                const response = await axios.post('/api/personnels_atfp/check-cin', {
                    cin: form.value.cin,
                    ...(isEditing.value && form.value.id ? { user_id: form.value.id } : {}),
                }, {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });
                errors.value.cin = response.data.exists ? ['Ce CIN existe déjà.'] : [];
            } catch (error) {
                errors.value.cin = ['Erreur lors de la validation du CIN.'];
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Erreur lors de la validation du CIN.',
                    life: 5000,
                });
            }
        }, 500);

        const debouncedValidateEmail = debounce(async () => {
            if (!form.value.email?.trim()) {
                errors.value.email = ["L'email est requis."];
                return;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
                errors.value.email = ["L'email n'est pas valide."];
                return;
            }
            if (form.value.email.length > 255) {
                errors.value.email = ["L'email ne doit pas dépasser 255 caractères."];
                return;
            }
            if (isEditing.value && form.value.email === props.userToEdit?.email) {
                errors.value.email = [];
                return;
            }
            try {
                const payload = {
                    email: form.value.email,
                    ...(isEditing.value && form.value.id ? { user_id: form.value.id } : {}),
                };
                const response = await axios.post('/api/personnels_atfp/check-email', payload, {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });
                errors.value.email = response.data.exists ? ['Cet email existe déjà.'] : [];
            } catch (error) {
                errors.value.email = ['Erreur lors de la validation de l\'email.'];
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Erreur lors de la validation de l\'email.',
                    life: 5000,
                });
            }
        }, 500);

        const onGouvernoratChange = () => {
            const selectedGouvernorat = lists.value['Gouvernorats']?.find(
                gouv => gouv.nom_fr === form.value.gouvernorat_fr
            );
            form.value.gouvernorat_ar = selectedGouvernorat?.nom_ar || '';
            validateGouvernoratFr();
            validateGouvernoratAr();
        };

        const onEtatCivilChange = () => {
            const etatCivilMap = {
                'Célibataire': 'أعزب(اء)',
                'Marié': 'متزوج(ة)',
                'Divorcé': 'مطلق(ة)',
                'Veuf': 'أرمل(ة)',
            };
            form.value.etat_civil_ar = etatCivilMap[form.value.etat_civil_fr] || '';
            validateEtatCivilFr();
            validateEtatCivilAr();
        };

        const onGenreChange = () => {
            const genreMap = {
                'Homme': 'ذكر',
                'Femme': 'أنثى',
            };
            form.value.genre_ar = genreMap[form.value.genre_fr] || '';
            validateGenreFr();
        };

        const onCauseInactiviteChange = () => {
            const causeInactiviteMap = {
                'Mise à la retraite': 'الإحالة على التقاعد',
                'Dispense / Exemption': 'الإعفاء',
                'Révocation': 'العزل',
                'Perte de la nationalité tunisienne': 'فقدان الجنسية التونسية',
                'Perte des droits civils': 'فقدان الحقوق المدنية',
                'Non-retour de l’agent après une période de détachement': 'عدم رجوع العون إثر فترة إلحاق',
                'Non-prise de fonction': 'عدم مباشرة',
                'Non-retour de l’agent après avoir accompli le service militaire': 'عدم رجوع العون بعد قيامه بالخدمة الوطنية',
                'Non-retour de l’agent à son poste après la fin d’un congé pour création d’entreprise, après un avertissement': 'عدم رجوع العون إلى عمله إثر انتهاء عطلة لبعث مؤسسة، وبعد التنبيه عليه',
            };
            form.value.cause_inactivite_ar = causeInactiviteMap[form.value.cause_inactivite_fr] || '';
            validateCauseInactiviteFr();
        };

        const validateForm = async () => {
            errors.value = {};
            await Promise.all([
                debouncedValidateMatricule.flush(),
                debouncedValidateCin.flush(),
                debouncedValidateEmail.flush(),
            ]);
            validateNomFr();
            validatePrenomFr();
            validateNomAr();
            validatePrenomAr();
            validateLieuCinFr();
            validateLieuCinAr();
            validateDateCin();
            validateDateNaissance();
            validateLieuNaissanceFr();
            validateLieuNaissanceAr();
            validateNationaliteFr();
            validateNationaliteAr();
            validateGenreFr();
            validateEtatCivilFr();
            validateEtatCivilAr();
            validateAdresseFr();
            validateAdresseAr();
            validateGouvernoratFr();
            validateGouvernoratAr();
            validateDelegationFr();
            validateDelegationAr();
            validateTelephone1();
            validateTelephone2();
            validateStatut();
            validateCauseInactiviteFr();
            validateObservationFr();
            validateObservationAr();
            validateRoles();
            validatePassword();
            validatePhoto();
            validateDateRecrutement();
            validateDateFinService();
        };

        const fetchRoles = async () => {
            loadingLists.value = true;
            try {
                const response = await axios.get('/api/roles', {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });
                if (!Array.isArray(response.data.data)) {
                    throw new Error('Les données des rôles ne sont pas au format attendu.');
                }
                availableRoles.value = response.data.data || [];
                if (availableRoles.value.length === 0) {
                    toast.add({
                        severity: 'warn',
                        summary: 'Avertissement',
                        detail: 'Aucun rôle disponible. Veuillez vérifier la configuration des rôles.',
                        life: 5000,
                    });
                }
            } catch (error) {
                console.error('Erreur fetchRoles:', error);
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Erreur lors de la récupération des rôles.',
                    life: 5000,
                });
                if (error.response?.status === 401) {
                    emit('update:visible', false);
                    window.location.href = '/login';
                }
            } finally {
                loadingLists.value = false;
            }
        };

        const fetchLists = async () => {
            loadingLists.value = true;
            try {
                const response = await axios.get('/api/personnels_atfp/lists', {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });
                if (!response.data.success) {
                    throw new Error(response.data.message || 'Erreur lors de la récupération des listes.');
                }
                lists.value = response.data.data;
            } catch (error) {
                console.error('Fetch lists error:', error);
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Erreur lors de la récupération des listes.',
                    life: 5000,
                });
                if (error.response?.status === 401) {
                    emit('update:visible', false);
                    window.location.href = '/login';
                }
            } finally {
                loadingLists.value = false;
            }
        };

        const fetchUserData = async (userId) => {
            loading.value = true;
            try {
                const response = await axios.get(`/api/personnels_atfp-with-options/${userId}`, {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });
                if (!response.data.success) {
                    throw new Error(response.data.message || 'Erreur lors de la récupération des données utilisateur.');
                }
                const { personnel, lists: responseLists, roles } = response.data.data;
                const genreMap = {
                    'Homme': 'ذكر',
                    'Femme': 'أنثى',
                };
                form.value = {
                    ...form.value,
                    ...personnel,
                    roles: Array.isArray(roles) ? roles : [],
                    date_cin: personnel.date_cin ? new Date(personnel.date_cin) : null,
                    date_naissance: personnel.date_naissance ? new Date(personnel.date_naissance) : null,
                    date_recrutement: personnel.date_recrutement ? new Date(personnel.date_recrutement) : null,
                    date_fin_service: personnel.date_fin_service ? new Date(personnel.date_fin_service) : null,
                    password: '',
                    password_confirmation: '',
                    nationalite_fr: personnel.nationalite_fr || 'Tunisienne',
                    nationalite_ar: personnel.nationalite_ar || 'تونسية',
                    genre_ar: genreMap[personnel.genre_fr] || personnel.genre_ar,
                    cause_inactivite_fr: personnel.cause_inactivite_fr || '',
                    cause_inactivite_ar: personnel.cause_inactivite_ar || '',
                };
                lists.value = responseLists;
                await validateForm();
            } catch (error) {
                console.error('Fetch user data error:', error);
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Erreur lors de la récupération des données utilisateur.',
                    life: 5000,
                });
                if (error.response?.status === 401) {
                    emit('update:visible', false);
                    window.location.href = '/login';
                }
            } finally {
                loading.value = false;
            }
        };

        const submitForm = async () => {
            isSaving.value = true;
            await validateForm();

            if (Object.keys(errors.value).some(key => errors.value[key]?.length)) {
                const errorFields = Object.keys(errors.value).filter(key => errors.value[key]?.length);
                if (errorFields.some(field => ['matricule', 'cin', 'date_cin', 'lieu_cin_fr', 'lieu_cin_ar'].includes(field))) {
                    activeTabIndex.value = 0;
                } else if (errorFields.some(field => ['nom_fr', 'prenom_fr', 'nom_ar', 'prenom_ar', 'genre_fr', 'etat_civil_fr', 'date_naissance', 'lieu_naissance_fr', 'lieu_naissance_ar', 'nationalite_fr', 'nationalite_ar'].includes(field))) {
                    activeTabIndex.value = 1;
                } else if (errorFields.some(field => ['statut', 'cause_inactivite_fr', 'date_recrutement', 'date_fin_service'].includes(field))) {
                    activeTabIndex.value = 2;
                } else if (errorFields.some(field => ['adresse_fr', 'adresse_ar', 'gouvernorat_fr', 'delegation_fr', 'delegation_ar', 'telephone_1', 'telephone_2'].includes(field))) {
                    activeTabIndex.value = 3;
                } else if (errorFields.includes('roles')) {
                    activeTabIndex.value = 4;
                } else if (errorFields.some(field => ['email', 'password', 'password_confirmation'].includes(field))) {
                    activeTabIndex.value = 5;
                } else if (errorFields.includes('photo')) {
                    activeTabIndex.value = 6;
                } else if (errorFields.some(field => ['observation_fr', 'observation_ar'].includes(field))) {
                    activeTabIndex.value = 7;
                }
                toast.add({
                    severity: 'error',
                    summary: 'Erreur de validation',
                    detail: 'Veuillez vérifier les champs du formulaire.',
                    life: 3000,
                });
                isSaving.value = false;
                return;
            }

            try {
                const payload = {
                    nom_fr: form.value.nom_fr,
                    prenom_fr: form.value.prenom_fr,
                    nom_ar: form.value.nom_ar,
                    prenom_ar: form.value.prenom_ar,
                    cin: form.value.cin,
                    matricule: form.value.matricule,
                    date_cin: form.value.date_cin ? form.value.date_cin.toISOString().split('T')[0] : null,
                    lieu_cin_fr: form.value.lieu_cin_fr,
                    lieu_cin_ar: form.value.lieu_cin_ar,
                    date_naissance: form.value.date_naissance ? form.value.date_naissance.toISOString().split('T')[0] : null,
                    lieu_naissance_fr: form.value.lieu_naissance_fr,
                    lieu_naissance_ar: form.value.lieu_naissance_ar,
                    nationalite_fr: form.value.nationalite_fr,
                    nationalite_ar: form.value.nationalite_ar,
                    genre_fr: form.value.genre_fr,
                    genre_ar: form.value.genre_ar,
                    etat_civil_fr: form.value.etat_civil_fr,
                    etat_civil_ar: form.value.etat_civil_ar,
                    adresse_fr: form.value.adresse_fr,
                    adresse_ar: form.value.adresse_ar,
                    gouvernorat_fr: form.value.gouvernorat_fr,
                    gouvernorat_ar: form.value.gouvernorat_ar,
                    delegation_fr: form.value.delegation_fr,
                    delegation_ar: form.value.delegation_ar,
                    telephone_1: form.value.telephone_1,
                    telephone_2: form.value.telephone_2,
                    statut: form.value.statut,
                    cause_inactivite_fr: form.value.cause_inactivite_fr,
                    cause_inactivite_ar: form.value.cause_inactivite_ar,
                    observation_fr: form.value.observation_fr,
                    observation_ar: form.value.observation_ar,
                    photo: form.value.photo,
                    ajouter_par: localStorage.getItem('user_id'),
                    email: form.value.email,
                    roles: form.value.roles,
                    date_recrutement: form.value.date_recrutement ? form.value.date_recrutement.toISOString().split('T')[0] : null,
                    date_fin_service: form.value.date_fin_service ? form.value.date_fin_service.toISOString().split('T')[0] : null,
                };
                if (form.value.password) {
                    payload.password = form.value.password;
                    payload.password_confirmation = form.value.password_confirmation;
                }

                let response;
                if (isEditing.value) {
                    response = await axios.put(`/api/personnels_atfp/${form.value.id}`, payload, {
                        headers: {
                            Authorization: `Bearer ${localStorage.getItem('token')}`,
                        },
                    });
                    emit('update', response.data.data);
                } else {
                    response = await axios.post('/api/personnels_atfp', payload, {
                        headers: {
                            Authorization: `Bearer ${localStorage.getItem('token')}`,
                        },
                    });
                    emit('save', response.data.data);
                }
                toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: isEditing.value ? 'Utilisateur mis à jour avec succès.' : 'Utilisateur créé avec succès.',
                    life: 3000,
                });
                close();
            } catch (error) {
                console.error('Submit form error:', error);
                if (error.response?.status === 422) {
                    errors.value = Object.entries(error.response.data.errors).reduce((acc, [key, value]) => {
                        acc[key] = Array.isArray(value) ? value : [value];
                        return acc;
                    }, {});
                    const errorFields = Object.keys(errors.value).filter(key => errors.value[key]?.length);
                    if (errorFields.some(field => ['matricule', 'cin', 'date_cin', 'lieu_cin_fr', 'lieu_cin_ar'].includes(field))) {
                        activeTabIndex.value = 0;
                    } else if (errorFields.some(field => ['nom_fr', 'prenom_fr', 'nom_ar', 'prenom_ar', 'genre_fr', 'etat_civil_fr', 'date_naissance', 'lieu_naissance_fr', 'lieu_naissance_ar', 'nationalite_fr', 'nationalite_ar'].includes(field))) {
                        activeTabIndex.value = 1;
                    } else if (errorFields.some(field => ['statut', 'cause_inactivite_fr', 'date_recrutement', 'date_fin_service'].includes(field))) {
                        activeTabIndex.value = 2;
                    } else if (errorFields.some(field => ['adresse_fr', 'adresse_ar', 'gouvernorat_fr', 'delegation_fr', 'delegation_ar', 'telephone_1', 'telephone_2'].includes(field))) {
                        activeTabIndex.value = 3;
                    } else if (errorFields.includes('roles')) {
                        activeTabIndex.value = 4;
                    } else if (errorFields.some(field => ['email', 'password', 'password_confirmation'].includes(field))) {
                        activeTabIndex.value = 5;
                    } else if (errorFields.includes('photo')) {
                        activeTabIndex.value = 6;
                    } else if (errorFields.some(field => ['observation_fr', 'observation_ar'].includes(field))) {
                        activeTabIndex.value = 7;
                    }
                    toast.add({
                        severity: 'error',
                        summary: 'Erreur de validation',
                        detail: 'Veuillez vérifier les champs du formulaire.',
                        life: 3000,
                    });
                } else {
                    toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: error.response?.data?.message || "Erreur lors de l'enregistrement.",
                        life: 3000,
                    });
                }
            } finally {
                isSaving.value = false;
            }
        };

        const close = () => {
            resetForm();
            emit('update:visible', false);
            emit('close');
        };

        const resetForm = () => {
            form.value = {
                id: null,
                nom_fr: '',
                prenom_fr: '',
                nom_ar: '',
                prenom_ar: '',
                cin: '',
                matricule: '',
                date_cin: null,
                lieu_cin_fr: '',
                lieu_cin_ar: '',
                date_naissance: null,
                lieu_naissance_fr: '',
                lieu_naissance_ar: '',
                nationalite_fr: 'Tunisienne',
                nationalite_ar: 'تونسية',
                genre_fr: 'Homme',
                genre_ar: 'ذكر',
                etat_civil_fr: '',
                etat_civil_ar: '',
                adresse_fr: '',
                adresse_ar: '',
                gouvernorat_fr: '',
                gouvernorat_ar: '',
                delegation_fr: '',
                delegation_ar: '',
                telephone_1: '',
                telephone_2: '',
                statut: 'Actif',
                cause_inactivite_fr: '',
                cause_inactivite_ar: '',
                observation_fr: '',
                observation_ar: '',
                photo: null,
                email: '',
                password: '',
                password_confirmation: '',
                roles: [],
                ajouter_par: null,
                date_recrutement: null,
                date_fin_service: null,
            };
            errors.value = {};
            photoPreview.value = null;
            selectedFile.value = null;
            previewImage.value = null;
            croppedImage.value = null;
            imageError.value = null;
            showCropper.value = false;
            activeTabIndex.value = 0;
            if (fileUpload.value) fileUpload.value.clear();
        };

        watch(
            () => props.visible,
            async (newVal) => {
                if (newVal) {
                    resetForm();
                    await Promise.all([fetchRoles(), fetchLists()]);
                    if (isEditing.value && (props.userId || props.userToEdit?.id)) {
                        await fetchUserData(props.userId || props.userToEdit.id);
                    }
                }
            },
            { immediate: true }
        );

        watch(
            () => props.userToEdit,
            (newUser) => {
                if (newUser && isEditing.value) {
                    const genreMap = {
                        'Homme': 'ذكر',
                        'Femme': 'أنثى',
                    };
                    form.value = {
                        ...form.value,
                        ...newUser,
                        roles: Array.isArray(newUser.roles) ? newUser.roles.map(role => role.id) : [],
                        date_cin: newUser.date_cin ? new Date(newUser.date_cin) : null,
                        date_naissance: newUser.date_naissance ? new Date(newUser.date_naissance) : null,
                        date_recrutement: newUser.date_recrutement ? new Date(newUser.date_recrutement) : null,
                        date_fin_service: newUser.date_fin_service ? new Date(newUser.date_fin_service) : null,
                        password: '',
                        password_confirmation: '',
                        nationalite_fr: newUser.nationalite_fr || 'Tunisienne',
                        nationalite_ar: newUser.nationalite_ar || 'تونسية',
                        genre_ar: genreMap[newUser.genre_fr] || newUser.genre_ar || 'ذكر',
                        cause_inactivite_fr: newUser.cause_inactivite_fr || '',
                        cause_inactivite_ar: newUser.cause_inactivite_ar || '',
                    };
                    validateForm();
                }
            }
        );

        watch(
            () => form.value.statut,
            (newStatut) => {
                if (newStatut === 'Actif') {
                    form.value.cause_inactivite_fr = '';
                    form.value.cause_inactivite_ar = '';
                    errors.value.cause_inactivite_fr = [];
                    errors.value.cause_inactivite_ar = [];
                }
                validateStatut();
            }
        );

        return {
            form,
            errors,
            isSaving,
            loading,
            loadingLists,
            availableRoles,
            isEditing,
            isFormValid,
            userRolesDisplay,
            gouvernoratOptions,
            statutOptions,
            causeInactiviteOptions,
            etatCivilOptions,
            genreOptions,
            showCropper,
            photoPreview,
            selectedFile,
            previewImage,
            croppedImage,
            imageError,
            fileUpload,
            tabview,
            activeTabIndex,
            submitForm,
            close,
            onFileSelect,
            onFileClear,
            onCropChange,
            onCropperReady,
            confirmCrop,
            cancelCrop,
            startCropping,
            deletePhoto,
            getPhotoUrl,
            isValidPhoto,
            onGouvernoratChange,
            onEtatCivilChange,
            onGenreChange,
            onCauseInactiviteChange,
            validateNomFr,
            validatePrenomFr,
            validateNomAr,
            validatePrenomAr,
            validateLieuCinFr,
            validateLieuCinAr,
            validateDateCin,
            validateDateNaissance,
            validateLieuNaissanceFr,
            validateLieuNaissanceAr,
            validateNationaliteFr,
            validateNationaliteAr,
            validateAdresseFr,
            validateAdresseAr,
            validateGouvernoratFr,
            validateGouvernoratAr,
            validateDelegationFr,
            validateDelegationAr,
            validateTelephone1,
            validateTelephone2,
            validateStatut,
            validateCauseInactiviteFr,
            validateObservationFr,
            validateObservationAr,
            validateRoles,
            validatePassword,
            validateDateRecrutement,
            validateDateFinService,
            validatePhoto,
            debouncedValidateMatricule,
            debouncedValidateCin,
            debouncedValidateEmail,
        };
    }
};
</script>

<style scoped>

@font-face {
    font-family: 'Montserrat-Arabic-Regular';
    src: url('/fonts/Montserrat-Arabic-Regular.otf') format('opentype');
    font-weight: normal;
    font-style: normal;
}
.font-arabic,
:deep(.p-inputtext[dir='rtl']),
:deep(.p-textarea[dir='rtl']) {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
}

:deep(.p-tabview .p-tabview-nav) {
    background: var(--surface-ground);
    border-bottom: 1px solid var(--surface-border);
}

:deep(.p-tabview .p-tabview-nav li .p-tabview-nav-link) {
    background: transparent;
    border-color: transparent;
    color: var(--text-color-secondary);
    font-weight: 500;
    padding: 1rem 1.25rem;
    transition: all 0.2s ease;
}



:deep(.p-tabview .p-tabview-nav li.p-highlight .p-tabview-nav-link) {
    background-color: var(--primary-color);
    color: white;
}

:deep(.p-tabview .p-tabview-nav li .p-tabview-nav-link:not(.p-disabled):hover) {
    background-color: var(--surface-100);
}
:deep(.custom-role-chip) {
    background-color: var(--primary-color);
    color: white;
    padding: 0.4rem 0.75rem; /* ↑ hauteur ↑ | → largeur → */
    border-radius: 16px; /* facultatif : arrondir les coins */
    font-size: 0.9rem;    /* facultatif : réduire ou ajuster le texte */
}






.cropper-container {
    max-width: 400px;
    margin: 0 auto;
}
</style>
