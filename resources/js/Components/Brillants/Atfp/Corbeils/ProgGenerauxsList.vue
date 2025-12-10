<template>
    <DataTable :headers="headers" :items="programmes" :items-per-page="10">
        <template v-slot:actions="{ item }">
            <button @click="$emit('edit', item)">Modifier</button>
            <button @click="deleteProgramme(item)">Supprimer</button>
        </template>
    </DataTable>
</template>

<script>
import DataTable from '@/Components/Shared/DataTable.vue';

export default {
    name: 'ProgrammesList',
    components: {
        DataTable,
    },
    data() {
        return {
            headers: [
                { text: 'Spécialité', value: 'specialite_id.nom_fr' },
                { text: 'Version', value: 'version' },
                { text: 'Date Début', value: 'date_debut' },
                { text: 'Date Fin', value: 'date_fin' },
                { text: 'Actif', value: 'actif' },
                { text: 'Actions', value: 'actions', sortable: false },
            ],
            programmes: [],
        };
    },
    created() {
        this.fetchProgrammes();
    },
    methods: {
        async fetchProgrammes() {
            try {
                const response = await this.$axios.get('/api/programmes');
                this.programmes = response.data;
            } catch (error) {
                console.error(
                    'Erreur lors du chargement des programmes:',
                    error
                );
            }
        },
        async deleteProgramme(item) {
            if (confirm('Voulez-vous vraiment supprimer ce programme ?')) {
                try {
                    await this.$axios.delete(`/api/programmes/${item.id}`);
                    console.log('Programme supprimé:', item);
                    this.fetchProgrammes();
                } catch (error) {
                    console.error('Erreur lors de la suppression:', error);
                }
            }
        },
    },
};
</script>

<style scoped>
button {
    margin-right: 10px;
    padding: 5px 10px;
    background-color: #dc3545;
    color: white;
    border: none;
    cursor: pointer;
}
button:first-child {
    background-color: #ffc107;
    color: black;
}
button:hover {
    opacity: 0.8;
}
</style>
