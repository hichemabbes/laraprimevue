<template>
    <div class="card">
        <TabView>
            <TabPanel header="Vue d'ensemble">
                <VueDensemble />
            </TabPanel>
            <TabPanel header="Spécialités">
                <SpecialitesList />
            </TabPanel>
            <TabPanel header="Informations Spécialités">
                <InfosSpecialites />
            </TabPanel>
            <TabPanel header="Programmes Spécialités">
                <ProgrammesSpecialites />
            </TabPanel>
            <TabPanel header="Documentations Spécialités">
                <DocumentationsSpecialites />
            </TabPanel>
        </TabView>
    </div>
</template>

<script>
import MainLayout from '@/primevue/layout/MainLayout.vue';

import VueDensemble from '@/corbeil/VueDensemble.vue';
import SpecialitesList from '@/corbeil/SpecialitesList.vue';
import InfosSpecialites from '@/corbeil/Informations/InfosSpecialites.vue';
import ProgrammesSpecialites from '@/corbeil/ProgramesSpecialites/ProgrammesSpecialites.vue';
import DocumentationsSpecialites from '@/corbeil/Documentations/DocumentationsSpecialites.vue';

export default {
    components: {
        VueDensemble,
        SpecialitesList,
        InfosSpecialites,
        ProgrammesSpecialites,
        DocumentationsSpecialites,
    },
};
</script>

<style scoped>
.card {
    overflow-x: auto;
}
</style>
