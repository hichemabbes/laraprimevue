<template>
    <DataTable
        :value="evaluations"
        :paginator="true"
        :rows="10"
        @row-dblclick="edit"
    >
        <Column field="stagiaire_id" header="ID Stagiaire" />
        <Column field="module_id" header="ID Module" />
        <Column field="date_evaluation" header="Date" />
        <Column field="note" header="Note" />
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
        const evaluations = ref([]);

        onMounted(async () => {
            const response = await axios.get('/api/evaluations');
            evaluations.value = response.data;
        });

        const edit = (evaluation) => {
            emit('edit', evaluation);
        };

        return { evaluations, edit };
    },
};
</script>
