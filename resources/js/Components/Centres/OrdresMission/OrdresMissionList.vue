<template>
    <DataTable
        :value="ordres"
        :paginator="true"
        :rows="10"
        @row-dblclick="edit"
    >
        <Column field="personnel_id" header="ID Formateur" />
        <Column field="centre_id" header="ID Centre" />
        <Column field="date_mission" header="Date Mission" />
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
        const ordres = ref([]);

        onMounted(async () => {
            const response = await axios.get('/api/ordres-mission');
            ordres.value = response.data;
        });

        const edit = (ordre) => {
            emit('edit', ordre);
        };

        return { ordres, edit };
    },
};
</script>
