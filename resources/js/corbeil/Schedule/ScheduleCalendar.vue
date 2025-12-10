<template>
    <div class="schedule-calendar">
        <FullCalendar :options="calendarOptions" />
    </div>
</template>

<script>
import { computed } from 'vue';
import { useStore } from 'vuex';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import resourceTimeGridPlugin from '@fullcalendar/resource-timegrid';
import frLocale from '@fullcalendar/core/locales/fr';
import dayjs from 'dayjs';

export default {
    components: {
        FullCalendar,
    },
    props: {
        events: {
            type: Array,
            required: true,
        },
        resources: {
            type: Array,
            default: () => [],
        },
    },
    emits: [
        'event-click',
        'date-click',
        'event-drop',
        'event-resize',
        'selection',
    ],
    setup(props, { emit }) {
        const store = useStore();

        const calendarOptions = computed(() => ({
            plugins: [
                dayGridPlugin,
                timeGridPlugin,
                interactionPlugin,
                resourceTimeGridPlugin,
            ],
            initialView: 'resourceTimeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,resourceTimeGridWeek,resourceTimeGridDay',
            },
            locale: frLocale,
            nowIndicator: true,
            editable: true,
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true,
            weekends: false,
            businessHours: {
                daysOfWeek: [1, 2, 3, 4, 5],
                startTime: '08:00',
                endTime: '18:00',
            },
            slotMinTime: '08:00',
            slotMaxTime: '20:00',
            slotDuration: '00:30:00',
            allDaySlot: false,
            resources: props.resources,
            events: props.events.map((event) => ({
                id: event.id,
                title:
                    event.title ||
                    `${event.moduleId ? store.state.schedule.modules.find((m) => m.id === event.moduleId)?.nom : 'Séance'} - ${event.groupeId ? store.state.schedule.groupes.find((g) => g.id === event.groupeId)?.nom : ''}`,
                start: event.start,
                end: event.end,
                resourceId: `groupe-${event.groupeId}`,
                extendedProps: {
                    ...event,
                    formateur: store.state.schedule.formateurs.find(
                        (f) => f.id === event.formateurId
                    ),
                    module: store.state.schedule.modules.find(
                        (m) => m.id === event.moduleId
                    ),
                    espace: store.state.schedule.espaces.find(
                        (e) => e.id === event.espaceId
                    ),
                },
                backgroundColor: getEventColor(event),
                borderColor: getEventColor(event),
            })),
            eventClick: (info) => {
                emit('event-click', {
                    id: info.event.id,
                    ...info.event.extendedProps,
                    start: info.event.start,
                    end: info.event.end,
                });
            },
            dateClick: (info) => {
                emit('date-click', new Date(info.date));
            },
            eventDrop: (info) => {
                emit('event-drop', {
                    id: info.event.id,
                    ...info.event.extendedProps,
                    start: info.event.start,
                    end: info.event.end,
                });
            },
            eventResize: (info) => {
                emit('event-resize', {
                    id: info.event.id,
                    ...info.event.extendedProps,
                    start: info.event.start,
                    end: info.event.end,
                });
            },
            select: (info) => {
                emit('selection', {
                    start: info.start,
                    end: info.end,
                    resourceId: info.resource?.id,
                });
            },
            eventDidMount: (info) => {
                if (info.event.extendedProps.conflicts) {
                    info.el.style.boxShadow = '0 0 0 2px var(--red-500)';
                    info.el.title =
                        'Conflits détectés - Cliquez pour voir les détails';
                }
            },
        }));

        const getEventColor = (event) => {
            const colors = [
                '#6366F1',
                '#8B5CF6',
                '#EC4899',
                '#F43F5E',
                '#F97316',
                '#F59E0B',
                '#10B981',
                '#14B8A6',
                '#0EA5E9',
                '#3B82F6',
            ];

            if (event.moduleId) {
                return colors[event.moduleId % colors.length];
            }
            return colors[0];
        };

        return {
            calendarOptions,
        };
    },
};
</script>

<style>
.schedule-calendar {
    height: 700px;
}

.fc {
    font-family: inherit;
}

.fc-event {
    cursor: pointer;
    border-radius: 4px;
    padding: 2px 4px;
}

.fc-event-title {
    white-space: normal;
    font-size: 0.85rem;
    font-weight: 500;
}

.fc-daygrid-event-dot {
    display: none;
}

.fc-timegrid-slot {
    height: 2.5em;
}

.fc-resource-time-grid .fc-timeline-slots td {
    vertical-align: top;
}
</style>
