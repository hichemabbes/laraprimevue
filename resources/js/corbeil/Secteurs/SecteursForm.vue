<template>
    <Dialog
        v-model:visible="visible"
        header="Ajouter un Module"
        :modal="true"
        :style="{ width: '50vw' }"
    >
        <form @submit.prevent="submitForm">
            <div class="p-fluid">
                <div class="p-field">
                    <label for="code">Code</label>
                    <InputText id="code" v-model="module.code" required />
                </div>
                <div class="p-field">
                    <label for="titre">Titre</label>
                    <InputText id="titre" v-model="module.titre" required />
                </div>
                <div class="p-field">
                    <label for="type">Type</label>
                    <Dropdown
                        id="type"
                        v-model="module.type"
                        :options="[
                            'Enseignement spécifique',
                            'Enseignement général',
                        ]"
                        placeholder="Sélectionner un type"
                    />
                </div>
                <div class="p-field">
                    <label for="heures_theorique">Heures Théoriques</label>
                    <InputNumber
                        id="heures_theorique"
                        v-model="module.heures_theorique"
                    />
                </div>
                <div class="p-field">
                    <label for="heures_pratiques">Heures Pratiques</label>
                    <InputNumber
                        id="heures_pratiques"
                        v-model="module.heures_pratiques"
                    />
                </div>
                <div class="p-field">
                    <label for="heures_evaluation">Heures Evaluation</label>
                    <InputNumber
                        id="heures_evaluation"
                        v-model="module.heures_evaluation"
                    />
                </div>
                <div class="p-field">
                    <label for="heures_totales">Heures Totales</label>
                    <InputNumber
                        id="heures_totales"
                        v-model="module.heures_totales"
                    />
                </div>
                <div class="p-field">
                    <label for="contenu">Contenu</label>
                    <Textarea id="contenu" v-model="module.contenu" rows="3" />
                </div>
                <div class="p-field">
                    <label for="annees">Années</label>
                    <MultiSelect
                        id="annees"
                        v-model="module.annees"
                        :options="anneesOptions"
                        optionLabel="intitule"
                        placeholder="Sélectionner les années"
                    />
                </div>
                <div class="p-field">
                    <label for="observation">Observation</label>
                    <Textarea
                        id="observation"
                        v-model="module.observation"
                        rows="2"
                    />
                </div>
            </div>
            <div class="p-dialog-footer">
                <Button
                    type="button"
                    label="Annuler"
                    class="p-button-text"
                    @click="closeDialog"
                />
                <Button
                    type="submit"
                    label="Enregistrer"
                    class="p-button-success"
                />
            </div>
        </form>
    </Dialog>
</template>

<script>
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

export default {
    props: {
        specialiteId: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            visible: false,
            module: {
                code: '',
                titre: '',
                type: '',
                heures_theorique: null,
                heures_pratiques: null,
                heures_evaluation: null,
                heures_totales: null,
                contenu: '',
                annees: [],
                observation: '',
            },
            anneesOptions: [],
        };
    },
    methods: {
        async fetchAnnees() {
            try {
                const response = await axios.get('/api/annees-formation');
                this.anneesOptions = response.data;
            } catch (error) {
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les années.',
                    life: 3000,
                });
            }
        },
        openDialog() {
            this.visible = true;
            this.fetchAnnees();
        },
        closeDialog() {
            this.visible = false;
            // Réinitialiser les données du formulaire
            this.module = {
                code: '',
                titre: '',
                type: '',
                heures_theorique: null,
                heures_pratiques: null,
                heures_evaluation: null,
                heures_totales: null,
                contenu: '',
                annees: [],
                observation: '',
            };
        },
        async submitForm() {
            const formData = new FormData();
            formData.append('specialite_id', this.specialiteId);
            formData.append('code', this.module.code);
            formData.append('titre', this.module.titre);
            formData.append('type', this.module.type);
            formData.append('annees', JSON.stringify(this.module.annees)); // Envoyer annees comme JSON
            formData.append('heures_theorique', this.module.heures_theorique);
            formData.append('heures_pratiques', this.module.heures_pratiques);
            formData.append('heures_evaluation', this.module.heures_evaluation);
            formData.append('heures_totales', this.module.heures_totales);
            formData.append('observation', this.module.observation);

            // Gérer le champ "contenu" si c'est un fichier
            if (this.module.contenu) {
                formData.append('contenu', this.module.contenu); // Assurez-vous que "contenu" est un fichier valide
            }

            try {
                const response = await axios.post('/api/modules', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });
                this.$toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'ModuleGeneral ajouté avec succès',
                    life: 3000,
                });
                this.closeDialog(); // Fermer le modal après l'ajout réussi
                this.$emit('module-added'); // Émettre un événement pour informer le parent
            } catch (error) {
                console.error('Erreur:', error);
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        "Échec de l'ajout du module",
                    life: 3000,
                });
            }
        },
    },
    created() {
        // Initialiser le composant Toast
        this.$toast = useToast();
    },
};
</script>

<style scoped>
/* Ajoutez vos styles ici si nécessaire */
</style>
