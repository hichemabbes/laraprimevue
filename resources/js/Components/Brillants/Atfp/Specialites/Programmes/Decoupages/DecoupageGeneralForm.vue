<template>
    <form @submit.prevent="submitForm">
        <div class="form-group">
            <label for="programme_id">Programme</label>
            <select v-model="form.programme_id" id="programme_id" required>
                <option value="">Sélectionner un programme</option>
                <option
                    v-for="programme in programmes"
                    :key="programme.id"
                    :value="programme.id"
                >
                    {{ programme.specialite_id.nom_fr }} -
                    {{ programme.version }}
                </option>
            </select>
        </div>
        <div class="form-group">
            <label for="module_id">Module</label>
            <select v-model="form.module_id" id="module_id" required>
                <option value="">Sélectionner un module</option>
                <option
                    v-for="module in modules"
                    :key="module.id"
                    :value="module.id"
                >
                    {{ module.titre_fr }}
                </option>
            </select>
        </div>
        <div class="form-group">
            <label for="mode_formation_id">Mode de Formation</label>
            <select
                v-model="form.mode_formation_id"
                id="mode_formation_id"
                required
            >
                <option value="">Sélectionner un mode</option>
                <option v-for="mode in modes" :key="mode.id" :value="mode.id">
                    {{ mode.nom }}
                </option>
            </select>
        </div>
        <div class="form-group">
            <label for="heures_centre">Heures Centre</label>
            <input
                v-model="form.heures_centre"
                id="heures_centre"
                type="number"
                placeholder="Ex: 20"
            />
        </div>
        <div class="form-group">
            <label for="heures_entreprise">Heures Entreprise</label>
            <input
                v-model="form.heures_entreprise"
                id="heures_entreprise"
                type="number"
                placeholder="Ex: 10"
            />
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
    name: 'DecoupageProgrammeForm',
    props: {
        decoupage: {
            type: Object,
            default: null,
        },
    },
    data() {
        return {
            form: {
                id: null,
                programme_id: '',
                module_id: '',
                mode_formation_id: '',
                heures_centre: 0,
                heures_entreprise: 0,
                observation: '',
            },
            programmes: [],
            modules: [],
            modes: [],
        };
    },
    created() {
        if (this.decoupage) {
            Object.assign(this.form, this.decoupage);
        }
        this.fetchOptions();
    },
    methods: {
        async fetchOptions() {
            try {
                const [programmesRes, modulesRes, modesRes] = await Promise.all(
                    [
                        this.$axios.get('/api/programmes'),
                        this.$axios.get('/api/modules'),
                        this.$axios.get(
                            '/api/options_listes?type=mode_formation'
                        ),
                    ]
                );
                this.programmes = programmesRes.data;
                this.modules = modulesRes.data;
                this.modes = modesRes.data;
            } catch (error) {
                console.error('Erreur lors du chargement des options:', error);
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
