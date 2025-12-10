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

                // Récupérer l'ID du secteur correspondant au code_secteur
                const secteurResponse = await axios.get(
                    `/api/getsecteur?code=${correctedData[3]}`
                );
                const secteurId = secteurResponse.data.id;
                // Récupérer l'ID du secteur correspondant au code_secteur-secteur
                const sousSecteurResponse = await axios.get(
                    `/api/getsoussecteur?code=${correctedData[4]}`
                );
                const sousSecteurId = sousSecteurResponse.data.id;
                // Envoyer les données corrigées au backend
                const response = await axios.post('/api/specialites', {
                    code: correctedData[0],
                    nom_fr: correctedData[1],
                    nom_ar: correctedData[2],
                    secteur_id: secteurId,
                    sous_secteur_id: sousSecteurId,
                    type: correctedData[5],
                    diplome: correctedData[6],
                    date_creation_specialite: correctedData[7],
                    statut: correctedData[8],
                    observation: correctedData[9],
                    date_annulation_specialite: correctedData[10],
                });
                // Émettre un événement pour informer le parent que la ligne a été importée avec succès
                this.$emit('line-imported', error);
                // Afficher un message de succès
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'La ligne a été importée avec succès.',
                    life: 3000,
                });
            } catch (error) {
                // Afficher un message d'erreur en cas d'échec
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Échec de l'importation de la ligne.",
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
