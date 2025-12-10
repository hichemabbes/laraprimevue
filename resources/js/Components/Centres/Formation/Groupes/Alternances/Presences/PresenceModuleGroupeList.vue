<template>
    <DataTable
        :value="presences"
        :paginator="true"
        :rows="10"
        @row-dblclick="edit"
    >
        <Column field="stagiaire_id" header="ID Stagiaire" />
        <Column field="emploi_temps_id" header="ID Emploi" />
        <Column field="present" header="Présent" />
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
        const presences = ref([]);

        onMounted(async () => {
            const response = await axios.get('/api/presences');
            presences.value = response.data;
        });

        const edit = (presence) => {
            emit('edit', presence);
        };

        return { presences, edit };
    },
};
</script>
