<template>
    <div class="card">
        <Toolbar>
            <template #start>
                <Button
                    label="Nouvelle Mémoire"
                    icon="pi pi-plus"
                    class="p-button-success mr-2"
                    @click="showForm()"
                />
            </template>
            <template #end>
                <InputText
                    v-model="filters.global"
                    placeholder="Rechercher..."
                />
            </template>
        </Toolbar>
        <DataTable
            :value="memoires"
            :paginator="true"
            :rows="10"
            :filters="filters"
            :loading="loading"
            @row-dblclick="showForm($event.data)"
        >
            <Column field="reference" header="Référence" sortable />
            <Column field="diplome.nom_fr" header="Diplôme" sortable />
            <Column field="numero_memoire" header="Numéro Mémoire" sortable />
            <Column field="date_memoire" header="Date Mémoire" sortable>
                <template #body="{ data }">
                    {{ formatDate(data.date_memoire) }}
                </template>
            </Column>
            <Column field="statut" header="Statut" sortable />
            <Column header="Actions">
                <template #body="{ data }">
                    <Button
                        icon="pi pi-pencil"
                        class="p-button-rounded p-button-warning mr-2"
                        @click="showForm(data)"
                    />
                    <Button
                        icon="pi pi-trash"
                        class="p-button-rounded p-button-danger"
                        @click="confirmDelete(data)"
                    />
                </template>
            </Column>
        </DataTable>
        <MemoireForm
            :visible="formVisible"
            :memoireToEdit="selectedMemoire"
            @update:visible="formVisible = $event"
            @save="saveMemoire"
            @update="updateMemoire"
            @close="formVisible = false"
        />
        <ConfirmDialog />
        <Toast />
    </div>
</template>

<script>
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import ConfirmDialog from 'primevue/confirmdialog';
import Toast from 'primevue/toast';
import MemoireForm from './MemoireForm.vue';
import { FilterMatchMode } from 'primevue/api';

export default {
    components: {
        DataTable,
        Column,
        Toolbar,
        Button,
        InputText,
        ConfirmDialog,
        Toast,
        MemoireForm,
    },
    setup() {
        return { toast: useToast() };
    },
    data() {
        return {
            memoires: [],
            loading: false,
            formVisible: false,
            selectedMemoire: null,
            filters: {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
            },
        };
    },
    async created() {
        await this.fetchMemoires();
    },
    methods: {
        async fetchMemoires() {
            this.loading = true;
            try {
                const response = await axios.get('/api/memoires-mg');
                this.memoires = response.data;
            } catch (error) {
                this.showError('Erreur lors du chargement des mémoires.');
            } finally {
                this.loading = false;
            }
        },
        showForm(memoire = null) {
            this.selectedMemoire = memoire;
            this.formVisible = true;
        },
        async saveMemoire(memoire) {
            try {
                await axios.post('/api/memoires-mg', memoire);
                this.showSuccess('Mémoire créée avec succès.');
                await this.fetchMemoires();
            } catch (error) {
                this.handleError(
                    error,
                    'Erreur lors de la création de la mémoire.'
                );
            }
        },
        async updateMemoire({ id, ...memoire }) {
            try {
                await axios.put(`/api/memoires-mg/${id}`, memoire);
                this.showSuccess('Mémoire mise à jour avec succès.');
                await this.fetchMemoires();
            } catch (error) {
                this.handleError(
                    error,
                    'Erreur lors de la mise à jour de la mémoire.'
                );
            }
        },
        confirmDelete(memoire) {
            this.$confirm.require({
                message: `Voulez-vous supprimer la mémoire ${memoire.reference} ?`,
                header: 'Confirmation',
                icon: 'pi pi-exclamation-triangle',
                accept: async () => {
                    try {
                        await axios.delete(`/api/memoires-mg/${memoire.id}`);
                        this.showSuccess('Mémoire supprimée avec succès.');
                        await this.fetchMemoires();
                    } catch (error) {
                        this.handleError(
                            error,
                            'Erreur lors de la suppression de la mémoire.'
                        );
                    }
                },
            });
        },
        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString('fr-FR');
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
        handleError(error, defaultMessage) {
            const message = error.response?.data?.message || defaultMessage;
            this.showError(message);
        },
    },
};
</script>

<style scoped>
.card {
    padding: 1rem;
}
</style>
