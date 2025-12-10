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
                <Column
                    field="line"
                    header="Ligne"
                    style="min-width: 100px"
                ></Column>
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
                <Column
                    field="message"
                    header="Message d'erreur"
                    style="min-width: 300px"
                ></Column>
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
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';

export default {
    components: {
        Button,
        Column,
        DataTable,
        Dialog,
        InputText,
    },
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
    methods: {
        async retryImport(error) {
            try {
                const correctedData = error.data
                    .split(',')
                    .map((item) => item.trim());
                const payload = {
                    code: correctedData[0],
                    nom_fr: correctedData[1] || null,
                    nom_ar: correctedData[2] || null,
                    adresse_fr: correctedData[3] || null,
                    adresse_ar: correctedData[4] || null,
                    telephone_1: correctedData[5] || '00000000',
                    telephone_2: correctedData[6] || null,
                    fax_1: correctedData[7] || null,
                    fax_2: correctedData[8] || null,
                    email: correctedData[9] || null,
                    gouvernerat_fr: correctedData[10],
                    type: correctedData[11] || null,
                    economat_fr: correctedData[12] || null,
                    economat_ar: correctedData[13] || null,
                    classe: correctedData[14] || null,
                    logo: correctedData[15] || null,
                    statut: correctedData[16],
                    date_creation: correctedData[17] || null,
                    date_fermeture: correctedData[18] || null,
                    observation: correctedData[19] || null,
                };
                const response = await axios.post('/api/centres', payload);
                this.$emit('line-imported', error);
                this.$toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'La ligne a été importée avec succès.',
                    life: 3000,
                });
            } catch (error) {
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec de l’importation de la ligne.',
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
