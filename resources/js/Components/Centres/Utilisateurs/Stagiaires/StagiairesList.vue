<template>
    <DataTable
        :value="stagiaires"
        :paginator="true"
        :rows="10"
        @row-dblclick="edit"
    >
        <Column field="user_id" header="ID Utilisateur" />
        <Column field="num_extrait_naissance" header="N° Extrait Naissance" />
        <Column field="date_inscription" header="Date Inscription" />
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
        const stagiaires = ref([]);

        onMounted(async () => {
            const response = await axios.get('/api/stagiaires');
            stagiaires.value = response.data;
        });

        const edit = (stagiaire) => {
            emit('edit', stagiaire);
        };

        return { stagiaires, edit };
    },
};
</script>
