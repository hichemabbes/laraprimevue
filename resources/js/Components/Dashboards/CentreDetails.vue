<template>
    <div v-if="centre" class="grid">
        <!-- En-tête -->
        <div class="col-12">
            <div class="flex justify-content-between align-items-center">
                <div>
                    <h2>{{ centre.nom }}</h2>
                    <p class="text-500">
                        {{ centre.code }} - {{ centre.region }}
                    </p>
                </div>
                <div class="text-right">
                    <p>
                        Directeur: <strong>{{ centre.directeur }}</strong>
                    </p>
                    <p>Date création: <strong>15/03/2010</strong></p>
                </div>
            </div>
            <Divider />
        </div>

        <!-- Cartes synthèse -->
        <div class="col-12">
            <div class="grid">
                <div class="col-12 md:col-6 lg:col-3">
                    <Card>
                        <template #title>Personnels</template>
                        <template #content>
                            <div class="text-center">
                                <div class="text-4xl font-bold text-primary">
                                    {{ centre.personnels }}
                                </div>
                                <div class="text-500">
                                    dont {{ centre.formateurs }} formateurs
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 md:col-6 lg:col-3">
                    <Card>
                        <template #title>Stagiaires</template>
                        <template #content>
                            <div class="text-center">
                                <div class="text-4xl font-bold text-primary">
                                    {{ centre.stagiaires }}
                                </div>
                                <div class="text-500">
                                    dans {{ centre.groupes }} groupes
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 md:col-6 lg:col-3">
                    <Card>
                        <template #title>Capacité</template>
                        <template #content>
                            <div class="text-center">
                                <div class="text-4xl font-bold text-primary">
                                    {{ centre.capacite }}%
                                </div>
                                <div class="text-500">Places occupées</div>
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 md:col-6 lg:col-3">
                    <Card>
                        <template #title>Réussite</template>
                        <template #content>
                            <div class="text-center">
                                <div class="text-4xl font-bold text-primary">
                                    82%
                                </div>
                                <div class="text-500">Taux moyen</div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Détails -->
        <div class="col-12 lg:col-8 mt-3">
            <TabView>
                <TabPanel header="Activité">
                    <Chart
                        type="line"
                        :data="chartData.activite"
                        :options="chartOptions"
                        height="250px"
                    />
                    <div class="grid mt-3">
                        <div class="col-12 md:col-6">
                            <h5>Répartition par mode</h5>
                            <Chart
                                type="doughnut"
                                :data="chartData.modes"
                                :options="chartOptions"
                                height="200px"
                            />
                        </div>
                        <div class="col-12 md:col-6">
                            <h5>Top 5 Spécialités</h5>
                            <Chart
                                type="bar"
                                :data="chartData.specialites"
                                :options="chartOptions"
                                height="200px"
                            />
                        </div>
                    </div>
                </TabPanel>
                <TabPanel header="Infrastructure">
                    <div class="grid">
                        <div class="col-12 md:col-6">
                            <h5>Répartition des espaces</h5>
                            <Chart
                                type="pie"
                                :data="chartData.espaces"
                                :options="chartOptions"
                                height="250px"
                            />
                        </div>
                        <div class="col-12 md:col-6">
                            <h5>État des lieux</h5>
                            <DataTable
                                :value="etatLieux"
                                :rows="5"
                                responsiveLayout="scroll"
                            >
                                <Column
                                    field="type"
                                    header="Type"
                                    sortable
                                ></Column>
                                <Column
                                    field="total"
                                    header="Total"
                                    sortable
                                ></Column>
                                <Column
                                    field="bon_etat"
                                    header="Bon État"
                                    sortable
                                >
                                    <template #body="{ data }">
                                        <ProgressBar
                                            :value="
                                                (data.bon_etat / data.total) *
                                                100
                                            "
                                            :showValue="false"
                                        />
                                        <span class="ml-2"
                                            >{{ data.bon_etat }} ({{
                                                Math.round(
                                                    (data.bon_etat /
                                                        data.total) *
                                                        100
                                                )
                                            }}%)</span
                                        >
                                    </template>
                                </Column>
                            </DataTable>
                        </div>
                    </div>
                </TabPanel>
            </TabView>
        </div>

        <!-- Informations complémentaires -->
        <div class="col-12 lg:col-4 mt-3">
            <Card>
                <template #title>Informations</template>
                <template #content>
                    <div class="mb-3">
                        <h5>Contact</h5>
                        <p><i class="pi pi-phone mr-2"></i> +216 70 123 456</p>
                        <p>
                            <i class="pi pi-envelope mr-2"></i> contact@{{
                                centre.code.toLowerCase()
                            }}.atfp.tn
                        </p>
                        <p>
                            <i class="pi pi-map-marker mr-2"></i> Rue des
                            Entrepreneurs, {{ centre.region }}
                        </p>
                    </div>
                    <Divider />
                    <div class="mb-3">
                        <h5>Dernières interventions</h5>
                        <Timeline
                            :value="interventions"
                            align="alternate"
                            class="mt-2"
                        >
                            <template #content="{ item }">
                                <div class="text-sm">
                                    <strong>{{ item.date }}</strong> -
                                    {{ item.description }}
                                </div>
                            </template>
                        </Timeline>
                    </div>
                    <Divider />
                    <div>
                        <h5>Alertes récentes</h5>
                        <DataTable
                            :value="alertes"
                            :rows="3"
                            responsiveLayout="scroll"
                            class="mt-2"
                        >
                            <Column field="date" header="Date" sortable>
                                <template #body="{ data }">
                                    {{ formatShortDate(data.date) }}
                                </template>
                            </Column>
                            <Column field="type" header="Type" sortable>
                                <template #body="{ data }">
                                    <Tag
                                        :value="data.type"
                                        :severity="getSeverity(data.type)"
                                    />
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue';
import Card from 'primevue/card';
import Chart from 'primevue/chart';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import ProgressBar from 'primevue/progressbar';
import Divider from 'primevue/divider';
import Tag from 'primevue/tag';
import Timeline from 'primevue/timeline';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';

export default {
    props: {
        centre: {
            type: Object,
            required: true,
        },
    },
    setup(props) {
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
                            1200, 1300, 1400, 1500, 1600, 1700, 1800, 1900,
                            2000, 2100, 2200, 2300,
                        ],
                        fill: false,
                        borderColor: '#3B82F6',
                        tension: 0.4,
                    },
                ],
            },
            modes: {
                labels: ['Résidentiel', 'Alternance', 'Apprentissage', 'F0'],
                datasets: [
                    {
                        data: [65, 25, 8, 2],
                        backgroundColor: [
                            '#3B82F6',
                            '#10B981',
                            '#F59E0B',
                            '#EF4444',
                        ],
                    },
                ],
            },
            specialites: {
                labels: [
                    'Mécanique Auto',
                    'Électricité Bâtiment',
                    'Informatique',
                    'Plomberie',
                    'Cuisine',
                ],
                datasets: [
                    {
                        label: 'Nombre de groupes',
                        data: [12, 10, 8, 6, 5],
                        backgroundColor: '#3B82F6',
                    },
                ],
            },
            espaces: {
                labels: [
                    'Pédagogiques',
                    'Administratifs',
                    'Communs',
                    'Entretien',
                ],
                datasets: [
                    {
                        data: [45, 25, 20, 10],
                        backgroundColor: [
                            '#3B82F6',
                            '#10B981',
                            '#F59E0B',
                            '#EF4444',
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

        const etatLieux = ref([
            { type: 'Ateliers', total: 15, bon_etat: 12 },
            { type: 'Salles Cours', total: 20, bon_etat: 18 },
            { type: 'Bureaux', total: 10, bon_etat: 8 },
            { type: 'Espaces Communs', total: 9, bon_etat: 7 },
        ]);

        const interventions = ref([
            { date: '2023-06-15', description: 'Rénovation atelier mécanique' },
            {
                date: '2023-04-10',
                description: 'Installation climatiseurs salles de cours',
            },
            { date: '2023-02-20', description: 'Contrôle électrique général' },
        ]);

        const alertes = ref([
            {
                date: '2023-08-01',
                type: 'Maintenance',
                message: 'Panne projecteur salle 203',
            },
            {
                date: '2023-07-25',
                type: 'Sécurité',
                message: 'Extincteur périmé atelier 2',
            },
            {
                date: '2023-07-10',
                type: 'Personnel',
                message: 'Formateur absent non remplacé',
            },
        ]);

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
                case 'Sécurité':
                    return 'danger';
                case 'Personnel':
                    return 'help';
                default:
                    return null;
            }
        };

        return {
            chartData,
            chartOptions,
            etatLieux,
            interventions,
            alertes,
            formatShortDate,
            getSeverity,
        };
    },
};
</script>

<style scoped>
.p-card {
    height: 100%;
}
</style>
