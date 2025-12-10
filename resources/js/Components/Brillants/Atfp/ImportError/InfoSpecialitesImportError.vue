<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :modal="true"
        :focusOnShow="false"
        :style="{ width: '95vw', maxWidth: '1600px' }"
        :breakpoints="{ '960px': '98vw', '640px': '100vw' }"
        class="p-dialog p-fluid surface-card border-round-lg shadow-4"
        :pt="{
            mask: { style: 'backdrop-filter: blur(4px)' },
            header: { class: 'surface-50 border-bottom-1 surface-border p-3' },
            content: { class: 'surface-ground p-4' },
            footer: { class: 'surface-50 border-top-1 surface-border p-3' },
        }"
    >
        <template #header>
            <div class="flex align-items-center gap-2">
                <i class="pi pi-exclamation-triangle text-red-500 text-2xl"></i>
                <span class="font-semibold text-2xl text-900"
                    >Erreurs d'importation des informations de spécialités</span
                >
            </div>
        </template>

        <div
            v-if="errors.length > 0"
            class="surface-card p-4 shadow-2 border-round-lg"
        >
            <div
                class="mb-4 flex flex-column md:flex-row justify-content-between align-items-start md:align-items-center gap-3"
            >
                <div class="flex align-items-center gap-3">
                    <Tag
                        :value="errors.length"
                        severity="danger"
                        class="font-bold"
                    />
                    <span class="text-lg font-semibold text-900"
                        >Erreurs détectées</span
                    >
                </div>
                <div
                    class="flex flex-column md:flex-row gap-3 w-full md:w-auto"
                >
                    <Button
                        label="Tout réimporter"
                        icon="pi pi-refresh"
                        class="p-button-outlined p-button-secondary"
                        @click="retryAllImports"
                        :loading="bulkLoading"
                    />
                </div>
            </div>

            <DataTable
                :value="errors"
                scrollable
                scrollHeight="65vh"
                :loading="loading"
                class="p-datatable-sm"
                :pt="{
                    root: { class: 'border-round-lg' },
                    header: { class: 'surface-100 p-2' },
                    paginator: {
                        class: 'surface-100 border-top-1 surface-border p-2',
                    },
                }"
                stripedRows
            >
                <Column field="line" header="Ligne" style="width: 80px">
                    <template #body="{ data }">
                        <Badge
                            :value="data.line"
                            severity="info"
                            class="font-bold"
                        />
                    </template>
                </Column>

                <Column field="data" header="Données" style="min-width: 900px">
                    <template #body="{ data }">
                        <div class="grid">
                            <div
                                v-for="(field, index) in fields"
                                :key="index"
                                class="col-12 md:col-4 field mb-3"
                            >
                                <label
                                    :for="field.key"
                                    class="block text-sm font-medium text-700 mb-1"
                                >
                                    {{ field.label }}
                                    <Chip
                                        v-if="field.required"
                                        label="Obligatoire"
                                        class="ml-2 text-xs bg-red-100 text-red-800"
                                    />
                                </label>
                                <InputText
                                    v-if="
                                        field.type === 'text' ||
                                        field.type === 'number'
                                    "
                                    v-model="data.parsedData[field.key]"
                                    :type="field.type"
                                    :placeholder="field.label"
                                    class="w-full"
                                    :class="{
                                        'p-invalid':
                                            data.errors &&
                                            data.errors[field.key],
                                    }"
                                    @input="validateInput(data)"
                                />
                                <Dropdown
                                    v-else-if="field.type === 'select'"
                                    v-model="data.parsedData[field.key]"
                                    :options="field.options"
                                    :placeholder="field.label"
                                    class="w-full"
                                    :class="{
                                        'p-invalid':
                                            data.errors &&
                                            data.errors[field.key],
                                    }"
                                    @change="validateInput(data)"
                                />
                                <small
                                    v-if="data.errors && data.errors[field.key]"
                                    class="p-error block mt-1"
                                >
                                    <i
                                        class="pi pi-exclamation-circle mr-1"
                                    ></i>
                                    {{ data.errors[field.key].join(', ') }}
                                </small>
                            </div>
                        </div>
                    </template>
                </Column>

                <Column
                    field="message"
                    header="Message d'erreur"
                    style="min-width: 300px"
                >
                    <template #body="{ data }">
                        <div class="flex align-items-start gap-2">
                            <i
                                class="pi pi-exclamation-circle text-red-500 mt-1"
                            ></i>
                            <div>
                                <span class="text-red-500 font-medium">{{
                                    data.message
                                }}</span>
                                <div v-if="data.errors" class="mt-2">
                                    <Chip
                                        v-for="(errs, field) in data.errors"
                                        :key="field"
                                        :label="
                                            fields.find((f) => f.key === field)
                                                ?.label || field
                                        "
                                        icon="pi pi-times"
                                        class="mr-2 mb-2 text-xs bg-red-100 text-red-800"
                                    />
                                </div>
                            </div>
                        </div>
                    </template>
                </Column>

                <Column header="Actions" style="width: 120px">
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Button
                                icon="pi pi-check"
                                class="p-button-rounded p-button-success p-button-text"
                                @click="retryImport(data)"
                                :loading="data.loading"
                                v-tooltip.top="'Réimporter cette ligne'"
                            />
                            <Button
                                icon="pi pi-times"
                                class="p-button-rounded p-button-danger p-button-text"
                                @click="removeError(data)"
                                v-tooltip.top="'Ignorer cette erreur'"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <div
            v-else
            class="flex flex-column align-items-center justify-content-center gap-3 p-5"
        >
            <i class="pi pi-check-circle text-green-500 text-5xl"></i>
            <span class="text-xl font-medium text-900"
                >Aucune erreur détectée</span
            >
        </div>

        <template #footer>
            <div
                class="flex flex-column md:flex-row justify-content-between align-items-center gap-3"
            >
                <small class="text-600 flex align-items-center">
                    <i class="pi pi-info-circle mr-2"></i>
                    Corrigez les données et cliquez sur
                    <i class="pi pi-check mx-1"></i> pour réimporter
                </small>
                <div class="flex gap-2">
                    <Button
                        label="Fermer"
                        icon="pi pi-times"
                        class="p-button-outlined p-button-secondary"
                        @click="closePopup"
                    />
                    <Button
                        label="Exporter les corrections"
                        icon="pi pi-file-excel"
                        class="p-button-primary"
                        @click="saveCorrections"
                        :loading="submitting"
                    />
                </div>
            </div>
        </template>
    </Dialog>
</template>

<script>
import axios from '@/axios'; // Instance Axios personnalisée
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Tag from 'primevue/tag';
import Badge from 'primevue/badge';
import Chip from 'primevue/chip';
import { useToast } from 'primevue/usetoast';
import ExcelJS from 'exceljs';

export default {
    components: {
        Button,
        Column,
        DataTable,
        Dialog,
        InputText,
        Dropdown,
        Tag,
        Badge,
        Chip,
    },
    props: {
        errors: {
            type: Array,
            required: true,
            default: () => [],
        },
        visible: {
            type: Boolean,
            required: true,
        },
    },
    emits: ['update:visible', 'line-imported', 'close', 'update:errors'],
    setup() {
        return { toast: useToast() };
    },
    data() {
        return {
            loading: false,
            bulkLoading: false,
            submitting: false,
            fields: [
                { key: 'Code', label: 'Code', type: 'text', required: true },
                { key: 'Année', label: 'Année', type: 'text', required: true },
                {
                    key: 'Homologation',
                    label: 'Homologation',
                    type: 'select',
                    options: ['', 'Homologuée', 'Non Homologuée'],
                    required: false,
                },
                {
                    key: 'Heures Techniques',
                    label: 'Heures Techniques',
                    type: 'number',
                    required: false,
                },
                {
                    key: 'Heures Généraux',
                    label: 'Heures Généraux',
                    type: 'number',
                    required: false,
                },
                {
                    key: 'Durée de Formation',
                    label: 'Durée de Formation',
                    type: 'number',
                    required: false,
                },
                {
                    key: 'Statut',
                    label: 'Statut',
                    type: 'select',
                    options: [
                        '',
                        'Active',
                        'Non Active',
                        'Annulée',
                        'Remplacée',
                    ],
                    required: false,
                },
                {
                    key: 'Observation',
                    label: 'Observation',
                    type: 'text',
                    required: false,
                },
            ],
        };
    },
    methods: {
        parseData(data) {
            if (!data || typeof data !== 'object') {
                console.warn('Données invalides dans parseData:', data);
                return {
                    Code: '',
                    Année: '',
                    Homologation: '',
                    'Heures Techniques': '',
                    'Heures Généraux': '',
                    'Durée de Formation': '',
                    Statut: '',
                    Observation: '',
                };
            }
            return {
                Code: data['Code'] || '',
                Année: data['Année'] || '',
                Homologation: data['Homologation'] || '',
                'Heures Techniques': data['Heures Techniques'] || '',
                'Heures Généraux': data['Heures Généraux'] || '',
                'Durée de Formation': data['Durée de Formation'] || '',
                Statut: data['Statut'] || '',
                Observation: data['Observation'] || '',
            };
        },
        async retryImport(error) {
            error.loading = true;
            try {
                const parsedData = error.parsedData;
                const errors = {};

                // Validation des champs
                if (!parsedData['Code'])
                    errors['Code'] = ['Le code est obligatoire.'];
                if (!parsedData['Année'])
                    errors['Année'] = ["L'année est obligatoire."];
                if (
                    parsedData['Homologation'] &&
                    !['Homologuée', 'Non Homologuée'].includes(
                        parsedData['Homologation']
                    )
                ) {
                    errors['Homologation'] = [
                        'L\'homologation doit être "Homologuée" ou "Non Homologuée".',
                    ];
                }
                if (
                    parsedData['Heures Techniques'] &&
                    (isNaN(parsedData['Heures Techniques']) ||
                        parsedData['Heures Techniques'] < 0)
                ) {
                    errors['Heures Techniques'] = [
                        'Les heures techniques doivent être un nombre positif ou zéro.',
                    ];
                }
                if (
                    parsedData['Heures Généraux'] &&
                    (isNaN(parsedData['Heures Généraux']) ||
                        parsedData['Heures Généraux'] < 0)
                ) {
                    errors['Heures Généraux'] = [
                        'Les heures généraux doivent être un nombre positif ou zéro.',
                    ];
                }
                if (
                    parsedData['Durée de Formation'] &&
                    (isNaN(parsedData['Durée de Formation']) ||
                        parsedData['Durée de Formation'] < 0 ||
                        parsedData['Durée de Formation'] > 9.9)
                ) {
                    errors['Durée de Formation'] = [
                        'La durée de formation doit être un nombre entre 0 et 9.9.',
                    ];
                }
                if (
                    parsedData['Statut'] &&
                    !['Active', 'Non Active', 'Annulée', 'Remplacée'].includes(
                        parsedData['Statut']
                    )
                ) {
                    errors['Statut'] = [
                        'Le statut doit être Active, Non Active, Annulée ou Remplacée.',
                    ];
                }
                if (Object.keys(errors).length > 0) {
                    error.errors = errors;
                    throw new Error(
                        'Veuillez corriger les erreurs dans le formulaire.'
                    );
                }

                // Vérification de la spécialité
                const specialiteResponse = await axios.get(
                    `/api/specialites/code/${encodeURIComponent(parsedData['Code'])}`
                );
                const specialiteId = specialiteResponse.data.id;

                // Vérification de l'année
                const anneeResponse = await axios.get(
                    `/api/annees-formation/intitule/${encodeURIComponent(parsedData['Année'])}`
                );
                const anneeId = anneeResponse.data.id;

                // Préparation des données pour l'importation
                const payload = {
                    specialite_id: specialiteId,
                    annee_formation_id: anneeId,
                    homologation_fr: parsedData['Homologation'] || null,
                    homologation_ar:
                        parsedData['Homologation'] === 'Homologuée'
                            ? 'منظر'
                            : parsedData['Homologation'] === 'Non Homologuée'
                              ? 'غير منظر'
                              : null,
                    heures_et: parsedData['Heures Techniques']
                        ? parseInt(parsedData['Heures Techniques'])
                        : null,
                    heures_eg: parsedData['Heures Généraux']
                        ? parseInt(parsedData['Heures Généraux'])
                        : null,
                    duree_formation: parsedData['Durée de Formation']
                        ? parseFloat(parsedData['Durée de Formation'])
                        : null,
                    statut: parsedData['Statut'] || null,
                    observation: parsedData['Observation'] || null,
                };

                // Envoi des données corrigées
                const response = await axios.post(
                    '/api/infos-specialites',
                    payload
                );

                // Émettre un événement pour informer le parent
                this.$emit('line-imported', {
                    ...error,
                    response: response.data,
                });
                this.$emit(
                    'update:errors',
                    this.errors.filter((e) => e.line !== error.line)
                );

                // Message de succès
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: `Ligne ${error.line} importée avec succès.`,
                    life: 3000,
                });
            } catch (error) {
                console.error('Erreur lors de la réimportation:', {
                    message: error.message,
                    response: error.response?.data,
                    status: error.response?.status,
                });
                let errorMessage =
                    error.response?.data?.message || error.message;
                let serverErrors = error.response?.data?.errors || null;
                if (error.response?.status === 422 && serverErrors) {
                    errorMessage =
                        'Veuillez corriger les erreurs dans le formulaire.';
                    error.errors = serverErrors;
                } else {
                    error.errors = error.errors || null;
                    error.message = this.translateError(errorMessage);
                }
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.message,
                    life: 5000,
                });
            } finally {
                error.loading = false;
            }
        },
        async retryAllImports() {
            this.bulkLoading = true;
            try {
                const results = await Promise.allSettled(
                    this.errors.map((error) => this.retryImport(error))
                );
                const successCount = results.filter(
                    (r) => r.status === 'fulfilled'
                ).length;
                const errorCount = results.filter(
                    (r) => r.status === 'rejected'
                ).length;
                this.toast.add({
                    severity: 'info',
                    summary: 'Importation terminée',
                    detail: `${successCount} lignes réussies, ${errorCount} échecs.`,
                    life: 5000,
                });
            } finally {
                this.bulkLoading = false;
            }
        },
        removeError(error) {
            this.$emit(
                'update:errors',
                this.errors.filter((e) => e.line !== error.line)
            );
            this.toast.add({
                severity: 'warn',
                summary: 'Ignoré',
                detail: `Ligne ${error.line} ignorée.`,
                life: 3000,
            });
        },
        async saveCorrections() {
            this.submitting = true;
            try {
                if (this.errors.length === 0) {
                    this.toast.add({
                        severity: 'warn',
                        summary: 'Aucune correction',
                        detail: 'Aucune erreur à sauvegarder.',
                        life: 3000,
                    });
                    this.closePopup();
                    return;
                }
                const headers = this.fields
                    .map((f) => f.label)
                    .concat('Erreur');
                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet(
                    'Corrections Infos Spécialités'
                );
                worksheet.addRow(headers);
                const headerRow = worksheet.getRow(1);
                headerRow.font = { bold: true, color: { argb: 'FFFFFFFF' } };
                headerRow.fill = {
                    type: 'pattern',
                    pattern: 'solid',
                    fgColor: { argb: 'FF3B82F6' },
                };
                headerRow.alignment = {
                    vertical: 'middle',
                    horizontal: 'center',
                };
                headerRow.eachCell((cell) => {
                    cell.border = {
                        top: { style: 'thin' },
                        left: { style: 'thin' },
                        bottom: { style: 'thin' },
                        right: { style: 'thin' },
                    };
                });
                this.errors.forEach((error, rowIndex) => {
                    const rowData = this.fields.map(
                        (field) => error.parsedData[field.key] || ''
                    );
                    const errorMessage = error.message || 'Erreur inconnue';
                    const fullRow = [...rowData, errorMessage];
                    const row = worksheet.addRow(fullRow);
                    row.alignment = { vertical: 'middle', wrapText: true };
                    const errorCell = row.getCell(headers.length);
                    errorCell.fill = {
                        type: 'pattern',
                        pattern: 'solid',
                        fgColor: { argb: 'FFFF0000' },
                    };
                    errorCell.font = { color: { argb: 'FFFFFFFF' } };
                    row.eachCell((cell) => {
                        cell.border = {
                            top: { style: 'thin' },
                            left: { style: 'thin' },
                            bottom: { style: 'thin' },
                            right: { style: 'thin' },
                        };
                    });
                });
                worksheet.columns.forEach((column) => {
                    let maxLength = 0;
                    column.eachCell({ includeEmpty: true }, (cell) => {
                        const columnLength = cell.value
                            ? String(cell.value).length
                            : 10;
                        if (columnLength > maxLength) {
                            maxLength = columnLength;
                        }
                    });
                    column.width = Math.min(Math.max(maxLength + 2, 10), 50);
                });
                const timestamp = new Date()
                    .toISOString()
                    .replace(/[:.]/g, '-');
                const fileName = `corrections_infos_specialites_${timestamp}.xlsx`;
                const buffer = await workbook.xlsx.writeBuffer();
                const blob = new Blob([buffer], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = fileName;
                link.click();
                URL.revokeObjectURL(link.href);
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail:
                        'Les corrections ont été sauvegardées dans ' + fileName,
                    life: 3000,
                });
                this.closePopup();
            } catch (error) {
                console.error('Erreur lors de la sauvegarde:', error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec de la sauvegarde des corrections.',
                    life: 3000,
                });
            } finally {
                this.submitting = false;
            }
        },
        closePopup() {
            this.$emit('close');
            this.$emit('update:visible', false);
        },
        validateInput(error) {
            const errors = {};
            this.fields.forEach((field) => {
                const value = error.parsedData[field.key] || '';
                if (field.required && !value) {
                    errors[field.key] = [
                        `Le champ '${field.label}' est obligatoire.`,
                    ];
                }
                if (field.type === 'text' && value && value.length > 255) {
                    errors[field.key] = [
                        `Le champ '${field.label}' ne doit pas dépasser 255 caractères.`,
                    ];
                }
                if (
                    field.key === 'Homologation' &&
                    value &&
                    !['Homologuée', 'Non Homologuée'].includes(value)
                ) {
                    errors[field.key] = [
                        "L'homologation doit être Homologuée ou Non Homologuée.",
                    ];
                }
                if (
                    field.key === 'Heures Techniques' &&
                    value &&
                    (isNaN(value) || value < 0)
                ) {
                    errors[field.key] = [
                        'Les heures techniques doivent être un nombre positif ou zéro.',
                    ];
                }
                if (
                    field.key === 'Heures Généraux' &&
                    value &&
                    (isNaN(value) || value < 0)
                ) {
                    errors[field.key] = [
                        'Les heures généraux doivent être un nombre positif ou zéro.',
                    ];
                }
                if (
                    field.key === 'Durée de Formation' &&
                    value &&
                    (isNaN(value) || value < 0 || value > 9.9)
                ) {
                    errors[field.key] = [
                        'La durée de formation doit être un nombre entre 0 et 9.9.',
                    ];
                }
                if (
                    field.key === 'Statut' &&
                    value &&
                    !['Active', 'Non Active', 'Annulée', 'Remplacée'].includes(
                        value
                    )
                ) {
                    errors[field.key] = [
                        'Le statut doit être Active, Non Active, Annulée ou Remplacée.',
                    ];
                }
            });
            error.errors = Object.keys(errors).length > 0 ? errors : null;
        },
        translateError(message) {
            const translations = {
                'Le code est obligatoire.': 'Le code est obligatoire.',
                "L'année est obligatoire.": "L'année est obligatoire.",
                'L\'homologation doit être "Homologuée" ou "Non Homologuée".':
                    'L\'homologation doit être "Homologuée" ou "Non Homologuée".',
                'Les heures techniques doivent être un nombre positif ou zéro.':
                    'Les heures techniques doivent être un nombre positif ou zéro.',
                'Les heures généraux doivent être un nombre positif ou zéro.':
                    'Les heures généraux doivent être un nombre positif ou zéro.',
                'La durée de formation doit être un nombre entre 0 et 9.9.':
                    'La durée de formation doit être un nombre entre 0 et 9.9.',
                'Le statut doit être Active, Non Active, Annulée ou Remplacée.':
                    'Le statut doit être Active, Non Active, Annulée ou Remplacée.',
                'Erreurs de validation':
                    'Veuillez corriger les erreurs dans le formulaire.',
            };
            return translations[message] || message;
        },
    },
    watch: {
        errors: {
            handler(newErrors) {
                if (!this.$el) return; // Éviter si le composant est démonté
                newErrors.forEach((error) => {
                    if (!error.parsedData) {
                        error.parsedData = this.parseData(error.data);
                        error.loading = false;
                    }
                });
            },
            deep: true,
            immediate: true,
        },
    },
};
</script>

<style scoped>
.p-dialog .p-dialog-content {
    padding: 0;
}

.p-datatable {
    border-radius: 0.5rem;
    overflow: hidden;
}

.p-datatable .p-datatable-thead > tr > th {
    background: var(--surface-100);
    font-weight: 600;
    padding: 0.75rem;
    color: var(--text-color);
    border-bottom: 1px solid var(--surface-border);
}

.p-datatable .p-datatable-tbody > tr {
    transition: background-color 0.2s;
}

.p-datatable .p-datatable-tbody > tr:hover {
    background: var(--surface-50) !important;
}

.p-inputtext,
.p-dropdown {
    border-radius: 6px;
    padding: 0.5rem;
    font-size: 0.875rem;
    border: 1px solid var(--surface-border);
}

.p-inputtext:focus,
.p-dropdown:focus {
    box-shadow: 0 0 0 0.2rem var(--primary-color);
    border-color: var(--primary-color);
}

.field {
    margin-bottom: 1rem;
}

.p-error {
    font-size: 0.75rem;
    color: var(--red-500);
    display: flex;
    align-items: center;
}

.p-chip {
    height: 1.5rem;
    font-size: 0.75rem;
}

.p-badge {
    min-width: 2rem;
    height: 2rem;
    line-height: 2rem;
    font-size: 0.875rem;
}

@media (max-width: 960px) {
    .p-dialog {
        width: 98vw !important;
    }
}

@media (max-width: 640px) {
    .p-dialog {
        width: 100vw !important;
    }

    .p-dialog .p-dialog-content {
        padding: 0.5rem;
    }

    .field {
        margin-bottom: 0.75rem;
    }
}
</style>
