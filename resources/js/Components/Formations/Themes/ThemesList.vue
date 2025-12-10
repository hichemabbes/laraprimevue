<template>
    <div class="surface-ground pt-2 px-1.5 pb-1.5" style="margin-top: 0">
        <!-- Header -->
        <div
            class="surface-card p-2 border-round border-1 surface-border"
            style="position: relative; top: -36px; margin-bottom: -32px"
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
                        label="Thèmes de Formation"
                        icon="pi pi-list"
                        class="p-button-text p-button-plain p-button-sm"
                        disabled
                    />
                </div>

                <div class="flex gap-2">
                    <Button
                        icon="pi pi-refresh"
                        class="p-button-text p-button-sm p-button-rounded p-button-secondary"
                        @click="fetchThemes"
                        v-tooltip="'Rafraîchir'"
                    />

                    <Button
                        icon="pi pi-plus"
                        class="p-button-success p-button-sm p-button-rounded"
                        @click="openAddDialog"
                        v-tooltip="'Ajouter un thème'"
                    />
                </div>
            </div>
        </div>

        <!-- Tableau -->
        <div class="surface-card p-4 pt-2 border-round shadow-2 border-1 surface-border">
            <!-- Header Table -->
            <div class="flex justify-content-between align-items-center mb-4">
                <span class="p-input-icon-left search-field">
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
                />
            </div>

            <DataTable
                :value="themes"
                v-model:filters="filters"
                dataKey="id"
                size="small"
                paginator
                :rows="20"
                :loading="loading"
                stripedRows
                filterDisplay="menu"
                :globalFilterFields="['nom_fr','nom_ar','statut']"
                class="p-datatable-sm border-1 surface-border"
                scrollable
                scrollHeight="700px"
            >
                <template #empty>
                    <div class="py-4 text-center">
                        <i class="pi pi-inbox text-4xl text-400 mb-2"></i>
                        <p>Aucun thème trouvé</p>
                    </div>
                </template>

                <Column field="nom_fr" header="Nom (FR)" frozen sortable>
                    <template #body="{ data }">
                        <span class="flex align-items-center gap-2">
                            <i
                                class="pi pi-circle-fill"
                                :class="data.statut === 'Actif' ? 'text-green-500' : 'text-red-500'"
                                style="font-size: .5rem"
                            />
                            <span>{{ data.nom_fr }}</span>
                        </span>
                    </template>
                </Column>

                <Column field="nom_ar" header="Nom (AR)">
                    <template #body="{ data }">
                        <span class="font-arabic">{{ data.nom_ar }}</span>
                    </template>
                </Column>

                <Column field="statut" header="Statut">
                    <template #body="{ data }">
                        <Tag
                            :value="data.statut"
                            :severity="data.statut === 'Actif' ? 'success' : 'danger'"
                        />
                    </template>
                </Column>

                <Column header="Actions" frozen>
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Button
                                icon="pi pi-pencil"
                                class="p-button-text p-button-rounded p-button-warning"
                                @click="openEditDialog(data)"
                                v-tooltip="'Modifier'"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-text p-button-rounded p-button-danger"
                                @click="confirmDelete(data)"
                                v-tooltip="'Supprimer'"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <!-- Dialog Add -->
            <ThemeAdd
                :visible="showAddDialog"
                @save="onThemeSaved"
                @update:visible="showAddDialog = $event"
            />

            <!-- Dialog Edit -->
            <ThemeEdit
                :visible="showEditDialog"
                :themeData="themeToEdit"
                :themeId="themeToEdit ? themeToEdit.id : null"
                @update="onThemeUpdated"
                @update:visible="showEditDialog = $event"
            />

            <!-- Dialog Delete -->
            <Dialog
                v-model:visible="showDeleteDialog"
                header="Confirmation"
                :modal="true"
                :style="{ width: '500px' }"
            >
                <p>
                    Voulez-vous vraiment supprimer le thème :
                    <b>{{ themeToDelete?.nom_fr }}</b> ?
                </p>

                <template #footer>
                    <Button
                        label="Annuler"
                        class="p-button-text"
                        icon="pi pi-times"
                        @click="showDeleteDialog = false"
                    />
                    <Button
                        label="Supprimer"
                        class="p-button-danger"
                        icon="pi pi-check"
                        @click="deleteTheme"
                    />
                </template>
            </Dialog>
        </div>

        <Toast position="top-right" />
    </div>
</template>

<script>
import axios from 'axios';
import { FilterMatchMode } from 'primevue/api';

import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Tag from 'primevue/tag';

import ThemeAdd from '@/Components/Formations/Themes/ThemeAdd.vue';
import ThemeEdit from '@/Components/Formations/Themes/ThemeEdit.vue';

import { useToast } from 'primevue/usetoast';

export default {
    components: {
        Button,
        Column,
        DataTable,
        InputText,
        Dialog,
        Toast,
        Tag,
        ThemeAdd,
        ThemeEdit,
    },
    setup() {
        return { toast: useToast() };
    },
    data() {
        return {
            themes: [],
            loading: false,
            filters: {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
            },
            showAddDialog: false,
            showEditDialog: false,
            themeToEdit: null,
            showDeleteDialog: false,
            themeToDelete: null,
        };
    },
    created() {
        this.fetchThemes();
    },
    methods: {
        async fetchThemes() {
            this.loading = true;
            try {
                const res = await axios.get('/api/themes');
                this.themes = res.data || [];
            } finally {
                this.loading = false;
            }
        },
        clearFilter() {
            this.filters.global.value = null;
        },
        openAddDialog() {
            this.showAddDialog = true;
        },
        openEditDialog(theme) {
            this.themeToEdit = { ...theme };
            this.showEditDialog = true;
        },
        confirmDelete(theme) {
            this.themeToDelete = theme;
            this.showDeleteDialog = true;
        },
        async deleteTheme() {
            await axios.delete(`/api/themes/${this.themeToDelete.id}`);
            this.themes = this.themes.filter((t) => t.id !== this.themeToDelete.id);
            this.showDeleteDialog = false;

            this.toast.add({
                severity: 'success',
                summary: 'Supprimé',
                detail: 'Thème supprimé avec succès.',
            });
        },
        onThemeSaved(theme) {
            this.themes.push(theme);
            this.toast.add({ severity: 'success', summary: 'Ajouté', detail: 'Thème ajouté.' });
        },
        onThemeUpdated(theme) {
            const index = this.themes.findIndex((t) => t.id === theme.id);
            if (index !== -1) this.themes[index] = theme;

            this.toast.add({ severity: 'success', summary: 'Mis à jour', detail: 'Thème mis à jour.' });
        },
    },
};
</script>

<style scoped>
.search-field {
    min-width: 250px;
}
.font-arabic {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
}
</style>
