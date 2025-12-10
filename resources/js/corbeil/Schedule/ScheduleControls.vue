<template>
    <div class="schedule-controls">
        <Toolbar>
            <template #start>
                <div class="flex flex-wrap gap-3">
                    <div class="p-inputgroup">
                        <span class="p-inputgroup-addon">
                            <i class="pi pi-filter"></i>
                        </span>
                        <Dropdown
                            v-model="localFilters.specialiteId"
                            :options="specialites"
                            optionLabel="nom"
                            optionValue="id"
                            placeholder="Spécialité"
                            class="w-12rem"
                            @change="handleSpecialiteChange"
                        />
                    </div>

                    <div class="p-inputgroup">
                        <span class="p-inputgroup-addon">
                            <i class="pi pi-users"></i>
                        </span>
                        <Dropdown
                            v-model="localFilters.groupeId"
                            :options="filteredGroupes"
                            optionLabel="nom"
                            optionValue="id"
                            placeholder="Groupe"
                            class="w-10rem"
                            @change="emitFilters"
                        />
                    </div>

                    <div class="p-inputgroup">
                        <span class="p-inputgroup-addon">
                            <i class="pi pi-calendar"></i>
                        </span>
                        <Dropdown
                            v-model="localFilters.phaseId"
                            :options="filteredPhases"
                            optionLabel="nom"
                            optionValue="id"
                            placeholder="Phase"
                            class="w-10rem"
                            @change="emitFilters"
                        />
                    </div>

                    <div class="p-inputgroup">
                        <span class="p-inputgroup-addon">
                            <i class="pi pi-clock"></i>
                        </span>
                        <Calendar
                            v-model="localFilters.dateRange"
                            selectionMode="range"
                            :manualInput="false"
                            dateFormat="dd/mm/yy"
                            placeholder="Période"
                            class="w-16rem"
                            @date-select="emitFilters"
                        />
                    </div>
                </div>
            </template>

            <template #end>
                <Button
                    label="Générer automatiquement"
                    icon="pi pi-magic"
                    class="p-button-help"
                    @click="showGenerateDialog = true"
                />
                <Button
                    label="Exporter"
                    icon="pi pi-file-excel"
                    class="p-button-success ml-2"
                    @click="handleExport"
                />
            </template>
        </Toolbar>

        <Dialog
            v-model:visible="showGenerateDialog"
            header="Génération automatique"
            :modal="true"
            :style="{ width: '500px' }"
        >
            <div class="p-fluid">
                <div class="field">
                    <label>Options de génération</label>
                    <div class="flex flex-column gap-2 mt-2">
                        <div class="flex align-items-center">
                            <Checkbox
                                v-model="generateOptions.respecterContraintes"
                                inputId="contraintes"
                                :binary="true"
                            />
                            <label for="contraintes" class="ml-2"
                                >Respecter toutes les contraintes</label
                            >
                        </div>
                        <div class="flex align-items-center">
                            <Checkbox
                                v-model="generateOptions.optimiserRessources"
                                inputId="ressources"
                                :binary="true"
                            />
                            <label for="ressources" class="ml-2"
                                >Optimiser l'utilisation des ressources</label
                            >
                        </div>
                        <div class="flex align-items-center">
                            <Checkbox
                                v-model="generateOptions.equilibrerCharge"
                                inputId="charge"
                                :binary="true"
                            />
                            <label for="charge" class="ml-2"
                                >Équilibrer la charge des formateurs</label
                            >
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label>Répartition des modules</label>
                    <Dropdown
                        v-model="generateOptions.repartition"
                        :options="repartitionOptions"
                        optionLabel="label"
                        optionValue="value"
                        class="w-full"
                    />
                </div>
            </div>

            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    @click="showGenerateDialog = false"
                    class="p-button-text"
                />
                <Button
                    label="Générer"
                    icon="pi pi-check"
                    @click="handleGenerate"
                    autofocus
                />
            </template>
        </Dialog>
    </div>
</template>

<script>
import { ref, computed } from 'vue';
import { useStore } from 'vuex';

export default {
    emits: ['filter-change', 'generate'],
    setup(props, { emit }) {
        const store = useStore();

        const localFilters = ref({
            specialiteId: null,
            groupeId: null,
            phaseId: null,
            dateRange: null,
        });

        const showGenerateDialog = ref(false);
        const generateOptions = ref({
            respecterContraintes: true,
            optimiserRessources: true,
            equilibrerCharge: true,
            repartition: 'quinzaine',
        });

        const repartitionOptions = ref([
            { label: 'Par quinzaine', value: 'quinzaine' },
            { label: 'Hebdomadaire', value: 'hebdo' },
            { label: 'Répartir uniformément', value: 'uniforme' },
        ]);

        const specialites = computed(() => store.state.schedule.specialites);
        const filteredGroupes = computed(() => {
            return store.getters['schedule/filteredGroupes'](
                localFilters.value.specialiteId
            );
        });

        const filteredPhases = computed(() => {
            return store.getters['schedule/filteredPhases'](
                localFilters.value.groupeId
            );
        });

        const handleSpecialiteChange = () => {
            localFilters.value.groupeId = null;
            localFilters.value.phaseId = null;
            emitFilters();
        };

        const emitFilters = () => {
            emit('filter-change', { ...localFilters.value });
            store.dispatch('schedule/updateFilters', localFilters.value);
        };

        const handleGenerate = () => {
            emit('generate', { ...generateOptions.value });
            showGenerateDialog.value = false;
        };

        const handleExport = () => {
            console.log('Export des données');
        };

        return {
            localFilters,
            showGenerateDialog,
            generateOptions,
            repartitionOptions,
            specialites,
            filteredGroupes,
            filteredPhases,
            handleSpecialiteChange,
            emitFilters,
            handleGenerate,
            handleExport,
        };
    },
};
</script>

<style scoped>
.schedule-controls {
    margin-bottom: 1rem;
}
</style>
