<template>
    <DataTable
        :value="historique"
        :paginator="true"
        :rows="10"
        @row-dblclick="edit"
    >
        <Column field="groupe_id" header="ID Groupe" />
        <Column field="action_id" header="Action" />
        <Column field="date_action" header="Date Action" />
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
        const historique = ref([]);

        onMounted(async () => {
            const response = await axios.get('/api/groupes-historique');
            historique.value = response.data;
        });

        const edit = (historique) => {
            emit('edit', historique);
        };

        return { historique, edit };
    },
};
</script>
