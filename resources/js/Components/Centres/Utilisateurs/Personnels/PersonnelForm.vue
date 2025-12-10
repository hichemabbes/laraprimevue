<template>
    <Dialog
        :visible="visible"
        @update:visible="handleVisibleUpdate"
        :modal="true"
        :focusOnShow="false"
        :style="{ width: '90vw', maxWidth: '1200px' }"
        :breakpoints="{ '960px': '98vw', '640px': '100vw' }"
        class="p-fluid"
        :pt="{
            root: 'border-round-xl shadow-5',
            mask: { style: 'backdrop-filter: blur(4px)' },
            header: { class: 'surface-50 border-bottom-1 surface-border p-4' },
            content: { class: 'surface-ground p-4' },
            footer: { class: 'surface-50 border-top-1 surface-border p-4' }
        }"
    >
        <!-- Header -->
        <template #header>
            <div class="flex align-items-center gap-2">
                <i class="pi pi-user text-primary text-2xl"></i>
                <span class="font-bold text-2xl">
                    {{ isEditMode ? 'Modifier le Personnel' : 'Ajouter un Personnel' }}
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
                <TabView ref="tabview" v-model:activeIndex="activeTab">
                    <!-- Identité -->
                    <TabPanel header="Identité">
                        <div class="grid p-fluid">
                            <div v-if="isEditMode" class="col-12 flex align-items-start gap-3 mb-2 user-data-container">
                                <div class="photo-container" style="width: 80px; height: 80px;">
                                    <img
                                        v-if="userInfo.photo || photoPreview"
                                        :src="userInfo.photo || photoPreview"
                                        alt="Photo du personnel"
                                        class="border-round shadow-2"
                                        style="width: 80px; height: 80px; object-fit: cover; border-radius: 50%"
                                        @error="userInfo.photo = '/default-avatar.png'"
                                    />
                                    <i
                                        v-else
                                        class="pi pi-user text-4xl text-400 flex align-items-center justify-content-center"
                                        style="width: 80px; height: 80px; border-radius: 50%; background: var(--surface-100);"
                                    ></i>
                                </div>
                                <div style="display: flex; flex-direction: column; justify-content: space-between; height: 80px;">
                                    <div class="font-bold">{{ userInfo.prenom_fr || '-' }} {{ userInfo.nom_fr || '-' }}</div>
                                    <div>
                                        <Tag
                                            :value="userInfo.role_name || 'Aucun rôle'"
                                            severity="info"
                                            style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"
                                        />
                                    </div>
                                    <div>
                                        <Tag
                                            :value="userInfo.centre_nom_fr || 'Aucun centre associé'"
                                            severity="success"
                                            style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="matricule" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Matricule
                                </label>
                                <InputText
                                    id="matricule"
                                    v-model="form.matricule"
                                    :class="{ 'p-invalid': errors.matricule }"
                                    placeholder="Entrez le matricule"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.matricule" class="p-error" style="font-size: 0.75rem">{{ errors.matricule }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="cin" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    CIN
                                </label>
                                <InputText
                                    id="cin"
                                    v-model="form.cin"
                                    :class="{ 'p-invalid': errors.cin }"
                                    placeholder="Entrez le CIN"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.cin" class="p-error" style="font-size: 0.75rem">{{ errors.cin }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="date_cin" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Date CIN
                                </label>
                                <Calendar
                                    id="date_cin"
                                    v-model="form.date_cin"
                                    :class="{ 'p-invalid': errors.date_cin }"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    placeholder="Sélectionner une date"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.date_cin" class="p-error" style="font-size: 0.75rem">{{ errors.date_cin }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="lieu_cin_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Lieu CIN (Français)
                                </label>
                                <InputText
                                    id="lieu_cin_fr"
                                    v-model="form.lieu_cin_fr"
                                    :class="{ 'p-invalid': errors.lieu_cin_fr }"
                                    placeholder="Entrez le lieu du CIN en français"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.lieu_cin_fr" class="p-error" style="font-size: 0.75rem">{{ errors.lieu_cin_fr }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="qualite_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Qualité (Français)
                                </label>
                                <Dropdown
                                    id="qualite_fr"
                                    v-model="form.qualite_fr"
                                    :options="qualiteOptions"
                                    optionLabel="nom_fr"
                                    optionValue="nom_fr"
                                    placeholder="Sélectionner une qualité"
                                    :class="{ 'p-invalid': errors.qualite_fr }"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.qualite_fr" class="p-error" style="font-size: 0.75rem">{{ errors.qualite_fr }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="role_id" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Rôle <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    id="role_id"
                                    v-model="form.role_id"
                                    :options="roles"
                                    optionLabel="name"
                                    optionValue="id"
                                    placeholder="Sélectionner un rôle"
                                    :class="{ 'p-invalid': errors.role_id }"
                                    :loading="loadingLists"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.role_id" class="p-error" style="font-size: 0.75rem">{{ errors.role_id }}</small>
                            </div>
                        </div>
                    </TabPanel>
                    <!-- Informations Personnelles -->
                    <TabPanel header="Informations Personnelles">
                        <div class="grid p-fluid">
                            <div class="col-12 md:col-6 field">
                                <label for="nom_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Nom (Français) <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="nom_fr"
                                    v-model="form.nom_fr"
                                    :class="{ 'p-invalid': errors.nom_fr }"
                                    placeholder="Entrez le nom en français"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.nom_fr" class="p-error" style="font-size: 0.75rem">{{ errors.nom_fr }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="prenom_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Prénom (Français) <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="prenom_fr"
                                    v-model="form.prenom_fr"
                                    :class="{ 'p-invalid': errors.prenom_fr }"
                                    placeholder="Entrez le prénom en français"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.prenom_fr" class="p-error" style="font-size: 0.75rem">{{ errors.prenom_fr }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="nom_ar" class="block font-medium mb-1 font-arabic" style="font-size: 0.875rem">
                                    الاسم (العربية)
                                </label>
                                <InputText
                                    id="nom_ar"
                                    v-model="form.nom_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.nom_ar }"
                                    placeholder="أدخل الاسم بالعربية"
                                    style="font-size: 0.875rem"
                                    class="font-arabic"
                                />
                                <small v-if="errors.nom_ar" class="p-error font-arabic" style="font-size: 0.75rem">{{ errors.nom_ar }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="prenom_ar" class="block font-medium mb-1 font-arabic" style="font-size: 0.875rem">
                                    الاسم الأول (العربية)
                                </label>
                                <InputText
                                    id="prenom_ar"
                                    v-model="form.prenom_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.prenom_ar }"
                                    placeholder="أدخل الاسم الأول بالعربية"
                                    style="font-size: 0.875rem"
                                    class="font-arabic"
                                />
                                <small v-if="errors.prenom_ar" class="p-error font-arabic" style="font-size: 0.75rem">{{ errors.prenom_ar }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="genre_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Genre (Français)
                                </label>
                                <Dropdown
                                    id="genre_fr"
                                    v-model="form.genre_fr"
                                    :options="genreOptions"
                                    optionLabel="nom_fr"
                                    optionValue="nom_fr"
                                    placeholder="Sélectionner un genre"
                                    :class="{ 'p-invalid': errors.genre_fr }"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.genre_fr" class="p-error" style="font-size: 0.75rem">{{ errors.genre_fr }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="nationalite_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Nationalité (Français)
                                </label>
                                <Dropdown
                                    id="nationalite_fr"
                                    v-model="form.nationalite_fr"
                                    :options="nationaliteOptions"
                                    optionLabel="nom_fr"
                                    optionValue="nom_fr"
                                    placeholder="Sélectionner une nationalité"
                                    :class="{ 'p-invalid': errors.nationalite_fr }"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.nationalite_fr" class="p-error" style="font-size: 0.75rem">{{ errors.nationalite_fr }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="nationalite_ar" class="block font-medium mb-1 font-arabic" style="font-size: 0.875rem">
                                    الجنسية (العربية)
                                </label>
                                <Dropdown
                                    id="nationalite_ar"
                                    v-model="form.nationalite_ar"
                                    :options="nationaliteOptions"
                                    optionLabel="nom_ar"
                                    optionValue="nom_ar"
                                    placeholder="اختر الجنسية"
                                    :class="{ 'p-invalid': errors.nationalite_ar }"
                                    style="font-size: 0.875rem"
                                    class="font-arabic"
                                    dir="rtl"
                                />
                                <small v-if="errors.nationalite_ar" class="p-error font-arabic" style="font-size: 0.75rem">{{ errors.nationalite_ar }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="lieu_cin_ar" class="block font-medium mb-1 font-arabic" style="font-size: 0.875rem">
                                    مكان إصدار بطاقة التعريف (العربية)
                                </label>
                                <InputText
                                    id="lieu_cin_ar"
                                    v-model="form.lieu_cin_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.lieu_cin_ar }"
                                    placeholder="أدخل مكان إصدار بطاقة التعريف بالعربية"
                                    style="font-size: 0.875rem"
                                    class="font-arabic"
                                />
                                <small v-if="errors.lieu_cin_ar" class="p-error font-arabic" style="font-size: 0.75rem">{{ errors.lieu_cin_ar }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="date_naissance" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Date de naissance
                                </label>
                                <Calendar
                                    id="date_naissance"
                                    v-model="form.date_naissance"
                                    :class="{ 'p-invalid': errors.date_naissance }"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    placeholder="Sélectionner une date"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.date_naissance" class="p-error" style="font-size: 0.75rem">{{ errors.date_naissance }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="lieu_naissance_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Lieu de naissance (Français)
                                </label>
                                <InputText
                                    id="lieu_naissance_fr"
                                    v-model="form.lieu_naissance_fr"
                                    :class="{ 'p-invalid': errors.lieu_naissance_fr }"
                                    placeholder="Entrez le lieu de naissance en français"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.lieu_naissance_fr" class="p-error" style="font-size: 0.75rem">{{ errors.lieu_naissance_fr }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="lieu_naissance_ar" class="block font-medium mb-1 font-arabic" style="font-size: 0.875rem">
                                    مكان الولادة (العربية)
                                </label>
                                <InputText
                                    id="lieu_naissance_ar"
                                    v-model="form.lieu_naissance_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.lieu_naissance_ar }"
                                    placeholder="أدخل مكان الولادة بالعربية"
                                    style="font-size: 0.875rem"
                                    class="font-arabic"
                                />
                                <small v-if="errors.lieu_naissance_ar" class="p-error font-arabic" style="font-size: 0.75rem">{{ errors.lieu_naissance_ar }}</small>
                            </div>
                        </div>
                    </TabPanel>
                    <!-- Niveau d'étude -->
                    <TabPanel header="Niveau d'étude">
                        <div class="grid p-fluid">
                            <div class="col-12 md:col-6 field">
                                <label for="niveau_etude_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Niveau d'étude
                                </label>
                                <Dropdown
                                    id="niveau_etude_fr"
                                    v-model="form.niveau_etude_fr"
                                    :options="niveauxEtudes"
                                    optionLabel="nom_fr"
                                    optionValue="nom_fr"
                                    placeholder="Sélectionner un niveau d'étude"
                                    :class="{ 'p-invalid': errors.niveau_etude_fr }"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.niveau_etude_fr" class="p-error" style="font-size: 0.75rem">{{ errors.niveau_etude_fr }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="specialite_diplome_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Spécialité du diplôme (Français)
                                </label>
                                <InputText
                                    id="specialite_diplome_fr"
                                    v-model="form.specialite_diplome_fr"
                                    :class="{ 'p-invalid': errors.specialite_diplome_fr }"
                                    placeholder="Entrez la spécialité du diplôme"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.specialite_diplome_fr" class="p-error" style="font-size: 0.75rem">{{ errors.specialite_diplome_fr }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="specialite_diplome_ar" class="block font-medium mb-1 font-arabic" style="font-size: 0.875rem">
                                    تخصص الدبلوم (العربية)
                                </label>
                                <InputText
                                    id="specialite_diplome_ar"
                                    v-model="form.specialite_diplome_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.specialite_diplome_ar }"
                                    placeholder="أدخل تخصص الدبلوم"
                                    style="font-size: 0.875rem"
                                    class="font-arabic"
                                />
                                <small v-if="errors.specialite_diplome_ar" class="p-error font-arabic" style="font-size: 0.75rem">{{ errors.specialite_diplome_ar }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="code_niveau" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Code Niveau
                                </label>
                                <Dropdown
                                    id="code_niveau"
                                    v-model="form.code_niveau"
                                    :options="codeNiveauOptions"
                                    optionLabel="nom_fr"
                                    optionValue="nom_fr"
                                    placeholder="Sélectionner un code niveau"
                                    :class="{ 'p-invalid': errors.code_niveau }"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.code_niveau" class="p-error" style="font-size: 0.75rem">{{ errors.code_niveau }}</small>
                            </div>
                        </div>
                    </TabPanel>
                    <!-- Affectation -->
                    <TabPanel header="Affectation">
                        <div class="grid p-fluid">
                            <div v-if="!isCentreRole" class="col-12 md:col-6 field">
                                <label for="centre_id" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Centre <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    id="centre_id"
                                    v-model="form.centre_id"
                                    :options="centres"
                                    optionLabel="nom_fr"
                                    optionValue="id"
                                    placeholder="Sélectionner un centre"
                                    :class="{ 'p-invalid': errors.centre_id }"
                                    :loading="loadingLists"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.centre_id" class="p-error" style="font-size: 0.75rem">{{ errors.centre_id }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="type_affectation_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Type d'affectation
                                </label>
                                <Dropdown
                                    id="type_affectation_fr"
                                    v-model="form.type_affectation_fr"
                                    :options="typeAffectationOptions"
                                    optionLabel="nom_fr"
                                    optionValue="nom_fr"
                                    placeholder="Sélectionner un type d'affectation"
                                    :class="{ 'p-invalid': errors.type_affectation_fr }"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.type_affectation_fr" class="p-error" style="font-size: 0.75rem">{{ errors.type_affectation_fr }}</small>
                            </div>
                            <div v-if="showEtablissementFields" class="col-12 md:col-6 field">
                                <label for="etablissement_origine_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Établissement d'origine (Français)
                                </label>
                                <InputText
                                    id="etablissement_origine_fr"
                                    v-model="form.etablissement_origine_fr"
                                    :class="{ 'p-invalid': errors.etablissement_origine_fr }"
                                    placeholder="Entrez l'établissement d'origine en français"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.etablissement_origine_fr" class="p-error" style="font-size: 0.75rem">{{ errors.etablissement_origine_fr }}</small>
                            </div>
                            <div v-if="showEtablissementFields" class="col-12 md:col-6 field">
                                <label for="etablissement_origine_ar" class="block font-medium mb-1 font-arabic" style="font-size: 0.875rem">
                                    المؤسسة الأصلية (العربية)
                                </label>
                                <InputText
                                    id="etablissement_origine_ar"
                                    v-model="form.etablissement_origine_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.etablissement_origine_ar }"
                                    placeholder="أدخل المؤسسة الأصلية بالعربية"
                                    style="font-size: 0.875rem"
                                    class="font-arabic"
                                />
                                <small v-if="errors.etablissement_origine_ar" class="p-error font-arabic" style="font-size: 0.75rem">{{ errors.etablissement_origine_ar }}</small>
                            </div>
                            <div v-if="showEtablissementFields" class="col-12 md:col-6 field">
                                <label for="mission_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Mission (Français)
                                </label>
                                <InputText
                                    id="mission_fr"
                                    v-model="form.mission_fr"
                                    :class="{ 'p-invalid': errors.mission_fr }"
                                    placeholder="Entrez la mission en français"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.mission_fr" class="p-error" style="font-size: 0.75rem">{{ errors.mission_fr }}</small>
                            </div>
                            <div v-if="showEtablissementFields" class="col-12 md:col-6 field">
                                <label for="mission_ar" class="block font-medium mb-1 font-arabic" style="font-size: 0.875rem">
                                    المهمة (العربية)
                                </label>
                                <InputText
                                    id="mission_ar"
                                    v-model="form.mission_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.mission_ar }"
                                    placeholder="أدخل المهمة بالعربية"
                                    style="font-size: 0.875rem"
                                    class="font-arabic"
                                />
                                <small v-if="errors.mission_ar" class="p-error font-arabic" style="font-size: 0.75rem">{{ errors.mission_ar }}</small>
                            </div>
                            <div v-if="showCentreOrigineFields" class="col-12 md:col-6 field">
                                <label for="centre_origine_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Centre d'origine (Français)
                                </label>
                                <Dropdown
                                    id="centre_origine_fr"
                                    v-model="form.centre_origine_fr"
                                    :options="centres"
                                    optionLabel="nom_fr"
                                    optionValue="nom_fr"
                                    placeholder="Sélectionner un centre d'origine"
                                    :class="{ 'p-invalid': errors.centre_origine_fr }"
                                    :loading="loadingLists"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.centre_origine_fr" class="p-error" style="font-size: 0.75rem">{{ errors.centre_origine_fr }}</small>
                            </div>
                            <div v-if="showCentreOrigineFields" class="col-12 md:col-6 field">
                                <label for="centre_origine_ar" class="block font-medium mb-1 font-arabic" style="font-size: 0.875rem">
                                    مركز الأصل (العربية)
                                </label>
                                <Dropdown
                                    id="centre_origine_ar"
                                    v-model="form.centre_origine_ar"
                                    :options="centres"
                                    optionLabel="nom_ar"
                                    optionValue="nom_ar"
                                    placeholder="اختر مركز الأصل"
                                    :class="{ 'p-invalid': errors.centre_origine_ar }"
                                    :loading="loadingLists"
                                    style="font-size: 0.875rem"
                                    class="font-arabic"
                                    dir="rtl"
                                />
                                <small v-if="errors.centre_origine_ar" class="p-error font-arabic" style="font-size: 0.75rem">{{ errors.centre_origine_ar }}</small>
                            </div>
                            <div v-if="showEtablissementFields" class="col-12 field">
                                <label for="observation_fr_users_centres" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Observations (Affectation)
                                </label>
                                <Textarea
                                    id="observation_fr_users_centres"
                                    v-model="form.observation_fr_users_centres"
                                    rows="5"
                                    :class="{ 'p-invalid': errors.observation_fr_users_centres }"
                                    placeholder="Entrez vos observations (Affectation)"
                                    style="width: 100%; font-size: 0.875rem"
                                />
                                <small v-if="errors.observation_fr_users_centres" class="p-error" style="font-size: 0.75rem">{{ errors.observation_fr_users_centres }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="date_debut" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Date de début
                                </label>
                                <Calendar
                                    id="date_debut"
                                    v-model="form.date_debut"
                                    :class="{ 'p-invalid': errors.date_debut }"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    placeholder="Sélectionner une date"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.date_debut" class="p-error" style="font-size: 0.75rem">{{ errors.date_debut }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="date_fin" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Date de fin
                                </label>
                                <Calendar
                                    id="date_fin"
                                    v-model="form.date_fin"
                                    :class="{ 'p-invalid': errors.date_fin }"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    placeholder="Sélectionner une date"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.date_fin" class="p-error" style="font-size: 0.75rem">{{ errors.date_fin }}</small>
                            </div>
                        </div>
                    </TabPanel>
                    <!-- Dates -->
                    <TabPanel header="Dates">
                        <div class="grid p-fluid">
                            <div class="col-12 md:col-6 field">
                                <label for="date_recrutement" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Date de recrutement
                                </label>
                                <Calendar
                                    id="date_recrutement"
                                    v-model="form.date_recrutement"
                                    :class="{ 'p-invalid': errors.date_recrutement }"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    placeholder="Sélectionner une date"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.date_recrutement" class="p-error" style="font-size: 0.75rem">{{ errors.date_recrutement }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="date_fin_service" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Date de fin de service
                                </label>
                                <Calendar
                                    id="date_fin_service"
                                    v-model="form.date_fin_service"
                                    :class="{ 'p-invalid': errors.date_fin_service }"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    placeholder="Sélectionner une date"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.date_fin_service" class="p-error" style="font-size: 0.75rem">{{ errors.date_fin_service }}</small>
                            </div>
                        </div>
                    </TabPanel>
                    <!-- Contact -->
                    <TabPanel header="Contact">
                        <div class="grid p-fluid">
                            <div class="col-12 md:col-6 field">
                                <label for="telephone_1" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Téléphone 1
                                </label>
                                <InputText
                                    id="telephone_1"
                                    v-model="form.telephone_1"
                                    :class="{ 'p-invalid': errors.telephone_1 }"
                                    placeholder="Entrez le numéro de téléphone"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.telephone_1" class="p-error" style="font-size: 0.75rem">{{ errors.telephone_1 }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="telephone_2" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Téléphone 2
                                </label>
                                <InputText
                                    id="telephone_2"
                                    v-model="form.telephone_2"
                                    :class="{ 'p-invalid': errors.telephone_2 }"
                                    placeholder="Entrez le numéro de téléphone"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.telephone_2" class="p-error" style="font-size: 0.75rem">{{ errors.telephone_2 }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="adresse_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Adresse (Français)
                                </label>
                                <InputText
                                    id="adresse_fr"
                                    v-model="form.adresse_fr"
                                    :class="{ 'p-invalid': errors.adresse_fr }"
                                    placeholder="Entrez l'adresse en français"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.adresse_fr" class="p-error" style="font-size: 0.75rem">{{ errors.adresse_fr }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="adresse_ar" class="block font-medium mb-1 font-arabic" style="font-size: 0.875rem">
                                    العنوان (العربية)
                                </label>
                                <InputText
                                    id="adresse_ar"
                                    v-model="form.adresse_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.adresse_ar }"
                                    placeholder="أدخل العنوان بالعربية"
                                    style="font-size: 0.875rem"
                                    class="font-arabic"
                                />
                                <small v-if="errors.adresse_ar" class="p-error font-arabic" style="font-size: 0.75rem">{{ errors.adresse_ar }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="gouvernorat_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Gouvernorat
                                </label>
                                <Dropdown
                                    id="gouvernorat_fr"
                                    v-model="form.gouvernorat_fr"
                                    :options="gouvernorats"
                                    optionLabel="nom_fr"
                                    optionValue="nom_fr"
                                    placeholder="Sélectionner un gouvernorat"
                                    :class="{ 'p-invalid': errors.gouvernorat_fr }"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.gouvernorat_fr" class="p-error" style="font-size: 0.75rem">{{ errors.gouvernorat_fr }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="delegation_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Délégation (Français)
                                </label>
                                <InputText
                                    id="delegation_fr"
                                    v-model="form.delegation_fr"
                                    :class="{ 'p-invalid': errors.delegation_fr }"
                                    placeholder="Entrez la délégation en français"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.delegation_fr" class="p-error" style="font-size: 0.75rem">{{ errors.delegation_fr }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="delegation_ar" class="block font-medium mb-1 font-arabic" style="font-size: 0.875rem">
                                    المندوبية (العربية)
                                </label>
                                <InputText
                                    id="delegation_ar"
                                    v-model="form.delegation_ar"
                                    dir="rtl"
                                    :class="{ 'p-invalid': errors.delegation_ar }"
                                    placeholder="أدخل المندوبية بالعربية"
                                    style="font-size: 0.875rem"
                                    class="font-arabic"
                                />
                                <small v-if="errors.delegation_ar" class="p-error font-arabic" style="font-size: 0.75rem">{{ errors.delegation_ar }}</small>
                            </div>
                        </div>
                    </TabPanel>
                    <!-- Connexion -->
                    <TabPanel header="Connexion">
                        <div class="grid p-fluid">
                            <div class="col-12 md:col-6 field">
                                <label for="email" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="email"
                                    v-model="form.email"
                                    :class="{ 'p-invalid': errors.email }"
                                    placeholder="Entrez l'adresse email"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.email" class="p-error" style="font-size: 0.75rem">{{ errors.email }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="password" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    {{ isEditMode ? 'Nouveau mot de passe' : 'Mot de passe' }} <span v-if="!isEditMode" class="text-red-500">*</span>
                                </label>
                                <Password
                                    id="password"
                                    v-model="form.password"
                                    :class="{ 'p-invalid': errors.password }"
                                    toggleMask
                                    placeholder="Entrez le mot de passe"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.password" class="p-error" style="font-size: 0.75rem">{{ errors.password }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="password_confirmation" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Confirmer le mot de passe <span v-if="!isEditMode" class="text-red-500">*</span>
                                </label>
                                <Password
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    :class="{ 'p-invalid': errors.password_confirmation }"
                                    toggleMask
                                    placeholder="Confirmez le mot de passe"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.password_confirmation" class="p-error" style="font-size: 0.75rem">{{ errors.password_confirmation }}</small>
                            </div>
                        </div>
                    </TabPanel>
                    <!-- Photo -->
                    <TabPanel header="Photo">
                        <div class="grid p-fluid">
                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-1" style="font-size: 0.875rem">Télécharger une photo</label>
                                <FileUpload
                                    ref="fileUploadRef"
                                    name="photo"
                                    accept="image/png,image/jpeg"
                                    :maxFileSize="2000000"
                                    :customUpload="true"
                                    chooseLabel="Choisir"
                                    :showUploadButton="false"
                                    cancelLabel="Annuler"
                                    @select="handleFileSelect"
                                    @clear="handleClear"
                                    :class="{ 'p-invalid': errors.photo }"
                                    style="font-size: 0.875rem"
                                >
                                    <template #empty>
                                        <p>
                                            Glissez-déposez une image ici ou cliquez pour sélectionner (PNG, JPEG, max 2Mo).
                                        </p>
                                    </template>
                                </FileUpload>
                                <small v-if="errors.photo" class="p-error" style="font-size: 0.75rem">{{ errors.photo }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label class="block font-medium mb-1" style="font-size: 0.875rem">Aperçu et Recadrage</label>
                                <div v-if="showCropper" class="cropper-container">
                                    <Cropper
                                        :src="previewImage"
                                        :canvas="{ minWidth: 100, minHeight: 100 }"
                                        @change="onCropChange"
                                    />
                                    <div class="cropper-buttons flex gap-2 mt-3">
                                        <Button
                                            label="Recadrer"
                                            icon="pi pi-check"
                                            class="p-button-success p-button-sm"
                                            @click="confirmCrop"
                                            style="font-size: 0.875rem"
                                        />
                                        <Button
                                            label="Annuler"
                                            class="p-button-secondary p-button-sm"
                                            @click="cancelCrop"
                                            style="font-size: 0.875rem"
                                        />
                                    </div>
                                </div>
                                <div v-else-if="photoPreview || form.photo" class="flex align-items-center gap-3">
                                    <img
                                        :src="photoPreview || form.photo"
                                        alt="Aperçu de la photo"
                                        class="border-round shadow-3"
                                        style="max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 50%"
                                    />
                                    <div class="flex flex-column gap-2">
                                        <Button
                                            label="Supprimer"
                                            icon="pi pi-trash"
                                            class="p-button-danger p-button-outlined p-button-sm"
                                            @click="deletePhoto"
                                            style="font-size: 0.875rem"
                                        />
                                        <Button
                                            label="Recadrer"
                                            icon="pi pi-crop"
                                            class="p-button-success p-button-outlined p-button-sm"
                                            @click="startCrop"
                                            style="font-size: 0.875rem"
                                        />
                                    </div>
                                </div>
                                <div v-else class="flex flex-column align-items-center gap-2">
                                    <i class="pi pi-image text-5xl text-surface-300"></i>
                                    <p class="text-500">Aucune photo sélectionnée.</p>
                                </div>
                            </div>
                        </div>
                    </TabPanel>
                    <!-- Observations -->
                    <TabPanel header="Observations">
                        <div class="grid p-fluid">
                            <div class="col-12 field">
                                <label for="observation_personnels" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Observations (Personnel)
                                </label>
                                <Textarea
                                    id="observation_personnels"
                                    v-model="form.observation_personnels"
                                    rows="5"
                                    :class="{ 'p-invalid': errors.observation_personnels }"
                                    placeholder="Entrez vos observations (Personnel)"
                                    style="width: 100%; font-size: 0.875rem"
                                />
                                <small v-if="errors.observation_personnels" class="p-error" style="font-size: 0.75rem">{{ errors.observation_personnels }}</small>
                            </div>
                        </div>
                    </TabPanel>
                </TabView>
            </form>
        </div>
        <!-- Footer -->
        <template #footer>
            <div class="flex justify-content-end gap-3">
                <Button
                    label="Annuler"
                    icon="pi pi-times-circle"
                    class="p-button-outlined p-button-secondary p-button-sm"
                    @click="handleCancel"
                    style="font-size: 0.875rem"
                />
                <Button
                    :label="isEditMode ? 'Mettre à jour' : 'Enregistrer'"
                    icon="pi pi-check-circle"
                    class="p-button-primary p-button-sm"
                    @click="submitForm"
                    :loading="submitting"
                    style="font-size: 0.875rem"
                />
            </div>
        </template>
    </Dialog>
    <Toast position="top-right" />
</template>

<script>
import axios from 'axios';
import Button from 'primevue/button';
import Calendar from 'primevue/calendar';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import ProgressSpinner from 'primevue/progressspinner';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Textarea from 'primevue/textarea';
import FileUpload from 'primevue/fileupload';
import Toast from 'primevue/toast';
import Tag from 'primevue/tag';
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';
import { useToast } from 'primevue/usetoast';

export default {
    name: 'PersonnelCentreForm',
    components: {
        Button,
        Calendar,
        Dialog,
        Dropdown,
        InputText,
        Password,
        ProgressSpinner,
        TabView,
        TabPanel,
        Textarea,
        FileUpload,
        Cropper,
        Toast,
        Tag
    },
    props: {
        visible: { type: Boolean, default: false },
        personnelToEdit: { type: Object, default: null },
        isCentreRole: { type: Boolean, default: false },
        centreId: { type: [Number, String], default: null }
    },
    emits: ['update:visible', 'save', 'close'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            form: {
                id: null,
                user_id: null,
                nom_fr: '',
                prenom_fr: '',
                nom_ar: '',
                prenom_ar: '',
                matricule: '',
                cin: '',
                date_cin: null,
                lieu_cin_fr: '',
                lieu_cin_ar: '',
                date_naissance: null,
                lieu_naissance_fr: '',
                lieu_naissance_ar: '',
                nationalite_fr: 'Tunisienne',
                nationalite_ar: 'تونسية',
                genre_fr: 'Homme',
                qualite_fr: 'Personnel (ATFP)',
                qualite_ar: '',
                etablissement_origine_fr: '',
                etablissement_origine_ar: '',
                mission_fr: '',
                mission_ar: '',
                email: '',
                password: '',
                password_confirmation: '',
                centre_id: this.isCentreRole ? localStorage.getItem('centreId') : null,
                role_id: null,
                type_affectation_fr: 'Permanent',
                type_affectation_ar: '',
                centre_origine_fr: '',
                centre_origine_ar: '',
                observation_fr_users_centres: '',
                date_debut: null,
                date_fin: null,
                niveau_etude_fr: null,
                niveau_etude_ar: '',
                specialite_diplome_fr: '',
                specialite_diplome_ar: '',
                code_niveau: null,
                date_recrutement: null,
                date_fin_service: null,
                adresse_fr: '',
                adresse_ar: '',
                gouvernorat_fr: null,
                gouvernorat_ar: '',
                delegation_fr: '',
                delegation_ar: '',
                telephone_1: '',
                telephone_2: '',
                statut: 'Actif',
                observation_personnels: '',
                photo: null,
                etat_civil_fr: '',
                etat_civil_ar: '',
                nombre_enfants: null
            },
            userInfo: {
                nom_fr: '',
                prenom_fr: '',
                nom_ar: '',
                prenom_ar: '',
                photo: null,
                role_name: '',
                centre_nom_fr: '',
                centre_id: null,
                is_super_admin: false
            },
            originalForm: {},
            errors: {},
            loading: false,
            loadingLists: false,
            submitting: false,
            gouvernorats: [],
            niveauxEtudes: [],
            centres: [],
            roles: [],
            codeNiveauOptions: [],
            genreOptions: [
                { nom_fr: 'Homme' },
                { nom_fr: 'Femme' }
            ],
            nationaliteOptions: [],
            qualiteOptions: [
                { nom_fr: 'Personnel (ATFP)' },
                { nom_fr: 'Personnel (Externe)' }
            ],
            typeAffectationOptions: [
                { nom_fr: 'Permanent' },
                { nom_fr: 'Contractuel' },
                { nom_fr: "D'autre Centre" },
                { nom_fr: "D'autre Etablissement" }
            ],
            activeTab: 0,
            photoPreview: null,
            previewImage: null,
            showCropper: false,
            fileUploadRef: null
        };
    },
    computed: {
        isEditMode() {
            return !!this.personnelToEdit?.id;
        },
        showEtablissementFields() {
            return this.form.qualite_fr === 'Personnel (Externe)' && this.form.type_affectation_fr === "D'autre Etablissement";
        },
        showCentreOrigineFields() {
            return this.form.qualite_fr === 'Personnel (ATFP)' && this.form.type_affectation_fr === "D'autre Centre";
        }
    },
    watch: {
        visible(newVisible) {
            if (newVisible) {
                this.initializeForm();
            } else {
                this.resetForm();
            }
        },
        personnelToEdit: {
            handler(newPersonnel) {
                if (this.visible && newPersonnel?.id) {
                    this.initializeForm();
                }
            },
            deep: true
        },
        'form.centre_origine_fr'(newCentreFr) {
            if (newCentreFr) {
                const selectedCentre = this.centres.find(centre => centre.nom_fr === newCentreFr);
                this.form.centre_origine_ar = selectedCentre ? selectedCentre.nom_ar : '';
            } else {
                this.form.centre_origine_ar = '';
            }
        },
        'form.centre_origine_ar'(newCentreAr) {
            if (newCentreAr) {
                const selectedCentre = this.centres.find(centre => centre.nom_ar === newCentreAr);
                this.form.centre_origine_fr = selectedCentre ? selectedCentre.nom_fr : '';
            } else {
                this.form.centre_origine_fr = '';
            }
        },
        'form.matricule': {
            async handler(newMatricule) {
                if (newMatricule && (this.isEditMode ? newMatricule !== this.originalForm.matricule : true)) {
                    try {
                        const payload = {
                            matricule: newMatricule,
                            id: this.isEditMode ? this.form.user_id : null
                        };
                        const response = await axios.post('/api/personnels-centres/check-matricule', payload, {
                            headers: this.getHeaders()
                        });
                        this.errors.matricule = response.data.exists ? 'Ce matricule est déjà utilisé.' : null;
                    } catch (error) {
                        this.handleApiError(error, 'Erreur lors de la vérification du matricule.');
                    }
                } else {
                    this.errors.matricule = null;
                }
            },
            immediate: false
        }
    },
    methods: {
        getHeaders() {
            return {
                Authorization: `Bearer ${localStorage.getItem('token')}`
            };
        },
        async fetchFormData() {
            this.loadingLists = true;
            try {
                const response = await axios.get('/api/personnels-centres/form-data', {
                    headers: this.getHeaders()
                });
                this.gouvernorats = response.data.gouvernorats || [];
                this.niveauxEtudes = response.data.niveauxEtudes || [];
                this.centres = response.data.centres || [];
                this.roles = response.data.roles || [];
                this.codeNiveauOptions = (response.data.code_niveau_options || []).map(opt => ({ nom_fr: opt }));
                this.nationaliteOptions = response.data.nationalites || [];
                if (!this.centres.length) {
                    this.showWarn('Aucun centre disponible. Veuillez vérifier la connexion au serveur.');
                }
                if (!this.roles.length) {
                    this.showWarn('Aucun rôle disponible. Veuillez vérifier la connexion au serveur.');
                }
            } catch (error) {
                this.handleApiError(error, 'Impossible de charger les données du formulaire.');
            } finally {
                this.loadingLists = false;
            }
        },
        async initializeForm() {
            this.activeTab = 0;
            await this.fetchFormData();

            if (this.isEditMode && this.personnelToEdit?.id) {
                this.loading = true;
                try {
                    const response = await axios.get(`/api/personnels-centres/${this.personnelToEdit.id}`, {
                        headers: this.getHeaders(),
                        params: {
                            isCentreRole: this.isCentreRole ? 1 : 0,
                            centreId: this.centreId
                        }
                    });

                    const data = response.data.data;
                    if (!data) {
                        throw new Error('Données du personnel non trouvées');
                    }

                    this.form = {
                        id: data.id || null,
                        user_id: data.user_id || null,
                        nom_fr: data.nom_fr || '',
                        prenom_fr: data.prenom_fr || '',
                        nom_ar: data.nom_ar || '',
                        prenom_ar: data.prenom_ar || '',
                        matricule: data.matricule || '',
                        cin: data.cin || '',
                        date_cin: data.date_cin && !isNaN(new Date(data.date_cin)) ? new Date(data.date_cin) : null,
                        lieu_cin_fr: data.lieu_cin_fr || '',
                        lieu_cin_ar: data.lieu_cin_ar || '',
                        date_naissance: data.date_naissance && !isNaN(new Date(data.date_naissance)) ? new Date(data.date_naissance) : null,
                        lieu_naissance_fr: data.lieu_naissance_fr || '',
                        lieu_naissance_ar: data.lieu_naissance_ar || '',
                        nationalite_fr: data.nationalite_fr || 'Tunisienne',
                        nationalite_ar: data.nationalite_ar || 'تونسية',
                        genre_fr: data.genre_fr || 'Homme',
                        qualite_fr: data.qualite_fr || 'Personnel (ATFP)',
                        qualite_ar: data.qualite_ar || '',
                        etablissement_origine_fr: data.etablissement_origine_fr || '',
                        etablissement_origine_ar: data.etablissement_origine_ar || '',
                        mission_fr: data.mission_fr || '',
                        mission_ar: data.mission_ar || '',
                        email: data.email || '',
                        password: '',
                        password_confirmation: '',
                        centre_id: this.isCentreRole ? localStorage.getItem('centreId') : (data.centre_id || null),
                        role_id: data.role_id || null,
                        type_affectation_fr: data.type_affectation_fr || 'Permanent',
                        type_affectation_ar: data.type_affectation_ar || '',
                        centre_origine_fr: data.centre_origine_fr || '',
                        centre_origine_ar: data.centre_origine_ar || '',
                        observation_fr_users_centres: data.observation_fr_users_centres || '',
                        date_debut: data.date_debut && !isNaN(new Date(data.date_debut)) ? new Date(data.date_debut) : null,
                        date_fin: data.date_fin && !isNaN(new Date(data.date_fin)) ? new Date(data.date_fin) : null,
                        niveau_etude_fr: data.niveau_etude_fr || null,
                        niveau_etude_ar: data.niveau_etude_ar || '',
                        specialite_diplome_fr: data.specialite_diplome_fr || '',
                        specialite_diplome_ar: data.specialite_diplome_ar || '',
                        code_niveau: data.code_niveau || null,
                        date_recrutement: data.date_recrutement && !isNaN(new Date(data.date_recrutement)) ? new Date(data.date_recrutement) : null,
                        date_fin_service: data.date_fin_service && !isNaN(new Date(data.date_fin_service)) ? new Date(data.date_fin_service) : null,
                        adresse_fr: data.adresse_fr || '',
                        adresse_ar: data.adresse_ar || '',
                        gouvernorat_fr: data.gouvernorat_fr || null,
                        gouvernorat_ar: data.gouvernorat_ar || '',
                        delegation_fr: data.delegation_fr || '',
                        delegation_ar: data.delegation_ar || '',
                        telephone_1: data.telephone_1 || '',
                        telephone_2: data.telephone_2 || '',
                        statut: data.statut || 'Actif',
                        observation_personnels: data.observation_personnels || '',
                        photo: data.photo || null,
                        etat_civil_fr: data.etat_civil_fr || '',
                        etat_civil_ar: data.etat_civil_ar || '',
                        nombre_enfants: data.nombre_enfants || null
                    };

                    this.userInfo = {
                        nom_fr: data.nom_fr || '',
                        prenom_fr: data.prenom_fr || '',
                        nom_ar: data.nom_ar || '',
                        prenom_ar: data.prenom_ar || '',
                        photo: data.photo || null,
                        role_name: this.roles.find(role => role.id === data.role_id)?.name || 'Aucun rôle',
                        centre_nom_fr: this.centres.find(centre => centre.id === data.centre_id)?.nom_fr || 'Aucun centre associé',
                        centre_id: data.centre_id || null,
                        is_super_admin: data.is_super_admin || false
                    };

                    this.originalForm = { ...this.form };
                    this.photoPreview = data.photo || null;

                    if (!this.userInfo.centre_id && !this.userInfo.is_super_admin) {
                        this.showInfo('Aucun centre associé à ce personnel. Cela peut être normal pour certains utilisateurs (ex. SuperAdmin).');
                    }
                } catch (error) {
                    this.handleApiError(error, 'Impossible de charger les données du personnel.');
                } finally {
                    this.loading = false;

                    this.$nextTick(() => {
                        const nomInput = document.getElementById('nom_fr');
                        if (nomInput && !this.isEditMode && !document.activeElement) {
                            nomInput.focus();
                        }
                    });
                }
            } else {
                this.resetForm();

                this.$nextTick(() => {
                    const nomInput = document.getElementById('nom_fr');
                    if (nomInput && !this.isEditMode && !document.activeElement) {
                        nomInput.focus();
                    }
                });
            }
        },
        resetForm() {
            this.form = {
                id: null,
                user_id: null,
                nom_fr: '',
                prenom_fr: '',
                nom_ar: '',
                prenom_ar: '',
                matricule: '',
                cin: '',
                date_cin: null,
                lieu_cin_fr: '',
                lieu_cin_ar: '',
                date_naissance: null,
                lieu_naissance_fr: '',
                lieu_naissance_ar: '',
                nationalite_fr: 'Tunisienne',
                nationalite_ar: 'تونسية',
                genre_fr: 'Homme',
                qualite_fr: 'Personnel (ATFP)',
                qualite_ar: '',
                etablissement_origine_fr: '',
                etablissement_origine_ar: '',
                mission_fr: '',
                mission_ar: '',
                email: '',
                password: '',
                password_confirmation: '',
                centre_id: this.isCentreRole ? localStorage.getItem('centreId') : null,
                role_id: null,
                type_affectation_fr: 'Permanent',
                type_affectation_ar: '',
                centre_origine_fr: '',
                centre_origine_ar: '',
                observation_fr_users_centres: '',
                date_debut: null,
                date_fin: null,
                niveau_etude_fr: null,
                niveau_etude_ar: '',
                specialite_diplome_fr: '',
                specialite_diplome_ar: '',
                code_niveau: null,
                date_recrutement: null,
                date_fin_service: null,
                adresse_fr: '',
                adresse_ar: '',
                gouvernorat_fr: null,
                gouvernorat_ar: '',
                delegation_fr: '',
                delegation_ar: '',
                telephone_1: '',
                telephone_2: '',
                statut: 'Actif',
                observation_personnels: '',
                photo: null,
                etat_civil_fr: '',
                etat_civil_ar: '',
                nombre_enfants: null
            };
            this.userInfo = {
                nom_fr: '',
                prenom_fr: '',
                nom_ar: '',
                prenom_ar: '',
                photo: null,
                role_name: '',
                centre_nom_fr: '',
                centre_id: null,
                is_super_admin: false
            };
            this.originalForm = {};
            this.errors = {};
            this.activeTab = 0;
            this.photoPreview = null;
            this.previewImage = null;
            this.showCropper = false;
            if (this.fileUploadRef) this.fileUploadRef.clear();
        },
        handleFileSelect(event) {
            if (!event.files || !event.files[0]) return;
            const file = event.files[0];
            if (!['image/jpeg', 'image/png'].includes(file.type)) {
                this.errors.photo = 'Seuls les formats JPEG et PNG sont acceptés';
                this.showError('Format de fichier non supporté.');
                return;
            }
            if (file.size > 2000000) {
                this.errors.photo = 'La photo doit être inférieure à 2 Mo';
                this.showError('La taille de la photo dépasse 2 Mo.');
                return;
            }
            const reader = new FileReader();
            reader.onload = () => {
                const base64 = reader.result;
                const base64Size = (base64.length * 3 / 4) / 1024 / 1024;
                if (base64Size > 2) {
                    this.errors.photo = 'La photo encodée dépasse 2 Mo';
                    this.showError('La taille de la photo encodée dépasse 2 Mo.');
                    return;
                }
                this.previewImage = base64;
                this.showCropper = true;
                this.errors.photo = null;
            };
            reader.readAsDataURL(file);
        },
        handleClear() {
            this.previewImage = null;
            this.form.photo = null;
            this.photoPreview = null;
            this.showCropper = false;
            this.errors.photo = null;
        },
        onCropChange({ canvas }) {
            if (!canvas) return;
            const base64 = canvas.toDataURL('image/png');
            const base64String = base64.split(',')[1];
            const base64Size = (base64String.length * 3 / 4) / 1024 / 1024;
            if (base64Size > 2) {
                this.errors.photo = 'La photo recadrée doit être inférieure à 2 Mo';
                this.showWarn('La photo recadrée dépasse 2 Mo.');
                return;
            }
            this.form.photo = base64;
            this.photoPreview = base64;
            this.errors.photo = null;
        },
        confirmCrop() {
            if (!this.form.photo) {
                this.errors.photo = 'Aucune image à recadrer';
                this.showWarn('Veuillez sélectionner une image à recadrer.');
                return;
            }
            this.showCropper = false;
            if (this.fileUploadRef) this.fileUploadRef.clear();
        },
        cancelCrop() {
            this.previewImage = null;
            this.showCropper = false;
            if (this.fileUploadRef) this.fileUploadRef.clear();
        },
        deletePhoto() {
            this.form.photo = null;
            this.photoPreview = null;
            this.showCropper = false;
            if (this.fileUploadRef) this.fileUploadRef.clear();
            this.errors.photo = null;
        },
        startCrop() {
            if (this.form.photo) {
                this.previewImage = this.form.photo;
                this.showCropper = true;
            }
        },
        handleVisibleUpdate(value) {
            this.$emit('update:visible', value);
            if (!value) {
                this.resetForm();
                this.$emit('close');
            }
        },
        handleCancel() {
            this.resetForm();
            this.$emit('update:visible', false);
            this.$emit('close');
        },
        validateForm() {
            this.errors = {};
            let isValid = true;
            if (!this.form.nom_fr) {
                this.errors.nom_fr = 'Le nom en français est requis';
                isValid = false;
            }
            if (!this.form.prenom_fr) {
                this.errors.prenom_fr = 'Le prénom en français est requis';
                isValid = false;
            }
            if (!this.form.email) {
                this.errors.email = "L'email est requis";
                isValid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
                this.errors.email = "L'email n'est pas valide";
                isValid = false;
            }
            if (!this.isEditMode) {
                if (!this.form.password) {
                    this.errors.password = 'Le mot de passe est requis';
                    isValid = false;
                } else if (this.form.password.length < 8) {
                    this.errors.password = 'Le mot de passe doit contenir au moins 8 caractères';
                    isValid = false;
                }
                if (!this.form.password_confirmation) {
                    this.errors.password_confirmation = 'La confirmation du mot de passe est requise';
                    isValid = false;
                } else if (this.form.password !== this.form.password_confirmation) {
                    this.errors.password_confirmation = 'Les mots de passe ne correspondent pas';
                    isValid = false;
                }
            } else if (this.form.password || this.form.password_confirmation) {
                if (this.form.password && this.form.password.length < 8) {
                    this.errors.password = 'Le mot de passe doit contenir au moins 8 caractères';
                    isValid = false;
                }
                if (this.form.password !== this.form.password_confirmation) {
                    this.errors.password_confirmation = 'Les mots de passe ne correspondent pas';
                    isValid = false;
                }
            }
            if (!this.form.centre_id && !this.isCentreRole) {
                this.errors.centre_id = 'Le centre est requis';
                isValid = false;
            }
            if (!this.form.role_id) {
                this.errors.role_id = 'Le rôle est requis';
                isValid = false;
            }
            if (this.showCentreOrigineFields && !this.form.centre_origine_fr) {
                this.errors.centre_origine_fr = "Le centre d'origine en français est requis";
                isValid = false;
            }
            if (this.showCentreOrigineFields && !this.form.centre_origine_ar) {
                this.errors.centre_origine_ar = 'مركز الأصل بالعربية مطلوب';
                isValid = false;
            }
            if (this.form.telephone_1 && !/^\+?\d{8,15}$/.test(this.form.telephone_1)) {
                this.errors.telephone_1 = "Le numéro de téléphone 1 n'est pas valide";
                isValid = false;
            }
            if (this.form.telephone_2 && !/^\+?\d{8,15}$/.test(this.form.telephone_2)) {
                this.errors.telephone_2 = "Le numéro de téléphone 2 n'est pas valide";
                isValid = false;
            }
            if (this.form.date_cin && isNaN(new Date(this.form.date_cin).getTime())) {
                this.errors.date_cin = "La date CIN n'est pas valide";
                isValid = false;
            }
            if (this.form.date_naissance && isNaN(new Date(this.form.date_naissance).getTime())) {
                this.errors.date_naissance = "La date de naissance n'est pas valide";
                isValid = false;
            }
            if (this.form.date_debut && isNaN(new Date(this.form.date_debut).getTime())) {
                this.errors.date_debut = "La date de début n'est pas valide";
                isValid = false;
            }
            if (this.form.date_fin && isNaN(new Date(this.form.date_fin).getTime())) {
                this.errors.date_fin = "La date de fin n'est pas valide";
                isValid = false;
            }
            if (this.form.date_recrutement && isNaN(new Date(this.form.date_recrutement).getTime())) {
                this.errors.date_recrutement = "La date de recrutement n'est pas valide";
                isValid = false;
            }
            if (this.form.date_fin_service && isNaN(new Date(this.form.date_fin_service).getTime())) {
                this.errors.date_fin_service = "La date de fin de service n'est pas valide";
                isValid = false;
            }
            if (this.form.cin && !/^\d{8}$/.test(this.form.cin)) {
                this.errors.cin = 'Le CIN doit contenir exactement 8 chiffres';
                isValid = false;
            }
            if (this.form.matricule && this.form.matricule.length > 50) {
                this.errors.matricule = 'Le matricule ne peut pas dépasser 50 caractères';
                isValid = false;
            }
            if (!isValid) {
                this.activeTab = this.determineFirstErrorTab();
            }
            return isValid;
        },
        async submitForm() {
            if (!this.validateForm()) {
                this.showWarn('Veuillez corriger les erreurs dans le formulaire.');
                return;
            }
            if (this.isEditMode && !this.form.id) {
                this.showError('ID du personnel manquant pour la mise à jour.');
                return;
            }
            this.submitting = true;
            this.errors = {};
            try {
                const formData = { ...this.form };
                if (formData.date_cin) {
                    formData.date_cin = this.formatDateForApi(formData.date_cin);
                }
                if (formData.date_naissance) {
                    formData.date_naissance = this.formatDateForApi(formData.date_naissance);
                }
                if (formData.date_debut) {
                    formData.date_debut = this.formatDateForApi(formData.date_debut);
                }
                if (formData.date_fin) {
                    formData.date_fin = this.formatDateForApi(formData.date_fin);
                }
                if (formData.date_recrutement) {
                    formData.date_recrutement = this.formatDateForApi(formData.date_recrutement);
                }
                if (formData.date_fin_service) {
                    formData.date_fin_service = this.formatDateForApi(formData.date_fin_service);
                }
                if (this.isEditMode && !formData.password) {
                    delete formData.password;
                    delete formData.password_confirmation;
                }
                const headers = this.getHeaders();
                let response;
                if (this.isEditMode) {
                    response = await axios.patch(`/api/personnels-centres/${this.form.id}`, formData, { headers });
                    this.showSuccess('Personnel mis à jour avec succès.');
                } else {
                    response = await axios.post('/api/personnels-centres', formData, { headers });
                    this.showSuccess('Personnel créé avec succès.');
                }
                this.$emit('save', response.data.data);
                this.resetForm();
                this.$emit('update:visible', false);
                this.$emit('close');
            } catch (error) {
                this.handleApiError(error, "Une erreur est survenue lors de l'enregistrement.");
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors || {};
                    this.activeTab = this.determineFirstErrorTab();
                }
            } finally {
                this.submitting = false;
            }
        },
        determineFirstErrorTab() {
            const errorFields = Object.keys(this.errors);
            if (!errorFields.length) return 0;
            const tabMapping = {
                nom_fr: 0, prenom_fr: 0, nom_ar: 0, prenom_ar: 0, genre_fr: 0,
                nationalite_fr: 0, nationalite_ar: 0, role_id: 0,
                matricule: 1, cin: 1, date_cin: 1, lieu_cin_fr: 1, lieu_cin_ar: 1,
                date_naissance: 1, lieu_naissance_fr: 1, lieu_naissance_ar: 1,
                niveau_etude_fr: 2, specialite_diplome_fr: 2, specialite_diplome_ar: 2, code_niveau: 2,
                centre_id: 3, qualite_fr: 3, type_affectation_fr: 3, etablissement_origine_fr: 3, etablissement_origine_ar: 3,
                mission_fr: 3, mission_ar: 3, centre_origine_fr: 3, centre_origine_ar: 3, date_debut: 3, date_fin: 3,
                observation_fr_users_centres: 3,
                date_recrutement: 4, date_fin_service: 4,
                telephone_1: 5, telephone_2: 5, adresse_fr: 5, adresse_ar: 5, gouvernorat_fr: 5, delegation_fr: 5, delegation_ar: 5,
                email: 6, password: 6, password_confirmation: 6,
                photo: 7,
                observation_personnels: 8
            };
            return Math.min(...errorFields.map(field => tabMapping[field] || 0));
        },
        formatDateForApi(date) {
            if (!date || isNaN(new Date(date).getTime())) return null;
            const d = new Date(date);
            return d.toISOString().split('T')[0];
        },
        handleApiError(error, defaultMessage) {
            if (error.response?.status === 401) {
                this.showError("Session expirée. Veuillez vous reconnecter.");
            } else if (error.response?.status === 403) {
                this.showError("Vous n'êtes pas autorisé à effectuer cette action.");
            } else if (error.response?.status === 422) {
                this.errors = error.response.data.errors || {};
                this.showWarn("Veuillez vérifier les informations saisies.");
            } else {
                this.showError(defaultMessage || "Une erreur inattendue est survenue.");
            }
            console.error(error);
        },
        showSuccess(message) {
            this.toast.add({ severity: 'success', summary: 'Succès', detail: message, life: 3000 });
        },
        showError(message) {
            this.toast.add({ severity: 'error', summary: 'Erreur', detail: message, life: 5000 });
        },
        showWarn(message) {
            this.toast.add({ severity: 'warn', summary: 'Attention', detail: message, life: 5000 });
        },
        showInfo(message) {
            this.toast.add({ severity: 'info', summary: 'Information', detail: message, life: 5000 });
        }
    }
};
</script>

<style scoped>
.user-data-container {
    background-color: var(--surface-50);
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid var(--surface-200);
}

.font-arabic {
    font-family: 'Noto Naskh Arabic', sans-serif !important;
    font-size: 0.875rem;
}

.p-tabview .p-tabview-nav {
    background-color: var(--surface-50);
    border-bottom: 1px solid var(--surface-200);
}

.p-tabview .p-tabview-nav li .p-tabview-nav-link {
    color: var(--text-color);
    font-weight: 500;
    font-size: 0.875rem;
}

.p-tabview .p-tabview-nav li.p-highlight .p-tabview-nav-link {
    color: var(--primary-color);
    border-color: var(--primary-color);
}

.p-tabview-panels {
    background-color: var(--surface-ground);
    padding: 1.5rem;
    border-radius: 0 0 8px 8px;
}

.field {
    margin-bottom: 1.5rem;
}

.p-button-sm {
    padding: 0.5rem 1rem;
}

.cropper-container {
    max-width: 400px;
    margin: 0 auto;
}

.p-error {
    color: var(--red-500);
}

.p-inputtext, .p-dropdown, .p-calendar, .p-textarea {
    font-size: 0.875rem;
}

.p-fileupload .p-fileupload-buttonbar {
    background-color: var(--surface-100);
    border-radius: 8px 8px 0 0;
}

.p-fileupload .p-fileupload-content {
    background-color: var(--surface-50);
    border-radius: 0 0 8px 8px;
}
</style>
