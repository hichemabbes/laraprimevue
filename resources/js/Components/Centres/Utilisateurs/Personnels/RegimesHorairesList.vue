<template>
    <DataTable
        :value="regimes"
        :paginator="true"
        :rows="10"
        @row-dblclick="edit"
    >
        <Column field="personnel_id" header="ID Personnel" />
        <Column field="regime_horaire" header="Régime Horaire" />
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
        const regimes = ref([]);

        onMounted(async () => {
            const response = await axios.get('/api/regimes-horaires');
            regimes.value = response.data;
        });

        const edit = (regime) => {
            emit('edit', regime);
        };

        return { regimes, edit };
    },
};
</script>
