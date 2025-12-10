<template>
    <div>
        <h1>Gestion des Groupes des Centres</h1>
        <Button
            label="Ajouter Groupe"
            @click="showModal = true"
            class="p-button-primary mb-3"
        />
        <GroupesCentresList @edit="openEditModal" />
        <Dialog
            v-model:visible="showModal"
            header="Ajouter/Modifier Groupe"
            :style="{ width: '50vw' }"
        >
            <GroupeCentreForm :groupe="selectedGroupe" @save="handleSave" />
        </Dialog>
    </div>
</template>

<script>
import { ref } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import GroupesCentresList from '@/Components/Centres/Formation/Groupes/Alternances/Groupes/GroupesCentreList.vue';
import GroupeCentreForm from '@/Components/Centres/Formation/Groupes/Alternances/Groupes/GroupeCentreForm.vue';
import axios from '@/axios.js';

export default {
    components: { Button, Dialog, GroupesCentresList, GroupeCentreForm },
    setup() {
        const showModal = ref(false);
        const selectedGroupe = ref(null);

        const openEditModal = (groupe) => {
            selectedGroupe.value = groupe;
            showModal.value = true;
        };

        const handleSave = async (data) => {
            if (selectedGroupe.value?.id) {
                await axios.put(
                    `/api/groupes/${selectedGroupe.value.id}`,
                    data
                );
            } else {
                await axios.post('/api/groupes', data);
            }
            showModal.value = false;
            selectedGroupe.value = null;
        };

        return { showModal, selectedGroupe, openEditModal, handleSave };
    },
};
</script>
