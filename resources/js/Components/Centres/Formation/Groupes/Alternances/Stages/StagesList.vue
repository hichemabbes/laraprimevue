<template>
    <DataTable
        :value="stages"
        :paginator="true"
        :rows="10"
        @row-dblclick="edit"
    >
        <Column field="stagiaire_id" header="ID Stagiaire" />
        <Column field="entreprise_id" header="ID Entreprise" />
        <Column field="date_debut" header="Date Début" />
        <Column field="statut_id" header="Statut" />
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
        const stages = ref([]);

        onMounted(async () => {
            const response = await axios.get('/api/stages');
            stages.value = response.data;
        });

        const edit = (stage) => {
            emit('edit', stage);
        };

        return { stages, edit };
    },
};
</script>
