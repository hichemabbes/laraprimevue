<template>
    <Card class="resources-card">
        <template #title>Ressources</template>
        <template #content>
            <TabView>
                <TabPanel header="Formateurs">
                    <DataTable
                        :value="formateurs"
                        :paginator="true"
                        :rows="5"
                        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                        currentPageReportTemplate="Showing {first} to {last} of {totalRecords}"
                        :rowsPerPageOptions="[5, 10, 25]"
                    >
                        <Column
                            field="nomComplet"
                            header="Nom"
                            :sortable="true"
                        >
                            <template #body="{ data }">
                                <Avatar
                                    :label="data.prenom[0] + data.nom[0]"
                                    size="small"
                                    shape="circle"
                                    class="mr-2"
                                />
                                <span>{{ data.prenom }} {{ data.nom }}</span>
                            </template>
                        </Column>
                        <Column header="Charge actuelle" :sortable="true">
                            <template #body="{ data }">
                                <Chip
                                    :label="`${calculateCharge(data.id)}h`"
                                    :severity="getChargeSeverity(data.id)"
                                />
                            </template>
                        </Column>
                        <Column header="Disponibilité">
                            <template #body="{ data }">
                                <Tag
                                    severity="success"
                                    value="Disponible"
                                    v-if="isFormateurDisponible(data.id)"
                                />
                                <Tag severity="danger" value="Occupé" v-else />
                            </template>
                        </Column>
                    </DataTable>
                </TabPanel>

                <TabPanel header="Espaces">
                    <DataTable
                        :value="espaces"
                        :paginator="true"
                        :rows="5"
                        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                        currentPageReportTemplate="Showing {first} to {last} of {totalRecords}"
                        :rowsPerPageOptions="[5, 10, 25]"
                    >
                        <Column field="nom" header="Nom" :sortable="true" />
                        <Column field="type" header="Type" :sortable="true" />
                        <Column
                            field="capacite"
                            header="Capacité"
                            :sortable="true"
                        >
                            <template #body="{ data }">
                                {{ data.capacite }} places
                            </template>
                        </Column>
                        <Column header="Disponibilité">
                            <template #body="{ data }">
                                <Tag
                                    severity="success"
                                    value="Disponible"
                                    v-if="isEspaceDisponible(data.id)"
                                />
                                <Tag severity="danger" value="Occupé" v-else />
                            </template>
                        </Column>
                    </DataTable>
                </TabPanel>

                <TabPanel header="Modules">
                    <DataTable
                        :value="modules"
                        :paginator="true"
                        :rows="5"
                        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                        currentPageReportTemplate="Showing {first} to {last} of {totalRecords}"
                        :rowsPerPageOptions="[5, 10, 25]"
                    >
                        <Column field="nom" header="Nom" :sortable="true" />
                        <Column
                            field="duree_totale"
                            header="Durée totale"
                            :sortable="true"
                        >
                            <template #body="{ data }">
                                {{ data.duree_totale }} heures
                            </template>
                        </Column>
                        <Column
                            field="duree_seance"
                            header="Durée/séance"
                            :sortable="true"
                        >
                            <template #body="{ data }">
                                {{ data.duree_seance }} heures
                            </template>
                        </Column>
                        <Column header="Progression">
                            <template #body="{ data }">
                                <ProgressBar
                                    :value="calculateModuleProgress(data.id)"
                                    :showValue="false"
                                    :class="getProgressBarClass(data.id)"
                                />
                                <small
                                    >{{ calculateModuleDoneHours(data.id) }}h/{{
                                        data.duree_totale
                                    }}h</small
                                >
                            </template>
                        </Column>
                    </DataTable>
                </TabPanel>
            </TabView>
        </template>
    </Card>
</template>

<script>
import { computed } from 'vue';
import { useStore } from 'vuex';
import { useConstraints } from '@/composables/useConstraints.js';
import dayjs from 'dayjs';

export default {
    setup() {
        const store = useStore();
        const {
            calculateWeeklyHours,
            checkEspaceDisponibility,
            checkFormateurDisponibility,
            getEventDuration,
            isTimeOverlap,
        } = useConstraints();

        const formateurs = computed(() => {
            return store.state.schedule.formateurs.map((f) => ({
                ...f,
                nomComplet: `${f.prenom} ${f.nom}`,
            }));
        });

        const espaces = computed(() => store.state.schedule.espaces);
        const modules = computed(() => store.state.schedule.modules);

        const calculateCharge = (formateurId) => {
            return calculateWeeklyHours(formateurId, new Date());
        };

        const getChargeSeverity = (formateurId) => {
            const charge = calculateCharge(formateurId);
            const formateur = store.state.schedule.formateurs.find(
                (f) => f.id === formateurId
            );
            const regime = formateur
                ? store.state.schedule.regimesHoraires.find(
                      (r) => r.id === formateur.regime_horaire_id
                  )
                : null;
            const maxHours = regime?.heures_semaine || 35;

            if (charge >= maxHours) return 'danger';
            if (charge >= maxHours * 0.8) return 'warning';
            return 'success';
        };

        const isFormateurDisponible = (formateurId) => {
            // Vérifier la disponibilité actuelle (par exemple maintenant + 1h)
            const now = new Date();
            const inOneHour = new Date(now.getTime() + 60 * 60 * 1000);
            return checkFormateurDisponibility(formateurId, now, inOneHour);
        };

        const isEspaceDisponible = (espaceId) => {
            // Vérifier la disponibilité actuelle
            const now = new Date();
            const inOneHour = new Date(now.getTime() + 60 * 60 * 1000);
            return checkEspaceDisponibility(espaceId, now, inOneHour);
        };

        const calculateModuleDoneHours = (moduleId) => {
            return store.state.schedule.events
                .filter((event) => event.moduleId === moduleId)
                .reduce((total, event) => {
                    return total + getEventDuration(event);
                }, 0);
        };

        const calculateModuleProgress = (moduleId) => {
            const module = store.state.schedule.modules.find(
                (m) => m.id === moduleId
            );
            if (!module) return 0;

            const done = calculateModuleDoneHours(moduleId);
            return (done / module.duree_totale) * 100;
        };

        const getProgressBarClass = (moduleId) => {
            const progress = calculateModuleProgress(moduleId);
            if (progress >= 100) return 'progress-complete';
            if (progress >= 75) return 'progress-high';
            if (progress >= 50) return 'progress-medium';
            if (progress >= 25) return 'progress-low';
            return 'progress-very-low';
        };

        return {
            formateurs,
            espaces,
            modules,
            calculateCharge,
            getChargeSeverity,
            isFormateurDisponible,
            isEspaceDisponible,
            calculateModuleDoneHours,
            calculateModuleProgress,
            getProgressBarClass,
        };
    },
};
</script>

<style scoped>
.resources-card {
    height: 100%;
}

:deep(.progress-complete) .p-progressbar-value {
    background-color: var(--green-500);
}

:deep(.progress-high) .p-progressbar-value {
    background-color: var(--green-400);
}

:deep(.progress-medium) .p-progressbar-value {
    background-color: var(--yellow-500);
}

:deep(.progress-low) .p-progressbar-value {
    background-color: var(--orange-500);
}

:deep(.progress-very-low) .p-progressbar-value {
    background-color: var(--red-500);
}
</style>
