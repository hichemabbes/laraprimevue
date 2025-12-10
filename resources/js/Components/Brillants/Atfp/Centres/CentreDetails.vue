<template>
    <div>
        <Toast position="top-right" />

        <Dialog
            :visible="showViewDialog"
            @update:visible="$emit('update:showViewDialog', $event)"
            :modal="true"
            :style="{ width: '80vw', maxWidth: '1200px' }"
            :breakpoints="{ '960px': '95vw', '640px': '100vw' }"
            class="p-fluid"
            :pt="{
                root: 'border-round-xl shadow-5',
                mask: { style: 'backdrop-filter: blur(4px)' },
                header: {
                    class: 'surface-50 border-bottom-1 surface-border p-4',
                },
                content: { class: 'surface-ground p-4' },
                footer: { class: 'surface-50 border-top-1 surface-border p-4' },
            }"
        >
            <!-- Header -->
            <template #header>
                <div class="flex align-items-center gap-3">
                    <i class="pi pi-building text-primary text-3xl"></i>
                    <div>
                        <span class="font-bold text-2xl text-900">
                            {{ selectedCentre?.nom_fr || 'Détails du Centre' }}
                        </span>
                        <div class="flex align-items-center gap-2 mt-1">
                            <Tag
                                v-if="selectedCentre"
                                :value="selectedCentre.code"
                                icon="pi pi-tag"
                                severity="info"
                                rounded
                            />
                            <Tag
                                v-if="selectedCentre"
                                :value="selectedCentre.statut_fr"
                                :severity="
                                    selectedCentre.statut_fr === 'Fonctionnel'
                                        ? 'success'
                                        : 'danger'
                                "
                                rounded
                            />
                        </div>
                    </div>
                </div>
            </template>

            <!-- Loading State -->
            <div
                v-if="!selectedCentre"
                class="flex flex-column align-items-center justify-content-center gap-3 p-6"
            >
                <ProgressSpinner
                    style="width: 50px; height: 50px"
                    strokeWidth="4"
                />
                <span class="text-color-secondary"
                    >Chargement des données...</span
                >
            </div>

            <!-- Main Content -->
            <div v-else class="surface-card p-4 shadow-2 border-round">
                <!-- Logo and Summary Card -->
                <Card class="shadow-3 border-round-xl overflow-hidden mb-4">
                    <template #content>
                        <div
                            class="flex flex-column md:flex-row gap-5 align-items-center"
                        >
                            <!-- Logo -->
                            <div class="flex-shrink-0 relative">
                                <div
                                    class="border-round-xl shadow-3 overflow-hidden"
                                    style="
                                        width: 280px;
                                        height: 160px;
                                        background: var(--surface-card);
                                    "
                                >
                                    <img
                                        v-if="
                                            selectedCentre.logo &&
                                            isValidLogo(selectedCentre.logo)
                                        "
                                        :src="getLogoUrl(selectedCentre.logo)"
                                        alt="Logo du Centre"
                                        class="w-full h-full object-contain p-3"
                                        style="object-fit: contain"
                                        @error="handleLogoError"
                                    />
                                    <div
                                        v-else
                                        class="flex align-items-center justify-content-center w-full h-full"
                                    >
                                        <i
                                            class="pi pi-image text-5xl text-surface-300"
                                        ></i>
                                    </div>
                                </div>
                                <Button
                                    icon="pi pi-camera"
                                    class="p-button-rounded p-button-sm logo-upload-button"
                                    severity="info"
                                    v-tooltip="'Changer le logo'"
                                    @click="showLogoDialog = true"
                                />
                            </div>

                            <!-- Summary Info -->
                            <div class="flex-grow-1 w-full">
                                <div class="grid gap-12">
                                    <div class="col-12 md:col-6">
                                        <div class="field">
                                            <label
                                                class="block font-medium mb-2 text-600"
                                                >Type</label
                                            >
                                            <Tag
                                                :value="
                                                    selectedCentre.type_centre_fr ||
                                                    '-'
                                                "
                                                :severity="
                                                    getSeverity(
                                                        selectedCentre.type_centre_fr
                                                    )
                                                "
                                                icon="pi pi-box"
                                            />
                                        </div>
                                        <div class="field">
                                            <label
                                                class="block font-medium mb-2 text-600"
                                                >Gouvernorat</label
                                            >
                                            <div
                                                class="flex align-items-center gap-2"
                                            >
                                                <i
                                                    class="pi pi-map-marker text-primary"
                                                ></i>
                                                <span
                                                    class="text-900 font-medium"
                                                    >{{
                                                        selectedCentre.gouvernorat_fr ||
                                                        '-'
                                                    }}</span
                                                >
                                            </div>
                                        </div>
                                        <div class="field">
                                            <label
                                                class="block font-medium mb-2 text-600"
                                                >Classe</label
                                            >
                                            <Chip
                                                :label="
                                                    selectedCentre.classe_fr ||
                                                    '-'
                                                "
                                                class="bg-surface-100"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-12 md:col-6">
                                        <div class="field">
                                            <label
                                                class="block font-medium mb-2 text-600"
                                                >Téléphone</label
                                            >
                                            <div
                                                class="flex align-items-center gap-2"
                                            >
                                                <i
                                                    class="pi pi-phone text-primary"
                                                ></i>
                                                <span
                                                    class="text-900 font-medium"
                                                    >{{
                                                        selectedCentre.telephone_1 ||
                                                        '-'
                                                    }}</span
                                                >
                                            </div>
                                        </div>
                                        <div class="field">
                                            <label
                                                class="block font-medium mb-2 text-600"
                                                >Email</label
                                            >
                                            <div
                                                class="flex align-items-center gap-2"
                                            >
                                                <i
                                                    class="pi pi-envelope text-primary"
                                                ></i>
                                                <span
                                                    class="text-900 font-medium"
                                                    >{{
                                                        selectedCentre.email ||
                                                        '-'
                                                    }}</span
                                                >
                                            </div>
                                        </div>
                                        <div class="field">
                                            <label
                                                class="block font-medium mb-2 text-600"
                                                >Économat</label
                                            >
                                            <Chip
                                                :label="
                                                    selectedCentre.economat_fr ||
                                                    '-'
                                                "
                                                icon="pi pi-building"
                                                class="bg-surface-100"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- TabView for Detailed Information -->
                <TabView class="shadow-3 border-round-xl overflow-hidden">
                    <!-- Informations Générales -->
                    <TabPanel header="Informations Générales">
                        <div class="flex flex-column gap-4 p-4">
                            <!-- French and Common Information -->
                            <div class="grid gap-12">
                                <div class="col-12 md:col-6">
                                    <Card
                                        class="shadow-1 border-round-lg h-full"
                                    >
                                        <template #title>
                                            <div
                                                class="flex align-items-center gap-2"
                                            >
                                                <i
                                                    class="pi pi-info-circle text-primary"
                                                ></i>
                                                <span class="font-medium"
                                                    >Informations en
                                                    Français</span
                                                >
                                            </div>
                                        </template>
                                        <template #content>
                                            <div class="grid gap-6">
                                                <div class="col-12 field">
                                                    <label
                                                        class="block font-medium mb-2"
                                                        >Nom</label
                                                    >
                                                    <p class="text-900">
                                                        {{
                                                            selectedCentre.nom_fr ||
                                                            '-'
                                                        }}
                                                    </p>
                                                </div>
                                                <div class="col-12 field">
                                                    <label
                                                        class="block font-medium mb-2"
                                                        >Adresse</label
                                                    >
                                                    <p class="text-900">
                                                        {{
                                                            selectedCentre.adresse_fr ||
                                                            '-'
                                                        }}
                                                    </p>
                                                </div>
                                                <div class="col-12 field">
                                                    <label
                                                        class="block font-medium mb-2"
                                                        >Observation</label
                                                    >
                                                    <p class="text-900">
                                                        {{
                                                            selectedCentre.observation_fr ||
                                                            '-'
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                        </template>
                                    </Card>
                                </div>
                                <div class="col-12 md:col-6">
                                    <Card
                                        class="shadow-1 border-round-lg h-full"
                                    >
                                        <template #title>
                                            <div
                                                class="flex align-items-center gap-2"
                                            >
                                                <i
                                                    class="pi pi-cog text-primary"
                                                ></i>
                                                <span class="font-medium"
                                                    >Coordonnées</span
                                                >
                                            </div>
                                        </template>
                                        <template #content>
                                            <div class="grid gap-6">
                                                <div
                                                    class="col-12 md:col-6 field"
                                                >
                                                    <label
                                                        class="block font-medium mb-2"
                                                        >Téléphone 1</label
                                                    >
                                                    <p class="text-900">
                                                        {{
                                                            selectedCentre.telephone_1 ||
                                                            '-'
                                                        }}
                                                    </p>
                                                </div>
                                                <div
                                                    class="col-12 md:col-6 field"
                                                >
                                                    <label
                                                        class="block font-medium mb-2"
                                                        >Téléphone 2</label
                                                    >
                                                    <p class="text-900">
                                                        {{
                                                            selectedCentre.telephone_2 ||
                                                            '-'
                                                        }}
                                                    </p>
                                                </div>
                                                <div
                                                    class="col-12 md:col-6 field"
                                                >
                                                    <label
                                                        class="block font-medium mb-2"
                                                        >Fax 1</label
                                                    >
                                                    <p class="text-900">
                                                        {{
                                                            selectedCentre.fax_1 ||
                                                            '-'
                                                        }}
                                                    </p>
                                                </div>
                                                <div
                                                    class="col-12 md:col-6 field"
                                                >
                                                    <label
                                                        class="block font-medium mb-2"
                                                        >Fax 2</label
                                                    >
                                                    <p class="text-900">
                                                        {{
                                                            selectedCentre.fax_2 ||
                                                            '-'
                                                        }}
                                                    </p>
                                                </div>
                                                <div class="col-12 field">
                                                    <label
                                                        class="block font-medium mb-2"
                                                        >Email</label
                                                    >
                                                    <p class="text-900">
                                                        {{
                                                            selectedCentre.email ||
                                                            '-'
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                        </template>
                                    </Card>
                                </div>
                            </div>

                            <!-- Dates -->
                            <Card class="shadow-1 border-round-lg">
                                <template #title>
                                    <div class="flex align-items-center gap-2">
                                        <i
                                            class="pi pi-calendar text-primary"
                                        ></i>
                                        <span class="font-medium"
                                            >Dates Importantes</span
                                        >
                                    </div>
                                </template>
                                <template #content>
                                    <div class="grid gap-6">
                                        <div class="col-12 md:col-4 field">
                                            <label
                                                class="block font-medium mb-2"
                                                >Date de Création</label
                                            >
                                            <div
                                                class="flex align-items-center gap-2"
                                            >
                                                <i
                                                    class="pi pi-calendar-plus text-primary"
                                                ></i>
                                                <span class="text-900">{{
                                                    formatDate(
                                                        selectedCentre.date_creation
                                                    )
                                                }}</span>
                                            </div>
                                        </div>
                                        <div class="col-12 md:col-4 field">
                                            <label
                                                class="block font-medium mb-2"
                                                >Date d'Ouverture</label
                                            >
                                            <div
                                                class="flex align-items-center gap-2"
                                            >
                                                <i
                                                    class="pi pi-check-circle text-primary"
                                                ></i>
                                                <span class="text-900">{{
                                                    formatDate(
                                                        selectedCentre.date_ouverture
                                                    )
                                                }}</span>
                                            </div>
                                        </div>
                                        <div class="col-12 md:col-4 field">
                                            <label
                                                class="block font-medium mb-2"
                                                >Date de Fermeture</label
                                            >
                                            <div
                                                class="flex align-items-center gap-2"
                                            >
                                                <i
                                                    class="pi pi-times-circle text-primary"
                                                ></i>
                                                <span class="text-900">{{
                                                    formatDate(
                                                        selectedCentre.date_fermeture
                                                    )
                                                }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </Card>

                            <!-- Arabic Information -->
                            <Card class="shadow-1 border-round-lg">
                                <template #title>
                                    <div
                                        class="flex align-items-center gap-2 justify-content-end"
                                    >
                                        <i
                                            class="pi pi-language text-primary"
                                        ></i>
                                        <span
                                            class="font-medium font-arabic text-right"
                                            >المعلومات بالعربية</span
                                        >
                                    </div>
                                </template>
                                <template #content>
                                    <div class="grid gap-6">
                                        <div class="col-12 md:col-6 field">
                                            <label
                                                class="block font-medium mb-2 text-right font-arabic"
                                                >الاسم</label
                                            >
                                            <p
                                                class="text-900 text-right font-arabic"
                                            >
                                                {{
                                                    selectedCentre.nom_ar || '-'
                                                }}
                                            </p>
                                        </div>
                                        <div class="col-12 md:col-6 field">
                                            <label
                                                class="block font-medium mb-2 text-right font-arabic"
                                                >العنوان</label
                                            >
                                            <p
                                                class="text-900 text-right font-arabic"
                                            >
                                                {{
                                                    selectedCentre.adresse_ar ||
                                                    '-'
                                                }}
                                            </p>
                                        </div>
                                        <div class="col-12 field">
                                            <label
                                                class="block font-medium mb-2 text-right font-arabic"
                                                >الملاحظات</label
                                            >
                                            <p
                                                class="text-900 text-right font-arabic"
                                            >
                                                {{
                                                    selectedCentre.observation_ar ||
                                                    '-'
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </template>
                            </Card>
                        </div>
                    </TabPanel>

                    <!-- Formation Types Tabs -->
                    <TabPanel header="Type de Centre">
                        <div class="p-4">
                            <Card class="shadow-1 border-round-lg">
                                <template #title>
                                    <div class="flex align-items-center gap-2">
                                        <i class="pi pi-box text-primary"></i>
                                        <span class="font-medium"
                                            >Type de Centre</span
                                        >
                                    </div>
                                </template>
                                <template #content>
                                    <div class="grid gap-6">
                                        <div class="col-12 md:col-6 field">
                                            <label
                                                class="block font-medium mb-2"
                                                >Type (Français)</label
                                            >
                                            <p class="text-900">
                                                {{
                                                    selectedCentre.type_centre_fr ||
                                                    '-'
                                                }}
                                            </p>
                                        </div>
                                        <div class="col-12 md:col-6 field">
                                            <label
                                                class="block font-medium mb-2 text-right font-arabic"
                                                >النوع (عربي)</label
                                            >
                                            <p
                                                class="text-900 text-right font-arabic"
                                            >
                                                {{
                                                    selectedCentre.type_centre_ar ||
                                                    '-'
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </template>
                            </Card>
                        </div>
                    </TabPanel>

                    <TabPanel header="Classe">
                        <div class="p-4">
                            <Card class="shadow-1 border-round-lg">
                                <template #title>
                                    <div class="flex align-items-center gap-2">
                                        <i class="pi pi-star text-primary"></i>
                                        <span class="font-medium"
                                            >Classe du Centre</span
                                        >
                                    </div>
                                </template>
                                <template #content>
                                    <div class="grid gap-6">
                                        <div class="col-12 md:col-6 field">
                                            <label
                                                class="block font-medium mb-2"
                                                >Classe (Français)</label
                                            >
                                            <p class="text-900">
                                                {{
                                                    selectedCentre.classe_fr ||
                                                    '-'
                                                }}
                                            </p>
                                        </div>
                                        <div class="col-12 md:col-6 field">
                                            <label
                                                class="block font-medium mb-2 text-right font-arabic"
                                                >الفئة (عربي)</label
                                            >
                                            <p
                                                class="text-900 text-right font-arabic"
                                            >
                                                {{
                                                    selectedCentre.classe_ar ||
                                                    '-'
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </template>
                            </Card>
                        </div>
                    </TabPanel>

                    <TabPanel header="Économat">
                        <div class="p-4">
                            <Card class="shadow-1 border-round-lg">
                                <template #title>
                                    <div class="flex align-items-center gap-2">
                                        <i
                                            class="pi pi-building text-primary"
                                        ></i>
                                        <span class="font-medium"
                                            >Économat</span
                                        >
                                    </div>
                                </template>
                                <template #content>
                                    <div class="grid gap-6">
                                        <div class="col-12 md:col-6 field">
                                            <label
                                                class="block font-medium mb-2"
                                                >Économat (Français)</label
                                            >
                                            <p class="text-900">
                                                {{
                                                    selectedCentre.economat_fr ||
                                                    '-'
                                                }}
                                            </p>
                                        </div>
                                        <div class="col-12 md:col-6 field">
                                            <label
                                                class="block font-medium mb-2 text-right font-arabic"
                                                >الإقتصاد (عربي)</label
                                            >
                                            <p
                                                class="text-900 text-right font-arabic"
                                            >
                                                {{
                                                    selectedCentre.economat_ar ||
                                                    '-'
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </template>
                            </Card>
                        </div>
                    </TabPanel>

                    <!-- Statut Timeline -->
                    <TabPanel header="Statut">
                        <div class="p-4">
                            <Card class="shadow-2 border-round-lg">
                                <template #title>
                                    <div class="flex align-items-center gap-2">
                                        <i class="pi pi-clock text-primary"></i>
                                        <span class="font-medium"
                                            >Chronologie du Statut</span
                                        >
                                    </div>
                                </template>
                                <template #content>
                                    <Timeline
                                        :value="statusTimeline"
                                        align="alternate"
                                        class="mt-3"
                                    >
                                        <template #content="slotProps">
                                            <Card
                                                class="shadow-1 border-round-lg mb-3"
                                            >
                                                <template #content>
                                                    <div
                                                        class="flex flex-column gap-1"
                                                    >
                                                        <span
                                                            class="font-medium text-900"
                                                            >{{
                                                                slotProps.item
                                                                    .status
                                                            }}</span
                                                        >
                                                        <small
                                                            class="text-color-secondary"
                                                            >{{
                                                                slotProps.item
                                                                    .date
                                                            }}</small
                                                        >
                                                    </div>
                                                </template>
                                            </Card>
                                        </template>
                                        <template #marker="slotProps">
                                            <span
                                                class="flex w-2.5rem h-2.5rem align-items-center justify-content-center bg-primary border-circle shadow-2"
                                            >
                                                <i
                                                    :class="slotProps.item.icon"
                                                    class="text-white"
                                                ></i>
                                            </span>
                                        </template>
                                        <template #opposite="slotProps">
                                            <small
                                                class="text-color-secondary"
                                                >{{
                                                    slotProps.item.opposite ||
                                                    ' '
                                                }}</small
                                            >
                                        </template>
                                    </Timeline>
                                </template>
                            </Card>
                        </div>
                    </TabPanel>
                </TabView>
            </div>

            <!-- Logo Upload Dialog -->
            <Dialog
                v-model:visible="showLogoDialog"
                header="Changer le Logo du Centre"
                :modal="true"
                :style="{ width: '30rem' }"
                :pt="{
                    root: 'border-round-xl shadow-5',
                    mask: { style: 'backdrop-filter: blur(4px)' },
                    header: {
                        class: 'surface-50 border-bottom-1 surface-border p-4',
                    },
                    content: { class: 'surface-ground p-4' },
                    footer: {
                        class: 'surface-50 border-top-1 surface-border p-4',
                    },
                }"
            >
                <div class="flex flex-column align-items-center gap-3">
                    <div
                        v-if="
                            previewImage ||
                            (selectedCentre.logo &&
                                isValidLogo(selectedCentre.logo))
                        "
                        class="cropper-container"
                    >
                        <Cropper
                            :src="
                                previewImage || getLogoUrl(selectedCentre.logo)
                            "
                            :stencil-props="{
                                aspectRatio: null,
                                movable: true,
                                resizable: true,
                                resizeHandlers: {
                                    enabled: true,
                                    positions: ['bottom-right'],
                                },
                                style: {
                                    border: '2px solid #3b82f6',
                                    borderRadius: '0',
                                    background: 'transparent',
                                },
                                initialSize: { width: 200, height: 100 },
                            }"
                            :canvas="{ minWidth: 100, minHeight: 100 }"
                            @change="onCropChange"
                            @ready="onCropperReady"
                        />
                    </div>
                    <div
                        v-else
                        class="flex flex-column align-items-center gap-2"
                    >
                        <i class="pi pi-image text-5xl text-surface-300"></i>
                        <small class="text-error" v-if="imageError">{{
                            imageError
                        }}</small>
                    </div>
                    <FileUpload
                        mode="basic"
                        accept="image/*"
                        :maxFileSize="2000000"
                        chooseLabel="Choisir une Image"
                        @select="onLogoSelect"
                        class="w-full"
                    />
                    <small class="text-500">Taille max: 2MB (JPG, PNG)</small>
                </div>
                <template #footer>
                    <Button
                        label="Annuler"
                        icon="pi pi-times"
                        severity="secondary"
                        raised
                        @click="cancelLogoUpload"
                    />
                    <Button
                        label="Enregistrer"
                        icon="pi pi-check"
                        severity="primary"
                        raised
                        :disabled="
                            !croppedImage &&
                            !selectedFile &&
                            !selectedCentre.logo
                        "
                        :loading="uploadingLogo"
                        @click="uploadLogo"
                    />
                </template>
            </Dialog>

            <!-- Footer -->
            <template #footer>
                <div class="flex justify-content-end gap-3">
                    <Button
                        label="Aperçu avant impression"
                        icon="pi pi-print"
                        severity="secondary"
                        raised
                        @click="printDocument"
                    />
                    <Button
                        label="Fermer"
                        icon="pi pi-times"
                        severity="secondary"
                        raised
                        @click="$emit('update:showViewDialog', false)"
                    />
                    <Button
                        label="Modifier"
                        icon="pi pi-pencil"
                        severity="primary"
                        raised
                        @click="$emit('edit', selectedCentre)"
                        :disabled="!selectedCentre"
                    />
                </div>
            </template>
        </Dialog>
    </div>
</template>

<script>
import { ref, computed } from 'vue';
import { useToast } from 'primevue/usetoast';
import axios from '@/axios';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Dialog from 'primevue/dialog';
import ProgressSpinner from 'primevue/progressspinner';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Tag from 'primevue/tag';
import Chip from 'primevue/chip';
import Toast from 'primevue/toast';
import Timeline from 'primevue/timeline';
import FileUpload from 'primevue/fileupload';
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';

export default {
    components: {
        Button,
        Card,
        Dialog,
        ProgressSpinner,
        TabView,
        TabPanel,
        Tag,
        Chip,
        Toast,
        Timeline,
        FileUpload,
        Cropper,
    },
    props: {
        showViewDialog: { type: Boolean, required: true },
        selectedCentre: { type: Object, default: null },
    },
    emits: ['update:showViewDialog', 'edit', 'refresh'],
    setup(props) {
        const toast = useToast();
        const showLogoDialog = ref(false);
        const selectedFile = ref(null);
        const previewImage = ref(null);
        const croppedImage = ref(null);
        const imageError = ref(null);
        const uploadingLogo = ref(false);
        const currentDate = new Date().toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });

        const formatDate = (date) => {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('fr-FR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
        };

        const statusTimeline = computed(() => {
            return [
                {
                    status: 'Création',
                    date: formatDate(props.selectedCentre?.date_creation),
                    icon: 'pi pi-calendar-plus',
                    opposite: 'Date de création',
                },
                {
                    status: 'Ouverture',
                    date: formatDate(props.selectedCentre?.date_ouverture),
                    icon: 'pi pi-check-circle',
                    opposite: "Date d'ouverture",
                },
                props.selectedCentre?.date_fermeture
                    ? {
                          status: 'Fermeture',
                          date: formatDate(props.selectedCentre.date_fermeture),
                          icon: 'pi pi-times-circle',
                          opposite: 'Date de fermeture',
                      }
                    : {
                          status: 'En activité',
                          date: 'Actuel',
                          icon: 'pi pi-circle-on',
                          opposite: 'Statut actuel',
                      },
            ].filter((item) => item.date !== '-');
        });

        return {
            toast,
            showLogoDialog,
            selectedFile,
            previewImage,
            croppedImage,
            imageError,
            uploadingLogo,
            currentDate,
            statusTimeline,
            formatDate,
        };
    },
    methods: {
        getSeverity(value) {
            switch (value) {
                case 'CFA':
                case 'CSF':
                    return 'success';
                case 'CFPTI':
                    return 'info';
                case 'CFR':
                    return 'warning';
                default:
                    return 'secondary';
            }
        },
        isValidLogo(logo) {
            if (!logo) return false;
            if (logo.startsWith('data:image/')) return true;
            const validFormats = ['.jpg', '.jpeg', '.png', '.gif', '.svg'];
            return validFormats.some((format) =>
                logo.toLowerCase().endsWith(format)
            );
        },
        getLogoUrl(logo) {
            if (!logo) return '';
            if (logo.startsWith('data:image/')) return logo;
            if (logo.startsWith('/storage/')) {
                return `${window.location.origin}${logo}`;
            }
            return `${window.location.origin}/storage/logos/${logo}`;
        },
        handleLogoError() {
            this.toast.add({
                severity: 'warn',
                summary: 'Avertissement',
                detail: "Le logo n'a pas pu être chargé. Une image par défaut est affichée.",
                life: 3000,
            });
        },
        onLogoSelect(event) {
            try {
                this.imageError = null;
                const file = event.files[0];
                if (!file) {
                    throw new Error('Aucun fichier sélectionné');
                }
                if (file.size > 2000000) {
                    this.imageError =
                        "La taille de l'image ne doit pas dépasser 2 Mo";
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: this.imageError,
                        life: 3000,
                    });
                    return;
                }
                if (!file.type.match('image/(jpg|jpeg|png)')) {
                    this.imageError =
                        'Veuillez sélectionner une image valide (JPG, PNG)';
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: this.imageError,
                        life: 3000,
                    });
                    return;
                }
                this.selectedFile = file;
                this.previewImage = URL.createObjectURL(file);
                this.croppedImage = null;
            } catch (err) {
                this.imageError =
                    err.message || "Erreur lors de la sélection de l'image";
                this.previewImage = null;
                this.selectedFile = null;
                this.croppedImage = null;
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: this.imageError,
                    life: 3000,
                });
            }
        },
        onCropperReady() {
            console.log('Cropper prêt : le composant est chargé et rendu');
        },
        onCropChange({ coordinates, canvas }) {
            try {
                this.croppedImage = canvas.toDataURL('image/png');
                console.log(
                    'Image recadrée générée:',
                    this.croppedImage.substring(0, 50) + '...'
                );
            } catch (err) {
                this.croppedImage = null;
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Erreur lors du recadrage de l'image",
                    life: 3000,
                });
            }
        },
        async uploadLogo() {
            try {
                this.uploadingLogo = true;
                let base64Image = this.croppedImage;

                // Si aucune image recadrée, convertir le fichier sélectionné en Base64
                if (!base64Image && this.selectedFile) {
                    base64Image = await this.toBase64(this.selectedFile);
                }

                // Si aucune image recadrée ni fichier sélectionné, utiliser le logo existant
                if (!base64Image && this.selectedCentre.logo) {
                    base64Image = this.selectedCentre.logo;
                }

                if (!base64Image) {
                    throw new Error('Aucune image sélectionnée ou existante');
                }

                // Vérifier le format de l'image
                if (
                    !base64Image.match(
                        /^data:image\/(png|jpeg|jpg|svg\+xml);base64,/
                    )
                ) {
                    throw new Error("Format de l'image invalide");
                }

                // Vérifier la taille de l'image
                const base64String = base64Image.replace(
                    /^data:image\/(png|jpeg|jpg|svg\+xml);base64,/,
                    ''
                );
                const sizeInBytes = atob(base64String).length;
                if (sizeInBytes > 2000000) {
                    throw new Error("L'image dépasse 2 Mo");
                }

                // Préparer la requête
                const endpoint = `/api/centres/${this.selectedCentre.id}/logo`;
                console.log('Envoi de la requête POST à:', endpoint);
                const response = await axios.post(
                    endpoint,
                    {
                        logo: base64Image,
                    },
                    {
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            Authorization: `Bearer ${localStorage.getItem('token')}`,
                        },
                    }
                );

                console.log("Réponse de l'API:", response.data);
                this.selectedCentre.logo = response.data.logo || base64Image;
                this.toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Logo mis à jour avec succès',
                    life: 3000,
                });
                this.showLogoDialog = false;
                this.$emit('refresh');
            } catch (err) {
                console.error('Erreur lors de uploadLogo:', err);
                let errorMessage = "Erreur lors de l'upload du logo";
                if (err.response?.status === 422) {
                    const errors = err.response.data.errors?.logo || [
                        'Erreur de validation',
                    ];
                    errorMessage = errors.join(', ');
                } else if (err.response?.status === 404) {
                    errorMessage = 'Centre non trouvé';
                } else if (err.response?.status === 401) {
                    errorMessage = 'Non autorisé. Veuillez vous reconnecter.';
                } else if (err.response?.status === 405) {
                    errorMessage =
                        'Méthode non autorisée. Vérifiez la configuration de la route serveur.';
                } else if (err.response?.status === 500) {
                    errorMessage =
                        err.response.data.error || 'Erreur serveur interne';
                } else if (err.message) {
                    errorMessage = err.message;
                }
                this.toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: errorMessage,
                    life: 3000,
                });
            } finally {
                this.uploadingLogo = false;
                this.selectedFile = null;
                this.previewImage = null;
                this.croppedImage = null;
                this.imageError = null;
            }
        },
        toBase64(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = (error) => reject(error);
                reader.readAsDataURL(file);
            });
        },
        cancelLogoUpload() {
            this.showLogoDialog = false;
            this.selectedFile = null;
            this.previewImage = null;
            this.croppedImage = null;
            this.imageError = null;
            this.toast.add({
                severity: 'info',
                summary: 'Annulé',
                detail: 'Upload du logo annulé.',
                life: 3000,
            });
        },
        printDocument() {
            const printContent = `
                <div class="surface-card p-4 shadow-2 border-round">
                    <!-- Header -->
                    <div class="header text-center mb-4">
                        <p class="text-xl m-0 font-bold">Agence Tunisienne de la Formation Professionnelle</p>
                        <p class="text-xl mt-2 font-arabic font-bold">الوكالة التونسية للتكوين المهني</p>
                        <h1 class="text-2xl uppercase font-bold mt-3 mb-4">FICHE DU CENTRE</h1>
                        <div class="mt-3">
                            ${
                                this.selectedCentre.logo &&
                                this.isValidLogo(this.selectedCentre.logo)
                                    ? `<img src="${this.getLogoUrl(this.selectedCentre.logo)}" alt="Logo du centre" class="w-15rem object-contain" />`
                                    : '<i class="pi pi-image text-5xl text-surface-300"></i>'
                            }
                        </div>
                    </div>

                    <!-- General Information Table -->
                    <div class="mb-5">
                        <h3 class="font-medium border-bottom-1 surface-border pb-2 mb-3">Informations Générales</h3>
                        <table class="p-datatable-table w-full">
                            <tbody>
                                <tr>
                                    <td class="p-2">Code</td>
                                    <td class="p-2">${this.selectedCentre.code || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="p-2">Nom (Français)</td>
                                    <td class="p-2">${this.selectedCentre.nom_fr || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="p-2">Adresse (Français)</td>
                                    <td class="p-2">${this.selectedCentre.adresse_fr || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="p-2">Gouvernorat (Français)</td>
                                    <td class="p-2">${this.selectedCentre.gouvernorat_fr || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="p-2">Téléphone 1</td>
                                    <td class="p-2">${this.selectedCentre.telephone_1 || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="p-2">Téléphone 2</td>
                                    <td class="p-2">${this.selectedCentre.telephone_2 || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="p-2">Fax 1</td>
                                    <td class="p-2">${this.selectedCentre.fax_1 || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="p-2">Fax 2</td>
                                    <td class="p-2">${this.selectedCentre.fax_2 || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="p-2">Email</td>
                                    <td class="p-2">${this.selectedCentre.email || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="p-2">Observation (Français)</td>
                                    <td class="p-2">${this.selectedCentre.observation_fr || '-'}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Arabic Information Table -->
                    <div class="mb-5">
                        <h3 class="font-medium border-bottom-1 surface-border pb-2 mb-3 font-arabic text-right">المعلومات بالعربية</h3>
                        <table class="p-datatable-table w-full" dir="rtl">
                            <tbody>
                                <tr>
                                    <td class="p-2 font-arabic">الاسم</td>
                                    <td class="p-2 font-arabic">${this.selectedCentre.nom_ar || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="p-2 font-arabic">العنوان</td>
                                    <td class="p-2 font-arabic">${this.selectedCentre.adresse_ar || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="p-2 font-arabic">الملاحظات</td>
                                    <td class="p-2 font-arabic">${this.selectedCentre.observation_ar || '-'}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Type de Centre Table -->
                    <div class="mb-5">
                        <h3 class="font-medium border-bottom-1 surface-border pb-2 mb-3">Type de Centre</h3>
                        <table class="p-datatable-table w-full">
                            <tbody>
                                <tr>
                                    <td class="p-2">Type (Français)</td>
                                    <td class="p-2">${this.selectedCentre.type_centre_fr || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="p-2 font-arabic">النوع (عربي)</td>
                                    <td class="p-2 font-arabic">${this.selectedCentre.type_centre_ar || '-'}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Classe Table -->
                    <div class="mb-5">
                        <h3 class="font-medium border-bottom-1 surface-border pb-2 mb-3">Classe</h3>
                        <table class="p-datatable-table w-full">
                            <tbody>
                                <tr>
                                    <td class="p-2">Classe (Français)</td>
                                    <td class="p-2">${this.selectedCentre.classe_fr || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="p-2 font-arabic">الفئة (عربي)</td>
                                    <td class="p-2 font-arabic">${this.selectedCentre.classe_ar || '-'}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Économat Table -->
                    <div class="mb-5">
                        <h3 class="font-medium border-bottom-1 surface-border pb-2 mb-3">Économat</h3>
                        <table class="p-datatable-table w-full">
                            <tbody>
                                <tr>
                                    <td class="p-2">Économat (Français)</td>
                                    <td class="p-2">${this.selectedCentre.economat_fr || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="p-2 font-arabic">الإقتصاد (عربي)</td>
                                    <td class="p-2 font-arabic">${this.selectedCentre.economat_ar || '-'}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- État et Statut Table -->
                    <div class="mb-5">
                        <h3 class="font-medium border-bottom-1 surface-border pb-2 mb-3">État et Statut</h3>
                        <table class="p-datatable-table w-full">
                            <tbody>
                                <tr>
                                    <td class="p-2">État (Français)</td>
                                    <td class="p-2">${this.selectedCentre.etat_fr || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="p-2 font-arabic">الحالة (عربي)</td>
                                    <td class="p-2 font-arabic">${this.selectedCentre.etat_ar || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="p-2">Statut (Français)</td>
                                    <td class="p-2">${this.selectedCentre.statut_fr || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="p-2 font-arabic">الوضعية (عربي)</td>
                                    <td class="p-2 font-arabic">${this.selectedCentre.statut_ar || '-'}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Important Dates Table -->
                    <div class="mb-5">
                        <h3 class="font-medium border-bottom-1 surface-border pb-2 mb-3">Dates Importantes</h3>
                        <table class="p-datatable-table w-full">
                            <tbody>
                                <tr>
                                    <td class="p-2">Date de création</td>
                                    <td class="p-2">${this.formatDate(this.selectedCentre.date_creation)}</td>
                                </tr>
                                <tr>
                                    <td class="p-2">Date d'ouverture</td>
                                    <td class="p-2">${this.formatDate(this.selectedCentre.date_ouverture)}</td>
                                </tr>
                                <tr>
                                    <td class="p-2">Date de fermeture</td>
                                    <td class="p-2">${this.formatDate(this.selectedCentre.date_fermeture)}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer -->
                    <div class="mt-5 pt-3 border-top-1 surface-border text-right text-sm text-color-secondary">
                        <p>Document généré le ${this.currentDate}</p>
                    </div>
                </div>
            `;

            const printWindow = window.open('', '_blank');
            if (printWindow) {
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>Fiche du Centre - ${this.selectedCentre.nom_fr}</title>
                            <style>
                                @font-face {
                                    font-family: 'Montserrat-Arabic';
                                    src: url('/fonts/Montserrat-Arabic-Regular.otf') format('opentype');
                                    font-weight: normal;
                                    font-style: normal;
                                }
                                body {
                                    font-family: Arial, sans-serif;
                                    margin: 20px;
                                    color: #212529;
                                    background: #fff;
                                }
                                .surface-card {
                                    background: #ffffff;
                                    border-radius: 8px;
                                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                                    padding: 1.5rem;
                                }
                                .font-arabic {
                                    font-family: 'Montserrat-Arabic', sans-serif;
                                }
                                .text-right {
                                    text-align: right;
                                }
                                .text-center {
                                    text-align: center;
                                }
                                .font-medium {
                                    color: #6c757d;
                                    font-weight: 500;
                                }
                                .text-900 {
                                    color: #212529;
                                    font-weight: 600;
                                }
                                .p-datatable-table {
                                    width: 100%;
                                    border-collapse: collapse;
                                    margin: 1rem 0;
                                    background: #ffffff;
                                    border: 1px solid #dee2e6;
                                    border-radius: 4px;
                                }
                                .p-datatable-table th, .p-datatable-table td {
                                    padding: 0.75rem;
                                    border-bottom: 1px solid #dee2e6;
                                    vertical-align: middle;
                                }
                                .p-datatable-table th {
                                    background: #f8f9fa;
                                    color: #495057;
                                    font-weight: 600;
                                }
                                .p-datatable-table tr:last-child td {
                                    border-bottom: none;
                                }
                                .border-bottom-1 {
                                    border-bottom: 1px solid #dee2e6;
                                }
                                .border-top-1 {
                                    border-top: 1px solid #dee2e6;
                                }
                                .mb-2 {
                                    margin-bottom: 0.5rem;
                                }
                                .mb-3 {
                                    margin-bottom: 1rem;
                                }
                                .mb-4 {
                                    margin-bottom: 1.5rem;
                                }
                                .mb-5 {
                                    margin-bottom: 2rem;
                                }
                                .mt-2 {
                                    margin-top: 0.5rem;
                                }
                                .mt-3 {
                                    margin-top: 1rem;
                                }
                                .mt-5 {
                                    margin-top: 2rem;
                                }
                                .pb-2 {
                                    padding-bottom: 0.5rem;
                                }
                                .pt-3 {
                                    padding-top: 1rem;
                                }
                                .w-15rem {
                                    width: 15rem;
                                }
                                .object-contain {
                                    object-fit: contain;
                                }
                                .uppercase {
                                    text-transform: uppercase;
                                }
                                .font-bold {
                                    font-weight: bold;
                                }
                                .text-xl {
                                    font-size: 1.25rem;
                                }
                                .text-2xl {
                                    font-size: 1.5rem;
                                }
                                .text-sm {
                                    font-size: 0.875rem;
                                }
                                @media print {
                                    body {
                                        margin: 0;
                                        padding: 20px;
                                    }
                                    .no-print {
                                        display: none !important;
                                    }
                                    .surface-card {
                                        box-shadow: none;
                                    }
                                    .p-datatable-table {
                                        border: 1px solid #000;
                                    }
                                    .p-datatable-table th, .p-datatable-table td {
                                        border: 1px solid #000;
                                    }
                                }
                            </style>
                        </head>
                        <body>
                            ${printContent}
                        </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                setTimeout(() => {
                    printWindow.print();
                    printWindow.close();
                }, 500);
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
@font-face {
    font-family: 'Montserrat-Arabic';
    src: url('/fonts/Montserrat-Arabic-Regular.otf') format('opentype');
    font-weight: normal;
    font-style: normal;
}

.field {
    margin-bottom: 1.25rem;
}

label.font-medium {
    color: #6c757d;
}

.font-arabic {
    font-family: 'Montserrat-Arabic', sans-serif;
}

:deep(.p-button) {
    max-width: 200px;
    padding: 0.8rem 1rem;
    font-size: 0.875rem;
    border-radius: 0.25rem;
}

:deep(.p-tabview .p-tabview-nav) {
    background: var(--surface-ground);
    border-bottom: 1px solid var(--surface-border);
}

:deep(.p-tabview .p-tabview-nav li .p-tabview-nav-link) {
    background: transparent;
    border-color: transparent;
    color: var(--text-color-secondary);
    font-weight: 500;
    padding: 1rem 1.25rem;
    transition: all 0.2s ease;
}

:deep(.p-tabview .p-tabview-nav li .p-tabview-nav-link:not(.p-disabled):focus) {
    box-shadow: none;
}

:deep(.p-tabview .p-tabview-nav li.p-highlight .p-tabview-nav-link) {
    color: var(--primary-color);
    border-bottom: 2px solid var(--primary-color);
}

:deep(.p-tabview .p-tabview-panels) {
    background: transparent;
    padding: 0;
}

:deep(.p-card-title) {
    font-size: 1.25rem;
    color: var(--primary-color);
}

:deep(.p-card-content) {
    padding: 0;
}

.grid {
    margin: 0;
    padding: 1rem;
}

:deep(.p-inputtext),
:deep(.p-dropdown),
:deep(.p-textarea),
:deep(.p-calendar) {
    border-radius: 0.25rem;
}

.logo-upload-button {
    position: absolute;
    bottom: -10px;
    right: -10px;
}

.cropper-container {
    width: 300px;
    height: 300px;
    position: relative;
    overflow: hidden;
    background: transparent;
}

:deep(.cropper-handler) {
    display: none !important;
}

:deep(.cropper-handler.bottom-right) {
    display: block !important;
    background: #3b82f6 !important;
    border: 2px solid #ffffff !important;
    width: 16px !important;
    height: 16px !important;
    border-radius: 50%;
    cursor: se-resize;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
}

:deep(.cropper-viewport) {
    background: transparent !important;
    border: none !important;
}

:deep(.cropper-face) {
    background: transparent !important;
}

.text-error {
    color: var(--red-500);
}

@media print {
    body * {
        visibility: hidden;
    }
    .surface-card,
    .surface-card * {
        visibility: visible;
    }
    .surface-card {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        padding: 20px;
    }
}

@media (max-width: 960px) {
    .flex-column-on-mobile {
        flex-direction: column !important;
    }
    .w-full-on-mobile {
        width: 100% !important;
    }
    .cropper-container {
        width: 250px;
        height: 250px;
    }
}

@media (max-width: 768px) {
    .cropper-container {
        width: 200px;
        height: 200px;
    }
}
</style>
