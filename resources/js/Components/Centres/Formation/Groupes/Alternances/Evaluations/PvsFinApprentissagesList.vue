<template>
    <DataTable
        :value="pvs"
        :paginator="true"
        :rows="10"
        :rowsPerPageOptions="[5, 10, 20, 50]"
        @row-dblclick="edit"
        responsiveLayout="scroll"
        :loading="loading"
    >
        <Column field="stagiaire.user.nom_fr" header="Stagiaire" sortable />
        <Column
            field="contrat_apprentissage.num_contrat"
            header="N° Contrat"
            sortable
        />
        <Column field="date_redaction" header="Date Rédaction" sortable>
            <template #body="slotProps">
                {{ formatDate(slotProps.data.date_redaction) }}
            </template>
        </Column>
        <Column field="resultat.nom" header="Résultat" sortable />
        <Column field="lieu_reunion.nom_fr" header="Lieu Réunion" sortable />
        <Column header="Actions">
            <template #body="slotProps">
                <Button
                    label="Modifier"
                    icon="pi pi-pencil"
                    @click="edit(slotProps.data)"
                    class="p-button-warning p-button-sm mr-2"
                />
                <Button
                    label="Supprimer"
                    icon="pi pi-trash"
                    @click="confirmDelete(slotProps.data)"
                    class="p-button-danger p-button-sm"
                />
            </template>
        </Column>
    </DataTable>

    <ConfirmDialog />
</template>

<script>
import { ref, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import ConfirmDialog from 'primevue/confirmdialog';
import axios from '@/axios.js';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';

export default {
    components: { DataTable, Column, Button, ConfirmDialog },
    emits: ['edit'],
    setup(_, { emit }) {
        const pvs = ref([]);
        const loading = ref(false);
        const confirm = useConfirm();
        const toast = useToast();

        onMounted(async () => {
            await fetchPvs();
        });

        const fetchPvs = async () => {
            try {
                loading.value = true;
                const response = await axios.get('/api/pvs-fin-apprentissage', {
                    params: {
                        with: 'stagiaire.user,contrat_apprentissage,resultat,lieu_reunion',
                    },
                });
                pvs.value = response.data;
            } catch (error) {
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les PVs',
                    life: 3000,
                });
                console.error('Erreur lors du chargement:', error);
            } finally {
                loading.value = false;
            }
        };

        const formatDate = (date) => {
            return date ? new Date(date).toLocaleDateString('fr-FR') : '-';
        };

        const edit = (pv) => {
            emit('edit', pv);
        };

        const confirmDelete = (pv) => {
            confirm.require({
                message: `Voulez-vous vraiment supprimer le PV pour ${pv.stagiaire?.user?.nom_fr || 'ce stagiaire'} ?`,
                header: 'Confirmation de suppression',
                icon: 'pi pi-exclamation-triangle',
                acceptLabel: 'Oui',
                rejectLabel: 'Non',
                accept: async () => {
                    try {
                        await axios.delete(
                            `/api/pvs-fin-apprentissage/${pv.id}`
                        );
                        pvs.value = pvs.value.filter(
                            (item) => item.id !== pv.id
                        );
                        toast.add({
                            severity: 'success',
                            summary: 'Succès',
                            detail: 'PV supprimé avec succès',
                            life: 3000,
                        });
                    } catch (error) {
                        toast.add({
                            severity: 'error',
                            summary: 'Erreur',
                            detail: 'Erreur lors de la suppression',
                            life: 3000,
                        });
                        console.error('Erreur lors de la suppression:', error);
                    }
                },
            });
        };

        return { pvs, loading, edit, confirmDelete, formatDate };
    },
};
</script>

<style scoped>
.p-datatable {
    min-height: 300px;
}
</style>
