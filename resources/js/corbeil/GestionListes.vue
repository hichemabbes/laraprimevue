<template>
    <div class="p-4">
        <h1 class="text-900 font-bold text-4xl mb-5">
            Gestion des Listes Déroulantes
        </h1>

        <!-- Barre d'outils -->
        <div class="flex justify-content-between mb-2 align-items-center">
            <div class="flex align-items-center gap-2">
                <Button
                    icon="pi pi-plus"
                    severity="success"
                    size="small"
                    label="Ajouter un type"
                    @click="showAddTypeDialog"
                />
                <span class="p-input-icon-right mr-2">
                    <InputText
                        v-model="filters.global.value"
                        size="small"
                        placeholder="Recherche..."
                        v-if="filters.global"
                    />
                    <i class="pi pi-search" />
                </span>
                <Button
                    type="button"
                    icon="pi pi-times"
                    size="small"
                    severity="danger"
                    outlined
                    @click="clearFilter"
                />
            </div>
            <div class="flex align-items-center gap-3">
                <Button
                    text
                    icon="pi pi-minus"
                    label="Tout Réduire"
                    @click="collapseAll"
                />
            </div>
        </div>

        <!-- Tableau principal des types de catégories -->
        <DataTable
            v-model:expandedRows="expandedRows"
            v-model:filters="filters"
            showGridlines
            stripedRows
            v-model:selection="selectedTypes"
            :value="typesCategories"
            dataKey="id"
            size="small"
            :rows="10"
            filterDisplay="menu"
            :globalFilterFields="['nom', 'description', 'actif']"
            :loading="loading"
            scrollable
            scrollHeight="700px"
            :pt="{ table: { style: 'min-width: 50rem' } }"
        >
            <template #empty>
                <div v-if="!loading" class="text-center p-3">
                    Aucun type de catégorie trouvé.
                </div>
                <div v-else class="text-center p-3">Chargement en cours...</div>
            </template>

            <Column expander style="width: 5rem" />
            <Column
                selectionMode="multiple"
                headerStyle="width: 3rem"
                frozen
                class="font-bold"
            ></Column>
            <Column field="nom" header="Nom" sortable style="width: 15rem">
                <template #body="{ data }">
                    <span>{{ data.nom }}</span>
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Rechercher par nom"
                        @input="filterCallback"
                    />
                </template>
            </Column>
            <Column
                field="description"
                header="Description"
                sortable
                style="width: 20rem"
            >
                <template #body="{ data }">
                    <span>{{ data.description }}</span>
                </template>
            </Column>
            <Column field="actif" header="Actif" sortable style="width: 10rem">
                <template #body="{ data }">
                    <div class="flex justify-content-center align-items-center">
                        <Tag
                            :value="data.actif ? 'Oui' : 'Non'"
                            :severity="data.actif ? 'success' : 'danger'"
                        />
                    </div>
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <Dropdown
                        v-model="filterModel.value"
                        :options="['Oui', 'Non']"
                        placeholder="Sélectionner un état"
                        showClear
                        @change="filterCallback"
                    >
                        <template #option="slotProps">
                            <Tag
                                :value="slotProps.option"
                                :severity="
                                    slotProps.option === 'Oui'
                                        ? 'success'
                                        : 'danger'
                                "
                            />
                        </template>
                    </Dropdown>
                </template>
            </Column>
            <Column header="Actions" style="width: 15rem" frozen>
                <template #body="{ data }">
                    <div class="flex gap-2">
                        <Button
                            icon="pi pi-pencil"
                            severity="info"
                            text
                            @click="showEditTypeDialog(data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            severity="danger"
                            text
                            @click="confirmDeleteType(data)"
                        />
                    </div>
                </template>
            </Column>

            <!-- Sous-tableau pour les options -->
            <template #expansion="{ data }">
                <div class="p-3 surface-100">
                    <div
                        class="flex justify-content-between align-items-center mb-2"
                    >
                        <h5 style="font-weight: bold">
                            <span style="color: #93c5fd"
                                >Options pour le type
                            </span>
                            <span style="color: #ef4444">{{ data.nom }}</span>
                        </h5>
                        <Button
                            icon="pi pi-plus"
                            severity="success"
                            size="small"
                            label="Ajouter une option"
                            @click="showAddOptionDialog(data)"
                        />
                    </div>
                    <DataTable
                        :value="data.options"
                        size="small"
                        :loading="optionsLoading"
                    >
                        <Column
                            field="nom"
                            header="Nom"
                            style="width: 15rem"
                        ></Column>
                        <Column
                            field="couleur"
                            header="Couleur"
                            style="width: 10rem"
                        >
                            <template #body="{ data }">
                                <span
                                    :style="{
                                        color: data.couleur,
                                        padding: '2px 5px',
                                        borderRadius: '3px',
                                    }"
                                    >{{ data.couleur }}</span
                                >
                            </template>
                        </Column>
                        <Column field="fond" header="Fond" style="width: 10rem">
                            <template #body="{ data }">
                                <span
                                    :style="{
                                        background: data.fond,
                                        padding: '2px 5px',
                                        borderRadius: '3px',
                                    }"
                                    >{{ data.fond }}</span
                                >
                            </template>
                        </Column>
                        <Column
                            field="ordre"
                            header="Ordre"
                            style="width: 10rem"
                        ></Column>
                        <Column
                            field="actif"
                            header="Actif"
                            style="width: 10rem"
                        >
                            <template #body="{ data }">
                                <Tag
                                    :value="data.actif ? 'Oui' : 'Non'"
                                    :severity="
                                        data.actif ? 'success' : 'danger'
                                    "
                                />
                            </template>
                        </Column>
                        <Column
                            field="centre_id"
                            header="Centre ID"
                            style="width: 10rem"
                        ></Column>
                        <Column header="Actions" style="width: 15rem">
                            <template #body="{ data: option }">
                                <div class="flex gap-2">
                                    <Button
                                        icon="pi pi-pencil"
                                        severity="info"
                                        text
                                        @click="showEditOptionDialog(option)"
                                    />
                                    <Button
                                        icon="pi pi-trash"
                                        severity="danger"
                                        text
                                        @click="confirmDeleteOption(option)"
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </template>
        </DataTable>

        <!-- Dialogue pour ajouter/modifier un type -->
        <Dialog
            v-model:visible="typeDialogVisible"
            :header="
                typeDialogMode === 'add'
                    ? 'Ajouter un Type'
                    : 'Modifier un Type'
            "
            :style="{ width: '30rem' }"
            :modal="true"
        >
            <div class="p-fluid">
                <div class="field">
                    <label for="nom">Nom</label>
                    <InputText id="nom" v-model="currentType.nom" />
                    <small v-if="typeErrors.nom" class="p-error">{{
                        typeErrors.nom
                    }}</small>
                </div>
                <div class="field">
                    <label for="description">Description</label>
                    <Textarea
                        id="description"
                        v-model="currentType.description"
                        rows="3"
                    />
                </div>
                <div class="field">
                    <label for="actif">Actif</label>
                    <InputSwitch id="actif" v-model="currentType.actif" />
                </div>
            </div>
            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    text
                    @click="typeDialogVisible = false"
                />
                <Button
                    :label="
                        typeDialogMode === 'add' ? 'Ajouter' : 'Enregistrer'
                    "
                    icon="pi pi-check"
                    severity="primary"
                    @click="saveType"
                    :loading="isLoading"
                />
            </template>
        </Dialog>

        <!-- Dialogue pour ajouter/modifier une option -->
        <Dialog
            v-model:visible="optionDialogVisible"
            :header="
                optionDialogMode === 'add'
                    ? 'Ajouter une Option'
                    : 'Modifier une Option'
            "
            :style="{ width: '30rem' }"
            :modal="true"
        >
            <div class="p-fluid">
                <div class="field">
                    <label for="optionNom">Nom</label>
                    <InputText id="optionNom" v-model="currentOption.nom" />
                    <small v-if="optionErrors.nom" class="p-error">{{
                        optionErrors.nom
                    }}</small>
                </div>
                <div class="field">
                    <label for="couleur">Couleur</label>
                    <ColorPicker id="couleur" v-model="currentOption.couleur" />
                </div>
                <div class="field">
                    <label for="fond">Fond</label>
                    <ColorPicker id="fond" v-model="currentOption.fond" />
                </div>
                <div class="field">
                    <label for="ordre">Ordre</label>
                    <InputNumber
                        id="ordre"
                        v-model="currentOption.ordre"
                        :min="0"
                    />
                </div>
                <div class="field">
                    <label for="optionActif">Actif</label>
                    <InputSwitch
                        id="optionActif"
                        v-model="currentOption.actif"
                    />
                </div>
                <div class="field" v-if="optionDialogMode === 'add'">
                    <label for="centreId">Centre ID</label>
                    <InputNumber
                        id="centreId"
                        v-model="currentOption.centre_id"
                        :min="0"
                    />
                </div>
            </div>
            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    text
                    @click="optionDialogVisible = false"
                />
                <Button
                    :label="
                        optionDialogMode === 'add' ? 'Ajouter' : 'Enregistrer'
                    "
                    icon="pi pi-check"
                    severity="primary"
                    @click="saveOption"
                    :loading="isLoading"
                />
            </template>
        </Dialog>

        <!-- Dialogue de confirmation pour supprimer un type -->
        <Dialog
            v-model:visible="deleteTypeDialogVisible"
            header="Confirmation de suppression"
            :modal="true"
            :style="{ width: '450px' }"
        >
            <div class="p-fluid">
                <p v-if="typeToDelete">
                    Êtes-vous sûr de vouloir supprimer le type
                    <strong>{{ typeToDelete.nom }}</strong> ?
                </p>
            </div>
            <template #footer>
                <Button
                    label="Non"
                    icon="pi pi-times"
                    text
                    @click="deleteTypeDialogVisible = false"
                />
                <Button
                    label="Oui"
                    icon="pi pi-check"
                    text
                    @click="deleteType"
                />
            </template>
        </Dialog>

        <!-- Dialogue de confirmation pour supprimer une option -->
        <Dialog
            v-model:visible="deleteOptionDialogVisible"
            header="Confirmation de suppression"
            :modal="true"
            :style="{ width: '450px' }"
        >
            <div class="p-fluid">
                <p v-if="optionToDelete">
                    Êtes-vous sûr de vouloir supprimer l’option
                    <strong>{{ optionToDelete.nom }}</strong> ?
                </p>
            </div>
            <template #footer>
                <Button
                    label="Non"
                    icon="pi pi-times"
                    text
                    @click="deleteOptionDialogVisible = false"
                />
                <Button
                    label="Oui"
                    icon="pi pi-check"
                    text
                    @click="deleteOption"
                />
            </template>
        </Dialog>

        <!-- Toast pour notifications -->
        <Toast />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import { useToast } from 'primevue/usetoast';
import axios from '@/axios.js';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InputSwitch from 'primevue/inputswitch';
import InputNumber from 'primevue/inputnumber';
import ColorPicker from 'primevue/colorpicker';
import Dropdown from 'primevue/dropdown';
import Toast from 'primevue/toast';

// États et données
const toast = useToast();
const typesCategories = ref([]);
const selectedTypes = ref(null);
const expandedRows = ref([]);
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    nom: {
        operator: FilterOperator.AND,
        constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }],
    },
    description: {
        operator: FilterOperator.AND,
        constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }],
    },
    actif: {
        operator: FilterOperator.OR,
        constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }],
    },
});
const loading = ref(true);
const optionsLoading = ref(false);
const isLoading = ref(false);
const typeDialogVisible = ref(false);
const optionDialogVisible = ref(false);
const deleteTypeDialogVisible = ref(false);
const deleteOptionDialogVisible = ref(false);
const typeDialogMode = ref('add');
const optionDialogMode = ref('add');
const currentType = ref({ nom: '', description: '', actif: true });
const currentOption = ref({
    nom: '',
    couleur: '#000000',
    fond: '#ffffff',
    ordre: 0,
    actif: true,
    centre_id: 0,
    type_categorie_id: null,
});
const typeToDelete = ref(null);
const optionToDelete = ref(null);
const typeErrors = ref({});
const optionErrors = ref({});

// Initialisation des filtres
const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        nom: {
            operator: FilterOperator.AND,
            constraints: [
                { value: null, matchMode: FilterMatchMode.STARTS_WITH },
            ],
        },
        description: {
            operator: FilterOperator.AND,
            constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }],
        },
        actif: {
            operator: FilterOperator.OR,
            constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }],
        },
    };
};

// Chargement des données au montage
onMounted(async () => {
    await loadTypesCategories();
});

// Charger les types de catégories
const loadTypesCategories = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/types-categories');
        typesCategories.value = response.data.map((type) => ({
            ...type,
            options: [], // Initialiser les options vides
        }));
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Impossible de charger les types',
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
};

// Charger les options d'un type lors de l'expansion
const onRowExpand = async (event) => {
    const typeId = event.data.id;
    optionsLoading.value = true;
    try {
        const response = await axios.get(`/options-listes/${event.data.nom}/0`);
        const typeIndex = typesCategories.value.findIndex(
            (t) => t.id === typeId
        );
        typesCategories.value[typeIndex].options = response.data;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Impossible de charger les options',
            life: 3000,
        });
    } finally {
        optionsLoading.value = false;
    }
};

// Ajouter un type
const showAddTypeDialog = () => {
    typeDialogMode.value = 'add';
    currentType.value = { nom: '', description: '', actif: true };
    typeErrors.value = {};
    typeDialogVisible.value = true;
};

// Modifier un type
const showEditTypeDialog = (type) => {
    typeDialogMode.value = 'edit';
    currentType.value = { ...type };
    typeErrors.value = {};
    typeDialogVisible.value = true;
};

// Enregistrer un type
const saveType = async () => {
    isLoading.value = true;
    typeErrors.value = {};
    try {
        const url =
            typeDialogMode.value === 'add'
                ? '/types-categories/create'
                : `/types-categories/update/${currentType.value.id}`;
        const method = typeDialogMode.value === 'add' ? 'post' : 'put';
        const response = await axios[method](url, currentType.value);

        if (response.data.status === 'success') {
            await loadTypesCategories();
            typeDialogVisible.value = false;
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail:
                    typeDialogMode.value === 'add'
                        ? 'Type ajouté'
                        : 'Type modifié',
                life: 3000,
            });
        }
    } catch (error) {
        if (error.response?.status === 422) {
            typeErrors.value = error.response.data.errors;
        } else {
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Échec de l’opération',
                life: 3000,
            });
        }
    } finally {
        isLoading.value = false;
    }
};

// Confirmer la suppression d’un type
const confirmDeleteType = (type) => {
    typeToDelete.value = type;
    deleteTypeDialogVisible.value = true;
};

// Supprimer un type
const deleteType = async () => {
    if (typeToDelete.value) {
        try {
            await axios.delete(
                `/types-categories/delete/${typeToDelete.value.id}`
            );
            await loadTypesCategories();
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Type supprimé',
                life: 3000,
            });
        } catch (error) {
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Échec de la suppression',
                life: 3000,
            });
        } finally {
            deleteTypeDialogVisible.value = false;
            typeToDelete.value = null;
        }
    }
};

// Ajouter une option
const showAddOptionDialog = (type) => {
    optionDialogMode.value = 'add';
    currentOption.value = {
        nom: '',
        couleur: '#000000',
        fond: '#ffffff',
        ordre: 0,
        actif: true,
        centre_id: 0,
        type_categorie_id: type.id,
    };
    optionErrors.value = {};
    optionDialogVisible.value = true;
};

// Modifier une option
const showEditOptionDialog = (option) => {
    optionDialogMode.value = 'edit';
    currentOption.value = { ...option };
    optionErrors.value = {};
    optionDialogVisible.value = true;
};

// Enregistrer une option
const saveOption = async () => {
    isLoading.value = true;
    optionErrors.value = {};
    try {
        const url =
            optionDialogMode.value === 'add'
                ? '/options-listes/create'
                : `/options-listes/update/${currentOption.value.id}`;
        const method = optionDialogMode.value === 'add' ? 'post' : 'put';
        const response = await axios[method](url, currentOption.value);

        if (response.data.status === 'success') {
            const typeIndex = typesCategories.value.findIndex(
                (t) => t.id === currentOption.value.type_categorie_id
            );
            await onRowExpand({ data: typesCategories.value[typeIndex] });
            optionDialogVisible.value = false;
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail:
                    optionDialogMode.value === 'add'
                        ? 'Option ajoutée'
                        : 'Option modifiée',
                life: 3000,
            });
        }
    } catch (error) {
        if (error.response?.status === 422) {
            optionErrors.value = error.response.data.errors;
        } else {
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Échec de l’opération',
                life: 3000,
            });
        }
    } finally {
        isLoading.value = false;
    }
};

// Confirmer la suppression d’une option
const confirmDeleteOption = (option) => {
    optionToDelete.value = option;
    deleteOptionDialogVisible.value = true;
};

// Supprimer une option
const deleteOption = async () => {
    if (optionToDelete.value) {
        try {
            await axios.delete(
                `/options-listes/delete/${optionToDelete.value.id}`
            );
            const typeIndex = typesCategories.value.findIndex((t) =>
                t.options.some((o) => o.id === optionToDelete.value.id)
            );
            await onRowExpand({ data: typesCategories.value[typeIndex] });
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Option supprimée',
                life: 3000,
            });
        } catch (error) {
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Échec de la suppression',
                life: 3000,
            });
        } finally {
            deleteOptionDialogVisible.value = false;
            optionToDelete.value = null;
        }
    }
};

// Réduire toutes les lignes
const collapseAll = () => {
    expandedRows.value = [];
};

// Effacer les filtres
const clearFilter = () => {
    initFilters();
};
</script>

<style scoped>
.p-datatable-sm {
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}
</style>
