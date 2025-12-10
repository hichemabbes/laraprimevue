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
        <template #header>
            <div class="flex align-items-center gap-2">
                <i class="pi pi-building text-primary text-2xl"></i>
                <span class="font-bold text-2xl">
                    {{ userCentreToEdit ? 'Modifier l\'affectation' : 'Ajouter une affectation' }}
                </span>
            </div>
        </template>
        <div v-if="loading" class="flex flex-column align-items-center justify-content-center gap-3 p-6">
            <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
            <span class="text-color-secondary">Chargement des données...</span>
        </div>
        <div v-else class="surface-card p-4 shadow-2 border-round">
            <TabView ref="tabview" v-model:activeIndex="activeTab">
                <TabPanel header="Ajouter une Affectation">
                    <form @submit.prevent="saveOrUpdate" class="form-spacing">
                        <div class="grid p-fluid">
                            <div class="col-12 flex align-items-start gap-3 mb-2 user-data-container">
                                <div class="photo-container" style="width: 80px; height: 80px;">
                                    <img
                                        v-if="userInfo.photo"
                                        :src="userInfo.photo"
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
                                            :value="activeRole || 'Aucun rôle actif'"
                                            severity="info"
                                            style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"
                                        />
                                    </div>
                                    <div>
                                        <Tag
                                            :value="associatedCentre || 'Aucun centre associé'"
                                            severity="success"
                                            style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"
                                        >
                                            <span class="text-sm">{{ associatedCentre || 'Aucun centre associé' }}</span>
                                        </Tag>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="matricule" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Matricule <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    id="matricule"
                                    v-model="form.user_id"
                                    :options="users"
                                    optionLabel="display_name"
                                    optionValue="id"
                                    placeholder="Sélectionner un matricule"
                                    :class="{ 'p-invalid': errors.user_id }"
                                    filter
                                    :loading="loadingUsers"
                                    emptyMessage="Aucun matricule disponible"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.user_id" class="p-error" style="font-size: 0.75rem">{{ errors.user_id }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
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
                                    filter
                                    :loading="loadingCentres"
                                    emptyMessage="Aucun centre disponible"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.centre_id" class="p-error" style="font-size: 0.75rem">{{ errors.centre_id }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="role_id" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Rôle (optionnel)
                                </label>
                                <Dropdown
                                    id="role_id"
                                    v-model="form.role_id"
                                    :options="roles"
                                    optionLabel="name"
                                    optionValue="id"
                                    placeholder="Sélectionner un rôle (facultatif)"
                                    :class="{ 'p-invalid': errors.role_id }"
                                    filter
                                    :showClear="true"
                                    :loading="loadingRoles"
                                    :disabled="loadingRoles && roles.length === 0"
                                    emptyMessage="Aucun rôle disponible"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.role_id" class="p-error" style="font-size: 0.75rem">{{ errors.role_id }}</small>
                                <small v-if="!loadingRoles && roles.length === 0" class="p-error" style="font-size: 0.75rem">
                                    Aucun rôle disponible. Veuillez vérifier la configuration des rôles.
                                </small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="type_affectation_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Type d'affectation (Français)
                                </label>
                                <InputText
                                    id="type_affectation_fr"
                                    v-model="form.type_affectation_fr"
                                    :class="{ 'p-invalid': errors.type_affectation_fr }"
                                    placeholder="Type d'affectation en français"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.type_affectation_fr" class="p-error" style="font-size: 0.75rem">{{ errors.type_affectation_fr }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="type_affectation_ar" class="block font-medium mb-1 font-arabic" style="font-size: 0.875rem">
                                    نوع التعيين (العربية)
                                </label>
                                <InputText
                                    id="type_affectation_ar"
                                    v-model="form.type_affectation_ar"
                                    :class="{ 'p-invalid': errors.type_affectation_ar }"
                                    placeholder="نوع التعيين بالعربية"
                                    style="font-size: 0.875rem"
                                    class="font-arabic"
                                    dir="rtl"
                                />
                                <small v-if="errors.type_affectation_ar" class="p-error font-arabic" style="font-size: 0.75rem">{{ errors.type_affectation_ar }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="etablissement_origine_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Établissement d'origine (Français)
                                </label>
                                <InputText
                                    id="etablissement_origine_fr"
                                    v-model="form.etablissement_origine_fr"
                                    :class="{ 'p-invalid': errors.etablissement_origine_fr }"
                                    placeholder="Établissement d'origine en français"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.etablissement_origine_fr" class="p-error" style="font-size: 0.75rem">{{ errors.etablissement_origine_fr }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="etablissement_origine_ar" class="block font-medium mb-1 font-arabic" style="font-size: 0.875rem">
                                    المؤسسة الأصلية (العربية)
                                </label>
                                <InputText
                                    id="etablissement_origine_ar"
                                    v-model="form.etablissement_origine_ar"
                                    :class="{ 'p-invalid': errors.etablissement_origine_ar }"
                                    placeholder="المؤسسة الأصلية بالعربية"
                                    style="font-size: 0.875rem"
                                    class="font-arabic"
                                    dir="rtl"
                                />
                                <small v-if="errors.etablissement_origine_ar" class="p-error font-arabic" style="font-size: 0.75rem">{{ errors.etablissement_origine_ar }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="mission_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Mission (Français)
                                </label>
                                <InputText
                                    id="mission_fr"
                                    v-model="form.mission_fr"
                                    :class="{ 'p-invalid': errors.mission_fr }"
                                    placeholder="Mission en français"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.mission_fr" class="p-error" style="font-size: 0.75rem">{{ errors.mission_fr }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="mission_ar" class="block font-medium mb-1 font-arabic" style="font-size: 0.875rem">
                                    المهمة (العربية)
                                </label>
                                <InputText
                                    id="mission_ar"
                                    v-model="form.mission_ar"
                                    :class="{ 'p-invalid': errors.mission_ar }"
                                    placeholder="المهمة بالعربية"
                                    style="font-size: 0.875rem"
                                    class="font-arabic"
                                    dir="rtl"
                                />
                                <small v-if="errors.mission_ar" class="p-error font-arabic" style="font-size: 0.75rem">{{ errors.mission_ar }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="centre_origine_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Centre d'origine (Français)
                                </label>
                                <InputText
                                    id="centre_origine_fr"
                                    v-model="form.centre_origine_fr"
                                    :class="{ 'p-invalid': errors.centre_origine_fr }"
                                    placeholder="Centre d'origine en français"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.centre_origine_fr" class="p-error" style="font-size: 0.75rem">{{ errors.centre_origine_fr }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="centre_origine_ar" class="block font-medium mb-1 font-arabic" style="font-size: 0.875rem">
                                    مركز الأصل (العربية)
                                </label>
                                <InputText
                                    id="centre_origine_ar"
                                    v-model="form.centre_origine_ar"
                                    :class="{ 'p-invalid': errors.centre_origine_ar }"
                                    placeholder="مركز الأصل بالعربية"
                                    style="font-size: 0.875rem"
                                    class="font-arabic"
                                    dir="rtl"
                                />
                                <small v-if="errors.centre_origine_ar" class="p-error font-arabic" style="font-size: 0.75rem">{{ errors.centre_origine_ar }}</small>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="date_debut" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Date de Début <span class="text-red-500">*</span>
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
                                    Date de Fin
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
                            <div class="col-12 md:col-6 field">
                                <label for="statut" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Statut <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    id="statut"
                                    v-model="form.statut"
                                    :options="statuts"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Sélectionner un statut"
                                    :class="{ 'p-invalid': errors.statut }"
                                    style="font-size: 0.875rem"
                                />
                                <small v-if="errors.statut" class="p-error" style="font-size: 0.75rem">{{ errors.statut }}</small>
                            </div>
                            <div class="col-12 field">
                                <label for="observation_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                    Observation (Français)
                                </label>
                                <Textarea
                                    id="observation_fr"
                                    v-model="form.observation_fr"
                                    rows="4"
                                    :class="{ 'p-invalid': errors.observation_fr }"
                                    placeholder="Entrez vos observations en français"
                                    style="width: 100%; font-size: 0.875rem"
                                />
                                <small v-if="errors.observation_fr" class="p-error" style="font-size: 0.75rem">{{ errors.observation_fr }}</small>
                            </div>
                        </div>
                    </form>
                </TabPanel>
                <TabPanel header="Liste des Affectations (Fr)">
                    <div class="surface-card p-4 pt-2 border-round-lg shadow-2 border-1 surface-border">
                        <div class="flex justify-content-between align-items-center mb-3">
                            <h2 class="m-0 font-bold text-xl">
                                Liste des Affectations de <span class="highlight-name">{{ userInfo.nom_fr || '' }}</span> <span class="highlight-name">{{ userInfo.prenom_fr || '' }}</span>
                            </h2>
                            <div class="flex align-items-center gap-2" style="margin-left: 0">
                                <span class="p-input-icon-left">
                                    <InputText
                                        v-model="searchQuery"
                                        placeholder="Rechercher une affectation..."
                                        class="w-full"
                                        style="font-size: 0.875rem"
                                    />
                                </span>
                                <Button
                                    icon="pi pi-filter-slash"
                                    class="p-button-outlined p-button-secondary p-button-sm"
                                    @click="searchQuery = ''"
                                    v-tooltip="'Réinitialiser le filtre'"
                                />
                            </div>
                        </div>
                        <DataTable
                            :value="filteredUserCentres"
                            :loading="loadingUserCentres"
                            dataKey="id"
                            size="small"
                            stripedRows
                            scrollable
                            scrollHeight="600px"
                            removableSort
                            class="p-datatable-sm border-round-lg"
                            :paginator="true"
                            :rows="10"
                            :rowsPerPageOptions="[10, 20, 50]"
                            paginatorTemplate="CurrentPageReport FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                            currentPageReportTemplate="Affichage de {first} à {last} sur {totalRecords} affectations"
                        >
                            <template #empty>
                                <div class="text-center py-4">
                                    <i class="pi pi-inbox text-4xl text-400 mb-2" />
                                    <p class="text-600">Aucune affectation trouvée</p>
                                </div>
                            </template>
                            <Column field="user_id" header="Matricule" sortable style="min-width: 12rem">
                                <template #body="{ data }">
                                    <span class="font-medium">{{ users.find(u => u.id === data.user_id)?.display_name || '-' }}</span>
                                </template>
                            </Column>
                            <Column field="centre_id" header="Centre" sortable style="min-width: 12rem">
                                <template #body="{ data }">
                                    <span>{{ centres.find(c => c.id === data.centre_id)?.nom_fr || '-' }}</span>
                                </template>
                            </Column>
                            <Column field="role_id" header="Rôle" sortable style="min-width: 8rem">
                                <template #body="{ data }">
                                    <span>{{ roles.find(r => r.id === data.role_id)?.name || '-' }}</span>
                                </template>
                            </Column>
                            <Column field="type_affectation_fr" header="Type d'affectation" sortable style="min-width: 12rem">
                                <template #body="{ data }">
                                    <span>{{ data.type_affectation_fr || '-' }}</span>
                                </template>
                            </Column>
                            <Column field="etablissement_origine_fr" header="Établissement d'origine" sortable style="min-width: 12rem">
                                <template #body="{ data }">
                                    <span>{{ data.etablissement_origine_fr || '-' }}</span>
                                </template>
                            </Column>
                            <Column field="mission_fr" header="Mission" sortable style="min-width: 12rem">
                                <template #body="{ data }">
                                    <span>{{ data.mission_fr || '-' }}</span>
                                </template>
                            </Column>
                            <Column field="centre_origine_fr" header="Centre d'origine" sortable style="min-width: 12rem">
                                <template #body="{ data }">
                                    <span>{{ data.centre_origine_fr || '-' }}</span>
                                </template>
                            </Column>
                            <Column field="date_debut" header="Date Début" sortable style="min-width: 10rem">
                                <template #body="{ data }">
                                    <Tag :value="formatDate(data.date_debut)" icon="pi pi-calendar" />
                                </template>
                            </Column>
                            <Column field="date_fin" header="Date Fin" sortable style="min-width: 10rem">
                                <template #body="{ data }">
                                    <Tag :value="formatDate(data.date_fin) || '-'" icon="pi pi-calendar" />
                                </template>
                            </Column>
                            <Column field="statut" header="Statut" sortable style="min-width: 10rem">
                                <template #body="{ data }">
                                    <Tag
                                        :value="data.statut || 'Inactif'"
                                        :severity="getSeverity(data.statut)"
                                        :icon="getStatutIcon(data.statut)"
                                    />
                                </template>
                            </Column>
                            <Column field="created_at" header="Créé le" sortable style="min-width: 10rem">
                                <template #body="{ data }">
                                    <Tag :value="formatDateTime(data.created_at) || '-'" icon="pi pi-clock" />
                                </template>
                            </Column>
                            <Column field="updated_at" header="Mis à jour le" sortable style="min-width: 10rem">
                                <template #body="{ data }">
                                    <Tag :value="formatDateTime(data.updated_at) || '-'" icon="pi pi-clock" />
                                </template>
                            </Column>
                            <Column field="deleted_at" header="Supprimé le" sortable style="min-width: 10rem">
                                <template #body="{ data }">
                                    <Tag :value="formatDateTime(data.deleted_at) || '-'" icon="pi pi-trash" />
                                </template>
                            </Column>
                            <Column header="Actions" style="min-width: 10rem">
                                <template #body="{ data }">
                                    <div class="flex gap-1">
                                        <Button
                                            icon="pi pi-pencil"
                                            class="p-button-rounded p-button-text p-button-primary p-button-sm"
                                            @click="editUserCentre(data, 1)"
                                            v-tooltip="'Modifier'"
                                        />
                                        <Button
                                            icon="pi pi-trash"
                                            class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                            @click="data.deleted_at ? null : confirmDeleteUserCentre(data)"
                                            v-tooltip="data.deleted_at ? 'Déjà supprimé' : 'Supprimer'"
                                            :disabled="data.deleted_at"
                                        />
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </TabPanel>
                <TabPanel header="Liste des Affectations (Ar)">
                    <div class="surface-card p-4 pt-2 border-round-lg shadow-20 border-1 surface-border">
                        <div class="flex justify-content-between align-items-center mb-3">
                            <h2 class="m-0 font-bold text-xl arabic-text arabic-text-header" dir="rtl">
                                قائمة التعيينات لـ <span class="highlight-name">{{ userInfo.prenom_ar || '' }}</span> <span class="highlight-name">{{ userInfo.nom_ar || '' }}</span>
                            </h2>
                            <div class="flex align-items-center gap-2" style="margin-right: 0">
                                <span class="p-input-icon-left">
                                    <InputText
                                        v-model="searchQueryAr"
                                        placeholder="ابحث عن تعيين..."
                                        class="w-full"
                                        style="font-size: 0.875rem;"
                                        dir="rtl"
                                    />
                                </span>
                                <Button
                                    icon="pi pi-filter-slash"
                                    class="p-button-outlined p-button-secondary p-button-sm"
                                    @click="searchQueryAr = ''"
                                    v-tooltip="'إعادة تعيين الفلتر'"
                                />
                            </div>
                        </div>
                        <DataTable
                            :value="filteredUserCentresAr"
                            :loading="loadingUserCentres"
                            dataKey="id"
                            size="small"
                            stripedRows
                            scrollable
                            scrollHeight="600px"
                            removableSort
                            class="p-datatable-sm border-round-lg arabic-text"
                            :paginator="true"
                            :rows="10"
                            :rowsPerPageOptions="[10, 20, 50]"
                            paginatorTemplate="CurrentPageReport FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                            currentPageReportTemplate="عرض من {first} إلى {last} من {totalRecords} تعيينات"
                            dir="rtl"
                        >
                            <template #empty>
                                <div class="text-center py-4">
                                    <i class="pi pi-inbox text-4xl text-400 mb-2" />
                                    <p class="text-600 arabic-text">لا توجد تعيينات</p>
                                </div>
                            </template>
                            <Column field="user_id" header="الرقم التعريفي" sortable style="min-width: 12rem">
                                <template #body="{ data }">
                                    <span class="arabic-text">{{ users.find(u => u.id === data.user_id)?.display_name || '-' }}</span>
                                </template>
                            </Column>
                            <Column field="centre_id" header="المركز" sortable style="min-width: 12rem">
                                <template #body="{ data }">
                                    <span class="arabic-text">{{ centres.find(c => c.id === data.centre_id)?.nom_ar || '-' }}</span>
                                </template>
                            </Column>
                            <Column field="role_id" header="الدور" sortable style="min-width: 8rem">
                                <template #body="{ data }">
                                    <span class="arabic-text">{{ roles.find(r => r.id === data.role_id)?.name || '-' }}</span>
                                </template>
                            </Column>
                            <Column field="type_affectation_ar" header="نوع التعيين" sortable style="min-width: 12rem">
                                <template #body="{ data }">
                                    <span class="arabic-text">{{ data.type_affectation_ar || '-' }}</span>
                                </template>
                            </Column>
                            <Column field="etablissement_origine_ar" header="المؤسسة الأصلية" sortable style="min-width: 12rem">
                                <template #body="{ data }">
                                    <span class="arabic-text">{{ data.etablissement_origine_ar || '-' }}</span>
                                </template>
                            </Column>
                            <Column field="mission_ar" header="المهمة" sortable style="min-width: 12rem">
                                <template #body="{ data }">
                                    <span class="arabic-text">{{ data.mission_ar || '-' }}</span>
                                </template>
                            </Column>
                            <Column field="centre_origine_ar" header="مركز الأصل" sortable style="min-width: 12rem">
                                <template #body="{ data }">
                                    <span class="arabic-text">{{ data.centre_origine_ar || '-' }}</span>
                                </template>
                            </Column>
                            <Column field="date_debut" header="تاريخ البداية" sortable style="min-width: 10rem">
                                <template #body="{ data }">
                                    <Tag :value="formatDate(data.date_debut)" icon="pi pi-calendar" class="arabic-text" />
                                </template>
                            </Column>
                            <Column field="date_fin" header="تاريخ النهاية" sortable style="min-width: 10rem">
                                <template #body="{ data }">
                                    <Tag :value="formatDate(data.date_fin) || '-'" icon="pi pi-calendar" class="arabic-text" />
                                </template>
                            </Column>
                            <Column field="statut" header="الحالة" sortable style="min-width: 10rem">
                                <template #body="{ data }">
                                    <Tag
                                        :value="getStatutAr(data.statut) || 'غير نشط'"
                                        :severity="getSeverity(data.statut)"
                                        :icon="getStatutIcon(data.statut)"
                                        class="arabic-text"
                                    />
                                </template>
                            </Column>
                            <Column field="created_at" header="تاريخ الإنشاء" sortable style="min-width: 10rem">
                                <template #body="{ data }">
                                    <Tag :value="formatDateTime(data.created_at) || '-'" icon="pi pi-clock" class="arabic-text" />
                                </template>
                            </Column>
                            <Column field="updated_at" header="تاريخ التحديث" sortable style="min-width: 10rem">
                                <template #body="{ data }">
                                    <Tag :value="formatDateTime(data.updated_at) || '-'" icon="pi pi-clock" class="arabic-text" />
                                </template>
                            </Column>
                            <Column field="deleted_at" header="تاريخ الحذف" sortable style="min-width: 10rem">
                                <template #body="{ data }">
                                    <Tag :value="formatDateTime(data.deleted_at) || '-'" icon="pi pi-trash" class="arabic-text" />
                                </template>
                            </Column>
                            <Column header="الإجراءات" style="min-width: 10rem">
                                <template #body="{ data }">
                                    <div class="flex gap-1">
                                        <Button
                                            icon="pi pi-pencil"
                                            class="p-button-rounded p-button-text p-button-primary p-button-sm"
                                            @click="editUserCentre(data, 2)"
                                            v-tooltip="'تعديل'"
                                        />
                                        <Button
                                            icon="pi pi-trash"
                                            class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                            @click="data.deleted_at ? null : confirmDeleteUserCentre(data)"
                                            v-tooltip="data.deleted_at ? 'محذوف بالفعل' : 'حذف'"
                                            :disabled="data.deleted_at"
                                        />
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </TabPanel>
                <TabPanel header="Observation">
                    <div class="grid p-fluid">
                        <div class="col-12 field">
                            <label for="observation_fr" class="block font-medium mb-1" style="font-size: 0.875rem">
                                Observation (Français)
                            </label>
                            <Textarea
                                id="observation_fr"
                                v-model="form.observation_fr"
                                rows="4"
                                :class="{ 'p-invalid': errors.observation_fr }"
                                placeholder="Entrez vos observations en français"
                                style="width: 100%; font-size: 0.875rem"
                            />
                            <small v-if="errors.observation_fr" class="p-error" style="font-size: 0.75rem">{{ errors.observation_fr }}</small>
                        </div>
                    </div>
                </TabPanel>
            </TabView>
        </div>
        <template #footer>
            <div class="flex justify-content-end gap-2">
                <Button
                    label="Annuler"
                    icon="pi pi-times-circle"
                    class="p-button-outlined p-button-secondary p-button-sm"
                    @click="close"
                    :disabled="loading"
                    style="font-size: 0.875rem"
                />
                <Button
                    :label="userCentreToEdit ? 'Mettre à jour' : 'Enregistrer'"
                    icon="pi pi-check-circle"
                    class="p-button-primary p-button-sm"
                    @click="saveOrUpdate"
                    :loading="loading"
                    style="font-size: 0.875rem"
                />
            </div>
        </template>
    </Dialog>
    <Dialog
        header="Confirmer la suppression"
        v-model:visible="deleteFormVisible"
        :modal="true"
        :style="{ width: '30rem' }"
        :pt="{
            root: 'border-round-xl shadow-5',
            mask: { style: 'backdrop-filter: blur(4px)' },
            header: { class: 'surface-50 border-bottom-1 surface-border p-4' },
            content: { class: 'surface-ground p-4' },
            footer: { class: 'surface-50 border-top-1 surface-border p-4' }
        }"
    >
        <div class="grid p-fluid">
            <div class="col-12 field">
                <label for="deletePassword" class="block font-medium mb-2" style="font-size: 0.875rem">
                    Mot de passe
                </label>
                <div class="p-inputgroup">
                    <InputText
                        id="deletePassword"
                        v-model="deletePassword"
                        :type="showPassword ? 'text' : 'password'"
                        :class="{ 'p-invalid': passwordError }"
                        placeholder="Entrez votre mot de passe"
                        style="font-size: 0.875rem;"
                    />
                    <Button
                        :icon="showPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"
                        class="p-button-outlined"
                        @click="toggleShowPassword"
                        v-tooltip="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                    />
                </div>
                <small v-if="passwordError" class="p-error" style="font-size: 0.75rem">{{ passwordError }}</small>
            </div>
        </div>
        <template #footer>
            <div class="flex justify-content-end gap-2">
                <Button
                    label="Annuler"
                    icon="pi pi-times-circle"
                    class="p-button-outlined p-button-secondary p-button-sm"
                    @click="cancelDelete"
                    style="font-size: 0.875rem"
                />
                <Button
                    label="Supprimer"
                    icon="pi pi-trash"
                    class="p-button-danger p-button-sm"
                    @click="deleteUserCentre"
                    :loading="loading"
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
import Textarea from 'primevue/textarea';
import ProgressSpinner from 'primevue/progressspinner';
import Toast from 'primevue/toast';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';

export default {
    name: 'UserCentreForm',
    components: {
        Button,
        Calendar,
        Dialog,
        Dropdown,
        InputText,
        Textarea,
        ProgressSpinner,
        Toast,
        TabView,
        TabPanel,
        DataTable,
        Column,
        Tag
    },
    props: {
        visible: {
            type: Boolean,
            required: true,
        },
        userCentreToEdit: {
            type: Object,
            default: null,
        },
        personnelId: {
            type: Number,
            required: false,
            validator: (value) => {
                if (value && (!Number.isInteger(value) || value <= 0)) {
                    console.warn('personnelId doit être un entier positif:', value);
                    return false;
                }
                return true;
            }
        },
    },
    emits: ['update:visible', 'save', 'update', 'close'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            form: {
                user_id: null,
                centre_id: null,
                role_id: null,
                type_affectation_fr: '',
                type_affectation_ar: '',
                etablissement_origine_fr: '',
                etablissement_origine_ar: '',
                mission_fr: '',
                mission_ar: '',
                centre_origine_fr: '',
                centre_origine_ar: '',
                date_debut: null,
                date_fin: null,
                statut: null,
                observation_fr: '',
            },
            users: [],
            centres: [],
            roles: [],
            userCentres: [],
            userInfo: {
                nom_fr: '',
                prenom_fr: '',
                nom_ar: '',
                prenom_ar: '',
                photo: null,
                centre_id: null,
            },
            statuts: [
                { label: 'Actif', value: 'actif' },
                { label: 'Inactif', value: 'inactif' },
                { label: 'Suspendu', value: 'suspendu' },
            ],
            errors: {},
            loading: false,
            loadingUsers: false,
            loadingCentres: false,
            loadingRoles: false,
            loadingUserCentres: false,
            deleteFormVisible: false,
            deletePassword: '',
            passwordError: '',
            showPassword: false,
            activeTab: 0,
            searchQuery: '',
            searchQueryAr: '',
            returnTab: 0,
        };
    },
    computed: {
        formVisible: {
            get() {
                return this.visible;
            },
            set(value) {
                this.$emit('update:visible', value);
            }
        },
        filteredUserCentres() {
            if (!this.searchQuery.trim()) {
                return this.userCentres;
            }
            const query = this.searchQuery.toLowerCase().trim();
            return this.userCentres.filter(centre =>
                (this.users.find(u => u.id === centre.user_id)?.display_name?.toLowerCase().includes(query) ||
                    this.centres.find(c => c.id === centre.centre_id)?.nom_fr?.toLowerCase().includes(query) ||
                    centre.type_affectation_fr?.toLowerCase().includes(query) ||
                    centre.etablissement_origine_fr?.toLowerCase().includes(query) ||
                    centre.mission_fr?.toLowerCase().includes(query) ||
                    centre.centre_origine_fr?.toLowerCase().includes(query))
            );
        },
        filteredUserCentresAr() {
            if (!this.searchQueryAr.trim()) {
                return this.userCentres;
            }
            const query = this.searchQueryAr.toLowerCase().trim();
            return this.userCentres.filter(centre =>
                (this.users.find(u => u.id === centre.user_id)?.display_name?.toLowerCase().includes(query) ||
                    this.centres.find(c => c.id === centre.centre_id)?.nom_ar?.toLowerCase().includes(query) ||
                    centre.type_affectation_ar?.toLowerCase().includes(query) ||
                    centre.etablissement_origine_ar?.toLowerCase().includes(query) ||
                    centre.mission_ar?.toLowerCase().includes(query) ||
                    centre.centre_origine_ar?.toLowerCase().includes(query))
            );
        },
        activeRole() {
            const latestCentre = this.userCentres
                .sort((a, b) => new Date(b.date_debut) - new Date(a.date_debut))[0];
            return latestCentre ? this.roles.find(r => r.id === latestCentre.role_id)?.name : null;
        },
        associatedCentre() {
            if (!this.centres.length) {
                console.warn('Liste des centres non chargée');
                return 'Aucun centre disponible';
            }
            if (!this.userInfo.centre_id) {
                return 'Aucun centre associé';
            }
            const centre = this.centres.find(centre => centre.id === this.userInfo.centre_id);
            if (!centre) {
                console.warn('Centre non trouvé pour centre_id:', this.userInfo.centre_id, 'dans centres:', this.centres);
                return 'Centre introuvable';
            }
            return centre.nom_fr || centre.nom_ar || 'Aucun centre associé';
        }
    },
    watch: {
        visible(newVal) {
            if (newVal) {
                this.fetchFormData();
                this.fetchUserCentres();
                this.activeTab = 0;
            } else {
                this.resetForm();
            }
        },
        userCentreToEdit: {
            handler(newVal) {
                if (newVal) {
                    this.form = {
                        user_id: newVal.user_id || null,
                        centre_id: newVal.centre_id || null,
                        role_id: newVal.role_id || null,
                        type_affectation_fr: newVal.type_affectation_fr || '',
                        type_affectation_ar: newVal.type_affectation_ar || '',
                        etablissement_origine_fr: newVal.etablissement_origine_fr || '',
                        etablissement_origine_ar: newVal.etablissement_origine_ar || '',
                        mission_fr: newVal.mission_fr || '',
                        mission_ar: newVal.mission_ar || '',
                        centre_origine_fr: newVal.centre_origine_fr || '',
                        centre_origine_ar: newVal.centre_origine_ar || '',
                        date_debut: newVal.date_debut ? new Date(newVal.date_debut) : null,
                        date_fin: newVal.date_fin ? new Date(newVal.date_fin) : null,
                        statut: newVal.statut || null,
                        observation_fr: newVal.observation_fr || '',
                    };
                } else {
                    this.resetForm();
                }
            },
            immediate: true,
        },
        personnelId(newVal, oldVal) {
            if (newVal !== oldVal && Number.isInteger(newVal) && newVal > 0) {
                this.fetchUserCentres();
            }
        }
    },
    mounted() {
        if (this.visible) {
            this.fetchFormData();
            this.fetchUserCentres();
        }
    },
    beforeUnmount() {
        this.resetForm();
    },
    methods: {
        async fetchFormData() {
            this.loading = this.loadingUsers = this.loadingCentres = this.loadingRoles = true;
            try {
                const token = localStorage.getItem('token');
                if (!token) {
                    console.error('No authentication token found in localStorage');
                    this.showError('Vous devez être connecté pour charger les données.');
                    this.$router.push({ name: 'login' });
                    return;
                }

                const response = await axios.get('/api/form-data', {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                });

                this.users = Array.isArray(response.data.users) ? response.data.users : [];
                this.centres = Array.isArray(response.data.centres) ? response.data.centres : [];
                this.roles = Array.isArray(response.data.roles) ? response.data.roles : [];

                if (this.users.length === 0) {
                    this.showWarn('Aucun matricule disponible. Vérifiez les données des utilisateurs.');
                }
                if (this.centres.length === 0) {
                    this.showWarn('Aucun centre disponible.');
                }
                if (this.roles.length === 0) {
                    this.showWarn('Aucun rôle disponible pour les centres.');
                }
            } catch (error) {
                console.error('Erreur lors de fetchFormData:', {
                    status: error.response?.status || 'N/A',
                    data: error.response?.data || null,
                    message: error.message,
                    stack: error.stack,
                });
                this.handleApiError(error, 'Erreur lors du chargement des données du formulaire. Veuillez vérifier la connexion au serveur.');
                this.users = this.centres = this.roles = [];
            } finally {
                this.loading = this.loadingUsers = this.loadingCentres = this.loadingRoles = false;
            }
        },
        async fetchUserCentres() {
            if (!this.personnelId) {
                this.showError('ID du personnel non valide.');
                return;
            }
            this.loadingUserCentres = true;
            try {
                const response = await axios.get('/api/users-centres', {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                    params: { personnel_id: this.personnelId }
                });
                if (typeof response.data === 'string') {
                    throw new Error('Réponse invalide : HTML reçu au lieu de JSON');
                }
                this.userCentres = response.data.user_centres || [];
                this.userInfo = response.data.user_info || {
                    nom_fr: '',
                    prenom_fr: '',
                    nom_ar: '',
                    prenom_ar: '',
                    photo: null,
                    centre_id: null,
                };
                this.centres = response.data.centres || this.centres;
                if (!this.userInfo.centre_id) {
                    this.showInfo('Aucun centre associé à ce personnel.');
                }
            } catch (error) {
                console.error('Erreur lors de fetchUserCentres:', {
                    status: error.response?.status || 'N/A',
                    data: error.response?.data || null,
                    message: error.message,
                    stack: error.stack,
                });
                this.handleApiError(error, 'Erreur lors du chargement des affectations du personnel. Veuillez vérifier la connexion au serveur.');
                this.userCentres = [];
            } finally {
                this.loadingUserCentres = false;
            }
        },
        async saveOrUpdate() {
            this.errors = {};
            if (!this.validateForm()) {
                this.showError('Veuillez corriger les erreurs dans le formulaire.');
                return;
            }
            this.loading = true;
            try {
                const payload = {
                    ...this.form,
                    date_debut: this.form.date_debut ? this.formatDateForApi(this.form.date_debut) : null,
                    date_fin: this.form.date_fin ? this.formatDateForApi(this.form.date_fin) : null,
                };
                let response;
                if (this.userCentreToEdit) {
                    response = await axios.put(`/api/users-centres/${this.userCentreToEdit.id}`, payload, {
                        headers: {
                            Authorization: `Bearer ${localStorage.getItem('token')}`,
                        },
                    });
                    this.$emit('update', {
                        ...response.data,
                        user_matricule: this.users.find(u => u.id === this.form.user_id)?.matricule || null,
                        centre_nom_fr: this.centres.find(c => c.id === this.form.centre_id)?.nom_fr || null,
                        centre_nom_ar: this.centres.find(c => c.id === this.form.centre_id)?.nom_ar || null,
                        role_name: this.form.role_id ? this.roles.find(r => r.id === this.form.role_id)?.name : null,
                    });
                    this.showSuccess('Affectation mise à jour avec succès.');
                    this.activeTab = this.returnTab || 1;
                } else {
                    response = await axios.post('/api/users-centres', payload, {
                        headers: {
                            Authorization: `Bearer ${localStorage.getItem('token')}`,
                        },
                    });
                    this.$emit('save', {
                        ...response.data,
                        user_matricule: this.users.find(u => u.id === this.form.user_id)?.matricule || null,
                        centre_nom_fr: this.centres.find(c => c.id === this.form.centre_id)?.nom_fr || null,
                        centre_nom_ar: this.centres.find(c => c.id === this.form.centre_id)?.nom_ar || null,
                        role_name: this.form.role_id ? this.roles.find(r => r.id === this.form.role_id)?.name || null : null,
                    });
                    this.showSuccess('Affectation créée avec succès.');
                    this.activeTab = 1;
                }
                await this.fetchUserCentres();
                this.resetForm();
            } catch (error) {
                console.error('Erreur lors de saveOrUpdate:', {
                    status: error.response?.status || 'N/A',
                    data: error.response?.data || null,
                    message: error.message,
                    stack: error.stack,
                });
                this.handleApiError(error, 'Erreur lors de l\'enregistrement de l\'affectation. Veuillez vérifier la connexion au serveur.');
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            } finally {
                this.loading = false;
            }
        },
        editUserCentre(userCentre, tabIndex) {
            this.userCentreToEdit = { ...userCentre };
            this.returnTab = tabIndex;
            this.form = {
                user_id: userCentre.user_id || null,
                centre_id: userCentre.centre_id || null,
                role_id: userCentre.role_id || null,
                type_affectation_fr: userCentre.type_affectation_fr || '',
                type_affectation_ar: userCentre.type_affectation_ar || '',
                etablissement_origine_fr: userCentre.etablissement_origine_fr || '',
                etablissement_origine_ar: userCentre.etablissement_origine_ar || '',
                mission_fr: userCentre.mission_fr || '',
                mission_ar: userCentre.mission_ar || '',
                centre_origine_fr: userCentre.centre_origine_fr || '',
                centre_origine_ar: userCentre.centre_origine_ar || '',
                date_debut: userCentre.date_debut ? new Date(userCentre.date_debut) : null,
                date_fin: userCentre.date_fin ? new Date(userCentre.date_fin) : null,
                statut: userCentre.statut || null,
                observation_fr: userCentre.observation_fr || '',
            };
            this.activeTab = 0;
        },
        confirmDeleteUserCentre(userCentre) {
            this.userCentreToEdit = userCentre;
            this.returnTab = this.activeTab;
            this.deletePassword = '';
            this.passwordError = '';
            this.deleteFormVisible = true;
        },
        async deleteUserCentre() {
            if (!this.userCentreToEdit?.id) return;
            this.loading = true;
            try {
                await axios.delete(`/api/users-centres/${this.userCentreToEdit.id}`, {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                    data: { password: this.deletePassword.trim() }
                });
                this.showSuccess('Affectation supprimée avec succès.');
                this.$emit('update', null);
                await this.fetchUserCentres();
                this.deleteFormVisible = false;
                this.activeTab = this.returnTab;
                this.resetForm();
            } catch (error) {
                console.error('Erreur lors de deleteUserCentre:', {
                    status: error.response?.status || 'N/A',
                    data: error.response?.data || null,
                    message: error.message,
                    stack: error.stack,
                });
                this.passwordError = error.response?.data?.error || 'Erreur lors de la suppression. Vérifiez votre mot de passe.';
                this.showError(this.passwordError);
            } finally {
                this.loading = false;
            }
        },
        cancelDelete() {
            this.deleteFormVisible = false;
            this.deletePassword = '';
            this.passwordError = '';
            this.userCentreToEdit = null;
            this.activeTab = this.returnTab;
        },
        resetForm() {
            this.form = {
                user_id: null,
                centre_id: null,
                role_id: null,
                type_affectation_fr: '',
                type_affectation_ar: '',
                etablissement_origine_fr: '',
                etablissement_origine_ar: '',
                mission_fr: '',
                mission_ar: '',
                centre_origine_fr: '',
                centre_origine_ar: '',
                date_debut: null,
                date_fin: null,
                statut: null,
                observation_fr: '',
            };
            this.errors = {};
            this.loading = false;
            this.deleteFormVisible = false;
            this.deletePassword = '';
            this.passwordError = '';
            this.searchQuery = '';
            this.searchQueryAr = '';
            this.returnTab = 0;
        },
        validateForm() {
            this.errors = {};
            let isValid = true;
            if (!this.form.user_id) {
                this.errors.user_id = 'Le matricule est requis.';
                isValid = false;
            }
            if (!this.form.centre_id) {
                this.errors.centre_id = 'Le centre est requis.';
                isValid = false;
            }
            if (!this.form.date_debut) {
                this.errors.date_debut = 'La date de début est requise.';
                isValid = false;
            }
            if (!this.form.statut) {
                this.errors.statut = 'Le statut est requis.';
                isValid = false;
            }
            if (this.form.date_fin && this.form.date_debut) {
                const startDate = new Date(this.form.date_debut);
                const endDate = new Date(this.form.date_fin);
                if (endDate <= startDate) {
                    this.errors.date_fin = 'La date de fin doit être strictement supérieure à la date de début.';
                    isValid = false;
                }
            }
            return isValid;
        },
        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString('fr-FR', { year: 'numeric', month: '2-digit', day: '2-digit' });
        },
        formatDateTime(date) {
            if (!date) return '';
            return new Date(date).toLocaleString('fr-FR', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
        },
        formatDateForApi(date) {
            if (!date) return null;
            return new Date(date).toISOString().split('T')[0];
        },
        getStatutAr(statut) {
            const statusMap = {
                'actif': 'نشط',
                'inactif': 'غير نشط',
                'suspendu': 'معلق'
            };
            return statusMap[statut] || statut;
        },
        getSeverity(statut) {
            const statusSeverity = {
                'actif': 'success',
                'inactif': 'secondary',
                'suspendu': 'warning',
                'نشط': 'success',
                'غير نشط': 'secondary',
                'معلق': 'warning'
            };
            return statusSeverity[statut] || 'secondary';
        },
        getStatutIcon(statut) {
            const statusIcons = {
                'actif': 'pi pi-check',
                'inactif': 'pi pi-times',
                'suspendu': 'pi pi-exclamation-triangle',
                'نشط': 'pi pi-check',
                'غير نشط': 'pi pi-times',
                'معلق': 'pi pi-exclamation-triangle'
            };
            return statusIcons[statut] || 'pi pi-info';
        },
        showSuccess(message) {
            this.toast.add({ severity: 'success', summary: 'Succès', detail: message, life: 3000 });
        },
        showError(message) {
            this.toast.add({ severity: 'error', summary: 'Erreur', detail: message, life: 5000 });
        },
        showWarn(message) {
            this.toast.add({ severity: 'warn', summary: 'Avertissement', detail: message, life: 5000 });
        },
        showInfo(message) {
            this.toast.add({ severity: 'info', summary: 'Information', detail: message, life: 5000 });
        },
        handleApiError(error, defaultMessage) {
            const errorDetails = {
                message: error.message || 'Erreur inconnue',
                status: error.response?.status || 'N/A',
                data: error.response?.data || null,
                stack: error.stack || 'N/A',
            };
            console.error('Erreur API:', errorDetails);
            this.showError(error.response?.data?.message || defaultMessage);
            if (error.response?.status === 401) {
                this.$router.push({ name: 'login' });
            }
        },
        close() {
            this.resetForm();
            this.formVisible = false;
            this.$emit('close');
        },
        handleVisibleUpdate(newVisible) {
            this.formVisible = newVisible;
            if (!newVisible) {
                this.resetForm();
            }
        },
        toggleShowPassword() {
            this.showPassword = !this.showPassword;
        },
    },
};
</script>

<style scoped>
@font-face {
    font-family: 'Montserrat-Arabic-Regular';
    src: url('/fonts/Montserrat-Arabic-Regular.otf') format('opentype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}
:deep(.p-tabview .p-tabview-panels) {
    padding-top: 1.5rem !important;
}
.field {
    margin-bottom: 0.5rem;
}
.form-spacing {
    margin-bottom: 1rem;
}
label.font-medium {
    color: var(--text-color, #2c3e50);
    font-weight: 500;
}
.highlight-name {
    color: var(--primary-color);
}
.arabic-text,
:deep(.p-inputtext[dir='rtl']),
:deep(.p-textarea[dir='rtl']) {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
    direction: rtl;
    text-align: right;
}
:deep(.p-datatable) {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
:deep(.p-datatable-sm .p-datatable-tbody > tr > td) {
    padding: 0.5rem 1rem;
}
:deep(.p-datatable .p-column-header-content) {
    justify-content: center;
}
:deep(.p-datatable.arabic-text .p-datatable-thead > tr > th),
:deep(.p-datatable.arabic-text .p-datatable-tbody > tr > td),
:deep(.p-datatable.arabic-text .p-paginator .p-paginator-current) {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
    text-align: right;
}
:deep(.p-button) {
    max-width: 200px;
    padding: 0.8rem 1rem;
    font-size: 0.875rem;
    border-radius: 0.25rem;
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
:deep(.p-tabview .p-tabview-nav li .p-tabview-nav-link:not(.p-disabled):focus) {
    box-shadow: none;
}
:deep(.p-tabview .p-tabview-nav li.p-highlight .p-tabview-nav-link) {
    color: var(--primary-color);
    border-bottom: 2px solid var(--primary-color);
}
:deep(.p-tabview .p-tabview-panels) {
    background: transparent;
    padding: 0;
}
:deep(.p-inputtext),
:deep(.p-dropdown),
:deep(.p-textarea),
:deep(.p-calendar) {
    border-radius: 0.25rem;
}
.p-error {
    color: #dc3545;
    font-size: 0.875rem;
}
.arabic-text-header {
    margin-right: auto;
    order: 2;
}
</style>
