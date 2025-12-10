<template>
    <DataTable
        :value="suivis"
        :paginator="true"
        :rows="10"
        @row-dblclick="edit"
    >
        <Column field="stagiaire_id" header="ID Stagiaire" />
        <Column field="entreprise_id" header="ID Entreprise" />
        <Column field="date_suivi" header="Date Suivi" />
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
        const suivis = ref([]);

        onMounted(async () => {
            const response = await axios.get('/api/suivis-pedagogiques');
            suivis.value = response.data;
        });

        const edit = (suivi) => {
            emit('edit', suivi);
        };

        return { suivis, edit };
    },
};
</script>
