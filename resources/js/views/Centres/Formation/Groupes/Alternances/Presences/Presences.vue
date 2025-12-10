<template>
    <div>
        <h1>Gestion des Présences</h1>
        <Button
            label="Ajouter Présence"
            @click="showModal = true"
            class="p-button-primary mb-3"
        />
        <PresencesList @edit="openEditModal" />
        <Dialog
            v-model:visible="showModal"
            header="Ajouter/Modifier Présence"
            :style="{ width: '50vw' }"
        >
            <PresenceForm :presence="selectedPresence" @save="handleSave" />
        </Dialog>
    </div>
</template>

<script>
import { ref } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import PresencesList from '@/Components/Centres/Formation/Groupes/Alternances/Presences/PresenceModuleGroupeList.vue';
import PresenceForm from '@/Components/Centres/Formation/Groupes/Alternances/Presences/PresenceForm.vue';
import axios from '@/axios.js';

export default {
    components: { Button, Dialog, PresencesList, PresenceForm },
    setup() {
        const showModal = ref(false);
        const selectedPresence = ref(null);

        const openEditModal = (presence) => {
            selectedPresence.value = presence;
            showModal.value = true;
        };

        const handleSave = async (data) => {
            if (selectedPresence.value?.id) {
                await axios.put(
                    `/api/presences/${selectedPresence.value.id}`,
                    data
                );
            } else {
                await axios.post('/api/presences', data);
            }
            showModal.value = false;
            selectedPresence.value = null;
        };

        return { showModal, selectedPresence, openEditModal, handleSave };
    },
};
</script>
