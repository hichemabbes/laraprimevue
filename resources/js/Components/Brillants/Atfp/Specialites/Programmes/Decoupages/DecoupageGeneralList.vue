<template>
    <DataTable :headers="headers" :items="decoupages" :items-per-page="10">
        <template v-slot:actions="{ item }">
            <button @click="$emit('edit', item)">Modifier</button>
            <button @click="deleteDecoupage(item)">Supprimer</button>
        </template>
    </DataTable>
</template>

<script>
import DataTable from '@/Components/Shared/DataTable.vue';

export default {
    name: 'DecoupagesProgrammesList',
    components: {
        DataTable,
    },
    data() {
        return {
            headers: [
                {
                    text: 'Programme',
                    value: 'programme_id.specialite_id.nom_fr',
                },
                { text: 'Module', value: 'module_id.titre_fr' },
                { text: 'Mode Formation', value: 'mode_formation_id.nom' },
                { text: 'Heures Centre', value: 'heures_centre' },
                { text: 'Heures Entreprise', value: 'heures_entreprise' },
                { text: 'Actions', value: 'actions', sortable: false },
            ],
            decoupages: [],
        };
    },
    created() {
        this.fetchDecoupages();
    },
    methods: {
        async fetchDecoupages() {
            try {
                const response = await this.$axios.get(
                    '/api/decoupages-programmes'
                );
                this.decoupages = response.data;
            } catch (error) {
                console.error(
                    'Erreur lors du chargement des découpages:',
                    error
                );
            }
        },
        async deleteDecoupage(item) {
            if (confirm('Voulez-vous vraiment supprimer ce découpage ?')) {
                try {
                    await this.$axios.delete(
                        `/api/decoupages-programmes/${item.id}`
                    );
                    console.log('Découpage supprimé:', item);
                    this.fetchDecoupages();
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
