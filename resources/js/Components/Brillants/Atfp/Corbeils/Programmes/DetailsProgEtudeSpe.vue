<template>
    <Dialog
        :visible="visible"
        :header="`Programmes d'Études - ${specialite?.nom_fr || ''}`"
        :style="{ width: '800px' }"
        :modal="true"
        @update:visible="$emit('update:visible', $event)"
    >
        <div class="surface-ground">
            <!-- Specialite Information -->
            <div class="surface-card p-3 mb-4 border-round">
                <div class="flex flex-column gap-2">
                    <div class="flex justify-content-between">
                        <span class="font-medium">Code:</span>
                        <span>{{ specialite?.code || '-' }}</span>
                    </div>
                    <div class="flex justify-content-between">
                        <span class="font-medium">Nom (FR):</span>
                        <span>{{ specialite?.nom_fr || '-' }}</span>
                    </div>
                    <div class="flex justify-content-between">
                        <span class="font-medium">Nom (AR):</span>
                        <span>{{ specialite?.nom_ar || '-' }}</span>
                    </div>
                    <div class="flex justify-content-between">
                        <span class="font-medium">Diplôme:</span>
                        <span>{{ specialite?.diplome || '-' }}</span>
                    </div>
                    <div class="flex justify-content-between">
                        <span class="font-medium">Durée Formation:</span>
                        <span
                            >{{
                                specialite?.infos_specialites?.[0]
                                    ?.duree_formation || '-'
                            }}
                            ans</span
                        >
                    </div>
                    <div class="flex justify-content-between">
                        <span class="font-medium">Homologation:</span>
                        <span>{{
                            specialite?.infos_specialites?.[0]
                                ?.homologation_fr || '-'
                        }}</span>
                    </div>
                </div>
            </div>

            <!-- Programmes d'Études Table -->
            <DataTable
                :value="specialite?.programmes_etudes || []"
                size="small"
                stripedRows
                :loading="loading"
                class="p-datatable-sm"
                scrollable
                scrollHeight="400px"
            >
                <template #empty>
                    <div class="text-center py-4">
                        <i class="pi pi-inbox text-4xl text-400 mb-2" />
                        <p class="text-600">Aucun programme d'étude trouvé</p>
                    </div>
                </template>
                <Column
                    field="version"
                    header="Version Programme d'Étude"
                    style="min-width: 12rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.version || '-' }}</span>
                    </template>
                </Column>
                <Column
                    field="programme.version"
                    header="Version Programme"
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <span>{{
                            data.programme ? data.programme.version : '-'
                        }}</span>
                    </template>
                </Column>
                <Column
                    field="memoire.reference"
                    header="Référence Mémoire"
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <span>{{
                            data.memoire ? data.memoire.reference : '-'
                        }}</span>
                    </template>
                </Column>
                <Column
                    field="date_debut"
                    header="Date Début"
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="formatDate(data.date_debut)"
                            icon="pi pi-calendar"
                        />
                    </template>
                </Column>
                <Column
                    field="date_fin"
                    header="Date Fin"
                    style="min-width: 10rem"
                >
                    <template #body="{ data }">
                        <Tag
                            :value="formatDate(data.date_fin)"
                            icon="pi pi-calendar"
                        />
                    </template>
                </Column>
                <Column
                    field="description"
                    header="Description"
                    style="min-width: 15rem"
                >
                    <template #body="{ data }">
                        <span>{{ data.description || '-' }}</span>
                    </template>
                </Column>
                <Column field="statut" header="Statut" style="min-width: 10rem">
                    <template #body="{ data }">
                        <Tag
                            :value="data.statut || '-'"
                            :severity="getSeverity(data.statut)"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>

        <template #footer>
            <Button
                label="Fermer"
                icon="pi pi-times"
                class="p-button-text"
                @click="$emit('update:visible', false)"
            />
        </template>
    </Dialog>
</template>

<script>
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';

export default {
    components: {
        Dialog,
        DataTable,
        Column,
        Tag,
        Button,
    },
    props: {
        visible: {
            type: Boolean,
            required: true,
        },
        specialiteId: {
            type: [Number, String],
            required: true,
        },
    },
    setup() {
        return { toast: useToast() };
    },
    data() {
        return {
            specialite: null,
            loading: false,
        };
    },
    watch: {
        visible(newVal) {
            if (newVal) {
                this.fetchSpecialite();
            }
        },
    },
    methods: {
        async fetchSpecialite() {
            this.loading = true;
            try {
                const response = await axios.get(
                    `/specialites/${this.specialiteId}`
                );
                this.specialite = response.data;
            } catch (error) {
                this.showError('Erreur lors du chargement de la spécialité');
            } finally {
                this.loading = false;
            }
        },
        formatDate(date) {
            if (!date) return '-';
            const d = new Date(date);
            return d.toLocaleDateString('fr-FR');
        },
        getSeverity(statut) {
            const statusSeverity = {
                Actif: 'success',
                Inactif: 'danger',
                'En attente': 'warning',
                Archivé: 'info',
            };
            return statusSeverity[statut] || 'secondary';
        },
        showError(message) {
            this.toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: message,
                life: 5000,
            });
        },
    },
};
</script>

<style scoped>
.surface-card {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
</style>
