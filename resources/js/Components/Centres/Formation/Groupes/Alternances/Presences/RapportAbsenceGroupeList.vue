<template>
    <DataTable
        :value="rapports"
        :paginator="true"
        :rows="10"
        @row-dblclick="edit"
    >
        <Column field="phase_formation_id" header="ID Phase" />
        <Column field="stagiaire_id" header="ID Stagiaire" />
        <Column field="total_heures_absence" header="Heures Absence" />
        <Column field="taux_absence" header="Taux (%)" />
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
        const rapports = ref([]);

        onMounted(async () => {
            const response = await axios.get('/api/rapports-absences');
            rapports.value = response.data;
        });

        const edit = (rapport) => {
            emit('edit', rapport);
        };

        return { rapports, edit };
    },
};
</script>
