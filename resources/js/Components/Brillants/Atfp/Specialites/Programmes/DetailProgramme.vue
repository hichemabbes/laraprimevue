<template>
    <div class="surface-ground p-3">
        <div class="surface-card p-4 border-round shadow-2">
            <!-- Loading State -->
            <div
                v-if="loading"
                class="flex flex-column align-items-center justify-content-center gap-3 p-6"
            >
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

            <!-- Programme Details -->
            <div v-else class="grid">
                <!-- Header Section -->
                <div class="col-12">
                    <div
                        class="flex flex-column md:flex-row md:align-items-center md:justify-content-between gap-3 mb-4"
                    >
                        <div>
                            <h1 class="text-2xl font-bold text-900 mb-1">
                                Détails du Programme
                            </h1>

                        </div>
                        <div class="flex gap-2">
                            <Button
                                icon="pi pi-eye"
                                label="Aperçu"
                                severity="info"
                                outlined
                                :disabled="!programme.specialite"
                                @click="openPreview"
                            />
                            <Button
                                icon="pi pi-print"
                                label="Imprimer"
                                severity="secondary"
                                outlined
                                :disabled="!programme.specialite"
                                @click="printDocument"
                            />
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-12 lg:col-6">
                    <Card class="h-full">
                        <template #title>
                            <div class="flex align-items-center gap-2">
                                <i class="pi pi-info-circle text-primary"></i>
                                <span>Informations de la Spécialité</span>
                            </div>
                        </template>
                        <template #content>
                            <div class="grid">
                                <div class="col-12 md:col-6">
                                    <div class="field">
                                        <label
                                            class="block text-600 text-sm font-medium mb-2"
                                            >Nom (FR)</label
                                        >
                                        <p class="text-900 font-medium">
                                            {{
                                                programme.specialite?.nom_fr ||
                                                'Non défini'
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-12 md:col-6">
                                    <div class="field">
                                        <label
                                            class="block text-600 text-sm font-medium mb-2"
                                            >Nom (AR)</label
                                        >
                                        <p
                                            class="text-900 font-medium arabic-normal"
                                        >
                                            {{
                                                programme.specialite?.nom_ar ||
                                                'غير محدد'
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-12 md:col-6">
                                    <div class="field">
                                        <label
                                            class="block text-600 text-sm font-medium mb-2"
                                            >Diplôme</label
                                        >
                                        <Tag
                                            :value="
                                                programme.specialite
                                                    ?.diplome_fr || '----'
                                            "
                                            :severity="
                                                getSeverity(
                                                    programme.specialite
                                                        ?.diplome_fr
                                                )
                                            "
                                        />
                                    </div>
                                </div>
                                <div class="col-12 md:col-6">
                                    <div class="field">
                                        <label
                                            class="block text-600 text-sm font-medium mb-2"
                                            >Standardisation</label
                                        >
                                        <Tag
                                            :value="
                                                programme.specialite
                                                    ?.standardisation_ar ||
                                                '----'
                                            "
                                            :severity="
                                                getSeverity(
                                                    programme.specialite
                                                        ?.standardisation_ar
                                                )
                                            "
                                            class="arabic-normal"
                                        />
                                    </div>
                                </div>
                                <div class="col-12 md:col-6">
                                    <div class="field">
                                        <label
                                            class="block text-600 text-sm font-medium mb-2"
                                            >Durée de Formation</label
                                        >
                                        <Chip
                                            :label="
                                                String(
                                                    getInfoSpecialite(
                                                        'duree_formation'
                                                    ) || 'Non disponible'
                                                )
                                            "
                                            icon="pi pi-clock"
                                            class="bg-blue-100 text-blue-800"
                                        />
                                    </div>
                                </div>
                                <div class="col-12 md:col-6">
                                    <div class="field">
                                        <label
                                            class="block text-600 text-sm font-medium mb-2"
                                            >Homologation</label
                                        >
                                        <Tag
                                            :value="
                                                getInfoSpecialite(
                                                    'homologation_fr'
                                                ) || 'Non disponible'
                                            "
                                            :severity="
                                                getSeverity(
                                                    getInfoSpecialite(
                                                        'homologation_fr'
                                                    )
                                                )
                                            "
                                        />
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="field">
                                        <label
                                            class="block text-600 text-sm font-medium mb-2"
                                            >Heures Totales</label
                                        >
                                        <div
                                            class="flex align-items-center gap-3"
                                        >
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

                <div class="col-12 lg:col-6">
                    <Card class="h-full">
                        <template #title>
                            <div class="flex align-items-center gap-2">
                                <i class="pi pi-file text-primary"></i>
                                <span>Informations du Programme</span>
                            </div>
                        </template>
                        <template #content>
                            <div class="grid">
                                <div class="col-12 md:col-6">
                                    <div class="field">
                                        <label
                                            class="block text-600 text-sm font-medium mb-2"
                                            >Version</label
                                        >
                                        <Tag
                                            :value="
                                                programme.version ||
                                                'Non défini'
                                            "
                                            severity="info"
                                            icon="pi pi-tag"
                                        />
                                    </div>
                                </div>
                                <div class="col-12 md:col-6">
                                    <div class="field">
                                        <label
                                            class="block text-600 text-sm font-medium mb-2"
                                            >Statut</label
                                        >
                                        <Tag
                                            :value="programme.statut || 'Actif'"
                                            :severity="
                                                getSeverity(programme.statut)
                                            "
                                        />
                                    </div>
                                </div>
                                <div class="col-12 md:col-6">
                                    <div class="field">
                                        <label
                                            class="block text-600 text-sm font-medium mb-2"
                                            >Date Début</label
                                        >
                                        <div
                                            class="flex align-items-center gap-2"
                                        >
                                            <i
                                                class="pi pi-calendar text-500"
                                            ></i>
                                            <span class="text-900">{{
                                                formatDate(
                                                    programme.date_debut
                                                ) || '-'
                                            }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 md:col-6">
                                    <div class="field">
                                        <label
                                            class="block text-600 text-sm font-medium mb-2"
                                            >Date Fin</label
                                        >
                                        <div
                                            class="flex align-items-center gap-2"
                                        >
                                            <i
                                                class="pi pi-calendar text-500"
                                            ></i>
                                            <span class="text-900">{{
                                                formatDate(
                                                    programme.date_fin
                                                ) || '-'
                                            }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="field">
                                        <label
                                            class="block text-600 text-sm font-medium mb-2"
                                            >Description</label
                                        >
                                        <p class="text-900 line-height-3">
                                            {{
                                                programme.description ||
                                                'Aucune description'
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="field">
                                        <label
                                            class="block text-600 text-sm font-medium mb-2"
                                            >Observation</label
                                        >
                                        <p class="text-900 line-height-3">
                                            {{
                                                programme.observation ||
                                                'Aucune observation'
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Modules Section -->
                <div class="col-12">
                    <Card>
                        <template #title>
                            <div class="flex align-items-center gap-2">
                                <i class="pi pi-list text-primary"></i>
                                <span>Liste des modules</span>
                            </div>
                        </template>
                        <template #content>
                            <DataTable
                                :value="programme.modules || []"
                                :paginator="true"
                                :rows="10"
                                :rowsPerPageOptions="[5, 10, 25]"
                                size="small"
                                stripedRows
                                scrollable
                                scrollHeight="flex"
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
                                        <Tag
                                            :value="data.code"
                                            severity="info"
                                        />
                                    </template>
                                </Column>
                                <Column
                                    field="titre_module"
                                    header="Titre"
                                    sortable
                                >
                                    <template #body="{ data }">
                                        <span class="font-medium">{{
                                            data.titre_module
                                        }}</span>
                                    </template>
                                </Column>

                                <Column
                                    field="type_module"
                                    header="Type"
                                    sortable
                                >
                                    <template #body="{ data }">
                                        <Tag
                                            :value="data.type_module"
                                            :severity="
                                                getSeverity(data.type_module)
                                            "
                                        />
                                    </template>
                                </Column>

                                <Column
                                    field="heures_theoriques"
                                    header="H. Théoriques"
                                    sortable
                                >
                                    <template #body="{ data }">
                                        <Chip
                                            :label="
                                                String(
                                                    data.heures_theoriques || 0
                                                )
                                            "
                                            icon="pi pi-book"
                                            class="bg-indigo-100 text-indigo-800"
                                        />
                                    </template>
                                    <template #footer>
                                        <span class="font-bold">{{
                                            totalTheoriques
                                        }}</span>
                                    </template>
                                </Column>

                                <Column
                                    field="heures_pratiques"
                                    header="H. Pratiques"
                                    sortable
                                >
                                    <template #body="{ data }">
                                        <Chip
                                            :label="
                                                String(
                                                    data.heures_pratiques || 0
                                                )
                                            "
                                            icon="pi pi-cog"
                                            class="bg-purple-100 text-purple-800"
                                        />
                                    </template>
                                    <template #footer>
                                        <span class="font-bold">{{
                                            totalPratiques
                                        }}</span>
                                    </template>
                                </Column>

                                <Column
                                    field="heures_evaluation"
                                    header="H. Évaluation"
                                    sortable
                                >
                                    <template #body="{ data }">
                                        <Chip
                                            :label="
                                                String(
                                                    data.heures_evaluation || 0
                                                )
                                            "
                                            icon="pi pi-check-circle"
                                            class="bg-teal-100 text-teal-800"
                                        />
                                    </template>
                                    <template #footer>
                                        <span class="font-bold">{{
                                            totalEvaluation
                                        }}</span>
                                    </template>
                                </Column>

                                <Column
                                    field="heures_total"
                                    header="Total"
                                    sortable
                                >
                                    <template #body="{ data }">
                                        <Chip
                                            :label="
                                                String(
                                                    calculateTotalHours(data)
                                                )
                                            "
                                            icon="pi pi-chart-bar"
                                            class="bg-cyan-100 text-cyan-800"
                                        />
                                    </template>
                                    <template #footer>
                                        <span class="font-bold">{{
                                            totalHeures
                                        }}</span>
                                    </template>
                                </Column>

                                <Column field="statut" header="Statut" sortable>
                                    <template #body="{ data }">
                                        <Tag
                                            :value="data.statut || 'Actif'"
                                            :severity="getSeverity(data.statut)"
                                        />
                                    </template>
                                </Column>

                                <Column headerStyle="width: 5rem">
                                    <template #body>
                                        <Button
                                            icon="pi pi-ellipsis-v"
                                            severity="secondary"
                                            text
                                            rounded
                                        />
                                    </template>
                                </Column>
                            </DataTable>
                        </template>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Preview Dialog -->
        <Dialog
            v-model:visible="showPreview"
            header="Aperçu avant impression"
            :style="{ width: '90vw', maxWidth: '1200px' }"
            :modal="true"
            :breakpoints="{ '960px': '95vw', '640px': '100vw' }"
        >
            <div class="flex flex-column gap-4">
                <div
                    class="flex flex-wrap justify-content-between align-items-center gap-3 p-3 surface-100 border-round"
                >
                    <div class="flex flex-wrap gap-4">
                        <div>
                            <label for="pageSize" class="font-bold block mb-2"
                                >Format de page</label
                            >
                            <Dropdown
                                id="pageSize"
                                v-model="pageSize"
                                :options="pageSizes"
                                optionLabel="label"
                                optionValue="value"
                                class="w-10rem"
                            />
                        </div>
                        <div>
                            <label
                                for="orientation"
                                class="font-bold block mb-2"
                                >Orientation</label
                            >
                            <Dropdown
                                id="orientation"
                                v-model="orientation"
                                :options="orientations"
                                optionLabel="label"
                                optionValue="value"
                                class="w-10rem"
                            />
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <Button
                            label="Imprimer"
                            icon="pi pi-print"
                            severity="info"
                            @click="printDocument"
                        />
                    </div>
                </div>

                <div
                    id="print-area"
                    class="print-container"
                    :class="printClasses"
                >
                    <div class="document-header">
                        <div class="header-left">
                            Agence Tunisienne de la Formation Professionnelle
                        </div>
                        <div class="header-right arabic-normal">
                            الوكالة التونسية للتكوين المهني
                        </div>
                    </div>
                    <div class="print-header">
                        <h1 class="text-center">Détails du Programme</h1>
                        <div class="specialite-title text-center">
                            <p class="nom-fr">
                                {{
                                    programme.specialite?.nom_fr || 'Non défini'
                                }}
                            </p>
                            <p class="nom-ar arabic-normal">
                                {{ programme.specialite?.nom_ar || 'غير محدد' }}
                            </p>
                        </div>
                    </div>

                    <div class="print-section">
                        <div class="section-header">
                            <h2>Informations de la Spécialité</h2>
                            <p class="section-date text-right">
                                {{ currentDate }}
                            </p>
                        </div>
                        <table class="print-table">
                            <tbody>
                                <tr>
                                    <th class="text-left" style="width: 30%">
                                        Nom (FR)
                                    </th>
                                    <td class="text-left">
                                        {{
                                            programme.specialite?.nom_fr ||
                                            'Non défini'
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-left">Nom (AR)</th>
                                    <td class="text-left arabic-normal">
                                        {{
                                            programme.specialite?.nom_ar ||
                                            'غير محدد'
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-left">Diplôme</th>
                                    <td class="text-left">
                                        {{
                                            programme.specialite?.diplome_fr ||
                                            '----'
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-left">Standardisation</th>
                                    <td class="text-left arabic-normal">
                                        {{
                                            programme.specialite
                                                ?.standardisation_ar || '----'
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-left">
                                        Durée de Formation
                                    </th>
                                    <td class="text-left">
                                        {{
                                            getInfoSpecialite(
                                                'duree_formation'
                                            ) || 'Non disponible'
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-left">Homologation</th>
                                    <td class="text-left">
                                        {{
                                            getInfoSpecialite(
                                                'homologation_fr'
                                            ) || 'Non disponible'
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-left">Heures Totales</th>
                                    <td class="text-left">
                                        {{ totalHeures || 'Non disponible' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="print-section">
                        <h2>Informations du Programme</h2>
                        <table class="print-table">
                            <tbody>
                                <tr>
                                    <th class="text-left" style="width: 30%">
                                        Version
                                    </th>
                                    <td class="text-left">
                                        {{ programme.version || 'Non défini' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-left">Date Début</th>
                                </tr>
                                <tr>
                                    <th class="text-left">Date Fin</th>
                                    <td class="text-left">
                                        {{
                                            formatDate(programme.date_fin) ||
                                            '-'
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-left">Statut</th>
                                    <td class="text-left">
                                        {{ programme.statut || 'Actif' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-left">Description</th>
                                    <td class="text-left">
                                        {{
                                            programme.description ||
                                            'Aucune description'
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-left">Observation</th>
                                    <td class="text-left">
                                        {{
                                            programme.observation ||
                                            'Aucune observation'
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="print-section">
                        <h2>Liste des modules</h2>
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
                                    <th class="text-left">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(
                                        module, index
                                    ) in programme.modules || []"
                                    :key="index"
                                >
                                    <td class="text-left">
                                        {{ module.code || '-' }}
                                    </td>
                                    <td class="text-left">
                                        {{ module.titre_module || '-' }}
                                    </td>
                                    <td class="text-left arabic-normal">
                                        {{ module.type_module || '-' }}
                                    </td>
                                    <td class="text-right">
                                        {{ module.heures_theoriques || 0 }}
                                    </td>
                                    <td class="text-right">
                                        {{ module.heures_pratiques || 0 }}
                                    </td>
                                    <td class="text-right">
                                        {{ module.heures_evaluation || 0 }}
                                    </td>
                                    <td class="text-right">
                                        {{ calculateTotalHours(module) }}
                                    </td>
                                    <td class="text-left arabic-normal">
                                        {{ module.statut || 'Actif' }}
                                    </td>
                                </tr>
                                <tr v-if="!programme.modules?.length">
                                    <td
                                        colspan="8"
                                        class="text-center arabic-normal"
                                    >
                                        Aucun module associé trouvé.
                                    </td>
                                </tr>
                                <tr class="total-row">
                                    <td
                                        colspan="3"
                                        class="text-right font-bold"
                                    >
                                        Total
                                    </td>
                                    <td class="text-right font-bold">
                                        {{ totalTheoriques }}
                                    </td>
                                    <td class="text-right font-bold">
                                        {{ totalPratiques }}
                                    </td>
                                    <td class="text-right font-bold">
                                        {{ totalEvaluation }}
                                    </td>
                                    <td class="text-right font-bold">
                                        {{ totalHeures }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </Dialog>
    </div>
</template>

<script>
import { ref, onMounted, computed, nextTick } from 'vue';
import axios from 'axios';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Chip from 'primevue/chip';
import Message from 'primevue/message';
import ProgressSpinner from 'primevue/progressspinner';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import Card from 'primevue/card';
import Breadcrumb from 'primevue/breadcrumb';

export default {
    name: 'DetailProgramme',
    components: {
        Button,
        DataTable,
        Column,
        Tag,
        Chip,
        Message,
        ProgressSpinner,
        Dropdown,
        Dialog,
        Card,
        Breadcrumb,
    },
    props: {
        programmeId: {
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
    setup(props, { emit }) {
        const programme = ref({});
        const specialite = ref({});
        const loading = ref(false);
        const error = ref(null);
        const showPreview = ref(false);
        const pageSize = ref('A4');
        const orientation = ref('portrait');

        // Current date for the document
        const currentDate = computed(() => {
            const today = new Date();
            return today.toLocaleDateString('fr-FR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
        });





        const pageSizes = [
            { label: 'A4', value: 'A4' },
            { label: 'A3', value: 'A3' },
        ];

        const orientations = [
            { label: 'Portrait', value: 'portrait' },
            { label: 'Paysage', value: 'landscape' },
        ];

        // Calcul des totaux
        const totalTheoriques = computed(() => {
            if (!programme.value.modules) return 0;
            return programme.value.modules.reduce(
                (sum, module) =>
                    sum + (parseInt(module.heures_theoriques) || 0),
                0
            );
        });

        const totalPratiques = computed(() => {
            if (!programme.value.modules) return 0;
            return programme.value.modules.reduce(
                (sum, module) => sum + (parseInt(module.heures_pratiques) || 0),
                0
            );
        });

        const totalEvaluation = computed(() => {
            if (!programme.value.modules) return 0;
            return programme.value.modules.reduce(
                (sum, module) =>
                    sum + (parseInt(module.heures_evaluation) || 0),
                0
            );
        });

        const totalHeures = computed(() => {
            return (
                totalTheoriques.value +
                totalPratiques.value +
                totalEvaluation.value
            );
        });

        // Computed class for print area
        const printClasses = computed(() => ({
            'page-a4': pageSize.value === 'A4',
            'page-a3': pageSize.value === 'A3',
            'orientation-portrait': orientation.value === 'portrait',
            'orientation-landscape': orientation.value === 'landscape',
        }));

        // Fetch programme and specialty details
        const fetchProgramme = async () => {
            if (!props.programmeId || !props.anneeId) {
                error.value = "ID du programme ou de l'année manquant.";
                return;
            }
            loading.value = true;
            error.value = null;
            try {
                const programmeResponse = await axios.get(
                    `/api/programmes/${props.programmeId}`,
                    {
                        params: { annee_id: props.anneeId },
                    }
                );
                programme.value = programmeResponse.data;

                if (!programmeResponse.data.specialite) {
                    error.value =
                        'Aucune spécialité trouvée pour ce programme.';
                    return;
                }

                const specialiteResponse = await axios.get(
                    `/api/specialites/${programmeResponse.data.specialite_id}`,
                    {
                        params: { annee_id: props.anneeId },
                    }
                );
                specialite.value = specialiteResponse.data;

                programme.value.specialite = {
                    ...programmeResponse.data.specialite,
                    infos_specialites:
                        specialiteResponse.data.infos_specialites || [],
                };
            } catch (err) {
                error.value =
                    err.response?.data?.error ||
                    'Erreur lors du chargement des données.';
                console.error('Error:', err);
            } finally {
                loading.value = false;
            }
        };

        // Open preview
        const openPreview = async () => {
            if (!programme.value.specialite) {
                error.value =
                    "Aucune donnée disponible pour l'aperçu. Veuillez réessayer.";
                return;
            }
            showPreview.value = true;
        };

        // Print document
        const printDocument = async () => {
            if (!programme.value.specialite) {
                error.value = "Aucune donnée disponible pour l'impression.";
                return;
            }
            if (!showPreview.value) {
                showPreview.value = true;
                await nextTick();
            }
            await nextTick();
            const element = document.getElementById('print-area');
            if (!element) {
                error.value =
                    "Erreur : l'élément à imprimer est introuvable. Veuillez ouvrir l'aperçu d'abord.";
                console.error('Print area element not found');
                return;
            }
            const content = element.innerHTML;
            const printWindow = window.open('', '_blank');
            const styles = `
                <style>
                    * {
                        box-sizing: border-box;
                    }
                    body {
                        font-family: 'Amiri', Arial, sans-serif;
                        margin: 10mm 5mm 20mm 10mm;
                        color: #333333;
                        background: #ffffff;
                    }
                    .print-container {
                        width: 100%;
                        max-width: 100%;
                        background: #ffffff;
                        color: #333333;
                        padding: 10mm 5mm 20mm 10mm;
                    }
                    .page-a4 { width: 210mm; min-height: 297mm; }
                    .page-a3 { width: 297mm; min-height: 420mm; }
                    .orientation-landscape.page-a4 { width: 297mm; min-height: 210mm; }
                    .orientation-landscape.page-a3 { width: 420mm; min-height: 297mm; }
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
                        text-align: center !important;
                        display: block;
                        width: 100%;
                    }
                    .arabic-normal {
                        font-family: 'Amiri', sans-serif;
                        direction: rtl;
                        color: #333333;
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
                    .print-footer {
                        text-align: center;
                        margin-top: 20px;
                        font-size: 12px;
                        color: #6c757d;
                        position: fixed;
                        bottom: 10mm;
                        width: 100%;
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
                            margin: 0;
                            padding: 0;
                            background: #ffffff;
                            color: #333333;
                        }
                        .page-a4 { width: 210mm; }
                        .page-a3 { width: 297mm; }
                        .orientation-landscape.page-a4 { width: 297mm; height: 210mm; }
                        .orientation-landscape.page-a3 { width: 420mm; height: 297mm; }
                        .print-section { page-break-inside: auto; page-break-before: avoid; page-break-after: avoid; }
                        .print-table { page-break-inside: auto; page-break-before: avoid; page-break-after: avoid; }
                        .print-table tr { page-break-inside: avoid; page-break-after: auto; }
                        .print-footer {
                            position: fixed;
                            bottom: 10mm;
                            width: 100%;
                        }
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
                        <title>Impression Programme</title>
                        <link href="https://fonts.googleapis.com/css2?family=Amiri&display=swap" rel="stylesheet">
                        ${styles}
                    </head>
                    <body>
                        <div class="print-container ${Object.keys(
                            printClasses.value
                        )
                            .filter((cls) => printClasses.value[cls])
                            .join(' ')}">
                            ${content}
                        </div>
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        };

        // Fetch data on mount
        onMounted(() => {
            if (props.programmeId && props.anneeId) {
                fetchProgramme();
            } else {
                error.value = "ID du programme ou de l'année manquant.";
            }
        });

        return {
            programme,
            specialite,
            loading,
            error,
            showPreview,
            pageSize,
            orientation,
            currentDate,
            pageSizes,
            orientations,
            printClasses,

            totalTheoriques,
            totalPratiques,
            totalEvaluation,
            totalHeures,
            openPreview,
            printDocument,
            fetchProgramme,
        };
    },
    methods: {
        getInfoSpecialite(field) {
            const info =
                this.programme.specialite?.infos_specialites?.find(
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
                case "Certificat de Fin d'Apprentissage":
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
        closeDialog() {
            this.$emit('update:visible', false);
            this.$emit('close');
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

.arabic-normal {
    font-family: 'Amiri', sans-serif;
    direction: rtl;
    text-align: right;
}

/* Custom scrollbar for DataTable */
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

/* Card header styling */
:deep(.p-card-title) {
    font-size: 1.25rem;
    color: var(--primary-color);
}

/* Responsive adjustments */
@media (max-width: 960px) {
    .flex-column-on-mobile {
        flex-direction: column !important;
    }

    .w-full-on-mobile {
        width: 100% !important;
    }
}

/* Print specific styles */
.print-container {
    background: white;
    color: black;
    width: 100%;
    max-width: 100%;
    margin: 0 auto;
    padding: 10mm 5mm 20mm 10mm;
    border: 1px solid #e0e0e0;
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
    margin-bottom: 20px;
    padding-top: 10px;
}

.print-header h1 {
    color: #005b96;
    font-size: 24px;
    margin-bottom: 5px;
}

.specialite-title {
    margin: 10px 0;
    text-align: center;
}

.nom-fr,
.nom-ar {
    font-weight: bold;
    margin: 5px 0;
    text-align: center;
}

.nom-ar {
    text-align: center !important;
    display: block;
    width: 100%;
}

.print-section {
    margin-bottom: 20px;
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
    border-bottom: 1px solid #007ad9;
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
    margin-bottom: 15px;
    page-break-inside: auto;
    page-break-before: avoid;
    page-break-after: avoid;
}

.print-table th {
    background-color: #f8f9fa;
    text-align: left;
    padding: 8px;
    border: 1px solid #dee2e6;
}

.print-table td {
    padding: 8px;
    border: 1px solid #dee2e6;
    overflow-wrap: break-word;
}

.modules-table {
    max-width: 100%;
}

.modules-table th,
.modules-table td {
    padding: 6px 8px;
}

.total-row {
    background-color: #f8f9fa;
    font-weight: bold;
}

.print-footer {
    text-align: center;
    margin-top: 20px;
    font-size: 12px;
    color: #6c757d;
    position: fixed;
    bottom: 10mm;
    width: 100%;
}

@media print {
    body {
        background: white !important;
        color: black !important;
    }

    .print-container {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .print-table {
        page-break-inside: auto;
    }

    .print-section {
        page-break-inside: auto;
        page-break-before: avoid;
        page-break-after: auto;
    }

    .print-table tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }

    .print-footer {
        position: fixed;
        bottom: 10mm;
        width: 100%;
    }

    .modules-table thead {
        display: table-header-group;
    }

    .nom-ar {
        text-align: center !important;
    }
}
</style>
