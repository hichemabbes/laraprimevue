```vue
<template>
    <div class="surface-ground p-3">
        <div class="surface-card p-4 border-round shadow-2">
            <!-- Header -->
            <div class="flex flex-column md:flex-row md:align-items-center md:justify-content-between gap-3 mb-4">
                <!-- Add header content if needed -->
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex flex-column align-items-center justify-content-center gap-3 p-6">
                <ProgressSpinner style="width: 50px; height: 50px" />
                <span class="text-600">Chargement des données...</span>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="p-4">
                <Message severity="error" :closable="false">
                    <div class="flex align-items-center gap-2">
                        <i class="pi pi-exclamation-circle"></i>
                        <span>{{ error }}</span>
                    </div>
                </Message>
            </div>

            <!-- Specialty Details -->
            <div v-else class="grid">
                <!-- Specialty Information -->
                <div class="col-12 lg:col-6">
                    <Card class="h-full">
                        <template #title>
                            <div class="flex align-items-center gap-2">
                                <i class="pi pi-info-circle text-primary"></i>
                                <span>Informations Générales</span>
                            </div>
                        </template>
                        <template #content>
                            <div class="grid">
                                <div class="col-12 md:col-6">
                                    <div class="field">
                                        <label class="block text-600 text-sm font-medium mb-2">Nom (FR)</label>
                                        <p class="text-900 font-medium">
                                            {{ specialite.nom_fr || 'Non défini' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-12 md:col-6">
                                    <div class="field">
                                        <label class="block text-600 text-sm font-medium mb-2">Nom (AR)</label>
                                        <p class="text-900 font-medium arabic-normal">
                                            {{ specialite.nom_ar || 'غير محدد' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-12 md:col-6">
                                    <div class="field">
                                        <label class="block text-600 text-sm font-medium mb-2">Diplôme</label>
                                        <Tag
                                            :value="specialite.diplome_fr || '----'"
                                            :severity="getSeverity(specialite.diplome_fr)"
                                        />
                                    </div>
                                </div>
                                <div class="col-12 md:col-6">
                                    <div class="field">
                                        <label class="block text-600 text-sm font-medium mb-2">Standardisation</label>
                                        <Tag
                                            :value="specialite.standardisation_ar || '----'"
                                            :severity="getSeverity(specialite.standardisation_ar)"
                                            class="arabic-normal"
                                        />
                                    </div>
                                </div>
                                <div class="col-12 md:col-6">
                                    <div class="field">
                                        <label class="block text-600 text-sm font-medium mb-2">Durée de Formation</label>
                                        <Chip
                                            :label="getInfoSpecialite('duree_formation') || 'Non disponible'"
                                            icon="pi pi-clock"
                                            class="bg-blue-100 text-blue-800"
                                        />
                                    </div>
                                </div>
                                <div class="col-12 md:col-6">
                                    <div class="field">
                                        <label class="block text-600 text-sm font-medium mb-2">Homologation</label>
                                        <Tag
                                            :value="getInfoSpecialite('homologation_fr') || 'Non disponible'"
                                            :severity="getSeverity(getInfoSpecialite('homologation_fr'))"
                                        />
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="field">
                                        <label class="block text-600 text-sm font-medium mb-2">Heures Totales</label>
                                        <div class="flex align-items-center gap-3">
                                            <Chip
                                                :label="`${totalTheoriques} H. Théoriques`"
                                                icon="pi pi-book"
                                                class="bg-indigo-100 text-indigo-800"
                                            />
                                            <Chip
                                                :label="`${totalPratiques} H. Pratiques`"
                                                icon="pi pi-cog"
                                                class="bg-purple-100 text-purple-800"
                                            />
                                            <Chip
                                                :label="`${totalEvaluation} H. Évaluation`"
                                                icon="pi pi-check-circle"
                                                class="bg-teal-100 text-teal-800"
                                            />
                                            <Chip
                                                :label="`${totalHeures} H. Total`"
                                                icon="pi pi-chart-bar"
                                                class="bg-cyan-100 text-cyan-800 font-bold"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Active Program Information -->
                <div class="col-12 lg:col-6">
                    <Card class="h-full programme-actif-card">
                        <template #title>
                            <div class="title-container">
                                <div class="flex align-items-center gap-2">
                                    <i class="pi pi-file text-primary"></i>
                                    <span>Programme Actif</span>
                                </div>
                                <div class="action-buttons">
                                    <PButton
                                        icon="pi pi-print"
                                        label="Imprimer"
                                        severity="secondary"
                                        outlined
                                        :disabled="!specialite.nom_fr"
                                        @click="printDocument"
                                    />
                                </div>
                            </div>
                        </template>
                        <template #content>
                            <div class="programme-actif-content">
                                <div v-if="activeProgramme" class="grid">
                                    <div class="col-12 md:col-6">
                                        <div class="field">
                                            <label class="block text-600 text-sm font-medium mb-2">Version</label>
                                            <Tag
                                                :value="activeProgramme.version || 'Non défini'"
                                                severity="info"
                                                icon="pi pi-tag"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-12 md:col-6">
                                        <div class="field">
                                            <label class="block text-600 text-sm font-medium mb-2">Statut</label>
                                            <Tag
                                                :value="activeProgramme.statut || 'Actif'"
                                                :severity="getSeverity(activeProgramme.statut)"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-12 md:col-6">
                                        <div class="field">
                                            <label class="block text-600 text-sm font-medium mb-2">Date Début</label>
                                            <div class="flex align-items-center gap-2">
                                                <i class="pi pi-calendar text-500"></i>
                                                <span class="text-900">{{ formatDate(activeProgramme.date_debut) || '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 md:col-6">
                                        <div class="field">
                                            <label class="block text-600 text-sm font-medium mb-2">Date Fin</label>
                                            <div class="flex align-items-center gap-2">
                                                <i class="pi pi-calendar text-500"></i>
                                                <span class="text-900">{{ formatDate(activeProgramme.date_fin) || '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Donut Chart -->
                                    <div class="col-12 mt-4 chart-container">
                                        <div id="customLegend" class="custom-legend"></div>
                                        <canvas id="repartitionHeuresChart" style="max-width: 180px; margin: 0 auto;"></canvas>
                                    </div>
                                </div>
                                <div v-else class="p-4">
                                    <Message severity="info" :closable="false">
                                        Aucun programme actif trouvé pour cette année.
                                    </Message>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Modules List -->
                <div class="col-12">
                    <Card>
                        <template #title>
                            <div class="flex align-items-center gap-2">
                                <i class="pi pi-list text-primary"></i>
                                <span>Modules Associés</span>
                            </div>
                        </template>
                        <template #content>
                            <DataTable
                                :value="activeProgramme?.modules || []"
                                :paginator="true"
                                :rows="10"
                                :rows-per-page-options="[5, 10, 25, 50]"
                                size="small"
                                striped-rows
                                scrollable
                                scroll-height="flex"
                                class="p-datatable-sm"
                                :pt="{
                                    table: { style: 'min-width: 50rem' },
                                    paginator: { class: 'mt-3' },
                                }"
                            >
                                <template #empty>
                                    <div class="text-center p-3 text-600">
                                        <i class="pi pi-database mr-2"></i>
                                        Aucun module associé trouvé
                                    </div>
                                </template>
                                <Column field="code" header="Code" sortable>
                                    <template #body="{ data }">
                                        <Tag :value="data.code || '-'" severity="info" />
                                    </template>
                                </Column>
                                <Column field="titre_module" header="Titre" sortable>
                                    <template #body="{ data }">
                                        <span class="font-medium">{{ data.titre_module || '-' }}</span>
                                    </template>
                                </Column>
                                <Column field="type_module_fr" header="Type" sortable>
                                    <template #body="{ data }">
                                        <Tag :value="data.type_module_fr || '-'" :severity="getSeverity(data.type_module_fr)" />
                                    </template>
                                </Column>
                                <Column field="heures_theoriques" header="H. Théoriques" sortable>
                                    <template #body="{ data }">
                                        <Chip
                                            :label="String(data.heures_theoriques || 0)"
                                            icon="pi pi-book"
                                            class="bg-indigo-100 text-indigo-800"
                                        />
                                    </template>
                                    <template #footer>
                                        <span class="font-bold">{{ totalTheoriques }}</span>
                                    </template>
                                </Column>
                                <Column field="heures_pratiques" header="H. Pratiques" sortable>
                                    <template #body="{ data }">
                                        <Chip
                                            :label="String(data.heures_pratiques || 0)"
                                            icon="pi pi-cog"
                                            class="bg-purple-100 text-purple-800"
                                        />
                                    </template>
                                    <template #footer>
                                        <span class="font-bold">{{ totalPratiques }}</span>
                                    </template>
                                </Column>
                                <Column field="heures_evaluation" header="H. Évaluation" sortable>
                                    <template #body="{ data }">
                                        <Chip
                                            :label="String(data.heures_evaluation || 0)"
                                            icon="pi pi-check-circle"
                                            class="bg-teal-100 text-teal-800"
                                        />
                                    </template>
                                    <template #footer>
                                        <span class="font-bold">{{ totalEvaluation }}</span>
                                    </template>
                                </Column>
                                <Column field="heures_total" header="H. Total" sortable>
                                    <template #body="{ data }">
                                        <Chip
                                            :label="String(calculateTotalHours(data))"
                                            icon="pi pi-chart-bar"
                                            class="bg-cyan-100 text-cyan-800"
                                        />
                                    </template>
                                    <template #footer>
                                        <span class="font-bold">{{ totalHeures }}</span>
                                    </template>
                                </Column>
                            </DataTable>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import PButton from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Chip from 'primevue/chip';
import Message from 'primevue/message';
import ProgressSpinner from 'primevue/progressspinner';
import Card from 'primevue/card';
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';

export default {
    name: 'ProfilSpecialite',
    components: {
        PButton,
        DataTable,
        Column,
        Tag,
        Chip,
        Message,
        ProgressSpinner,
        Card,
    },
    props: {
        specialiteId: {
            type: [Number, String],
            required: true,
        },
        anneeId: {
            type: [Number, String],
            required: true,
        },
        visible: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['update:visible', 'close'],
    data() {
        return {
            specialite: {},
            loading: false,
            error: null,
        };
    },
    computed: {
        activeProgramme() {
            if (!this.specialite.programmes || !Array.isArray(this.specialite.programmes)) {
                return null;
            }
            return this.specialite.programmes.find((programme) => programme.statut === 'Actif') || null;
        },
        currentDate() {
            const today = new Date();
            return today.toLocaleDateString('fr-FR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
        },
        totalTheoriques() {
            return this.activeProgramme?.modules?.reduce((sum, module) => sum + (parseInt(module.heures_theoriques) || 0), 0) || 0;
        },
        totalPratiques() {
            return this.activeProgramme?.modules?.reduce((sum, module) => sum + (parseInt(module.heures_pratiques) || 0), 0) || 0;
        },
        totalEvaluation() {
            return this.activeProgramme?.modules?.reduce((sum, module) => sum + (parseInt(module.heures_evaluation) || 0), 0) || 0;
        },
        totalHeures() {
            return this.totalTheoriques + this.totalPratiques + this.totalEvaluation;
        },
        totalHeuresSpecifique() {
            return this.activeProgramme?.modules
                ?.filter((module) => module.type_module_fr === 'Spécifique')
                .reduce((sum, module) => sum + this.calculateTotalHours(module), 0) || 0;
        },
        totalHeuresStage() {
            return this.activeProgramme?.modules
                ?.filter((module) => module.type_module_fr === 'Stage')
                .reduce((sum, module) => sum + this.calculateTotalHours(module), 0) || 0;
        },
        totalHeuresGeneral() {
            return this.activeProgramme?.modules
                ?.filter((module) => module.type_module_fr === 'Général')
                .reduce((sum, module) => sum + this.calculateTotalHours(module), 0) || 0;
        },
    },
    watch: {
        activeProgramme: {
            handler(newVal) {
                if (newVal && newVal.modules) {
                    this.$nextTick(() => {
                        this.renderChart();
                    });
                }
            },
            deep: true,
            immediate: true,
        },
    },
    mounted() {
        if (this.specialiteId && this.anneeId) {
            this.fetchSpecialite();
        } else {
            this.error = "ID de spécialité ou d'année manquant.";
        }
        Chart.register(ChartDataLabels);
    },
    methods: {
        async fetchSpecialite() {
            if (!this.specialiteId || !this.anneeId) {
                this.error = "ID de spécialité ou d'année manquant.";
                return;
            }
            this.loading = true;
            try {
                console.log('Fetching data for specialiteId:', this.specialiteId, 'with anneeId:', this.anneeId);
                const response = await axios.get(`/api/specialites/${this.specialiteId}`, {
                    params: { annee_id: this.anneeId },
                });
                console.log('API Response:', response.data);
                this.specialite = response.data;
                if (!response.data.infos_specialites?.length) {
                    console.warn('No infos_specialites found for anneeId:', this.anneeId);
                }
                if (!response.data.programmes?.length) {
                    console.warn('No programmes found for anneeId:', this.anneeId);
                }
            } catch (err) {
                this.error = err.response?.data?.error || 'Erreur lors du chargement de la spécialité.';
                console.error('Error:', err);
            } finally {
                this.loading = false;
            }
        },
        getInfoSpecialite(field) {
            const info = this.specialite.infos_specialites?.find(
                (info) => info.annee_formation_id === parseInt(this.anneeId)
            ) || {};
            return info[field] || null;
        },
        calculateTotalHours(data) {
            const theoriques = parseInt(data.heures_theoriques) || 0;
            const pratiques = parseInt(data.heures_pratiques) || 0;
            const evaluation = parseInt(data.heures_evaluation) || 0;
            return theoriques + pratiques + evaluation;
        },
        formatDate(date) {
            if (!date) return null;
            const d = new Date(date);
            return d.toISOString().split('T')[0];
        },
        getSeverity(value) {
            switch (value) {
                case 'Actif':
                case 'Homologuée':
                case 'Certificat de Compétence':
                case 'مقيس':
                case 'Spécifique':
                case 'Pratique':
                    return 'success';
                case 'Inactif':
                case 'Non Homologuée':
                case "'Certificat de Fin d'Apprentissage":
                case 'غير مقيس':
                    return 'danger';
                case "Certificat d'Aptitude Professionnelle":
                case 'جديد':
                case 'Évaluation':
                    return 'warning';
                case 'Brevet de Technicien Supérieur':
                case 'Théorique':
                    return 'info';
                case 'Stage':
                case 'Brevet de Technicien Professionnel':
                case 'Certificat de Formation Professionnelle':
                case 'Général':
                    return 'secondary';
                default:
                    return 'secondary';
            }
        },
        async printDocument() {
            if (!this.specialite.nom_fr) {
                this.error = "Aucune donnée disponible pour l'impression.";
                return;
            }
            await this.$nextTick();
            const element = document.createElement('div');
            element.innerHTML = `
                <div class="print-container">
                    <div class="document-header">
                        <div class="header-left">
                            Agence Tunisienne de la Formation Professionnelle
                        </div>
                        <div class="header-right arabic-normal">
                            الوكالة التونسية للتكوين المهني
                        </div>
                    </div>
                    <div class="print-header">
                        <h1 class="text-center">Détails de la Spécialité</h1>
                        <div class="specialite-title text-center">
                            <p class="nom-fr">${this.specialite.nom_fr || 'Non défini'}</p>
                            <p class="nom-ar arabic-normal">${this.specialite.nom_ar || 'غير محدد'}</p>
                        </div>
                    </div>

                    <div class="print-section">
                        <div class="section-header">
                            <h2>Informations Générales</h2>
                            <p class="section-date text-right">${this.currentDate}</p>
                        </div>
                        <table class="print-table">
                            <tbody>
                                <tr>
                                    <th class="text-left" style="width: 30%">Nom (FR)</th>
                                    <td class="text-left">${this.specialite.nom_fr || 'Non défini'}</td>
                                </tr>
                                <tr>
                                    <th class="text-left">Nom (AR)</th>
                                    <td class="text-left arabic-normal">${this.specialite.nom_ar || 'غير محدد'}</td>
                                </tr>
                                <tr>
                                    <th class="text-left">Diplôme</th>
                                    <td class="text-left">${this.specialite.diplome_fr || '----'}</td>
                                </tr>
                                <tr>
                                    <th class="text-left">Standardisation</th>
                                    <td class="text-left arabic-normal">${this.specialite.standardisation_ar || '----'}</td>
                                </tr>
                                <tr>
                                    <th class="text-left">Durée de Formation</th>
                                    <td class="text-left">${this.getInfoSpecialite('duree_formation') || 'Non disponible'}</td>
                                </tr>
                                <tr>
                                    <th class="text-left">Homologation</th>
                                    <td class="text-left">${this.getInfoSpecialite('homologation_fr') || 'Non disponible'}</td>
                                </tr>
                                <tr>
                                    <th class="text-left">Heures Totales</th>
                                    <td class="text-left">${this.totalHeures || 'Non disponible'}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="print-section">
                        <h2>Programme Actif</h2>
                        <table class="print-table">
                            <tbody>
                                ${this.activeProgramme ? `
                                    <tr>
                                        <th class="text-left" style="width: 30%;">Version</th>
                                        <td class="text-left">${this.activeProgramme.version || 'Non défini'}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-left">Date Début</th>
                                        <td class="text-left">${this.formatDate(this.activeProgramme.date_debut) || '-'}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-left">Date Fin</th>
                                        <td class="text-left">${this.formatDate(this.activeProgramme.date_fin) || '-'}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-left">Statut</th>
                                        <td class="text-left">${this.activeProgramme.statut || 'Actif'}</td>
                                    </tr>
                                ` : `
                                    <tr>
                                        <td colspan="2" class="text-center arabic-normal">
                                            Aucun programme actif ou trouvé pour cette année.
                                        </td>
                                    </tr>
                                `}
                            </tbody>
                        </table>
                    </div>

                    <div class="print-section">
                        <h2>Modules Associés</h2>
                        <table class="print-table modules-table">
                            <thead>
                                <tr>
                                    <th class="text-left">Code</th>
                                    <th class="text-left">Titre</th>
                                    <th class="text-left">Type</th>
                                    <th class="text-right">H. Théoriques</th>
                                    <th class="text-right">H. Pratiques</th>
                                    <th class="text-right">H. Évaluation</th>
                                    <th class="text-right">H. Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${this.activeProgramme?.modules?.length ? this.activeProgramme.modules.map(module => `
                                    <tr>
                                        <td class="text-left">${module.code || '-'}</td>
                                        <td class="text-left">${module.titre_module || '-'}</td>
                                        <td class="text-left arabic-normal">${module.type_module_fr || '-'}</td>
                                        <td class="text-right">${module.heures_theoriques || 0}</td>
                                        <td class="text-right">${module.heures_pratiques || 0}</td>
                                        <td class="text-right">${module.heures_evaluation || 0}</td>
                                        <td class="text-right">${this.calculateTotalHours(module)}</td>
                                    </tr>
                                `).join('') : `
                                    <tr>
                                        <td colspan="7" class="text-center arabic-normal">
                                            Aucun module associé trouvé.
                                        </td>
                                    </tr>
                                `}
                                ${this.activeProgramme?.modules?.length ? `
                                    <tr class="total-row">
                                        <td colspan="3" class="text-right font-bold">Total</td>
                                        <td class="text-right font-bold">${this.totalTheoriques}</td>
                                        <td class="text-right font-bold">${this.totalPratiques}</td>
                                        <td class="text-right font-bold">${this.totalEvaluation}</td>
                                        <td class="text-right font-bold">${this.totalHeures}</td>
                                    </tr>
                                ` : ''}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            const printWindow = window.open('', '_blank');
            const styles = `
                <style>
                    * {
                        box-sizing: border-box;
                    }
                    body {
                        font-family: 'Arial', sans-serif;
                        margin: 0;
                        color: #333333;
                        background: #ffffff;
                    }
                    .print-container {
                        width: 100%;
                        max-width: 100%;
                        margin: 0;
                        padding: 0;
                        background: #ffffff;
                        color: #333333;
                    }
                    .document-header {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 10px;
                        font-size: 12px;
                        font-weight: bold;
                    }
                    .header-left {
                        text-align: left;
                    }
                    .header-right {
                        text-align: right;
                        direction: rtl;
                    }
                    .print-header {
                        text-align: center;
                        margin-bottom: 10px;
                        padding-top: 10px;
                    }
                    .print-header h1 {
                        color: #005b96;
                        font-size: 24px;
                        margin: 0 0 5px 0;
                    }
                    .specialite-title {
                        margin: 5px 0;
                        text-align: center;
                    }
                    .nom-fr {
                        font-size: 18px;
                        font-weight: bold;
                        color: #333333;
                        direction: ltr;
                        margin: 0;
                        text-align: center;
                    }
                    .nom-ar {
                        font-size: 18px;
                        font-weight: bold;
                        color: #333333;
                        margin: 0;
                        text-align: center;
                        display: block;
                        width: 100%;
                    }
                    .arabic-normal {
                        font-family: 'Amiri', sans-serif;
                        direction: rtl;
                        text-align: right;
                    }
                    .print-section {
                        margin-bottom: 15px;
                        page-break-inside: auto;
                        page-break-before: avoid;
                        page-break-after: avoid;
                    }
                    .section-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    }
                    .print-section h2 {
                        color: #005b96;
                        font-size: 16px;
                        border-bottom: 2px solid #007ad9;
                        padding-bottom: 5px;
                        margin-bottom: 10px;
                    }
                    .section-date {
                        font-size: 12px;
                        color: #333333;
                        margin: 0;
                    }
                    .print-table {
                        width: 100%;
                        max-width: 100%;
                        border-collapse: collapse;
                        margin-top: 10px;
                        color: #333333;
                        page-break-inside: auto;
                        page-break-before: avoid;
                        page-break-after: avoid;
                    }
                    .print-table th,
                    .print-table td {
                        border: 1px solid #dee2e6;
                        padding: 8px;
                        font-size: 14px;
                        overflow-wrap: break-word;
                    }
                    .print-table th {
                        background-color: #e9ecef;
                        font-weight: bold;
                        color: #333333;
                    }
                    .print-table td {
                        background-color: #ffffff;
                    }
                    .total-row {
                        background-color: #e9ecef;
                        font-weight: bold;
                    }
                    .modules-table {
                        max-width: 100%;
                    }
                    .modules-table thead {
                        display: table-header-group;
                    }
                    .modules-table tbody {
                        display: table-row-group;
                    }
                    @media print {
                        body, .print-container {
                            margin: 0 !important;
                            padding: 0 !important;
                            background: #ffffff;
                            color: #333333;
                        }
                        .page-a4 { width: 210mm; }
                        .print-section { page-break-inside: auto; page-break-before: avoid; page-break-after: avoid; }
                        .print-table { page-break-inside: auto; page-break-before: avoid; page-break-after: auto; }
                        .print-table tr { page-break-inside: avoid; page-break-after: auto; }
                        .modules-table thead {
                            display: table-header-group;
                        }
                        .nom-ar {
                            text-align: center !important;
                        }
                    }
                </style>
            `;
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Impression Spécialité</title>
                        <link href="https://fonts.googleapis.com/css2?family=Amiri&display=swap" rel="stylesheet">
                        ${styles}
                    </head>
                    <body>
                        ${element.innerHTML}
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
            printWindow.close();
        },
        closeDialog() {
            this.$emit('update:visible', false);
            this.$emit('close');
        },
        renderChart() {
            console.log('Attempting to render chart...');
            if (!this.activeProgramme || !this.activeProgramme.modules || !this.totalHeures) {
                console.warn('No activeProgramme, modules or total hours available for chart');
                return;
            }
            const ctx = document.getElementById('repartitionHeuresChart')?.getContext('2d');
            if (!ctx) {
                console.warn('Chart canvas not found');
                return;
            }
            console.log('Chart data:', {
                specifique: this.totalHeuresSpecifique,
                stage: this.totalHeuresStage,
                general: this.totalHeuresGeneral,
            });

            const gradientSpecifique = ctx.createLinearGradient(0, 200, 0, 0);
            gradientSpecifique.addColorStop(0, '#3B82F6');
            gradientSpecifique.addColorStop(1, '#1E40AF');

            const gradientStage = ctx.createLinearGradient(0, 200, 0, 0);
            gradientStage.addColorStop(0, '#A855F7');
            gradientStage.addColorStop(1, '#6B21A8');

            const gradientGeneral = ctx.createLinearGradient(0, 200, 0, 0);
            gradientGeneral.addColorStop(0, '#22C55E');
            gradientGeneral.addColorStop(1, '#15803D');

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Spécifique', 'Stage', 'Général'],
                    datasets: [{
                        data: [
                            this.totalHeuresSpecifique,
                            this.totalHeuresStage,
                            this.totalHeuresGeneral,
                        ],
                        backgroundColor: [gradientSpecifique, gradientStage, gradientGeneral],
                        borderColor: '#E5E7EB',
                        borderWidth: 1,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#1F2937',
                            bodyColor: '#1F2937',
                            borderColor: '#D1D5DB',
                            borderWidth: 1,
                            cornerRadius: 8,
                            padding: 10,
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: ${context.raw} heures`;
                                },
                            },
                        },
                        datalabels: {
                            formatter: (value) => {
                                return value > 0 ? `${value} h` : '';
                            },
                            color: '#FFFFFF',
                            textStrokeColor: '#1F2937',
                            textStrokeWidth: 0.5,
                            font: {
                                family: 'Roboto',
                                size: 9,
                                weight: '600',
                            },
                            textAlign: 'center',
                            anchor: 'center',
                            align: 'center',
                        },
                        htmlLegend: {
                            containerID: 'customLegend',
                        },
                    },
                    elements: {
                        arc: {
                            borderWidth: 1,
                            borderColor: '#E5E7EB',
                        },
                    },
                    rotation: -45,
                    cutout: '60%',
                },
                plugins: [
                    {
                        id: 'htmlLegend',
                        afterUpdate(chart) {
                            const items = chart.options.plugins.legend.labels.generateLabels(chart);
                            const ul = document.createElement('ul');
                            ul.style.display = 'flex';
                            ul.style.flexDirection = 'row';
                            ul.style.gap = '10px';
                            ul.style.listStyle = 'none';
                            ul.style.padding = '0';
                            ul.style.margin = '10px 0 0 0';
                            ul.style.justifyContent = 'center';
                            ul.style.alignItems = 'center';

                            items.forEach((item) => {
                                const li = document.createElement('li');
                                li.style.display = 'flex';
                                li.style.alignItems = 'center';
                                li.style.gap = '5px';

                                const boxSpan = document.createElement('span');
                                boxSpan.style.background = item.fillStyle;
                                boxSpan.style.borderColor = item.strokeStyle;
                                boxSpan.style.borderWidth = item.lineWidth + 'px';
                                boxSpan.style.height = '8px';
                                boxSpan.style.width = '8px';
                                boxSpan.style.borderRadius = '50%';
                                boxSpan.style.display = 'inline-block';

                                const textContainer = document.createElement('span');
                                textContainer.style.color = '#1F2937';
                                textContainer.style.fontFamily = "'Roboto', sans-serif";
                                textContainer.style.fontSize = '12px';
                                textContainer.style.fontWeight = '500';
                                textContainer.textContent = item.text;

                                li.appendChild(boxSpan);
                                li.appendChild(textContainer);
                                ul.appendChild(li);
                            });

                            const legendContainer = document.getElementById('customLegend');
                            legendContainer.innerHTML = '';
                            legendContainer.appendChild(ul);
                        },
                    },
                ],
            });
            console.log('Chart rendered successfully');
        },
    },
};
</script>

<style scoped>
.surface-card {
    transition: box-shadow 0.3s ease;
}

.surface-card:hover {
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.field {
    margin-bottom: 1.25rem;
}

.title-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.arabic-normal {
    font-family: 'Amiri', sans-serif;
    direction: rtl;
    text-align: right;
}

.programme-actif-card {
    position: relative;
    display: flex;
    flex-direction: column;
}

.programme-actif-content {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.action-buttons .p-button {
    padding: 0.5rem 0.75rem;
    font-size: 1rem;
    min-height: 50px;
}

.programme-actif-card .field {
    margin-bottom: 1rem;
}

.chart-container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    margin-top: 0.5rem;
    margin-bottom: 0.25rem;
}

.chart-container canvas {
    max-width: 180px;
    max-height: 180px;
}

.custom-legend {
    display: flex;
    justify-content: center;
    margin-bottom: 10px;
}

.custom-legend ul {
    display: flex;
    flex-direction: row;
    gap: 10px;
    list-style: none;
    padding: 0;
    margin: 0;
}

.custom-legend li {
    display: flex;
    align-items: center;
    gap: 5px;
}

.custom-legend span {
    font-size: 12px;
    font-family: 'Roboto', sans-serif;
    font-weight: 500;
    color: #1F2937;
}

.custom-legend span:first-child {
    height: 8px;
    width: 8px;
    border-radius: 50%;
    display: inline-block;
}

@media (max-width: 768px) {
    .action-buttons {
        flex-direction: column;
        align-items: flex-end;
        gap: 0.25rem;
    }
    .action-buttons .p-button {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .action-buttons .p-button {
        padding: 0.4rem 0.6rem;
        font-size: 0.875rem;
    }
}

:deep(.p-datatable-wrapper)::-webkit-scrollbar {
    height: 8px;
    width: 8px;
}

:deep(.p-datatable-wrapper)::-webkit-scrollbar-track {
    background: #f1f1f1;
}

:deep(.p-datatable-wrapper)::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

:deep(.p-datatable-wrapper)::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

:deep(.p-card-title) {
    font-size: 1.25rem;
    color: var(--primary-color);
}

@media (max-width: 960px) {
    .flex-column-on-mobile {
        flex-direction: column !important;
    }
    .w-full-on-mobile {
        width: 100% !important;
    }
}
</style>
