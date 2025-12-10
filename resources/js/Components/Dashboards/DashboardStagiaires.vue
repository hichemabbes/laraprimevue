<template>
    <div class="grid">
        <!-- Section supérieure - Statistiques globales -->
        <div class="col-12">
            <div class="grid">
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

                <!-- Carte Répartition par Spécialité -->
                <div class="col-12 md:col-6 lg:col-3">
                    <Card class="h-full">
                        <template #title>Spécialités</template>
                        <template #content>
                            <div
                                class="flex justify-content-between align-items-center"
                            >
                                <div>
                                    <div
                                        class="text-4xl font-bold text-primary"
                                    >
                                        {{ stats.specialites.total }}
                                    </div>
                                    <div class="text-500">
                                        Spécialités actives
                                    </div>
                                </div>
                                <div>
                                    <i
                                        class="pi pi-book text-4xl text-primary"
                                    ></i>
                                </div>
                            </div>
                            <Divider />
                            <div class="flex flex-column">
                                <span class="text-500"
                                    >Top 1: {{ stats.specialites.top1 }}</span
                                >
                                <span class="text-500"
                                    >Top 2: {{ stats.specialites.top2 }}</span
                                >
                                <span class="text-500"
                                    >Top 3: {{ stats.specialites.top3 }}</span
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
                                    >Groupes: {{ stats.groupes.actifs }}/{{
                                        stats.groupes.totaux
                                    }}</span
                                >
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Carte Taux de Réussite -->
                <div class="col-12 md:col-6 lg:col-3">
                    <Card class="h-full">
                        <template #title>Réussite</template>
                        <template #content>
                            <div
                                class="flex justify-content-between align-items-center"
                            >
                                <div>
                                    <div
                                        class="text-4xl font-bold text-primary"
                                    >
                                        {{ stats.reussite.global }}%
                                    </div>
                                    <div class="text-500">Taux global</div>
                                </div>
                                <div>
                                    <i
                                        class="pi pi-chart-line text-4xl"
                                        :class="
                                            stats.reussite.tendance >= 0
                                                ? 'text-green-500'
                                                : 'text-red-500'
                                        "
                                    ></i>
                                </div>
                            </div>
                            <Divider />
                            <div class="flex justify-content-between">
                                <span class="text-500"
                                    >Session précédente:
                                    {{ stats.reussite.precedent }}%</span
                                >
                                <span
                                    class="text-500"
                                    :class="
                                        stats.reussite.tendance >= 0
                                            ? 'text-green-500'
                                            : 'text-red-500'
                                    "
                                >
                                    {{ stats.reussite.tendance >= 0 ? '+' : ''
                                    }}{{ stats.reussite.tendance }}%
                                </span>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Section centrale - Graphiques et données clés -->
        <div class="col-12 lg:col-8">
            <Card>
                <template #title>Activité des Stagiaires</template>
                <template #subtitle>Dernières 12 semaines</template>
                <template #content>
                    <div class="grid">
                        <div class="col-12">
                            <TabView>
                                <TabPanel header="Présence">
                                    <Chart
                                        type="line"
                                        :data="chartData.presence"
                                        :options="chartOptions"
                                        height="250px"
                                    />
                                </TabPanel>
                                <TabPanel header="Performances">
                                    <Chart
                                        type="bar"
                                        :data="chartData.performances"
                                        :options="chartOptions"
                                        height="250px"
                                    />
                                </TabPanel>
                                <TabPanel header="Suivi Entreprise">
                                    <Chart
                                        type="line"
                                        :data="chartData.suivi"
                                        :options="chartOptions"
                                        height="250px"
                                    />
                                </TabPanel>
                            </TabView>
                        </div>
                        <div class="col-12 md:col-6">
                            <div class="text-500">Groupes à surveiller</div>
                            <DataTable
                                :value="groupesSurveillance"
                                :rows="5"
                                :paginator="true"
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
                                <Column
                                    field="taux_absence"
                                    header="Absence"
                                    sortable
                                >
                                    <template #body="{ data }">
                                        <ProgressBar
                                            :value="data.taux_absence"
                                            :showValue="false"
                                            :class="
                                                getAbsenceSeverity(
                                                    data.taux_absence
                                                )
                                            "
                                        />
                                        <span>{{ data.taux_absence }}%</span>
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
                        </div>
                        <div class="col-12 md:col-6">
                            <div class="text-500">Alertes récentes</div>
                            <Timeline
                                :value="alertes"
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
                                                :value="item.type"
                                                :severity="
                                                    getAlertSeverity(item.type)
                                                "
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

        <!-- Section droite - Résumé et actions -->
        <div class="col-12 lg:col-4">
            <Card>
                <template #title>Actions Rapides</template>
                <template #content>
                    <div class="grid">
                        <div class="col-6">
                            <Button
                                label="Nouveau Stagiaire"
                                icon="pi pi-user-plus"
                                class="p-button-sm w-full mb-3"
                                @click="showNewStagiaireDialog"
                            />
                        </div>
                        <div class="col-6">
                            <Button
                                label="Affecter Groupe"
                                icon="pi pi-users"
                                class="p-button-sm w-full mb-3 p-button-secondary"
                                @click="showAffectationDialog"
                            />
                        </div>
                        <div class="col-6">
                            <Button
                                label="Générer PV"
                                icon="pi pi-file-pdf"
                                class="p-button-sm w-full mb-3 p-button-help"
                                @click="showPVDialog"
                            />
                        </div>
                        <div class="col-6">
                            <Button
                                label="Générer Cartes"
                                icon="pi pi-id-card"
                                class="p-button-sm w-full mb-3 p-button-success"
                                @click="generateCartes"
                            />
                        </div>
                    </div>

                    <Divider />

                    <div class="mb-4">
                        <h5>Répartition par Mode</h5>
                        <Chart
                            type="doughnut"
                            :data="chartData.modes"
                            :options="chartOptions"
                            height="200px"
                        />
                    </div>

                    <Divider />

                    <div class="mb-4">
                        <h5>Stagiaires en difficulté</h5>
                        <DataTable
                            :value="stagiairesDifficulte"
                            :rows="5"
                            responsiveLayout="scroll"
                        >
                            <Column field="nom" header="Nom" sortable></Column>
                            <Column
                                field="groupe"
                                header="Groupe"
                                sortable
                            ></Column>
                            <Column field="modules" header="Modules" sortable>
                                <template #body="{ data }">
                                    <Tag
                                        :value="data.modules"
                                        :severity="
                                            getModuleSeverity(data.modules)
                                        "
                                    />
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
                    </div>
                </template>
            </Card>
        </div>

        <!-- Section inférieure - Détails -->
        <div class="col-12">
            <Card>
                <template #title>Détails des Stagiaires</template>
                <template #content>
                    <TabView>
                        <TabPanel header="Liste Complète">
                            <div class="flex justify-content-between mb-4">
                                <span class="p-input-icon-left">
                                    <i class="pi pi-search" />
                                    <InputText
                                        v-model="filters.global"
                                        placeholder="Rechercher..."
                                    />
                                </span>
                                <Dropdown
                                    v-model="selectedCentre"
                                    :options="centres"
                                    optionLabel="nom"
                                    placeholder="Tous les centres"
                                    class="w-10rem"
                                />
                            </div>
                            <DataTable
                                :value="filteredStagiaires"
                                :paginator="true"
                                :rows="10"
                                :filters="filters"
                                responsiveLayout="scroll"
                                :loading="loading"
                                stripedRows
                            >
                                <Column
                                    field="matricule"
                                    header="Matricule"
                                    sortable
                                ></Column>
                                <Column
                                    field="nom_complet"
                                    header="Nom Complet"
                                    sortable
                                ></Column>
                                <Column
                                    field="centre"
                                    header="Centre"
                                    sortable
                                ></Column>
                                <Column
                                    field="groupe"
                                    header="Groupe"
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
                                    field="specialite"
                                    header="Spécialité"
                                    sortable
                                ></Column>
                                <Column
                                    field="date_entree"
                                    header="Date Entrée"
                                    sortable
                                >
                                    <template #body="{ data }">
                                        {{ formatDate(data.date_entree) }}
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
                                <Column header="Actions">
                                    <template #body>
                                        <Button
                                            icon="pi pi-eye"
                                            class="p-button-text p-button-rounded p-button-info"
                                            @click="viewStagiaire(data)"
                                        />
                                        <Button
                                            icon="pi pi-pencil"
                                            class="p-button-text p-button-rounded p-button-warning"
                                            @click="editStagiaire(data)"
                                        />
                                        <Button
                                            icon="pi pi-file-pdf"
                                            class="p-button-text p-button-rounded p-button-danger"
                                            @click="generatePDF(data)"
                                        />
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Contrats & Documents">
                            <DataTable
                                :value="contrats"
                                :paginator="true"
                                :rows="10"
                                responsiveLayout="scroll"
                            >
                                <Column
                                    field="stagiaire"
                                    header="Stagiaire"
                                    sortable
                                ></Column>
                                <Column field="type" header="Type" sortable>
                                    <template #body="{ data }">
                                        <Tag
                                            :value="data.type"
                                            :severity="
                                                getContratSeverity(data.type)
                                            "
                                        />
                                    </template>
                                </Column>
                                <Column
                                    field="entreprise"
                                    header="Entreprise"
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
                                <Column field="date_fin" header="Fin" sortable>
                                    <template #body="{ data }">
                                        {{ formatDate(data.date_fin) }}
                                    </template>
                                </Column>
                                <Column field="statut" header="Statut" sortable>
                                    <template #body="{ data }">
                                        <Tag
                                            :value="data.statut"
                                            :severity="
                                                getContratStatutSeverity(
                                                    data.statut
                                                )
                                            "
                                        />
                                    </template>
                                </Column>
                                <Column header="Actions">
                                    <template #body>
                                        <Button
                                            icon="pi pi-eye"
                                            class="p-button-text p-button-rounded"
                                        />
                                        <Button
                                            icon="pi pi-download"
                                            class="p-button-text p-button-rounded p-button-success"
                                        />
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Évaluations">
                            <DataTable
                                :value="evaluations"
                                :paginator="true"
                                :rows="10"
                                responsiveLayout="scroll"
                            >
                                <Column
                                    field="stagiaire"
                                    header="Stagiaire"
                                    sortable
                                ></Column>
                                <Column
                                    field="module"
                                    header="Module"
                                    sortable
                                ></Column>
                                <Column
                                    field="note_principale"
                                    header="Principale"
                                    sortable
                                >
                                    <template #body="{ data }">
                                        <Tag
                                            :value="data.note_principale"
                                            :severity="
                                                getNoteSeverity(
                                                    data.note_principale
                                                )
                                            "
                                        />
                                    </template>
                                </Column>
                                <Column
                                    field="note_reprise1"
                                    header="Reprise 1"
                                    sortable
                                >
                                    <template #body="{ data }">
                                        <Tag
                                            :value="data.note_reprise1"
                                            :severity="
                                                getNoteSeverity(
                                                    data.note_reprise1
                                                )
                                            "
                                        />
                                    </template>
                                </Column>
                                <Column
                                    field="note_reprise2"
                                    header="Reprise 2"
                                    sortable
                                >
                                    <template #body="{ data }">
                                        <Tag
                                            :value="data.note_reprise2"
                                            :severity="
                                                getNoteSeverity(
                                                    data.note_reprise2
                                                )
                                            "
                                        />
                                    </template>
                                </Column>
                                <Column
                                    field="moyenne"
                                    header="Moyenne"
                                    sortable
                                >
                                    <template #body="{ data }">
                                        <Tag
                                            :value="data.moyenne"
                                            :severity="
                                                getNoteSeverity(data.moyenne)
                                            "
                                        />
                                    </template>
                                </Column>
                                <Column header="Actions">
                                    <template #body>
                                        <Button
                                            icon="pi pi-eye"
                                            class="p-button-text p-button-rounded"
                                        />
                                        <Button
                                            icon="pi pi-pencil"
                                            class="p-button-text p-button-rounded p-button-warning"
                                        />
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>
                    </TabView>
                </template>
            </Card>
        </div>

        <!-- Dialogues -->
        <Dialog
            v-model:visible="newStagiaireDialog"
            header="Nouveau Stagiaire"
            :modal="true"
            class="w-9"
        >
            <div class="grid">
                <div class="col-12 md:col-6">
                    <div class="field">
                        <label for="nom">Nom</label>
                        <InputText
                            id="nom"
                            v-model="newStagiaire.nom"
                            class="w-full"
                        />
                    </div>
                </div>
                <div class="col-12 md:col-6">
                    <div class="field">
                        <label for="prenom">Prénom</label>
                        <InputText
                            id="prenom"
                            v-model="newStagiaire.prenom"
                            class="w-full"
                        />
                    </div>
                </div>
                <div class="col-12 md:col-6">
                    <div class="field">
                        <label for="cin">CIN</label>
                        <InputText
                            id="cin"
                            v-model="newStagiaire.cin"
                            class="w-full"
                        />
                    </div>
                </div>
                <div class="col-12 md:col-6">
                    <div class="field">
                        <label for="date_naissance">Date Naissance</label>
                        <Calendar
                            id="date_naissance"
                            v-model="newStagiaire.date_naissance"
                            dateFormat="dd/mm/yy"
                            class="w-full"
                        />
                    </div>
                </div>
                <div class="col-12 md:col-6">
                    <div class="field">
                        <label for="centre">Centre</label>
                        <Dropdown
                            id="centre"
                            v-model="newStagiaire.centre"
                            :options="centres"
                            optionLabel="nom"
                            class="w-full"
                        />
                    </div>
                </div>
                <div class="col-12 md:col-6">
                    <div class="field">
                        <label for="mode">Mode Formation</label>
                        <Dropdown
                            id="mode"
                            v-model="newStagiaire.mode"
                            :options="modesFormation"
                            optionLabel="label"
                            optionValue="value"
                            class="w-full"
                        />
                    </div>
                </div>
                <div class="col-12">
                    <Divider />
                    <div class="flex justify-content-end">
                        <Button
                            label="Annuler"
                            icon="pi pi-times"
                            class="p-button-text"
                            @click="newStagiaireDialog = false"
                        />
                        <Button
                            label="Enregistrer"
                            icon="pi pi-check"
                            class="p-button-primary"
                            @click="saveStagiaire"
                        />
                    </div>
                </div>
            </div>
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
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import { FilterMatchMode } from 'primevue/api';

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
        Button,
        Dialog,
        InputText,
        Dropdown,
        Calendar,
    },
    setup() {
        const stats = ref({
            stagiaires: {
                total: 1842,
                residentiel: 1251,
                alternance: 410,
                apprentissage: 145,
                f0: 36,
            },
            specialites: {
                total: 24,
                top1: 'Mécanique Auto (320)',
                top2: 'Électricité Bâtiment (280)',
                top3: 'Développement Web (240)',
            },
            capacite: {
                taux: 78,
                occupees: 1842,
                totales: 2360,
                salles_utilisees: 142,
                salles_totales: 154,
            },
            groupes: {
                actifs: 62,
                totaux: 80,
            },
            reussite: {
                global: 82,
                precedent: 79,
                tendance: 3.8,
            },
        });

        const chartData = ref({
            presence: {
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
                        label: 'Taux de Présence (%)',
                        data: [95, 93, 94, 92, 91, 90, 89, 88, 90, 91, 92, 93],
                        fill: false,
                        borderColor: '#3B82F6',
                        tension: 0.4,
                    },
                    {
                        label: "Taux d'Absence (%)",
                        data: [5, 7, 6, 8, 9, 10, 11, 12, 10, 9, 8, 7],
                        fill: false,
                        borderColor: '#EF4444',
                        tension: 0.4,
                    },
                ],
            },
            performances: {
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
                        label: 'Moyenne des Notes',
                        data: [14.5, 13.8, 12.9, 13.2, 14.1, 12.5],
                        backgroundColor: '#3B82F6',
                    },
                    {
                        label: 'Taux de Réussite (%)',
                        data: [85, 82, 78, 80, 83, 75],
                        backgroundColor: '#10B981',
                    },
                ],
            },
            suivi: {
                labels: ['M1', 'M2', 'M3', 'M4', 'M5', 'M6'],
                datasets: [
                    {
                        label: 'Visites Planifiées',
                        data: [120, 135, 130, 145, 150, 160],
                        fill: false,
                        borderColor: '#3B82F6',
                        tension: 0.4,
                    },
                    {
                        label: 'Visites Réalisées',
                        data: [110, 125, 120, 135, 140, 150],
                        fill: false,
                        borderColor: '#10B981',
                        tension: 0.4,
                    },
                    {
                        label: 'Rapports Soumis',
                        data: [105, 120, 115, 130, 135, 145],
                        fill: false,
                        borderColor: '#F59E0B',
                        tension: 0.4,
                    },
                ],
            },
            modes: {
                labels: ['Résidentiel', 'Alternance', 'Apprentissage', 'F0'],
                datasets: [
                    {
                        data: [1251, 410, 145, 36],
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
                    max: 100,
                },
            },
            maintainAspectRatio: false,
        });

        const groupesSurveillance = ref([
            { nom: 'MEC-205', specialite: 'Mécanique Auto', taux_absence: 18 },
            {
                nom: 'DEV-203',
                specialite: 'Développement Web',
                taux_absence: 15,
            },
            {
                nom: 'ELEC-207',
                specialite: 'Électricité Bâtiment',
                taux_absence: 12,
            },
            { nom: 'PLOM-202', specialite: 'Plomberie', taux_absence: 22 },
            { nom: 'COMPT-201', specialite: 'Comptabilité', taux_absence: 9 },
        ]);

        const alertes = ref([
            {
                title: 'Absentéisme élevé',
                date: '2023-09-01',
                description: "Taux d'absentéisme > 20% dans le groupe MEC-205",
                type: 'Absentéisme',
            },
            {
                title: 'Contrat expiré',
                date: '2023-09-02',
                description:
                    "5 contrats d'apprentissage expirent cette semaine",
                type: 'Contrat',
            },
            {
                title: 'Suivi en retard',
                date: '2023-09-03',
                description: '15 visites de suivi non réalisées',
                type: 'Suivi',
            },
            {
                title: 'Évaluation manquante',
                date: '2023-09-04',
                description: 'Notes manquantes pour 3 modules dans ELEC-207',
                type: 'Évaluation',
            },
        ]);

        const stagiairesDifficulte = ref([
            { nom: 'Mohamed Ben Ali', groupe: 'MEC-205', modules: 3 },
            { nom: 'Fatima Khemiri', groupe: 'DEV-203', modules: 2 },
            { nom: 'Samir Trabelsi', groupe: 'ELEC-207', modules: 4 },
            { nom: 'Leila Abbes', groupe: 'PLOM-202', modules: 1 },
            { nom: 'Hichem Ben Salah', groupe: 'COMPT-201', modules: 2 },
        ]);

        const stagiaires = ref([
            {
                matricule: 'STG-2023-001',
                nom_complet: 'Mohamed Ben Ali',
                centre: 'Centre Tunis',
                groupe: 'MEC-205',
                mode: 'Résidentiel',
                specialite: 'Mécanique Auto',
                date_entree: '2023-01-15',
                statut: 'Actif',
            },
            {
                matricule: 'STG-2023-002',
                nom_complet: 'Fatima Khemiri',
                centre: 'Centre Sousse',
                groupe: 'DEV-203',
                mode: 'Alternance',
                specialite: 'Développement Web',
                date_entree: '2023-02-01',
                statut: 'Actif',
            },
            {
                matricule: 'STG-2023-003',
                nom_complet: 'Samir Trabelsi',
                centre: 'Centre Sfax',
                groupe: 'ELEC-207',
                mode: 'Apprentissage',
                specialite: 'Électricité Bâtiment',
                date_entree: '2023-03-15',
                statut: 'Actif',
            },
            {
                matricule: 'STG-2023-004',
                nom_complet: 'Leila Abbes',
                centre: 'Centre Nabeul',
                groupe: 'PLOM-202',
                mode: 'F0',
                specialite: 'Plomberie',
                date_entree: '2023-04-01',
                statut: 'Actif',
            },
            {
                matricule: 'STG-2023-005',
                nom_complet: 'Hichem Ben Salah',
                centre: 'Centre Bizerte',
                groupe: 'COMPT-201',
                mode: 'Résidentiel',
                specialite: 'Comptabilité',
                date_entree: '2023-05-15',
                statut: 'Inactif',
            },
            {
                matricule: 'STG-2023-006',
                nom_complet: 'Amina Boukari',
                centre: 'Centre Tunis',
                groupe: 'MEC-205',
                mode: 'Résidentiel',
                specialite: 'Mécanique Auto',
                date_entree: '2023-01-15',
                statut: 'Actif',
            },
            {
                matricule: 'STG-2023-007',
                nom_complet: 'Karim Ben Amor',
                centre: 'Centre Sousse',
                groupe: 'DEV-203',
                mode: 'Alternance',
                specialite: 'Développement Web',
                date_entree: '2023-02-01',
                statut: 'Actif',
            },
            {
                matricule: 'STG-2023-008',
                nom_complet: 'Salma Trabelsi',
                centre: 'Centre Sfax',
                groupe: 'ELEC-207',
                mode: 'Apprentissage',
                specialite: 'Électricité Bâtiment',
                date_entree: '2023-03-15',
                statut: 'Actif',
            },
            {
                matricule: 'STG-2023-009',
                nom_complet: 'Youssef Ksouri',
                centre: 'Centre Nabeul',
                groupe: 'PLOM-202',
                mode: 'F0',
                specialite: 'Plomberie',
                date_entree: '2023-04-01',
                statut: 'Inactif',
            },
            {
                matricule: 'STG-2023-010',
                nom_complet: 'Nadia Ben Youssef',
                centre: 'Centre Bizerte',
                groupe: 'COMPT-201',
                mode: 'Résidentiel',
                specialite: 'Comptabilité',
                date_entree: '2023-05-15',
                statut: 'Actif',
            },
        ]);

        const contrats = ref([
            {
                stagiaire: 'Mohamed Ben Ali',
                type: 'Apprentissage',
                entreprise: 'STEG',
                date_debut: '2023-01-15',
                date_fin: '2024-01-14',
                statut: 'Actif',
            },
            {
                stagiaire: 'Fatima Khemiri',
                type: 'Stage',
                entreprise: 'SOFTECH',
                date_debut: '2023-06-01',
                date_fin: '2023-08-31',
                statut: 'Terminé',
            },
            {
                stagiaire: 'Samir Trabelsi',
                type: 'Apprentissage',
                entreprise: 'ENNOUR',
                date_debut: '2023-03-15',
                date_fin: '2024-03-14',
                statut: 'Actif',
            },
            {
                stagiaire: 'Leila Abbes',
                type: 'Apprentissage',
                entreprise: 'SOPAL',
                date_debut: '2023-04-01',
                date_fin: '2024-03-31',
                statut: 'Résilié',
            },
            {
                stagiaire: 'Hichem Ben Salah',
                type: 'Stage',
                entreprise: 'BIAT',
                date_debut: '2023-05-15',
                date_fin: '2023-07-15',
                statut: 'Terminé',
            },
        ]);

        const evaluations = ref([
            {
                stagiaire: 'Mohamed Ben Ali',
                module: 'Mécanique Moteur',
                note_principale: 14,
                note_reprise1: 12,
                note_reprise2: 15,
                moyenne: 14,
            },
            {
                stagiaire: 'Fatima Khemiri',
                module: 'Programmation Web',
                note_principale: 16,
                note_reprise1: null,
                note_reprise2: null,
                moyenne: 16,
            },
            {
                stagiaire: 'Samir Trabelsi',
                module: 'Installations Électriques',
                note_principale: 11,
                note_reprise1: 13,
                note_reprise2: null,
                moyenne: 12,
            },
            {
                stagiaire: 'Leila Abbes',
                module: 'Plomberie Sanitaire',
                note_principale: 9,
                note_reprise1: 10,
                note_reprise2: 8,
                moyenne: 9,
            },
            {
                stagiaire: 'Hichem Ben Salah',
                module: 'Comptabilité Générale',
                note_principale: 15,
                note_reprise1: null,
                note_reprise2: null,
                moyenne: 15,
            },
        ]);

        const centres = ref([
            { id: 1, nom: 'Centre Tunis' },
            { id: 2, nom: 'Centre Sousse' },
            { id: 3, nom: 'Centre Sfax' },
            { id: 4, nom: 'Centre Nabeul' },
            { id: 5, nom: 'Centre Bizerte' },
        ]);

        const modesFormation = ref([
            { label: 'Résidentiel', value: 'residentiel' },
            { label: 'Alternance', value: 'alternance' },
            { label: 'Apprentissage', value: 'apprentissage' },
            { label: 'F0', value: 'f0' },
        ]);

        const filters = ref({
            global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        });

        const selectedCentre = ref(null);
        const loading = ref(false);
        const newStagiaireDialog = ref(false);
        const newStagiaire = ref({
            nom: '',
            prenom: '',
            cin: '',
            date_naissance: null,
            centre: null,
            mode: null,
        });

        const formatDate = (dateString) => {
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('fr-FR', options);
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

        const getStatutSeverity = (statut) => {
            switch (statut) {
                case 'Actif':
                    return 'success';
                case 'Inactif':
                    return 'warning';
                case 'Abandon':
                    return 'danger';
                case 'Diplômé':
                    return 'info';
                default:
                    return null;
            }
        };

        const getAlertSeverity = (type) => {
            switch (type) {
                case 'Absentéisme':
                    return 'danger';
                case 'Contrat':
                    return 'warning';
                case 'Suivi':
                    return 'info';
                case 'Évaluation':
                    return 'help';
                default:
                    return null;
            }
        };

        const getAbsenceSeverity = (taux) => {
            if (taux < 10) return 'bg-green-500';
            if (taux < 20) return 'bg-yellow-500';
            if (taux < 30) return 'bg-orange-500';
            return 'bg-red-500';
        };

        const getModuleSeverity = (count) => {
            if (count < 2) return 'success';
            if (count < 4) return 'warning';
            return 'danger';
        };

        const getContratSeverity = (type) => {
            switch (type) {
                case 'Apprentissage':
                    return 'info';
                case 'Stage':
                    return 'help';
                default:
                    return null;
            }
        };

        const getContratStatutSeverity = (statut) => {
            switch (statut) {
                case 'Actif':
                    return 'success';
                case 'Terminé':
                    return 'info';
                case 'Résilié':
                    return 'danger';
                default:
                    return null;
            }
        };

        const getNoteSeverity = (note) => {
            if (!note) return null;
            if (note >= 16) return 'success';
            if (note >= 13) return 'info';
            if (note >= 10) return 'warning';
            return 'danger';
        };

        const filteredStagiaires = computed(() => {
            if (!selectedCentre.value) return stagiaires.value;
            return stagiaires.value.filter(
                (s) => s.centre === selectedCentre.value.nom
            );
        });

        const showNewStagiaireDialog = () => {
            newStagiaireDialog.value = true;
        };

        const showAffectationDialog = () => {
            // Implémentation à ajouter
        };

        const showPVDialog = () => {
            // Implémentation à ajouter
        };

        const generateCartes = () => {
            // Implémentation à ajouter
        };

        const saveStagiaire = () => {
            // Implémentation à ajouter
            newStagiaireDialog.value = false;
        };

        const viewStagiaire = (data) => {
            // Implémentation à ajouter
        };

        const editStagiaire = (data) => {
            // Implémentation à ajouter
        };

        const generatePDF = (data) => {
            // Implémentation à ajouter
        };

        onMounted(() => {
            // Ici vous pourriez faire des appels API pour récupérer les données réelles
        });

        return {
            stats,
            chartData,
            chartOptions,
            groupesSurveillance,
            alertes,
            stagiairesDifficulte,
            stagiaires,
            contrats,
            evaluations,
            centres,
            modesFormation,
            filters,
            selectedCentre,
            loading,
            newStagiaireDialog,
            newStagiaire,
            filteredStagiaires,
            formatDate,
            getModeSeverity,
            getStatutSeverity,
            getAlertSeverity,
            getAbsenceSeverity,
            getModuleSeverity,
            getContratSeverity,
            getContratStatutSeverity,
            getNoteSeverity,
            showNewStagiaireDialog,
            showAffectationDialog,
            showPVDialog,
            generateCartes,
            saveStagiaire,
            viewStagiaire,
            editStagiaire,
            generatePDF,
        };
    },
};
</script>

<style scoped>
.grid {
    margin-top: -1.4rem;
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

.field {
    margin-bottom: 1.5rem;
}

.w-10rem {
    width: 10rem;
}
</style>
