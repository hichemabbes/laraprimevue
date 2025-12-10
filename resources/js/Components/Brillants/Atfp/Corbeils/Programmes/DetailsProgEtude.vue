<template>
    <Dialog
        :visible="visible"
        :header="`Détails Programme d'Étude - ${programmeEtude?.version || ''}`"
        :style="{ width: '800px' }"
        :modal="true"
        @update:visible="$emit('update:visible', $event)"
    >
        <div class="surface-ground">
            <!-- Programme d'Étude Information -->
            <div class="surface-card p-3 mb-4 border-round">
                <div class="flex flex-column gap-2">
                    <div class="flex justify-content-between">
                        <span class="font-medium">Version:</span>
                        <span>{{ programmeEtude?.version || '-' }}</span>
                    </div>
                    <div class="flex justify-content-between">
                        <span class="font-medium">Spécialité:</span>
                        <span>{{
                            programmeEtude?.specialite?.nom_fr || '-'
                        }}</span>
                    </div>
                    <div class="flex justify-content-between">
                        <span class="font-medium">Date Début:</span>
                        <span>{{
                            formatDate(programmeEtude?.date_debut)
                        }}</span>
                    </div>
                    <div class="flex justify-content-between">
                        <span class="font-medium">Date Fin:</span>
                        <span>{{ formatDate(programmeEtude?.date_fin) }}</span>
                    </div>
                    <div class="flex justify-content-between">
                        <span class="font-medium">Description:</span>
                        <span>{{ programmeEtude?.description || '-' }}</span>
                    </div>
                    <div class="flex justify-content-between">
                        <span class="font-medium">Statut:</span>
                        <Tag
                            :value="programmeEtude?.statut || '-'"
                            :severity="getSeverity(programmeEtude?.statut)"
                        />
                    </div>
                </div>
            </div>

            <!-- Programme Information (if exists) -->
            <div
                v-if="programmeEtude?.programme"
                class="surface-card p-3 mb-4 border-round"
            >
                <h3 class="font-medium mb-3">Programme Associé</h3>
                <div class="flex flex-column gap-2">
                    <div class="flex justify-content-between">
                        <span class="font-medium">Version:</span>
                        <span>{{
                            programmeEtude.programme.version || '-'
                        }}</span>
                    </div>
                    <div class="flex justify-content-between">
                        <span class="font-medium">Date Début:</span>
                        <span>{{
                            formatDate(programmeEtude.programme.date_debut)
                        }}</span>
                    </div>
                    <div class="flex justify-content-between">
                        <span class="font-medium">Date Fin:</span>
                        <span>{{
                            formatDate(programmeEtude.programme.date_fin)
                        }}</span>
                    </div>
                    <div class="flex justify-content-between">
                        <span class="font-medium">Statut:</span>
                        <Tag
                            :value="programmeEtude.programme.statut || '-'"
                            :severity="
                                getSeverity(programmeEtude.programme.statut)
                            "
                        />
                    </div>
                </div>
            </div>

            <!-- Mémoire Information (if exists) -->
            <div
                v-if="programmeEtude?.memoire"
                class="surface-card p-3 mb-4 border-round"
            >
                <h3 class="font-medium mb-3">Mémoire Associé</h3>
                <div class="flex flex-column gap-2">
                    <div class="flex justify-content-between">
                        <span class="font-medium">Référence:</span>
                        <span>{{
                            programmeEtude.memoire.reference || '-'
                        }}</span>
                    </div>
                    <div class="flex justify-content-between">
                        <span class="font-medium">Numéro Mémoire:</span>
                        <span>{{
                            programmeEtude.memoire.numero_memoire || '-'
                        }}</span>
                    </div>
                    <div class="flex justify-content-between">
                        <span class="font-medium">Date Mémoire:</span>
                        <span>{{
                            formatDate(programmeEtude.memoire.date_memoire)
                        }}</span>
                    </div>
                    <div class="flex justify-content-between">
                        <span class="font-medium">Date Début:</span>
                        <span>{{
                            formatDate(programmeEtude.memoire.date_debut)
                        }}</span>
                    </div>
                    <div class="flex justify-content-between">
                        <span class="font-medium">Date Fin:</span>
                        <span>{{
                            formatDate(programmeEtude.memoire.date_fin)
                        }}</span>
                    </div>
                    <div class="flex justify-content-between">
                        <span class="font-medium">Statut:</span>
                        <Tag
                            :value="programmeEtude.memoire.statut || '-'"
                            :severity="
                                getSeverity(programmeEtude.memoire.statut)
                            "
                        />
                    </div>
                </div>
            </div>
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
import Tag from 'primevue/tag';
import Button from 'primevue/button';

export default {
    components: {
        Dialog,
        Tag,
        Button,
    },
    props: {
        visible: {
            type: Boolean,
            required: true,
        },
        programmeEtudeId: {
            type: [Number, String],
            required: true,
        },
    },
    setup() {
        return { toast: useToast() };
    },
    data() {
        return {
            programmeEtude: null,
            loading: false,
        };
    },
    watch: {
        visible(newVal) {
            if (newVal) {
                this.fetchProgrammeEtude();
            }
        },
    },
    methods: {
        async fetchProgrammeEtude() {
            this.loading = true;
            try {
                const response = await axios.get(
                    `/programmes-etudes/${this.programmeEtudeId}`
                );
                this.programmeEtude = response.data;
            } catch (error) {
                this.showError(
                    "Erreur lors du chargement du programme d'étude"
                );
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
