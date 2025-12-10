<template>
    <div class="grid">
        <!-- Section supérieure - Statistiques globales -->
        <div class="col-12">
            <div class="grid">
                <!-- Carte Centres de Formation -->
                <div class="col-12 md:col-6 lg:col-3">
                    <Card class="h-full">
                        <template #title>Centres de Formation</template>
                        <template #content>
                            <div
                                class="flex justify-content-between align-items-center"
                            >
                                <div>
                                    <div
                                        class="text-4xl font-bold text-primary"
                                    >
                                        {{ stats.centres.total }}
                                    </div>
                                    <div class="text-500">Total centres</div>
                                </div>
                                <div class="flex flex-column">
                                    <span class="text-green-500 font-medium"
                                        >+{{
                                            stats.centres.actifs
                                        }}
                                        actifs</span
                                    >
                                    <span class="text-blue-500 font-medium"
                                        >+{{ stats.centres.construction }} en
                                        construction</span
                                    >
                                </div>
                            </div>
                            <Divider />
                            <div class="flex justify-content-between">
                                <span class="text-500"
                                    >Publics: {{ stats.centres.publics }}</span
                                >
                                <span class="text-500"
                                    >Privés: {{ stats.centres.prives }}</span
                                >
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Carte PersonnelsDirectionCentrale -->
                <div class="col-12 md:col-6 lg:col-3">
                    <Card class="h-full">
                        <template #title>Personnels</template>
                        <template #content>
                            <div
                                class="flex justify-content-between align-items-center"
                            >
                                <div>
                                    <div
                                        class="text-4xl font-bold text-primary"
                                    >
                                        {{ stats.personnels.total }}
                                    </div>
                                    <div class="text-500">Total personnels</div>
                                </div>
                                <div class="flex flex-column">
                                    <span class="text-green-500 font-medium"
                                        >+{{
                                            stats.personnels.centrale
                                        }}
                                        centrale</span
                                    >
                                    <span class="text-blue-500 font-medium"
                                        >+{{
                                            stats.personnels.centres
                                        }}
                                        centres</span
                                    >
                                </div>
                            </div>
                            <Divider />
                            <div class="flex justify-content-between">
                                <span class="text-500"
                                    >Formateurs:
                                    {{ stats.personnels.formateurs }}</span
                                >
                                <span class="text-500"
                                    >Directeurs:
                                    {{ stats.personnels.directeurs }}</span
                                >
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Carte StagiairesCentres -->
                <div class="col-12 md:col-6 lg:col-3">
                    <Card class="h-full">
                        <template #title>Stagiaires</template>
                        <template #content>
                            <div
                                class="flex justify-content-between align-items-center"
                            >
                                <div>
                                    <div
                                        class="text-4xl font-bold text-primary"
                                    >
                                        {{ stats.stagiaires.total }}
                                    </div>
                                    <div class="text-500">Total stagiaires</div>
                                </div>
                                <div class="flex flex-column">
                                    <span class="text-green-500 font-medium"
                                        >+{{
                                            stats.stagiaires.residentiel
                                        }}
                                        résidentiel</span
                                    >
                                    <span class="text-blue-500 font-medium"
                                        >+{{
                                            stats.stagiaires.alternance
                                        }}
                                        alternance</span
                                    >
                                </div>
                            </div>
                            <Divider />
                            <div class="flex justify-content-between">
                                <span class="text-500"
                                    >Apprentissage:
                                    {{ stats.stagiaires.apprentissage }}</span
                                >
                                <span class="text-500"
                                    >F0: {{ stats.stagiaires.f0 }}</span
                                >
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Carte Taux de Remplissage -->
                <div class="col-12 md:col-6 lg:col-3">
                    <Card class="h-full">
                        <template #title>Capacité Globale</template>
                        <template #content>
                            <div
                                class="flex justify-content-between align-items-center"
                            >
                                <div>
                                    <div
                                        class="text-4xl font-bold text-primary"
                                    >
                                        {{ stats.capacite.taux }}%
                                    </div>
                                    <div class="text-500">Taux remplissage</div>
                                </div>
                                <div>
                                    <Knob
                                        v-model="stats.capacite.taux"
                                        :size="80"
                                        :strokeWidth="10"
                                        readonly
                                        valueColor="var(--primary-color)"
                                        rangeColor="var(--surface-200)"
                                    />
                                </div>
                            </div>
                            <Divider />
                            <div class="flex justify-content-between">
                                <span class="text-500"
                                    >Places: {{ stats.capacite.occupees }}/{{
                                        stats.capacite.totales
                                    }}</span
                                >
                                <span class="text-500"
                                    >Centres:
                                    {{ stats.capacite.centres_utilises }}/{{
                                        stats.capacite.centres_totaux
                                    }}</span
                                >
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Section centrale - Graphiques et données clés -->
        <div class="col-12 lg:col-8">
            <Card>
                <template #title>Activité des Centres</template>
                <template #subtitle>Dernières 12 semaines</template>
                <template #content>
                    <div class="grid">
                        <div class="col-12">
                            <Chart
                                type="line"
                                :data="chartData.activite"
                                :options="chartOptions"
                                :height="250"
                            />
                        </div>
                        <div class="col-12 md:col-6">
                            <div class="text-500">
                                Prochaines Formations par Centre
                            </div>
                            <DataTable
                                :value="upcomingFormations"
                                :rows="5"
                                :paginator="true"
                                responsiveLayout="scroll"
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
                                        {{ formatDate(data.date_debut) }}
                                    </template>
                                </Column>
                                <Column field="places" header="Places" sortable>
                                    <template #body="{ data }">
                                        <ProgressBar
                                            :value="
                                                (data.inscrits / data.places) *
                                                100
                                            "
                                            :showValue="false"
                                        />
                                        <span
                                            >{{ data.inscrits }}/{{
                                                data.places
                                            }}</span
                                        >
                                    </template>
                                </Column>
                            </DataTable>
                        </div>
                        <div class="col-12 md:col-6">
                            <div class="text-500">Événements à venir</div>
                            <Timeline
                                :value="events"
                                align="alternate"
                                class="mt-3"
                            >
                                <template #content="{ item }">
                                    <Card class="mb-3">
                                        <template #title>{{
                                            item.title
                                        }}</template>
                                        <template #subtitle>{{
                                            formatDate(item.date)
                                        }}</template>
                                        <template #content>
                                            <p class="m-0">
                                                {{ item.description }}
                                            </p>
                                            <Tag
                                                :value="item.centre"
                                                severity="info"
                                                class="mt-2"
                                            />
                                        </template>
                                    </Card>
                                </template>
                            </Timeline>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Section droite - Résumé et alertes -->
        <div class="col-12 lg:col-4">
            <Card>
                <template #title>Résumé Global</template>
                <template #content>
                    <div class="mb-4">
                        <h5>Répartition par Spécialité</h5>
                        <Chart
                            type="doughnut"
                            :data="chartData.specialites"
                            :options="noAxesChartOptions"
                            :height="200"
                        />
                    </div>

                    <Divider />

                    <div class="mb-4">
                        <h5>Alertes Récentes</h5>
                        <DataTable
                            :value="alertes"
                            :rows="5"
                            responsiveLayout="scroll"
                        >
                            <Column field="date" header="Date" sortable>
                                <template #body="{ data }">
                                    {{ formatShortDate(data.date) }}
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
                                        :severity="getSeverity(data.type)"
                                    />
                                </template>
                            </Column>
                            <Column field="message" header="Message"></Column>
                        </DataTable>
                    </div>

                    <Divider />

                    <div>
                        <h5>Taux de Réussite</h5>
                        <div class="grid">
                            <div class="col-6">
                                <div class="text-center">
                                    <div
                                        class="text-4xl font-bold text-primary"
                                    >
                                        {{ stats.reussite.global }}%
                                    </div>
                                    <div class="text-500">Global</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <div
                                        class="text-4xl font-bold"
                                        :class="
                                            stats.reussite.tendance >= 0
                                                ? 'text-green-500'
                                                : 'text-red-500'
                                        "
                                    >
                                        {{ stats.reussite.tendance }}%
                                    </div>
                                    <div class="text-500">Tendance</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Section inférieure - Détails par centre -->
        <div class="col-12">
            <Card>
                <template #title>Détails par Centre</template>
                <template #content>
                    <TabView>
                        <TabPanel header="Centres">
                            <div class="grid">
                                <div class="col-12 md:col-6">
                                    <Dropdown
                                        v-model="selectedRegion"
                                        :options="regions"
                                        optionLabel="name"
                                        placeholder="Sélectionner une région"
                                        class="w-full mb-3"
                                    />
                                </div>
                                <div class="col-12 md:col-6">
                                    <Dropdown
                                        v-model="selectedGouvernorat"
                                        :options="filteredGouvernorats"
                                        optionLabel="name"
                                        placeholder="Sélectionner un gouvernorat"
                                        class="w-full mb-3"
                                    />
                                </div>
                            </div>

                            <DataTable
                                :value="filteredCentres"
                                :paginator="true"
                                :rows="10"
                                responsiveLayout="scroll"
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
                                    field="region"
                                    header="Région"
                                    sortable
                                ></Column>
                                <Column
                                    field="gouvernorat"
                                    header="Gouvernorat"
                                    sortable
                                ></Column>
                                <Column field="type" header="Type" sortable>
                                    <template #="{ data }">
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
                                <Column field="statut" header="Statut" sortable>
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
                                >
                                    <template #body="{ data }">
                                        <ProgressBar
                                            :value="
                                                (data.occupation /
                                                    data.capacite) *
                                                100
                                            "
                                            :showValue="false"
                                        />
                                        <span
                                            >{{ data.occupation }}/{{
                                                data.capacite
                                            }}</span
                                        >
                                    </template>
                                </Column>
                                <Column header="Actions">
                                    <template #body="{ data }">
                                        <Button
                                            icon="pi pi-eye"
                                            class="p-button-text p-button-rounded p-button-info"
                                            @click="openCentreDetails(data)"
                                        />
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Indicateurs Clés">
                            <div class="grid">
                                <div class="col-12 md:col-6">
                                    <h5>Taux de Réussite par Centre</h5>
                                    <Chart
                                        type="bar"
                                        :data="chartData.reussiteCentres"
                                        :options="chartOptions"
                                        :height="300"
                                    />
                                </div>
                                <div class="col-12 md:col-6">
                                    <h5>Taux de Remplissage par Centre</h5>
                                    <Chart
                                        type="bar"
                                        :data="chartData.remplissageCentres"
                                        :options="chartOptions"
                                        :height="300"
                                    />
                                </div>
                                <div class="col-12">
                                    <h5>
                                        Répartition des Spécialités par Centre
                                    </h5>
                                    <DataTable
                                        :value="specialitesParCentre"
                                        :paginator="true"
                                        :rows="5"
                                        responsiveLayout="scroll"
                                    >
                                        <Column
                                            field="centre"
                                            header="Centre"
                                            sortable
                                        ></Column>
                                        <Column
                                            field="specialites"
                                            header="Spécialités"
                                            sortable
                                        >
                                            <template #body="{ data }">
                                                <Chip
                                                    v-for="spec in data.specialites"
                                                    :label="spec"
                                                    class="mr-2 mb-2"
                                                />
                                            </template>
                                        </Column>
                                        <Column
                                            field="groupes"
                                            header="Groupes Actifs"
                                            sortable
                                        ></Column>
                                        <Column
                                            field="stagiaires"
                                            header="Stagiaires"
                                            sortable
                                        ></Column>
                                    </DataTable>
                                </div>
                            </div>
                        </TabPanel>
                        <TabPanel header="Infrastructure">
                            <div class="grid">
                                <div class="col-12 md:col-6">
                                    <h5>État des Centres</h5>
                                    <Chart
                                        type="pie"
                                        :data="chartData.etatCentres"
                                        :options="noAxesChartOptions"
                                        :height="250"
                                    />
                                </div>
                                <div class="col-12 md:col-6">
                                    <h5>Travaux en Cours</h5>
                                    <DataTable
                                        :value="travauxEnCours"
                                        :rows="5"
                                        responsiveLayout="scroll"
                                    >
                                        <Column
                                            field="centre"
                                            header="Centre"
                                            sortable
                                        ></Column>
                                        <Column
                                            field="type_travaux"
                                            header="Type"
                                            sortable
                                        >
                                            <template #body="{ data }">
                                                <Tag
                                                    :value="data.type_travaux"
                                                    severity="info"
                                                />
                                            </template>
                                        </Column>
                                        <Column
                                            field="date_debut"
                                            header="Début"
                                            sortable
                                        >
                                            <template #body="{ data }">
                                                {{
                                                    formatShortDate(
                                                        data.date_debut
                                                    )
                                                }}
                                            </template>
                                        </Column>
                                        <Column
                                            field="date_fin"
                                            header="Fin prévue"
                                            sortable
                                        >
                                            <template #body="{ data }">
                                                {{
                                                    formatShortDate(
                                                        data.date_fin
                                                    )
                                                }}
                                            </template>
                                        </Column>
                                        <Column
                                            field="cout"
                                            header="Coût (DT)"
                                            sortable
                                        ></Column>
                                    </DataTable>
                                </div>
                            </div>
                        </TabPanel>
                    </TabView>
                </template>
            </Card>
        </div>

        <!-- Dialog Détails Centre -->
        <Dialog
            v-model:visible="centreDialog"
            :style="{ width: '80vw' }"
            header="Détails du Centre"
            :modal="true"
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
    </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import Card from 'primevue/card';
import Chart from 'primevue/chart';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import ProgressBar from 'primevue/progressbar';
import Knob from 'primevue/knob';
import Divider from 'primevue/divider';
import Tag from 'primevue/tag';
import Timeline from 'primevue/timeline';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Chip from 'primevue/chip';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import CentreDetails from '@/Components/Dashboards/CentreDetails.vue';
import axios from '@/axios.js';
import { useToast } from 'primevue/usetoast';

export default {
    components: {
        Card,
        Chart,
        DataTable,
        Column,
        ProgressBar,
        Knob,
        Divider,
        Tag,
        Timeline,
        TabView,
        TabPanel,
        Chip,
        Button,
        Dropdown,
        Dialog,
        CentreDetails,
    },
    setup() {
        const toast = useToast();
        const isLoading = ref(false); // État de chargement
        const stats = ref({
            centres: {
                total: 140,
                actifs: 135,
                construction: 5,
                publics: 120,
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
                centres_totaux: 140,
            },
            reussite: {
                global: 82,
                tendance: 2.5,
            },
        });

        const chartData = ref({
            activite: {
                labels: [
                    'Sem 1',
                    'Sem 2',
                    'Sem 3',
                    'Sem 4',
                    'Sem 5',
                    'Sem 6',
                    'Sem 7',
                    'Sem 8',
                    'Sem 9',
                    'Sem 10',
                    'Sem 11',
                    'Sem 12',
                ],
                datasets: [
                    {
                        label: 'Heures Formation',
                        data: [
                            12500, 13000, 12800, 13500, 14000, 14500, 14200,
                            15000, 14800, 15500, 15200, 16000,
                        ],
                        fill: false,
                        borderColor: '#3B82F6',
                        tension: 0.4,
                    },
                    {
                        label: 'Heures Suivi',
                        data: [
                            4500, 4800, 5000, 5200, 5500, 5800, 6000, 6200,
                            6500, 6800, 7000, 7200,
                        ],
                        fill: false,
                        borderColor: '#10B981',
                        tension: 0.4,
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
                    },
                ],
            },
            reussiteCentres: {
                labels: [
                    'CFP Sfax',
                    'CFP Tunis',
                    'CFP Sousse',
                    'CFP Gabès',
                    'CFP Nabeul',
                    'CFP Bizerte',
                    'CFP Kairouan',
                ],
                datasets: [
                    {
                        label: 'Taux de Réussite (%)',
                        data: [85, 88, 82, 80, 84, 79, 81],
                        backgroundColor: '#10B981',
                    },
                ],
            },
            remplissageCentres: {
                labels: [
                    'CFP Sfax',
                    'CFP Tunis',
                    'CFP Sousse',
                    'CFP Gabès',
                    'CFP Nabeul',
                    'CFP Bizerte',
                    'CFP Kairouan',
                ],
                datasets: [
                    {
                        label: 'Taux de Remplissage (%)',
                        data: [90, 85, 88, 75, 82, 78, 80],
                        backgroundColor: '#3B82F6',
                    },
                ],
            },
            etatCentres: {
                labels: ['Bon état', 'Moyen', 'À rénover', 'En construction'],
                datasets: [
                    {
                        data: [80, 40, 15, 5],
                        backgroundColor: [
                            '#10B981',
                            '#F59E0B',
                            '#EF4444',
                            '#3B82F6',
                        ],
                    },
                ],
            },
        });

        const chartOptions = ref({
            plugins: {
                legend: {
                    position: 'bottom',
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                },
            },
            maintainAspectRatio: false,
        });

        const noAxesChartOptions = ref({
            plugins: {
                legend: {
                    position: 'bottom',
                },
            },
            maintainAspectRatio: false,
        });

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

        const events = ref([
            {
                title: 'Réunion des DirecteursCentres',
                date: '2023-09-10',
                description: 'Préparation rentrée scolaire',
                centre: 'Tous',
            },
            {
                title: 'Journée Portes Ouvertes',
                date: '2023-09-25',
                description: 'Présentation des formations',
                centre: 'CFP Tunis 1',
            },
            {
                title: 'Formation Sécurité',
                date: '2023-10-05',
                description: 'Formation sécurité pour formateurs',
                centre: 'CFP Sfax 2',
            },
            {
                title: 'Audit Qualité',
                date: '2023-10-20',
                description: 'Audit qualité dans les centres du Nord',
                centre: 'Région Nord',
            },
        ]);

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

        const specialitesParCentre = computed(() => {
            return centres.value.map((centre) => ({
                centre: centre.nom,
                specialites: centre.specialites,
                groupes: centre.groupes,
                stagiaires: centre.occupation,
            }));
        });

        const travauxEnCours = ref([
            {
                centre: 'CFP Bizerte',
                type_travaux: 'Rénovation',
                date_debut: '2023-07-15',
                date_fin: '2023-12-20',
                cout: 250000,
            },
            {
                centre: 'CFP Tunis 1',
                type_travaux: 'Extension',
                date_debut: '2023-08-01',
                date_fin: '2024-02-28',
                cout: 500000,
            },
            {
                centre: 'CFP Gabès',
                type_travaux: 'Aménagement',
                date_debut: '2023-09-01',
                date_fin: '2023-11-30',
                cout: 120000,
            },
            {
                centre: 'CFP Sfax 2',
                type_travaux: 'Équipement',
                date_debut: '2023-08-15',
                date_fin: '2023-10-31',
                cout: 180000,
            },
        ]);

        const selectedRegion = ref();
        const selectedGouvernorat = ref();
        const selectedCentre = ref();
        const centreDialog = ref(false);

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

        const formatDate = (dateString) => {
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('fr-FR', options);
        };

        const formatShortDate = (dateString) => {
            return new Date(dateString).toLocaleDateString('fr-FR', {
                month: 'short',
                day: 'numeric',
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

        const openCentreDetails = (centre) => {
            selectedCentre.value = centre;
            centreDialog.value = true;
        };

        // Récupérer les données via API
        const fetchDashboardData = async () => {
            isLoading.value = true;
            try {
                const response = await axios.get('/stats/personnels');
                if (response.data.status === 'success') {
                    stats.value = response.data.data;
                    // Mettre à jour les graphiques si nécessaire
                    if (response.data.data.chartData) {
                        chartData.value = {
                            ...chartData.value,
                            ...response.data.data.chartData,
                        };
                    }
                }
            } catch (error) {
                console.error(
                    'Erreur lors de la récupération des données :',
                    error.response?.data || error.message
                );
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec de la récupération des données du tableau de bord',
                    life: 3000,
                });
            } finally {
                isLoading.value = false;
            }
        };

        onMounted(fetchDashboardData);

        return {
            stats,
            chartData,
            chartOptions,
            noAxesChartOptions,
            upcomingFormations,
            events,
            alertes,
            regions,
            gouvernorats,
            selectedRegion,
            selectedGouvernorat,
            filteredGouvernorats,
            filteredCentres,
            specialitesParCentre,
            travauxEnCours,
            selectedCentre,
            centreDialog,
            formatDate,
            formatShortDate,
            getSeverity,
            getStatutSeverity,
            openCentreDetails,
            isLoading,
        };
    },
};
</script>

<style scoped>
.grid {
    margin-top: 0; /* Supprime toute marge supérieure */
    /* ou */
    margin-top: -1.4rem; /* Réduit encore plus si nécessaire */
}

.p-card-title {
    font-size: 1.25rem;
    font-weight: 600;
}

.p-card-subtitle {
    font-size: 0.875rem;
    color: var(--text-color-secondary);
}

.p-timeline-event-opposite {
    display: none;
}

.p-progressbar {
    height: 0.5rem;
    margin-top: 0.25rem;
}

.p-chip {
    font-size: 0.75rem;
}

.p-datatable .p-datatable-thead > tr > th {
    background: var(--surface-b);
    font-weight: 600;
}

.p-tabview .p-tabview-nav li .p-tabview-nav-link {
    padding: 1rem 1.5rem;
}

.p-knob-text {
    font-size: 1.5rem;
    font-weight: bold;
}

.centre-map {
    height: 300px;
    width: 100%;
    border-radius: 4px;
    margin-bottom: 1rem;
}
</style>
