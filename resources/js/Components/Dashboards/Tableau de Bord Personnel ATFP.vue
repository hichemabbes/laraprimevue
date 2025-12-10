<template>
    <div class="dashboard-container">
        <!-- En-tête avec titre et outils -->
        <div class="flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="text-3xl font-bold text-900">
                    Tableau de Bord Personnel ATFP
                </h1>
                <span class="text-600"
                    >Vue d'ensemble des activités et indicateurs clés</span
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
                            <i class="pi pi-users text-primary"></i>
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
                                    :value="`+${stats.personnels.centrale} centrale`"
                                    severity="success"
                                    class="font-semibold"
                                />
                            </div>
                            <div class="flex justify-content-between">
                                <span class="text-600"
                                    >Formateurs:
                                    {{ stats.personnels.formateurs }}</span
                                >
                                <span class="text-600"
                                    >Administratifs:
                                    {{ stats.personnels.administratifs }}</span
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
                            <i class="pi pi-id-card text-blue-500"></i>
                            <span>Formations</span>
                        </div>
                    </template>
                    <template #content>
                        <div class="flex flex-column gap-3">
                            <div
                                class="flex justify-content-between align-items-center"
                            >
                                <span class="text-900 font-bold text-4xl">{{
                                    stats.formations.total
                                }}</span>
                                <Tag
                                    :value="`+${stats.formations.encours} en cours`"
                                    severity="info"
                                    class="font-semibold"
                                />
                            </div>
                            <div class="flex justify-content-between">
                                <span class="text-600"
                                    >Terminées:
                                    {{ stats.formations.terminees }}</span
                                >
                                <span class="text-600"
                                    >Planifiées:
                                    {{ stats.formations.planifiees }}</span
                                >
                            </div>
                            <ProgressBar
                                :value="
                                    (stats.formations.terminees /
                                        stats.formations.total) *
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
                            <i class="pi pi-chart-line text-orange-500"></i>
                            <span>Indicateurs</span>
                        </div>
                    </template>
                    <template #content>
                        <div class="flex flex-column gap-3">
                            <div
                                class="flex justify-content-between align-items-center"
                            >
                                <span class="text-900 font-bold text-4xl"
                                    >{{ stats.indicateurs.taux }}%</span
                                >
                                <Knob
                                    v-model="stats.indicateurs.taux"
                                    :size="60"
                                    :strokeWidth="8"
                                    readonly
                                    valueColor="var(--orange-500)"
                                    rangeColor="var(--surface-200)"
                                />
                            </div>
                            <div class="flex justify-content-between">
                                <span class="text-600"
                                    >Réussite:
                                    {{ stats.indicateurs.reussite }}%</span
                                >
                                <span class="text-600"
                                    >Satisfaction:
                                    {{ stats.indicateurs.satisfaction }}%</span
                                >
                            </div>
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
                            <i class="pi pi-inbox text-green-500"></i>
                            <span>Alertes</span>
                        </div>
                    </template>
                    <template #content>
                        <div class="flex flex-column gap-3">
                            <div
                                class="flex justify-content-between align-items-center"
                            >
                                <span class="text-900 font-bold text-4xl">{{
                                    stats.alertes.total
                                }}</span>
                                <Tag
                                    :value="`+${stats.alertes.nonLues} non lues`"
                                    severity="danger"
                                    class="font-semibold"
                                />
                            </div>
                            <div class="flex justify-content-between">
                                <span class="text-600"
                                    >Urgentes:
                                    {{ stats.alertes.urgentes }}</span
                                >
                                <span class="text-600"
                                    >Normales:
                                    {{ stats.alertes.normales }}</span
                                >
                            </div>
                            <ProgressBar
                                :value="
                                    (stats.alertes.nonLues /
                                        stats.alertes.total) *
                                    100
                                "
                                :showValue="false"
                                class="h-1rem bg-red-100"
                            />
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
                                            type="bar"
                                            :data="chartData.activite"
                                            :options="barChartOptions"
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
                                <h5 class="mb-3">Dernières Formations</h5>
                                <DataTable
                                    :value="lastFormations"
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
                                        field="date_fin"
                                        header="Fin"
                                        sortable
                                    >
                                        <template #body="{ data }">
                                            <Tag
                                                :value="
                                                    formatShortDate(
                                                        data.date_fin
                                                    )
                                                "
                                                icon="pi pi-calendar"
                                            />
                                        </template>
                                    </Column>
                                    <Column
                                        field="stagiaires"
                                        header="Stagiaires"
                                        sortable
                                    ></Column>
                                </DataTable>
                            </div>

                            <div class="col-12 md:col-6">
                                <h5 class="mb-3">Événements Récents</h5>
                                <Timeline
                                    :value="recentEvents"
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
                                <span>Mes Missions</span>
                            </div>
                        </template>
                        <template #content>
                            <div class="grid">
                                <div class="col-12">
                                    <div
                                        class="text-center p-3 border-round surface-card mb-3"
                                    >
                                        <div
                                            class="text-900 font-bold text-3xl"
                                        >
                                            {{ missions.total }}
                                        </div>
                                        <div class="text-600">
                                            Missions assignées
                                        </div>
                                        <Divider class="my-2" />
                                        <div
                                            class="flex justify-content-center gap-4"
                                        >
                                            <div>
                                                <i
                                                    class="pi pi-check-circle text-green-500"
                                                ></i>
                                                <span class="ml-1">{{
                                                    missions.completed
                                                }}</span>
                                            </div>
                                            <div>
                                                <i
                                                    class="pi pi-spinner text-blue-500"
                                                ></i>
                                                <span class="ml-1">{{
                                                    missions.inProgress
                                                }}</span>
                                            </div>
                                            <div>
                                                <i
                                                    class="pi pi-clock text-orange-500"
                                                ></i>
                                                <span class="ml-1">{{
                                                    missions.pending
                                                }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card class="border-round-lg shadow-2">
                        <template #title>
                            <div class="flex align-items-center gap-2">
                                <i class="pi pi-map text-blue-500"></i>
                                <span>Centres Assignés</span>
                            </div>
                        </template>
                        <template #content>
                            <DataTable
                                :value="assignedCenters"
                                :rows="5"
                                scrollable
                            >
                                <Column
                                    field="code"
                                    header="Code"
                                    sortable
                                ></Column>
                                <Column
                                    field="nom"
                                    header="Nom"
                                    sortable
                                ></Column>
                                <Column
                                    field="visites"
                                    header="Visites"
                                    sortable
                                >
                                    <template #body="{ data }">
                                        <Tag
                                            :value="data.visites"
                                            severity="info"
                                        />
                                    </template>
                                </Column>
                            </DataTable>
                        </template>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Section inférieure - Détails des activités -->
        <div class="grid mt-4">
            <div class="col-12">
                <Card class="border-round-lg shadow-2">
                    <template #title>
                        <div
                            class="flex justify-content-between align-items-center"
                        >
                            <span>Mes Activités Récentes</span>
                            <Button
                                label="Exporter"
                                icon="pi pi-download"
                                class="p-button-outlined"
                            />
                        </div>
                    </template>
                    <template #content>
                        <TabView>
                            <TabPanel header="Visites">
                                <DataTable
                                    :value="visits"
                                    :paginator="true"
                                    :rows="5"
                                    responsiveLayout="scroll"
                                >
                                    <Column field="date" header="Date" sortable>
                                        <template #body="{ data }">
                                            {{ formatDate(data.date) }}
                                        </template>
                                    </Column>
                                    <Column
                                        field="centre"
                                        header="Centre"
                                        sortable
                                    ></Column>
                                    <Column field="type" header="Type" sortable>
                                        <template #body="{ data }">
                                            <Tag
                                                :value="data.type"
                                                severity="info"
                                            />
                                        </template>
                                    </Column>
                                    <Column
                                        field="statut"
                                        header="Statut"
                                        sortable
                                    >
                                        <template #body="{ data }">
                                            <Tag
                                                :value="data.statut"
                                                :severity="
                                                    data.statut === 'Terminée'
                                                        ? 'success'
                                                        : data.statut ===
                                                            'Planifiée'
                                                          ? 'warning'
                                                          : 'danger'
                                                "
                                            />
                                        </template>
                                    </Column>
                                    <Column header="Rapport">
                                        <template #body>
                                            <Button
                                                icon="pi pi-eye"
                                                class="p-button-text p-button-sm"
                                            />
                                        </template>
                                    </Column>
                                </DataTable>
                            </TabPanel>
                            <TabPanel header="Formations">
                                <DataTable
                                    :value="trainings"
                                    :paginator="true"
                                    :rows="5"
                                    responsiveLayout="scroll"
                                >
                                    <Column field="date" header="Date" sortable>
                                        <template #body="{ data }">
                                            {{ formatDate(data.date) }}
                                        </template>
                                    </Column>
                                    <Column
                                        field="titre"
                                        header="Titre"
                                        sortable
                                    ></Column>
                                    <Column
                                        field="centre"
                                        header="Centre"
                                        sortable
                                    ></Column>
                                    <Column
                                        field="participants"
                                        header="Participants"
                                        sortable
                                    ></Column>
                                    <Column
                                        field="statut"
                                        header="Statut"
                                        sortable
                                    >
                                        <template #body="{ data }">
                                            <Tag
                                                :value="data.statut"
                                                :severity="
                                                    data.statut === 'Terminée'
                                                        ? 'success'
                                                        : data.statut ===
                                                            'En cours'
                                                          ? 'info'
                                                          : 'warning'
                                                "
                                            />
                                        </template>
                                    </Column>
                                </DataTable>
                            </TabPanel>
                            <TabPanel header="Évaluations">
                                <DataTable
                                    :value="evaluations"
                                    :paginator="true"
                                    :rows="5"
                                    responsiveLayout="scroll"
                                >
                                    <Column field="date" header="Date" sortable>
                                        <template #body="{ data }">
                                            {{ formatDate(data.date) }}
                                        </template>
                                    </Column>
                                    <Column
                                        field="centre"
                                        header="Centre"
                                        sortable
                                    ></Column>
                                    <Column
                                        field="type"
                                        header="Type"
                                        sortable
                                    ></Column>
                                    <Column
                                        field="resultat"
                                        header="Résultat"
                                        sortable
                                    >
                                        <template #body="{ data }">
                                            <Tag
                                                :value="data.resultat"
                                                :severity="
                                                    data.resultat === 'Conforme'
                                                        ? 'success'
                                                        : data.resultat ===
                                                            'Partiel'
                                                          ? 'warning'
                                                          : 'danger'
                                                "
                                            />
                                        </template>
                                    </Column>
                                    <Column header="Détails">
                                        <template #body>
                                            <Button
                                                icon="pi pi-file-pdf"
                                                class="p-button-text p-button-sm"
                                            />
                                        </template>
                                    </Column>
                                </DataTable>
                            </TabPanel>
                        </TabView>
                    </template>
                </Card>
            </div>
        </div>

        <!-- Dialog Détails Événement -->
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
import { ref, computed, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';

export default {
    setup() {
        const toast = useToast();
        const dateRange = ref();
        const timeRange = ref('month');
        const timeOptions = ref([
            { label: '7 jours', value: 'week' },
            { label: 'Mois', value: 'month' },
            { label: 'Trimestre', value: 'quarter' },
        ]);

        // Données statistiques
        const stats = ref({
            personnels: {
                total: 4520,
                centrale: 320,
                formateurs: 3800,
                administratifs: 400,
            },
            formations: {
                total: 685,
                encours: 120,
                terminees: 420,
                planifiees: 145,
            },
            indicateurs: {
                taux: 78,
                reussite: 82,
                satisfaction: 87,
            },
            alertes: {
                total: 24,
                nonLues: 5,
                urgentes: 3,
                normales: 21,
            },
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
                        label: 'Visites réalisées',
                        data: [12, 15, 8, 10, 14, 18, 16, 20, 15, 22, 18, 25],
                        backgroundColor: '#3B82F6',
                    },
                    {
                        label: 'Formations supervisées',
                        data: [5, 8, 6, 9, 12, 15, 14, 18, 12, 20, 15, 22],
                        backgroundColor: '#10B981',
                    },
                ],
            },
            specialites: {
                labels: [
                    'Informatique',
                    'Électricité',
                    'Mécanique',
                    'BTP',
                    'Commerce',
                    'Services',
                ],
                datasets: [
                    {
                        data: [25, 20, 18, 15, 12, 10],
                        backgroundColor: [
                            '#3B82F6',
                            '#10B981',
                            '#F59E0B',
                            '#EF4444',
                            '#8B5CF6',
                            '#64748B',
                        ],
                    },
                ],
            },
            regions: {
                labels: ['Nord', 'Centre', 'Sud'],
                datasets: [
                    {
                        data: [45, 30, 25],
                        backgroundColor: ['#3B82F6', '#10B981', '#F59E0B'],
                    },
                ],
            },
        });

        // Options des graphiques
        const barChartOptions = ref({
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                    },
                },
                x: {
                    grid: {
                        display: false,
                    },
                },
            },
            maintainAspectRatio: false,
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
            },
            maintainAspectRatio: false,
        });

        // Données des dernières formations
        const lastFormations = ref([
            {
                centre: 'CFP Tunis 1',
                specialite: 'Développement Web',
                date_fin: '2023-10-15',
                stagiaires: 25,
            },
            {
                centre: 'CFP Sfax 2',
                specialite: 'Électricité Bâtiment',
                date_fin: '2023-10-10',
                stagiaires: 20,
            },
            {
                centre: 'CFP Sousse',
                specialite: 'Maintenance Industrielle',
                date_fin: '2023-10-05',
                stagiaires: 18,
            },
            {
                centre: 'CFP Gabès',
                specialite: 'Plomberie',
                date_fin: '2023-09-28',
                stagiaires: 15,
            },
            {
                centre: 'CFP Nabeul',
                specialite: 'Tourisme',
                date_fin: '2023-09-20',
                stagiaires: 22,
            },
        ]);

        // Événements récents
        const recentEvents = ref([
            {
                title: 'Réunion des Coordinateurs',
                date: '2023-10-20',
                description: 'Réunion mensuelle des coordinateurs pédagogiques',
                centre: 'ATFP Siège',
                type: 'meeting',
            },
            {
                title: 'Visite de Supervision',
                date: '2023-10-18',
                description: 'Visite de supervision au CFP Tunis 1',
                centre: 'CFP Tunis 1',
                type: 'visit',
            },
            {
                title: 'Formation FormateursCentres',
                date: '2023-10-15',
                description:
                    'Formation sur les nouvelles méthodes pédagogiques',
                centre: 'ATFP Siège',
                type: 'training',
            },
            {
                title: 'Conseil Pédagogique',
                date: '2023-10-10',
                description: 'Conseil pédagogique régional Nord',
                centre: 'Région Nord',
                type: 'council',
            },
        ]);

        // Alertes
        const alertes = ref([
            {
                date: '2023-10-22',
                type: 'Maintenance',
                message:
                    'Atelier Mécanique nécessite réparation au CFP Bizerte',
            },
            {
                date: '2023-10-21',
                type: 'Ressources',
                message: 'Pénurie matières premières pour spécialité Chimie',
            },
            {
                date: '2023-10-20',
                type: 'Personnel',
                message: '3 formateurs en congé maladie simultanés à Sousse',
            },
            {
                date: '2023-10-19',
                type: 'Urgent',
                message: 'Problème de sécurité signalé au CFP Tunis 2',
            },
            {
                date: '2023-10-18',
                type: 'Information',
                message: 'Nouvelle circulaire ministérielle disponible',
            },
        ]);

        // Missions
        const missions = ref({
            total: 15,
            completed: 8,
            inProgress: 4,
            pending: 3,
        });

        // Centres assignés
        const assignedCenters = ref([
            { code: 'CFP001', nom: 'CFP Tunis 1', visites: 5 },
            { code: 'CFP002', nom: 'CFP Sfax 2', visites: 3 },
            { code: 'CFP003', nom: 'CFP Sousse', visites: 2 },
        ]);

        // Visites
        const visits = ref([
            {
                date: '2023-10-18',
                centre: 'CFP Tunis 1',
                type: 'Supervision',
                statut: 'Terminée',
            },
            {
                date: '2023-10-15',
                centre: 'CFP Sfax 2',
                type: 'Évaluation',
                statut: 'Terminée',
            },
            {
                date: '2023-10-10',
                centre: 'CFP Sousse',
                type: 'Audit',
                statut: 'Terminée',
            },
            {
                date: '2023-11-05',
                centre: 'CFP Gabès',
                type: 'Supervision',
                statut: 'Planifiée',
            },
            {
                date: '2023-11-12',
                centre: 'CFP Nabeul',
                type: 'Évaluation',
                statut: 'Planifiée',
            },
        ]);

        // Formations
        const trainings = ref([
            {
                date: '2023-10-15',
                titre: 'Nouvelles Méthodes Pédagogiques',
                centre: 'ATFP Siège',
                participants: 25,
                statut: 'Terminée',
            },
            {
                date: '2023-10-20',
                titre: 'Utilisation des NTIC',
                centre: 'CFP Tunis 1',
                participants: 18,
                statut: 'En cours',
            },
            {
                date: '2023-11-05',
                titre: 'Sécurité en Atelier',
                centre: 'CFP Sousse',
                participants: 15,
                statut: 'Planifiée',
            },
        ]);

        // Évaluations
        const evaluations = ref([
            {
                date: '2023-10-18',
                centre: 'CFP Tunis 1',
                type: 'Pédagogique',
                resultat: 'Conforme',
            },
            {
                date: '2023-10-15',
                centre: 'CFP Sfax 2',
                type: 'Infrastructure',
                resultat: 'Partiel',
            },
            {
                date: '2023-10-10',
                centre: 'CFP Sousse',
                type: 'Administrative',
                resultat: 'Non conforme',
            },
        ]);

        // Dialogs
        const eventDialog = ref(false);
        const selectedEvent = ref(null);

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
                case 'Urgent':
                    return 'danger';
                case 'Maintenance':
                    return 'warning';
                case 'Ressources':
                    return 'info';
                case 'Personnel':
                    return 'help';
                default:
                    return null;
            }
        };

        const eventSeverity = (item) => {
            const types = {
                meeting: { class: 'bg-blue-500', icon: 'pi pi-users' },
                visit: { class: 'bg-green-500', icon: 'pi pi-map-marker' },
                training: { class: 'bg-orange-500', icon: 'pi pi-book' },
                council: { class: 'bg-purple-500', icon: 'pi pi-briefcase' },
                default: { class: 'bg-gray-500', icon: 'pi pi-info-circle' },
            };

            return types[item.type] || types.default;
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
            barChartOptions,
            pieChartOptions,
            lastFormations,
            recentEvents,
            alertes,
            missions,
            assignedCenters,
            visits,
            trainings,
            evaluations,
            eventDialog,
            selectedEvent,
            formatDate,
            formatShortDate,
            getSeverity,
            eventSeverity,
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
</style>
