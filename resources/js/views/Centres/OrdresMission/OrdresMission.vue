<template>
    <div>
        <h1>Gestion des Ordres de Mission</h1>
        <Button
            label="Ajouter Ordre"
            @click="showModal = true"
            class="p-button-primary mb-3"
        />
        <OrdresMissionList @edit="openEditModal" />
        <Dialog
            v-model:visible="showModal"
            header="Ajouter/Modifier Ordre"
            :style="{ width: '50vw' }"
        >
            <OrdreMissionForm :ordre="selectedOrdre" @save="handleSave" />
        </Dialog>
    </div>
</template>

<script>
import { ref } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import OrdresMissionList from '@/Components/Centres/OrdresMission/OrdresMissionList.vue';
import OrdreMissionForm from '@/Components/Centres/OrdresMission/OrdreMissionForm.vue';
import axios from '@/axios.js';

export default {
    components: { Button, Dialog, OrdresMissionList, OrdreMissionForm },
    setup() {
        const showModal = ref(false);
        const selectedOrdre = ref(null);

        const openEditModal = (ordre) => {
            selectedOrdre.value = ordre;
            showModal.value = true;
        };

        const handleSave = async (data) => {
            if (selectedOrdre.value?.id) {
                await axios.put(
                    `/api/ordres-mission/${selectedOrdre.value.id}`,
                    data
                );
            } else {
                await axios.post('/api/ordres-mission', data);
            }
            showModal.value = false;
            selectedOrdre.value = null;
        };

        return { showModal, selectedOrdre, openEditModal, handleSave };
    },
};
</script>
