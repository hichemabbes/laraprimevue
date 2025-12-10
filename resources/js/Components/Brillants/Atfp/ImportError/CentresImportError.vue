```vue
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
                    >Erreurs d'importation</span
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
                                <Dropdown
                                    v-if="field.type === 'list'"
                                    v-model="data.parsedData[index]"
                                    :options="listOptions[field.key] || []"
                                    optionLabel="nom_fr"
                                    optionValue="nom_fr"
                                    :placeholder="`Sélectionner ${field.label}`"
                                    class="w-full"
                                    :class="{
                                        'p-invalid':
                                            data.errors &&
                                            data.errors[field.key],
                                    }"
                                    @change="validateInput(data)"
                                >
                                    <template #option="slotProps">
                                        <div class="flex align-items-center">
                                            <span>{{
                                                slotProps.option.nom_fr
                                            }}</span>
                                        </div>
                                    </template>
                                </Dropdown>
                                <InputText
                                    v-else
                                    v-model="data.parsedData[index]"
                                    :type="
                                        field.type === 'email'
                                            ? 'email'
                                            : field.type === 'date'
                                              ? 'date'
                                              : 'text'
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
import axios from 'axios';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import { useToast } from 'primevue/usetoast';
import ExcelJS from 'exceljs';
import Tag from 'primevue/tag';
import Badge from 'primevue/badge';
import Chip from 'primevue/chip';

export default {
    components: {
        Button,
        Column,
        DataTable,
        Dialog,
        Dropdown,
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
            listOptions: {
                gouvernorat_fr: [],
                type_centre_fr: [],
                classe_fr: [],
                economat_fr: [],
                etat_fr: [],
                statut_fr: [],
            },
            fields: [
                { key: 'code', label: 'Code', type: 'text', required: true },
                {
                    key: 'nom_fr',
                    label: 'Nom FR',
                    type: 'text',
                    required: true,
                },
                { key: 'nom_ar', label: 'Nom AR', type: 'text' },
                {
                    key: 'adresse_fr',
                    label: 'Adresse FR',
                    type: 'text',
                    required: true,
                },
                { key: 'adresse_ar', label: 'Adresse AR', type: 'text' },
                { key: 'telephone_1', label: 'Téléphone 1', type: 'text' },
                { key: 'telephone_2', label: 'Téléphone 2', type: 'text' },
                { key: 'fax_1', label: 'Fax 1', type: 'text' },
                { key: 'fax_2', label: 'Fax 2', type: 'text' },
                { key: 'email', label: 'Email', type: 'email' },
                {
                    key: 'gouvernorat_fr',
                    label: 'Gouvernorat FR',
                    type: 'list',
                    required: true,
                },
                {
                    key: 'type_centre_fr',
                    label: 'Type Centre FR',
                    type: 'list',
                },
                { key: 'classe_fr', label: 'Classe FR', type: 'list' },
                { key: 'economat_fr', label: 'Économat FR', type: 'list' },
                { key: 'etat_fr', label: 'État FR', type: 'list' },
                {
                    key: 'statut_fr',
                    label: 'Statut FR',
                    type: 'list',
                    required: true,
                },
                { key: 'date_creation', label: 'Date Création', type: 'date' },
                {
                    key: 'date_ouverture',
                    label: 'Date Ouverture',
                    type: 'date',
                },
                {
                    key: 'date_fermeture',
                    label: 'Date Fermeture',
                    type: 'date',
                },
                {
                    key: 'observation_fr',
                    label: 'Observation FR',
                    type: 'text',
                },
            ],
        };
    },
    async created() {
        await this.loadListOptions();
    },
    methods: {
        async loadListOptions() {
            try {
                const response = await axios.get('/api/centres-with-options', {
                    params: { per_page: 1 },
                });
                const lists = response.data.lists || {};
                this.listOptions.gouvernorat_fr = lists['Gouvernorats'] || [];
                this.listOptions.type_centre_fr = lists['Types Centres'] || [];
                this.listOptions.classe_fr = lists['Classes Centres'] || [];
                this.listOptions.economat_fr = lists['Economats'] || [];
                this.listOptions.etat_fr = lists['Etats Centres'] || [];
                this.listOptions.statut_fr = lists['Statuts Centres'] || [];
            } catch (error) {
                console.error('Erreur lors du chargement des listes:', error);
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les options des listes.',
                    life: 3000,
                });
            }
        },
        parseData(data) {
            const parsed = data.split('|').map((item) => item.trim());
            return parsed.slice(0, 20);
        },
        async retryImport(error) {
            error.loading = true;
            try {
                let parsedData = [...error.parsedData];
                parsedData = parsedData.concat(Array(20).fill('')).slice(0, 20);

                const lineData = {
                    code: parsedData[0] || '',
                    nom_fr: parsedData[1] || '',
                    nom_ar: parsedData[2] || null,
                    adresse_fr: parsedData[3] || '',
                    adresse_ar: parsedData[4] || null,
                    telephone_1: parsedData[5]
                        ? parsedData[5].replace(/[^+\d]/g, '')
                        : null,
                    telephone_2: parsedData[6]
                        ? parsedData[6].replace(/[^+\d]/g, '')
                        : null,
                    fax_1: parsedData[7]
                        ? parsedData[7].replace(/[^+\d]/g, '')
                        : null,
                    fax_2: parsedData[8]
                        ? parsedData[8].replace(/[^+\d]/g, '')
                        : null,
                    email: parsedData[9] || null,
                    gouvernorat_fr: parsedData[10] || '',
                    type_centre_fr: parsedData[11] || null,
                    classe_fr: parsedData[12] || null,
                    economat_fr: parsedData[13] || null,
                    etat_fr: parsedData[14] || null,
                    statut_fr: parsedData[15] || '',
                    date_creation: parsedData[16] || null,
                    date_ouverture: parsedData[17] || null,
                    date_fermeture: parsedData[18] || null,
                    observation_fr: parsedData[19] || null,
                    line: error.line,
                };

                // Validation côté client
                const errors = {};
                if (!lineData.code) errors.code = ['Le code est obligatoire.'];
                if (!lineData.nom_fr)
                    errors.nom_fr = ['Le nom en français est obligatoire.'];
                if (!lineData.adresse_fr)
                    errors.adresse_fr = [
                        "L'adresse en français est obligatoire.",
                    ];
                if (!lineData.gouvernorat_fr)
                    errors.gouvernorat_fr = ['Le gouvernorat est obligatoire.'];
                if (!lineData.statut_fr)
                    errors.statut_fr = ['Le statut est obligatoire.'];

                if (
                    lineData.telephone_1 &&
                    !/^\+?\d{8,15}$/.test(lineData.telephone_1)
                ) {
                    errors.telephone_1 = [
                        'Le téléphone 1 doit contenir entre 8 et 15 chiffres.',
                    ];
                }
                if (
                    lineData.telephone_2 &&
                    !/^\+?\d{8,15}$/.test(lineData.telephone_2)
                ) {
                    errors.telephone_2 = [
                        'Le téléphone 2 doit contenir entre 8 et 15 chiffres.',
                    ];
                }
                if (lineData.fax_1 && !/^\+?\d{8,15}$/.test(lineData.fax_1)) {
                    errors.fax_1 = [
                        'Le fax 1 doit contenir entre 8 et 15 chiffres.',
                    ];
                }
                if (lineData.fax_2 && !/^\+?\d{8,15}$/.test(lineData.fax_2)) {
                    errors.fax_2 = [
                        'Le fax 2 doit contenir entre 8 et 15 chiffres.',
                    ];
                }
                if (
                    lineData.email &&
                    !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(lineData.email)
                ) {
                    errors.email = ["L'email est invalide."];
                }
                if (
                    lineData.gouvernorat_fr &&
                    !this.listOptions.gouvernorat_fr.some(
                        (opt) => opt.nom_fr === lineData.gouvernorat_fr
                    )
                ) {
                    errors.gouvernorat_fr = [
                        "Le gouvernorat n'est pas valide.",
                    ];
                }
                if (
                    lineData.type_centre_fr &&
                    !this.listOptions.type_centre_fr.some(
                        (opt) => opt.nom_fr === lineData.type_centre_fr
                    )
                ) {
                    errors.type_centre_fr = [
                        "Le type de centre n'est pas valide.",
                    ];
                }
                if (
                    lineData.classe_fr &&
                    !this.listOptions.classe_fr.some(
                        (opt) => opt.nom_fr === lineData.classe_fr
                    )
                ) {
                    errors.classe_fr = ["La classe n'est pas valide."];
                }
                if (
                    lineData.economat_fr &&
                    !this.listOptions.economat_fr.some(
                        (opt) => opt.nom_fr === lineData.economat_fr
                    )
                ) {
                    errors.economat_fr = ["L'économat n'est pas valide."];
                }
                if (
                    lineData.etat_fr &&
                    !this.listOptions.etat_fr.some(
                        (opt) => opt.nom_fr === lineData.etat_fr
                    )
                ) {
                    errors.etat_fr = ["L'état n'est pas valide."];
                }
                if (
                    lineData.statut_fr &&
                    !this.listOptions.statut_fr.some(
                        (opt) => opt.nom_fr === lineData.statut_fr
                    )
                ) {
                    errors.statut_fr = ["Le statut n'est pas valide."];
                }
                if (
                    lineData.date_ouverture &&
                    lineData.date_creation &&
                    lineData.date_ouverture < lineData.date_creation
                ) {
                    errors.date_ouverture = [
                        "La date d'ouverture doit être postérieure ou égale à la date de création.",
                    ];
                }
                if (
                    lineData.date_fermeture &&
                    lineData.date_ouverture &&
                    lineData.date_fermeture < lineData.date_ouverture
                ) {
                    errors.date_fermeture = [
                        "La date de fermeture doit être postérieure ou égale à la date d'ouverture.",
                    ];
                }

                if (Object.keys(errors).length > 0) {
                    error.errors = errors;
                    throw new Error(
                        'Veuillez corriger les erreurs dans le formulaire.'
                    );
                }

                // Nettoyage des champs vides
                Object.keys(lineData).forEach((key) => {
                    if (lineData[key] === '' || lineData[key] === null) {
                        if (
                            ![
                                'code',
                                'nom_fr',
                                'adresse_fr',
                                'gouvernorat_fr',
                                'statut_fr',
                            ].includes(key)
                        ) {
                            delete lineData[key];
                        }
                    }
                });

                // Émettre l'événement sans Toast
                await this.$emit('line-imported', lineData);
                this.$emit(
                    'update:errors',
                    this.errors.filter((e) => e.line !== error.line)
                );
            } catch (error) {
                console.error('Erreur lors de la réimportation:', error);
                let errorMessage =
                    error.response?.data?.message || error.message;
                let serverErrors = error.response?.data?.errors || null;

                // Mapper les erreurs du serveur aux champs
                if (error.response?.status === 422 && serverErrors) {
                    errorMessage =
                        'Veuillez corriger les erreurs dans le formulaire.';
                    const mappedErrors = {};
                    Object.keys(serverErrors).forEach((key) => {
                        if (this.fields.some((f) => f.key === key)) {
                            mappedErrors[key] = serverErrors[key];
                        } else {
                            // Si l'erreur n'est pas spécifique à un champ, l'ajouter à un champ générique
                            mappedErrors['general'] =
                                mappedErrors['general'] || [];
                            mappedErrors['general'].push(...serverErrors[key]);
                        }
                    });
                    error.errors = mappedErrors;
                    error.message = errorMessage;
                } else {
                    error.errors = null;
                    error.message = this.translateError(errorMessage);
                }

                // Afficher le Toast uniquement ici
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: errorMessage,
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
                console.log('Errors avant export', this.errors); // Log pour déboguer
                console.log('Fields', this.fields); // Log des champs
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

                // Définir les en-têtes (21 colonnes + Erreur)
                const headers = [...this.fields.map((f) => f.label), 'Erreur'];
                console.log('Headers', headers); // Log des en-têtes
                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet('Corrections');

                // Ajouter l'en-tête
                worksheet.addRow(headers);
                const headerRow = worksheet.getRow(1);
                headerRow.font = { bold: true, color: { argb: 'FFFFFFFF' } };
                headerRow.fill = {
                    type: 'pattern',
                    pattern: 'solid',
                    fgColor: { argb: 'FF3B82F6' }, // Bleu pour l'en-tête
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

                // Ajouter les lignes avec la colonne Erreur
                this.errors.forEach((error, rowIndex) => {
                    // Préparer les données : 21 colonnes + message d'erreur
                    const rowData = (
                        error.parsedData || Array(20).fill('')
                    ).slice(0, 20);
                    const errorMessage = error.message || 'Erreur inconnue';
                    const fullRow = [...rowData, errorMessage];
                    console.log('Row data', {
                        rowIndex: rowIndex + 2,
                        data: fullRow,
                        message: errorMessage,
                    }); // Log des données

                    const row = worksheet.addRow(fullRow);
                    row.alignment = { vertical: 'middle', wrapText: true };

                    // Appliquer le style à la cellule de la colonne Erreur (colonne 21)
                    const errorCell = row.getCell(21);
                    errorCell.fill = {
                        type: 'pattern',
                        pattern: 'solid',
                        fgColor: { argb: 'FFFF0000' }, // Rouge
                    };
                    errorCell.font = { color: { argb: 'FFFFFFFF' } }; // Blanc

                    // Ajouter une bordure à chaque cellule
                    row.eachCell((cell) => {
                        cell.border = {
                            top: { style: 'thin' },
                            left: { style: 'thin' },
                            bottom: { style: 'thin' },
                            right: { style: 'thin' },
                        };
                    });
                });

                // Ajuster la largeur des colonnes
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
                const fileName = `corrections_centres_${timestamp}.xlsx`;
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
                    ['telephone_1', 'telephone_2', 'fax_1', 'fax_2'].includes(
                        field.key
                    ) &&
                    value
                ) {
                    const cleanValue = value.replace(/[^+\d]/g, '');
                    if (!/^\+?\d{8,15}$/.test(cleanValue)) {
                        errors[field.key] = [
                            `Le numéro '${field.label}' doit contenir entre 8 et 15 chiffres.`,
                        ];
                    }
                }
                if (
                    field.type === 'email' &&
                    value &&
                    !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
                ) {
                    errors[field.key] = ["L'adresse email est invalide."];
                }
                if (
                    field.type === 'list' &&
                    value &&
                    !this.listOptions[field.key]?.some(
                        (opt) => opt.nom_fr === value
                    )
                ) {
                    errors[field.key] = [
                        `La valeur pour '${field.label}' n'est pas valide.`,
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

            const dateCreation =
                error.parsedData[
                    this.fields.findIndex((f) => f.key === 'date_creation')
                ] || '';
            const dateOuverture =
                error.parsedData[
                    this.fields.findIndex((f) => f.key === 'date_ouverture')
                ] || '';
            const dateFermeture =
                error.parsedData[
                    this.fields.findIndex((f) => f.key === 'date_fermeture')
                ] || '';
            if (dateOuverture && dateCreation && dateOuverture < dateCreation) {
                errors.date_ouverture = [
                    "La date d'ouverture doit être postérieure ou égale à la date de création.",
                ];
            }
            if (
                dateFermeture &&
                dateOuverture &&
                dateFermeture < dateOuverture
            ) {
                errors.date_fermeture = [
                    "La date de fermeture doit être postérieure ou égale à la date d'ouverture.",
                ];
            }

            error.errors = Object.keys(errors).length > 0 ? errors : null;
        },
        translateError(message) {
            const translations = {
                'Le code est obligatoire.': 'Le code est obligatoire.',
                'Ce code est déjà utilisé.': 'Ce code est déjà utilisé.',
                'Le nom en français est obligatoire.':
                    'Le nom en français est obligatoire.',
                "L'adresse en français est obligatoire.":
                    "L'adresse en français est obligatoire.",
                'Le gouvernorat est obligatoire.':
                    'Le gouvernorat est obligatoire.',
                'Le statut est obligatoire.': 'Le statut est obligatoire.',
                'Le téléphone 1 doit contenir entre 8 et 15 chiffres.':
                    'Le téléphone 1 doit contenir entre 8 et 15 chiffres.',
                'Le téléphone 2 doit contenir entre 8 et 15 chiffres.':
                    'Le téléphone 2 doit contenir entre 8 et 15 chiffres.',
                'Le fax 1 doit contenir entre 8 et 15 chiffres.':
                    'Le fax 1 doit contenir entre 8 et 15 chiffres.',
                'Le fax 2 doit contenir entre 8 et 15 chiffres.':
                    'Le fax 2 doit contenir entre 8 et 15 chiffres.',
                "L'email est invalide.": "L'email est invalide.",
                "La date d'ouverture doit être postérieure ou égale à la date de création.":
                    "La date d'ouverture doit être postérieure ou égale à la date de création.",
                "La date de fermeture doit être postérieure ou égale à la date d'ouverture.":
                    "La date de fermeture doit être postérieure ou égale à la date d'ouverture.",
                'Valeur invalide pour gouvernorat_fr':
                    "Le gouvernorat n'est pas valide.",
                'Valeur invalide pour type_centre_fr':
                    "Le type de centre n'est pas valide.",
                'Valeur invalide pour classe_fr': "La classe n'est pas valide.",
                'Valeur invalide pour economat_fr':
                    "L'économat n'est pas valide.",
                'Valeur invalide pour etat_fr': "L'état n'est pas valide.",
                'Valeur invalide pour statut_fr': "Le statut n'est pas valide.",
                'Erreurs de validation':
                    'Veuillez corriger les erreurs dans le formulaire.',
                'Liste Gouvernorats non disponible.':
                    "Le gouvernorat n'est pas valide.",
                'Liste Types Centres non disponible.':
                    "Le type de centre n'est pas valide.",
                'Liste Classes Centres non disponible.':
                    "La classe n'est pas valide.",
                'Liste Economats non disponible.':
                    "L'économat n'est pas valide.",
                'Liste Etats Centres non disponible.':
                    "L'état n'est pas valide.",
                'Liste Statuts Centres non disponible.':
                    "Le statut n'est pas valide.",
            };
            return translations[message] || message;
        },
    },
    watch: {
        errors: {
            handler(newErrors) {
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

.p-inputtext,
.p-dropdown {
    border-radius: 6px;
    padding: 0.5rem;
    font-size: 0.875rem;
    border: 1px solid var(--surface-border);
}

.p-inputtext:focus,
.p-dropdown:not(.p-disabled).p-focus {
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
```
