<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :style="{ width: '70vw' }"
        header="Erreurs d'importation des modules"
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
                            @click="
                                retryImport({
                                    ...data,
                                    specialiteId: currentSpecialiteId,
                                })
                            "
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
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

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
        currentSpecialiteId: {
            type: Number,
            required: true,
        },
    },
    emits: ['update:visible', 'line-imported', 'close'],
    setup() {
        const toast = useToast();
        return { toast };
    },
    methods: {
        async retryImport(error) {
            try {
                // Convertir les données corrigées en tableau
                const correctedData = error.data
                    .split(',')
                    .map((item) => item.trim());
                console.log('Données corrigées :', correctedData);

                // Vérifier que les données ont le bon nombre de colonnes
                if (correctedData.length < 10) {
                    throw new Error(
                        'Les données corrigées ne contiennent pas le bon nombre de colonnes.'
                    );
                }

                // Vérifier si l'ID de la spécialité est défini
                if (!error.specialiteId) {
                    throw new Error("L'ID de la spécialité est manquant.");
                }

                // Préparer le champ 'annees' comme un tableau
                let annees = [];
                const rawAnnees = correctedData[3]
                    ? correctedData[3].replace(/[\[\]]/g, '').trim()
                    : '';
                if (rawAnnees) {
                    // Convertir les années en tableau JSON
                    annees = rawAnnees.split(',').map((year) => year.trim());
                }

                // Préparer les données pour l'envoi au backend
                const moduleData = {
                    specialite_id: error.specialiteId,
                    code: correctedData[0] || '',
                    titre: correctedData[1] || '',
                    type: correctedData[2] || '',
                    annees: annees, // Envoyer le tableau d'années (peut être vide)
                    heures_theorique: correctedData[4]
                        ? String(correctedData[4].trim())
                        : null,
                    heures_pratiques: correctedData[5]
                        ? String(correctedData[5].trim())
                        : null,
                    heures_evaluation: correctedData[6]
                        ? String(correctedData[6].trim())
                        : null,
                    heures_totales: correctedData[7]
                        ? String(correctedData[7].trim())
                        : null,
                    contenu: null, // Ne pas envoyer de contenu si ce n'est pas un fichier PDF
                    observation: correctedData[9] || null,
                };

                // Afficher les données préparées pour débogage
                console.log('Données préparées :', moduleData);

                // Valider les champs obligatoires avant l'envoi
                if (!moduleData.code) {
                    throw new Error("Le champ 'code' est requis.");
                }
                if (!moduleData.titre) {
                    throw new Error("Le champ 'titre' est requis.");
                }
                if (
                    ![
                        'Enseignement spécifique',
                        'Enseignement général',
                    ].includes(moduleData.type)
                ) {
                    throw new Error(
                        "Le champ 'type' doit être 'Enseignement spécifique' ou 'Enseignement général'."
                    );
                }

                // Envoyer les données corrigées au backend
                const response = await axios.post('/api/modules', moduleData);

                // Émettre un événement pour informer le parent que la ligne a été importée avec succès
                this.$emit('line-imported', error);

                // Afficher un message de succès
                this.$toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Le module a été importé avec succès.',
                    life: 3000,
                });
            } catch (error) {
                // Extraire le message d'erreur depuis la réponse du backend
                let errorMessage =
                    error.response?.data?.message ||
                    error.message ||
                    "Échec de l'importation du module.";
                console.error('Erreur complète :', error.response?.data);

                // Afficher un message d'erreur en cas d'échec
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: errorMessage,
                    life: 3000,
                });
            }
        },

        closePopup() {
            this.$emit('close');
            this.$emit('update:visible', false);
        },
    },
};
</script>

<style scoped>
pre {
    white-space: pre-wrap;
    word-wrap: break-word;
}
</style>
