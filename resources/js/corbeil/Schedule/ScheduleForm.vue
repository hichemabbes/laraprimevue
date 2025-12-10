<template>
    <Dialog
        v-model:visible="visible"
        :style="{ width: '700px' }"
        :header="formTitle"
        :modal="true"
        :closable="false"
    >
        <div class="p-fluid form-grid">
            <div class="field col-12 md:col-6">
                <label for="groupe">Groupe</label>
                <Dropdown
                    id="groupe"
                    v-model="localEvent.groupeId"
                    :options="groupes"
                    optionLabel="nom"
                    optionValue="id"
                    placeholder="Sélectionnez un groupe"
                    :disabled="!!event?.id"
                    @change="updatePhaseOptions"
                />
            </div>

            <div class="field col-12 md:col-6">
                <label for="phase">Phase de formation</label>
                <Dropdown
                    id="phase"
                    v-model="localEvent.phaseId"
                    :options="phaseOptions"
                    optionLabel="nom"
                    optionValue="id"
                    placeholder="Sélectionnez une phase"
                    :disabled="!!event?.id"
                />
            </div>

            <div class="field col-12 md:col-6">
                <label for="module">Module</label>
                <MultiSelect
                    id="module"
                    v-model="localEvent.moduleIds"
                    :options="filteredModules"
                    optionLabel="nom"
                    optionValue="id"
                    placeholder="Sélectionnez un ou plusieurs modules"
                    :maxSelectedLabels="2"
                    @change="updateFormateursDisponibles"
                >
                    <template #option="slotProps">
                        <div class="flex align-items-center">
                            <span>{{ slotProps.option.nom }}</span>
                            <Chip
                                :label="`${slotProps.option.duree_totale}h`"
                                class="ml-2"
                            />
                        </div>
                    </template>
                </MultiSelect>
            </div>

            <div class="field col-12 md:col-6">
                <label for="formateur">Formateur</label>
                <Dropdown
                    id="formateur"
                    v-model="localEvent.formateurId"
                    :options="formateursDisponibles"
                    optionLabel="nomComplet"
                    optionValue="id"
                    placeholder="Sélectionnez un formateur"
                    :filter="true"
                    @change="checkDisponibilite"
                >
                    <template #option="slotProps">
                        <div class="flex align-items-center">
                            <Avatar
                                :label="
                                    slotProps.option.prenom[0] +
                                    slotProps.option.nom[0]
                                "
                                size="small"
                                shape="circle"
                                class="mr-2"
                            />
                            <span>{{ slotProps.option.nomComplet }}</span>
                            <Chip
                                :label="`${calculateFormateurCharge(slotProps.option.id)}h`"
                                class="ml-2"
                                :severity="
                                    getChargeSeverity(slotProps.option.id)
                                "
                            />
                        </div>
                    </template>
                </Dropdown>
                <small v-if="formateurCharge > 0" class="p-error">
                    Charge hebdomadaire: {{ formateurCharge }}h (Max:
                    {{ formateurMaxHours }}h)
                </small>
            </div>

            <div class="field col-12 md:col-6">
                <label for="espace">Espace pédagogique</label>
                <Dropdown
                    id="espace"
                    v-model="localEvent.espaceId"
                    :options="espacesDisponibles"
                    optionLabel="nom"
                    optionValue="id"
                    placeholder="Sélectionnez un espace"
                    :filter="true"
                    @change="checkDisponibilite"
                >
                    <template #option="slotProps">
                        <div class="flex align-items-center">
                            <span>{{ slotProps.option.nom }}</span>
                            <Chip :label="slotProps.option.type" class="ml-2" />
                            <Chip
                                :label="`${slotProps.option.capacite} places`"
                                class="ml-2"
                            />
                        </div>
                    </template>
                </Dropdown>
            </div>

            <div class="field col-12 md:col-6">
                <label for="date">Date</label>
                <Calendar
                    id="date"
                    v-model="localEvent.start"
                    :timeOnly="false"
                    :showTime="true"
                    :showSeconds="false"
                    :hourFormat="24"
                    dateFormat="dd/mm/yy"
                    @date-select="updateEndTime"
                />
            </div>

            <div class="field col-12 md:col-6">
                <label for="duree">Durée (heures)</label>
                <InputNumber
                    id="duree"
                    v-model="dureeHeures"
                    :min="1"
                    :max="4"
                    :step="0.5"
                    showButtons
                    @update:modelValue="updateEndTime"
                />
            </div>

            <div class="field col-12 md:col-6">
                <label for="fin">Heure de fin</label>
                <Calendar
                    id="fin"
                    v-model="localEvent.end"
                    :timeOnly="true"
                    :showSeconds="false"
                    :hourFormat="24"
                    :disabled="true"
                />
            </div>

            <div class="field col-12">
                <label for="description">Description/Objectifs</label>
                <Textarea
                    id="description"
                    v-model="localEvent.description"
                    rows="3"
                    autoResize
                />
            </div>

            <div class="field col-12">
                <ToggleButton
                    v-model="localEvent.recurrent"
                    onLabel="Séance récurrente"
                    offLabel="Séance unique"
                    onIcon="pi pi-check"
                    offIcon="pi pi-times"
                    class="w-full"
                />
            </div>

            <div v-if="localEvent.recurrent" class="field col-12">
                <label>Récurrence</label>
                <div class="flex flex-wrap gap-3">
                    <div class="flex align-items-center">
                        <RadioButton
                            v-model="localEvent.recurrenceType"
                            inputId="hebdo"
                            name="recurrence"
                            value="hebdo"
                        />
                        <label for="hebdo" class="ml-2">Hebdomadaire</label>
                    </div>
                    <div class="flex align-items-center">
                        <RadioButton
                            v-model="localEvent.recurrenceType"
                            inputId="quinzaine"
                            name="recurrence"
                            value="quinzaine"
                        />
                        <label for="quinzaine" class="ml-2"
                            >Toutes les 2 semaines</label
                        >
                    </div>
                </div>
            </div>

            <div v-if="conflictsExist" class="field col-12">
                <Message severity="warn" :closable="false">
                    <div class="flex flex-column">
                        <span class="font-bold">Conflits détectés:</span>
                        <div
                            v-for="(conflictType, type) in conflicts"
                            :key="type"
                        >
                            <div v-if="conflictType.length > 0" class="mt-2">
                                <span class="font-medium capitalize"
                                    >{{ type }}:</span
                                >
                                <ul class="mt-1 pl-3">
                                    <li
                                        v-for="(conflict, idx) in conflictType"
                                        :key="idx"
                                    >
                                        {{ formatConflict(conflict, type) }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </Message>
            </div>
        </div>

        <template #footer>
            <Button
                label="Annuler"
                icon="pi pi-times"
                @click="visible = false"
                class="p-button-text"
            />
            <Button
                label="Supprimer"
                icon="pi pi-trash"
                @click="handleDelete"
                class="p-button-danger"
                v-if="event?.id"
            />
            <Button
                label="Enregistrer"
                icon="pi pi-check"
                @click="handleSave"
                :disabled="conflictsExist && !forceSave"
                autofocus
            />
            <Button
                v-if="conflictsExist"
                label="Forcer l'enregistrement"
                icon="pi pi-exclamation-triangle"
                @click="forceSaveAndClose"
                class="p-button-warning"
            />
        </template>
    </Dialog>
</template>

<script>
import { ref, computed, watch } from 'vue';
import { useStore } from 'vuex';
import { useConstraints } from '@/composables/useConstraints.js';
import dayjs from 'dayjs';

export default {
    props: {
        modelValue: Boolean,
        event: Object,
        conflicts: Object,
    },
    emits: ['update:modelValue', 'save', 'delete'],
    setup(props, { emit }) {
        const store = useStore();
        const {
            checkEventConflicts,
            checkEspaceDisponibility,
            checkFormateurDisponibility,
            calculateWeeklyHours,
            getEventDuration,
            isTimeOverlap,
        } = useConstraints();

        const visible = computed({
            get() {
                return props.modelValue;
            },
            set(value) {
                emit('update:modelValue', value);
            },
        });

        const localEvent = ref({
            id: null,
            title: '',
            start: null,
            end: null,
            groupeId: null,
            phaseId: null,
            moduleIds: [],
            formateurId: null,
            espaceId: null,
            description: '',
            recurrent: false,
            recurrenceType: 'hebdo',
        });

        const dureeHeures = ref(2);
        const formateursDisponibles = ref([]);
        const espacesDisponibles = ref([]);
        const phaseOptions = ref([]);
        const formateurCharge = ref(0);
        const formateurMaxHours = ref(35);
        const forceSave = ref(false);
        const currentConflicts = ref({});

        // Mettre à jour le formulaire quand l'événement change
        watch(
            () => props.event,
            (newEvent) => {
                if (newEvent) {
                    localEvent.value = {
                        ...newEvent,
                        moduleIds:
                            newEvent.moduleIds ||
                            [newEvent.moduleId].filter(Boolean),
                    };

                    if (newEvent.start && newEvent.end) {
                        const diff = dayjs(newEvent.end).diff(
                            dayjs(newEvent.start),
                            'hour',
                            true
                        );
                        dureeHeures.value = diff;
                    }

                    updatePhaseOptions();
                    updateFormateursDisponibles();
                    updateEspacesDisponibles();
                    checkDisponibilite();
                } else {
                    resetForm();
                }
            },
            { deep: true }
        );

        // Mettre à jour les conflits quand ils changent
        watch(
            () => props.conflicts,
            (newConflicts) => {
                currentConflicts.value = newConflicts || {};
            },
            { deep: true }
        );

        const formTitle = computed(() => {
            return localEvent.value.id
                ? 'Modifier la séance'
                : 'Créer une nouvelle séance';
        });

        const groupes = computed(() => store.state.schedule.groupes);
        const filteredModules = computed(() => store.state.schedule.modules);
        const conflictsExist = computed(() => {
            return Object.values(currentConflicts.value).some(
                (conflicts) => conflicts.length > 0
            );
        });

        const resetForm = () => {
            localEvent.value = {
                id: null,
                title: '',
                start: null,
                end: null,
                groupeId: store.state.schedule.currentFilters.groupeId,
                phaseId: store.state.schedule.currentFilters.phaseId,
                moduleIds: [],
                formateurId: null,
                espaceId: null,
                description: '',
                recurrent: false,
                recurrenceType: 'hebdo',
            };
            dureeHeures.value = 2;
            formateurCharge.value = 0;
            forceSave.value = false;
        };

        const updateEndTime = () => {
            if (localEvent.value.start) {
                localEvent.value.end = dayjs(localEvent.value.start)
                    .add(dureeHeures.value, 'hour')
                    .toDate();
            }
        };

        const updatePhaseOptions = () => {
            if (localEvent.value.groupeId) {
                phaseOptions.value = store.state.schedule.phases.filter(
                    (p) => p.groupe_id === localEvent.value.groupeId
                );
                if (phaseOptions.value.length === 1) {
                    localEvent.value.phaseId = phaseOptions.value[0].id;
                }
            } else {
                phaseOptions.value = [];
                localEvent.value.phaseId = null;
            }
        };

        const updateFormateursDisponibles = () => {
            if (localEvent.value.moduleIds.length === 0) {
                formateursDisponibles.value = [];
                return;
            }

            // Ici vous devriez filtrer les formateurs qui enseignent ces modules
            // Pour l'exemple, nous prenons tous les formateurs
            formateursDisponibles.value = store.state.schedule.formateurs.map(
                (f) => ({
                    ...f,
                    nomComplet: `${f.prenom} ${f.nom}`,
                })
            );
        };

        const updateEspacesDisponibles = () => {
            // Filtrer par capacité si nécessaire
            const groupe = store.state.schedule.groupes.find(
                (g) => g.id === localEvent.value.groupeId
            );
            if (groupe) {
                espacesDisponibles.value = store.state.schedule.espaces.filter(
                    (e) => e.capacite >= groupe.nombre_etudiants
                );
            } else {
                espacesDisponibles.value = [...store.state.schedule.espaces];
            }
        };

        const checkDisponibilite = () => {
            if (!localEvent.value.start || !localEvent.value.end) return;

            // Vérifier la disponibilité du formateur
            if (localEvent.value.formateurId) {
                const formateur = store.state.schedule.formateurs.find(
                    (f) => f.id === localEvent.value.formateurId
                );
                if (formateur) {
                    const regime = store.state.schedule.regimesHoraires.find(
                        (r) => r.id === formateur.regime_horaire_id
                    );
                    formateurMaxHours.value = regime?.heures_semaine || 35;
                    formateurCharge.value =
                        calculateWeeklyHours(
                            localEvent.value.formateurId,
                            localEvent.value.start
                        ) + getEventDuration(localEvent.value);
                }
            }

            // Vérifier les conflits
            currentConflicts.value = checkEventConflicts(localEvent.value);
        };

        const calculateFormateurCharge = (formateurId) => {
            return calculateWeeklyHours(
                formateurId,
                localEvent.value.start || new Date()
            );
        };

        const getChargeSeverity = (formateurId) => {
            const charge = calculateFormateurCharge(formateurId);
            const formateur = store.state.schedule.formateurs.find(
                (f) => f.id === formateurId
            );
            const regime = formateur
                ? store.state.schedule.regimesHoraires.find(
                      (r) => r.id === formateur.regime_horaire_id
                  )
                : null;
            const maxHours = regime?.heures_semaine || 35;

            if (charge >= maxHours) return 'danger';
            if (charge >= maxHours * 0.8) return 'warning';
            return 'success';
        };

        const formatConflict = (conflict, type) => {
            if (typeof conflict === 'string') return conflict;

            switch (type) {
                case 'formateur':
                    return `Formateur déjà occupé avec ${conflict.title}`;
                case 'espace':
                    return `Espace déjà réservé pour ${conflict.title}`;
                case 'groupe':
                    return `Groupe déjà en séance avec ${conflict.title}`;
                default:
                    return JSON.stringify(conflict);
            }
        };

        const handleSave = () => {
            const eventToSave = {
                ...localEvent.value,
                moduleId:
                    localEvent.value.moduleIds.length === 1
                        ? localEvent.value.moduleIds[0]
                        : null,
                conflicts: currentConflicts.value,
            };

            emit('save', eventToSave);
            visible.value = false;
        };

        const forceSaveAndClose = () => {
            forceSave.value = true;
            handleSave();
        };

        const handleDelete = () => {
            emit('delete', localEvent.value.id);
            visible.value = false;
        };

        return {
            localEvent,
            dureeHeures,
            formateursDisponibles,
            espacesDisponibles,
            phaseOptions,
            formateurCharge,
            formateurMaxHours,
            forceSave,
            currentConflicts,
            groupes,
            filteredModules,
            conflictsExist,
            visible,
            formTitle,
            resetForm,
            updateEndTime,
            updatePhaseOptions,
            updateFormateursDisponibles,
            updateEspacesDisponibles,
            checkDisponibilite,
            calculateFormateurCharge,
            getChargeSeverity,
            formatConflict,
            handleSave,
            forceSaveAndClose,
            handleDelete,
        };
    },
};
</script>

<style scoped>
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.field {
    margin-bottom: 1rem;
}

@media (max-width: 960px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
