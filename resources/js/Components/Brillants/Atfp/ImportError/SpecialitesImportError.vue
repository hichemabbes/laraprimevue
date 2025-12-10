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
                    >Erreurs d'importation des spécialités</span
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
                                    v-model="data.parsedData[index]"
                                    :type="
                                        field.type === 'date' ? 'date' : 'text'
                                    "
                                    :placeholder="field.label"
                                    class="w-full"
                                    :class="{
                                        'p-invalid':
                                            data.errors &&
                                            data.errors[field.key],
                                    }"
                                    @input="validateInput(data)"
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
                { key: 'code', label: 'Code', type: 'text', required: true },
                { key: 'nom_fr', label: 'Nom (FR)', type: 'text' },
                {
                    key: 'nom_ar',
                    label: 'Nom (AR)',
                    type: 'text',
                    required: true,
                },
                { key: 'code_secteur', label: 'Code Secteur', type: 'text' },
                {
                    key: 'code_sous_secteur',
                    label: 'Code Sous-Secteur',
                    type: 'text',
                },
                { key: 'type', label: 'Type', type: 'text', required: true },
                {
                    key: 'diplome_fr',
                    label: 'Diplôme (FR)',
                    type: 'text',
                    required: true,
                },
                { key: 'date_creation', label: 'Date Création', type: 'date' },
                {
                    key: 'statut',
                    label: 'Statut',
                    type: 'text',
                    required: true,
                },
                { key: 'observation', label: 'Observation', type: 'text' },
                {
                    key: 'date_annulation',
                    label: 'Date Annulation',
                    type: 'date',
                },
            ],
        };
    },
    methods: {
        parseData(data) {
            if (!data || typeof data !== 'string') {
                console.warn('Données invalides dans parseData:', data);
                return Array(11).fill('');
            }
            const parsed = data.split(',').map((item) => item?.trim() ?? '');
            const lengthDiff = 11 - parsed.length;
            if (lengthDiff > 0) {
                return parsed.concat(Array(lengthDiff).fill(''));
            }
            return parsed.slice(0, 11);
        },
        async retryImport(error) {
            error.loading = true;
            try {
                const parsedData = [...error.parsedData];
                const lineData = {
                    code: parsedData[0] || '',
                    nom_fr: parsedData[1] || null,
                    nom_ar: parsedData[2] || '',
                    code_secteur: parsedData[3] || null,
                    code_sous_secteur: parsedData[4] || null,
                    type: parsedData[5] || '',
                    diplome_fr: parsedData[6] || '',
                    date_creation_specialite: parsedData[7] || null,
                    statut: parsedData[8] || '',
                    observation: parsedData[9] || null,
                    date_annulation_specialite: parsedData[10] || null,
                    line: error.line,
                };
                const errors = {};
                if (!lineData.code) errors.code = ['Le code est obligatoire.'];
                if (!lineData.nom_ar)
                    errors.nom_ar = ['Le nom en arabe est obligatoire.'];
                if (!lineData.type) errors.type = ['Le type est obligatoire.'];
                if (!lineData.diplome_fr)
                    errors.diplome_fr = ['Le diplôme est obligatoire.'];
                if (!lineData.statut)
                    errors.statut = ['Le statut est obligatoire.'];
                if (
                    !['Active', 'Annulée', 'Remplacée'].includes(
                        lineData.statut
                    )
                ) {
                    errors.statut = [
                        'Le statut doit être Active, Annulée ou Remplacée.',
                    ];
                }
                if (lineData.type !== 'جديد' && !lineData.code_secteur) {
                    errors.code_secteur = [
                        'Le code secteur est obligatoire pour les types "مقيس" et "غير مقيس".',
                    ];
                }
                if (lineData.type === 'مقيس' && !lineData.code_sous_secteur) {
                    errors.code_sous_secteur = [
                        'Le code sous-secteur est obligatoire pour le type "مقيس".',
                    ];
                }
                if (
                    lineData.date_creation_specialite &&
                    !/^\d{4}-\d{2}-\d{2}$/.test(
                        lineData.date_creation_specialite
                    )
                ) {
                    errors.date_creation = [
                        'La date de création doit être au format AAAA-MM-JJ.',
                    ];
                }
                if (
                    lineData.date_annulation_specialite &&
                    !/^\d{4}-\d{2}-\d{2}$/.test(
                        lineData.date_annulation_specialite
                    )
                ) {
                    errors.date_annulation = [
                        "La date d'annulation doit être au format AAAA-MM-JJ.",
                    ];
                }
                if (Object.keys(errors).length > 0) {
                    error.errors = errors;
                    throw new Error(
                        'Veuillez corriger les erreurs dans le formulaire.'
                    );
                }
                let secteurId = null;
                let sousSecteurId = null;
                if (lineData.code_secteur && lineData.type !== 'جديد') {
                    try {
                        const secteurResponse = await axios.get(`/getsecteur`, {
                            params: { code: lineData.code_secteur },
                        });
                        if (!secteurResponse.data || !secteurResponse.data.id) {
                            errors.code_secteur = [
                                `Le code secteur '${lineData.code_secteur}' est invalide ou n'existe pas.`,
                            ];
                            error.errors = errors;
                            throw new Error('Code secteur invalide.');
                        }
                        secteurId = secteurResponse.data.id;
                    } catch (err) {
                        console.error(
                            'Erreur lors de la récupération du secteur:',
                            {
                                code: lineData.code_secteur,
                                status: err.response?.status,
                                data: err.response?.data,
                                message: err.message,
                            }
                        );
                        errors.code_secteur = [
                            `Le code secteur '${lineData.code_secteur}' est invalide ou introuvable (erreur ${err.response?.status || 'inconnu'}).`,
                        ];
                        error.errors = errors;
                        throw new Error(
                            'Erreur lors de la récupération du secteur.'
                        );
                    }
                }
                if (lineData.code_sous_secteur && lineData.type === 'مقيس') {
                    try {
                        const sousSecteurResponse = await axios.get(
                            `/getsoussecteur`,
                            {
                                params: { code: lineData.code_sous_secteur },
                            }
                        );
                        if (
                            !sousSecteurResponse.data ||
                            !sousSecteurResponse.data.id
                        ) {
                            errors.code_sous_secteur = [
                                `Le code sous-secteur '${lineData.code_sous_secteur}' est invalide ou n'existe pas.`,
                            ];
                            error.errors = errors;
                            throw new Error('Code sous-secteur invalide.');
                        }
                        sousSecteurId = sousSecteurResponse.data.id;
                    } catch (err) {
                        console.error(
                            'Erreur lors de la récupération du sous-secteur:',
                            {
                                code: lineData.code_sous_secteur,
                                status: err.response?.status,
                                data: err.response?.data,
                                message: err.message,
                            }
                        );
                        errors.code_sous_secteur = [
                            `Le code sous-secteur '${lineData.code_sous_secteur}' est invalide ou introuvable (erreur ${err.response?.status || 'inconnu'}).`,
                        ];
                        error.errors = errors;
                        throw new Error(
                            'Erreur lors de la récupération du sous-secteur.'
                        );
                    }
                }
                const response = await axios.post('/specialites', {
                    code: lineData.code,
                    nom_fr: lineData.nom_fr,
                    nom_ar: lineData.nom_ar,
                    secteur_id: secteurId,
                    sous_secteur_id: sousSecteurId,
                    standardisation_ar: lineData.type,
                    diplome_fr: lineData.diplome_fr,
                    date_creation: lineData.date_creation_specialite,
                    statut: lineData.statut,
                    observation: lineData.observation,
                    date_annulation: lineData.date_annulation_specialite,
                });
                this.$emit('line-imported', {
                    ...error,
                    response: response.data,
                });
                this.$emit(
                    'update:errors',
                    this.errors.filter((e) => e.line !== error.line)
                );
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
                let errorMessage = error.response?.data?.error || error.message;
                let serverErrors = error.response?.data?.errors || null;
                if (error.response?.status === 422 && serverErrors) {
                    console.log('Erreurs de validation serveur:', serverErrors);
                    errorMessage =
                        'Veuillez corriger les erreurs dans le formulaire.';
                    const mappedErrors = {};
                    Object.keys(serverErrors).forEach((key) => {
                        if (this.fields.some((f) => f.key === key)) {
                            mappedErrors[key] = serverErrors[key];
                        } else {
                            mappedErrors['general'] =
                                mappedErrors['general'] || [];
                            mappedErrors['general'].push(...serverErrors[key]);
                        }
                    });
                    error.errors = mappedErrors;
                    error.message = errorMessage;
                } else if (errorMessage.includes('Le code est déjà utilisé')) {
                    error.errors = {
                        code: [
                            'Le code est déjà utilisé. Veuillez choisir un code unique.',
                        ],
                    };
                    error.message = `Le code '${lineData.code}' est déjà utilisé. Veuillez choisir un code unique.`;
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
                const headers = [...this.fields.map((f) => f.label), 'Erreur'];
                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet(
                    'Corrections Spécialités'
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
                    const rowData = (
                        error.parsedData || Array(11).fill('')
                    ).slice(0, 11);
                    const errorMessage = error.message || 'Erreur inconnue';
                    const fullRow = [...rowData, errorMessage];
                    const row = worksheet.addRow(fullRow);
                    row.alignment = { vertical: 'middle', wrapText: true };
                    const errorCell = row.getCell(12);
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
                const fileName = `corrections_specialites_${timestamp}.xlsx`;
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
            this.fields.forEach((field, index) => {
                const value = error.parsedData[index] || '';
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
                if (field.key === 'code' && value && value.length > 20) {
                    errors[field.key] = [
                        'Le code ne doit pas dépasser 20 caractères.',
                    ];
                }
                if (
                    field.key === 'statut' &&
                    value &&
                    !['Active', 'Annulée', 'Remplacée'].includes(value)
                ) {
                    errors[field.key] = [
                        'Le statut doit être Active, Annulée ou Remplacée.',
                    ];
                }
                if (
                    field.type === 'date' &&
                    value &&
                    !/^\d{4}-\d{2}-\d{2}$/.test(value)
                ) {
                    errors[field.key] = [
                        `La date '${field.label}' doit être au format AAAA-MM-JJ.`,
                    ];
                }
            });
            const type =
                error.parsedData[
                    this.fields.findIndex((f) => f.key === 'type')
                ] || '';
            const codeSecteur =
                error.parsedData[
                    this.fields.findIndex((f) => f.key === 'code_secteur')
                ] || '';
            const codeSousSecteur =
                error.parsedData[
                    this.fields.findIndex((f) => f.key === 'code_sous_secteur')
                ] || '';
            if (type && type !== 'جديد' && !codeSecteur) {
                errors.code_secteur = [
                    'Le code secteur est obligatoire pour les types "مقيس" et "غير مقيس".',
                ];
            }
            if (type === 'مقيس' && !codeSousSecteur) {
                errors.code_sous_secteur = [
                    'Le code sous-secteur est obligatoire pour le type "مقيس".',
                ];
            }
            error.errors = Object.keys(errors).length > 0 ? errors : null;
        },
        translateError(message) {
            const translations = {
                'Le code est obligatoire.': 'Le code est obligatoire.',
                'Le code est déjà utilisé.': 'Le code est déjà utilisé.',
                'Le nom en arabe est obligatoire.':
                    'Le nom en arabe est obligatoire.',
                'Le type est obligatoire.': 'Le type est obligatoire.',
                "Le type n'est pas valide.": "Le type n'est pas valide.",
                'Le diplôme est obligatoire.': 'Le diplôme est obligatoire.',
                "Le diplôme n'est pas valide.": "Le diplôme n'est pas valide.",
                'Le statut est obligatoire.': 'Le statut est obligatoire.',
                "Le statut n'est pas valide.": "Le statut n'est pas valide.",
                'Le code secteur est obligatoire pour les types "مقيس" et "غير مقيس".':
                    'Le code secteur est obligatoire pour les types "مقيس" et "غير مقيس".',
                'Le code sous-secteur est obligatoire pour le type "مقيس".':
                    'Le code sous-secteur est obligatoire pour le type "مقيس".',
                'Code secteur invalide.': 'Code secteur invalide.',
                'Code sous-secteur invalide.': 'Code sous-secteur invalide.',
                'Erreurs de validation':
                    'Veuillez corriger les erreurs dans le formulaire.',
                'Secteur non trouvé':
                    "Le secteur n'existe pas dans la base de données.",
                'Sous-secteur non trouvé':
                    "Le sous-secteur n'existe pas dans la base de données.",
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

.p-inputtext {
    border-radius: 6px;
    padding: 0.5rem;
    font-size: 0.875rem;
    border: 1px solid var(--surface-border);
}

.p-inputtext:focus {
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
