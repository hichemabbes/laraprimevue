<template>
    <DataTable
        :value="groupes"
        :paginator="true"
        :rows="10"
        @row-dblclick="edit"
    >
        <Column field="code_groupe" header="Code Groupe" />
        <Column field="nom" header="Nom" />
        <Column field="date_debut_formation" header="Date Début" />
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
        const groupes = ref([]);

        onMounted(async () => {
            const response = await axios.get('/api/groupes');
            groupes.value = response.data;
        });

        const edit = (groupe) => {
            emit('edit', groupe);
        };

        return { groupes, edit };
    },
};
</script>
