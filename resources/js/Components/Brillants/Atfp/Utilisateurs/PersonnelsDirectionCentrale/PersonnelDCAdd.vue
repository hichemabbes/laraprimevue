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
                <span class="font-bold text-2xl">Ajouter un Personnel DC</span>
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
                    <!-- Identification -->
                    <TabPanel header="Identification">
                        <div class="grid p-fluid mt-4">
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
                            </div>     <!-- Qualité -->
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
                            </div>     <!-- Matricule -->
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
                            </div>     <!-- CIN -->
                            <div class="col-12 md:col-3 field">
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
                            </div>     <!-- Date CIN -->
                            <div class="col-12 md:col-3 field">
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
                            </div>     <!-- Lieu CIN -->
                            <div class="col-12 md:col-3 field">
                                <label for="date_recrutement" class="block font-medium mb-2">
                                    Date de Recrutement
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
                            </div>     <!-- Date recrutement -->
                            <div class="col-12 md:col-6 field">
                            </div>     <!-- Espace vide -->

                            <!-- Champs conditionnels pour qualite_fr = 'Personnel (Externe)' -->
                            <div class="col-12 md:col-6 field">
                                <label for="etablissement_origine_fr" class="block font-medium mb-2">
                                    Établissement d'Origine
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
                            </div>     <!-- Établissement origine fr -->
                            <div class="col-12 md:col-6 field">
                                <label for="etablissement_origine_ar" class="block font-medium mb-2 text-right font-arabic">
                                    المؤسسة الأصلية
                                </label>
                                <InputText
                                    id="etablissement_origine_ar"
                                    v-model="form.etablissement_origine_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.etablissement_origine_ar?.length }"
                                    placeholder="أدخل المؤسسة الأصلية بالعربية"
                                    @input="validateEtablissementOrigineAr"
                                />
                                <small v-if="errors.etablissement_origine_ar?.length" class="p-error font-arabic text-right" dir="rtl">
                                    {{ errors.etablissement_origine_ar[0] }}
                                </small>
                            </div>     <!-- Établissement origine ar -->
                            <div class="col-12 md:col-6 field">
                                <label for="mission_fr" class="block font-medium mb-2">
                                    Mission
                                </label>
                                <Textarea
                                    id="mission_fr"
                                    v-model="form.mission_fr"
                                    rows="3"
                                    :class="{ 'p-invalid': errors.mission_fr?.length }"
                                    placeholder="Entrez la mission en français"
                                    @input="validateMissionFr"
                                />
                                <small v-if="errors.mission_fr?.length" class="p-error">
                                    {{ errors.mission_fr[0] }}
                                </small>
                            </div>     <!-- Mission fr -->
                            <div class="col-12 md:col-6 field">
                                <label for="mission_ar" class="block font-medium mb-2 text-right font-arabic">
                                    المهمة
                                </label>
                                <Textarea
                                    id="mission_ar"
                                    v-model="form.mission_ar"
                                    rows="3"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.mission_ar?.length }"
                                    placeholder="أدخل المهمة بالعربية"
                                    @input="validateMissionAr"
                                />
                                <small v-if="errors.mission_ar?.length" class="p-error font-arabic text-right" dir="rtl">
                                    {{ errors.mission_ar[0] }}
                                </small>
                            </div>     <!-- Mission ar -->
                        </div>
                    </TabPanel>
                    <!-- Informations Personnel -->
                    <TabPanel header="Informations Personnel">
                        <div class="grid p-fluid mt-4">
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
                            </div>     <!-- Prénom fr -->
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
                            </div>     <!-- Nom fr -->
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
                            </div>     <!-- Nationalité fr -->
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
                            </div>     <!-- Genre fr -->
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
                            </div>     <!-- État civil fr -->
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
                            </div>     <!-- Date naissance -->
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
                            </div>     <!-- Lieu naissance fr -->
                            <!-- Section arabe -->
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
                            </div>     <!-- Nationalité ar -->
                            <div class="col-12 md:col-4 field">
                                <label for="nom_ar" class="block font-medium mb-2 text-right font-arabic">
                                    اللقب <span class="text-red-500">*</span>
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
                            </div>     <!-- Nom ar -->
                            <div class="col-12 md:col-4 field">
                                <label for="prenom_ar" class="block font-medium mb-2 text-right font-arabic">
                                    الاسم <span class="text-red-500">*</span>
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
                            </div>     <!-- Prénom ar -->
                            <div class="col-12 md:col-6 field">
                            </div>
                            <div class="col-12 md:col-3 field">
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
                            </div>     <!-- Lieu CIN ar -->
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
                            </div>     <!-- Lieu naissance ar -->
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
                            <!-- Section arabe -->
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
                    <!-- Rôles -->
                    <TabPanel header="Rôles">
                        <div class="grid p-fluid mt-4">
                            <div class="col-12 field">
                                <label for="roles" class="block font-medium mb-2">
                                    Rôles <span class="text-red-500">*</span>
                                </label>
                                <MultiSelect
                                    id="roles"
                                    v-model="selectedRoles"
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
                            <div class="col-12">
                                <div v-for="roleId in selectedRoles" :key="roleId" class="field-radiobutton">
                                    <label :for="'est_officiel_' + roleId" class="block font-medium mb-2">
                                        Est officiel pour le rôle {{ getRoleName(roleId) }}
                                    </label>
                                    <RadioButton id="est_officiel_0" name="est_officiel" :value="0" v-model="form.roles.find(r => r.id === roleId).est_officiel" />
                                    <label for="est_officiel_0">0</label>
                                    <RadioButton id="est_officiel_1" name="est_officiel" :value="1" v-model="form.roles.find(r => r.id === roleId).est_officiel" />
                                    <label for="est_officiel_1">1</label>
                                </div>
                            </div>
                        </div>
                    </TabPanel>
                    <!-- Connexion -->
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
                    <!-- Observations -->
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
        <Toast position="top-right" group="tl" />
    </Dialog>
</template>

<script>
import { ref, computed, watch } from 'vue';
import { useToast } from 'primevue/usetoast';
import axios from '@/axios.js';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import MultiSelect from 'primevue/multiselect';
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
import RadioButton from 'primevue/radiobutton';

export default {
    components: {
        InputText,
        Dropdown,
        MultiSelect,
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
        RadioButton,
    },
    props: {
        visible: { type: Boolean, required: true },
    },
    emits: ['update:visible', 'save', 'close'],
    setup(props, { emit }) {
        const toast = useToast();
        const form = ref({
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
            roles: [], // Tableau d'objets {id, est_officiel}
            qualite_fr: '',
            qualite_ar: '',
            date_recrutement: null,
            etablissement_origine_fr: '',
            etablissement_origine_ar: '',
            mission_fr: '',
            mission_ar: '',
            type_user_fr: 'Personnel Direction Centrale',
            type_user_ar: 'عون الإدارة المركزية',
        });
        const selectedRoles = ref([]); // Pour MultiSelect (IDs seulement)
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
                form.value.qualite_fr;
            const noErrors = Object.values(errors.value).every(error => !error?.length);
            return !isSaving.value && requiredFieldsValid && noErrors;
        });
        const gouvernoratOptions = computed(() => lists.value['Gouvernorats'] || []);
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
        const qualiteOptions = ref([
            { label_fr: 'Personnel (ATFP)', value_fr: 'Personnel (ATFP)' },
            { label_fr: 'Personnel (Externe)', value_fr: 'Personnel (Externe)' },
        ]);
        const getRoleName = (roleId) => {
            const role = availableRoles.value.find(r => r.id === roleId);
            return role ? role.name : 'Inconnu';
        };
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
        function validateDateRecrutement() {
            errors.value.date_recrutement = [];
            if (form.value.date_recrutement) {
                const date = new Date(form.value.date_recrutement);
                if (isNaN(date.getTime())) {
                    errors.value.date_recrutement = ['La date de recrutement est invalide.'];
                } else if (date > new Date()) {
                    errors.value.date_recrutement = ['La date de recrutement ne peut pas être dans le futur.'];
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
                    const base64String = window.atob(form.value.photo.replace(/^data:image\/(png|jpeg|jpg|gif|bmp|webp);base64,/, ''));
                    const sizeInBytes = base64String.length;
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
        function validateQualiteFr() {
            errors.value.qualite_fr = [];
            if (!form.value.qualite_fr) {
                errors.value.qualite_fr = ['La qualité est requise.'];
            } else if (!['Personnel (ATFP)', 'Personnel (Externe)'].includes(form.value.qualite_fr)) {
                errors.value.qualite_fr = ['La qualité doit être "Personnel (ATFP)" ou "Personnel (Externe)".'];
            }
        }
        function validateEtablissementOrigineFr() {
            errors.value.etablissement_origine_fr = [];
            if (form.value.qualite_fr === 'Personnel (Externe)' && !form.value.etablissement_origine_fr) {
                errors.value.etablissement_origine_fr = ['L\'établissement d\'origine est requis pour le personnel externe.'];
            } else if (form.value.etablissement_origine_fr && form.value.etablissement_origine_fr.length > 255) {
                errors.value.etablissement_origine_fr = ['L\'établissement d\'origine ne doit pas dépasser 255 caractères.'];
            }
        }
        function validateEtablissementOrigineAr() {
            errors.value.etablissement_origine_ar = [];
            if (form.value.etablissement_origine_ar && form.value.etablissement_origine_ar.length > 255) {
                errors.value.etablissement_origine_ar = ['المؤسسة الأصلية بالعربية لا يجب أن تتجاوز 255 حرفًا.'];
            }
        }
        function validateMissionFr() {
            errors.value.mission_fr = [];
            if (form.value.mission_fr && form.value.mission_fr.length > 16777215) {
                errors.value.mission_fr = ['La mission en français ne doit pas dépasser 16777215 caractères.'];
            }
        }
        function validateMissionAr() {
            errors.value.mission_ar = [];
            if (form.value.mission_ar && form.value.mission_ar.length > 16777215) {
                errors.value.mission_ar = ['المهمة بالعربية لا يجب أن تتجاوز 16777215 حرفًا.'];
            }
        }
        const debouncedValidateMatricule = debounce(async () => {
            if (!form.value.matricule?.trim() || form.value.matricule.length > 20) return;
            try {
                const response = await axios.post('/api/personnels_dc/check-matricule', { matricule: form.value.matricule });
                errors.value.matricule = response.data.exists ? ['Ce matricule existe déjà.'] : [];
            } catch (error) {
                errors.value.matricule = ['Erreur lors de la validation du matricule.'];
                toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de la validation du matricule.', life: 5000 });
            }
        }, 500);
        const debouncedValidateCin = debounce(async () => {
            if (!form.value.cin?.trim() || !/^\d{8}$/.test(form.value.cin)) return;
            try {
                const response = await axios.post('/api/personnels_dc/check-cin', { cin: form.value.cin });
                errors.value.cin = response.data.exists ? ['Ce CIN existe déjà.'] : [];
            } catch (error) {
                errors.value.cin = ['Erreur lors de la validation du CIN.'];
                toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de la validation du CIN.', life: 5000 });
            }
        }, 500);
        const debouncedValidateEmail = debounce(async () => {
            if (!form.value.email?.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email) || form.value.email.length > 255) return;
            try {
                const response = await axios.post('/api/personnels_dc/check-email', { email: form.value.email });
                errors.value.email = response.data.exists ? ['Cet email existe déjà.'] : [];
            } catch (error) {
                errors.value.email = ['Erreur lors de la validation de l\'email.'];
                toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de la validation de l\'email.', life: 5000 });
            }
        }, 500);
        const onGouvernoratChange = () => {
            const selected = lists.value['Gouvernorats']?.find(g => g.nom_fr === form.value.gouvernorat_fr);
            form.value.gouvernorat_ar = selected?.nom_ar || '';
            validateGouvernoratFr();
            validateGouvernoratAr();
        };
        const onEtatCivilChange = () => {
            const map = { 'Célibataire': 'أعزب(اء)', 'Marié': 'متزوج(ة)', 'Divorcé': 'مطلق(ة)', 'Veuf': 'أرمل(ة)' };
            form.value.etat_civil_ar = map[form.value.etat_civil_fr] || '';
            validateEtatCivilFr();
            validateEtatCivilAr();
        };
        const onGenreChange = () => {
            const map = { 'Homme': 'ذكر', 'Femme': 'أنثى' };
            form.value.genre_ar = map[form.value.genre_fr] || '';
            validateGenreFr();
        };
        const onQualiteChange = () => {
            const map = { 'Personnel (ATFP)': 'عون الوكالة', 'Personnel (Externe)': 'عون من خارج الوكالة' };
            form.value.qualite_ar = map[form.value.qualite_fr] || '';
            validateQualiteFr();
            validateEtablissementOrigineFr();
            validateEtablissementOrigineAr();
        };
        const validateForm = async () => {
            errors.value = {};
            validateMatricule();
            validateCin();
            validateQualiteFr();
            validateEtablissementOrigineFr();
            validateEtablissementOrigineAr();
            validateMissionFr();
            validateMissionAr();
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
            validateDateRecrutement();
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
            validatePhoto();
        };
        const fetchRoles = async () => {
            loadingLists.value = true;
            try {
                const response = await axios.get('/api/roles');
                availableRoles.value = response.data.data || [];
                if (!availableRoles.value.length) toast.add({ severity: 'warn', summary: 'Avertissement', detail: 'Aucun rôle disponible.', life: 5000 });
            } catch (error) {
                toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de la récupération des rôles.', life: 5000 });
            } finally {
                loadingLists.value = false;
            }
        };
        const fetchLists = async () => {
            loadingLists.value = true;
            try {
                const response = await axios.get('/api/personnels_dc/lists');
                lists.value = response.data.data;
            } catch (error) {
                toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de la récupération des listes.', life: 5000 });
            } finally {
                loadingLists.value = false;
            }
        };
        const submitForm = async () => {
            isSaving.value = true;
            await validateForm();
            if (Object.keys(errors.value).filter(key => errors.value[key]?.length).length > 0) {
                // Logique pour naviguer à l'onglet avec erreur (adaptée sans les champs supprimés)
                toast.add({ severity: 'error', summary: 'Erreur de validation', detail: 'Veuillez vérifier les champs.', life: 3000 });
                isSaving.value = false;
                return;
            }
            try {
                const payload = { ...form.value };
                const response = await axios.post('/api/personnels_dc', payload);
                toast.add({ severity: 'success', summary: 'Succès', detail: 'Personnel DC créé.', life: 3000 });
                emit('save', response.data.data);
                close();
            } catch (error) {
                if (error.response?.status === 422) {
                    errors.value = error.response.data.errors;
                    toast.add({ severity: 'error', summary: 'Erreur de validation', detail: 'Veuillez vérifier les champs.', life: 3000 });
                } else {
                    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de l\'enregistrement.', life: 3000 });
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
                roles: [],
                qualite_fr: '',
                qualite_ar: '',
                date_recrutement: null,
                etablissement_origine_fr: '',
                etablissement_origine_ar: '',
                mission_fr: '',
                mission_ar: '',
                type_user_fr: 'Personnel Direction Centrale',
                type_user_ar: 'عون الإدارة المركزية',
            };
            selectedRoles.value = [];
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
        watch(selectedRoles, (newRoles) => {
            form.value.roles = newRoles.map(id => ({ id, est_officiel: 1 })); // Défaut 1
            validateRoles();
        });
        watch(() => props.visible, async (newVal) => {
            if (newVal) {
                resetForm();
                await Promise.all([fetchRoles(), fetchLists()]);
            }
        }, { immediate: true });
        return {
            form,
            selectedRoles,
            errors,
            isSaving,
            loading,
            loadingLists,
            availableRoles,
            isFormValid,
            gouvernoratOptions,
            etatCivilOptions,
            genreOptions,
            qualiteOptions,
            getRoleName,
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
            onGouvernoratChange,
            onEtatCivilChange,
            onGenreChange,
            onQualiteChange,
            validateNomFr,
            validatePrenomFr,
            validateNomAr,
            validatePrenomAr,
            validateLieuCinFr,
            validateLieuCinAr,
            validateDateCin,
            validateDateNaissance,
            validateDateRecrutement,
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
            validatePhoto,
            validateMatricule,
            validateCin,
            validateQualiteFr,
            validateEtablissementOrigineFr,
            validateEtablissementOrigineAr,
            validateMissionFr,
            validateMissionAr,
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
/* Styles existants inchangés */
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
/* Pour les textareas en arabe - inchangé */
:deep(.p-inputtext[dir='rtl']),
:deep(.p-textarea[dir='rtl']) {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
    text-align: right;
    direction: rtl;
}
.border-b-1 {
    border-bottom: 1px solid #4483c2; /* Light gray for a subtle, thin line */
}
</style>
