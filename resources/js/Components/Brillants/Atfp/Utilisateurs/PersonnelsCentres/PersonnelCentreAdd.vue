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
                <i class="pi pi-plus text-primary text-2xl"></i>
                <span class="font-bold text-2xl">Ajouter un Personnel</span>
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
                    <!-- NOUVEL ONGLET: Identification -->
                    <TabPanel header="Identification">
                        <div class="grid p-fluid mt-2">
                            <!-- Champ qualité_fr (non affiché) -->
                            <!-- Qualité -->
                            <div class="col-12 md:col-3 field">
                                <label for="qualite_fr" class="block font-medium mb-2">
                                    Qualité <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    id="qualite_fr"
                                    v-model="form.qualite_fr"
                                    :options="qualiteOptions"
                                    optionLabel="label_fr"
                                    optionValue="value_fr"
                                    placeholder="Sélectionner une qualité"
                                    :class="{ 'p-invalid': errors.qualite_fr?.length }"
                                    @change="onQualiteChange"
                                />
                                <small v-if="errors.qualite_fr?.length" class="p-error">
                                    {{ errors.qualite_fr[0] }}
                                </small>
                            </div>
                            <input type="hidden" v-model="form.qualite_fr">
                            <!-- État du personnel -->
                            <div class="col-12 md:col-3 field">
                                <label for="etat_personnel_fr" class="block font-medium mb-2">
                                    État du personnel <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    id="etat_personnel_fr"
                                    v-model="form.etat_personnel_fr"
                                    :options="etatPersonnelOptions"
                                    optionLabel="label_fr"
                                    optionValue="value_fr"
                                    placeholder="Sélectionner un état"
                                    :class="{ 'p-invalid': errors.etat_personnel_fr?.length }"
                                    @change="onEtatPersonnelChange"
                                />
                                <small v-if="errors.etat_personnel_fr?.length" class="p-error">
                                    {{ errors.etat_personnel_fr[0] }}
                                </small>
                            </div>
                            <!-- Champs déplacés depuis Informations Personnelles -->
                            <div class="col-12 md:col-3 field">
                                <label for="matricule" class="block font-medium mb-2">
                                    Matricule <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="matricule"
                                    v-model.trim="form.matricule"
                                    :class="{ 'p-invalid': errors.matricule?.length }"
                                    placeholder="Entrez le matricule"
                                    @input="validateMatricule(); debouncedValidateMatricule()"
                                    :autofocus="true"
                                />
                                <small v-if="errors.matricule?.length" class="p-error">
                                    {{ errors.matricule[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-3 field">
                                <label for="cin" class="block font-medium mb-2">
                                    Numéro CIN <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="cin"
                                    v-model.trim="form.cin"
                                    :class="{ 'p-invalid': errors.cin?.length }"
                                    placeholder="Entrez le CIN"
                                    @input="validateCin(); debouncedValidateCin()"
                                />
                                <small v-if="errors.cin?.length" class="p-error">
                                    {{ errors.cin[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-4 field">
                                <label for="date_cin" class="block font-medium mb-2">
                                    Date de délivrance CIN
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
                            <div class="col-12 md:col-4 field">
                                <label for="lieu_cin_fr" class="block font-medium mb-2">
                                    Lieu de la CIN
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
                            <div class="col-12 md:col-4 field">
                                <label for="lieu_cin_ar" class="block font-medium mb-2 text-right font-arabic">
                                    مكان إصدار ب ت و
                                </label>
                                <InputText
                                    id="lieu_cin_ar"
                                    v-model="form.lieu_cin_ar"
                                    dir="rtl"
                                    class="text-right"
                                    :class="{ 'p-invalid': errors.lieu_cin_ar?.length }"
                                    placeholder="أدخل مكان الإصدار بالعربية"
                                    @input="validateLieuCinAr"
                                />
                                <small v-if="errors.lieu_cin_ar?.length" class="p-error font-arabic error-text-arabic">
                                    {{ errors.lieu_cin_ar[0] }}
                                </small>
                            </div>
                            <!-- Champs établissement d'origine (conditionnels) -->
                            <div v-if="form.qualite_fr === 'Personnel (Externe)'" class="col-12 md:col-6 field">
                                <label for="etablissement_origine_fr" class="block font-medium mb-2">
                                    Établissement d'origine <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="etablissement_origine_fr"
                                    v-model="form.etablissement_origine_fr"
                                    :class="{ 'p-invalid': errors.etablissement_origine_fr?.length }"
                                    placeholder="Entrez l'établissement d'origine en français"
                                    @input="validateEtablissementOrigineFr"
                                />
                                <small v-if="errors.etablissement_origine_fr?.length" class="p-error">
                                    {{ errors.etablissement_origine_fr[0] }}
                                </small>
                            </div>
                            <div v-if="form.qualite_fr === 'Personnel (Externe)'" class="col-12 md:col-6 field">
                                <label for="etablissement_origine_ar" class="block font-medium mb-2 text-right font-arabic">
                                    المؤسسة الأصلية <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="etablissement_origine_ar"
                                    v-model="form.etablissement_origine_ar"
                                    dir="rtl"
                                    class="text-right"
                                    :class="{ 'p-invalid': errors.etablissement_origine_ar?.length }"
                                    placeholder="أدخل المؤسسة الأصلية بالعربية"
                                    @input="validateEtablissementOrigineAr"
                                />
                                <small v-if="errors.etablissement_origine_ar?.length" class="p-error font-arabic error-text-arabic">
                                    {{ errors.etablissement_origine_ar[0] }}
                                </small>
                            </div>
                            <!-- Champ Filière -->
                            <div class="col-12 md:col-6 field">
                                <label for="filiere_fr" class="block font-medium mb-2">
                                    Filière <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    id="filiere_fr"
                                    v-model="form.filiere_fr"
                                    :options="filiereOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Sélectionner une filière"
                                    :class="{ 'p-invalid': errors.filiere_fr?.length }"
                                    @change="onFiliereChange"
                                />
                                <small v-if="errors.filiere_fr?.length" class="p-error">
                                    {{ errors.filiere_fr[0] }}
                                </small>
                            </div>
                            <!-- Champ C/F (conditionnel) -->
                            <div v-if="form.filiere_fr === 'Agent de formation'" class="col-12 md:col-6 field">
                                <label for="for_cons_fr" class="block font-medium mb-2">
                                    C/F <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    id="for_cons_fr"
                                    v-model="form.for_cons_fr"
                                    :options="forConsOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Sélectionner un type"
                                    :class="{ 'p-invalid': errors.for_cons_fr?.length }"
                                    @change="onForConsChange"
                                />
                                <small v-if="errors.for_cons_fr?.length" class="p-error">
                                    {{ errors.for_cons_fr[0] }}
                                </small>
                            </div>
                        </div>
                    </TabPanel>
                    <!-- Onglet Informations Personnelles (modifié) -->
                    <TabPanel header="Informations Personnelles">
                        <div class="grid p-fluid mt-4">
                            <!-- Les champs Matricule, CIN, etc. ont été déplacés vers l'onglet Identification -->
                            <div class="col-12 md:col-4 field">
                                <label for="prenom_fr" class="block font-medium mb-2">
                                    Prénom <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="prenom_fr"
                                    v-model="form.prenom_fr"
                                    :class="{ 'p-invalid': errors.prenom_fr?.length }"
                                    placeholder="Entrez le prénom en français"
                                    @input="validatePrenomFr"
                                />
                                <small v-if="errors.prenom_fr?.length" class="p-error">
                                    {{ errors.prenom_fr[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-4 field">
                                <label for="nom_fr" class="block font-medium mb-2">
                                    Nom <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="nom_fr"
                                    v-model="form.nom_fr"
                                    :class="{ 'p-invalid': errors.nom_fr?.length }"
                                    placeholder="Entrez le nom en français"
                                    @input="validateNomFr"
                                />
                                <small v-if="errors.nom_fr?.length" class="p-error">
                                    {{ errors.nom_fr[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-4 field">
                                <label for="nationalite_fr" class="block font-medium mb-2">
                                    Nationalité
                                </label>
                                <InputText
                                    id="nationalite_fr"
                                    v-model="form.nationalite_fr"
                                    :class="{ 'p-invalid': errors.nationalite_fr?.length }"
                                    placeholder="Entrez la nationalité en français"
                                    @input="validateNationaliteFr"
                                />
                                <small v-if="errors.nationalite_fr?.length" class="p-error">
                                    {{ errors.nationalite_fr[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-3 field">
                                <label for="genre_fr" class="block font-medium mb-2">
                                    Genre <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    id="genre_fr"
                                    v-model="form.genre_fr"
                                    :options="genreOptions"
                                    optionLabel="label_fr"
                                    optionValue="value_fr"
                                    placeholder="Sélectionner un genre"
                                    :class="{ 'p-invalid': errors.genre_fr?.length }"
                                    @change="onGenreChange"
                                />
                                <small v-if="errors.genre_fr?.length" class="p-error">
                                    {{ errors.genre_fr[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-3 field">
                                <label for="etat_civil_fr" class="block font-medium mb-2">
                                    État Civil
                                </label>
                                <Dropdown
                                    id="etat_civil_fr"
                                    v-model="form.etat_civil_fr"
                                    :options="etatCivilOptions"
                                    optionLabel="label_fr"
                                    optionValue="value_fr"
                                    placeholder="Sélectionner l'état civil"
                                    :class="{ 'p-invalid': errors.etat_civil_fr?.length }"
                                    @change="onEtatCivilChange"
                                />
                                <small v-if="errors.etat_civil_fr?.length" class="p-error">
                                    {{ errors.etat_civil_fr[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-3 field">
                                <label for="date_naissance" class="block font-medium mb-2">
                                    Date de Naissance
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
                            <div class="col-12 md:col-3 field">
                                <label for="lieu_naissance_fr" class="block font-medium mb-2">
                                    Lieu de Naissance
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
                            <!-- Champs en arabe -->
                            <div class="col-12">
                                <h3 class="font-medium mb-2 text-right font-arabic border-b-1" dir="rtl"></h3>
                            </div>
                            <div class="col-12 md:col-4 field">
                                <label for="nationalite_ar" class="block font-medium mb-2 text-right font-arabic">
                                    الجنسية
                                </label>
                                <InputText
                                    id="nationalite_ar"
                                    v-model="form.nationalite_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.nationalite_ar?.length }"
                                    placeholder="أدخل الجنسية بالعربية"
                                    @input="validateNationaliteAr"
                                />
                                <small v-if="errors.nationalite_ar?.length" class="p-error font-arabic text-right" dir="rtl">
                                    {{ errors.nationalite_ar[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-4 field">
                                <label for="nom_ar" class="block font-medium mb-2 text-right font-arabic">
                                    اللقب  <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="nom_ar"
                                    v-model="form.nom_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.nom_ar?.length }"
                                    placeholder="أدخل اللقب بالعربية"
                                    @input="validateNomAr"
                                />
                                <small v-if="errors.nom_ar?.length" class="p-error font-arabic text-right" dir="rtl">
                                    {{ errors.nom_ar[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-4 field">
                                <label for="prenom_ar" class="block font-medium mb-2 text-right font-arabic">
                                    الاسم  <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="prenom_ar"
                                    v-model="form.prenom_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.prenom_ar?.length }"
                                    placeholder="أدخل الاسم بالعربية"
                                    @input="validatePrenomAr"
                                />
                                <small v-if="errors.prenom_ar?.length" class="p-error font-arabic text-right" dir="rtl">
                                    {{ errors.prenom_ar[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-9 field">
                            </div>
                            <div class="col-12 md:col-3 field">
                                <label for="lieu_naissance_ar" class="block font-medium mb-2 text-right font-arabic">
                                    مكان الولادة
                                </label>
                                <InputText
                                    id="lieu_naissance_ar"
                                    v-model="form.lieu_naissance_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.lieu_naissance_ar?.length }"
                                    placeholder="أدخل مكان الولادة بالعربية"
                                    @input="validateLieuNaissanceAr"
                                />
                                <small v-if="errors.lieu_naissance_ar?.length" class="p-error font-arabic text-right" dir="rtl">
                                    {{ errors.lieu_naissance_ar[0] }}
                                </small>
                            </div>
                        </div>
                    </TabPanel>
                    <!-- Onglet Contact -->
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
                            <div class="col-12 md:col-3 field">
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
                            <div class="col-12 md:col-3 field">
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
                            <div class="col-12 md:col-3 field">
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
                            <div class="col-12 md:col-3 field">
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
                            <!-- Champs en arabe -->
                            <div class="col-12">
                                <h3 class="font-medium mb-2 text-right font-arabic border-b-1" dir="rtl"></h3>
                            </div>
                            <div class="col-12 md:col-3 field">
                                <label for="delegation_ar" class="block font-medium mb-2 text-right font-arabic">
                                    المعتمدية
                                </label>
                                <InputText
                                    id="delegation_ar"
                                    v-model="form.delegation_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.delegation_ar?.length }"
                                    placeholder="أدخل المعتمدية بالعربية"
                                    @input="validateDelegationAr"
                                />
                                <small v-if="errors.delegation_ar?.length" class="p-error font-arabic text-right" dir="rtl">
                                    {{ errors.delegation_ar[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-9 field">
                                <label for="adresse_ar" class="block font-medium mb-2 text-right font-arabic">
                                    العنوان
                                </label>
                                <InputText
                                    id="adresse_ar"
                                    v-model="form.adresse_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.adresse_ar?.length }"
                                    placeholder="أدخل العنوان بالعربية"
                                    @input="validateAdresseAr"
                                />
                                <small v-if="errors.adresse_ar?.length" class="p-error font-arabic text-right" dir="rtl">
                                    {{ errors.adresse_ar[0] }}
                                </small>
                            </div>
                        </div>
                    </TabPanel>
                    <!-- Onglet Rôles -->
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
                                    :class="{ 'p-invalid': errors.roles?.length }"
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
                    <!-- Onglet Connexion -->
                    <TabPanel header="Connexion">
                        <div class="grid p-fluid mt-4">
                            <div class="col-12 md:col-4 field">
                                <label for="email" class="block font-medium mb-2">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="email"
                                    v-model="form.email"
                                    :class="{ 'p-invalid': errors.email?.length }"
                                    placeholder="Entrez l'adresse email"
                                    @input="debouncedValidateEmail"
                                />
                                <small v-if="errors.email?.length" class="p-error">
                                    {{ errors.email[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-4 field">
                                <label for="password" class="block font-medium mb-2">
                                    Mot de passe
                                </label>
                                <Password
                                    id="password"
                                    v-model="form.password"
                                    :class="{ 'p-invalid': errors.password?.length }"
                                    toggleMask
                                    :feedback="false"
                                    placeholder="Entrez le mot de passe"
                                    @input="validatePassword"
                                />
                                <small v-if="errors.password?.length" class="p-error">
                                    {{ errors.password[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-4 field">
                                <label for="password_confirmation" class="block font-medium mb-2">
                                    Confirmer mot de passe
                                </label>
                                <Password
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    :class="{ 'p-invalid': errors.password_confirmation?.length }"
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
                    <!-- Onglet Photo -->
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
                                        v-else
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
                                <div v-else-if="photoPreview" class="flex align-items-center gap-3">
                                    <img
                                        :src="photoPreview"
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
                                    <p class="text-500">Aucune image sélectionnée.</p>
                                </div>
                            </div>
                        </div>
                    </TabPanel>
                    <!-- Onglet Affectation -->
                    <TabPanel header="Affectation">
                        <div class="grid p-fluid mt-4">
                            <!-- Champ centre (conditionnel) -->
                            <div v-if="!isCentreRole" class="col-12 md:col-9 field">
                                <label for="centre_id" class="block font-medium mb-2">
                                    Centre <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    id="centre_id"
                                    v-model="form.centre_id"
                                    :options="centres"
                                    optionLabel="nom_fr"
                                    optionValue="id"
                                    placeholder="Sélectionner un centre"
                                    :class="{ 'p-invalid': errors.centre_id?.length }"
                                    :loading="loadingLists"
                                    @change="validateCentreId"
                                />
                                <small v-if="errors.centre_id?.length" class="p-error">
                                    {{ errors.centre_id[0] }}
                                </small>
                            </div>
                            <!-- Statut du centre -->
                            <div class="col-12 md:col-3 field">
                                <label for="centre_statut_fr" class="block font-medium mb-2">
                                    Statut du centre <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    id="centre_statut_fr"
                                    v-model="form.centre_statut_fr"
                                    :options="centreStatutOptions"
                                    optionLabel="label_fr"
                                    optionValue="value_fr"
                                    placeholder="Sélectionner un statut"
                                    :class="{ 'p-invalid': errors.centre_statut_fr?.length }"
                                    @change="onCentreStatutChange"
                                />
                                <small v-if="errors.centre_statut_fr?.length" class="p-error">
                                    {{ errors.centre_statut_fr[0] }}
                                </small>
                            </div>
                            <!-- Dates -->
                            <div class="col-12 md:col-3 field">
                                <label for="date_debut" class="block font-medium mb-2">
                                    Date de début <span class="text-red-500">*</span>
                                </label>
                                <Calendar
                                    id="date_debut"
                                    v-model="form.date_debut"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    :class="{ 'p-invalid': errors.date_debut?.length }"
                                    @update:modelValue="validateDateDebut"
                                />
                                <small v-if="errors.date_debut?.length" class="p-error">
                                    {{ errors.date_debut[0] }}
                                </small>
                            </div>
                            <div v-if="form.statut_personnel === 'Inactif'" class="col-12 md:col-3 field">
                                <label for="date_fin" class="block font-medium mb-2">
                                    Date de fin <span class="text-red-500">*</span>
                                </label>
                                <Calendar
                                    id="date_fin"
                                    v-model="form.date_fin"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    :class="{ 'p-invalid': errors.date_fin?.length }"
                                    @update:modelValue="validateDateFin"
                                />
                                <small v-if="errors.date_fin?.length" class="p-error">
                                    {{ errors.date_fin[0] }}
                                </small>
                            </div>
                            <!-- Statut du personnel (pour personnels_centres) -->
                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-2">
                                    Statut <span class="text-red-500">*</span>
                                </label>
                                <div class="flex align-items-center gap-4">
                                    <div class="flex align-items-center">
                                        <RadioButton
                                            id="statut_actif"
                                            v-model="form.statut_personnel"
                                            value="Actif"
                                            @change="onStatutPersonnelChange"
                                        />
                                        <label for="statut_actif" class="ml-2">Actif</label>
                                    </div>
                                    <div class="flex align-items-center">
                                        <RadioButton
                                            id="statut_inactif"
                                            v-model="form.statut_personnel"
                                            value="Inactif"
                                            @change="onStatutPersonnelChange"
                                        />
                                        <label for="statut_inactif" class="ml-2">Inactif</label>
                                    </div>
                                </div>
                                <small v-if="errors.statut_personnel?.length" class="p-error">
                                    {{ errors.statut_personnel[0] }}
                                </small>
                            </div>
                            <!-- Observation -->
                            <div class="col-6 field">
                                <label for="observation_fr_affectation" class="block font-medium mb-2">
                                    Observation
                                </label>
                                <Textarea
                                    id="observation_fr_affectation"
                                    v-model="form.observation_fr_affectation"
                                    rows="4"
                                    :class="{ 'p-invalid': errors.observation_fr_affectation?.length }"
                                    placeholder="Entrez l'observation en français"
                                    @input="validateObservationFrAffectation"
                                />
                                <small v-if="errors.observation_fr_affectation?.length" class="p-error">
                                    {{ errors.observation_fr_affectation[0] }}
                                </small>
                            </div>
                        </div>
                    </TabPanel>
                    <!-- Onglet Niveau & Spécialité -->
                    <TabPanel header="Niveau & Spécialité">
                        <div class="grid p-fluid mt-4">
                            <!-- Type de personnel -->
                            <!-- Niveau d'étude -->
                            <div class="col-12 md:col-9 field">
                                <label for="niveau_etude_fr" class="block font-medium mb-2">
                                    Niveau d'étude
                                </label>
                                <Dropdown
                                    id="niveau_etude_fr"
                                    v-model="form.niveau_etude_fr"
                                    :options="niveauEtudeOptions"
                                    optionLabel="nom_fr"
                                    optionValue="nom_fr"
                                    placeholder="Sélectionner un niveau"
                                    :class="{ 'p-invalid': errors.niveau_etude_fr?.length }"
                                    :loading="loadingLists"
                                    @change="onNiveauEtudeChange"
                                />
                                <small v-if="errors.niveau_etude_fr?.length" class="p-error">
                                    {{ errors.niveau_etude_fr[0] }}
                                </small>
                            </div>
                            <!-- Code du niveau -->
                            <div class="col-12 md:col-3 field">
                                <label for="code_niveau" class="block font-medium mb-2">
                                    Code du niveau <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    id="code_niveau"
                                    v-model="form.code_niveau"
                                    :options="codeNiveauOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Sélectionner un code"
                                    :class="{ 'p-invalid': errors.code_niveau?.length }"
                                    @change="validateCodeNiveau"
                                />
                                <small v-if="errors.code_niveau?.length" class="p-error">
                                    {{ errors.code_niveau[0] }}
                                </small>
                            </div>
                            <!-- Spécialité du diplôme -->
                            <div class="col-12 md:col-6 field">
                                <label for="specialite_diplome_fr" class="block font-medium mb-2">
                                    Spécialité du diplôme
                                </label>
                                <InputText
                                    id="specialite_diplome_fr"
                                    v-model="form.specialite_diplome_fr"
                                    :class="{ 'p-invalid': errors.specialite_diplome_fr?.length }"
                                    placeholder="Entrez la spécialité en français"
                                    @input="validateSpecialiteDiplomeFr"
                                />
                                <small v-if="errors.specialite_diplome_fr?.length" class="p-error">
                                    {{ errors.specialite_diplome_fr[0] }}
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="specialite_diplome_ar" class="block font-medium mb-2 text-right font-arabic">
                                    تخصص الشهادة
                                </label>
                                <InputText
                                    id="specialite_diplome_ar"
                                    v-model="form.specialite_diplome_ar"
                                    dir="rtl"
                                    class="text-right"
                                    :class="{ 'p-invalid': errors.specialite_diplome_ar?.length }"
                                    placeholder="أدخل التخصص بالعربية"
                                    @input="validateSpecialiteDiplomeAr"
                                />
                                <small v-if="errors.specialite_diplome_ar?.length" class="p-error font-arabic error-text-arabic">
                                    {{ errors.specialite_diplome_ar[0] }}
                                </small>
                            </div>
                            <!-- Observation -->
                            <div class="col-12 field">
                                <label for="observation_fr_personnel" class="block font-medium mb-2">
                                    Observation
                                </label>
                                <Textarea
                                    id="observation_fr_personnel"
                                    v-model="form.observation_fr_personnel"
                                    rows="4"
                                    :class="{ 'p-invalid': errors.observation_fr_personnel?.length }"
                                    placeholder="Entrez l'observation en français"
                                    @input="validateObservationFrPersonnel"
                                />
                                <small v-if="errors.observation_fr_personnel?.length" class="p-error">
                                    {{ errors.observation_fr_personnel[0] }}
                                </small>
                            </div>
                        </div>
                    </TabPanel>
                    <!-- Onglet Observations -->
                    <TabPanel header="Observations">
                        <div class="grid p-fluid mt-4">
                            <div class="col-12 field">
                                <label for="observation_fr" class="block font-medium mb-2">
                                    Observation
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
                                    الملاحظات
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
                                <small v-if="errors.observation_ar?.length" class="p-error font-arabic text-right" dir="rtl">
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
                    label="Enregistrer"
                    icon="pi pi-check"
                    class="p-button-primary"
                    :loading="isSaving"
                    :disabled="isSaving"
                    @click="submitForm"
                />
            </div>
        </template>
        <Toast position="top-right" />
    </Dialog>
</template>
<script>
import { ref, computed, watch, onMounted } from 'vue';
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
import RadioButton from 'primevue/radiobutton';
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
        RadioButton,
        Cropper,
    },
    props: {
        visible: { type: Boolean, required: true },
    },
    emits: ['update:visible', 'save', 'close'],
    setup(props, { emit }) {
        const toast = useToast();
        const form = ref({
            // Champs de la table users
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
            observation_fr: '',
            observation_ar: '',
            photo: null,
            email: '',
            password: '',
            password_confirmation: '',
            roles: [],
            ajouter_par: null,
            statut: 'Actif',
            // Champs de la table users_centres
            centre_id: null,
            qualite_fr: 'Personnel (ATFP)', // Valeur par défaut
            qualite_ar: 'عون الوكالة',
            etablissement_origine_fr: '',
            etablissement_origine_ar: '',
            centre_statut_fr: '',
            centre_statut_ar: '',
            date_debut: null,
            date_fin: null,
            statut_affectation: 'Actif',
            observation_fr_affectation: '',
            observation_ar_affectation: '',
            // Champs de la table personnels_centres
            etat_personnel_fr: '',
            etat_personnel_ar: '',
            niveau_etude_fr: '',
            niveau_etude_ar: '',
            specialite_diplome_fr: '',
            specialite_diplome_ar: '',
            code_niveau: '',
            statut_personnel: 'Actif',
            observation_fr_personnel: '',
            observation_ar_personnel: '',
            // Nouveaux champs pour l'onglet Identification
            filiere_fr: '',
            filiere_ar: '',
            for_cons_fr: '',
            for_cons_ar: 'مكون، مستشار تدريب',
        });
        const errors = ref({});
        const isSaving = ref(false);
        const loading = ref(false);
        const loadingLists = ref(false);
        const availableRoles = ref([]);
        const lists = ref({});
        const centres = ref([]);
        const niveauEtudeOptions = ref([]);
        const codeNiveauOptions = ref([
            { label: '1', value: '1' },
            { label: '2', value: '2' },
            { label: '3', value: '3' },
            { label: '4', value: '4' },
            { label: '5', value: '5' },
            { label: '6', value: '6' },
            { label: '7', value: '7' },
            { label: '8', value: '8' },
            { label: '9', value: '9' },
            { label: '10', value: '10' },
            { label: '11', value: '11' },
            { label: '12', value: '12' },
        ]);
        const showCropper = ref(false);
        const photoPreview = ref(null);
        const selectedFile = ref(null);
        const previewImage = ref(null);
        const croppedImage = ref(null);
        const imageError = ref(null);
        const fileUpload = ref(null);
        const tabview = ref(null);
        const activeTabIndex = ref(0);
        const isCentreRole = ref(false);
        const centreId = ref(null);
        // Options pour les listes déroulantes
        const genreOptions = ref([
            { label_fr: 'Homme', value_fr: 'Homme' },
            { label_fr: 'Femme', value_fr: 'Femme' },
        ]);
        const etatCivilOptions = ref([
            { label_fr: 'Célibataire', value_fr: 'Célibataire' },
            { label_fr: 'Marié', value_fr: 'Marié' },
            { label_fr: 'Divorcé', value_fr: 'Divorcé' },
            { label_fr: 'Veuf', value_fr: 'Veuf' },
        ]);
        const qualiteOptions = ref([
            { label_fr: 'Personnel (ATFP)', value_fr: 'Personnel (ATFP)' },
            { label_fr: 'Personnel (Externe)', value_fr: 'Personnel (Externe)' },
        ]);
        const centreStatutOptions = ref([
            { label_fr: 'Centre officiel', value_fr: 'Centre officiel' },
            { label_fr: 'Centre secondaire', value_fr: 'Centre secondaire' },
        ]);
        const etatPersonnelOptions = ref([
            { label_fr: 'Titulaire', value_fr: 'Titulaire' },
            { label_fr: 'Contractuel', value_fr: 'Contractuel' },
            { label_fr: 'Détaché', value_fr: 'Détaché' },
            { label_fr: 'Autre', value_fr: 'Autre' },
        ]);
        const statutOptions = ref([
            { label_fr: 'Actif', value_fr: 'Actif' },
            { label_fr: 'Inactif', value_fr: 'Inactif' },
        ]);
        // Nouvelles options pour les champs Filière et C/F
        const filiereOptions = ref([
            { label: 'Agent Administratif', value: 'Agent Administratif' },
            { label: 'Agent de formation', value: 'Agent de formation' },
            { label: 'Corps para-pédagogique', value: 'Corps para-pédagogique' },
            { label: 'Agent Technique', value: 'Agent Technique' },
            { label: 'Ouvrier', value: 'Ouvrier' },
        ]);
        const forConsOptions = ref([
            { label: 'Formateur', value: 'Formateur' },
            { label: 'Conseiller d\'apprentissage', value: 'Conseiller d\'apprentissage' },
        ]);
        const gouvernoratOptions = computed(() => lists.value['Gouvernorats'] || []);
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
                form.value.roles.length > 0 &&
                form.value.statut &&
                form.value.centre_id &&
                form.value.qualite_fr &&
                form.value.centre_statut_fr &&
                form.value.date_debut &&
                form.value.etat_personnel_fr &&
                form.value.code_niveau &&
                form.value.statut_personnel &&
                form.value.filiere_fr;
            const noErrors = Object.values(errors.value).every(error => !error?.length);
            return !isSaving.value && requiredFieldsValid && noErrors;
        });
        // Méthodes de validation
        function validateDateCin() {
            errors.value.date_cin = [];
            if (form.value.date_cin) {
                const date = new Date(form.value.date_cin);
                if (isNaN(date.getTime())) {
                    errors.value.date_cin = ['La date CIN est invalide.'];
                } else if (date > new Date()) {
                    errors.value.date_cin = ['La date CIN ne peut pas être dans le futur.'];
                }
            }
        }
        function validateDateNaissance() {
            errors.value.date_naissance = [];
            if (form.value.date_naissance) {
                const date = new Date(form.value.date_naissance);
                if (isNaN(date.getTime())) {
                    errors.value.date_naissance = ['La date de naissance est invalide.'];
                } else if (date > new Date()) {
                    errors.value.date_naissance = ['La date de naissance ne peut pas être dans le futur.'];
                }
            }
        }
        function validateGenreFr() {
            errors.value.genre_fr = form.value.genre_fr && !['Homme', 'Femme'].includes(form.value.genre_fr)
                ? ['Le genre doit être Homme ou Femme.']
                : form.value.genre_fr ? [] : ['Le genre est requis.'];
        }
        function isValidPhoto(photo) {
            if (!photo) return false;
            return photo.startsWith('data:image/');
        }
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
            photoPreview.value = null;
            errors.value.photo = [];
            toast.add({
                severity: 'info',
                summary: 'Recadrage annulé',
                detail: 'Recadrage de l\'image annulé.',
                life: 3000,
            });
        }
        function startCropping() {
            if (!photoPreview.value) {
                imageError.value = 'Aucune image sélectionnée.';
                toast.add({ severity: 'error', summary: 'Erreur', detail: imageError.value, life: 3000 });
                return;
            }
            previewImage.value = photoPreview.value;
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
            errors.value.password = [];
            errors.value.password_confirmation = [];
            if (form.value.password || form.value.password_confirmation) {
                if (form.value.password && form.value.password.length < 8) {
                    errors.value.password = ['Le mot de passe doit contenir au moins 8 caractères.'];
                }
                if (form.value.password !== form.value.password_confirmation) {
                    errors.value.password_confirmation = ['Les mots de passe ne correspondent pas.'];
                }
            }
        }
        function validateMatricule() {
            errors.value.matricule = [];
            if (!form.value.matricule?.trim()) {
                errors.value.matricule = ['Le matricule est requis.'];
            } else if (form.value.matricule.length > 20) {
                errors.value.matricule = ['Le matricule ne doit pas dépasser 20 caractères.'];
            }
        }
        function validateCin() {
            errors.value.cin = [];
            if (!form.value.cin?.trim()) {
                errors.value.cin = ['Le CIN est requis.'];
            } else if (!/^\d{8}$/.test(form.value.cin)) {
                errors.value.cin = ['Le CIN doit contenir exactement 8 chiffres.'];
            }
        }
        // Validations pour les nouveaux champs
        function validateCentreId() {
            errors.value.centre_id = form.value.centre_id ? [] : ['Le centre est requis.'];
        }
        function validateQualiteFr() {
            errors.value.qualite_fr = form.value.qualite_fr ? [] : ['La qualité est requise.'];
        }
        function validateEtablissementOrigineFr() {
            errors.value.etablissement_origine_fr = [];
            if (form.value.qualite_fr === 'Personnel (Externe)') {
                if (!form.value.etablissement_origine_fr?.trim()) {
                    errors.value.etablissement_origine_fr = ["L'établissement d'origine est requis."];
                } else if (form.value.etablissement_origine_fr.length > 255) {
                    errors.value.etablissement_origine_fr = ["L'établissement d'origine ne doit pas dépasser 255 caractères."];
                }
            }
        }
        function validateEtablissementOrigineAr() {
            errors.value.etablissement_origine_ar = [];
            if (form.value.qualite_fr === 'Personnel (Externe)') {
                if (!form.value.etablissement_origine_ar?.trim()) {
                    errors.value.etablissement_origine_ar = ['المؤسسة الأصلية مطلوبة.'];
                } else if (form.value.etablissement_origine_ar.length > 255) {
                    errors.value.etablissement_origine_ar = ['المؤسسة الأصلية لا يجب أن تتجاوز 255 حرفًا.'];
                }
            }
        }
        function validateCentreStatutFr() {
            errors.value.centre_statut_fr = form.value.centre_statut_fr ? [] : ['Le statut du centre est requis.'];
        }
        function validateDateDebut() {
            errors.value.date_debut = form.value.date_debut ? [] : ['La date de début est requise.'];
        }
        function validateDateFin() {
            errors.value.date_fin = [];
            if (form.value.statut_personnel === 'Inactif') {
                if (!form.value.date_fin) {
                    errors.value.date_fin = ['La date de fin est requise.'];
                } else if (form.value.date_debut && form.value.date_fin < form.value.date_debut) {
                    errors.value.date_fin = ['La date de fin doit être postérieure à la date de début.'];
                }
            }
        }
        function validateObservationFrAffectation() {
            errors.value.observation_fr_affectation = form.value.observation_fr_affectation &&
            form.value.observation_fr_affectation.length > 1000
                ? ['L\'observation ne doit pas dépasser 1000 caractères.']
                : [];
        }
        function validateEtatPersonnelFr() {
            errors.value.etat_personnel_fr = form.value.etat_personnel_fr ? [] : ['L\'état du personnel est requis.'];
        }
        function validateNiveauEtudeFr() {
            errors.value.niveau_etude_fr = []; // Rendu optionnel
        }
        function validateCodeNiveau() {
            errors.value.code_niveau = [];
            if (!form.value.code_niveau) {
                errors.value.code_niveau = ['Le code du niveau est requis.'];
            }
        }
        function validateSpecialiteDiplomeFr() {
            errors.value.specialite_diplome_fr = form.value.specialite_diplome_fr &&
            form.value.specialite_diplome_fr.length > 255
                ? ['La spécialité ne doit pas dépasser 255 caractères.']
                : [];
        }
        function validateSpecialiteDiplomeAr() {
            errors.value.specialite_diplome_ar = form.value.specialite_diplome_ar && form.value.specialite_diplome_ar.length > 255
                ? ['تخصص الشهادة بالعربية لا يجب أن يتجاوز 255 حرفًا.']
                : [];
        }
        function validateStatutPersonnel() {
            errors.value.statut_personnel = form.value.statut_personnel ? [] : ['Le statut est requis.'];
        }
        function validateObservationFrPersonnel() {
            errors.value.observation_fr_personnel = form.value.observation_fr_personnel &&
            form.value.observation_fr_personnel.length > 1000
                ? ['L\'observation ne doit pas dépasser 1000 caractères.']
                : [];
        }
        // Validations pour les nouveaux champs
        function validateFiliereFr() {
            errors.value.filiere_fr = form.value.filiere_fr ? [] : ['La filière est requise.'];
        }
        function validateForConsFr() {
            errors.value.for_cons_fr = [];
            if (form.value.filiere_fr === 'Agent de formation' && !form.value.for_cons_fr) {
                errors.value.for_cons_fr = ['Le champ C/F est requis pour cette filière.'];
            }
        }
        const debouncedValidateMatricule = debounce(async () => {
            if (!form.value.matricule?.trim() || form.value.matricule.length > 20) {
                return; // Skip API call if client-side validation fails
            }
            try {
                const response = await axios.post('/api/personnels_centres/check-matricule', {
                    matricule: form.value.matricule,
                }, {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });
                if (response.data.exists) {
                    errors.value.matricule = ['Ce matricule existe déjà.'];
                } else if (errors.value.matricule?.length && errors.value.matricule[0] === 'Erreur lors de la validation du matricule.') {
                    errors.value.matricule = [];
                }
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
            if (!form.value.cin?.trim() || !/^\d{8}$/.test(form.value.cin)) {
                return; // Skip API call if client-side validation fails
            }
            try {
                const response = await axios.post('/api/personnels_centres/check-cin', {
                    cin: form.value.cin,
                }, {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });
                if (response.data.exists) {
                    errors.value.cin = ['Ce CIN existe déjà.'];
                } else if (errors.value.cin?.length && errors.value.cin[0] === 'Erreur lors de la validation du CIN.') {
                    errors.value.cin = [];
                }
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
            try {
                const response = await axios.post('/api/personnels_centres/check-email', {
                    email: form.value.email,
                }, {
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
        // Méthodes pour gérer les changements
        const onGenreChange = () => {
            const genreMap = {
                'Homme': 'ذكر',
                'Femme': 'أنثى',
            };
            form.value.genre_ar = genreMap[form.value.genre_fr] || '';
            validateGenreFr();
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
        const onGouvernoratChange = () => {
            const selectedGouvernorat = lists.value['Gouvernorats']?.find(
                gouv => gouv.nom_fr === form.value.gouvernorat_fr
            );
            form.value.gouvernorat_ar = selectedGouvernorat?.nom_ar || '';
            validateGouvernoratFr();
            validateGouvernoratAr();
        };
        const onQualiteChange = () => {
            const qualiteMap = {
                'Personnel (ATFP)': 'عون الوكالة',
                'Personnel (Externe)': 'عون من خارج الوكالة',
            };
            form.value.qualite_ar = qualiteMap[form.value.qualite_fr] || '';
            validateQualiteFr();
        };
        const onCentreStatutChange = () => {
            const statutMap = {
                'Centre officiel': 'المركز الأصلي',
                'Centre secondaire': 'المركز الثانوي',
            };
            form.value.centre_statut_ar = statutMap[form.value.centre_statut_fr] || '';
            validateCentreStatutFr();
        };
        const onEtatPersonnelChange = () => {
            const etatMap = {
                'Titulaire': 'عون قار',
                'Contractuel': 'عون متعاقد',
                'Détaché': 'عون ملحق',
                'Autre': 'حالة أخرى',
            };
            form.value.etat_personnel_ar = etatMap[form.value.etat_personnel_fr] || '';
            validateEtatPersonnelFr();
        };
        const onNiveauEtudeChange = () => {
            const selectedNiveau = niveauEtudeOptions.value.find(
                niveau => niveau.nom_fr === form.value.niveau_etude_fr
            );
            form.value.niveau_etude_ar = selectedNiveau?.nom_ar || '';
            form.value.code_niveau = selectedNiveau?.code || '';
            validateNiveauEtudeFr();
        };
        const onStatutPersonnelChange = () => {
            const statutMap = {
                'Actif': 'نشط',
                'Inactif': 'غير نشط',
            };
            form.value.statut_personnel_ar = statutMap[form.value.statut_personnel] || '';
            validateStatutPersonnel();
            validateDateFin();
        };
        // Méthodes pour les nouveaux champs
        const onFiliereChange = () => {
            const filiereArMap = {
                'Agent Administratif': 'إداري',
                'Agent de formation': 'عون تكوين',
                'Corps para-pédagogique': 'إطار شبه بيداغوجي',
                'Agent Technique': 'فني',
                'Ouvrier': 'عامل(ة)',
            };
            form.value.filiere_ar = filiereArMap[form.value.filiere_fr] || '';
            // Réinitialiser le champ C/F si la filière change
            if (form.value.filiere_fr !== 'Agent de formation') {
                form.value.for_cons_fr = '';
            }
            validateFiliereFr();
        };
        const onForConsChange = () => {
            // Le champ for_cons_ar est toujours le même
            form.value.for_cons_ar = 'مكون، مستشار تدريب';
            validateForConsFr();
        };
        const validateForm = async () => {
            errors.value = {};
            validateMatricule();
            validateCin();
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
            validateObservationFr();
            validateObservationAr();
            validateRoles();
            validatePassword();
            validatePhoto();
            // Validations pour les nouveaux champs
            validateCentreId();
            validateQualiteFr();
            validateEtablissementOrigineFr();
            validateEtablissementOrigineAr();
            validateCentreStatutFr();
            validateDateDebut();
            validateDateFin();
            validateObservationFrAffectation();
            validateEtatPersonnelFr();
            validateNiveauEtudeFr();
            validateCodeNiveau();
            validateSpecialiteDiplomeFr();
            validateSpecialiteDiplomeAr(); // Ajouter cette ligne
            validateStatutPersonnel();
            validateObservationFrPersonnel();
            validateFiliereFr();
            validateForConsFr();
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
                // Récupérer les centres si l'utilisateur n'a pas isCentreRole
                if (!isCentreRole.value) {
                    const centresResponse = await axios.get('/api/centres', {
                        headers: {
                            Authorization: `Bearer ${localStorage.getItem('token')}`,
                        },
                    });
                    centres.value = centresResponse.data.data || [];
                }
                // Récupérer les niveaux d'étude
                const niveauxResponse = await axios.get('/api/personnels_centres/niveaux-etude', {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });
                niveauEtudeOptions.value = niveauxResponse.data.data || [];
                // Récupérer les listes existantes
                const response = await axios.get('/api/personnels_centres/lists', {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });
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
        const submitForm = async () => {
            isSaving.value = true;
            await validateForm();
            const errorFields = Object.keys(errors.value).filter(key => errors.value[key]?.length);
            if (errorFields.length > 0) {
                // Naviguer vers le premier onglet contenant une erreur
                if (errorFields.some(field => ['matricule', 'cin', 'date_cin', 'lieu_cin_fr', 'lieu_cin_ar', 'nom_fr', 'prenom_fr', 'nom_ar', 'prenom_ar', 'genre_fr', 'etat_civil_fr', 'date_naissance', 'lieu_naissance_fr', 'lieu_naissance_ar', 'nationalite_fr', 'nationalite_ar', 'adresse_ar', 'delegation_ar', 'filiere_fr', 'for_cons_fr'].includes(field))) {
                    activeTabIndex.value = 0; // Identification
                } else if (errorFields.some(field => ['adresse_fr', 'gouvernorat_fr', 'delegation_fr', 'telephone_1', 'telephone_2'].includes(field))) {
                    activeTabIndex.value = 1; // Contact
                } else if (errorFields.includes('roles')) {
                    activeTabIndex.value = 2; // Rôles
                } else if (errorFields.some(field => ['email', 'password', 'password_confirmation'].includes(field))) {
                    activeTabIndex.value = 3; // Connexion
                } else if (errorFields.includes('photo')) {
                    activeTabIndex.value = 4; // Photo
                } else if (errorFields.some(field => ['centre_id', 'qualite_fr', 'etablissement_origine_fr', 'centre_statut_fr', 'date_debut', 'date_fin', 'observation_fr_affectation'].includes(field))) {
                    activeTabIndex.value = 5; // Affectation
                } else if (errorFields.some(field => ['etat_personnel_fr', 'niveau_etude_fr', 'code_niveau', 'specialite_diplome_fr', 'statut_personnel', 'observation_fr_personnel'].includes(field))) {
                    activeTabIndex.value = 6; // Niveau & Spécialité
                } else if (errorFields.some(field => ['observation_fr', 'observation_ar'].includes(field))) {
                    activeTabIndex.value = 7; // Observations
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
                // Préparer les données pour l'API
                const payload = {
                    // Champs de la table users
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
                    date_recrutement: form.value.date_recrutement ? form.value.date_recrutement.toISOString().split('T')[0] : null,
                    date_fin_service: form.value.date_fin_service ? form.value.date_fin_service.toISOString().split('T')[0] : null,
                    genre_fr: form.value.genre_fr,
                    genre_ar: form.value.genre_ar,
                    statut: form.value.statut,
                    cause_inactivite_fr: form.value.statut === 'Inactif' ? form.value.cause_inactivite_fr : null,
                    cause_inactivite_ar: form.value.statut === 'Inactif' ? form.value.cause_inactivite_ar : null,
                    adresse_fr: form.value.adresse_fr,
                    adresse_ar: form.value.adresse_ar,
                    gouvernorat_fr: form.value.gouvernorat_fr,
                    gouvernorat_ar: form.value.gouvernorat_ar,
                    delegation_fr: form.value.delegation_fr,
                    delegation_ar: form.value.delegation_ar,
                    telephone_1: form.value.telephone_1,
                    telephone_2: form.value.telephone_2,
                    etat_civil_fr: form.value.etat_civil_fr,
                    etat_civil_ar: form.value.etat_civil_ar,
                    observation_fr: form.value.observation_fr,
                    observation_ar: form.value.observation_ar,
                    photo: form.value.photo,
                    email: form.value.email,
                    password: form.value.password,
                    password_confirmation: form.value.password_confirmation,
                    roles: form.value.roles.map(roleId => ({
                        id: roleId,
                        centre_id: isCentreRole.value ? centreId.value : form.value.centre_id
                    })),
                    // Champs de la table users_centres
                    centre_id: isCentreRole.value ? centreId.value : form.value.centre_id,
                    qualite_fr: form.value.qualite_fr,
                    qualite_ar: form.value.qualite_ar,
                    // Inclure conditionnellement les champs d'établissement d'origine
                    ...(form.value.qualite_fr === 'Personnel (Externe)' ? {
                        etablissement_origine_fr: form.value.etablissement_origine_fr,
                        etablissement_origine_ar: form.value.etablissement_origine_ar,
                    } : {}),
                    centre_statut_fr: form.value.centre_statut_fr,
                    centre_statut_ar: form.value.centre_statut_ar,
                    date_debut: form.value.date_debut ? form.value.date_debut.toISOString().split('T')[0] : null,
                    date_fin: form.value.date_fin ? form.value.date_fin.toISOString().split('T')[0] : null,
                    statut_affectation: form.value.statut_affectation,
                    observation_fr_affectation: form.value.observation_fr_affectation,
                    observation_ar_affectation: form.value.observation_ar_affectation,
                    // Champs de la table personnels_centres
                    etat_personnel_fr: form.value.etat_personnel_fr,
                    etat_personnel_ar: form.value.etat_personnel_ar,
                    niveau_etude_fr: form.value.niveau_etude_fr,
                    niveau_etude_ar: form.value.niveau_etude_ar,
                    specialite_diplome_fr: form.value.specialite_diplome_fr,
                    specialite_diplome_ar: form.value.specialite_diplome_ar,
                    code_niveau: form.value.code_niveau,
                    statut_personnel: form.value.statut_personnel,
                    observation_fr_personnel: form.value.observation_fr_personnel,
                    observation_ar_personnel: form.value.observation_ar_personnel,
                    // Nouveaux champs pour la table grades_personnels
                    filiere_fr: form.value.filiere_fr,
                    filiere_ar: form.value.filiere_ar,
                    for_cons_fr: form.value.for_cons_fr,
                    for_cons_ar: form.value.for_cons_ar,
                };
                const response = await axios.post('/api/personnels_centres', payload, {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });
                toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Personnel créé avec succès.',
                    life: 3000,
                });
                emit('save', response.data.data);
                close();
            } catch (error) {
                console.error('Submit form error:', error);
                if (error.response?.status === 422) {
                    errors.value = Object.entries(error.response.data.errors).reduce((acc, [key, value]) => {
                        acc[key] = Array.isArray(value) ? value : [value];
                        return acc;
                    }, {});
                    const errorFields = Object.keys(errors.value).filter(key => errors.value[key]?.length);
                    if (errorFields.some(field => ['matricule', 'cin', 'date_cin', 'lieu_cin_fr', 'lieu_cin_ar', 'nom_fr', 'prenom_fr', 'nom_ar', 'prenom_ar', 'genre_fr', 'etat_civil_fr', 'date_naissance', 'lieu_naissance_fr', 'lieu_naissance_ar', 'nationalite_fr', 'nationalite_ar', 'adresse_ar', 'delegation_ar', 'filiere_fr', 'for_cons_fr'].includes(field))) {
                        activeTabIndex.value = 0; // Identification
                    } else if (errorFields.some(field => ['adresse_fr', 'gouvernorat_fr', 'delegation_fr', 'telephone_1', 'telephone_2'].includes(field))) {
                        activeTabIndex.value = 1; // Contact
                    } else if (errorFields.includes('roles')) {
                        activeTabIndex.value = 2; // Rôles
                    } else if (errorFields.some(field => ['email', 'password', 'password_confirmation'].includes(field))) {
                        activeTabIndex.value = 3; // Connexion
                    } else if (errorFields.includes('photo')) {
                        activeTabIndex.value = 4; // Photo
                    } else if (errorFields.some(field => ['centre_id', 'qualite_fr', 'etablissement_origine_fr', 'centre_statut_fr', 'date_debut', 'date_fin', 'observation_fr_affectation'].includes(field))) {
                        activeTabIndex.value = 5; // Affectation
                    } else if (errorFields.some(field => ['etat_personnel_fr', 'niveau_etude_fr', 'code_niveau', 'specialite_diplome_fr', 'statut_personnel', 'observation_fr_personnel'].includes(field))) {
                        activeTabIndex.value = 6; // Niveau & Spécialité
                    } else if (errorFields.some(field => ['observation_fr', 'observation_ar'].includes(field))) {
                        activeTabIndex.value = 7; // Observations
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
                // Champs de la table users
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
                observation_fr: '',
                observation_ar: '',
                photo: null,
                email: '',
                password: '',
                password_confirmation: '',
                roles: [],
                ajouter_par: null,
                statut: 'Actif',
                // Champs de la table users_centres
                centre_id: null,
                qualite_fr: 'Personnel (ATFP)', // Valeur par défaut
                qualite_ar: 'عون الوكالة',
                etablissement_origine_fr: '',
                etablissement_origine_ar: '',
                centre_statut_fr: '',
                centre_statut_ar: '',
                date_debut: null,
                date_fin: null,
                statut_affectation: 'Actif',
                observation_fr_affectation: '',
                observation_ar_affectation: '',
                // Champs de la table personnels_centres
                etat_personnel_fr: '',
                etat_personnel_ar: '',
                niveau_etude_fr: '',
                niveau_etude_ar: '',
                specialite_diplome_fr: '',
                specialite_diplome_ar: '',
                code_niveau: '',
                statut_personnel: 'Actif',
                observation_fr_personnel: '',
                observation_ar_personnel: '',
                // Nouveaux champs pour l'onglet Identification
                filiere_fr: '',
                filiere_ar: '',
                for_cons_fr: '',
                for_cons_ar: 'مكون، مستشار تدريب',
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
        // Récupérer les informations de l'utilisateur depuis le localStorage
        onMounted(() => {
            const userData = localStorage.getItem('user_data');
            if (userData) {
                try {
                    const user = JSON.parse(userData);
                    isCentreRole.value = user.isCentreRole === 1;
                    centreId.value = user.centreId;
                } catch (e) {
                    console.error('Erreur lors du parsing des données utilisateur:', e);
                }
            }
        });
        watch(
            () => props.visible,
            async (newVal) => {
                if (newVal) {
                    resetForm();
                    await Promise.all([fetchRoles(), fetchLists()]);
                }
            },
            { immediate: true }
        );
        return {
            form,
            errors,
            isSaving,
            loading,
            loadingLists,
            availableRoles,
            isFormValid,
            gouvernoratOptions,
            genreOptions,
            etatCivilOptions,
            qualiteOptions,
            centreStatutOptions,
            etatPersonnelOptions,
            statutOptions,
            centres,
            niveauEtudeOptions,
            codeNiveauOptions,
            filiereOptions,
            forConsOptions,
            showCropper,
            photoPreview,
            selectedFile,
            previewImage,
            croppedImage,
            imageError,
            fileUpload,
            tabview,
            activeTabIndex,
            isCentreRole,
            centreId,
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
            isValidPhoto,
            onGenreChange,
            onEtatCivilChange,
            onGouvernoratChange,
            onQualiteChange,
            onCentreStatutChange,
            onEtatPersonnelChange,
            onNiveauEtudeChange,
            onStatutPersonnelChange,
            onFiliereChange,
            onForConsChange,
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
            validateGenreFr,
            validateAdresseFr,
            validateAdresseAr,
            validateGouvernoratFr,
            validateGouvernoratAr,
            validateDelegationFr,
            validateDelegationAr,
            validateTelephone1,
            validateTelephone2,
            validateObservationFr,
            validateObservationAr,
            validateRoles,
            validatePassword,
            validatePhoto,
            validateMatricule,
            validateCin,
            validateFiliereFr,
            validateForConsFr,
            debouncedValidateMatricule,
            debouncedValidateCin,
            debouncedValidateEmail,
            validateCentreId,
            validateQualiteFr,
            validateEtablissementOrigineFr,
            validateEtablissementOrigineAr,
            validateCentreStatutFr,
            validateDateDebut,
            validateDateFin,
            validateObservationFrAffectation,
            validateEtatPersonnelFr,
            validateNiveauEtudeFr,
            validateCodeNiveau,
            validateSpecialiteDiplomeFr,
            validateSpecialiteDiplomeAr,
            validateStatutPersonnel,
            validateObservationFrPersonnel,
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
.cropper-container {
    max-width: 400px;
    margin: 0 auto;
}
/* Style spécifique pour les messages d'erreur PrimeVue en arabe */
:deep(.p-error.font-arabic),
:deep(.p-error[dir='rtl']) {
    display: block;
    text-align: right;
    direction: rtl;
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
    width: 100%;
    margin-right: 0;
    padding-right: 0;
}
/* Pour les textareas en arabe */
:deep(.p-inputtext[dir='rtl']),
:deep(.p-textarea[dir='rtl']) {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
    text-align: right;
    direction: rtl;
}
.border-b-1 {
    border-bottom: 1px solid #4483c2;
}
</style>
