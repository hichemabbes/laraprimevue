<template>
    <DataTable
        :value="phases"
        :paginator="true"
        :rows="10"
        @row-dblclick="edit"
    >
        <Column field="groupe_id" header="ID Groupe" />
        <Column field="type_phase_id" header="Type Phase" />
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
        const phases = ref([]);

        onMounted(async () => {
            const response = await axios.get('/api/phases-formation');
            phases.value = response.data;
        });

        const edit = (phase) => {
            emit('edit', phase);
        };

        return { phases, edit };
    },
};
</script>
