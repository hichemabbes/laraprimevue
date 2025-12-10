<template>
    <Dialog
        :visible="showViewDialog"
        @update:visible="$emit('update:showViewDialog', $event)"
        :modal="true"
        :style="{ width: '1000px' }"
        :breakpoints="{ '960px': '80vw', '640px': '95vw' }"
        :pt="{
            root: 'border-round-lg shadow-4',
            mask: { style: 'backdrop-filter: blur(3px)' },
            header: {
                class: 'bg-custom-dark text-white border-bottom-1 border-gray-700 p-3',
            },
            content: { class: 'bg-surface-0 p-4' },
            footer: {
                class: 'bg-custom-dark text-white border-top-1 border-gray-700 p-3',
            },
        }"
    >
        <!-- Header -->
        <template #header>
            <div class="flex align-items-center gap-3">
                <i class="pi pi-building text-white text-3xl"></i>
                <span class="font-bold text-2xl text-white">
                    {{ selectedCentre?.nom_fr || 'Détails du Centre' }}
                </span>
            </div>
        </template>

        <!-- Content -->
        <div v-if="selectedCentre" class="flex flex-column gap-5">
            <!-- Logo and Basic Info Card -->
            <Card class="shadow-2 border-round-lg">
                <template #content>
                    <div
                        class="flex flex-column md:flex-row gap-5 align-items-center"
                    >
                        <!-- Logo -->
                        <div class="flex-shrink-0">
                            <img
                                v-if="selectedCentre.logo"
                                :src="selectedCentre.logo"
                                alt="Logo du Centre"
                                class="border-round-lg shadow-2"
                                style="
                                    width: 150px;
                                    height: 150px;
                                    object-fit: contain;
                                "
                                @error="onImageError"
                            />
                            <div
                                v-else
                                class="flex align-items-center justify-content-center border-round-lg bg-surface-100"
                                style="width: 150px; height: 150px"
                            >
                                <i
                                    class="pi pi-image text-5xl text-surface-400"
                                ></i>
                            </div>
                        </div>
                        <!-- Basic Info -->
                        <div class="flex-grow-1">
                            <h3
                                class="text-xl font-semibold text-primary-700 mb-3"
                            >
                                {{ selectedCentre.nom_fr }}
                            </h3>
                            <div class="grid">
                                <div class="col-12 md:col-6">
                                    <p class="m-0">
                                        <strong>Code :</strong>
                                        {{ selectedCentre.code || '-' }}
                                    </p>
                                    <p class="m-0">
                                        <strong>Gouvernorat :</strong>
                                        {{
                                            selectedCentre.gouvernorat_fr || '-'
                                        }}
                                    </p>
                                    <p class="m-0">
                                        <strong>Type :</strong>
                                        {{ getTypeLabel(selectedCentre.type) }}
                                    </p>
                                </div>
                                <div class="col-12 md:col-6">
                                    <p class="m-0">
                                        <strong>Statut :</strong>
                                        <Tag
                                            :value="selectedCentre.statut_fr"
                                            :severity="
                                                selectedCentre.statut_fr ===
                                                'Actif'
                                                    ? 'success'
                                                    : 'danger'
                                            "
                                            class="ml-2"
                                        />
                                    </p>
                                    <p class="m-0">
                                        <strong>Téléphone :</strong>
                                        {{ selectedCentre.telephone_1 || '-' }}
                                    </p>
                                    <p class="m-0">
                                        <strong>Email :</strong>
                                        {{ selectedCentre.email || '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Detailed Information -->
            <TabView class="tabview-custom">
                <!-- Informations Générales -->
                <TabPanel header="Informations Générales">
                    <div class="flex flex-column gap-5">
                        <!-- Top Section: French and Common Information -->
                        <div class="grid equal-height-cards">
                            <!-- French Information -->
                            <div class="col-12 md:col-6">
                                <Card class="shadow-1 border-round-lg">
                                    <template #content>
                                        <h3
                                            class="text-lg font-semibold text-primary-600 mb-4"
                                        >
                                            Informations en Français
                                        </h3>
                                        <div class="field mb-4">
                                            <label
                                                class="font-medium text-primary-600"
                                                >Nom</label
                                            >
                                            <p class="text-700">
                                                {{
                                                    selectedCentre.nom_fr || '-'
                                                }}
                                            </p>
                                        </div>
                                        <div class="field mb-4">
                                            <label
                                                class="font-medium text-primary-600"
                                                >Adresse</label
                                            >
                                            <p class="text-700">
                                                {{
                                                    selectedCentre.adresse_fr ||
                                                    '-'
                                                }}
                                            </p>
                                        </div>
                                        <div class="field mb-4">
                                            <label
                                                class="font-medium text-primary-600"
                                                >Gouvernorat</label
                                            >
                                            <p class="text-700">
                                                {{
                                                    selectedCentre.gouvernorat_fr ||
                                                    '-'
                                                }}
                                            </p>
                                        </div>
                                        <div class="field mb-4">
                                            <label
                                                class="font-medium text-primary-600"
                                                >Économat</label
                                            >
                                            <p class="text-700">
                                                {{
                                                    selectedCentre.economat
                                                        ?.nom_fr || '-'
                                                }}
                                            </p>
                                        </div>
                                        <div class="field mb-4">
                                            <label
                                                class="font-medium text-primary-600"
                                                >Observation</label
                                            >
                                            <p class="text-700">
                                                {{
                                                    selectedCentre.observation_fr ||
                                                    '-'
                                                }}
                                            </p>
                                        </div>
                                    </template>
                                </Card>
                            </div>
                            <!-- Common Information -->
                            <div class="col-12 md:col-6">
                                <Card class="shadow-1 border-round-lg">
                                    <template #content>
                                        <h3
                                            class="text-lg font-semibold text-primary-600 mb-4"
                                        >
                                            Informations Communes
                                        </h3>
                                        <div class="field mb-4">
                                            <label
                                                class="font-medium text-primary-600"
                                                >Téléphone 1</label
                                            >
                                            <p class="text-700">
                                                {{
                                                    selectedCentre.telephone_1 ||
                                                    '-'
                                                }}
                                            </p>
                                        </div>
                                        <div class="field mb-4">
                                            <label
                                                class="font-medium text-primary-600"
                                                >Téléphone 2</label
                                            >
                                            <p class="text-700">
                                                {{
                                                    selectedCentre.telephone_2 ||
                                                    '-'
                                                }}
                                            </p>
                                        </div>
                                        <div class="field mb-4">
                                            <label
                                                class="font-medium text-primary-600"
                                                >Fax 1</label
                                            >
                                            <p class="text-700">
                                                {{
                                                    selectedCentre.fax_1 || '-'
                                                }}
                                            </p>
                                        </div>
                                        <div class="field mb-4">
                                            <label
                                                class="font-medium text-primary-600"
                                                >Fax 2</label
                                            >
                                            <p class="text-700">
                                                {{
                                                    selectedCentre.fax_2 || '-'
                                                }}
                                            </p>
                                        </div>
                                        <div class="field mb-4">
                                            <label
                                                class="font-medium text-primary-600"
                                                >Email</label
                                            >
                                            <p class="text-700">
                                                {{
                                                    selectedCentre.email || '-'
                                                }}
                                            </p>
                                        </div>
                                    </template>
                                </Card>
                            </div>
                            <!-- Common Dates -->
                            <div class="col-12">
                                <Card class="shadow-1 border-round-lg">
                                    <template #content>
                                        <div class="grid">
                                            <div class="col-12 md:col-4">
                                                <div class="field mb-4">
                                                    <label
                                                        class="font-medium text-primary-600"
                                                        >Date de création</label
                                                    >
                                                    <p class="text-700">
                                                        {{
                                                            formatDate(
                                                                selectedCentre.date_creation
                                                            )
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-12 md:col-4">
                                                <div class="field mb-4">
                                                    <label
                                                        class="font-medium text-primary-600"
                                                        >Date d'ouverture</label
                                                    >
                                                    <p class="text-700">
                                                        {{
                                                            formatDate(
                                                                selectedCentre.date_ouverture
                                                            )
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-12 md:col-4">
                                                <div class="field mb-4">
                                                    <label
                                                        class="font-medium text-primary-600"
                                                        >Date de
                                                        fermeture</label
                                                    >
                                                    <p class="text-700">
                                                        {{
                                                            formatDate(
                                                                selectedCentre.date_fermeture
                                                            )
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </Card>
                            </div>
                        </div>
                        <!-- Bottom Section: Arabic Information -->
                        <div class="grid">
                            <div class="col-12">
                                <Card class="shadow-1 border-round-lg">
                                    <template #content>
                                        <h3
                                            class="text-lg font-semibold text-primary-600 mb-4 text-right font-arabic"
                                        >
                                            المعلومات بالعربية
                                        </h3>
                                        <div class="grid">
                                            <div class="col-12 md:col-6">
                                                <div class="field mb-4">
                                                    <label
                                                        class="font-medium text-primary-600 text-right block font-arabic"
                                                        >الاسم</label
                                                    >
                                                    <p
                                                        class="text-700 text-right font-arabic"
                                                    >
                                                        {{
                                                            selectedCentre.nom_ar ||
                                                            '-'
                                                        }}
                                                    </p>
                                                </div>
                                                <div class="field mb-4">
                                                    <label
                                                        class="font-medium text-primary-600 text-right block font-arabic"
                                                        >العنوان</label
                                                    >
                                                    <p
                                                        class="text-700 text-right font-arabic"
                                                    >
                                                        {{
                                                            selectedCentre.adresse_ar ||
                                                            '-'
                                                        }}
                                                    </p>
                                                </div>
                                                <div class="field mb-4">
                                                    <label
                                                        class="font-medium text-primary-600 text-right block font-arabic"
                                                        >المحافظة</label
                                                    >
                                                    <p
                                                        class="text-700 text-right font-arabic"
                                                    >
                                                        {{
                                                            selectedCentre.gouvernorat_ar ||
                                                            '-'
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-12 md:col-6">
                                                <div class="field mb-4">
                                                    <label
                                                        class="font-medium text-primary-600 text-right block font-arabic"
                                                        >الإقتصادية</label
                                                    >
                                                    <p
                                                        class="text-700 text-right font-arabic"
                                                    >
                                                        {{
                                                            selectedCentre
                                                                .economat
                                                                ?.nom_ar || '-'
                                                        }}
                                                    </p>
                                                </div>
                                                <div class="field mb-4">
                                                    <label
                                                        class="font-medium text-primary-600 text-right block font-arabic"
                                                        >الملاحظات</label
                                                    >
                                                    <p
                                                        class="text-700 text-right font-arabic"
                                                    >
                                                        {{
                                                            selectedCentre.observation_ar ||
                                                            '-'
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </Card>
                            </div>
                        </div>
                    </div>
                </TabPanel>

                <!-- Spécialités -->
                <TabPanel header="Spécialités">
                    <Card class="shadow-2 border-round-lg">
                        <template #content>
                            <DataTable
                                :value="specialitesDetails"
                                responsiveLayout="scroll"
                                :paginator="specialitesDetails.length > 5"
                                :rows="5"
                                class="p-datatable-sm"
                                stripedRows
                            >
                                <template #empty>
                                    <div class="text-center py-4">
                                        <i
                                            class="pi pi-inbox text-4xl text-surface-400 mb-2"
                                        ></i>
                                        <p class="text-600">
                                            Aucune spécialité associée
                                        </p>
                                    </div>
                                </template>
                                <Column
                                    field="code"
                                    header="Code"
                                    sortable
                                    style="min-width: 10rem"
                                ></Column>
                                <Column
                                    field="nom_fr"
                                    header="Nom (FR)"
                                    sortable
                                    style="min-width: 15rem"
                                ></Column>
                                <Column
                                    field="nom_ar"
                                    header="Nom (AR)"
                                    sortable
                                    style="min-width: 15rem"
                                ></Column>
                                <Column
                                    field="duree_formation"
                                    header="Durée de formation"
                                    sortable
                                    style="min-width: 12rem"
                                >
                                    <template #body="{ data }">
                                        {{
                                            formatDureeFormation(
                                                data.duree_formation
                                            )
                                        }}
                                    </template>
                                </Column>
                                <Column
                                    field="homologation_fr"
                                    header="Homologation"
                                    sortable
                                    style="min-width: 12rem"
                                >
                                    <template #body="{ data }">
                                        <Tag
                                            :value="
                                                data.homologation_fr ||
                                                'Non défini'
                                            "
                                            :severity="
                                                getSeverity(
                                                    data.homologation_fr
                                                )
                                            "
                                        />
                                    </template>
                                </Column>
                                <Column
                                    header="Actions"
                                    style="min-width: 8rem"
                                >
                                    <template #body="{ data }">
                                        <Button
                                            icon="pi pi-trash"
                                            class="p-button-rounded p-button-danger p-button-text"
                                            v-tooltip="'Dissocier'"
                                            @click="
                                                dissociateSpecialite(
                                                    selectedCentre.id,
                                                    data.id
                                                )
                                            "
                                        />
                                    </template>
                                </Column>
                            </DataTable>
                        </template>
                    </Card>
                </TabPanel>

                <!-- Historique -->
                <TabPanel header="Historique">
                    <Card class="shadow-2 border-round-lg">
                        <template #content>
                            <DataTable
                                :value="historique"
                                responsiveLayout="scroll"
                                :paginator="historique.length > 5"
                                :rows="5"
                                class="p-datatable-sm"
                                stripedRows
                            >
                                <template #empty>
                                    <div class="text-center py-4">
                                        <i
                                            class="pi pi-inbox text-4xl text-surface-400 mb-2"
                                        ></i>
                                        <p class="text-600">
                                            Aucun historique disponible
                                        </p>
                                    </div>
                                </template>
                                <Column
                                    field="action"
                                    header="Action"
                                    sortable
                                    style="min-width: 10rem"
                                ></Column>
                                <Column
                                    field="champ_modifie"
                                    header="Champ modifié"
                                    sortable
                                    style="min-width: 12rem"
                                ></Column>
                                <Column
                                    field="ancienne_valeur"
                                    header="Ancienne valeur"
                                    style="min-width: 12rem"
                                ></Column>
                                <Column
                                    field="nouvelle_valeur"
                                    header="Nouvelle valeur"
                                    style="min-width: 12rem"
                                ></Column>
                                <Column
                                    field="user.name"
                                    header="Utilisateur"
                                    sortable
                                    style="min-width: 10rem"
                                ></Column>
                                <Column
                                    field="created_at"
                                    header="Date"
                                    sortable
                                    style="min-width: 12rem"
                                >
                                    <template #body="{ data }">
                                        {{ formatDateTime(data.created_at) }}
                                    </template>
                                </Column>
                            </DataTable>
                        </template>
                    </Card>
                </TabPanel>
            </TabView>
        </div>
        <div v-else class="text-center py-5">
            <i class="pi pi-spinner pi-spin text-primary-500 text-5xl mb-3"></i>
            <p class="text-600 text-lg">Chargement des données...</p>
        </div>

        <!-- Footer -->
        <template #footer>
            <div class="flex justify-content-end gap-3">
                <Button
                    label="Imprimer"
                    icon="pi pi-print"
                    class="p-button-outlined p-button-info"
                    @click="printDocument"
                />
                <Button
                    label="Fermer"
                    icon="pi pi-times"
                    class="p-button-outlined p-button-secondary"
                    @click="$emit('update:showViewDialog', false)"
                />
                <Button
                    label="Modifier"
                    icon="pi pi-pencil"
                    class="p-button-primary"
                    @click="$emit('edit', selectedCentre)"
                    :disabled="!selectedCentre"
                />
            </div>
        </template>

        <Toast position="top-right" />
    </Dialog>
</template>

<script>
import axios from 'axios';
import { ref } from 'vue';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';

export default {
    components: {
        Button,
        Card,
        Column,
        DataTable,
        Dialog,
        TabView,
        TabPanel,
        Tag,
        Toast,
    },
    props: {
        showViewDialog: { type: Boolean, required: true },
        selectedCentre: { type: Object, default: null },
    },
    emits: ['update:showViewDialog', 'edit', 'refresh'],
    setup() {
        const toast = useToast();
        const currentDate = new Date().toLocaleDateString('fr-FR');

        return {
            toast,
            currentDate,
        };
    },
    data() {
        return {
            types: [
                { label: 'Centre de Formation Professionnelle', value: '1' },
                { label: "Centre de Formation et d'Apprentissage", value: '2' },
                { label: 'Centre de Formation Continue', value: '3' },
                { label: 'Centre Sectoriel', value: '4' },
                { label: 'Centre de la Jeune Fille Rurale', value: '5' },
            ],
        };
    },
    computed: {
        specialitesDetails() {
            const details =
                this.selectedCentre?.specialites?.map((specialite) => {
                    console.log(
                        `Spécialité ${specialite.id} infos_specialites:`,
                        specialite.infos_specialites
                    );
                    const info = Array.isArray(specialite.infos_specialites)
                        ? specialite.infos_specialites[0] || {}
                        : {};
                    console.log(
                        `Spécialité ${specialite.id} selected info:`,
                        info
                    );
                    return {
                        id: specialite.id,
                        code: specialite.code,
                        nom_fr: specialite.nom_fr,
                        nom_ar: specialite.nom_ar,
                        duree_formation:
                            info.duree_formation !== undefined
                                ? info.duree_formation
                                : null,
                        homologation_fr: info.homologation_fr || null,
                        pivot: {
                            statut_fr: specialite.pivot?.statut_fr || 'Inconnu',
                            statut_ar:
                                specialite.pivot?.statut_ar || 'غير معروف',
                            observation_fr:
                                specialite.pivot?.observation_fr || null,
                            observation_ar:
                                specialite.pivot?.observation_ar || null,
                            date_debut: specialite.pivot?.date_debut || null,
                            date_fin: specialite.pivot?.date_fin || null,
                        },
                    };
                }) || [];
            console.log('specialitesDetails:', details);
            return details;
        },
        historique() {
            return this.selectedCentre?.historique || [];
        },
    },
    methods: {
        getSeverity(value) {
            switch (value) {
                case 'Homologuée':
                    return 'success';
                case 'Non Homologuée':
                    return 'danger';
                default:
                    return 'secondary';
            }
        },
        getTypeLabel(type) {
            const found = this.types.find((t) => t.value === type);
            return found ? found.label : type || '-';
        },
        formatDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('fr-FR');
        },
        formatDateTime(date) {
            if (!date) return '-';
            return new Date(date).toLocaleString('fr-FR');
        },
        formatDureeFormation(duree) {
            if (duree === null || duree === undefined) return 'Non défini';
            return duree === 0 ? '0 an' : `${duree} an${duree > 1 ? 's' : ''}`;
        },
        onImageError() {
            this.toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: "Impossible de charger le logo. Vérifiez le format de l'image.",
                life: 3000,
            });
        },
        async dissociateSpecialite(centreId, specialiteId) {
            try {
                await axios.delete(
                    `/api/centres/${centreId}/specialites/${specialiteId}`
                );
                if (this.selectedCentre) {
                    this.selectedCentre.specialites =
                        this.selectedCentre.specialites.filter(
                            (s) => s.id !== specialiteId
                        );
                }
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Spécialité dissociée avec succès.',
                    life: 3000,
                });
                this.$emit('refresh');
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail:
                        error.response?.data?.message ||
                        'Erreur lors de la dissociation de la spécialité.',
                    life: 3000,
                });
            }
        },
        printDocument() {
            const printWindow = window.open('', '_blank');
            if (printWindow) {
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>Fiche du Centre</title>
                            <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
                            <style>
                                body { font-family: Arial, sans-serif; margin: 20px; color: #374151; }
                                table { width: 100%; border-collapse: collapse; }
                                tr td, th { padding: 0.75rem; vertical-align: top; }
                                th { background-color: #e6f2ff; border: 1px solid #e0e0e0; font-weight: bold; }
                                td { border: 1px solid #e0e0e0; }
                                .w-3 { width: 30%; }
                                .font-bold { font-weight: bold; }
                                .font-arabic { font-family: 'Amiri', sans-serif; font-size: 1.1rem; }
                                .text-right { text-align: right; }
                                .text-center { text-align: center; }
                                .text-primary-600 { color: #1565c0; }
                                .text-700 { color: #374151; }
                                .text-600 { color: #6b7280; }
                                .text-xl { font-size: 1.5rem; }
                                .text-lg { font-size: 1.2rem; }
                                .text-sm { font-size: 0.8rem; }
                                .m-0 { margin: 0; }
                                .mt-2 { margin-top: 0.5rem; }
                                .mt-3 { margin-top: 1rem; }
                                .mt-5 { margin-top: 2rem; }
                                .mb-0 { margin-bottom: 0; }
                                .mb-3 { margin-bottom: 1rem; }
                                .mb-5 { margin-bottom: 2rem; }
                                .pb-2 { padding-bottom: 0.5rem; }
                                .pb-3 { padding-bottom: 1rem; }
                                .pt-3 { padding-top: 1rem; }
                                .w-full { width: 100%; }
                                .w-20rem { width: 20rem; }
                                .object-contain { object-fit: contain; }
                                .border-bottom-1 { border-bottom: 1px solid #e0e0e0; }
                                .border-top-1 { border-top: 1px solid #e0e0e0; }
                                .uppercase { text-transform: uppercase; }
                                .header { margin-bottom: 2rem; }
                                .content { margin-top: 2rem; margin-bottom: 2rem; }
                            </style>
                        </head>
                        <body>
                            <!-- Header -->
                            <div class="header text-center mb-5">
                                <p class="text-lg m-0">Agence Tunisienne de la Formation Professionnelle</p>
                                <p class="text-lg mt-2 font-arabic">الوكالة التونسية للتكوين المهني</p>
                            </div>
                            <!-- Title and Logo -->
                            <div class="content text-center mb-5">
                                <h1 class="text-xl uppercase font-bold m-0">FICHE DU CENTRE</h1>
                                <div class="mt-3">
                                    <img
                                        src="${this.selectedCentre.logo || ''}"
                                        alt="Logo du centre"
                                        class="w-20rem object-contain"
                                        onerror="this.style.display='none'"
                                    />
                                </div>
                            </div>
                            <!-- Body -->
                            <div>
                                <!-- French Information -->
                                <div class="mb-5">
                                    <h3 class="text-primary-600 border-bottom-1 pb-2 mb-3">Informations en Français</h3>
                                    <table class="w-full">
                                        <tbody>
                                        <tr>
                                            <td class="font-bold text-primary-600 w-3">Code:</td>
                                            <td class="text-700">${this.selectedCentre.code || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600">Nom:</td>
                                            <td class="text-700">${this.selectedCentre.nom_fr || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600">Adresse:</td>
                                            <td class="text-700">${this.selectedCentre.adresse_fr || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600">Gouvernorat:</td>
                                            <td class="text-700">${this.selectedCentre.gouvernorat_fr || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600">Économat:</td>
                                            <td class="text-700">${this.selectedCentre.economat?.nom_fr || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600">État:</td>
                                            <td class="text-700">${this.selectedCentre.etat_fr || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600">Statut:</td>
                                            <td class="text-700">${this.selectedCentre.statut_fr || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600">Observation:</td>
                                            <td class="text-700">${this.selectedCentre.observation_fr || '-'}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Arabic Information -->
                                <div class="mb-5">
                                    <h3 class="text-primary-600 border-bottom-1 pb-2 mb-3 font-arabic text-right">المعلومات بالعربية</h3>
                                    <table class="w-full" style="direction: rtl;">
                                        <tbody>
                                        <tr>
                                            <td class="font-bold text-primary-600 w-3 text-right font-arabic">الكود:</td>
                                            <td class="text-700 text-right font-arabic">${this.selectedCentre.code || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600 text-right font-arabic">الاسم:</td>
                                            <td class="text-700 text-right font-arabic">${this.selectedCentre.nom_ar || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600 text-right font-arabic">العنوان:</td>
                                            <td class="text-700 text-right font-arabic">${this.selectedCentre.adresse_ar || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600 text-right font-arabic">المحافظة:</td>
                                            <td class="text-700 text-right font-arabic">${this.selectedCentre.gouvernorat_ar || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600 text-right font-arabic">الإقتصادية:</td>
                                            <td class="text-700 text-right font-arabic">${this.selectedCentre.economat?.nom_ar || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600 text-right font-arabic">الحالة:</td>
                                            <td class="text-700 text-right font-arabic">${this.selectedCentre.etat_ar || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600 text-right font-arabic">الوضع:</td>
                                            <td class="text-700 text-right font-arabic">${this.selectedCentre.statut_ar || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600 text-right font-arabic">الملاحظات:</td>
                                            <td class="text-700 text-right font-arabic">${this.selectedCentre.observation_ar || '-'}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Common Information -->
                                <div class="mb-5">
                                    <h3 class="text-primary-600 border-bottom-1 pb-2 mb-3">Informations Communes</h3>
                                    <table class="w-full">
                                        <tbody>
                                        <tr>
                                            <td class="font-bold text-primary-600 w-3">Code:</td>
                                            <td class="text-700">${this.selectedCentre.code || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600">Type:</td>
                                            <td class="text-700">${this.getTypeLabel(this.selectedCentre.type) || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600">Classe:</td>
                                            <td class="text-700">${this.selectedCentre.classe || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600">Téléphone 1:</td>
                                            <td class="text-700">${this.selectedCentre.telephone_1 || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600">Téléphone 2:</td>
                                            <td class="text-700">${this.selectedCentre.telephone_2 || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600">Fax 1:</td>
                                            <td class="text-700">${this.selectedCentre.fax_1 || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600">Fax 2:</td>
                                            <td class="text-700">${this.selectedCentre.fax_2 || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600">Email:</td>
                                            <td class="text-700">${this.selectedCentre.email || '-'}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Important Dates -->
                                <div class="mb-5">
                                    <h3 class="text-primary-600 border-bottom-1 pb-2 mb-3">Dates Importantes</h3>
                                    <table class="w-full">
                                        <tbody>
                                        <tr>
                                            <td class="font-bold text-primary-600 w-3">Date de création:</td>
                                            <td class="text-700">${this.formatDate(this.selectedCentre.date_creation)}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-bold text-primary-600">Date d'ouverture:</td>
                                            <td class="text-700">${this.formatDate(this.selectedCentre.date_ouverture)}</td>
                                        </tr>
                                        ${
                                            this.selectedCentre.date_fermeture
                                                ? `
                                        <tr>
                                            <td class="font-bold text-primary-600">Date de fermeture:</td>
                                            <td class="text-700">${this.formatDate(this.selectedCentre.date_fermeture)}</td>
                                        </tr>
                                        `
                                                : ''
                                        }
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Spécialités Associées -->
                                <div class="mb-5">
                                    <h3 class="text-primary-600 border-bottom-1 pb-2 mb-3">Spécialités Associées</h3>
                                    <table class="w-full" style="border-collapse: collapse;">
                                        <thead>
                                        <tr style="background-color: #e6f2ff;">
                                            <th class="p-2 text-left font-bold text-primary-600" style="border: 1px solid #e0e0e0;">Code</th>
                                            <th class="p-2 text-left font-bold text-primary-600" style="border: 1px solid #e0e0e0; width: 25%;">Nom (Fr)</th>
                                            <th class="p-2 text-right font-bold text-primary-600" style="border: 1px solid #e0e0e0; width: 25%;">Nom (Ar)</th>
                                            <th class="p-2 text-left font-bold text-primary-600" style="border: 1px solid #e0e0e0;">Durée de formation</th>
                                            <th class="p-2 text-left font-bold text-primary-600" style="border: 1px solid #e0e0e0;">Homologation</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        ${this.specialitesDetails
                                            .map(
                                                (specialite) => `
                                            <tr style="border-bottom: 1px solid #e0e0e0;">
                                                <td class="p-2 text-700" style="border: 1px solid #e0e0e0;">${specialite.code || '-'}</td>
                                                <td class="p-2 text-700" style="border: 1px solid #e0e0e0;">${specialite.nom_fr || '-'}</td>
                                                <td class="p-2 text-700 text-right font-arabic" style="border: 1px solid #e0e0e0;">${specialite.nom_ar || '-'}</td>
                                                <td class="p-2 text-700" style="border: 1px solid #e0e0e0;">
                                                    ${this.formatDureeFormation(specialite.duree_formation)}
                                                </td>
                                                <td class="p-2 text-700" style="border: 1px solid #e0e0e0;">
                                                    ${specialite.homologation_fr || 'Non défini'}
                                                </td>
                                            </tr>
                                        `
                                            )
                                            .join('')}
                                        ${
                                            !this.specialitesDetails.length
                                                ? `
                                            <tr>
                                                <td colspan="5" class="p-2 text-center text-600">Aucune spécialité associée</td>
                                            </tr>
                                        `
                                                : ''
                                        }
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="mt-5 pt-3 border-top-1 text-right text-sm text-600">
                                <p>Document généré le ${this.currentDate}</p>
                            </div>
                        </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            } else {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Impossible d'ouvrir la fenêtre d'impression. Vérifiez les paramètres de votre navigateur.",
                    life: 3000,
                });
            }

            this.toast.add({
                severity: 'success',
                summary: 'Impression',
                detail: "Le document a été envoyé à l'imprimante",
                life: 3000,
            });
        },
    },
};
</script>

<style scoped>
:deep(.tabview-custom .p-tabview-nav) {
    background-color: var(--surface-50);
    border-bottom: 2px solid var(--primary-200);
}
:deep(.tabview-custom .p-tabview-nav li .p-tabview-nav-link) {
    color: var(--primary-600);
    font-weight: 500;
    padding: 1rem 1.5rem;
    transition: all 0.2s ease;
}
:deep(.tabview-custom .p-tabview-nav li .p-tabview-nav-link.p-highlight) {
    background-color: var(--primary-100);
    color: var(--primary-600);
    border-bottom: 3px solid var(--primary-500);
}
:deep(.tabview-custom .p-tabview-panels) {
    padding: 1.5rem;
    background-color: var(--surface-0);
}
.field label {
    display: block;
    margin-bottom: 0.5rem;
}
.field p {
    margin: 0;
    line-height: 1.5;
}
:deep(.bg-custom-dark) {
    background-color: #1f2937;
}
:deep(.border-gray-700) {
    border-color: #374151;
}
:deep(.p-card .p-card-content) {
    padding: 1rem;
}
.equal-height-cards {
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
}
.equal-height-cards .p-card {
    display: flex;
    flex-direction: column;
    height: 100%;
}
.equal-height-cards .p-card .p-card-body {
    flex-grow: 1;
}
</style>
