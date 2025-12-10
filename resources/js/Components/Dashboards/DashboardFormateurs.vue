<template>
    <div class="grid">
        <!-- Section supérieure - Statistiques personnelles -->
        <div class="col-12">
            <div class="grid">
                <!-- Carte Heures d'enseignement -->
                <div class="col-12 md:col-6 lg:col-3">
                    <Card class="h-full">
                        <template #title>Heures d'enseignement</template>
                        <template #content>
                            <div
                                class="flex justify-content-between align-items-center"
                            >
                                <div>
                                    <div
                                        class="text-4xl font-bold text-primary"
                                    >
                                        {{ stats.heures.mensuelles }}
                                    </div>
                                    <div class="text-500">Ce mois</div>
                                </div>
                                <div>
                                    <Knob
                                        v-model="stats.heures.remplissage"
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
                                    >Annuelle:
                                    {{ stats.heures.annuelles }}</span
                                >
                                <span class="text-500"
                                    >Sup:
                                    {{ stats.heures.supplementaires }}</span
                                >
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Carte Groupes encadrés -->
                <div class="col-12 md:col-6 lg:col-3">
                    <Card class="h-full">
                        <template #title>Groupes encadrés</template>
                        <template #content>
                            <div
                                class="flex justify-content-between align-items-center"
                            >
                                <div>
                                    <div
                                        class="text-4xl font-bold text-primary"
                                    >
                                        {{ stats.groupes.actifs }}
                                    </div>
                                    <div class="text-500">Actuellement</div>
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
                                    >Total: {{ stats.groupes.totaux }}</span
                                >
                                <span class="text-500"
                                    >Stagiaires:
                                    {{ stats.groupes.stagiaires }}</span
                                >
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Carte Modules enseignés -->
                <div class="col-12 md:col-6 lg:col-3">
                    <Card class="h-full">
                        <template #title>Modules enseignés</template>
                        <template #content>
                            <div
                                class="flex justify-content-between align-items-center"
                            >
                                <div>
                                    <div
                                        class="text-4xl font-bold text-primary"
                                    >
                                        {{ stats.modules.encours }}
                                    </div>
                                    <div class="text-500">En cours</div>
                                </div>
                                <div>
                                    <i
                                        class="pi pi-book text-4xl text-primary"
                                    ></i>
                                </div>
                            </div>
                            <Divider />
                            <div class="flex justify-content-between">
                                <span class="text-500"
                                    >Terminés:
                                    {{ stats.modules.termines }}</span
                                >
                                <span class="text-500"
                                    >Total: {{ stats.modules.totaux }}</span
                                >
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Carte Suivi pédagogique -->
                <div class="col-12 md:col-6 lg:col-3">
                    <Card class="h-full">
                        <template #title>Suivi pédagogique</template>
                        <template #content>
                            <div
                                class="flex justify-content-between align-items-center"
                            >
                                <div>
                                    <div
                                        class="text-4xl font-bold text-primary"
                                    >
                                        {{ stats.suivi.visites }}
                                    </div>
                                    <div class="text-500">Visites ce mois</div>
                                </div>
                                <div>
                                    <i
                                        class="pi pi-map-marker text-4xl text-primary"
                                    ></i>
                                </div>
                            </div>
                            <Divider />
                            <div class="flex justify-content-between">
                                <span class="text-500"
                                    >Entreprises:
                                    {{ stats.suivi.entreprises }}</span
                                >
                                <span class="text-500"
                                    >Stagiaires:
                                    {{ stats.suivi.stagiaires }}</span
                                >
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Section centrale - Emploi du temps et activités -->
        <div class="col-12 lg:col-8">
            <Card>
                <template #title>Emploi du temps</template>
                <template #subtitle>Semaine du {{ currentWeek }}</template>
                <template #content>
                    <div class="grid">
                        <div class="col-12">
                            <FullCalendar :options="calendarOptions" />
                        </div>
                        <div class="col-12 md:col-6">
                            <div class="text-500">Prochaines séances</div>
                            <DataTable
                                :value="upcomingSessions"
                                :rows="5"
                                :paginator="true"
                                responsiveLayout="scroll"
                            >
                                <Column field="date" header="Date" sortable>
                                    <template #body="{ data }">
                                        {{ formatDate(data.date) }}
                                    </template>
                                </Column>
                                <Column
                                    field="groupe"
                                    header="Groupe"
                                    sortable
                                ></Column>
                                <Column
                                    field="module"
                                    header="Module"
                                    sortable
                                ></Column>
                                <Column
                                    field="lieu"
                                    header="Lieu"
                                    sortable
                                ></Column>
                            </DataTable>
                        </div>
                        <div class="col-12 md:col-6">
                            <div class="text-500">
                                Prochaines visites en entreprise
                            </div>
                            <Timeline
                                :value="upcomingVisits"
                                align="alternate"
                                class="mt-3"
                            >
                                <template #content="{ item }">
                                    <Card class="mb-3">
                                        <template #title>{{
                                            item.entreprise
                                        }}</template>
                                        <template #subtitle
                                            >{{ formatDate(item.date) }} -
                                            {{ item.heure }}</template
                                        >
                                        <template #content>
                                            <p class="m-0">
                                                Stagiaires:
                                                {{ item.stagiaires.join(', ') }}
                                            </p>
                                            <p class="m-0 mt-2">
                                                Objectif: {{ item.objectif }}
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
                <template #title>Mes Spécialités</template>
                <template #content>
                    <div class="mb-4">
                        <Chart
                            type="doughnut"
                            :data="chartData.specialites"
                            :options="chartOptions"
                            height="200px"
                        />
                    </div>

                    <Divider />

                    <div class="mb-4">
                        <h5>Alertes et rappels</h5>
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
                        <h5>Évaluations à corriger</h5>
                        <DataTable
                            :value="evaluations"
                            :rows="3"
                            responsiveLayout="scroll"
                        >
                            <Column field="module" header="Module"></Column>
                            <Column field="groupe" header="Groupe"></Column>
                            <Column field="date" header="Date limite">
                                <template #body="{ data }">
                                    {{ formatShortDate(data.date) }}
                                </template>
                            </Column>
                            <Column header="Action">
                                <template #body>
                                    <Button
                                        icon="pi pi-pencil"
                                        class="p-button-text p-button-rounded p-button-sm"
                                    />
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Section inférieure - Détails des groupes et modules -->
        <div class="col-12">
            <Card>
                <template #title>Mes Groupes et Modules</template>
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
                                        <Button
                                            icon="pi pi-list"
                                            class="p-button-text p-button-rounded"
                                        />
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Modules en Cours">
                            <DataTable
                                :value="modulesEncours"
                                :paginator="true"
                                :rows="10"
                                responsiveLayout="scroll"
                            >
                                <Column
                                    field="nom"
                                    header="Module"
                                    sortable
                                ></Column>
                                <Column
                                    field="groupe"
                                    header="Groupe"
                                    sortable
                                ></Column>
                                <Column
                                    field="heures_prevues"
                                    header="Heures"
                                    sortable
                                >
                                    <template #body="{ data }">
                                        {{ data.heures_realisees }}/{{
                                            data.heures_prevues
                                        }}
                                    </template>
                                </Column>
                                <Column
                                    field="progression"
                                    header="Progression"
                                    sortable
                                >
                                    <template #body="{ data }">
                                        <ProgressBar
                                            :value="
                                                (data.heures_realisees /
                                                    data.heures_prevues) *
                                                100
                                            "
                                        />
                                    </template>
                                </Column>
                                <Column
                                    field="date_fin"
                                    header="Fin prévue"
                                    sortable
                                >
                                    <template #body="{ data }">
                                        {{ formatDate(data.date_fin) }}
                                    </template>
                                </Column>
                                <Column header="Actions">
                                    <template #body>
                                        <Button
                                            icon="pi pi-file-edit"
                                            class="p-button-text p-button-rounded"
                                        />
                                        <Button
                                            icon="pi pi-chart-bar"
                                            class="p-button-text p-button-rounded"
                                        />
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Suivi Pédagogique">
                            <div class="grid">
                                <div class="col-12 md:col-6">
                                    <h5>Dernières visites</h5>
                                    <DataTable
                                        :value="dernieresVisites"
                                        :rows="5"
                                        responsiveLayout="scroll"
                                    >
                                        <Column
                                            field="date"
                                            header="Date"
                                            sortable
                                        >
                                            <template #body="{ data }">
                                                {{ formatShortDate(data.date) }}
                                            </template>
                                        </Column>
                                        <Column
                                            field="entreprise"
                                            header="Entreprise"
                                            sortable
                                        ></Column>
                                        <Column
                                            field="stagiaires"
                                            header="Stagiaires"
                                        >
                                            <template #body="{ data }">
                                                {{ data.stagiaires.length }}
                                            </template>
                                        </Column>
                                        <Column header="Rapport">
                                            <template #body>
                                                <Button
                                                    icon="pi pi-download"
                                                    class="p-button-text p-button-rounded p-button-sm"
                                                />
                                            </template>
                                        </Column>
                                    </DataTable>
                                </div>
                                <div class="col-12 md:col-6">
                                    <h5>Prochaines visites programmées</h5>
                                    <Chart
                                        type="bar"
                                        :data="chartData.visites"
                                        :options="chartOptions"
                                        height="250px"
                                    />
                                </div>
                            </div>
                        </TabPanel>
                    </TabView>
                </template>
            </Card>
        </div>

        <!-- Section Matériel pédagogique -->
        <div class="col-12">
            <Card>
                <template #title>Demandes de Matériel Pédagogique</template>
                <template #content>
                    <div class="grid">
                        <div class="col-12 md:col-6">
                            <h5>Demandes en cours</h5>
                            <DataTable
                                :value="demandesMateriel"
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
                                    field="module"
                                    header="Module"
                                    sortable
                                ></Column>
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
                                <Column field="articles" header="Articles">
                                    <template #body="{ data }">
                                        {{ data.articles.length }}
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
                            <h5>Historique des approvisionnements</h5>
                            <DataTable
                                :value="historiqueMateriel"
                                :paginator="true"
                                :rows="5"
                                responsiveLayout="scroll"
                            >
                                <Column
                                    field="date_reception"
                                    header="Date"
                                    sortable
                                >
                                    <template #body="{ data }">
                                        {{ formatDate(data.date_reception) }}
                                    </template>
                                </Column>
                                <Column
                                    field="module"
                                    header="Module"
                                    sortable
                                ></Column>
                                <Column
                                    field="fournisseur"
                                    header="Fournisseur"
                                    sortable
                                ></Column>
                                <Column field="articles" header="Articles">
                                    <template #body="{ data }">
                                        {{ data.articles.length }}
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
                    </div>
                    <Divider />
                    <div class="flex justify-content-end">
                        <Button
                            label="Nouvelle Demande"
                            icon="pi pi-plus"
                            class="p-button-primary"
                            @click="openDemandeDialog"
                        />
                    </div>
                </template>
            </Card>
        </div>
    </div>

    <!-- Dialog pour nouvelle demande de matériel -->
    <Dialog
        v-model:visible="showDemandeDialog"
        header="Nouvelle Demande de Matériel"
        :modal="true"
        :style="{ width: '50vw' }"
    >
        <div class="p-fluid">
            <div class="field">
                <label for="module">Module concerné</label>
                <Dropdown
                    id="module"
                    v-model="newDemande.module"
                    :options="modulesOptions"
                    optionLabel="nom"
                    placeholder="Sélectionnez un module"
                />
            </div>
            <div class="field">
                <label for="groupe">Groupe concerné</label>
                <Dropdown
                    id="groupe"
                    v-model="newDemande.groupe"
                    :options="groupesOptions"
                    optionLabel="nom"
                    placeholder="Sélectionnez un groupe"
                />
            </div>

            <h5>Articles demandés</h5>
            <DataTable :value="newDemande.articles" :rows="5" class="mb-3">
                <Column field="designation" header="Désignation"></Column>
                <Column field="quantite" header="Quantité"></Column>
                <Column field="justification" header="Justification"></Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <Button
                            icon="pi pi-trash"
                            class="p-button-text p-button-rounded p-button-danger"
                            @click="removeArticle(slotProps.index)"
                        />
                    </template>
                </Column>
            </DataTable>

            <div class="grid">
                <div class="col-12 md:col-4">
                    <InputText
                        v-model="article.designation"
                        placeholder="Désignation"
                        class="w-full"
                    />
                </div>
                <div class="col-12 md:col-2">
                    <InputNumber
                        v-model="article.quantite"
                        placeholder="Quantité"
                        class="w-full"
                    />
                </div>
                <div class="col-12 md:col-4">
                    <InputText
                        v-model="article.justification"
                        placeholder="Justification"
                        class="w-full"
                    />
                </div>
                <div class="col-12 md:col-2">
                    <Button
                        icon="pi pi-plus"
                        class="p-button-primary w-full"
                        @click="addArticle"
                    />
                </div>
            </div>
        </div>
        <template #footer>
            <Button
                label="Annuler"
                icon="pi pi-times"
                class="p-button-text"
                @click="showDemandeDialog = false"
            />
            <Button
                label="Soumettre"
                icon="pi pi-check"
                class="p-button-primary"
                @click="submitDemande"
            />
        </template>
    </Dialog>
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
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

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
        Dropdown,
        InputText,
        InputNumber,
        FullCalendar,
    },
    setup() {
        const stats = ref({
            heures: {
                mensuelles: 84,
                annuelles: 756,
                supplementaires: 12,
                remplissage: 75,
            },
            groupes: {
                actifs: 4,
                totaux: 8,
                stagiaires: 72,
            },
            modules: {
                encours: 6,
                termines: 12,
                totaux: 18,
            },
            suivi: {
                visites: 8,
                entreprises: 15,
                stagiaires: 32,
            },
        });

        const chartData = ref({
            specialites: {
                labels: ['Mécanique Auto', 'Électricité', 'Soudure', 'Autres'],
                datasets: [
                    {
                        data: [45, 30, 15, 10],
                        backgroundColor: [
                            '#3B82F6',
                            '#10B981',
                            '#F59E0B',
                            '#64748B',
                        ],
                    },
                ],
            },
            visites: {
                labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'],
                datasets: [
                    {
                        label: 'Visites programmées',
                        data: [3, 2, 4, 3],
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
            maintainAspectRatio: false,
        });

        const currentWeek = ref('12/09/2023 - 18/09/2023');

        const calendarOptions = ref({
            plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay',
            },
            events: [
                {
                    title: 'MEC-201 - ModuleGeneral 1',
                    start: '2023-09-12T08:00:00',
                    end: '2023-09-12T12:00:00',
                    backgroundColor: '#3B82F6',
                    borderColor: '#3B82F6',
                },
                {
                    title: 'ELEC-202 - ModuleGeneral 3',
                    start: '2023-09-12T13:00:00',
                    end: '2023-09-12T17:00:00',
                    backgroundColor: '#10B981',
                    borderColor: '#10B981',
                },
                {
                    title: 'Visite Entreprise - STG Motors',
                    start: '2023-09-13T09:00:00',
                    end: '2023-09-13T12:00:00',
                    backgroundColor: '#F59E0B',
                    borderColor: '#F59E0B',
                },
                {
                    title: 'MEC-201 - ModuleGeneral 1',
                    start: '2023-09-14T08:00:00',
                    end: '2023-09-14T12:00:00',
                    backgroundColor: '#3B82F6',
                    borderColor: '#3B82F6',
                },
                {
                    title: 'Réunion Pédagogique',
                    start: '2023-09-14T14:00:00',
                    end: '2023-09-14T16:00:00',
                    backgroundColor: '#EF4444',
                    borderColor: '#EF4444',
                },
                {
                    title: 'ELEC-202 - ModuleGeneral 3',
                    start: '2023-09-15T13:00:00',
                    end: '2023-09-15T17:00:00',
                    backgroundColor: '#10B981',
                    borderColor: '#10B981',
                },
            ],
        });

        const upcomingSessions = ref([
            {
                date: '2023-09-18',
                groupe: 'MEC-201',
                module: 'ModuleGeneral 1 - Transmission',
                lieu: 'Atelier Mécanique',
            },
            {
                date: '2023-09-19',
                groupe: 'ELEC-202',
                module: 'ModuleGeneral 3 - Circuits',
                lieu: 'Salle 12',
            },
            {
                date: '2023-09-20',
                groupe: 'MEC-201',
                module: 'ModuleGeneral 1 - Moteurs',
                lieu: 'Atelier Mécanique',
            },
            {
                date: '2023-09-21',
                groupe: 'ELEC-202',
                module: 'ModuleGeneral 3 - Panneaux',
                lieu: 'Atelier Électricité',
            },
            {
                date: '2023-09-22',
                groupe: 'MEC-201',
                module: 'ModuleGeneral 1 - Freinage',
                lieu: 'Atelier Mécanique',
            },
        ]);

        const upcomingVisits = ref([
            {
                entreprise: 'STG Motors',
                date: '2023-09-13',
                heure: '09:00 - 12:00',
                stagiaires: ['Ahmed Ben Ali', 'Mohamed Trabelsi'],
                objectif: 'Suivi progression sur diagnostics',
            },
            {
                entreprise: 'ElectroPlus',
                date: '2023-09-16',
                heure: '10:00 - 13:00',
                stagiaires: ['Fatima Zohra', 'Samir Khemiri'],
                objectif: 'Évaluation installation tableau électrique',
            },
            {
                entreprise: 'AutoService',
                date: '2023-09-20',
                heure: '14:00 - 17:00',
                stagiaires: ['Hichem Abbes', 'Leila Ben Amor'],
                objectif: 'Contrôle entretien préventif',
            },
        ]);

        const alertes = ref([
            {
                date: '2023-09-10',
                type: 'Absence',
                message: 'Taux absence élevé groupe MEC-201',
            },
            {
                date: '2023-09-11',
                type: 'Ressource',
                message: 'Stock pièces mécaniques faible',
            },
            {
                date: '2023-09-12',
                type: 'Évaluation',
                message: 'Corrections évaluations ModuleGeneral 1 en retard',
            },
        ]);

        const evaluations = ref([
            {
                module: 'ModuleGeneral 1 - Transmission',
                groupe: 'MEC-201',
                date: '2023-09-15',
            },
            {
                module: 'ModuleGeneral 3 - Circuits',
                groupe: 'ELEC-202',
                date: '2023-09-18',
            },
            {
                module: 'ModuleGeneral 1 - Freinage',
                groupe: 'MEC-201',
                date: '2023-09-20',
            },
        ]);

        const groupesActifs = ref([
            {
                nom: 'MEC-201',
                specialite: 'Mécanique Auto',
                mode: 'Résidentiel',
                date_debut: '2023-01-15',
                date_fin: '2023-12-20',
                stagiaires_actuels: 18,
                stagiaires_max: 20,
            },
            {
                nom: 'ELEC-202',
                specialite: 'Électricité Bâtiment',
                mode: 'Alternance',
                date_debut: '2023-02-01',
                date_fin: '2024-01-30',
                stagiaires_actuels: 15,
                stagiaires_max: 15,
            },
            {
                nom: 'MEC-203',
                specialite: 'Mécanique Auto',
                mode: 'Apprentissage',
                date_debut: '2023-03-15',
                date_fin: '2024-03-14',
                stagiaires_actuels: 12,
                stagiaires_max: 12,
            },
            {
                nom: 'ELEC-204',
                specialite: 'Électricité Bâtiment',
                mode: 'F0',
                date_debut: '2023-04-01',
                date_fin: '2024-03-30',
                stagiaires_actuels: 10,
                stagiaires_max: 10,
            },
        ]);

        const modulesEncours = ref([
            {
                nom: 'ModuleGeneral 1 - Transmission',
                groupe: 'MEC-201',
                heures_prevues: 40,
                heures_realisees: 28,
                date_fin: '2023-10-15',
            },
            {
                nom: 'ModuleGeneral 3 - Circuits',
                groupe: 'ELEC-202',
                heures_prevues: 30,
                heures_realisees: 18,
                date_fin: '2023-10-01',
            },
            {
                nom: 'ModuleGeneral 1 - Moteurs',
                groupe: 'MEC-201',
                heures_prevues: 35,
                heures_realisees: 20,
                date_fin: '2023-11-01',
            },
            {
                nom: 'ModuleGeneral 3 - Panneaux',
                groupe: 'ELEC-202',
                heures_prevues: 25,
                heures_realisees: 12,
                date_fin: '2023-10-20',
            },
            {
                nom: 'ModuleGeneral 1 - Freinage',
                groupe: 'MEC-201',
                heures_prevues: 30,
                heures_realisees: 15,
                date_fin: '2023-11-15',
            },
        ]);

        const dernieresVisites = ref([
            {
                date: '2023-09-05',
                entreprise: 'STG Motors',
                stagiaires: ['Ahmed Ben Ali', 'Mohamed Trabelsi'],
                rapport: true,
            },
            {
                date: '2023-09-06',
                entreprise: 'ElectroPlus',
                stagiaires: ['Fatima Zohra', 'Samir Khemiri'],
                rapport: true,
            },
            {
                date: '2023-09-08',
                entreprise: 'AutoService',
                stagiaires: ['Hichem Abbes', 'Leila Ben Amor'],
                rapport: false,
            },
        ]);

        const demandesMateriel = ref([
            {
                date: '2023-08-15',
                module: 'ModuleGeneral 1 - Transmission',
                statut: 'Approuvée',
                articles: [
                    {
                        designation: 'Kit transmission',
                        quantite: 5,
                        justification: 'Pratique atelier',
                    },
                    {
                        designation: 'Outils diagnostic',
                        quantite: 2,
                        justification: 'Nouvelle technologie',
                    },
                ],
            },
            {
                date: '2023-09-01',
                module: 'ModuleGeneral 3 - Circuits',
                statut: 'En traitement',
                articles: [
                    {
                        designation: 'Câbles électriques',
                        quantite: 20,
                        justification: 'Stock épuisé',
                    },
                    {
                        designation: 'Multimètres',
                        quantite: 5,
                        justification: 'Équipement de base',
                    },
                ],
            },
        ]);

        const historiqueMateriel = ref([
            {
                date_reception: '2023-06-10',
                module: 'ModuleGeneral 1 - Moteurs',
                fournisseur: 'AutoParts',
                articles: [
                    { designation: 'Kit moteur', quantite: 3 },
                    { designation: 'Outils spéciaux', quantite: 2 },
                ],
            },
            {
                date_reception: '2023-07-20',
                module: 'ModuleGeneral 3 - Panneaux',
                fournisseur: 'ElectroFournitures',
                articles: [
                    { designation: 'Panneaux électriques', quantite: 4 },
                    { designation: 'Interrupteurs', quantite: 15 },
                ],
            },
        ]);

        const showDemandeDialog = ref(false);
        const newDemande = ref({
            module: null,
            groupe: null,
            articles: [],
        });
        const article = ref({
            designation: '',
            quantite: 1,
            justification: '',
        });
        const modulesOptions = ref([
            { nom: 'ModuleGeneral 1 - Transmission' },
            { nom: 'ModuleGeneral 1 - Moteurs' },
            { nom: 'ModuleGeneral 1 - Freinage' },
            { nom: 'ModuleGeneral 3 - Circuits' },
            { nom: 'ModuleGeneral 3 - Panneaux' },
        ]);
        const groupesOptions = ref([
            { nom: 'MEC-201' },
            { nom: 'ELEC-202' },
            { nom: 'MEC-203' },
            { nom: 'ELEC-204' },
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
                case 'Absence':
                    return 'danger';
                case 'Ressource':
                    return 'warning';
                case 'Évaluation':
                    return 'info';
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

        const getStatutSeverity = (statut) => {
            switch (statut) {
                case 'Approuvée':
                    return 'success';
                case 'En traitement':
                    return 'info';
                case 'Rejetée':
                    return 'danger';
                default:
                    return null;
            }
        };

        const openDemandeDialog = () => {
            showDemandeDialog.value = true;
        };

        const addArticle = () => {
            if (article.value.designation && article.value.quantite > 0) {
                newDemande.value.articles.push({ ...article.value });
                article.value = {
                    designation: '',
                    quantite: 1,
                    justification: '',
                };
            }
        };

        const removeArticle = (index) => {
            newDemande.value.articles.splice(index, 1);
        };

        const submitDemande = () => {
            if (
                newDemande.value.module &&
                newDemande.value.groupe &&
                newDemande.value.articles.length > 0
            ) {
                demandesMateriel.value.unshift({
                    date: new Date().toISOString().split('T')[0],
                    module: newDemande.value.module.nom,
                    statut: 'En traitement',
                    articles: [...newDemande.value.articles],
                });

                newDemande.value = {
                    module: null,
                    groupe: null,
                    articles: [],
                };

                showDemandeDialog.value = false;
            }
        };

        onMounted(() => {
            // Ici vous pourriez faire des appels API pour récupérer les données réelles
        });

        return {
            stats,
            chartData,
            chartOptions,
            currentWeek,
            calendarOptions,
            upcomingSessions,
            upcomingVisits,
            alertes,
            evaluations,
            groupesActifs,
            modulesEncours,
            dernieresVisites,
            demandesMateriel,
            historiqueMateriel,
            showDemandeDialog,
            newDemande,
            article,
            modulesOptions,
            groupesOptions,
            formatDate,
            formatShortDate,
            getSeverity,
            getModeSeverity,
            getStatutSeverity,
            openDemandeDialog,
            addArticle,
            removeArticle,
            submitDemande,
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

.fc-event {
    cursor: pointer;
}

.fc-event-title {
    white-space: normal;
}
</style>
