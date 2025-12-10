<template>
    <form @submit.prevent="submitForm">
        <div class="form-group">
            <label for="specialite_id">Spécialité</label>
            <select v-model="form.specialite_id" id="specialite_id" required>
                <option value="">Sélectionner une spécialité</option>
                <option
                    v-for="specialite in specialites"
                    :key="specialite.id"
                    :value="specialite.id"
                >
                    {{ specialite.nom_fr }}
                </option>
            </select>
        </div>
        <div class="form-group">
            <label for="version">Version</label>
            <input
                v-model="form.version"
                id="version"
                type="text"
                placeholder="Ex: 1.0"
            />
        </div>
        <div class="form-group">
            <label for="date_debut">Date Début</label>
            <input v-model="form.date_debut" id="date_debut" type="date" />
        </div>
        <div class="form-group">
            <label for="date_fin">Date Fin</label>
            <input v-model="form.date_fin" id="date_fin" type="date" />
        </div>
        <div class="form-group">
            <label for="actif">Actif</label>
            <input v-model="form.actif" id="actif" type="checkbox" />
        </div>
        <div class="form-group">
            <label for="observation">Observation</label>
            <textarea
                v-model="form.observation"
                id="observation"
                placeholder="Notes supplémentaires"
            ></textarea>
        </div>
        <button type="submit">Enregistrer</button>
        <button type="button" @click="$emit('close')">Annuler</button>
    </form>
</template>

<script>
export default {
    name: 'ProgrammeForm',
    props: {
        programme: {
            type: Object,
            default: null,
        },
    },
    data() {
        return {
            form: {
                id: null,
                specialite_id: '',
                version: '',
                date_debut: '',
                date_fin: '',
                actif: true,
                observation: '',
            },
            specialites: [],
        };
    },
    created() {
        if (this.programme) {
            Object.assign(this.form, this.programme);
        }
        this.fetchSpecialites();
    },
    methods: {
        async fetchSpecialites() {
            try {
                const response = await this.$axios.get('/api/specialites');
                this.specialites = response.data;
            } catch (error) {
                console.error(
                    'Erreur lors du chargement des spécialités:',
                    error
                );
            }
        },
        submitForm() {
            this.$emit('save', this.form);
        },
    },
};
</script>

<style scoped>
.form-group {
    margin-bottom: 15px;
}
label {
    display: block;
    margin-bottom: 5px;
}
input,
select,
textarea {
    width: 100%;
    padding: 8px;
    box-sizing: border-box;
}
button {
    padding: 10px 20px;
    margin-right: 10px;
    background-color: #28a745;
    color: white;
    border: none;
    cursor: pointer;
}
button[type='button'] {
    background-color: #6c757d;
}
button:hover {
    opacity: 0.8;
}
</style>
