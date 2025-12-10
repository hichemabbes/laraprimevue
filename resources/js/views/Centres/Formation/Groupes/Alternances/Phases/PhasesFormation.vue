<template>
    <div>
        <h1>Gestion des Phases de Formation</h1>
        <Button
            label="Ajouter Phase"
            @click="showModal = true"
            class="p-button-primary mb-3"
        />
        <PhasesFormationList @edit="openEditModal" />
        <Dialog
            v-model:visible="showModal"
            header="Ajouter/Modifier Phase"
            :style="{ width: '50vw' }"
        >
            <PhaseFormationForm :phase="selectedPhase" @save="handleSave" />
        </Dialog>
    </div>
</template>

<script>
import { ref } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import PhasesFormationList from '@/Components/Centres/Formation/Groupes/Alternances/Phases/PhasesFormationList.vue';
import PhaseFormationForm from '@/Components/Centres/Formation/Groupes/Alternances/Phases/PhaseFormationForm.vue';
import axios from '@/axios.js';

export default {
    components: { Button, Dialog, PhasesFormationList, PhaseFormationForm },
    setup() {
        const showModal = ref(false);
        const selectedPhase = ref(null);

        const openEditModal = (phase) => {
            selectedPhase.value = phase;
            showModal.value = true;
        };

        const handleSave = async (data) => {
            if (selectedPhase.value?.id) {
                await axios.put(
                    `/api/phases-formation/${selectedPhase.value.id}`,
                    data
                );
            } else {
                await axios.post('/api/phases-formation', data);
            }
            showModal.value = false;
            selectedPhase.value = null;
        };

        return { showModal, selectedPhase, openEditModal, handleSave };
    },
};
</script>
