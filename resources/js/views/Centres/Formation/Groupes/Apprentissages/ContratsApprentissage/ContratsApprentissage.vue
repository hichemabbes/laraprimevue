<template>
    <div>
        <h1>Gestion des Contrats d'Apprentissage</h1>
        <Button
            label="Ajouter Contrat"
            @click="showModal = true"
            class="p-button-primary mb-3"
        />
        <ContratsApprentissageList @edit="openEditModal" />
        <Dialog
            v-model:visible="showModal"
            header="Ajouter/Modifier Contrat"
            :style="{ width: '50vw' }"
        >
            <ContratApprentissageForm
                :contrat="selectedContrat"
                @save="handleSave"
            />
        </Dialog>
    </div>
</template>

<script>
import { ref } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import ContratsApprentissageList from '@/Components/Centres/Formation/Groupes/Apprentissages/ContratsApprentissage/ContratsApprentissageList.vue';
import ContratApprentissageForm from '@/Components/Centres/Formation/Groupes/Apprentissages/ContratsApprentissage/ContratApprentissageForm.vue';
import axios from '@/axios.js';

export default {
    components: {
        Button,
        Dialog,
        ContratsApprentissageList,
        ContratApprentissageForm,
    },
    setup() {
        const showModal = ref(false);
        const selectedContrat = ref(null);

        const openEditModal = (contrat) => {
            selectedContrat.value = contrat;
            showModal.value = true;
        };

        const handleSave = async (data) => {
            if (selectedContrat.value?.id) {
                await axios.put(
                    `/api/contrats-apprentissage/${selectedContrat.value.id}`,
                    data
                );
            } else {
                await axios.post('/api/contrats-apprentissage', data);
            }
            showModal.value = false;
            selectedContrat.value = null;
        };

        return { showModal, selectedContrat, openEditModal, handleSave };
    },
};
</script>
