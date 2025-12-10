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
                <span class="font-semibold text-2xl text-900">Erreurs d'importation</span>
            </div>
        </template>

        <div v-if="computedErrors.length > 0" class="surface-card p-4 shadow-2 border-round-lg">
            <div class="mb-4 flex flex-column md:flex-row justify-content-between align-items-start md:align-items-center gap-3">
                <div class="flex align-items-center gap-3">
                    <Tag :value="computedErrors.length" severity="danger" class="font-bold" />
                    <span class="text-lg font-semibold text-900">Erreurs détectées</span>
                </div>
                <div class="flex flex-column md:flex-row gap-3 w-full md:w-auto">
                    <Button
                        label="Tout annuler"
                        icon="pi pi-times"
                        class="p-button-outlined p-button-secondary"
                        @click="cancelAllErrors"
                    />
                </div>
            </div>

            <DataTable
                :value="computedErrors"
                scrollable
                scrollHeight="65vh"
                :loading="loading"
                class="p-datatable-sm"
                :pt="{
                    root: { class: 'border-round-lg' },
                    header: { class: 'surface-100 p-2' },
                    paginator: { class: 'surface-100 border-top-1 surface-border p-2' },
                }"
                stripedRows
                paginator
                :rows="rowsPerPage"
                :rowsPerPageOptions="[5, 10, 15, 20]"
                :totalRecords="computedErrors.length"
            >
                <Column field="line" header="Ligne" style="width: 80px">
                    <template #body="{ data }">
                        <Badge :value="data.line" severity="info" class="font-bold" />
                    </template>
                </Column>

                <Column
                    v-for="(field, index) in fields"
                    :key="field.key"
                    :field="field.key"
                    :header="field.label"
                    style="min-width: 150px"
                >
                    <template #body="{ data }">
                        <div class="cell-container">
                            <div
                                class="cell-content"
                                :class="{ 'p-invalid': data.errors && data.errors[field.key], 'arabic-text': field.key.includes('_ar') }"
                            >
                                {{ formatDisplayDate(data.parsedData[index], field.type) || 'N/A' }}
                            </div>
                            <small v-if="data.errors && data.errors[field.key]" class="p-error block mt-1">
                                <i class="pi pi-exclamation-circle mr-1"></i>
                                {{ data.errors[field.key].join(', ') }}
                            </small>
                        </div>
                    </template>
                </Column>

                <Column header="Actions" style="width: 150px">
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Button
                                icon="pi pi-pencil"
                                class="p-button-rounded p-button-primary p-button-text"
                                @click="openCorrectionForm(data)"
                                v-tooltip.top="'Corriger cette ligne'"
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

        <div v-else class="flex flex-column align-items-center justify-content-center gap-3 p-5">
            <i class="pi pi-check-circle text-green-500 text-5xl"></i>
            <span class="text-xl font-medium text-900">Aucune erreur détectée</span>
        </div>

        <template #footer>
            <div class="flex flex-column md:flex-row justify-content-between align-items-center gap-3">
                <small class="text-600 flex align-items-center">
                    <i class="pi pi-info-circle mr-2"></i>
                    Cliquez sur <i class="pi pi-pencil mx-1"></i> pour corriger une ligne
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

        <CorrectionImportError
            :visible="correctionFormVisible"
            :errorData="selectedError"
            :fields="fields"
            :listOptions="listOptions"
            @update:visible="correctionFormVisible = $event"
            @corrected="handleCorrectedData"
        />
    </Dialog>
</template>

<script>
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';
import Badge from 'primevue/badge';
import { useToast } from 'primevue/usetoast';
import ExcelJS from 'exceljs';
import CorrectionImportError from '@/Components/Atfp/Utilisateurs/PersonnelsDirectionCentrale/CorrectionImportError.vue';

export default {
    components: {
        Button,
        Column,
        DataTable,
        Dialog,
        Tag,
        Badge,
        CorrectionImportError,
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
    setup(props, { emit }) {
        const toast = useToast();
        const loadingLists = ref(false);
        const correctionFormVisible = ref(false);
        const selectedError = ref(null);
        const rowsPerPage = ref(10);
        const listOptions = ref({
            gouvernorat_fr: [],
            genre_fr: [
                { nom_fr: 'Homme', nom_ar: 'ذكر' },
                { nom_fr: 'Femme', nom_ar: 'أنثى' },
                { nom_fr: 'Autre', nom_ar: 'أخرى' },
            ],
            etat_civil_fr: [
                { nom_fr: 'Célibataire', nom_ar: 'أعزب' },
                { nom_fr: 'Marié', nom_ar: 'متزوج' },
                { nom_fr: 'Divorcé', nom_ar: 'مطلق' },
                { nom_fr: 'Veuf', nom_ar: 'أرمل' },
            ],
            roles: [],
        });

        const reactiveErrors = ref(props.errors.map(error => ({
            ...error,
            parsedData: error.parsedData || error.data.split('|').map(item => item.trim() === '' ? null : item.trim()),
        })));

        watch(
            () => props.errors,
            (newErrors) => {
                reactiveErrors.value = newErrors.map(error => ({
                    ...error,
                    parsedData: error.parsedData || error.data.split('|').map(item => item.trim() === '' ? null : item.trim()),
                }));
            },
            { deep: true }
        );

        const fetchRoles = async () => {
            loadingLists.value = true;
            try {
                const response = await axios.get('/api/roles', {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });
                if (!Array.isArray(response.data.data)) {
                    throw new Error('Les données des rôles ne sont pas au format attendu.');
                }
                listOptions.value.roles = response.data.data
                    .filter(role => role.name !== 'SuperAdmin')
                    .map(role => ({
                        name: role.name,
                        name_ar: role.name_ar || role.name,
                    }));
                if (listOptions.value.roles.length === 0) {
                    toast.add({
                        severity: 'warn',
                        summary: 'Avertissement',
                        detail: 'Aucun rôle actif disponible. Veuillez vérifier la configuration des rôles.',
                        life: 5000,
                    });
                }
            } catch (error) {
                console.error('Erreur fetchRoles:', error);
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Erreur lors de la récupération des rôles.',
                    life: 5000,
                });
                if (error.response?.status === 401) {
                    emit('update:visible', false);
                    window.location.href = '/login';
                }
            } finally {
                loadingLists.value = false;
            }
        };

        const fetchLists = async () => {
            loadingLists.value = true;
            try {
                const response = await axios.get('/api/personnels_atfp/lists', {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });
                if (!response.data.success) {
                    throw new Error(response.data.message || 'Erreur lors de la récupération des listes.');
                }
                listOptions.value.gouvernorat_fr = response.data.data['Gouvernorats'] || [];
            } catch (error) {
                console.error('Fetch lists error:', error);
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Erreur lors de la récupération des gouvernorats.',
                    life: 5000,
                });
                if (error.response?.status === 401) {
                    emit('update:visible', false);
                    window.location.href = '/login';
                }
            } finally {
                loadingLists.value = false;
            }
        };

        watch(
            () => props.visible,
            async (newVal) => {
                if (newVal) {
                    await Promise.all([fetchRoles(), fetchLists()]);
                }
            },
            { immediate: true }
        );

        return {
            toast,
            listOptions,
            loadingLists,
            reactiveErrors,
            correctionFormVisible,
            selectedError,
            rowsPerPage,
        };
    },
    data() {
        return {
            loading: false,
            submitting: false,
            fields: [
                { key: 'nom_fr', label: 'Nom FR', type: 'text' },
                { key: 'prenom_fr', label: 'Prénom FR', type: 'text' },
                { key: 'nom_ar', label: 'Nom AR', type: 'text' },
                { key: 'prenom_ar', label: 'Prénom AR', type: 'text' },
                { key: 'matricule', label: 'Matricule', type: 'text' },
                { key: 'cin', label: 'CIN', type: 'text' },
                { key: 'date_cin', label: 'Date CIN', type: 'date' },
                { key: 'lieu_cin_fr', label: 'Lieu CIN FR', type: 'text' },
                { key: 'lieu_cin_ar', label: 'Lieu CIN AR', type: 'text' },
                { key: 'date_naissance', label: 'Date Naissance', type: 'date' },
                { key: 'lieu_naissance_fr', label: 'Lieu Naissance FR', type: 'text' },
                { key: 'lieu_naissance_ar', label: 'Lieu Naissance AR', type: 'text' },
                { key: 'nationalite_fr', label: 'Nationalité FR', type: 'text' },
                { key: 'nationalite_ar', label: 'Nationalité AR', type: 'text' },
                { key: 'date_recrutement', label: 'Date Recrutement', type: 'date' },
                { key: 'date_fin_service', label: 'Date Fin Service', type: 'date' },
                { key: 'genre_fr', label: 'Genre FR', type: 'list' },
                { key: 'adresse_fr', label: 'Adresse FR', type: 'text' },
                { key: 'adresse_ar', label: 'Adresse AR', type: 'text' },
                { key: 'gouvernorat_fr', label: 'Gouvernorat FR', type: 'list' },
                { key: 'delegation_fr', label: 'Délégation FR', type: 'text' },
                { key: 'delegation_ar', label: 'Délégation AR', type: 'text' },
                { key: 'telephone_1', label: 'Téléphone 1', type: 'text' },
                { key: 'telephone_2', label: 'Téléphone 2', type: 'text' },
                { key: 'etat_civil_fr', label: 'État Civil FR', type: 'list' },
                { key: 'email', label: 'Email', type: 'email', required: true },
                { key: 'roles', label: 'Rôles', type: 'list', required: true, optionLabel: 'name', optionValue: 'name' },
            ],
        };
    },
    computed: {
        computedErrors() {
            return this.reactiveErrors;
        },
    },
    methods: {
        excelSerialToDate(serial) {
            if (!serial || isNaN(serial) || serial <= 0) return null;
            const excelEpoch = new Date(1899, 11, 30);
            const date = new Date(excelEpoch.getTime() + serial * 24 * 60 * 60 * 1000);
            if (isNaN(date.getTime())) return null;
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        isValidDate(dateStr) {
            if (!dateStr) return false;
            const parsed = this.parseDate(dateStr);
            if (!parsed) return false;
            const [year, month, day] = parsed.split('-').map(Number);
            const date = new Date(year, month - 1, day);
            return (
                !isNaN(date.getTime()) &&
                date.getFullYear() === year &&
                date.getMonth() + 1 === month &&
                date.getDate() === day
            );
        },
        parseDate(value) {
            if (!value) return null;

            // Gestion du format JJ/MM/AAAA ou MM/JJ/AAAA
            const dateRegex = /^(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})$/;
            if (dateRegex.test(value)) {
                const [, part1, part2, year] = value.match(dateRegex);
                // Essayer JJ/MM/AAAA
                let day = part1.padStart(2, '0');
                let month = part2.padStart(2, '0');
                let formatted = `${year}-${month}-${day}`;
                if (this.isValidDate(formatted)) return formatted;

                // Essayer MM/JJ/AAAA
                day = part2.padStart(2, '0');
                month = part1.padStart(2, '0');
                formatted = `${year}-${month}-${day}`;
                if (this.isValidDate(formatted)) return formatted;

                return null;
            }

            // Gestion des numéros de série Excel
            if (!isNaN(value) && !/^\d{4}-\d{2}-\d{2}$/.test(value)) {
                return this.excelSerialToDate(parseFloat(value));
            }

            // Gestion du format AAAA-MM-JJ
            if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
                return value;
            }

            // Tentative de parsing avec Date
            const parsed = new Date(value);
            if (!isNaN(parsed.getTime())) {
                const year = parsed.getFullYear();
                const month = String(parsed.getMonth() + 1).padStart(2, '0');
                const day = String(parsed.getDate()).padStart(2, '0');
                const formatted = `${year}-${month}-${day}`;
                return this.isValidDate(formatted) ? formatted : null;
            }

            return null;
        },
        formatDisplayDate(value, fieldType) {
            if (fieldType !== 'date' || !value) return value;
            const parsed = this.parseDate(value);
            if (!parsed) return value; // Affiche la valeur originale si non valide
            const [year, month, day] = parsed.split('-');
            return `${day}/${month}/${year}`; // Affiche au format JJ/MM/AAAA
        },
        openCorrectionForm(error) {
            this.selectedError = {
                ...error,
                parsedData: error.parsedData.map((val, index) =>
                    this.fields[index].type === 'date' ? this.parseDate(val) : val
                ),
            };
            this.correctionFormVisible = true;
        },
        handleCorrectedData(correctedData) {
            const index = this.computedErrors.findIndex(e => e.line === correctedData.line);
            if (index !== -1) {
                this.reactiveErrors[index] = { ...correctedData };
                this.retryImport(correctedData);
            }
            this.correctionFormVisible = false;
            this.selectedError = null;
        },
        async retryImport(error) {
            error.loading = true;
            try {
                let parsedData = [...error.parsedData];
                parsedData = parsedData.concat(Array(27).fill(null)).slice(0, 27);

                const lineData = {
                    nom_fr: parsedData[0] || null,
                    prenom_fr: parsedData[1] || null,
                    nom_ar: parsedData[2] || null,
                    prenom_ar: parsedData[3] || null,
                    matricule: parsedData[4] || null,
                    cin: parsedData[5] || null,
                    date_cin: this.parseDate(parsedData[6]) || null,
                    lieu_cin_fr: parsedData[7] || null,
                    lieu_cin_ar: parsedData[8] || null,
                    date_naissance: this.parseDate(parsedData[9]) || null,
                    lieu_naissance_fr: parsedData[10] || null,
                    lieu_naissance_ar: parsedData[11] || null,
                    nationalite_fr: parsedData[12] || 'Tunisienne',
                    nationalite_ar: parsedData[13] || 'تونسية',
                    date_recrutement: this.parseDate(parsedData[14]) || null,
                    date_fin_service: this.parseDate(parsedData[15]) || null,
                    genre_fr: parsedData[16] || null,
                    adresse_fr: parsedData[17] || null,
                    adresse_ar: parsedData[18] || null,
                    gouvernorat_fr: parsedData[19] || null,
                    delegation_fr: parsedData[20] || null,
                    delegation_ar: parsedData[21] || null,
                    telephone_1: parsedData[22] ? parsedData[22].replace(/[^+\d]/g, '') : null,
                    telephone_2: parsedData[23] ? parsedData[23].replace(/[^+\d]/g, '') : null,
                    etat_civil_fr: parsedData[24] || null,
                    email: parsedData[25] || null,
                    roles: parsedData[26] ? parsedData[26].split(',').map(r => r.trim()).filter(r => r) : [],
                    statut: 'Actif',
                };

                const errors = {};
                if (!lineData.email) errors.email = ["L'email est requis."];
                else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(lineData.email)) errors.email = ["L'email est invalide."];
                if (!lineData.roles || lineData.roles.length === 0) errors.roles = ['Au moins un rôle doit être sélectionné.'];
                if (lineData.cin && !/^[A-Za-z0-9]{8}$/.test(lineData.cin)) errors.cin = ['Le CIN doit contenir exactement 8 caractères alphanumériques.'];
                if (lineData.telephone_1 && !/^\+?\d{8,15}$/.test(lineData.telephone_1)) errors.telephone_1 = ['Le numéro de téléphone 1 doit contenir entre 8 et 15 chiffres.'];
                if (lineData.telephone_2 && !/^\+?\d{8,15}$/.test(lineData.telephone_2)) errors.telephone_2 = ['Le numéro de téléphone 2 doit contenir entre 8 et 15 chiffres.'];
                if (lineData.gouvernorat_fr && !this.listOptions.gouvernorat_fr.some(opt => opt.nom_fr === lineData.gouvernorat_fr)) {
                    errors.gouvernorat_fr = ["Le gouvernorat n'est pas valide."];
                }
                if (lineData.etat_civil_fr && !['Célibataire', 'Marié', 'Divorcé', 'Veuf'].includes(lineData.etat_civil_fr)) {
                    errors.etat_civil_fr = ["L'état civil n'est pas valide."];
                }
                if (lineData.genre_fr && !['Homme', 'Femme', 'Autre'].includes(lineData.genre_fr)) {
                    errors.genre_fr = ['Le genre doit être Homme, Femme ou Autre.'];
                }
                if (lineData.date_cin && !this.isValidDate(lineData.date_cin)) {
                    errors.date_cin = ['La date CIN doit être au format AAAA-MM-JJ et valide.'];
                } else if (lineData.date_cin) {
                    const cinDate = new Date(lineData.date_cin);
                    if (cinDate > new Date()) {
                        errors.date_cin = ['La date CIN doit être antérieure à aujourd\'hui.'];
                    }
                }
                if (lineData.date_naissance && !this.isValidDate(lineData.date_naissance)) {
                    errors.date_naissance = ['La date de naissance doit être au format AAAA-MM-JJ et valide.'];
                } else if (lineData.date_naissance) {
                    const birthDate = new Date(lineData.date_naissance);
                    if (birthDate > new Date()) {
                        errors.date_naissance = ['La date de naissance doit être antérieure à aujourd\'hui.'];
                    }
                }
                if (lineData.date_recrutement && !this.isValidDate(lineData.date_recrutement)) {
                    errors.date_recrutement = ['La date de recrutement doit être au format AAAA-MM-JJ et valide.'];
                } else if (lineData.date_recrutement) {
                    const hireDate = new Date(lineData.date_recrutement);
                    if (hireDate > new Date()) {
                        errors.date_recrutement = ['La date de recrutement doit être antérieure ou égale à aujourd\'hui.'];
                    }
                }
                if (lineData.date_fin_service && !this.isValidDate(lineData.date_fin_service)) {
                    errors.date_fin_service = ['La date de fin de service doit être au format AAAA-MM-JJ et valide.'];
                }
                if (lineData.date_naissance && lineData.date_cin && lineData.date_cin < lineData.date_naissance) {
                    errors.date_cin = ['La date CIN doit être postérieure ou égale à la date de naissance.'];
                }
                if (lineData.date_fin_service && lineData.date_recrutement && lineData.date_fin_service < lineData.date_recrutement) {
                    errors.date_fin_service = ['La date de fin de service doit être postérieure ou égale à la date de recrutement.'];
                }

                if (Object.keys(errors).length > 0) {
                    error.errors = { ...error.errors, ...errors };
                    error.message = 'Veuillez corriger les erreurs dans le formulaire.';
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Veuillez corriger les erreurs dans le formulaire.',
                        life: 5000,
                    });
                    return false;
                }

                Object.keys(lineData).forEach(key => {
                    if (lineData[key] === '' || lineData[key] === null) {
                        if (!['email', 'roles'].includes(key)) {
                            delete lineData[key];
                        }
                    }
                });

                if (lineData.email) {
                    const emailCheck = await axios.post('/api/personnels_atfp/check-email', { email: lineData.email }, {
                        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
                    });
                    if (emailCheck.data.exists) {
                        error.errors = { ...error.errors, email: ['Cet email est déjà utilisé.'] };
                        error.message = 'Veuillez corriger les erreurs dans le formulaire.';
                        this.toast.add({
                            severity: 'error',
                            summary: 'Erreur',
                            detail: 'Cet email est déjà utilisé.',
                            life: 5000,
                        });
                        return false;
                    }
                }

                const response = await axios.post('/api/personnels_atfp/bulk', {
                    action: 'import-line',
                    lineData: lineData,
                }, {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });

                if (response.data.success) {
                    this.$emit('line-imported', lineData);
                    this.$emit('update:errors', this.computedErrors.filter(e => e.line !== error.line));
                    this.toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: `Ligne ${error.line} réimportée avec succès.`,
                        life: 3000,
                    });
                    return true;
                }
            } catch (error) {
                console.error('Erreur lors de la réimportation:', error);
                let errorMessage = error.response?.data?.message || error.message;
                let serverErrors = error.response?.data?.errors || null;

                if (error.response?.status === 422 && serverErrors) {
                    error.errors = { ...error.errors, ...serverErrors };
                    errorMessage = 'Veuillez corriger les erreurs dans le formulaire.';
                } else if (error.response?.status === 500) {
                    errorMessage = 'Erreur serveur lors de l\'importation. Veuillez réessayer ou contacter l\'administrateur.';
                    error.errors = { ...error.errors };
                } else {
                    errorMessage = this.translateError(errorMessage);
                    error.errors = { ...error.errors };
                }

                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: errorMessage,
                    life: 5000,
                });
                return false;
            } finally {
                error.loading = false;
            }
        },
        cancelAllErrors() {
            this.$emit('update:errors', []);
            this.toast.add({
                severity: 'warn',
                summary: 'Ignoré',
                detail: `Toutes les erreurs (${this.computedErrors.length}) ont été ignorées.`,
                life: 3000,
            });
        },
        removeError(error) {
            this.$emit('update:errors', this.computedErrors.filter(e => e.line !== error.line));
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
                if (this.computedErrors.length === 0) {
                    this.toast.add({
                        severity: 'warn',
                        summary: 'Aucune correction',
                        detail: 'Aucune erreur à sauvegarder.',
                        life: 3000,
                    });
                    this.closePopup();
                    return;
                }

                const headers = this.fields.map(f => f.label).concat(['Erreur']);
                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet('Corrections');

                worksheet.addRow(headers);
                const headerRow = worksheet.getRow(1);
                headerRow.font = { bold: true, color: { argb: 'FFFFFFFF' } };
                headerRow.fill = {
                    type: 'pattern',
                    pattern: 'solid',
                    fgColor: { argb: 'FF3B82F6' },
                };
                headerRow.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };

                this.computedErrors.forEach((error, index) => {
                    const rowData = (error.parsedData || Array(27).fill(null)).slice(0, 27).map((val, i) =>
                        this.fields[i].type === 'date' ? this.formatDisplayDate(val, this.fields[i].type) : val
                    );
                    const errorMessages = error.errors
                        ? Object.entries(error.errors)
                            .map(([key, messages]) => `${this.fields.find(f => f.key === key)?.label || key}: ${Array.isArray(messages) ? messages.join('; ') : messages}`)
                            .join('; ')
                        : error.message || 'Erreur inconnue';
                    const fullRow = [...rowData, errorMessages];
                    const row = worksheet.addRow(fullRow);
                    row.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };

                    // Appliquer le style à toutes les cellules jusqu'à la colonne 27 (Rôles), y compris les cellules vides
                    for (let col = 1; col <= 27; col++) {
                        const cell = row.getCell(col);
                        cell.fill = {
                            type: 'pattern',
                            pattern: 'solid',
                            fgColor: { argb: (index % 2 === 0) ? 'FFE6F0FA' : 'FFF5F8FD' },
                        };
                        cell.border = {
                            top: { style: 'thin', color: { argb: 'FFD3D3D3' } },
                            bottom: { style: 'thin', color: { argb: 'FFD3D3D3' } },
                        };
                        // Appliquer la police pour les colonnes en arabe
                        if ([3, 4, 9, 12, 14, 19, 22].includes(col)) {
                            cell.font = { name: 'Montserrat-Arabic-Regular' };
                        }
                    }

                    // Style spécifique pour la colonne "Erreur" (colonne 28)
                    const errorCell = row.getCell(28);
                    errorCell.fill = {
                        type: 'pattern',
                        pattern: 'solid',
                        fgColor: { argb: 'FFFF0000' },
                    };
                    errorCell.font = { color: { argb: 'FFFFFFFF' } };
                });

                worksheet.columns.forEach(column => {
                    let maxLength = 0;
                    column.eachCell({ includeEmpty: true }, cell => {
                        const columnLength = cell.value ? String(cell.value).length : 10;
                        if (columnLength > maxLength) {
                            maxLength = columnLength;
                        }
                    });
                    column.width = Math.min(Math.max(maxLength + 2, 10), 50);
                });

                const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
                const fileName = `corrections_personnels_atfp_${timestamp}.xlsx`;
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
                    detail: 'Les corrections ont été sauvegardées dans ' + fileName,
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
        translateError(message) {
            const translations = {
                'Le nom en français est requis.': 'Le nom en français est requis.',
                'Le prénom en français est requis.': 'Le prénom en français est requis.',
                'Le nom en arabe est requis.': 'Le nom en arabe est requis.',
                'Le prénom en arabe est requis.': 'Le prénom en arabe est requis.',
                'Le CIN est requis.': 'Le CIN est requis.',
                'Le CIN doit contenir exactement 8 chiffres.': 'Le CIN doit contenir exactement 8 caractères alphanumériques.',
                'Ce CIN est déjà utilisé.': 'Ce CIN est déjà utilisé.',
                'The cin has already been taken.': 'Ce CIN est déjà utilisé.',
                'Le matricule est requis.': 'Le matricule est requis.',
                'Ce matricule est déjà utilisé.': 'Ce matricule est déjà utilisé.',
                'The matricule has already been taken.': 'Ce matricule est déjà utilisé.',
                'Le genre en français est requis.': 'Le genre en français est requis.',
                'Le genre doit être Homme ou Femme.': 'Le genre doit être Homme, Femme ou Autre.',
                'Le genre en arabe doit correspondre au genre en français.': 'Le genre en arabe doit correspondre au genre en français.',
                'Au moins un rôle doit être sélectionné.': 'Au moins un rôle doit être sélectionné.',
                "L'email est requis.": "L'email est requis.",
                'The email has already been taken.': 'Cet email est déjà utilisé.',
                "L'email est invalide.": "L'email est invalide.",
                'Le numéro de téléphone 1 doit contenir entre 8 et 15 chiffres.': 'Le numéro de téléphone 1 doit contenir entre 8 et 15 chiffres.',
                'Le numéro de téléphone 2 doit contenir entre 8 et 15 chiffres.': 'Le numéro de téléphone 2 doit contenir entre 8 et 15 chiffres.',
                "L'état civil n'est pas valide.": "L'état civil n'est pas valide.",
                'La date CIN doit être au format AAAA-MM-JJ.': 'La date CIN doit être au format AAAA-MM-JJ et valide.',
                'La date de naissance doit être au format AAAA-MM-JJ.': 'La date de naissance doit être au format AAAA-MM-JJ et valide.',
                'La date de recrutement doit être au format AAAA-MM-JJ.': 'La date de recrutement doit être au format AAAA-MM-JJ et valide.',
                'La date de fin de service doit être au format AAAA-MM-JJ.': 'La date de fin de service doit être au format AAAA-MM-JJ et valide.',
                'La date CIN doit être postérieure ou égale à la date de naissance.': 'La date CIN doit être postérieure ou égale à la date de naissance.',
                'La date de fin de service doit être postérieure ou égale à la date de recrutement.': 'La date de fin de service doit être postérieure ou égale à la date de recrutement.',
                'Liste Gouvernorats non disponible.': "Le gouvernorat n'est pas valide.",
                'Valeur invalide pour gouvernorat_fr': "Le gouvernorat n'est pas valide.",
            };
            return translations[message] || message;
        },
    },
};
</script>

<style>
@font-face {
    font-family: 'Montserrat-Arabic-Regular';
    src: url('/fonts/Montserrat-Arabic-Regular.otf') format('opentype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}

.arabic-text {
    font-family: 'Montserrat-Arabic-Regular', sans-serif;
    direction: rtl;
    text-align: right;
}

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

.cell-container {
    display: flex;
    flex-direction: column;
}

.cell-content {
    padding: 0.5rem;
}

.cell-content.p-invalid {
    border: 1px solid var(--red-500);
    border-radius: 6px;
}

.p-error {
    font-size: 0.75rem;
    color: var(--red-500);
    display: flex;
    align-items: center;
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

    .cell-container {
        margin-bottom: 0.75rem;
    }
}
</style>
