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
            <label for="code">Code</label>
            <input
                v-model="form.code"
                id="code"
                type="text"
                placeholder="Ex: MOD001"
                required
            />
        </div>
        <div class="form-group">
            <label for="titre_fr">Titre (FR)</label>
            <input
                v-model="form.titre_fr"
                id="titre_fr"
                type="text"
                placeholder="Titre en français"
            />
        </div>
        <div class="form-group">
            <label for="titre_ar">Titre (AR)</label>
            <input
                v-model="form.titre_ar"
                id="titre_ar"
                type="text"
                placeholder="Titre en arabe"
            />
        </div>
        <div class="form-group">
            <label for="heures_theoriques">Heures Théoriques</label>
            <input
                v-model="form.heures_theoriques"
                id="heures_theoriques"
                type="number"
                placeholder="Ex: 30"
            />
        </div>
        <div class="form-group">
            <label for="heures_pratiques">Heures Pratiques</label>
            <input
                v-model="form.heures_pratiques"
                id="heures_pratiques"
                type="number"
                placeholder="Ex: 20"
            />
        </div>
        <div class="form-group">
            <label for="heures_evaluation">Heures Évaluation</label>
            <input
                v-model="form.heures_evaluation"
                id="heures_evaluation"
                type="number"
                placeholder="Ex: 5"
            />
        </div>
        <div class="form-group">
            <label for="type_id">Type</label>
            <select v-model="form.type_id" id="type_id">
                <option value="">Sélectionner un type</option>
                <option v-for="type in types" :key="type.id" :value="type.id">
                    {{ type.nom }}
                </option>
            </select>
        </div>
        <div class="form-group">
            <label for="categorie_module_id">Catégorie</label>
            <select v-model="form.categorie_module_id" id="categorie_module_id">
                <option value="">Sélectionner une catégorie</option>
                <option
                    v-for="categorie in categories"
                    :key="categorie.id"
                    :value="categorie.id"
                >
                    {{ categorie.nom }}
                </option>
            </select>
        </div>
        <div class="form-group">
            <label for="toujours_centre">Toujours au Centre</label>
            <input
                v-model="form.toujours_centre"
                id="toujours_centre"
                type="checkbox"
            />
        </div>
        <div class="form-group">
            <label for="contenu_pdf">Contenu PDF</label>
            <input
                v-model="form.contenu_pdf"
                id="contenu_pdf"
                type="text"
                placeholder="Chemin ou URL du PDF"
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
    name: 'ModuleForm',
    props: {
        module: {
            type: Object,
            default: null,
        },
    },
    data() {
        return {
            form: {
                id: null,
                programme_id: '',
                code: '',
                titre_fr: '',
                titre_ar: '',
                heures_theoriques: '',
                heures_pratiques: '',
                heures_evaluation: '',
                type_id: '',
                categorie_module_id: '',
                toujours_centre: false,
                contenu_pdf: '',
                observation: '',
            },
            programmes: [],
            types: [],
            categories: [],
        };
    },
    created() {
        if (this.module) {
            Object.assign(this.form, this.module);
        }
        this.fetchOptions();
    },
    methods: {
        async fetchOptions() {
            try {
                const [programmesRes, typesRes, categoriesRes] =
                    await Promise.all([
                        this.$axios.get('/api/programmes'),
                        this.$axios.get('/api/options_listes?type=type_module'),
                        this.$axios.get(
                            '/api/options_listes?type=categorie_module'
                        ),
                    ]);
                this.programmes = programmesRes.data;
                this.types = typesRes.data;
                this.categories = categoriesRes.data;
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
