<template>
    <div class="surface-ground min-h-screen">
        <!-- Header Card with Navigation -->
        <div
            class="surface-card p-2 border-round border-1 surface-border"
            style="
                position: relative;
                top: -30px;
                box-shadow: none;
                margin-bottom: -26px;
            "
        >
            <div class="flex justify-content-between align-items-center">
                <div class="flex gap-3">
                    <Button
                        label="Accueil"
                        icon="pi pi-home"
                        class="p-button-text"
                        @click="$router.push({ name: 'dashboard' })"
                    />
                    <Button
                        label="Vacances & Jours Fériés"
                        icon="pi pi-calendar"
                        class="p-button-text"
                        @click="
                            $router.push({ name: 'periodes-repos-formation' })
                        "
                    />
                    <Button
                        label="Années de Formation"
                        icon="pi pi-sitemap"
                        class="p-button-text"
                        disabled
                    />
                </div>
                <Button
                    icon="pi pi-refresh"
                    class="p-button-text p-button-rounded p-button-plain"
                    @click="fetchEvents"
                />
            </div>
        </div>

        <!-- Main Content Section -->
        <div
            class="surface-card p-4 pt-2 border-round shadow-2 border-1 surface-border"
        >
            <!-- Table Header with Actions -->

            <!-- Calendar Card -->
            <Card class="shadow-3 border-round-lg overflow-hidden">
                <template #content>
                    <FullCalendar
                        :options="calendarOptions"
                        class="w-full h-full border-round-lg"
                        ref="fullCalendar"
                    />
                </template>
            </Card>

            <!-- Event Dialog -->
            <Dialog
                v-model:visible="displayDialog"
                :style="{ width: '500px' }"
                header="Détails de l'événement"
                :modal="true"
                :breakpoints="{ '960px': '90vw' }"
            >
                <div class="grid p-fluid">
                    <div class="col-12 md:col-6">
                        <div class="field">
                            <label
                                for="eventType"
                                class="font-medium text-900 mb-2"
                                >Type</label
                            >
                            <Dropdown
                                v-model="currentEvent.type"
                                :options="eventTypes"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Sélectionnez un type"
                                class="w-full"
                                inputClass="w-full"
                            />
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="field">
                            <label for="title" class="font-medium text-900 mb-2"
                                >Titre
                                <span class="text-red-500">*</span></label
                            >
                            <InputText
                                id="title"
                                v-model="currentEvent.title"
                                required
                                class="w-full"
                                placeholder="Titre de l'événement"
                            />
                        </div>
                    </div>

                    <div class="col-12 md:col-6">
                        <div class="field">
                            <label for="start" class="font-medium text-900 mb-2"
                                >Début</label
                            >
                            <Calendar
                                id="start"
                                v-model="currentEvent.start"
                                showTime
                                hourFormat="24"
                                dateFormat="dd/mm/yy"
                                @date-select="updateEndDateIfNeeded"
                                class="w-full"
                                inputClass="w-full"
                            />
                        </div>
                    </div>

                    <div class="col-12 md:col-6">
                        <div class="field">
                            <label for="end" class="font-medium text-900 mb-2"
                                >Fin</label
                            >
                            <Calendar
                                id="end"
                                v-model="currentEvent.end"
                                showTime
                                hourFormat="24"
                                dateFormat="dd/mm/yy"
                                class="w-full"
                                inputClass="w-full"
                            />
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="field">
                            <label
                                for="description"
                                class="font-medium text-900 mb-2"
                                >Description</label
                            >
                            <Textarea
                                id="description"
                                v-model="currentEvent.description"
                                rows="3"
                                class="w-full"
                                placeholder="Description de l'événement"
                            />
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="field">
                            <label
                                for="observation"
                                class="font-medium text-900 mb-2"
                                >Observation</label
                            >
                            <Textarea
                                id="observation"
                                v-model="currentEvent.observation"
                                rows="2"
                                class="w-full"
                                placeholder="Notes supplémentaires"
                            />
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="field">
                            <label for="color" class="font-medium text-900 mb-2"
                                >Couleur</label
                            >
                            <div class="flex align-items-center gap-3">
                                <ColorPicker
                                    v-model="currentEvent.color"
                                    format="hex"
                                    @update:modelValue="formatColor"
                                    class="flex-1"
                                />
                                <div
                                    class="w-2rem h-2rem border-1 surface-border border-round shadow-1"
                                    :style="{
                                        backgroundColor: currentEvent.color,
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <template #footer>
                    <div class="flex justify-content-between w-full">
                        <Button
                            v-if="
                                currentEvent.id &&
                                !currentEvent.id.startsWith('repos_')
                            "
                            label="Supprimer"
                            icon="pi pi-trash"
                            class="p-button-outlined p-button-danger"
                            @click="deleteEvent"
                        />
                        <div class="flex gap-2">
                            <Button
                                label="Annuler"
                                icon="pi pi-times"
                                class="p-button-outlined"
                                @click="displayDialog = false"
                            />
                            <Button
                                label="Enregistrer"
                                icon="pi pi-check"
                                class="p-button-primary"
                                @click="saveEvent"
                                :disabled="
                                    currentEvent.type === 'repos_formation' ||
                                    !currentEvent.title
                                "
                            />
                        </div>
                    </div>
                </template>
            </Dialog>

            <!-- Today View Dialog -->
            <Dialog
                v-model:visible="displayTodayView"
                :style="{ width: '600px' }"
                header="Événements du jour"
                :modal="true"
                :breakpoints="{ '960px': '90vw' }"
            >
                <TodayView />
                <template #footer>
                    <Button
                        label="Fermer"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="displayTodayView = false"
                    />
                </template>
            </Dialog>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import frLocale from '@fullcalendar/core/locales/fr';
import { useToast } from 'primevue/usetoast';
import { useRouter } from 'vue-router';
import TodayView from '@/Components/Tools/TodayView.vue';

// Fonction pour déterminer la couleur du texte en fonction de la luminosité du fond
function getContrastColor(hexColor) {
    hexColor = hexColor.replace('#', '');
    const r = parseInt(hexColor.substr(0, 2), 16);
    const g = parseInt(hexColor.substr(2, 2), 16);
    const b = parseInt(hexColor.substr(4, 2), 16);
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    return brightness > 128 ? '#000000' : '#ffffff';
}

const router = useRouter();
const toast = useToast();
const fullCalendar = ref(null);
const events = ref([]);
const displayDialog = ref(false);
const displayTodayView = ref(false);
const currentEvent = ref({
    id: null,
    title: '',
    start: null,
    end: null,
    description: '',
    observation: '',
    color: '#3b82f6',
    type: 'événement',
});

const eventTypes = ref([
    { label: 'Événement', value: 'événement' },
    { label: 'Tâche', value: 'tâche' },
    { label: 'Note', value: 'note' },
    { label: 'Vacance/Jour Férié', value: 'repos_formation' },
]);

const calendarOptions = ref({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    locale: frLocale,
    headerToolbar: {
        left: 'prev,next todayCustom',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay',
    },
    buttonText: {
        month: 'Mois',
        week: 'Semaine',
        day: 'Jour',
    },
    customButtons: {
        todayCustom: {
            text: "Aujourd'hui",
            click: function () {
                if (fullCalendar.value) {
                    const calendarApi = fullCalendar.value.getApi();
                    calendarApi.today();
                    displayTodayView.value = true;
                }
            },
        },
    },
    events: [],
    editable: true,
    selectable: true,
    selectMirror: true,
    select: handleDateSelect,
    eventClick: handleEventClick,
    eventDrop: handleEventDrop,
    eventResize: handleEventResize,
    eventContent: renderEventContent,
    height: 'auto',
    nowIndicator: true,
    firstDay: 1,
    dayMaxEvents: true,
    eventDisplay: 'block',
    eventTimeFormat: {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    },
    datesSet: handleDatesSet,
    views: {
        timeGridWeek: {
            dayHeaderFormat: {
                weekday: 'long',
                day: 'numeric',
                month: 'short',
            },
            slotMinTime: '00:00:00',
            slotMaxTime: '24:00:00',
            slotDuration: '00:30:00',
            allDaySlot: true,
            expandRows: true,
            slotLabelFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
            },
        },
        timeGridDay: {
            dayHeaderFormat: {
                weekday: 'long',
                day: 'numeric',
                month: 'short',
            },
            slotMinTime: '00:00:00',
            slotMaxTime: '24:00:00',
            slotDuration: '00:15:00',
            allDaySlot: true,
            expandRows: true,
            slotLabelFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
            },
        },
    },
});

function handleDatesSet(arg) {
    if (fullCalendar.value) {
        const calendarApi = fullCalendar.value.getApi();
        calendarApi.updateSize();
    }
}

function renderEventContent(eventInfo) {
    const typeIcon =
        {
            événement: 'pi pi-calendar',
            tâche: 'pi pi-check-circle',
            note: 'pi pi-book',
            repos_formation: 'pi pi-umbrella',
        }[eventInfo.event.extendedProps.type] || 'pi pi-calendar';

    const isReposFormation =
        eventInfo.event.extendedProps.type === 'repos_formation';
    const backgroundColor = isReposFormation
        ? eventInfo.event.backgroundColor
        : eventInfo.event.backgroundColor || '#3b82f6';
    const borderColor = isReposFormation
        ? eventInfo.event.borderColor
        : eventInfo.event.borderColor ||
          eventInfo.event.backgroundColor ||
          '#3b82f6';
    const textColor = getContrastColor(
        isReposFormation ? eventInfo.event.borderColor : backgroundColor
    );

    return {
        html: `
            <div class="fc-event-main p-2 border-round" style="color: ${textColor}; background-color: ${backgroundColor}; border-left: 4px solid ${borderColor};">
                <div class="flex align-items-center gap-2">
                    <i class="${typeIcon}" style="color: ${textColor}"></i>
                    <div class="fc-event-title font-medium">${eventInfo.event.title}</div>
                </div>
                ${
                    eventInfo.event.extendedProps.description
                        ? `<div class="text-sm mt-1" style="color: ${textColor}; opacity: 0.9;">${eventInfo.event.extendedProps.description}</div>`
                        : ''
                }
                ${
                    eventInfo.timeText
                        ? `<div class="text-xs mt-1 font-semibold" style="color: ${textColor};">${eventInfo.timeText}</div>`
                        : ''
                }
            </div>
        `,
    };
}

function handleDateSelect(selectInfo) {
    currentEvent.value = {
        id: null,
        title: '',
        start: new Date(selectInfo.startStr),
        end: selectInfo.endStr
            ? new Date(selectInfo.endStr)
            : new Date(
                  new Date(selectInfo.startStr).getTime() + 60 * 60 * 1000
              ),
        description: '',
        observation: '',
        color: '#3b82f6',
        type: 'événement',
    };
    displayDialog.value = true;
}

function handleEventClick(clickInfo) {
    if (clickInfo.event.extendedProps.type === 'repos_formation') {
        toast.add({
            severity: 'info',
            summary: 'Information',
            detail: 'Les périodes de repos doivent être modifiées via la gestion des années de formation.',
            life: 3000,
        });
        return;
    }

    currentEvent.value = {
        id: clickInfo.event.id,
        title: clickInfo.event.title,
        start: new Date(clickInfo.event.startStr),
        end: clickInfo.event.end ? new Date(clickInfo.event.endStr) : null,
        description: clickInfo.event.extendedProps?.description || '',
        observation: clickInfo.event.extendedProps?.observation || '',
        color: clickInfo.event.backgroundColor || '#3b82f6',
        type: clickInfo.event.extendedProps?.type || 'événement',
    };
    displayDialog.value = true;
}

function handleEventDrop(dropInfo) {
    if (dropInfo.event.extendedProps.type === 'repos_formation') {
        dropInfo.revert();
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Les périodes de repos ne peuvent pas être déplacées ici.',
            life: 3000,
        });
        return;
    }
    updateEventInStore(dropInfo.event);
}

function handleEventResize(resizeInfo) {
    if (resizeInfo.event.extendedProps.type === 'repos_formation') {
        resizeInfo.revert();
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Les périodes de repos ne peuvent pas être redimensionnées ici.',
            life: 3000,
        });
        return;
    }
    updateEventInStore(resizeInfo.event);
}

function updateEndDateIfNeeded() {
    if (
        !currentEvent.value.end ||
        currentEvent.value.start > currentEvent.value.end
    ) {
        currentEvent.value.end = new Date(
            currentEvent.value.start.getTime() + 60 * 60 * 1000
        );
    }
}

function formatColor(color) {
    if (color && !color.startsWith('#')) {
        currentEvent.value.color = `#${color}`;
    } else if (!color) {
        currentEvent.value.color = '#3b82f6';
    }
}

async function updateEventInStore(event) {
    try {
        const response = await axios.put(`/api/calendrier/${event.id}`, {
            titre: event.title,
            date_debut: event.start.toISOString(),
            date_fin: event.end ? event.end.toISOString() : null,
            description: event.extendedProps?.description,
            observation: event.extendedProps?.observation,
            type: event.extendedProps?.type,
            color: event.backgroundColor.startsWith('#')
                ? event.backgroundColor
                : `#${event.backgroundColor}`,
        });

        toast.add({
            severity: 'success',
            summary: 'Mis à jour',
            detail: 'Événement déplacé',
            life: 3000,
        });
    } catch (error) {
        console.error("Erreur lors de la mise à jour de l'événement:", error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: error.response?.data?.message || error.message,
            life: 3000,
        });
    }
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

    if (currentEvent.value.type === 'repos_formation') {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Les périodes de repos doivent être créées via la gestion des années de formation.',
            life: 3000,
        });
        return;
    }

    const formattedColor = currentEvent.value.color.startsWith('#')
        ? currentEvent.value.color
        : `#${currentEvent.value.color}`;

    const eventData = {
        titre: currentEvent.value.title,
        date_debut: currentEvent.value.start.toISOString(),
        date_fin: currentEvent.value.end?.toISOString() || null,
        description: currentEvent.value.description,
        observation: currentEvent.value.observation,
        type: currentEvent.value.type,
        color: formattedColor,
    };

    try {
        let response;
        if (currentEvent.value.id) {
            response = await axios.put(
                `/api/calendrier/${currentEvent.value.id}`,
                eventData
            );
        } else {
            response = await axios.post('/api/calendrier', eventData);
            currentEvent.value.id = response.data.id;
        }

        await fetchEvents();
        displayDialog.value = false;
        toast.add({
            severity: 'success',
            summary: currentEvent.value.id ? 'Mis à jour' : 'Ajouté',
            detail: currentEvent.value.id
                ? 'Événement mis à jour'
                : 'Nouvel événement ajouté',
            life: 3000,
        });
    } catch (error) {
        console.error("Erreur lors de l'enregistrement de l'événement:", error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: error.response?.data?.message || error.message,
            life: 3000,
        });
    }
}

async function deleteEvent() {
    if (!currentEvent.value.id || currentEvent.value.id.startsWith('repos_')) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Cet événement ne peut pas être supprimé ici.',
            life: 3000,
        });
        return;
    }

    try {
        await axios.delete(`/api/calendrier/${currentEvent.value.id}`);
        await fetchEvents();
        displayDialog.value = false;
        toast.add({
            severity: 'warn',
            summary: 'Supprimé',
            detail: 'Événement supprimé',
            life: 3000,
        });
    } catch (error) {
        console.error("Erreur lors de la suppression de l'événement:", error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: error.response?.data?.message || error.message,
            life: 3000,
        });
    }
}

async function fetchEvents() {
    try {
        const [calendrierResponse, reposResponse] = await Promise.all([
            axios.get('/api/calendrier'),
            axios.get('/api/repos-formations'),
        ]);

        const calendrierEvents = calendrierResponse.data.map((event) => ({
            ...event,
            backgroundColor: event.backgroundColor || event.color || '#3b82f6',
            borderColor: event.backgroundColor || event.color || '#3b82f6',
            textColor: getContrastColor(
                event.backgroundColor || event.color || '#3b82f6'
            ),
        }));

        const reposEvents = reposResponse.data.map((event) => ({
            id: `repos_${event.id}`,
            title: event.nom_fr || 'Période sans nom',
            start: event.date_debut,
            end: event.date_fin,
            description: event.description_fr || '',
            backgroundColor: event.couleur || 'rgba(240, 107, 172, 0.7)',
            borderColor: event.couleur
                ? event.couleur.replace('0.7', '1')
                : '#f06bac',
            textColor: getContrastColor(event.couleur || '#f06bac'),
            allDay: true,
            extendedProps: {
                type: 'repos_formation',
                description: event.description_fr || '',
                observation: null,
            },
        }));

        events.value = [...calendrierEvents, ...reposEvents];
        calendarOptions.value.events = [...events.value];

        if (fullCalendar.value) {
            const calendarApi = fullCalendar.value.getApi();
            calendarApi.removeAllEvents();
            calendarApi.addEventSource(events.value);
            calendarApi.refetchEvents();
            calendarApi.render();
        }
    } catch (error) {
        console.error('Erreur lors de la récupération des événements:', error);
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: error.response?.data?.message || error.message,
            life: 3000,
        });
    }
}

let pollingInterval = null;

onMounted(() => {
    fetchEvents();
    setTimeout(() => {
        if (fullCalendar.value) {
            const calendarApi = fullCalendar.value.getApi();
            calendarApi.updateSize();
            calendarApi.render();
        }
    }, 100);

    pollingInterval = setInterval(() => {
        fetchEvents();
    }, 30000);
});

onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});
</script>

<style scoped>
.surface-ground {
    background-color: var(--surface-ground);
}

/* FullCalendar Custom Styles */
:deep(.fc) {
    font-family: var(--font-family);
    --fc-border-color: var(--surface-border);
    --fc-page-bg-color: var(--surface-card);
    --fc-today-bg-color: rgba(var(--primary-color-rgb), 0.1);
    --fc-neutral-bg-color: var(--surface-ground);
    --fc-list-event-hover-bg-color: var(--surface-hover);
    --fc-event-border-color: var(--surface-border);
    --fc-highlight-color: rgba(var(--primary-color-rgb), 0.1);
}

:deep(.fc-scrollgrid) {
    border: 1px solid var(--surface-border) !important;
    border-radius: 12px;
    overflow: hidden;
}

:deep(.fc-scrollgrid-section) {
    border-left: 1px solid var(--surface-border);
    border-right: 1px solid var(--surface-border);
}

:deep(.fc-col-header) {
    width: 100% !important;
    background-color: var(--surface-50);
}

:deep(.fc-col-header-cell),
:deep(.fc-daygrid-day) {
    border-right: 1px solid var(--surface-border) !important;
    border-left: 1px solid var(--surface-border) !important;
}

:deep(.fc-col-header-cell:first-child),
:deep(.fc-daygrid-day:first-child) {
    border-left: none !important;
}

:deep(.fc-col-header-cell:last-child),
:deep(.fc-daygrid-day:last-child) {
    border-right: none !important;
}

:deep(.fc-col-header-cell) {
    background-color: var(--surface-50);
    border-bottom: 1px solid var(--surface-border) !important;
    border-top: none !important;
    padding: 8px 4px;
}

:deep(.fc-col-header-cell-cushion) {
    color: var(--text-color);
    font-weight: 500;
    text-transform: capitalize;
}

:deep(.fc-daygrid-day) {
    border: 1px solid var(--surface-border) !important;
    background-color: var(--surface-card);
    transition: background-color 0.2s;
}

:deep(.fc-daygrid-day:hover) {
    background-color: var(--surface-hover);
}

:deep(.fc-daygrid-day-frame) {
    padding: 4px;
}

:deep(.fc-day-today) {
    background-color: var(--fc-today-bg-color) !important;
}

:deep(.fc-day-today .fc-daygrid-day-number) {
    color: var(--primary-color);
    font-weight: bold;
}

:deep(.fc-day-today .fc-daygrid-day-frame) {
    border: 2px solid var(--primary-color) !important;
    border-radius: 8px;
}

:deep(.fc-event) {
    border: none !important;
    margin: 2px;
    cursor: pointer;
    transition:
        transform 0.2s,
        box-shadow 0.2s;
}

:deep(.fc-event:hover) {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

:deep(.fc-event-main) {
    padding: 6px;
    border-radius: 6px;
}

:deep(.fc-event-title) {
    font-weight: 500;
    line-height: 1.2;
}

:deep(.fc-event-time) {
    font-weight: 500;
}

:deep(.fc-button) {
    background: var(--surface-a) !important;
    color: var(--text-color) !important;
    border: 1px solid var(--surface-border) !important;
    padding: 0.5rem 1rem !important;
    border-radius: 6px !important;
    transition: all 0.2s;
}

:deep(.fc-button:hover) {
    background: var(--surface-100) !important;
}

:deep(.fc-button-active) {
    background: var(--primary-color) !important;
    color: white !important;
    border-color: var(--primary-color) !important;
}

:deep(.fc-button-active:hover) {
    background: var(--primary-600) !important;
}

:deep(.fc-todayCustom-button) {
    text-transform: capitalize !important;
    font-weight: 500 !important;
}

:deep(.fc-toolbar-title) {
    font-size: 1.25rem !important;
    font-weight: 600 !important;
    color: var(--text-color) !important;
    margin: 0 1rem;
}

:deep(.fc-popover) {
    background-color: var(--surface-card);
    border: 1px solid var(--surface-border);
    border-radius: 6px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

@media (max-width: 960px) {
    :deep(.fc-toolbar) {
        flex-direction: column;
        gap: 0.5rem;
    }

    :deep(.fc-toolbar-chunk) {
        display: flex;
        justify-content: center;
        width: 100%;
    }

    :deep(.fc-header-toolbar .fc-toolbar-chunk:last-child) {
        order: -1;
    }
}

@media (max-width: 640px) {
    :deep(.fc .fc-toolbar-title) {
        font-size: 1rem !important;
    }

    :deep(.fc-button) {
        padding: 0.4rem 0.6rem !important;
        font-size: 0.8rem !important;
    }
}
:deep(.fc-toolbar-chunk) {
    display: flex;
    align-items: center;
    gap: 1rem; /* Ajoute un espacement entre les éléments dans chaque chunk */
}

:deep(.fc-button-group) {
    display: flex;
    gap: 0.5rem; /* Espacement spécifique entre les boutons Mois, Semaine, Jour */
}

:deep(.fc-prev-button),
:deep(.fc-next-button) {
    margin-right: 0.5rem; /* Espacement à droite des flèches précédent/suivant */
}
</style>
