<template>
    <div>
        <h1>Historique des Groupes</h1>
        <Button
            label="Ajouter Historique"
            @click="showModal = true"
            class="p-button-primary mb-3"
        />
        <GroupesHistoriqueList @edit="openEditModal" />
        <Dialog
            v-model:visible="showModal"
            header="Ajouter/Modifier Historique"
            :style="{ width: '50vw' }"
        >
            <GroupeHistoriqueForm
                :historique="selectedHistorique"
                @save="handleSave"
            />
        </Dialog>
    </div>
</template>

<script>
import { ref } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import GroupesHistoriqueList from '@/Components/Centres/Formation/Groupes/Alternances/Groupes/GroupesHistoriqueList.vue';
import GroupeHistoriqueForm from '@/Components/Centres/Formation/Groupes/Alternances/Groupes/GroupeHistoriqueForm.vue';
import axios from '@/axios.js';

export default {
    components: { Button, Dialog, GroupesHistoriqueList, GroupeHistoriqueForm },
    setup() {
        const showModal = ref(false);
        const selectedHistorique = ref(null);

        const openEditModal = (historique) => {
            selectedHistorique.value = historique;
            showModal.value = true;
        };

        const handleSave = async (data) => {
            if (selectedHistorique.value?.id) {
                await axios.put(
                    `/api/groupes-historique/${selectedHistorique.value.id}`,
                    data
                );
            } else {
                await axios.post('/api/groupes-historique', data);
            }
            showModal.value = false;
            selectedHistorique.value = null;
        };

        return { showModal, selectedHistorique, openEditModal, handleSave };
    },
};
</script>
