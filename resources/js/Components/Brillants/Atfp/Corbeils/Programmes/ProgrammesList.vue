```vue
<template>
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
                        label="Programmes"
                        icon="pi pi-book"
                        class="p-button-text p-button-plain"
                        :class="{ 'p-button-active': activeTab === 0 }"
                        @click="activeTab = 0"
                    />
                    <Button
                        label="Mémoires"
                        icon="pi pi-file"
                        class="p-button-text p-button-plain"
                        :class="{ 'p-button-active': activeTab === 1 }"
                        @click="activeTab = 1"
                    />
                    <Button
                        label="Programmes d'Études"
                        icon="pi pi-sitemap"
                        class="p-button-text p-button-plain"
                        :class="{ 'p-button-active': activeTab === 2 }"
                        @click="activeTab = 2"
                    />
                </div>
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-refresh"
                        class="p-button-text p-button-plain"
                        @click="fetchData"
                        v-tooltip="'Rafraîchir'"
                    />
                </div>
            </div>
        </div>

        <!-- Main Content Section -->
        <div
            class="surface-card p-4 pt-2 border-round shadow-2 border-1 surface-border"
        >
            <!-- Tabs -->
            <TabView
                v-model:activeIndex="activeTab"
                @update:activeIndex="onTabChange"
            >
                <!-- Onglet Programmes -->
                <TabPanel header="Programmes">
                    <!-- Table Header with Actions -->
                    <div
                        class="flex justify-content-between align-items-center mb-4"
                    >
                        <div class="flex align-items-center gap-2">
                            <span class="p-input-icon-left search-field">
                                <i class="pi pi-search" />
                                <InputText
                                    v-model="filters['global'].value"
                                    placeholder="Rechercher..."
                                />
                            </span>
                            <Button
                                icon="pi pi-filter-slash"
                                class="p-button-outlined p-button-secondary"
                                size="small"
                                @click="clearFilter"
                                v-tooltip="'Réinitialiser les filtres'"
                            />
                        </div>
                    </div>

                    <!-- Main DataTable for Specialites -->
                    <DataTable
                        v-model:expandedRows="expandedRows"
                        v-model:filters="filters"
                        :value="specialites"
                        dataKey="id"
                        size="small"
                        stripedRows
                        paginator
                        :rows="10"
                        :rowsPerPageOptions="[10, 25, 50]"
                        filterDisplay="menu"
                        :globalFilterFields="[
                            'code',
                            'nom_fr',
                            'nom_ar',
                            'diplome_fr',
                        ]"
                        :loading="loading"
                        scrollable
                        scrollHeight="600px"
                        removableSort
                        class="p-datatable-sm border-1 surface-border custom-datatable"
                    >
                        <template #empty>
                            <div class="text-center py-4">
                                <i class="pi pi-inbox text-4xl text-400 mb-2" />
                                <p class="text-600">
                                    Aucune spécialité trouvée
                                </p>
                            </div>
                        </template>

                        <Column expander style="width: 3rem" />
                        <Column
                            field="code"
                            header="Code"
                            sortable
                            style="min-width: 10rem"
                        >
                            <template #body="{ data }">
                                <span class="font-medium">{{
                                    data.code || '-'
                                }}</span>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <InputText
                                    v-model="filterModel.value"
                                    type="text"
                                    class="p-column-filter"
                                    placeholder="Rechercher par code"
                                    @input="filterCallback"
                                />
                            </template>
                        </Column>
                        <Column
                            field="nom_fr"
                            header="Nom (FR)"
                            sortable
                            style="min-width: 15rem"
                        >
                            <template #body="{ data }">
                                <span>{{ data.nom_fr || '-' }}</span>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <InputText
                                    v-model="filterModel.value"
                                    type="text"
                                    class="p-column-filter"
                                    placeholder="Rechercher par nom (FR)"
                                    @input="filterCallback"
                                />
                            </template>
                        </Column>
                        <Column
                            field="nom_ar"
                            header="Nom (AR)"
                            sortable
                            style="min-width: 15rem"
                        >
                            <template #body="{ data }">
                                <span>{{ data.nom_ar || '-' }}</span>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <InputText
                                    v-model="filterModel.value"
                                    type="text"
                                    class="p-column-filter"
                                    placeholder="Rechercher par nom (AR)"
                                    @input="filterCallback"
                                />
                            </template>
                        </Column>
                        <Column
                            field="diplome_fr"
                            header="Diplôme"
                            sortable
                            style="min-width: 12rem"
                        >
                            <template #body="{ data }">
                                <Tag :value="data.diplome_fr || '-'" />
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <InputText
                                    v-model="filterModel.value"
                                    type="text"
                                    class="p-column-filter"
                                    placeholder="Rechercher par diplôme"
                                    @input="filterCallback"
                                />
                            </template>
                        </Column>
                        <Column
                            header="Actions"
                            :exportable="false"
                            style="min-width: 12rem"
                        >
                            <template #body="{ data }">
                                <div class="flex gap-1">
                                    <Button
                                        icon="pi pi-lock"
                                        class="p-button-rounded p-button-text p-button-sm"
                                        :class="{
                                            'p-button-info':
                                                frozenRows.includes(data.id),
                                        }"
                                        @click="toggleFreeze(data.id)"
                                        v-tooltip="'Fixer la ligne'"
                                    />
                                    <Button
                                        icon="pi pi-plus"
                                        class="p-button-rounded p-button-text p-button-success p-button-sm"
                                        @click="openProgrammeForm(data.id)"
                                        v-tooltip="'Ajouter Programme'"
                                    />
                                    <Button
                                        icon="pi pi-eye"
                                        class="p-button-rounded p-button-text p-button-info p-button-sm"
                                        @click="
                                            openDetailsProgSpecialite(data.id)
                                        "
                                        v-tooltip="'Visionner'"
                                    />
                                </div>
                            </template>
                        </Column>

                        <!-- Expanded Row Template for Programmes -->
                        <template #expansion="{ data }">
                            <div class="p-3 surface-50 border-round">
                                <div
                                    class="flex justify-content-between align-items-center mb-3"
                                >
                                    <div class="flex align-items-center gap-2">
                                        <i
                                            class="pi pi-book text-primary-500"
                                        />
                                        <span class="font-semibold"
                                            >Liste des programmes de
                                            {{ data.nom_fr }}</span
                                        >
                                    </div>
                                    <Button
                                        icon="pi pi-plus"
                                        label="Ajouter Module"
                                        class="p-button-sm p-button-success"
                                        @click="openModuleForm(null, data.id)"
                                    />
                                </div>

                                <DataTable
                                    v-model:expandedRows="expandedProgrammes"
                                    :value="data.programmes || []"
                                    dataKey="id"
                                    size="small"
                                    :loading="loading"
                                    class="p-datatable-sm custom-datatable"
                                >
                                    <Column expander style="width: 3rem" />
                                    <Column
                                        field="version"
                                        header="Version"
                                        sortable
                                        style="min-width: 12rem"
                                    >
                                        <template #body="{ data }">
                                            <span>{{
                                                data.version || '-'
                                            }}</span>
                                        </template>
                                    </Column>
                                    <Column
                                        field="date_debut"
                                        header="Date Début"
                                        sortable
                                        style="min-width: 12rem"
                                    >
                                        <template #body="{ data }">
                                            <Tag
                                                :value="
                                                    formatDate(data.date_debut)
                                                "
                                                icon="pi pi-calendar"
                                            />
                                        </template>
                                    </Column>
                                    <Column
                                        field="date_fin"
                                        header="Date Fin"
                                        sortable
                                        style="min-width: 12rem"
                                    >
                                        <template #body="{ data }">
                                            <Tag
                                                :value="
                                                    formatDate(data.date_fin)
                                                "
                                                icon="pi pi-calendar"
                                            />
                                        </template>
                                    </Column>
                                    <Column
                                        field="statut"
                                        header="Statut"
                                        sortable
                                        style="min-width: 12rem"
                                    >
                                        <template #body="{ data }">
                                            <Tag
                                                :value="data.statut || '-'"
                                                :severity="
                                                    getSeverity(data.statut)
                                                "
                                            />
                                        </template>
                                    </Column>
                                    <Column
                                        header="Actions"
                                        style="min-width: 15rem"
                                    >
                                        <template #body="{ data }">
                                            <div class="flex gap-1">
                                                <Button
                                                    icon="pi pi-eye"
                                                    class="p-button-rounded p-button-text p-button-info p-button-sm"
                                                    @click="
                                                        openDetailsProgramme(
                                                            data.id
                                                        )
                                                    "
                                                    v-tooltip="'Visionner'"
                                                />
                                                <Button
                                                    icon="pi pi-plus"
                                                    class="p-button-rounded p-button-text p-button-success p-button-sm"
                                                    @click="
                                                        openModuleForm(data.id)
                                                    "
                                                    v-tooltip="'Ajouter DocumentProgrammeSpecialite'"
                                                />
                                                <Button
                                                    icon="pi pi-pencil"
                                                    class="p-button-rounded p-button-text p-button-sm"
                                                    @click="editProgramme(data)"
                                                    v-tooltip="'Modifier'"
                                                />
                                                <Button
                                                    icon="pi pi-trash"
                                                    class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                                    @click="
                                                        confirmDeleteProgramme(
                                                            data
                                                        )
                                                    "
                                                    v-tooltip="'Supprimer'"
                                                />
                                                <Button
                                                    icon="pi pi-file-excel"
                                                    class="p-button-rounded p-button-text p-button-sm"
                                                    @click="
                                                        exportModules(data.id)
                                                    "
                                                    v-tooltip="
                                                        'Exporter Modules'
                                                    "
                                                />
                                                <Button
                                                    icon="pi pi-upload"
                                                    class="p-button-rounded p-button-text p-button-sm"
                                                    @click="
                                                        $refs[
                                                            'import-' + data.id
                                                        ].click()
                                                    "
                                                    v-tooltip="
                                                        'Importer Modules'
                                                    "
                                                />
                                                <input
                                                    type="file"
                                                    accept=".csv,.xlsx,.xls"
                                                    class="hidden"
                                                    :ref="'import-' + data.id"
                                                    @change="
                                                        importModules(
                                                            $event,
                                                            data.id
                                                        )
                                                    "
                                                />
                                            </div>
                                        </template>
                                    </Column>

                                    <!-- Expanded Row Template for Modules -->
                                    <template #expansion="{ data }">
                                        <div
                                            class="p-3 surface-100 border-round"
                                        >
                                            <div
                                                class="flex justify-content-between align-items-center mb-3"
                                            >
                                                <div
                                                    class="flex align-items-center gap-2"
                                                >
                                                    <i
                                                        class="pi pi-list text-primary-500"
                                                    />
                                                    <span class="font-semibold"
                                                        >Liste des modules</span
                                                    >
                                                </div>
                                                <Button
                                                    icon="pi pi-plus"
                                                    label="Ajouter Module"
                                                    class="p-button-sm p-button-success"
                                                    @click="
                                                        openModuleForm(data.id)
                                                    "
                                                />
                                            </div>

                                            <DataTable
                                                :value="data.modules || []"
                                                size="small"
                                                :loading="loading"
                                                dataKey="id"
                                                class="p-datatable-sm custom-datatable"
                                            >
                                                <Column
                                                    field="code"
                                                    header="Code"
                                                    style="min-width: 12rem"
                                                >
                                                    <template #body="{ data }">
                                                        <span>{{
                                                            data.code || '-'
                                                        }}</span>
                                                    </template>
                                                </Column>
                                                <Column
                                                    field="titre_module"
                                                    header="Titre Module"
                                                    style="min-width: 15rem"
                                                >
                                                    <template #body="{ data }">
                                                        <span>{{
                                                            data.titre_module ||
                                                            '-'
                                                        }}</span>
                                                    </template>
                                                </Column>
                                                <Column
                                                    field="type_module"
                                                    header="Type Module"
                                                    style="min-width: 12rem"
                                                >
                                                    <template #body="{ data }">
                                                        <span>{{
                                                            data.type_module ||
                                                            '-'
                                                        }}</span>
                                                    </template>
                                                </Column>
                                                <Column
                                                    field="heures_theoriques"
                                                    header="H. Théoriques"
                                                    style="min-width: 12rem"
                                                >
                                                    <template #body="{ data }">
                                                        <Chip
                                                            :label="
                                                                data.heures_theoriques ||
                                                                '0'
                                                            "
                                                        />
                                                    </template>
                                                </Column>
                                                <Column
                                                    field="heures_pratiques"
                                                    header="H. Pratiques"
                                                    style="min-width: 12rem"
                                                >
                                                    <template #body="{ data }">
                                                        <Chip
                                                            :label="
                                                                data.heures_pratiques ||
                                                                '0'
                                                            "
                                                        />
                                                    </template>
                                                </Column>
                                                <Column
                                                    field="heures_evaluation"
                                                    header="H. Évaluation"
                                                    style="min-width: 12rem"
                                                >
                                                    <template #body="{ data }">
                                                        <Chip
                                                            :label="
                                                                data.heures_evaluation ||
                                                                '0'
                                                            "
                                                        />
                                                    </template>
                                                </Column>
                                                <Column
                                                    header="H. Total"
                                                    style="min-width: 12rem"
                                                >
                                                    <template #body="{ data }">
                                                        <Badge
                                                            :value="
                                                                calculateTotalHours(
                                                                    data
                                                                )
                                                            "
                                                            severity="info"
                                                        />
                                                    </template>
                                                </Column>
                                                <Column
                                                    field="statut"
                                                    header="Statut"
                                                    style="min-width: 12rem"
                                                >
                                                    <template #body="{ data }">
                                                        <Tag
                                                            :value="
                                                                data.statut ||
                                                                '-'
                                                            "
                                                            :severity="
                                                                getSeverity(
                                                                    data.statut
                                                                )
                                                            "
                                                        />
                                                    </template>
                                                </Column>
                                                <Column
                                                    header="Actions"
                                                    style="min-width: 15rem"
                                                >
                                                    <template #body="{ data }">
                                                        <div class="flex gap-1">
                                                            <Button
                                                                icon="pi pi-eye"
                                                                class="p-button-rounded p-button-text p-button-info p-button-sm"
                                                                @click="
                                                                    openDetailsModule(
                                                                        data.id
                                                                    )
                                                                "
                                                                v-tooltip="
                                                                    'Prévisualiser'
                                                                "
                                                            />
                                                            <Button
                                                                icon="pi pi-file-pdf"
                                                                class="p-button-rounded p-button-text p-button-success p-button-sm"
                                                                @click="
                                                                    openDocsModuleForm(
                                                                        data.id
                                                                    )
                                                                "
                                                                v-tooltip="
                                                                    'Ajouter Document'
                                                                "
                                                            />
                                                            <Button
                                                                icon="pi pi-pencil"
                                                                class="p-button-rounded p-button-text p-button-sm"
                                                                @click="
                                                                    editModule(
                                                                        data
                                                                    )
                                                                "
                                                                v-tooltip="
                                                                    'Modifier'
                                                                "
                                                            />
                                                            <Button
                                                                icon="pi pi-trash"
                                                                class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                                                @click="
                                                                    confirmDeleteModule(
                                                                        data
                                                                    )
                                                                "
                                                                v-tooltip="
                                                                    'Supprimer'
                                                                "
                                                            />
                                                        </div>
                                                    </template>
                                                </Column>
                                            </DataTable>
                                        </div>
                                    </template>
                                </DataTable>
                            </div>
                        </template>
                    </DataTable>
                </TabPanel>

                <!-- Onglet Mémoires -->
                <TabPanel header="Mémoires">
                    <!-- Table Header with Actions -->
                    <div
                        class="flex justify-content-between align-items-center mb-4"
                    >
                        <div class="flex align-items-center gap-2">
                            <span class="p-input-icon-left search-field">
                                <i class="pi pi-search" />
                                <InputText
                                    v-model="filtersMemoires['global'].value"
                                    placeholder="Rechercher..."
                                />
                            </span>
                            <Button
                                icon="pi pi-filter-slash"
                                class="p-button-outlined p-button-secondary"
                                size="small"
                                @click="clearFilterMemoires"
                                v-tooltip="'Réinitialiser les filtres'"
                            />
                        </div>
                    </div>

                    <!-- Main DataTable for Diplomes -->
                    <DataTable
                        v-model:expandedRows="expandedDiplomes"
                        v-model:filters="filtersMemoires"
                        :value="diplomes"
                        dataKey="id"
                        size="small"
                        stripedRows
                        paginator
                        :rows="10"
                        :rowsPerPageOptions="[10, 25, 50]"
                        filterDisplay="menu"
                        :globalFilterFields="['nom_fr', 'nombre_memoires']"
                        :loading="loading"
                        scrollable
                        scrollHeight="600px"
                        removableSort
                        class="p-datatable-sm border-1 surface-border custom-datatable"
                    >
                        <template #empty>
                            <div class="text-center py-4">
                                <i class="pi pi-inbox text-4xl text-400 mb-2" />
                                <p class="text-600">Aucun diplôme trouvé</p>
                            </div>
                        </template>

                        <Column expander style="width: 3rem" />
                        <Column
                            field="nom_fr"
                            header="Diplôme"
                            sortable
                            style="min-width: 15rem"
                            frozen
                        >
                            <template #body="{ data }">
                                <span class="font-medium">{{
                                    data.nom_fr || '-'
                                }}</span>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <InputText
                                    v-model="filterModel.value"
                                    type="text"
                                    class="p-column-filter"
                                    placeholder="Rechercher par diplôme"
                                    @input="filterCallback"
                                />
                            </template>
                        </Column>
                        <Column
                            field="nombre_memoires"
                            header="Nombre de Mémoires"
                            sortable
                            style="min-width: 15rem"
                        >
                            <template #body="{ data }">
                                <Chip :label="data.nombre_memoires || '0'" />
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <InputText
                                    v-model="filterModel.value"
                                    type="text"
                                    class="p-column-filter"
                                    placeholder="Rechercher par nombre"
                                    @input="filterCallback"
                                />
                            </template>
                        </Column>
                        <Column
                            header="Actions"
                            :exportable="false"
                            style="min-width: 12rem"
                        >
                            <template #body="{ data }">
                                <div class="flex gap-1">
                                    <Button
                                        icon="pi pi-plus"
                                        class="p-button-rounded p-button-text p-button-success p-button-sm"
                                        @click="openMemoireForm(data.id)"
                                        v-tooltip="'Ajouter Mémoire'"
                                    />
                                    <Button
                                        icon="pi pi-eye"
                                        class="p-button-rounded p-button-text p-button-info p-button-sm"
                                        @click="openDetailsMemDiplome(data.id)"
                                        v-tooltip="'Visionner'"
                                    />
                                </div>
                            </template>
                        </Column>

                        <!-- Expanded Row Template for Memoires -->
                        <template #expansion="{ data }">
                            <div class="p-3 surface-50 border-round">
                                <div
                                    class="flex justify-content-between align-items-center mb-3"
                                >
                                    <div class="flex align-items-center gap-2">
                                        <i
                                            class="pi pi-file text-primary-500"
                                        />
                                        <span class="font-semibold"
                                            >Liste des mémoires de
                                            {{ data.nom_fr }}</span
                                        >
                                    </div>
                                    <Button
                                        icon="pi pi-plus"
                                        label="Ajouter Module Général"
                                        class="p-button-sm p-button-success"
                                        @click="
                                            openModuleGeneralForm(null, data.id)
                                        "
                                    />
                                </div>

                                <DataTable
                                    v-model:expandedRows="expandedMemoires"
                                    :value="data.memoires || []"
                                    dataKey="id"
                                    size="small"
                                    :loading="loading"
                                    class="p-datatable-sm custom-datatable"
                                >
                                    <Column expander style="width: 3rem" />
                                    <Column
                                        field="reference"
                                        header="Référence"
                                        sortable
                                        style="min-width: 12rem"
                                    >
                                        <template #body="{ data }">
                                            <span>{{
                                                data.reference || '-'
                                            }}</span>
                                        </template>
                                    </Column>
                                    <Column
                                        field="numero_memoire"
                                        header="Numéro Mémoire"
                                        sortable
                                        style="min-width: 12rem"
                                    >
                                        <template #body="{ data }">
                                            <span>{{
                                                data.numero_memoire || '-'
                                            }}</span>
                                        </template>
                                    </Column>
                                    <Column
                                        field="date_memoire"
                                        header="Date Mémoire"
                                        sortable
                                        style="min-width: 12rem"
                                    >
                                        <template #body="{ data }">
                                            <Tag
                                                :value="
                                                    formatDate(
                                                        data.date_memoire
                                                    )
                                                "
                                                icon="pi pi-calendar"
                                            />
                                        </template>
                                    </Column>
                                    <Column
                                        field="date_debut"
                                        header="Date Début"
                                        sortable
                                        style="min-width: 12rem"
                                    >
                                        <template #body="{ data }">
                                            <Tag
                                                :value="
                                                    formatDate(data.date_debut)
                                                "
                                                icon="pi pi-calendar"
                                            />
                                        </template>
                                    </Column>
                                    <Column
                                        field="date_fin"
                                        header="Date Fin"
                                        sortable
                                        style="min-width: 12rem"
                                    >
                                        <template #body="{ data }">
                                            <Tag
                                                :value="
                                                    formatDate(data.date_fin)
                                                "
                                                icon="pi pi-calendar"
                                            />
                                        </template>
                                    </Column>
                                    <Column
                                        field="statut"
                                        header="Statut"
                                        sortable
                                        style="min-width: 12rem"
                                    >
                                        <template #body="{ data }">
                                            <Tag
                                                :value="data.statut || '-'"
                                                :severity="
                                                    getSeverity(data.statut)
                                                "
                                            />
                                        </template>
                                    </Column>
                                    <Column
                                        header="Actions"
                                        style="min-width: 15rem"
                                    >
                                        <template #body="{ data }">
                                            <div class="flex gap-1">
                                                <Button
                                                    icon="pi pi-eye"
                                                    class="p-button-rounded p-button-text p-button-info p-button-sm"
                                                    @click="
                                                        openDetailsMemoire(
                                                            data.id
                                                        )
                                                    "
                                                    v-tooltip="'Visionner'"
                                                />
                                                <Button
                                                    icon="pi pi-plus"
                                                    class="p-button-rounded p-button-text p-button-success p-button-sm"
                                                    @click="
                                                        openModuleGeneralForm(
                                                            data.id
                                                        )
                                                    "
                                                    v-tooltip="
                                                        'Ajouter DocumentProgrammeSpecialite Général'
                                                    "
                                                />
                                                <Button
                                                    icon="pi pi-pencil"
                                                    class="p-button-rounded p-button-text p-button-sm"
                                                    @click="editMemoire(data)"
                                                    v-tooltip="'Modifier'"
                                                />
                                                <Button
                                                    icon="pi pi-trash"
                                                    class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                                    @click="
                                                        confirmDeleteMemoire(
                                                            data
                                                        )
                                                    "
                                                    v-tooltip="'Supprimer'"
                                                />
                                                <Button
                                                    icon="pi pi-file-excel"
                                                    class="p-button-rounded p-button-text p-button-sm"
                                                    @click="
                                                        exportModulesGeneraux(
                                                            data.id
                                                        )
                                                    "
                                                    v-tooltip="
                                                        'Exporter Modules Généraux'
                                                    "
                                                />
                                                <Button
                                                    icon="pi pi-upload"
                                                    class="p-button-rounded p-button-text p-button-sm"
                                                    @click="
                                                        $refs[
                                                            'import-mg-' +
                                                                data.id
                                                        ].click()
                                                    "
                                                    v-tooltip="
                                                        'Importer Modules Généraux'
                                                    "
                                                />
                                                <input
                                                    type="file"
                                                    accept=".csv,.xlsx,.xls"
                                                    class="hidden"
                                                    :ref="
                                                        'import-mg-' + data.id
                                                    "
                                                    @change="
                                                        importModulesGeneraux(
                                                            $event,
                                                            data.id
                                                        )
                                                    "
                                                />
                                            </div>
                                        </template>
                                    </Column>

                                    <!-- Expanded Row Template for Modules Généraux -->
                                    <template #expansion="{ data }">
                                        <div
                                            class="p-3 surface-100 border-round"
                                        >
                                            <div
                                                class="flex justify-content-between align-items-center mb-3"
                                            >
                                                <div
                                                    class="flex align-items-center gap-2"
                                                >
                                                    <i
                                                        class="pi pi-list text-primary-500"
                                                    />
                                                    <span class="font-semibold"
                                                        >Liste des modules
                                                        généraux</span
                                                    >
                                                </div>
                                                <Button
                                                    icon="pi pi-plus"
                                                    label="Ajouter Module Général"
                                                    class="p-button-sm p-button-success"
                                                    @click="
                                                        openModuleGeneralForm(
                                                            data.id
                                                        )
                                                    "
                                                />
                                            </div>

                                            <DataTable
                                                :value="
                                                    data.modules_generaux || []
                                                "
                                                size="small"
                                                :loading="loading"
                                                dataKey="id"
                                                class="p-datatable-sm custom-datatable"
                                            >
                                                <Column
                                                    field="code"
                                                    header="Code"
                                                    style="min-width: 12rem"
                                                >
                                                    <template #body="{ data }">
                                                        <span>{{
                                                            data.code || '-'
                                                        }}</span>
                                                    </template>
                                                </Column>
                                                <Column
                                                    field="titre_module"
                                                    header="Titre Module"
                                                    style="min-width: 15rem"
                                                >
                                                    <template #body="{ data }">
                                                        <span>{{
                                                            data.titre_module ||
                                                            '-'
                                                        }}</span>
                                                    </template>
                                                </Column>
                                                <Column
                                                    field="heures_theoriques"
                                                    header="H. Théoriques"
                                                    style="min-width: 12rem"
                                                >
                                                    <template #body="{ data }">
                                                        <Chip
                                                            :label="
                                                                data.heures_theoriques ||
                                                                '0'
                                                            "
                                                        />
                                                    </template>
                                                </Column>
                                                <Column
                                                    field="heures_pratiques"
                                                    header="H. Pratiques"
                                                    style="min-width: 12rem"
                                                >
                                                    <template #body="{ data }">
                                                        <Chip
                                                            :label="
                                                                data.heures_pratiques ||
                                                                '0'
                                                            "
                                                        />
                                                    </template>
                                                </Column>
                                                <Column
                                                    field="heures_evaluation"
                                                    header="H. Évaluation"
                                                    style="min-width: 12rem"
                                                >
                                                    <template #body="{ data }">
                                                        <Chip
                                                            :label="
                                                                data.heures_evaluation ||
                                                                '0'
                                                            "
                                                        />
                                                    </template>
                                                </Column>
                                                <Column
                                                    header="H. Total"
                                                    style="min-width: 12rem"
                                                >
                                                    <template #body="{ data }">
                                                        <Badge
                                                            :value="
                                                                calculateTotalHours(
                                                                    data
                                                                )
                                                            "
                                                            severity="info"
                                                        />
                                                    </template>
                                                </Column>
                                                <Column
                                                    field="statut"
                                                    header="Statut"
                                                    style="min-width: 12rem"
                                                >
                                                    <template #body="{ data }">
                                                        <Tag
                                                            :value="
                                                                data.statut ||
                                                                '-'
                                                            "
                                                            :severity="
                                                                getSeverity(
                                                                    data.statut
                                                                )
                                                            "
                                                        />
                                                    </template>
                                                </Column>
                                                <Column
                                                    header="Actions"
                                                    style="min-width: 15rem"
                                                >
                                                    <template #body="{ data }">
                                                        <div class="flex gap-1">
                                                            <Button
                                                                icon="pi pi-eye"
                                                                class="p-button-rounded p-button-text p-button-info p-button-sm"
                                                                @click="
                                                                    openDetailsModuleGeneral(
                                                                        data.id
                                                                    )
                                                                "
                                                                v-tooltip="
                                                                    'Prévisualiser'
                                                                "
                                                            />
                                                            <Button
                                                                icon="pi pi-file-pdf"
                                                                class="p-button-rounded p-button-text p-button-success p-button-sm"
                                                                @click="
                                                                    openDocsModuleGeneralForm(
                                                                        data.id
                                                                    )
                                                                "
                                                                v-tooltip="
                                                                    'Ajouter Document'
                                                                "
                                                            />
                                                            <Button
                                                                icon="pi pi-pencil"
                                                                class="p-button-rounded p-button-text p-button-sm"
                                                                @click="
                                                                    editModuleGeneral(
                                                                        data
                                                                    )
                                                                "
                                                                v-tooltip="
                                                                    'Modifier'
                                                                "
                                                            />
                                                            <Button
                                                                icon="pi pi-trash"
                                                                class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                                                @click="
                                                                    confirmDeleteModuleGeneral(
                                                                        data
                                                                    )
                                                                "
                                                                v-tooltip="
                                                                    'Supprimer'
                                                                "
                                                            />
                                                        </div>
                                                    </template>
                                                </Column>
                                            </DataTable>
                                        </div>
                                    </template>
                                </DataTable>
                            </div>
                        </template>
                    </DataTable>
                </TabPanel>

                <!-- Onglet Programmes d'Études -->
                <TabPanel header="Programmes d'Études">
                    <!-- Filters -->
                    <div
                        class="flex justify-content-between align-items-center mb-4"
                    >
                        <div class="flex align-items-center gap-2">
                            <span class="p-input-icon-left search-field">
                                <i class="pi pi-search" />
                                <InputText
                                    v-model="
                                        filtersProgrammesEtudes['global'].value
                                    "
                                    placeholder="Rechercher..."
                                />
                            </span>
                            <Button
                                icon="pi pi-filter-slash"
                                class="p-button-outlined p-button-secondary"
                                size="small"
                                @click="clearFilterProgrammesEtudes"
                                v-tooltip="'Réinitialiser les filtres'"
                            />
                            <Dropdown
                                v-model="standardisationFilter"
                                :options="standardisationOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Filtrer par standardisation"
                                class="w-12rem"
                                @change="filterProgrammesEtudes"
                            />
                        </div>
                    </div>

                    <!-- Main DataTable for Specialites -->
                    <DataTable
                        v-model:expandedRows="expandedRowsEtudes"
                        v-model:filters="filtersProgrammesEtudes"
                        :value="specialites"
                        dataKey="id"
                        size="small"
                        stripedRows
                        paginator
                        :rows="10"
                        :rowsPerPageOptions="[10, 25, 50]"
                        filterDisplay="menu"
                        :globalFilterFields="[
                            'code',
                            'nom_fr',
                            'nom_ar',
                            'diplome_fr',
                        ]"
                        :loading="loading"
                        scrollable
                        scrollHeight="600px"
                        removableSort
                        class="p-datatable-sm border-1 surface-border custom-datatable"
                    >
                        <template #empty>
                            <div class="text-center py-4">
                                <i class="pi pi-inbox text-4xl text-400 mb-2" />
                                <p class="text-600">
                                    Aucune spécialité trouvée
                                </p>
                            </div>
                        </template>

                        <Column expander style="width: 3rem" />
                        <Column
                            field="code"
                            header="Code"
                            sortable
                            style="min-width: 10rem"
                            frozen
                        >
                            <template #body="{ data }">
                                <span class="font-medium">{{
                                    data.code || '-'
                                }}</span>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <InputText
                                    v-model="filterModel.value"
                                    type="text"
                                    class="p-column-filter"
                                    placeholder="Rechercher par code"
                                    @input="filterCallback"
                                />
                            </template>
                        </Column>
                        <Column
                            field="nom_fr"
                            header="Nom (FR)"
                            sortable
                            style="min-width: 15rem"
                        >
                            <template #body="{ data }">
                                <span>{{ data.nom_fr || '-' }}</span>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <InputText
                                    v-model="filterModel.value"
                                    type="text"
                                    class="p-column-filter"
                                    placeholder="Rechercher par nom (FR)"
                                    @input="filterCallback"
                                />
                            </template>
                        </Column>
                        <Column
                            field="nom_ar"
                            header="Nom (AR)"
                            sortable
                            style="min-width: 15rem"
                        >
                            <template #body="{ data }">
                                <span>{{ data.nom_ar || '-' }}</span>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <InputText
                                    v-model="filterModel.value"
                                    type="text"
                                    class="p-column-filter"
                                    placeholder="Rechercher par nom (AR)"
                                    @input="filterCallback"
                                />
                            </template>
                        </Column>
                        <Column
                            field="diplome_fr"
                            header="Diplôme"
                            sortable
                            style="min-width: 12rem"
                        >
                            <template #body="{ data }">
                                <Tag :value="data.diplome_fr || '-'" />
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <InputText
                                    v-model="filterModel.value"
                                    type="text"
                                    class="p-column-filter"
                                    placeholder="Rechercher par diplôme"
                                    @input="filterCallback"
                                />
                            </template>
                        </Column>
                        <Column
                            header="Actions"
                            :exportable="false"
                            style="min-width: 12rem"
                        >
                            <template #body="{ data }">
                                <div class="flex gap-1">
                                    <Button
                                        icon="pi pi-lock"
                                        class="p-button-rounded p-button-text p-button-sm"
                                        :class="{
                                            'p-button-info':
                                                frozenRowsEtudes.includes(
                                                    data.id
                                                ),
                                        }"
                                        @click="toggleFreeze(data.id, 'etudes')"
                                        v-tooltip="'Fixer la ligne'"
                                    />
                                    <Button
                                        icon="pi pi-plus"
                                        class="p-button-rounded p-button-text p-button-success p-button-sm"
                                        @click="openProgrammeEtudeForm(data.id)"
                                        v-tooltip="'Ajouter Programme d\'Étude'"
                                    />
                                    <Button
                                        icon="pi pi-eye"
                                        class="p-button-rounded p-button-text p-button-info p-button-sm"
                                        @click="
                                            openDetailsProgEtudeSpe(data.id)
                                        "
                                        v-tooltip="'Visionner'"
                                    />
                                </div>
                            </template>
                        </Column>

                        <!-- Expanded Row Template for Programmes d'Études -->
                        <template #expansion="{ data }">
                            <div class="p-3 surface-50 border-round">
                                <div
                                    class="flex justify-content-between align-items-center mb-3"
                                >
                                    <div class="flex align-items-center gap-2">
                                        <i
                                            class="pi pi-sitemap text-primary-500"
                                        />
                                        <span class="font-semibold"
                                            >Liste des programmes d'études de
                                            {{ data.nom_fr }}</span
                                        >
                                    </div>
                                    <Button
                                        icon="pi pi-plus"
                                        label="Ajouter Programme d'Étude"
                                        class="p-button-sm p-button-success"
                                        @click="openProgrammeEtudeForm(data.id)"
                                    />
                                </div>

                                <DataTable
                                    :value="data.programmes_etudes || []"
                                    dataKey="id"
                                    size="small"
                                    :loading="loading"
                                    class="p-datatable-sm custom-datatable"
                                >
                                    <Column
                                        field="version"
                                        header="Version Programme d'Étude"
                                        sortable
                                        style="min-width: 15rem"
                                    >
                                        <template #body="{ data }">
                                            <span>{{
                                                data.version || '-'
                                            }}</span>
                                        </template>
                                    </Column>
                                    <Column
                                        field="programme.version"
                                        header="Version Programme"
                                        sortable
                                        style="min-width: 12rem"
                                    >
                                        <template #body="{ data }">
                                            <span>{{
                                                data.programme
                                                    ? data.programme.version
                                                    : '-'
                                            }}</span>
                                        </template>
                                    </Column>
                                    <Column
                                        field="memoire.reference"
                                        header="Référence Mémoire"
                                        sortable
                                        style="min-width: 12rem"
                                    >
                                        <template #body="{ data }">
                                            <span>{{
                                                data.memoire
                                                    ? data.memoire.reference
                                                    : '-'
                                            }}</span>
                                        </template>
                                    </Column>
                                    <Column
                                        field="date_debut"
                                        header="Date Début"
                                        sortable
                                        style="min-width: 12rem"
                                    >
                                        <template #body="{ data }">
                                            <Tag
                                                :value="
                                                    formatDate(data.date_debut)
                                                "
                                                icon="pi pi-calendar"
                                            />
                                        </template>
                                    </Column>
                                    <Column
                                        field="date_fin"
                                        header="Date Fin"
                                        sortable
                                        style="min-width: 12rem"
                                    >
                                        <template #body="{ data }">
                                            <Tag
                                                :value="
                                                    formatDate(data.date_fin)
                                                "
                                                icon="pi pi-calendar"
                                            />
                                        </template>
                                    </Column>
                                    <Column
                                        field="description"
                                        header="Description"
                                        sortable
                                        style="min-width: 15rem"
                                    >
                                        <template #body="{ data }">
                                            <span>{{
                                                data.description || '-'
                                            }}</span>
                                        </template>
                                    </Column>
                                    <Column
                                        field="statut"
                                        header="Statut"
                                        sortable
                                        style="min-width: 12rem"
                                    >
                                        <template #body="{ data }">
                                            <Tag
                                                :value="data.statut || '-'"
                                                :severity="
                                                    getSeverity(data.statut)
                                                "
                                            />
                                        </template>
                                    </Column>
                                    <Column
                                        header="Actions"
                                        style="min-width: 15rem"
                                    >
                                        <template #body="{ data }">
                                            <div class="flex gap-1">
                                                <Button
                                                    icon="pi pi-eye"
                                                    class="p-button-rounded p-button-text p-button-info p-button-sm"
                                                    @click="
                                                        openDetailsProgEtude(
                                                            data.id
                                                        )
                                                    "
                                                    v-tooltip="'Visionner'"
                                                />
                                                <Button
                                                    icon="pi pi-plus"
                                                    class="p-button-rounded p-button-text p-button-success p-button-sm"
                                                    @click="
                                                        openProgrammeEtudeForm(
                                                            data.specialite_id
                                                        )
                                                    "
                                                    v-tooltip="
                                                        'Ajouter Programme d\'Étude'
                                                    "
                                                />
                                                <Button
                                                    icon="pi pi-pencil"
                                                    class="p-button-rounded p-button-text p-button-sm"
                                                    @click="
                                                        editProgrammeEtude(data)
                                                    "
                                                    v-tooltip="'Modifier'"
                                                />
                                                <Button
                                                    icon="pi pi-trash"
                                                    class="p-button-rounded p-button-text p-button-danger p-button-sm"
                                                    @click="
                                                        confirmDeleteProgrammeEtude(
                                                            data
                                                        )
                                                    "
                                                    v-tooltip="'Supprimer'"
                                                />
                                            </div>
                                        </template>
                                    </Column>
                                </DataTable>
                            </div>
                        </template>
                    </DataTable>
                </TabPanel>
            </TabView>
        </div>

        <!-- Modals -->
        <ProgrammeForm
            :visible="formVisible.programme"
            :specialiteId="selectedSpecialiteId"
            :programmeToEdit="programmeToEdit"
            @update:visible="formVisible.programme = $event"
            @save="handleSaveProgramme"
            @update="handleUpdateProgramme"
            @close="closeProgrammeForm"
        />
        <ModuleForm
            :visible="formVisible.module"
            :programmeId="selectedProgrammeId"
            :specialiteId="selectedSpecialiteId"
            :moduleToEdit="moduleToEdit"
            @update:visible="formVisible.module = $event"
            @save="handleSaveModule"
            @update="handleUpdateModule"
            @close="closeModuleForm"
        />
        <DetailsProgSpecialite
            :visible="formVisible.detailsProgSpecialite"
            :specialiteId="selectedSpecialiteId"
            @update:visible="formVisible.detailsProgSpecialite = $event"
            @close="formVisible.detailsProgSpecialite = false"
        />
        <DetailsProgramme
            :visible="formVisible.detailsProgramme"
            :programmeId="selectedProgrammeId"
            @update:visible="formVisible.detailsProgramme = $event"
            @close="formVisible.detailsProgramme = false"
        />
        <DetailsModule
            :visible="formVisible.detailsModule"
            :moduleId="selectedModuleId"
            @update:visible="formVisible.detailsModule = $event"
            @close="formVisible.detailsModule = false"
        />
        <DocsModuleForm
            :visible="formVisible.docsModule"
            :moduleId="selectedModuleId"
            @update:visible="formVisible.docsModule = $event"
            @save="handleSaveDocsModule"
            @close="formVisible.docsModule = false"
        />
        <MemoireForm
            :visible="formVisible.memoire"
            :diplomeId="selectedDiplomeId"
            :memoireToEdit="memoireToEdit"
            @update:visible="formVisible.memoire = $event"
            @save="handleSaveMemoire"
            @update="handleUpdateMemoire"
            @close="closeMemoireForm"
        />
        <DetailsMemDiplome
            :visible="formVisible.detailsMemDiplome"
            :diplomeId="selectedDiplomeId"
            @update:visible="formVisible.detailsMemDiplome = $event"
            @close="formVisible.detailsMemDiplome = false"
        />
        <DetailsMemoire
            :visible="formVisible.detailsMemoire"
            :memoireId="selectedMemoireId"
            @update:visible="formVisible.detailsMemoire = $event"
            @close="formVisible.detailsMemoire = false"
        />
        <ModuleGeneralForm
            :visible="formVisible.moduleGeneral"
            :memoireId="selectedMemoireId"
            :diplomeId="selectedDiplomeId"
            :moduleToEdit="moduleGeneralToEdit"
            @update:visible="formVisible.moduleGeneral = $event"
            @save="handleSaveModuleGeneral"
            @update="handleUpdateModuleGeneral"
            @close="closeModuleGeneralForm"
        />
        <DetailsModuleGeneral
            :visible="formVisible.detailsModuleGeneral"
            :moduleId="selectedModuleId"
            @update:visible="formVisible.detailsModuleGeneral = $event"
            @close="formVisible.detailsModuleGeneral = false"
        />
        <DocsModuleGeneralForm
            :visible="formVisible.docsModuleGeneral"
            :moduleId="selectedModuleId"
            @update:visible="formVisible.docsModuleGeneral = $event"
            @save="handleSaveDocsModuleGeneral"
            @close="formVisible.docsModuleGeneral = false"
        />
        <ProgrammeEtudeForm
            :visible="formVisible.programmeEtude"
            :specialiteId="selectedSpecialiteId"
            :programmeEtudeToEdit="programmeEtudeToEdit"
            @update:visible="formVisible.programmeEtude = $event"
            @save="handleSaveProgrammeEtude"
            @update="handleUpdateProgrammeEtude"
            @close="closeProgrammeEtudeForm"
        />
        <DetailsProgEtudeSpe
            :visible="formVisible.detailsProgEtudeSpe"
            :specialiteId="selectedSpecialiteId"
            @update:visible="formVisible.detailsProgEtudeSpe = $event"
            @close="formVisible.detailsProgEtudeSpe = false"
        />
        <DetailsProgEtude
            :visible="formVisible.detailsProgEtude"
            :programmeEtudeId="selectedProgrammeEtudeId"
            @update:visible="formVisible.detailsProgEtude = $event"
            @close="formVisible.detailsProgEtude = false"
        />

        <!-- Delete Confirmation Dialogs -->
        <Dialog
            v-model:visible="deleteFormVisible.programme"
            header="Confirmer la suppression"
            :style="{ width: '450px' }"
            :modal="true"
        >
            <div class="confirmation-content flex align-items-center gap-3">
                <i class="pi pi-exclamation-triangle text-4xl text-red-500" />
                <span>
                    Voulez-vous vraiment supprimer le programme
                    <b>{{ programmeToDelete?.version }}</b> ? Cette action est
                    irréversible.
                </span>
            </div>
            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="deleteFormVisible.programme = false"
                />
                <Button
                    label="Supprimer"
                    icon="pi pi-check"
                    class="p-button-danger"
                    @click="deleteProgramme"
                />
            </template>
        </Dialog>

        <Dialog
            v-model:visible="deleteFormVisible.module"
            header="Confirmer la suppression"
            :style="{ width: '450px' }"
            :modal="true"
        >
            <div class="confirmation-content flex align-items-center gap-3">
                <i class="pi pi-exclamation-triangle text-4xl text-red-500" />
                <span>
                    Voulez-vous vraiment supprimer le module
                    <b>{{ moduleToDelete?.code }}</b> ? Cette action est
                    irréversible.
                </span>
            </div>
            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="deleteFormVisible.module = false"
                />
                <Button
                    label="Supprimer"
                    icon="pi pi-check"
                    class="p-button-danger"
                    @click="deleteModule"
                />
            </template>
        </Dialog>

        <Dialog
            v-model:visible="deleteFormVisible.memoire"
            header="Confirmer la suppression"
            :style="{ width: '450px' }"
            :modal="true"
        >
            <div class="confirmation-content flex align-items-center gap-3">
                <i class="pi pi-exclamation-triangle text-4xl text-red-500" />
                <span>
                    Voulez-vous vraiment supprimer le mémoire
                    <b>{{ memoireToDelete?.reference }}</b> ? Cette action est
                    irréversible.
                </span>
            </div>
            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="deleteFormVisible.memoire = false"
                />
                <Button
                    label="Supprimer"
                    icon="pi pi-check"
                    class="p-button-danger"
                    @click="deleteMemoire"
                />
            </template>
        </Dialog>

        <Dialog
            v-model:visible="deleteFormVisible.moduleGeneral"
            header="Confirmer la suppression"
            :style="{ width: '450px' }"
            :modal="true"
        >
            <div class="confirmation-content flex align-items-center gap-3">
                <i class="pi pi-exclamation-triangle text-4xl text-red-500" />
                <span>
                    Voulez-vous vraiment supprimer le module général
                    <b>{{ moduleGeneralToDelete?.code }}</b> ? Cette action est
                    irréversible.
                </span>
            </div>
            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="deleteFormVisible.moduleGeneral = false"
                />
                <Button
                    label="Supprimer"
                    icon="pi pi-check"
                    class="p-button-danger"
                    @click="deleteModuleGeneral"
                />
            </template>
        </Dialog>

        <Dialog
            v-model:visible="deleteFormVisible.programmeEtude"
            header="Confirmer la suppression"
            :style="{ width: '450px' }"
            :modal="true"
        >
            <div class="confirmation-content flex align-items-center gap-3">
                <i class="pi pi-exclamation-triangle text-4xl text-red-500" />
                <span>
                    Voulez-vous vraiment supprimer le programme d'étude
                    <b>{{ programmeEtudeToDelete?.version }}</b> ? Cette action
                    est irréversible.
                </span>
            </div>
            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="deleteFormVisible.programmeEtude = false"
                />
                <Button
                    label="Supprimer"
                    icon="pi pi-check"
                    class="p-button-danger"
                    @click="deleteProgrammeEtude"
                />
            </template>
        </Dialog>

        <!-- Toast Notification -->
        <Toast position="top-right" />
    </div>
</template>

<script>
import { FilterMatchMode } from 'primevue/api';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

// Components
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Chip from 'primevue/chip';
import Badge from 'primevue/badge';
import Dialog from 'primevue/dialog';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Dropdown from 'primevue/dropdown';
import Toast from 'primevue/toast';
import ProgrammeForm from './ProgrammeForm.vue';
import ModuleForm from './ModuleForm.vue';
import DetailsProgSpecialite from './DetailsProgSpecialite.vue';
import DetailsProgramme from './DetailsProgramme.vue';
import DetailsModule from './DetailsModule.vue';
import DocsModuleForm from './DocsModuleForm.vue';
import MemoireForm from './MemoireForm.vue';
import DetailsMemDiplome from './DetailsMemDiplome.vue';
import DetailsMemoire from './DetailsMemoire.vue';
import ModuleGeneralForm from './ModuleGeneralForm.vue';
import DetailsModuleGeneral from './DetailsModuleGeneral.vue';
import DocsModuleGeneralForm from './DocsModuleGeneralForm.vue';
import ProgrammeEtudeForm from './ProgrammeEtudeForm.vue';
import DetailsProgEtudeSpe from './DetailsProgEtudeSpe.vue';
import DetailsProgEtude from './DetailsProgEtude.vue';

export default {
    components: {
        Button,
        DataTable,
        Column,
        InputText,
        Tag,
        Chip,
        Badge,
        Dialog,
        TabView,
        TabPanel,
        Dropdown,
        Toast,
        ProgrammeForm,
        ModuleForm,
        DetailsProgSpecialite,
        DetailsProgramme,
        DetailsModule,
        DocsModuleForm,
        MemoireForm,
        DetailsMemDiplome,
        DetailsMemoire,
        ModuleGeneralForm,
        DetailsModuleGeneral,
        DocsModuleGeneralForm,
        ProgrammeEtudeForm,
        DetailsProgEtudeSpe,
        DetailsProgEtude,
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            activeTab: 0,
            specialites: [],
            diplomes: [],
            filters: null,
            filtersMemoires: null,
            filtersProgrammesEtudes: null,
            standardisationFilter: 'all',
            standardisationOptions: [
                { label: 'Tous', value: 'all' },
                { label: 'مقيس', value: 'مقيس' },
                { label: 'غير مقيس', value: 'غير مقيس' },
                { label: 'جديد', value: 'جديد' },
            ],
            expandedRows: [],
            expandedProgrammes: [],
            expandedDiplomes: [],
            expandedMemoires: [],
            expandedRowsEtudes: [],
            frozenRows: [],
            frozenRowsEtudes: [],
            formVisible: {
                programme: false,
                module: false,
                detailsProgSpecialite: false,
                detailsProgramme: false,
                detailsModule: false,
                docsModule: false,
                memoire: false,
                detailsMemDiplome: false,
                detailsMemoire: false,
                moduleGeneral: false,
                detailsModuleGeneral: false,
                docsModuleGeneral: false,
                programmeEtude: false,
                detailsProgEtudeSpe: false,
                detailsProgEtude: false,
            },
            deleteFormVisible: {
                programme: false,
                module: false,
                memoire: false,
                moduleGeneral: false,
                programmeEtude: false,
            },
            selectedSpecialiteId: null,
            selectedProgrammeId: null,
            selectedModuleId: null,
            selectedDiplomeId: null,
            selectedMemoireId: null,
            selectedProgrammeEtudeId: null,
            programmeToEdit: null,
            moduleToEdit: null,
            memoireToEdit: null,
            moduleGeneralToEdit: null,
            programmeEtudeToEdit: null,
            programmeToDelete: null,
            moduleToDelete: null,
            memoireToDelete: null,
            moduleGeneralToDelete: null,
            programmeEtudeToDelete: null,
            loading: false,
        };
    },
    created() {
        this.initFilters();
        this.fetchData();
    },
    methods: {
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                nom_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_ar: { value: null, matchMode: FilterMatchMode.CONTAINS },
                diplome_fr: {
                    value: null,
                    matchMode: FilterMatchMode.CONTAINS,
                },
            };
            this.filtersMemoires = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nombre_memoires: {
                    value: null,
                    matchMode: FilterMatchMode.EQUALS,
                },
            };
            this.filtersProgrammesEtudes = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                nom_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_ar: { value: null, matchMode: FilterMatchMode.CONTAINS },
                diplome_fr: {
                    value: null,
                    matchMode: FilterMatchMode.CONTAINS,
                },
            };
        },
        clearFilter() {
            this.initFilters();
        },
        clearFilterMemoires() {
            this.filtersMemoires = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nombre_memoires: {
                    value: null,
                    matchMode: FilterMatchMode.EQUALS,
                },
            };
        },
        clearFilterProgrammesEtudes() {
            this.filtersProgrammesEtudes = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                code: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                nom_fr: { value: null, matchMode: FilterMatchMode.CONTAINS },
                nom_ar: { value: null, matchMode: FilterMatchMode.CONTAINS },
                diplome_fr: {
                    value: null,
                    matchMode: FilterMatchMode.CONTAINS,
                },
            };
            this.standardisationFilter = 'all';
            this.filterProgrammesEtudes();
        },
        async fetchData() {
            this.loading = true;
            try {
                const [specialitesRes, diplomesRes] = await Promise.all([
                    axios.get('/specialites'),
                    axios.get('/diplomes-memoires'),
                ]);
                console.log('Réponse /specialites:', specialitesRes.data);
                console.log('Réponse /diplomes-memoires:', diplomesRes.data);
                if (!Array.isArray(specialitesRes.data)) {
                    console.error(
                        "specialitesRes.data n'est pas un tableau:",
                        specialitesRes.data
                    );
                    this.specialites = [];
                } else {
                    this.specialites = specialitesRes.data;
                    console.log('this.specialites:', this.specialites);
                }
                this.diplomes = diplomesRes.data;
            } catch (error) {
                console.error(
                    'Erreur fetchData:',
                    error.response?.data || error.message
                );
                this.showError(
                    'Erreur lors du chargement des données: ' +
                        (error.response?.data?.message || error.message)
                );
                this.specialites = [];
            } finally {
                this.loading = false;
            }
        },
        async filterProgrammesEtudes() {
            this.loading = true;
            try {
                const response = await axios.get(
                    `/programmes-etudes/filter/${this.standardisationFilter || 'all'}`
                );
                console.log(
                    'Réponse /programmes-etudes/filter:',
                    response.data
                );
                this.specialites = response.data.map((pe) => pe.specialite);
                console.log('this.specialites (filtered):', this.specialites);
                this.expandedRowsEtudes = [];
            } catch (error) {
                console.error(
                    'Erreur filterProgrammesEtudes:',
                    error.response?.data || error.message
                );
                this.showError(
                    "Erreur lors du filtrage des programmes d'études: " +
                        (error.response?.data?.message || error.message)
                );
                this.specialites = [];
            } finally {
                this.loading = false;
            }
        },
        onTabChange(index) {
            this.activeTab = index;
            if (index === 2) {
                this.filterProgrammesEtudes();
            } else {
                this.fetchData();
            }
        },
        formatDate(date) {
            if (!date) return '-';
            const d = new Date(date);
            return d.toLocaleDateString('fr-FR');
        },
        getSeverity(statut) {
            const statusSeverity = {
                Actif: 'success',
                Inactif: 'danger',
                'En attente': 'warning',
                Archivé: 'info',
            };
            return statusSeverity[statut] || 'secondary';
        },
        calculateTotalHours(data) {
            return (
                (data.heures_theoriques || 0) +
                (data.heures_pratiques || 0) +
                (data.heures_evaluation || 0)
            );
        },
        toggleFreeze(id, type = 'programmes') {
            const target =
                type === 'etudes' ? this.frozenRowsEtudes : this.frozenRows;
            if (target.includes(id)) {
                target.splice(target.indexOf(id), 1);
            } else {
                target.push(id);
            }
        },
        openProgrammeForm(specialiteId, programme = null) {
            this.selectedSpecialiteId = specialiteId;
            this.programmeToEdit = programme;
            this.formVisible.programme = true;
        },
        closeProgrammeForm() {
            this.formVisible.programme = false;
            this.programmeToEdit = null;
            this.selectedSpecialiteId = null;
        },
        editProgramme(programme) {
            this.programmeToEdit = { ...programme };
            this.selectedSpecialiteId = programme.specialite_id;
            this.formVisible.programme = true;
        },
        confirmDeleteProgramme(programme) {
            this.programmeToDelete = programme;
            this.deleteFormVisible.programme = true;
        },
        async deleteProgramme() {
            try {
                await axios.delete(`/programmes/${this.programmeToDelete.id}`);
                this.showSuccess('Programme supprimé avec succès');
                await this.fetchData();
            } catch (error) {
                this.showError('Erreur lors de la suppression du programme');
            } finally {
                this.deleteFormVisible.programme = false;
                this.programmeToDelete = null;
            }
        },
        async handleSaveProgramme(newProgramme) {
            try {
                await axios.post('/programmes', newProgramme);
                this.showSuccess('Programme créé avec succès');
                await this.fetchData();
            } catch (error) {
                this.showError(
                    error.response?.data?.message ||
                        'Erreur lors de la création du programme'
                );
            }
        },
        async handleUpdateProgramme(updatedProgramme) {
            try {
                await axios.put(
                    `/programmes/${updatedProgramme.id}`,
                    updatedProgramme
                );
                this.showSuccess('Programme mis à jour avec succès');
                await this.fetchData();
            } catch (error) {
                this.showError(
                    error.response?.data?.message ||
                        'Erreur lors de la mise à jour du programme'
                );
            }
        },
        openModuleForm(programmeId, specialiteId = null, module = null) {
            this.selectedProgrammeId = programmeId;
            this.selectedSpecialiteId = specialiteId;
            this.moduleToEdit = module;
            this.formVisible.module = true;
        },
        closeModuleForm() {
            this.formVisible.module = false;
            this.moduleToEdit = null;
            this.selectedProgrammeId = null;
            this.selectedSpecialiteId = null;
        },
        editModule(module) {
            this.moduleToEdit = { ...module };
            this.selectedProgrammeId = module.version_id;
            this.formVisible.module = true;
        },
        confirmDeleteModule(module) {
            this.moduleToDelete = module;
            this.deleteFormVisible.module = true;
        },
        async deleteModule() {
            try {
                await axios.delete(`/modules/${this.moduleToDelete.id}`);
                this.showSuccess('DocumentProgrammeSpecialite supprimé avec succès');
                await this.fetchData();
            } catch (error) {
                this.showError('Erreur lors de la suppression du module');
            } finally {
                this.deleteFormVisible.module = false;
                this.moduleToDelete = null;
            }
        },
        async handleSaveModule(newModule) {
            try {
                await axios.post('/modules', newModule);
                this.showSuccess('DocumentProgrammeSpecialite créé avec succès');
                await this.fetchData();
            } catch (error) {
                this.showError(
                    error.response?.data?.message ||
                        'Erreur lors de la création du module'
                );
            }
        },
        async handleUpdateModule(updatedModule) {
            try {
                await axios.put(`/modules/${updatedModule.id}`, updatedModule);
                this.showSuccess('DocumentProgrammeSpecialite mis à jour avec succès');
                await this.fetchData();
            } catch (error) {
                this.showError(
                    error.response?.data?.message ||
                        'Erreur lors de la mise à jour du module'
                );
            }
        },
        openDocsModuleForm(moduleId) {
            this.selectedModuleId = moduleId;
            this.formVisible.docsModule = true;
        },
        async handleSaveDocsModule(formData) {
            try {
                const response = await axios.post(
                    `/modules/${this.selectedModuleId}/documents`,
                    formData,
                    {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    }
                );
                this.showSuccess('Document ajouté avec succès');
                await this.fetchData();
                return response.data;
            } catch (error) {
                this.showError(
                    error.response?.data?.message ||
                        "Erreur lors de l'ajout du document"
                );
                throw error;
            }
        },
        async exportModules(programmeId) {
            try {
                const response = await axios.get(
                    `/programmes/${programmeId}/export-modules`,
                    {
                        responseType: 'blob',
                    }
                );
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute(
                    'download',
                    `modules_programme_${programmeId}.csv`
                );
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } catch (error) {
                this.showError("Erreur lors de l'exportation des modules");
            }
        },
        async importModules(event, programmeId) {
            const file = event.target.files[0];
            if (!file) return;
            const formData = new FormData();
            formData.append('file', file);
            try {
                await axios.post(
                    `/programmes/${programmeId}/import-modules`,
                    formData,
                    {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    }
                );
                this.showSuccess('Modules importés avec succès');
                await this.fetchData();
                event.target.value = '';
            } catch (error) {
                this.showError(
                    error.response?.data?.message ||
                        "Erreur lors de l'importation des modules"
                );
            }
        },
        openMemoireForm(diplomeId, memoire = null) {
            this.selectedDiplomeId = diplomeId;
            this.memoireToEdit = memoire;
            this.formVisible.memoire = true;
        },
        closeMemoireForm() {
            this.formVisible.memoire = false;
            this.memoireToEdit = null;
            this.selectedDiplomeId = null;
        },
        editMemoire(memoire) {
            this.memoireToEdit = { ...memoire };
            this.selectedDiplomeId = memoire.diplome_fr_id;
            this.formVisible.memoire = true;
        },
        confirmDeleteMemoire(memoire) {
            this.memoireToDelete = memoire;
            this.deleteFormVisible.memoire = true;
        },
        async deleteMemoire() {
            try {
                await axios.delete(`/memoires-mg/${this.memoireToDelete.id}`);
                this.showSuccess('Mémoire supprimé avec succès');
                await this.fetchData();
            } catch (error) {
                this.showError('Erreur lors de la suppression du mémoire');
            } finally {
                this.deleteFormVisible.memoire = false;
                this.memoireToDelete = null;
            }
        },
        async handleSaveMemoire(newMemoire) {
            try {
                const formData = new FormData();
                Object.entries(newMemoire).forEach(([key, value]) => {
                    if (value instanceof File) {
                        formData.append(key, value);
                    } else if (value !== null && value !== undefined) {
                        formData.append(key, value);
                    }
                });
                await axios.post('/memoires-mg', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                });
                this.showSuccess('Mémoire créé avec succès');
                await this.fetchData();
            } catch (error) {
                this.showError(
                    error.response?.data?.message ||
                        'Erreur lors de la création du mémoire'
                );
            }
        },
        async handleUpdateMemoire(updatedMemoire) {
            try {
                const formData = new FormData();
                Object.entries(updatedMemoire).forEach(([key, value]) => {
                    if (value instanceof File) {
                        formData.append(key, value);
                    } else if (value !== null && value !== undefined) {
                        formData.append(key, value);
                    }
                });
                await axios.post(
                    `/memoires-mg/${updatedMemoire.id}`,
                    formData,
                    {
                        headers: { 'Content-Type': 'multipart/form-data' },
                        transformRequest: [
                            (data) => {
                                data.append('_method', 'PUT');
                                return data;
                            },
                        ],
                    }
                );
                this.showSuccess('Mémoire mis à jour avec succès');
                await this.fetchData();
            } catch (error) {
                this.showError(
                    error.response?.data?.message ||
                        'Erreur lors de la mise à jour du mémoire'
                );
            }
        },
        openModuleGeneralForm(memoireId, diplomeId = null, module = null) {
            this.selectedMemoireId = memoireId;
            this.selectedDiplomeId = diplomeId;
            this.moduleGeneralToEdit = module;
            this.formVisible.moduleGeneral = true;
        },
        closeModuleGeneralForm() {
            this.formVisible.moduleGeneral = false;
            this.moduleGeneralToEdit = null;
            this.selectedMemoireId = null;
            this.selectedDiplomeId = null;
        },
        editModuleGeneral(module) {
            this.moduleGeneralToEdit = { ...module };
            this.selectedMemoireId = module.reference_id;
            this.formVisible.moduleGeneral = true;
        },
        confirmDeleteModuleGeneral(module) {
            this.moduleGeneralToDelete = module;
            this.deleteFormVisible.moduleGeneral = true;
        },
        async deleteModuleGeneral() {
            try {
                await axios.delete(
                    `/modules-generaux/${this.moduleGeneralToDelete.id}`
                );
                this.showSuccess('DocumentProgrammeSpecialite général supprimé avec succès');
                await this.fetchData();
            } catch (error) {
                this.showError(
                    'Erreur lors de la suppression du module général'
                );
            } finally {
                this.deleteFormVisible.moduleGeneral = false;
                this.moduleGeneralToDelete = null;
            }
        },
        async handleSaveModuleGeneral(newModule) {
            try {
                await axios.post('/modules-generaux', newModule);
                this.showSuccess('DocumentProgrammeSpecialite général créé avec succès');
                await this.fetchData();
            } catch (error) {
                this.showError(
                    error.response?.data?.message ||
                        'Erreur lors de la création du module général'
                );
            }
        },
        async handleUpdateModuleGeneral(updatedModule) {
            try {
                await axios.put(
                    `/modules-generaux/${updatedModule.id}`,
                    updatedModule
                );
                this.showSuccess('DocumentProgrammeSpecialite général mis à jour avec succès');
                await this.fetchData();
            } catch (error) {
                this.showError(
                    error.response?.data?.message ||
                        'Erreur lors de la mise à jour du module général'
                );
            }
        },
        openDocsModuleGeneralForm(moduleId) {
            this.selectedModuleId = moduleId;
            this.formVisible.docsModuleGeneral = true;
        },
        async handleSaveDocsModuleGeneral(formData) {
            try {
                const response = await axios.post(
                    `/modules-generaux/${this.selectedModuleId}/documents`,
                    formData,
                    {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    }
                );
                this.showSuccess('Document ajouté avec succès');
                await this.fetchData();
                return response.data;
            } catch (error) {
                this.showError(
                    error.response?.data?.message ||
                        "Erreur lors de l'ajout du document"
                );
                throw error;
            }
        },
        async exportModulesGeneraux(memoireId) {
            try {
                const response = await axios.get(
                    `/memoires-mg/${memoireId}/export-modules-generaux`,
                    {
                        responseType: 'blob',
                    }
                );
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute(
                    'download',
                    `modules_generaux_memoire_${memoireId}.csv`
                );
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } catch (error) {
                this.showError(
                    "Erreur lors de l'exportation des modules généraux"
                );
            }
        },
        async importModulesGeneraux(event, memoireId) {
            const file = event.target.files[0];
            if (!file) return;
            const formData = new FormData();
            formData.append('file', file);
            try {
                await axios.post(
                    `/memoires-mg/${memoireId}/import-modules-generaux`,
                    formData,
                    {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    }
                );
                this.showSuccess('Modules généraux importés avec succès');
                await this.fetchData();
                event.target.value = '';
            } catch (error) {
                this.showError(
                    error.response?.data?.message ||
                        "Erreur lors de l'importation des modules généraux"
                );
            }
        },
        openProgrammeEtudeForm(specialiteId, programmeEtude = null) {
            this.selectedSpecialiteId = specialiteId;
            this.programmeEtudeToEdit = programmeEtude;
            this.formVisible.programmeEtude = true;
        },
        closeProgrammeEtudeForm() {
            this.formVisible.programmeEtude = false;
            this.programmeEtudeToEdit = null;
            this.selectedSpecialiteId = null;
        },
        editProgrammeEtude(programmeEtude) {
            this.programmeEtudeToEdit = { ...programmeEtude };
            this.selectedSpecialiteId = programmeEtude.specialite_id;
            this.formVisible.programmeEtude = true;
        },
        confirmDeleteProgrammeEtude(programmeEtude) {
            this.programmeEtudeToDelete = programmeEtude;
            this.deleteFormVisible.programmeEtude = true;
        },
        async deleteProgrammeEtude() {
            try {
                await axios.delete(
                    `/programmes-etudes/${this.programmeEtudeToDelete.id}`
                );
                this.showSuccess("Programme d'étude supprimé avec succès");
                await this.filterProgrammesEtudes();
            } catch (error) {
                this.showError(
                    "Erreur lors de la suppression du programme d'étude"
                );
            } finally {
                this.deleteFormVisible.programmeEtude = false;
                this.programmeEtudeToDelete = null;
            }
        },
        async handleSaveProgrammeEtude(newProgrammeEtude) {
            try {
                await axios.post('/programmes-etudes', newProgrammeEtude);
                this.showSuccess("Programme d'étude créé avec succès");
                await this.filterProgrammesEtudes();
            } catch (error) {
                this.showError(
                    error.response?.data?.message ||
                        "Erreur lors de la création du programme d'étude"
                );
            }
        },
        async handleUpdateProgrammeEtude(updatedProgrammeEtude) {
            try {
                await axios.put(
                    `/programmes-etudes/${updatedProgrammeEtude.id}`,
                    updatedProgrammeEtude
                );
                this.showSuccess("Programme d'étude mis à jour avec succès");
                await this.filterProgrammesEtudes();
            } catch (error) {
                this.showError(
                    error.response?.data?.message ||
                        "Erreur lors de la mise à jour du programme d'étude"
                );
            }
        },
        openDetailsProgSpecialite(specialiteId) {
            this.selectedSpecialiteId = specialiteId;
            this.formVisible.detailsProgSpecialite = true;
        },
        openDetailsProgramme(programmeId) {
            this.selectedProgrammeId = programmeId;
            this.formVisible.detailsProgramme = true;
        },
        openDetailsModule(moduleId) {
            this.selectedModuleId = moduleId;
            this.formVisible.detailsModule = true;
        },
        openDetailsMemDiplome(diplomeId) {
            this.selectedDiplomeId = diplomeId;
            this.formVisible.detailsMemDiplome = true;
        },
        openDetailsMemoire(memoireId) {
            this.selectedMemoireId = memoireId;
            this.formVisible.detailsMemoire = true;
        },
        openDetailsModuleGeneral(moduleId) {
            this.selectedModuleId = moduleId;
            this.formVisible.detailsModuleGeneral = true;
        },
        openDetailsProgEtudeSpe(specialiteId) {
            this.selectedSpecialiteId = specialiteId;
            this.formVisible.detailsProgEtudeSpe = true;
        },
        openDetailsProgEtude(programmeEtudeId) {
            this.selectedProgrammeEtudeId = programmeEtudeId;
            this.formVisible.detailsProgEtude = true;
        },
        showSuccess(message) {
            this.toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: message,
                life: 3000,
            });
        },
        showError(message) {
            this.toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: message,
                life: 5000,
            });
        },
    },
};
</script>

<style scoped>
.custom-datatable {
    border-radius: 8px;
}
.search-field {
    min-width: 250px;
}
.p-button-active {
    background-color: #e7e7e7;
    color: #000;
}
</style>
```
