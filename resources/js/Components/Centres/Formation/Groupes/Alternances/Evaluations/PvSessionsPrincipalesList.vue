<template>
    <DataTable :value="pvs" :paginator="true" :rows="10" @row-dblclick="edit">
        <Column field="groupe_id" header="ID Groupe" />
        <Column field="stagiaire_id" header="ID Stagiaire" />
        <Column field="date_redaction" header="Date Rédaction" />
        <Column field="resultat_id" header="Résultat" />
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
        const pvs = ref([]);

        onMounted(async () => {
            const response = await axios.get('/api/pvs-fin-formation');
            pvs.value = response.data;
        });

        const edit = (pv) => {
            emit('edit', pv);
        };

        return { pvs, edit };
    },
};
</script>
