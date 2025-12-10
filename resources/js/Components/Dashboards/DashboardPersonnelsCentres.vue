<template>
    <div class="grid">
        <!-- Section supérieure - Statistiques globales -->
        <div class="col-12">
            <div class="grid">
                <!-- Carte Statistiques PersonnelsDirectionCentrale -->
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
                                            stats.personnels.titulaires
                                        }}
                                        titulaires</span
                                    >
                                    <span class="text-blue-500 font-medium"
                                        >+{{
                                            stats.personnels.contractuels
                                        }}
                                        contractuels</span
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

                <!-- Carte Statistiques StagiairesCentres -->
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

                <!-- Carte Groupes de Formation -->
                <div class="col-12 md:col-6 lg:col-3">
                    <Card class="h-full">
                        <template #title>Groupes</template>
                        <template #content>
                            <div
                                class="flex justify-content-between align-items-center"
                            >
                                <div>
                                    <div
                                        class="text-4xl font-bold text-primary"
                                    >
                                        {{ stats.groupes.total }}
                                    </div>
                                    <div class="text-500">Groupes actifs</div>
                                </div>
                                <div>
                                    <i
                                        class="pi pi-users text-4xl text-primary"
                                    ></i>
                                </div>
                            </div>
                            <Divider />
                            <div class="flex justify-content-between">
                                <span class="text-500"
                                    >En cours:
                                    {{ stats.groupes.en_cours }}</span
                                >
                                <span class="text-500"
                                    >Nouveaux:
                                    {{ stats.groupes.nouveaux }}</span
                                >
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Carte Taux de Remplissage -->
                <div class="col-12 md:col-6 lg:col-3">
                    <Card class="h-full">
                        <template #title>Capacité Centres</template>
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
                                    >Salles:
                                    {{ stats.capacite.salles_utilisees }}/{{
                                        stats.capacite.salles_totales
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
                <template #title>Activité du Centre</template>
                <template #subtitle>Dernières 12 semaines</template>
                <template #content>
                    <div class="grid">
                        <div class="col-12">
                            <Chart
                                type="line"
                                :data="chartData.activite"
                                :options="chartOptions"
                                height="250px"
                            />
                        </div>
                        <div class="col-12 md:col-6">
                            <div class="text-500">Prochaines Formations</div>
                            <DataTable
                                :value="upcomingFormations"
                                :rows="5"
                                :paginator="true"
                                responsiveLayout="scroll"
                            >
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
                <template #title>Résumé Centre</template>
                <template #content>
                    <div class="mb-4">
                        <h5>Spécialités enseignées</h5>
                        <Chart
                            type="doughnut"
                            :data="chartData.specialites"
                            :options="chartOptions"
                            height="200px"
                        />
                    </div>

                    <Divider />

                    <div class="mb-4">
                        <h5>Alertes récentes</h5>
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
                        <h5>Taux de réussite</h5>
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

        <!-- Section inférieure - Détails -->
        <div class="col-12">
            <Card>
                <template #title>Détails des Formations en Cours</template>
                <template #content>
                    <TabView>
                        <TabPanel header="Groupes Actifs">
                            <DataTable
                                :value="groupesActifs"
                                :paginator="true"
                                :rows="10"
                                responsiveLayout="scroll"
                            >
                                <Column
                                    field="nom"
                                    header="Groupe"
                                    sortable
                                ></Column>
                                <Column
                                    field="specialite"
                                    header="Spécialité"
                                    sortable
                                ></Column>
                                <Column field="mode" header="Mode" sortable>
                                    <template #body="{ data }">
                                        <Tag
                                            :value="data.mode"
                                            :severity="
                                                getModeSeverity(data.mode)
                                            "
                                        />
                                    </template>
                                </Column>
                                <Column
                                    field="date_debut"
                                    header="Début"
                                    sortable
                                >
                                    <template #body="{ data }">
                                        {{ formatDate(data.date_debut) }}
                                    </template>
                                </Column>
                                <Column field="date_fin" header="Fin" sortable>
                                    <template #body="{ data }">
                                        {{ formatDate(data.date_fin) }}
                                    </template>
                                </Column>
                                <Column field="stagiaires" header="Stagiaires">
                                    <template #body="{ data }">
                                        <ProgressBar
                                            :value="
                                                (data.stagiaires_actuels /
                                                    data.stagiaires_max) *
                                                100
                                            "
                                            :showValue="false"
                                        />
                                        <span
                                            >{{ data.stagiaires_actuels }}/{{
                                                data.stagiaires_max
                                            }}</span
                                        >
                                    </template>
                                </Column>
                                <Column header="Actions">
                                    <template #body>
                                        <Button
                                            icon="pi pi-eye"
                                            class="p-button-text p-button-rounded"
                                        />
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Formateurs">
                            <DataTable
                                :value="formateurs"
                                :paginator="true"
                                :rows="10"
                                responsiveLayout="scroll"
                            >
                                <Column
                                    field="nom"
                                    header="Nom"
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
                                    header="Groupes"
                                    sortable
                                >
                                    <template #body="{ data }">
                                        <span
                                            v-for="groupe in data.groupes"
                                            class="block"
                                            >{{ groupe }}</span
                                        >
                                    </template>
                                </Column>
                                <Column
                                    field="heures"
                                    header="Heures/Semaine"
                                    sortable
                                ></Column>
                                <Column header="Actions">
                                    <template #body>
                                        <Button
                                            icon="pi pi-eye"
                                            class="p-button-text p-button-rounded"
                                        />
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Infrastructure">
                            <div class="grid">
                                <div class="col-12 md:col-6">
                                    <h5>Occupation des Espaces</h5>
                                    <Chart
                                        type="bar"
                                        :data="chartData.espaces"
                                        :options="chartOptions"
                                        height="250px"
                                    />
                                </div>
                                <div class="col-12 md:col-6">
                                    <h5>État des Lieux</h5>
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
                                                        (data.bon_etat /
                                                            data.total) *
                                                        100
                                                    "
                                                    :showValue="false"
                                                />
                                                <span
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
                </template>
            </Card>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
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
    },
    setup() {
        const stats = ref({
            personnels: {
                total: 124,
                titulaires: 89,
                contractuels: 35,
                formateurs: 76,
                directeurs: 5,
            },
            stagiaires: {
                total: 842,
                residentiel: 512,
                alternance: 210,
                apprentissage: 95,
                f0: 25,
            },
            groupes: {
                total: 32,
                en_cours: 28,
                nouveaux: 4,
            },
            capacite: {
                taux: 78,
                occupees: 842,
                totales: 1080,
                salles_utilisees: 42,
                salles_totales: 54,
            },
            reussite: {
                global: 82,
                tendance: 3.5,
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
                            120, 135, 130, 145, 150, 160, 155, 170, 165, 180,
                            175, 190,
                        ],
                        fill: false,
                        borderColor: '#3B82F6',
                        tension: 0.4,
                    },
                    {
                        label: 'Heures Suivi',
                        data: [40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95],
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
                    'Autres',
                ],
                datasets: [
                    {
                        data: [35, 25, 20, 10, 5, 5],
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
            espaces: {
                labels: [
                    'Ateliers',
                    'Salles Cours',
                    'Bureaux',
                    'Espaces Communs',
                ],
                datasets: [
                    {
                        label: 'Occupation (%)',
                        data: [85, 90, 65, 45],
                        backgroundColor: '#3B82F6',
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

        const upcomingFormations = ref([
            {
                specialite: 'Mécanique Auto',
                date_debut: '2023-09-15',
                places: 25,
                inscrits: 22,
            },
            {
                specialite: 'Électricité Bâtiment',
                date_debut: '2023-10-01',
                places: 20,
                inscrits: 18,
            },
            {
                specialite: 'Développement Web',
                date_debut: '2023-10-15',
                places: 15,
                inscrits: 12,
            },
            {
                specialite: 'Plomberie',
                date_debut: '2023-11-01',
                places: 20,
                inscrits: 15,
            },
            {
                specialite: 'Comptabilité',
                date_debut: '2023-11-15',
                places: 15,
                inscrits: 10,
            },
        ]);

        const events = ref([
            {
                title: 'Réunion Pédagogique',
                date: '2023-09-10',
                description: 'Préparation rentrée scolaire',
            },
            {
                title: 'Journée Portes Ouvertes',
                date: '2023-09-25',
                description: 'Présentation des formations',
            },
            {
                title: 'Formation Sécurité',
                date: '2023-10-05',
                description: 'Formation sécurité pour formateurs',
            },
            {
                title: 'Conseil de Discipline',
                date: '2023-10-20',
                description: 'Examen des cas disciplinaires',
            },
        ]);

        const alertes = ref([
            {
                date: '2023-09-01',
                type: 'Maintenance',
                message: 'Atelier Mécanique nécessite réparation',
            },
            {
                date: '2023-09-02',
                type: 'Absentéisme',
                message: 'Taux absentéisme élevé groupe DEV-202',
            },
            {
                date: '2023-09-03',
                type: 'Ressources',
                message: 'Stock matières premières faible',
            },
            {
                date: '2023-09-04',
                type: 'Personnel',
                message: '2 formateurs en congé maladie',
            },
        ]);

        const groupesActifs = ref([
            {
                nom: 'MEC-201',
                specialite: 'Mécanique Auto',
                mode: 'Résidentiel',
                date_debut: '2023-01-15',
                date_fin: '2023-12-20',
                stagiaires_actuels: 24,
                stagiaires_max: 25,
            },
            {
                nom: 'ELEC-202',
                specialite: 'Électricité Bâtiment',
                mode: 'Alternance',
                date_debut: '2023-02-01',
                date_fin: '2024-01-30',
                stagiaires_actuels: 18,
                stagiaires_max: 20,
            },
            {
                nom: 'DEV-203',
                specialite: 'Développement Web',
                mode: 'Résidentiel',
                date_debut: '2023-03-15',
                date_fin: '2023-12-15',
                stagiaires_actuels: 14,
                stagiaires_max: 15,
            },
            {
                nom: 'PLOM-204',
                specialite: 'Plomberie',
                mode: 'Apprentissage',
                date_debut: '2023-04-01',
                date_fin: '2024-03-30',
                stagiaires_actuels: 10,
                stagiaires_max: 12,
            },
            {
                nom: 'COMPT-205',
                specialite: 'Comptabilité',
                mode: 'F0',
                date_debut: '2023-05-15',
                date_fin: '2024-05-14',
                stagiaires_actuels: 8,
                stagiaires_max: 10,
            },
        ]);

        const formateurs = ref([
            {
                nom: 'Mohamed Ali',
                specialites: ['Mécanique', 'Soudure'],
                groupes: ['MEC-201', 'MEC-202'],
                heures: 30,
            },
            {
                nom: 'Fatima Ben Salah',
                specialites: ['Électricité'],
                groupes: ['ELEC-202'],
                heures: 25,
            },
            {
                nom: 'Samir Khemiri',
                specialites: ['Informatique', 'Réseaux'],
                groupes: ['DEV-203', 'RES-204'],
                heures: 35,
            },
            {
                nom: 'Leila Trabelsi',
                specialites: ['Plomberie', 'Chauffage'],
                groupes: ['PLOM-204'],
                heures: 20,
            },
            {
                nom: 'Hichem Abbes',
                specialites: ['Comptabilité', 'Gestion'],
                groupes: ['COMPT-205'],
                heures: 18,
            },
        ]);

        const etatLieux = ref([
            { type: 'Ateliers', total: 15, bon_etat: 12 },
            { type: 'Salles Cours', total: 20, bon_etat: 18 },
            { type: 'Bureaux', total: 10, bon_etat: 8 },
            { type: 'Espaces Communs', total: 9, bon_etat: 7 },
        ]);

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

        const getModeSeverity = (mode) => {
            switch (mode) {
                case 'Résidentiel':
                    return 'success';
                case 'Alternance':
                    return 'info';
                case 'Apprentissage':
                    return 'warning';
                case 'F0':
                    return 'danger';
                default:
                    return null;
            }
        };

        onMounted(() => {
            // Ici vous pourriez faire des appels API pour récupérer les données réelles
        });

        return {
            stats,
            chartData,
            chartOptions,
            upcomingFormations,
            events,
            alertes,
            groupesActifs,
            formateurs,
            etatLieux,
            formatDate,
            formatShortDate,
            getSeverity,
            getModeSeverity,
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
</style>
