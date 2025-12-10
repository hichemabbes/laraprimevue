<template>
    <div>
        <h1>Affectations Stagiaires aux Groupes</h1>
        <Button
            label="Ajouter Affectation"
            @click="showModal = true"
            class="p-button-primary mb-3"
        />
        <StagiairesGroupesList @edit="openEditModal" />
        <Dialog
            v-model:visible="showModal"
            header="Ajouter/Modifier Affectation"
            :style="{ width: '50vw' }"
        >
            <StagiaireGroupeForm
                :stagiaireGroupe="selectedStagiaireGroupe"
                @save="handleSave"
            />
        </Dialog>
    </div>
</template>

<script>
import { ref } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import StagiairesGroupesList from '@/Components/Centres/Formation/Groupes/Alternances/Groupes/StagiairesGroupesList.vue';
import StagiaireGroupeForm from '@/Components/Centres/Formation/Groupes/Alternances/Groupes/StagiaireGroupeForm.vue';
import axios from '@/axios.js';

export default {
    components: { Button, Dialog, StagiairesGroupesList, StagiaireGroupeForm },
    setup() {
        const showModal = ref(false);
        const selectedStagiaireGroupe = ref(null);

        const openEditModal = (stagiaireGroupe) => {
            selectedStagiaireGroupe.value = stagiaireGroupe;
            showModal.value = true;
        };

        const handleSave = async (data) => {
            if (selectedStagiaireGroupe.value?.id) {
                await axios.put(
                    `/api/stagiaires-groupes/${selectedStagiaireGroupe.value.id}`,
                    data
                );
            } else {
                await axios.post('/api/stagiaires-groupes', data);
            }
            showModal.value = false;
            selectedStagiaireGroupe.value = null;
        };

        return {
            showModal,
            selectedStagiaireGroupe,
            openEditModal,
            handleSave,
        };
    },
};
</script>
