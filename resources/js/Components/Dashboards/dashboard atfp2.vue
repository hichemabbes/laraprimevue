<template>
    <div class="dashboard-container">
        <!-- En-tête avec titre et outils -->
        <div class="flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="text-3xl font-bold text-900">
                    Tableau de Bord des Centres de Formation
                </h1>
                <span class="text-600"
                    >Vue d'ensemble des activités et performances</span
                >
            </div>
            <div class="flex gap-3">
                <Calendar
                    v-model="dateRange"
                    selectionMode="range"
                    :manualInput="false"
                    dateFormat="dd/mm/yy"
                    placeholder="Période"
                />
                <Button
                    icon="pi pi-refresh"
                    class="p-button-rounded p-button-outlined"
                    @click="refreshData"
                />
                <Button
                    icon="pi pi-cog"
                    class="p-button-rounded p-button-outlined"
                />
            </div>
        </div>

        <!-- Cartes de statistiques principales -->
        <div class="grid mb-5">
            <div class="col-12 md:col-6 lg:col-3">
                <Card
                    class="h-full border-round-lg shadow-2 hover:shadow-4 transition-all transition-duration-300"
                >
                    <template #title>
                        <div class="flex align-items-center gap-2">
                            <i class="pi pi-building text-primary"></i>
                            <span>Centres</span>
                        </div>
                    </template>
                    <template #content>
                        <div class="flex flex-column gap-3">
                            <div
                                class="flex justify-content-between align-items-center"
                            >
                                <span class="text-900 font-bold text-4xl">{{
                                    stats.centres.total
                                }}</span>
                                <Tag
                                    :value="`+${stats.centres.actifs} actifs`"
                                    severity="success"
                                    class="font-semibold"
                                />
                            </div>
                            <div class="flex justify-content-between">
                                <span class="text-600"
                                    >Publics: {{ stats.centres.publics }}</span
                                >
                                <span class="text-600"
                                    >Privés: {{ stats.centres.prives }}</span
                                >
                            </div>
                            <ProgressBar
                                :value="
                                    (stats.centres.actifs /
                                        stats.centres.total) *
                                    100
                                "
                                :showValue="false"
                                class="h-1rem"
                            />
                        </div>
                    </template>
                </Card>
            </div>

            <div class="col-12 md:col-6 lg:col-3">
                <Card
                    class="h-full border-round-lg shadow-2 hover:shadow-4 transition-all transition-duration-300"
                >
                    <template #title>
                        <div class="flex align-items-center gap-2">
                            <i class="pi pi-users text-blue-500"></i>
                            <span>Personnels</span>
                        </div>
                    </template>
                    <template #content>
                        <div class="flex flex-column gap-3">
                            <div
                                class="flex justify-content-between align-items-center"
                            >
                                <span class="text-900 font-bold text-4xl">{{
                                    stats.personnels.total
                                }}</span>
                                <Tag
                                    :value="`+${stats.personnels.formateurs} form.`"
                                    severity="info"
                                    class="font-semibold"
                                />
                            </div>
                            <div class="flex justify-content-between">
                                <span class="text-600"
                                    >Centrale:
                                    {{ stats.personnels.centrale }}</span
                                >
                                <span class="text-600"
                                    >Centres:
                                    {{ stats.personnels.centres }}</span
                                >
                            </div>
                            <ProgressBar
                                :value="
                                    (stats.personnels.formateurs /
                                        stats.personnels.total) *
                                    100
                                "
                                :showValue="false"
                                class="h-1rem"
                            />
                        </div>
                    </template>
                </Card>
            </div>

            <div class="col-12 md:col-6 lg:col-3">
                <Card
                    class="h-full border-round-lg shadow-2 hover:shadow-4 transition-all transition-duration-300"
                >
                    <template #title>
                        <div class="flex align-items-center gap-2">
                            <i class="pi pi-id-card text-orange-500"></i>
                            <span>Stagiaires</span>
                        </div>
                    </template>
                    <template #content>
                        <div class="flex flex-column gap-3">
                            <div
                                class="flex justify-content-between align-items-center"
                            >
                                <span class="text-900 font-bold text-4xl">{{
                                    stats.stagiaires.total
                                }}</span>
                                <Tag
                                    :value="`+${stats.stagiaires.residentiel} rés.`"
                                    severity="warning"
                                    class="font-semibold"
                                />
                            </div>
                            <div class="flex justify-content-between">
                                <span class="text-600"
                                    >Alternance:
                                    {{ stats.stagiaires.alternance }}</span
                                >
                                <span class="text-600"
                                    >F0: {{ stats.stagiaires.f0 }}</span
                                >
                            </div>
                            <ProgressBar
                                :value="
                                    (stats.stagiaires.residentiel /
                                        stats.stagiaires.total) *
                                    100
                                "
                                :showValue="false"
                                class="h-1rem"
                            />
                        </div>
                    </template>
                </Card>
            </div>

            <div class="col-12 md:col-6 lg:col-3">
                <Card
                    class="h-full border-round-lg shadow-2 hover:shadow-4 transition-all transition-duration-300"
                >
                    <template #title>
                        <div class="flex align-items-center gap-2">
                            <i class="pi pi-chart-line text-green-500"></i>
                            <span>Capacité</span>
                        </div>
                    </template>
                    <template #content>
                        <div class="flex flex-column gap-3">
                            <div
                                class="flex justify-content-between align-items-center"
                            >
                                <span class="text-900 font-bold text-4xl"
                                    >{{ stats.capacite.taux }}%</span
                                >
                                <Knob
                                    v-model="stats.capacite.taux"
                                    :size="60"
                                    :strokeWidth="8"
                                    readonly
                                    valueColor="var(--primary-color)"
                                    rangeColor="var(--surface-200)"
                                />
                            </div>
                            <div class="flex justify-content-between">
                                <span class="text-600"
                                    >Places: {{ stats.capacite.occupees }}/{{
                                        stats.capacite.totales
                                    }}</span
                                >
                                <span class="text-600"
                                    >Centres:
                                    {{ stats.capacite.centres_utilises }}/{{
                                        stats.capacite.centres_totaux
                                    }}</span
                                >
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>

        <!-- Section principale avec graphiques -->
        <div class="grid">
            <!-- Colonne de gauche - Graphiques principaux -->
            <div class="col-12 lg:col-8">
                <Card class="h-full border-round-lg shadow-2">
                    <template #title>
                        <div
                            class="flex justify-content-between align-items-center"
                        >
                            <span>Activité des Centres</span>
                            <SelectButton
                                v-model="timeRange"
                                :options="timeOptions"
                                optionLabel="label"
                                optionValue="value"
                            />
                        </div>
                    </template>
                    <template #content>
                        <div class="grid">
                            <div class="col-12">
                                <TabView>
                                    <TabPanel header="Activité">
                                        <Chart
                                            type="line"
                                            :data="chartData.activite"
                                            :options="lineChartOptions"
                                            class="h-20rem"
                                        />
                                    </TabPanel>
                                    <TabPanel header="Répartition">
                                        <div class="grid">
                                            <div class="col-12 md:col-6">
                                                <h5 class="text-center mb-3">
                                                    Par Spécialité
                                                </h5>
                                                <Chart
                                                    type="pie"
                                                    :data="
                                                        chartData.specialites
                                                    "
                                                    :options="pieChartOptions"
                                                    class="h-16rem"
                                                />
                                            </div>
                                            <div class="col-12 md:col-6">
                                                <h5 class="text-center mb-3">
                                                    Par Région
                                                </h5>
                                                <Chart
                                                    type="doughnut"
                                                    :data="chartData.regions"
                                                    :options="pieChartOptions"
                                                    class="h-16rem"
                                                />
                                            </div>
                                        </div>
                                    </TabPanel>
                                </TabView>
                            </div>

                            <div class="col-12 md:col-6">
                                <h5 class="mb-3">Prochaines Formations</h5>
                                <DataTable
                                    :value="upcomingFormations"
                                    :rows="5"
                                    :paginator="true"
                                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink"
                                    currentPageReportTemplate="{first} à {last} sur {totalRecords}"
                                >
                                    <Column
                                        field="centre"
                                        header="Centre"
                                        sortable
                                    ></Column>
                                    <Column
                                        field="specialite"
                                        header="Spécialité"
                                        sortable
                                    ></Column>
                                    <Column
                                        field="date_debut"
                                        header="Début"
                                        sortable
                                    >
                                        <template #body="{ data }">
                                            <Tag
                                                :value="
                                                    formatShortDate(
                                                        data.date_debut
                                                    )
                                                "
                                                icon="pi pi-calendar"
                                            />
                                        </template>
                                    </Column>
                                    <Column
                                        field="places"
                                        header="Places"
                                        sortable
                                    >
                                        <template #body="{ data }">
                                            <div class="flex flex-column gap-2">
                                                <ProgressBar
                                                    :value="
                                                        (data.inscrits /
                                                            data.places) *
                                                        100
                                                    "
                                                    :showValue="false"
                                                    class="h-1rem"
                                                />
                                                <span class="text-sm"
                                                    >{{ data.inscrits }}/{{
                                                        data.places
                                                    }}
                                                    ({{
                                                        Math.round(
                                                            (data.inscrits /
                                                                data.places) *
                                                                100
                                                        )
                                                    }}%)</span
                                                >
                                            </div>
                                        </template>
                                    </Column>
                                </DataTable>
                            </div>

                            <div class="col-12 md:col-6">
                                <h5 class="mb-3">Événements à Venir</h5>
                                <Timeline
                                    :value="events"
                                    align="alternate"
                                    class="mt-3"
                                >
                                    <template #marker="{ item }">
                                        <span
                                            class="flex w-2rem h-2rem align-items-center justify-content-center text-white border-circle z-1 shadow-2"
                                            :class="eventSeverity(item).class"
                                        >
                                            <i
                                                :class="
                                                    eventSeverity(item).icon
                                                "
                                            ></i>
                                        </span>
                                    </template>
                                    <template #content="{ item }">
                                        <Card
                                            class="mb-3 cursor-pointer"
                                            @click="openEventDialog(item)"
                                        >
                                            <template #title>{{
                                                item.title
                                            }}</template>
                                            <template #subtitle>
                                                <div
                                                    class="flex align-items-center gap-2"
                                                >
                                                    <i
                                                        class="pi pi-map-marker"
                                                    ></i>
                                                    <span>{{
                                                        item.centre
                                                    }}</span>
                                                </div>
                                            </template>
                                            <template #content>
                                                <p class="line-clamp-2">
                                                    {{ item.description }}
                                                </p>
                                                <div
                                                    class="flex justify-content-between mt-2"
                                                >
                                                    <Tag
                                                        :value="
                                                            formatShortDate(
                                                                item.date
                                                            )
                                                        "
                                                        icon="pi pi-clock"
                                                        severity="info"
                                                    />
                                                    <Button
                                                        icon="pi pi-chevron-right"
                                                        class="p-button-text p-button-sm"
                                                    />
                                                </div>
                                            </template>
                                        </Card>
                                    </template>
                                </Timeline>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Colonne de droite - Résumé et alertes -->
            <div class="col-12 lg:col-4">
                <div class="flex flex-column gap-4">
                    <Card class="border-round-lg shadow-2">
                        <template #title>
                            <div class="flex align-items-center gap-2">
                                <i class="pi pi-bell text-red-500"></i>
                                <span>Alertes Récentes</span>
                            </div>
                        </template>
                        <template #content>
                            <DataTable
                                :value="alertes"
                                :rows="5"
                                scrollHeight="flex"
                                scrollable
                            >
                                <Column
                                    field="date"
                                    header="Date"
                                    style="width: 25%"
                                >
                                    <template #body="{ data }">
                                        <Tag
                                            :value="formatShortDate(data.date)"
                                            :severity="getSeverity(data.type)"
                                        />
                                    </template>
                                </Column>
                                <Column
                                    field="type"
                                    header="Type"
                                    style="width: 25%"
                                >
                                    <template #body="{ data }">
                                        <Badge
                                            :value="data.type"
                                            :severity="getSeverity(data.type)"
                                        />
                                    </template>
                                </Column>
                                <Column
                                    field="message"
                                    header="Message"
                                    style="width: 50%"
                                >
                                    <template #body="{ data }">
                                        <span class="line-clamp-1">{{
                                            data.message
                                        }}</span>
                                    </template>
                                </Column>
                                <template #empty>
                                    <div class="text-center py-4">
                                        <i
                                            class="pi pi-check-circle text-green-500 text-4xl mb-2"
                                        ></i>
                                        <p class="text-600">
                                            Aucune alerte récente
                                        </p>
                                    </div>
                                </template>
                            </DataTable>
                        </template>
                    </Card>

                    <Card class="border-round-lg shadow-2">
                        <template #title>
                            <div class="flex align-items-center gap-2">
                                <i class="pi pi-star text-yellow-500"></i>
                                <span>Performance</span>
                            </div>
                        </template>
                        <template #content>
                            <div class="grid">
                                <div class="col-12 md:col-6">
                                    <div
                                        class="text-center p-3 border-round surface-card"
                                    >
                                        <div
                                            class="text-900 font-bold text-3xl"
                                        >
                                            {{ stats.reussite.global }}%
                                        </div>
                                        <div class="text-600">
                                            Taux de réussite
                                        </div>
                                        <Divider class="my-2" />
                                        <div
                                            class="flex justify-content-center gap-2"
                                        >
                                            <i
                                                class="pi pi-arrow-up text-green-500"
                                                v-if="
                                                    stats.reussite.tendance >= 0
                                                "
                                            ></i>
                                            <i
                                                class="pi pi-arrow-down text-red-500"
                                                v-else
                                            ></i>
                                            <span
                                                :class="
                                                    stats.reussite.tendance >= 0
                                                        ? 'text-green-500'
                                                        : 'text-red-500'
                                                "
                                            >
                                                {{
                                                    Math.abs(
                                                        stats.reussite.tendance
                                                    )
                                                }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 md:col-6">
                                    <div
                                        class="text-center p-3 border-round surface-card"
                                    >
                                        <div
                                            class="text-900 font-bold text-3xl"
                                        >
                                            {{ stats.satisfaction }}%
                                        </div>
                                        <div class="text-600">Satisfaction</div>
                                        <Divider class="my-2" />
                                        <Rating
                                            v-model="stats.satisfaction"
                                            :cancel="false"
                                            readonly
                                            :stars="5"
                                        />
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card class="border-round-lg shadow-2">
                        <template #title>
                            <div class="flex align-items-center gap-2">
                                <i class="pi pi-map text-blue-500"></i>
                                <span>Carte des Centres</span>
                            </div>
                        </template>
                        <template #content>
                            <div
                                class="border-round overflow-hidden"
                                style="
                                    height: 200px;
                                    background: var(--surface-ground);
                                "
                            >
                                <div
                                    class="flex justify-content-center align-items-center h-full"
                                >
                                    <i class="pi pi-map text-5xl text-400"></i>
                                    <span class="ml-2 text-600"
                                        >Carte interactive des centres</span
                                    >
                                </div>
                            </div>
                            <div class="flex justify-content-between mt-3">
                                <Chip
                                    label="Actifs"
                                    icon="pi pi-check-circle"
                                    class="bg-green-100 text-green-800"
                                />
                                <Chip
                                    label="En travaux"
                                    icon="pi pi-wrench"
                                    class="bg-yellow-100 text-yellow-800"
                                />
                                <Chip
                                    label="En construction"
                                    icon="pi pi-hourglass"
                                    class="bg-blue-100 text-blue-800"
                                />
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Section inférieure - Détails des centres -->
        <div class="grid mt-4">
            <div class="col-12">
                <Card class="border-round-lg shadow-2">
                    <template #title>
                        <div
                            class="flex justify-content-between align-items-center"
                        >
                            <span>Liste des Centres</span>
                            <div class="flex gap-2">
                                <Dropdown
                                    v-model="selectedRegion"
                                    :options="regions"
                                    optionLabel="name"
                                    placeholder="Région"
                                    class="w-10rem"
                                />
                                <Dropdown
                                    v-model="selectedGouvernorat"
                                    :options="filteredGouvernorats"
                                    optionLabel="name"
                                    placeholder="Gouvernorat"
                                    class="w-12rem"
                                />
                                <Button
                                    label="Exporter"
                                    icon="pi pi-download"
                                    class="p-button-outlined"
                                />
                            </div>
                        </div>
                    </template>
                    <template #content>
                        <DataTable
                            :value="filteredCentres"
                            :paginator="true"
                            :rows="10"
                            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                            :rowsPerPageOptions="[5, 10, 25, 50]"
                            currentPageReportTemplate="Affichage de {first} à {last} sur {totalRecords}"
                            responsiveLayout="scroll"
                            stripedRows
                        >
                            <Column
                                field="code"
                                header="Code"
                                sortable
                                style="width: 100px"
                            ></Column>
                            <Column field="nom" header="Nom" sortable>
                                <template #body="{ data }">
                                    <span class="font-medium">{{
                                        data.nom
                                    }}</span>
                                </template>
                            </Column>
                            <Column
                                field="region"
                                header="Région"
                                sortable
                                style="width: 120px"
                            ></Column>
                            <Column
                                field="gouvernorat"
                                header="Gouvernorat"
                                sortable
                                style="width: 140px"
                            ></Column>
                            <Column
                                field="type"
                                header="Type"
                                sortable
                                style="width: 120px"
                            >
                                <template #body="{ data }">
                                    <Tag
                                        :value="data.type"
                                        :severity="
                                            data.type === 'Public'
                                                ? 'success'
                                                : 'info'
                                        "
                                    />
                                </template>
                            </Column>
                            <Column
                                field="statut"
                                header="Statut"
                                sortable
                                style="width: 140px"
                            >
                                <template #body="{ data }">
                                    <Tag
                                        :value="data.statut"
                                        :severity="
                                            getStatutSeverity(data.statut)
                                        "
                                    />
                                </template>
                            </Column>
                            <Column
                                field="capacite"
                                header="Capacité"
                                sortable
                                style="width: 180px"
                            >
                                <template #body="{ data }">
                                    <div class="flex flex-column gap-1">
                                        <div
                                            class="flex justify-content-between"
                                        >
                                            <span class="text-sm"
                                                >{{ data.occupation }}/{{
                                                    data.capacite
                                                }}</span
                                            >
                                            <span class="text-sm font-medium"
                                                >{{
                                                    Math.round(
                                                        (data.occupation /
                                                            data.capacite) *
                                                            100
                                                    )
                                                }}%</span
                                            >
                                        </div>
                                        <ProgressBar
                                            :value="
                                                (data.occupation /
                                                    data.capacite) *
                                                100
                                            "
                                            :showValue="false"
                                            :class="
                                                getCapacityClass(
                                                    data.occupation /
                                                        data.capacite
                                                )
                                            "
                                        />
                                    </div>
                                </template>
                            </Column>
                            <Column header="Actions" style="width: 120px">
                                <template #body="{ data }">
                                    <Button
                                        icon="pi pi-eye"
                                        class="p-button-rounded p-button-text p-button-plain"
                                        @click="openCentreDetails(data)"
                                        v-tooltip.top="'Voir détails'"
                                    />
                                    <Button
                                        icon="pi pi-chart-line"
                                        class="p-button-rounded p-button-text p-button-plain ml-2"
                                        @click="openCentreStats(data)"
                                        v-tooltip.top="'Statistiques'"
                                    />
                                </template>
                            </Column>
                        </DataTable>
                    </template>
                </Card>
            </div>
        </div>

        <!-- Dialog Détails Centre -->
        <Dialog
            v-model:visible="centreDialog"
            :style="{ width: '70vw' }"
            header="Détails du Centre"
            :modal="true"
            :dismissableMask="true"
        >
            <CentreDetails :centre="selectedCentre" v-if="selectedCentre" />
            <template #footer>
                <Button
                    label="Fermer"
                    icon="pi pi-times"
                    @click="centreDialog = false"
                    class="p-button-text"
                />
            </template>
        </Dialog>

        <!-- Dialog Événement -->
        <Dialog
            v-model:visible="eventDialog"
            :style="{ width: '40vw' }"
            :header="selectedEvent?.title"
            :modal="true"
        >
            <div class="grid" v-if="selectedEvent">
                <div class="col-12">
                    <div class="flex align-items-center gap-3 mb-3">
                        <i class="pi pi-calendar text-primary"></i>
                        <span>{{ formatDate(selectedEvent.date) }}</span>
                    </div>
                    <div class="flex align-items-center gap-3 mb-3">
                        <i class="pi pi-map-marker text-primary"></i>
                        <span>{{ selectedEvent.centre }}</span>
                    </div>
                    <Divider />
                    <p class="line-height-3">{{ selectedEvent.description }}</p>
                </div>
            </div>
            <template #footer>
                <Button
                    label="Fermer"
                    icon="pi pi-times"
                    @click="eventDialog = false"
                    class="p-button-text"
                />
            </template>
        </Dialog>
    </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { useToast } from 'primevue/usetoast';

// Composants PrimeVue
import Card from 'primevue/card';
import Chart from 'primevue/chart';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import ProgressBar from 'primevue/progressbar';
import Knob from 'primevue/knob';
import Tag from 'primevue/tag';
import Timeline from 'primevue/timeline';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import SelectButton from 'primevue/selectbutton';
import Rating from 'primevue/rating';
import Badge from 'primevue/badge';
import Chip from 'primevue/chip';
import Divider from 'primevue/divider';
import Calendar from 'primevue/calendar';

// Composants personnalisés
import CentreDetails from '@/components/CentreDetails.vue';

export default {
    components: {
        Card,
        Chart,
        DataTable,
        Column,
        ProgressBar,
        Knob,
        Tag,
        Timeline,
        TabView,
        TabPanel,
        Button,
        Dropdown,
        Dialog,
        SelectButton,
        Rating,
        Badge,
        Chip,
        Divider,
        Calendar,
        CentreDetails,
    },
    setup() {
        const toast = useToast();
        const dateRange = ref();
        const timeRange = ref('month');
        const timeOptions = ref([
            { label: '7 jours', value: 'week' },
            { label: 'Mois', value: 'month' },
            { label: 'Année', value: 'year' },
        ]);

        // Données statistiques
        const stats = ref({
            centres: {
                total: 142,
                actifs: 135,
                construction: 7,
                publics: 122,
                prives: 20,
            },
            personnels: {
                total: 4520,
                centrale: 320,
                centres: 4200,
                formateurs: 3800,
                directeurs: 140,
            },
            stagiaires: {
                total: 68500,
                residentiel: 42000,
                alternance: 15000,
                apprentissage: 10000,
                f0: 1500,
            },
            capacite: {
                taux: 78,
                occupees: 68500,
                totales: 88000,
                centres_utilises: 135,
                centres_totaux: 142,
            },
            reussite: {
                global: 82,
                tendance: 2.5,
            },
            satisfaction: 87,
        });

        // Données graphiques
        const chartData = ref({
            activite: {
                labels: [
                    'Jan',
                    'Fév',
                    'Mar',
                    'Avr',
                    'Mai',
                    'Jun',
                    'Jul',
                    'Aoû',
                    'Sep',
                    'Oct',
                    'Nov',
                    'Déc',
                ],
                datasets: [
                    {
                        label: 'Heures Formation',
                        data: [
                            12500, 13000, 12800, 13500, 14000, 14500, 14200,
                            15000, 14800, 15500, 15200, 16000,
                        ],
                        fill: true,
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderColor: '#3B82F6',
                        tension: 0.4,
                        borderWidth: 2,
                    },
                    {
                        label: 'Heures Suivi',
                        data: [
                            4500, 4800, 5000, 5200, 5500, 5800, 6000, 6200,
                            6500, 6800, 7000, 7200,
                        ],
                        fill: true,
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderColor: '#10B981',
                        tension: 0.4,
                        borderWidth: 2,
                    },
                ],
            },
            specialites: {
                labels: [
                    'Mécanique',
                    'Électricité',
                    'Informatique',
                    'BTP',
                    'Commerce',
                    'Services',
                    'Autres',
                ],
                datasets: [
                    {
                        data: [25, 20, 18, 15, 12, 8, 2],
                        backgroundColor: [
                            '#3B82F6',
                            '#10B981',
                            '#F59E0B',
                            '#EF4444',
                            '#8B5CF6',
                            '#64748B',
                            '#EC4899',
                        ],
                        hoverBackgroundColor: [
                            '#2563EB',
                            '#059669',
                            '#D97706',
                            '#DC2626',
                            '#7C3AED',
                            '#475569',
                            '#DB2777',
                        ],
                    },
                ],
            },
            regions: {
                labels: ['Nord', 'Centre', 'Sud'],
                datasets: [
                    {
                        data: [65, 45, 32],
                        backgroundColor: ['#3B82F6', '#10B981', '#F59E0B'],
                        hoverBackgroundColor: ['#2563EB', '#059669', '#D97706'],
                    },
                ],
            },
        });

        // Options des graphiques
        const lineChartOptions = ref({
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                    },
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                    },
                    ticks: {
                        callback: function (value) {
                            return value.toLocaleString();
                        },
                    },
                },
                x: {
                    grid: {
                        display: false,
                    },
                },
            },
            maintainAspectRatio: false,
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false,
            },
        });

        const pieChartOptions = ref({
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                    },
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce(
                                (acc, data) => acc + data,
                                0
                            );
                            const percentage = Math.round(
                                (value / total) * 100
                            );
                            return `${label}: ${value} (${percentage}%)`;
                        },
                    },
                },
            },
            cutout: '60%',
            maintainAspectRatio: false,
        });

        // Données des régions et gouvernorats
        const regions = ref([
            { name: 'Nord', code: 'N' },
            { name: 'Centre', code: 'C' },
            { name: 'Sud', code: 'S' },
        ]);

        const gouvernorats = ref([
            { name: 'Tunis', region: 'Nord' },
            { name: 'Ariana', region: 'Nord' },
            { name: 'Ben Arous', region: 'Nord' },
            { name: 'Manouba', region: 'Nord' },
            { name: 'Nabeul', region: 'Nord' },
            { name: 'Zaghouan', region: 'Nord' },
            { name: 'Bizerte', region: 'Nord' },
            { name: 'Béja', region: 'Nord' },
            { name: 'Jendouba', region: 'Nord' },
            { name: 'Kef', region: 'Nord' },
            { name: 'Siliana', region: 'Nord' },
            { name: 'Sousse', region: 'Centre' },
            { name: 'Monastir', region: 'Centre' },
            { name: 'Mahdia', region: 'Centre' },
            { name: 'Kairouan', region: 'Centre' },
            { name: 'Kasserine', region: 'Centre' },
            { name: 'Sidi Bouzid', region: 'Centre' },
            { name: 'Sfax', region: 'Sud' },
            { name: 'Gafsa', region: 'Sud' },
            { name: 'Tozeur', region: 'Sud' },
            { name: 'Kebili', region: 'Sud' },
            { name: 'Gabès', region: 'Sud' },
            { name: 'Medenine', region: 'Sud' },
            { name: 'Tataouine', region: 'Sud' },
        ]);

        // Données des centres
        const centres = ref([
            {
                code: 'CFP001',
                nom: 'CFP Tunis 1',
                region: 'Nord',
                gouvernorat: 'Tunis',
                type: 'Public',
                statut: 'Actif',
                capacite: 500,
                occupation: 420,
                specialites: ['Mécanique', 'Électricité', 'Informatique'],
                groupes: 12,
                taux_reussite: 85,
            },
            {
                code: 'CFP002',
                nom: 'CFP Sfax 2',
                region: 'Sud',
                gouvernorat: 'Sfax',
                type: 'Public',
                statut: 'Actif',
                capacite: 600,
                occupation: 550,
                specialites: ['BTP', 'Plomberie', 'Électricité'],
                groupes: 15,
                taux_reussite: 88,
            },
            {
                code: 'CFP003',
                nom: 'CFP Sousse',
                region: 'Centre',
                gouvernorat: 'Sousse',
                type: 'Public',
                statut: 'Actif',
                capacite: 450,
                occupation: 400,
                specialites: ['Informatique', 'Commerce', 'Services'],
                groupes: 10,
                taux_reussite: 82,
            },
            {
                code: 'CFP004',
                nom: 'CFP Gabès',
                region: 'Sud',
                gouvernorat: 'Gabès',
                type: 'Public',
                statut: 'Actif',
                capacite: 350,
                occupation: 300,
                specialites: ['Chimie', 'Mécanique', 'BTP'],
                groupes: 8,
                taux_reussite: 80,
            },
            {
                code: 'CFP005',
                nom: 'CFP Nabeul',
                region: 'Nord',
                gouvernorat: 'Nabeul',
                type: 'Public',
                statut: 'Actif',
                capacite: 400,
                occupation: 380,
                specialites: ['Tourisme', 'Hôtellerie', 'Commerce'],
                groupes: 9,
                taux_reussite: 84,
            },
            {
                code: 'CFP006',
                nom: 'CFP Bizerte',
                region: 'Nord',
                gouvernorat: 'Bizerte',
                type: 'Public',
                statut: 'Rénovation',
                capacite: 300,
                occupation: 200,
                specialites: ['Mécanique', 'Électricité'],
                groupes: 6,
                taux_reussite: 79,
            },
            {
                code: 'CFP007',
                nom: 'CFP Kairouan',
                region: 'Centre',
                gouvernorat: 'Kairouan',
                type: 'Public',
                statut: 'Actif',
                capacite: 320,
                occupation: 280,
                specialites: ['Agriculture', 'Élevage', 'Artisanat'],
                groupes: 7,
                taux_reussite: 81,
            },
            {
                code: 'CFP008',
                nom: 'CFP Privé Tunis',
                region: 'Nord',
                gouvernorat: 'Tunis',
                type: 'Privé',
                statut: 'Actif',
                capacite: 200,
                occupation: 180,
                specialites: ['Informatique', 'Design', 'Langues'],
                groupes: 5,
                taux_reussite: 87,
            },
        ]);

        // Données des formations à venir
        const upcomingFormations = ref([
            {
                centre: 'CFP Tunis 1',
                specialite: 'Mécanique Auto',
                date_debut: '2023-09-15',
                places: 25,
                inscrits: 22,
            },
            {
                centre: 'CFP Sfax 2',
                specialite: 'Électricité Bâtiment',
                date_debut: '2023-10-01',
                places: 20,
                inscrits: 18,
            },
            {
                centre: 'CFP Sousse',
                specialite: 'Développement Web',
                date_debut: '2023-10-15',
                places: 15,
                inscrits: 12,
            },
            {
                centre: 'CFP Gabès',
                specialite: 'Plomberie',
                date_debut: '2023-11-01',
                places: 20,
                inscrits: 15,
            },
            {
                centre: 'CFP Nabeul',
                specialite: 'Comptabilité',
                date_debut: '2023-11-15',
                places: 15,
                inscrits: 10,
            },
        ]);

        // Données des événements
        const events = ref([
            {
                title: 'Réunion des DirecteursCentres',
                date: '2023-09-10',
                description:
                    'Préparation rentrée scolaire avec tous les directeurs des centres de formation',
                centre: 'Tous',
            },
            {
                title: 'Journée Portes Ouvertes',
                date: '2023-09-25',
                description:
                    'Présentation des formations disponibles pour la nouvelle année académique',
                centre: 'CFP Tunis 1',
            },
            {
                title: 'Formation Sécurité',
                date: '2023-10-05',
                description:
                    'Formation sécurité pour formateurs sur les nouvelles normes en atelier',
                centre: 'CFP Sfax 2',
            },
            {
                title: 'Audit Qualité',
                date: '2023-10-20',
                description:
                    'Audit qualité dans les centres du Nord pour certification ISO',
                centre: 'Région Nord',
            },
        ]);

        // Données des alertes
        const alertes = ref([
            {
                date: '2023-09-01',
                centre: 'CFP Bizerte',
                type: 'Maintenance',
                message: 'Atelier Mécanique nécessite réparation urgente',
            },
            {
                date: '2023-09-02',
                centre: 'CFP Kairouan',
                type: 'Absentéisme',
                message: 'Taux absentéisme élevé groupe AGRI-202',
            },
            {
                date: '2023-09-03',
                centre: 'CFP Gabès',
                type: 'Ressources',
                message: 'Pénurie matières premières pour spécialité Chimie',
            },
            {
                date: '2023-09-04',
                centre: 'CFP Sousse',
                type: 'Personnel',
                message: '3 formateurs en congé maladie simultanés',
            },
        ]);

        // Filtres
        const selectedRegion = ref();
        const selectedGouvernorat = ref();

        // Dialogs
        const centreDialog = ref(false);
        const eventDialog = ref(false);
        const selectedCentre = ref(null);
        const selectedEvent = ref(null);

        // Computed properties
        const filteredGouvernorats = computed(() => {
            if (!selectedRegion.value) return gouvernorats.value;
            return gouvernorats.value.filter(
                (g) => g.region === selectedRegion.value.name
            );
        });

        const filteredCentres = computed(() => {
            let result = centres.value;

            if (selectedRegion.value) {
                result = result.filter(
                    (c) => c.region === selectedRegion.value.name
                );
            }

            if (selectedGouvernorat.value) {
                result = result.filter(
                    (c) => c.gouvernorat === selectedGouvernorat.value.name
                );
            }

            return result;
        });

        // Méthodes
        const formatDate = (dateString) => {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('fr-FR', options);
        };

        const formatShortDate = (dateString) => {
            return new Date(dateString).toLocaleDateString('fr-FR', {
                day: 'numeric',
                month: 'short',
            });
        };

        const getSeverity = (type) => {
            switch (type) {
                case 'Maintenance':
                    return 'warning';
                case 'Absentéisme':
                    return 'danger';
                case 'Ressources':
                    return 'info';
                case 'Personnel':
                    return 'help';
                default:
                    return null;
            }
        };

        const getStatutSeverity = (statut) => {
            switch (statut) {
                case 'Actif':
                    return 'success';
                case 'Rénovation':
                    return 'warning';
                case 'Construction':
                    return 'info';
                case 'Inactif':
                    return 'danger';
                default:
                    return null;
            }
        };

        const getCapacityClass = (ratio) => {
            if (ratio >= 0.9) return 'bg-red-100';
            if (ratio >= 0.7) return 'bg-orange-100';
            if (ratio >= 0.5) return 'bg-yellow-100';
            return 'bg-green-100';
        };

        const eventSeverity = (event) => {
            const today = new Date();
            const eventDate = new Date(event.date);
            const diffTime = eventDate - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays < 0)
                return { class: 'bg-gray-500', icon: 'pi pi-check' };
            if (diffDays <= 7)
                return {
                    class: 'bg-red-500',
                    icon: 'pi pi-exclamation-triangle',
                };
            if (diffDays <= 14)
                return { class: 'bg-orange-500', icon: 'pi pi-calendar' };
            return { class: 'bg-blue-500', icon: 'pi pi-calendar' };
        };

        const openCentreDetails = (centre) => {
            selectedCentre.value = centre;
            centreDialog.value = true;
        };

        const openCentreStats = (centre) => {
            toast.add({
                severity: 'info',
                summary: 'Statistiques',
                detail: `Ouverture des statistiques pour ${centre.nom}`,
                life: 3000,
            });
        };

        const openEventDialog = (event) => {
            selectedEvent.value = event;
            eventDialog.value = true;
        };

        const refreshData = () => {
            toast.add({
                severity: 'success',
                summary: 'Actualisation',
                detail: 'Les données ont été actualisées',
                life: 3000,
            });
        };

        // Initialisation
        onMounted(() => {
            // Simuler un chargement de données
            setTimeout(() => {
                toast.add({
                    severity: 'success',
                    summary: 'Chargement terminé',
                    detail: 'Tableau de bord prêt',
                    life: 3000,
                });
            }, 1000);
        });

        return {
            dateRange,
            timeRange,
            timeOptions,
            stats,
            chartData,
            lineChartOptions,
            pieChartOptions,
            upcomingFormations,
            events,
            alertes,
            regions,
            gouvernorats,
            selectedRegion,
            selectedGouvernorat,
            filteredGouvernorats,
            filteredCentres,
            selectedCentre,
            selectedEvent,
            centreDialog,
            eventDialog,
            formatDate,
            formatShortDate,
            getSeverity,
            getStatutSeverity,
            getCapacityClass,
            eventSeverity,
            openCentreDetails,
            openCentreStats,
            openEventDialog,
            refreshData,
        };
    },
};
</script>

<style scoped>
.dashboard-container {
    padding: 1.5rem;
}

.p-card {
    border-radius: 12px;
    overflow: hidden;
}

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.transition-all {
    transition: all 0.3s ease;
}

.h-20rem {
    height: 20rem;
}

.h-16rem {
    height: 16rem;
}

.z-1 {
    z-index: 1;
}

.bg-red-100 {
    background-color: rgba(254, 226, 226, 0.5);
}

.bg-orange-100 {
    background-color: rgba(255, 237, 213, 0.5);
}

.bg-yellow-100 {
    background-color: rgba(254, 243, 199, 0.5);
}

.bg-green-100 {
    background-color: rgba(220, 252, 231, 0.5);
}

.bg-blue-100 {
    background-color: rgba(219, 234, 254, 0.5);
}

.bg-gray-100 {
    background-color: rgba(243, 244, 246, 0.5);
}
</style>
