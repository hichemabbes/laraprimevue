<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :style="{ width: '70vw' }"
        header="Erreurs d'importation"
        :modal="true"
    >
        <div v-if="errors.length > 0">
            <p>Nombre total d'erreurs : {{ errors.length }}</p>
            <DataTable :value="errors" scrollable scrollHeight="400px">
                <!-- Colonne Ligne -->
                <Column
                    field="line"
                    header="Ligne"
                    style="min-width: 100px"
                ></Column>

                <!-- Colonne Données -->
                <Column field="data" header="Données" style="min-width: 300px">
                    <template #body="{ data }">
                        <InputText
                            v-model="data.data"
                            type="text"
                            placeholder="Modifier les données"
                            style="width: 100%"
                        />
                    </template>
                </Column>

                <!-- Colonne Message d'erreur -->
                <Column
                    field="message"
                    header="Message d'erreur"
                    style="min-width: 300px"
                ></Column>

                <!-- Colonne Actions -->
                <Column header="Actions" style="min-width: 150px">
                    <template #body="{ data }">
                        <Button
                            icon="pi pi-check"
                            class="p-button-rounded p-button-success mr-2"
                            @click="retryImport(data)"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>
        <div v-else>
            <p>Aucune erreur détectée.</p>
        </div>
        <template #footer>
            <Button label="Fermer" icon="pi pi-times" @click="closePopup" />
        </template>
    </Dialog>
</template>

<script>
export default {
    props: {
        errors: {
            type: Array,
            required: true,
        },
        visible: {
            type: Boolean,
            required: true,
        },
    },
    methods: {
        // Méthode pour réessayer l'importation d'une ligne corrigée
        async retryImport(error) {
            try {
                // Convertir les données corrigées en tableau
                const correctedData = error.data
                    .split(',')
                    .map((item) => item.trim());

                // Vérifications préalables
                if (
                    !correctedData[2] ||
                    !['Homologuée', 'Non Homologuée'].includes(correctedData[2])
                ) {
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'La valeur de "homologation" est invalide. Elle doit être "Homologuée" ou "Non Homologuée".',
                        life: 3000,
                    });
                    return;
                }

                // Conversion des champs numériques
                const heuresEt = parseFloat(correctedData[3]);
                const heuresEg = parseFloat(correctedData[4]);
                const dureeFormation = parseFloat(correctedData[5]);

                // Vérifier si les champs numériques sont valides
                if (
                    isNaN(heuresEt) ||
                    isNaN(heuresEg) ||
                    isNaN(dureeFormation)
                ) {
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Les champs "heures_et", "heures_eg" et "duree_formation" doivent être des nombres valides.',
                        life: 3000,
                    });
                    return;
                }

                // Vérifier si la spécialité existe
                try {
                    const specialiteResponse = await axios.get(
                        `/api/specialites/code/${correctedData[0]}`
                    );
                    if (
                        !specialiteResponse.data ||
                        !specialiteResponse.data.id
                    ) {
                        throw new Error(
                            `La spécialité avec le code "${correctedData[0]}" n'existe pas.`
                        );
                    }
                    const specialiteId = specialiteResponse.data.id;
                } catch (error) {
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: error.message,
                        life: 3000,
                    });
                    return;
                }

                // Vérifier si l'année existe
                try {
                    const anneeResponse = await axios.get(
                        `/api/annees_formation/intitule/${correctedData[1]}`
                    );
                    if (!anneeResponse.data || !anneeResponse.data.id) {
                        throw new Error(
                            `L'année "${correctedData[1]}" n'existe pas.`
                        );
                    }
                    const anneeId = anneeResponse.data.id;
                } catch (error) {
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: error.message,
                        life: 3000,
                    });
                    return;
                }

                // Préparer les données corrigées
                const payload = {
                    specialite_id: specialiteId,
                    annee_id: anneeId,
                    homologation: correctedData[2],
                    heures_et: heuresEt.toString(),
                    heures_eg: heuresEg.toString(),
                    duree_formation: dureeFormation.toFixed(1),
                    observation: correctedData[6] || null, // Gérer les valeurs vides pour "observation"
                };

                // Envoyer les données corrigées au backend
                const response = await axios.post(
                    '/api/informations-specialites',
                    payload
                );

                // Émettre un événement pour informer le parent que la ligne a été importée avec succès
                this.$emit('line-imported', error);

                // Afficher un message de succès
                this.$toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'La ligne a été importée avec succès.',
                    life: 3000,
                });
            } catch (error) {
                // Gérer les erreurs spécifiques du backend
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    let errorMessage =
                        'Veuillez corriger les erreurs suivantes : ';
                    for (const field in errors) {
                        errorMessage += `${field}: ${errors[field][0]}. `;
                    }
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: errorMessage,
                        life: 5000,
                    });
                } else {
                    // Afficher un message générique en cas d'autres erreurs
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Échec de l'importation de la ligne. Veuillez vérifier les données corrigées.",
                        life: 3000,
                    });
                }
            }
        },

        closePopup() {
            this.$emit('close'); // Émettre un événement pour informer le parent que le popup est fermé
            this.$emit('update:visible', false); // Fermer le popup
        },
    },
};
</script>

<style scoped>
pre {
    white-space: pre-wrap;
    word-wrap: break-word;
    background-color: #f5f5f5;
    padding: 8px;
    border-radius: 4px;
    margin-bottom: 8px;
}
</style>
