<template>
    <DataTable
        :value="contrats"
        :paginator="true"
        :rows="10"
        @row-dblclick="edit"
    >
        <Column field="stagiaire_id" header="ID Stagiaire" />
        <Column field="entreprise_id" header="ID Entreprise" />
        <Column field="num_contrat" header="N° Contrat" />
        <Column field="date_debut" header="Date Début" />
        <Column header="Actions">
            <template #body="slotProps">
                <Button
                    label="Modifier"
                    @click="edit(slotProps.data)"
                    class="p-button-warning"
                />
            </template>
        </Column>
    </DataTable>
</template>

<script>
import { ref, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import axios from '@/axios.js';

export default {
    components: { DataTable, Column, Button },
    emits: ['edit'],
    setup(_, { emit }) {
        const contrats = ref([]);

        onMounted(async () => {
            const response = await axios.get('/api/contrats-apprentissage');
            contrats.value = response.data;
        });

        const edit = (contrat) => {
            emit('edit', contrat);
        };

        return { contrats, edit };
    },
};
</script>
