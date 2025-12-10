<template>
    <div>
        <h1>Gestion des Stages</h1>
        <Button
            label="Ajouter Stage"
            @click="showModal = true"
            class="p-button-primary mb-3"
        />
        <StagesList @edit="openEditModal" />
        <Dialog
            v-model:visible="showModal"
            header="Ajouter/Modifier Stage"
            :style="{ width: '50vw' }"
        >
            <StageForm :stage="selectedStage" @save="handleSave" />
        </Dialog>
    </div>
</template>

<script>
import { ref } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import StagesList from '@/Components/Centres/Formation/Groupes/Alternances/Stages/StagesList.vue';
import StageForm from '@/Components/Centres/Formation/Groupes/Alternances/Stages/StageForm.vue';
import axios from '@/axios.js';

export default {
    components: { Button, Dialog, StagesList, StageForm },
    setup() {
        const showModal = ref(false);
        const selectedStage = ref(null);

        const openEditModal = (stage) => {
            selectedStage.value = stage;
            showModal.value = true;
        };

        const handleSave = async (data) => {
            if (selectedStage.value?.id) {
                await axios.put(`/api/stages/${selectedStage.value.id}`, data);
            } else {
                await axios.post('/api/stages', data);
            }
            showModal.value = false;
            selectedStage.value = null;
        };

        return { showModal, selectedStage, openEditModal, handleSave };
    },
};
</script>
