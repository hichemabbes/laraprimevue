<template>
    <div>
        <h1>Gestion des Rapports d'Absence</h1>
        <Button
            label="Ajouter Rapport"
            @click="showModal = true"
            class="p-button-primary mb-3"
        />
        <RapportsAbsencesList @edit="openEditModal" />
        <Dialog
            v-model:visible="showModal"
            header="Ajouter/Modifier Rapport"
            :style="{ width: '50vw' }"
        >
            <RapportAbsenceForm :rapport="selectedRapport" @save="handleSave" />
        </Dialog>
    </div>
</template>

<script>
import { ref } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import RapportsAbsencesList from '@/Components/Centres/Formation/Groupes/Alternances/Presences/RapportAbsenceGroupeList.vue';
import RapportAbsenceForm from '@/Components/Centres/Formation/Groupes/Alternances/Presences/RapportAbsenceForm.vue';
import axios from '@/axios.js';

export default {
    components: { Button, Dialog, RapportsAbsencesList, RapportAbsenceForm },
    setup() {
        const showModal = ref(false);
        const selectedRapport = ref(null);

        const openEditModal = (rapport) => {
            selectedRapport.value = rapport;
            showModal.value = true;
        };

        const handleSave = async (data) => {
            if (selectedRapport.value?.id) {
                await axios.put(
                    `/api/rapports-absences/${selectedRapport.value.id}`,
                    data
                );
            } else {
                await axios.post('/api/rapports-absences', data);
            }
            showModal.value = false;
            selectedRapport.value = null;
        };

        return { showModal, selectedRapport, openEditModal, handleSave };
    },
};
</script>
