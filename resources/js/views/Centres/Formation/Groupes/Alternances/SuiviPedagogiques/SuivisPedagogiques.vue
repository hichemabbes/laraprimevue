<template>
    <div>
        <h1>Gestion des Suivis Pédagogiques</h1>
        <Button
            label="Ajouter Suivi"
            @click="showModal = true"
            class="p-button-primary mb-3"
        />
        <SuivisPedagogiquesList @edit="openEditModal" />
        <Dialog
            v-model:visible="showModal"
            header="Ajouter/Modifier Suivi"
            :style="{ width: '50vw' }"
        >
            <SuiviPedagogiqueForm :suivi="selectedSuivi" @save="handleSave" />
        </Dialog>
    </div>
</template>

<script>
import { ref } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import SuivisPedagogiquesList from '@/Components/Centres/Formation/Groupes/Alternances/SuivisPedagogiques/SuivisPedagogiquesList.vue';
import SuiviPedagogiqueForm from '@/Components/Centres/Formation/Groupes/Alternances/SuivisPedagogiques/SuiviPedagogiqueForm.vue';
import axios from '@/axios.js';

export default {
    components: {
        Button,
        Dialog,
        SuivisPedagogiquesList,
        SuiviPedagogiqueForm,
    },
    setup() {
        const showModal = ref(false);
        const selectedSuivi = ref(null);

        const openEditModal = (suivi) => {
            selectedSuivi.value = suivi;
            showModal.value = true;
        };

        const handleSave = async (data) => {
            if (selectedSuivi.value?.id) {
                await axios.put(
                    `/api/suivis-pedagogiques/${selectedSuivi.value.id}`,
                    data
                );
            } else {
                await axios.post('/api/suivis-pedagogiques', data);
            }
            showModal.value = false;
            selectedSuivi.value = null;
        };

        return { showModal, selectedSuivi, openEditModal, handleSave };
    },
};
</script>
