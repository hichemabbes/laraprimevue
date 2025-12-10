<template>
    <div class="surface-ground p-3">
        <Toast position="top-right" />

        <div class="flex justify-content-between align-items-center mb-4">
            <div class="flex align-items-center gap-3">
                <Button
                    icon="pi pi-arrow-left"
                    class="p-button-rounded p-button-text"
                    @click="previousDay"
                />
                <h2 class="m-0">{{ formattedDate }}</h2>
                <Button
                    icon="pi pi-arrow-right"
                    class="p-button-rounded p-button-text"
                    @click="nextDay"
                />
                <Button
                    label="Aujourd'hui"
                    class="p-button-text"
                    @click="goToToday"
                />
            </div>
            <div class="ml-auto">
                <Button
                    icon="pi pi-plus"
                    class="p-button-primary"
                    @click="showAddEventDialog(null)"
                />
            </div>
        </div>

        <Card>
            <template #content>
                <div class="timeline-container">
                    <div class="timeline-hours">
                        <div
                            v-for="hour in hours"
                            :key="hour"
                            class="hour-label"
                        >
                            {{ hour }}:00
                        </div>
                    </div>

                    <div class="timeline-events">
                        <div
                            v-for="hour in hours"
                            :key="hour"
                            class="hour-slot"
                            @click="showAddEventDialog(hour)"
                        >
                            <div
                                v-for="event in getEventsForHour(hour)"
                                :key="event.id"
                                class="event-card"
                                :style="{
                                    backgroundColor: event.backgroundColor,
                                    color: event.textColor,
                                    borderLeft: `4px solid ${event.borderColor}`,
                                }"
                                @click.stop="editEvent(event)"
                            >
                                <div class="event-time">
                                    {{ formatTime(event.start) }} -
                                    {{ formatTime(event.end) }}
                                </div>
                                <div class="event-title">
                                    <i
                                        :class="getEventIcon(event)"
                                        class="mr-2"
                                    ></i>
                                    {{ event.title }}
                                </div>
                                <div
                                    v-if="event.description"
                                    class="event-description"
                                >
                                    {{ event.description }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </Card>

        <!-- Event Dialog -->
        <Dialog
            v-model:visible="displayDialog"
            :style="{ width: '600px' }"
            :header="dialogTitle"
            :modal="true"
        >
            <div class="grid p-fluid">
                <div class="col-12 md:col-6">
                    <div class="field">
                        <label for="eventType">Type</label>
                        <Dropdown
                            v-model="currentEvent.type"
                            :options="eventTypes"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Sélectionnez un type"
                        />
                    </div>
                </div>

                <div class="col-12">
                    <div class="field">
                        <label for="title"
                            >Titre <span class="text-red-500">*</span></label
                        >
                        <InputText
                            id="title"
                            v-model="currentEvent.title"
                            required
                            :autofocus="true"
                        />
                    </div>
                </div>

                <div class="col-12 md:col-6">
                    <div class="field">
                        <label for="start">Début</label>
                        <Calendar
                            id="start"
                            v-model="currentEvent.start"
                            showTime
                            hourFormat="24"
                            dateFormat="dd/mm/yy"
                        />
                    </div>
                </div>

                <div class="col-12 md:col-6">
                    <div class="field">
                        <label for="end">Fin</label>
                        <Calendar
                            id="end"
                            v-model="currentEvent.end"
                            showTime
                            hourFormat="24"
                            dateFormat="dd/mm/yy"
                        />
                    </div>
                </div>

                <div class="col-12">
                    <div class="field">
                        <label for="description">Description</label>
                        <Textarea
                            id="description"
                            v-model="currentEvent.description"
                            rows="3"
                        />
                    </div>
                </div>

                <div class="col-12">
                    <div class="field">
                        <label for="color">Couleur</label>
                        <div class="flex align-items-center gap-3">
                            <ColorPicker
                                v-model="currentEvent.color"
                                format="hex"
                            />
                            <div
                                class="w-2rem h-2rem border-1 surface-border border-round"
                                :style="{ backgroundColor: currentEvent.color }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-content-between w-full">
                    <Button
                        v-if="currentEvent.id"
                        label="Supprimer"
                        icon="pi pi-trash"
                        class="p-button-outlined p-button-danger"
                        @click="deleteEvent"
                    />
                    <div class="flex gap-2">
                        <Button
                            label="Annuler"
                            icon="pi pi-times"
                            class="p-button-text"
                            @click="displayDialog = false"
                        />
                        <Button
                            label="Enregistrer"
                            icon="pi pi-check"
                            class="p-button-primary"
                            @click="saveEvent"
                        />
                    </div>
                </div>
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { format, parseISO, addDays, isToday, isSameDay } from 'date-fns';
import { fr } from 'date-fns/locale';

const router = useRouter();
const toast = useToast();

// Date management
const currentDate = ref(new Date());
const hours = Array.from({ length: 24 }, (_, i) => i); // 0-23 hours

const formattedDate = computed(() => {
    return format(currentDate.value, 'EEEE d MMMM yyyy', { locale: fr });
});

const previousDay = () => {
    currentDate.value = addDays(currentDate.value, -1);
    fetchEvents();
};

const nextDay = () => {
    currentDate.value = addDays(currentDate.value, 1);
    fetchEvents();
};

const goToToday = () => {
    currentDate.value = new Date();
    fetchEvents();
};

// Events management
const events = ref([]);
const displayDialog = ref(false);
const currentEvent = ref(createEmptyEvent());

const eventTypes = [
    { label: 'Événement', value: 'événement' },
    { label: 'Tâche', value: 'tâche' },
    { label: 'Note', value: 'note' },
    { label: 'Réunion', value: 'réunion' },
];

const dialogTitle = computed(() => {
    return currentEvent.value.id ? 'Modifier événement' : 'Nouvel événement';
});

function createEmptyEvent(hour = null) {
    const start = new Date(currentDate.value);
    if (hour !== null) {
        start.setHours(hour, 0, 0, 0);
    } else {
        start.setHours(new Date().getHours(), 0, 0, 0);
    }

    const end = new Date(start);
    end.setHours(start.getHours() + 1);

    return {
        id: null,
        title: '',
        start: start,
        end: end,
        description: '',
        type: 'événement',
        color: '#3b82f6',
    };
}

function showAddEventDialog(hour) {
    currentEvent.value = createEmptyEvent(hour);
    displayDialog.value = true;
}

function editEvent(event) {
    currentEvent.value = {
        id: event.id,
        title: event.title,
        start: new Date(event.start),
        end: new Date(event.end),
        description: event.description,
        type: event.extendedProps?.type || 'événement',
        color: event.backgroundColor || '#3b82f6',
    };
    displayDialog.value = true;
}

async function fetchEvents() {
    try {
        const startOfDay = new Date(currentDate.value);
        startOfDay.setHours(0, 0, 0, 0);

        const endOfDay = new Date(currentDate.value);
        endOfDay.setHours(23, 59, 59, 999);

        const response = await axios.get('/api/calendrier', {
            params: {
                start: startOfDay.toISOString(),
                end: endOfDay.toISOString(),
            },
        });

        events.value = response.data.map((event) => ({
            ...event,
            start: event.start,
            end: event.end,
            backgroundColor: event.backgroundColor || event.color || '#3b82f6',
            borderColor:
                event.borderColor || event.backgroundColor || '#3b82f6',
            textColor: getContrastColor(
                event.backgroundColor || event.color || '#3b82f6'
            ),
            extendedProps: {
                type: event.type || 'événement',
                description: event.description,
            },
        }));
    } catch (error) {
        console.error('Error fetching events:', error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Impossible de charger les événements',
            life: 3000,
        });
    }
}

function getEventsForHour(hour) {
    const hourStart = new Date(currentDate.value);
    hourStart.setHours(hour, 0, 0, 0);

    const hourEnd = new Date(currentDate.value);
    hourEnd.setHours(hour, 59, 59, 999);

    return events.value.filter((event) => {
        const eventStart = new Date(event.start);
        const eventEnd = new Date(event.end || event.start);

        return (
            (eventStart >= hourStart && eventStart <= hourEnd) ||
            (eventEnd >= hourStart && eventEnd <= hourEnd) ||
            (eventStart <= hourStart && eventEnd >= hourEnd)
        );
    });
}

function formatTime(date) {
    return format(new Date(date), 'HH:mm');
}

function getEventIcon(event) {
    const type = event.extendedProps?.type || 'événement';
    switch (type) {
        case 'tâche':
            return 'pi pi-check-circle';
        case 'note':
            return 'pi pi-book';
        case 'réunion':
            return 'pi pi-users';
        default:
            return 'pi pi-calendar';
    }
}

function getContrastColor(hexColor) {
    hexColor = hexColor.replace('#', '');
    const r = parseInt(hexColor.substr(0, 2), 16);
    const g = parseInt(hexColor.substr(2, 2), 16);
    const b = parseInt(hexColor.substr(4, 2), 16);
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    return brightness > 128 ? '#000000' : '#ffffff';
}

async function saveEvent() {
    if (!currentEvent.value.title) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Le titre est requis',
            life: 3000,
        });
        return;
    }

    try {
        const eventData = {
            titre: currentEvent.value.title,
            date_debut: currentEvent.value.start.toISOString(),
            date_fin: currentEvent.value.end.toISOString(),
            description: currentEvent.value.description,
            type: currentEvent.value.type,
            color: currentEvent.value.color.startsWith('#')
                ? currentEvent.value.color
                : `#${currentEvent.value.color}`,
        };

        let response;
        if (currentEvent.value.id) {
            response = await axios.put(
                `/api/calendrier/${currentEvent.value.id}`,
                eventData
            );
        } else {
            response = await axios.post('/api/calendrier', eventData);
        }

        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: currentEvent.value.id
                ? 'Événement mis à jour'
                : 'Événement créé',
            life: 3000,
        });

        displayDialog.value = false;
        await fetchEvents();
    } catch (error) {
        console.error('Error saving event:', error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail:
                error.response?.data?.message || 'Erreur lors de la sauvegarde',
            life: 3000,
        });
    }
}

async function deleteEvent() {
    if (!currentEvent.value.id) return;

    try {
        await axios.delete(`/api/calendrier/${currentEvent.value.id}`);
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Événement supprimé',
            life: 3000,
        });
        displayDialog.value = false;
        await fetchEvents();
    } catch (error) {
        console.error('Error deleting event:', error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail:
                error.response?.data?.message ||
                'Erreur lors de la suppression',
            life: 3000,
        });
    }
}

onMounted(() => {
    fetchEvents();
});
</script>

<style scoped>
.timeline-container {
    display: flex;
    height: calc(100vh - 200px);
    overflow-y: auto;
    border: 1px solid var(--surface-border);
    border-radius: 6px;
}

.timeline-hours {
    width: 80px;
    border-right: 1px solid var(--surface-border);
    background-color: var(--surface-50);
}

.hour-label {
    height: 60px;
    padding: 0.5rem;
    display: flex;
    align-items: center;
    font-size: 0.875rem;
    color: var(--text-color-secondary);
    border-bottom: 1px solid var(--surface-border);
}

.timeline-events {
    flex: 1;
}

.hour-slot {
    height: 60px;
    padding: 2px;
    border-bottom: 1px solid var(--surface-border);
    position: relative;
    cursor: pointer;
    transition: background-color 0.2s;
}

.hour-slot:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

.hour-slot.current-hour {
    background-color: rgba(59, 130, 246, 0.1);
}

.event-card {
    padding: 0.5rem;
    margin-bottom: 2px;
    border-radius: 4px;
    font-size: 0.875rem;
    cursor: pointer;
    transition:
        transform 0.2s,
        box-shadow 0.2s;
}

.event-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.event-time {
    font-size: 0.75rem;
    opacity: 0.8;
    margin-bottom: 0.25rem;
}

.event-title {
    font-weight: 500;
    display: flex;
    align-items: center;
}

.event-description {
    font-size: 0.75rem;
    margin-top: 0.25rem;
    opacity: 0.9;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.current-time-indicator {
    position: absolute;
    left: 0;
    right: 0;
    height: 2px;
    background-color: var(--primary-color);
    z-index: 10;
}

.current-time-indicator::before {
    content: '';
    position: absolute;
    left: -6px;
    top: -4px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: var(--primary-color);
}

@media (max-width: 768px) {
    .timeline-container {
        flex-direction: column;
        height: auto;
    }

    .timeline-hours {
        width: 100%;
        display: flex;
        overflow-x: auto;
        border-right: none;
        border-bottom: 1px solid var(--surface-border);
    }

    .hour-label {
        width: 60px;
        height: auto;
        flex-shrink: 0;
        border-bottom: none;
        border-right: 1px solid var(--surface-border);
    }

    .hour-slot {
        height: auto;
        min-height: 60px;
    }
}
</style>
