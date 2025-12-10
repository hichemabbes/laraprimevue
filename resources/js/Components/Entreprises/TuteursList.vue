<template>
    <DataTable :headers="headers" :items="tuteurs" :items-per-page="10">
        <template v-slot:actions="{ item }">
            <button @click="$emit('edit', item)">Modifier</button>
            <button @click="deleteTuteur(item)">Supprimer</button>
        </template>
    </DataTable>
</template>

<script>
import DataTable from '@/Components/Shared/DataTable.vue';

export default {
    name: 'TuteursList',
    components: {
        DataTable,
    },
    data() {
        return {
            headers: [
                { text: 'Nom (FR)', value: 'nom_fr' },
                { text: 'Prénom (FR)', value: 'prenom_fr' },
                { text: 'Nom (AR)', value: 'nom_ar' },
                { text: 'Prénom (AR)', value: 'prenom_ar' },
                { text: 'CIN', value: 'cin' },
                { text: 'Téléphone', value: 'telephone' },
                { text: 'Email', value: 'email' },
                { text: 'Entreprise', value: 'entreprise.nom_fr' },
                { text: 'Actions', value: 'actions', sortable: false },
            ],
            tuteurs: [],
        };
    },
    created() {
        this.fetchTuteurs();
    },
    methods: {
        async fetchTuteurs() {
            try {
                const response = await this.$axios.get('/api/tuteurs');
                this.tuteurs = response.data;
            } catch (error) {
                console.error('Erreur lors du chargement des tuteurs:', error);
            }
        },
        async deleteTuteur(item) {
            if (confirm('Voulez-vous vraiment supprimer ce tuteur ?')) {
                try {
                    await this.$axios.delete(`/api/tuteurs/${item.id}`);
                    console.log('Tuteur supprimé:', item);
                    this.fetchTuteurs(); // Rafraîchir la liste
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
