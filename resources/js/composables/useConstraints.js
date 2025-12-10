import { useStore } from 'vuex';

export function useConstraints() {
    const store = useStore();

    function checkEventConflicts(event) {
        console.log('Checking conflicts for event:', event);
        const conflicts = {
            formateur: [],
            espace: [],
            groupe: [],
        };
        store.state.schedule.events.forEach((e) => {
            if (
                e.id !== event.id &&
                e.day === event.day &&
                e.groupeId === event.groupeId &&
                e.phaseId === event.phaseId
            ) {
                // Check formateur conflict
                if (e.formateurId === event.formateurId) {
                    if (
                        (event.start >= e.start && event.start < e.end) ||
                        (event.end > e.start && event.end <= e.end) ||
                        (event.start <= e.start && event.end >= e.end)
                    ) {
                        conflicts.formateur.push(
                            `Conflit avec l'événement ID ${e.id}`
                        );
                    }
                }
                // Check espace conflict
                if (e.espaceId === event.espaceId) {
                    if (
                        (event.start >= e.start && event.start < e.end) ||
                        (event.end > e.start && event.end <= e.end) ||
                        (event.start <= e.start && event.end >= e.end)
                    ) {
                        conflicts.espace.push(
                            `Conflit avec l'événement ID ${e.id}`
                        );
                    }
                }
                // Check groupe conflict
                if (e.groupeId === event.groupeId) {
                    if (
                        (event.start >= e.start && event.start < e.end) ||
                        (event.end > e.start && event.end <= e.end) ||
                        (event.start <= e.start && event.end >= e.end)
                    ) {
                        conflicts.groupe.push(
                            `Conflit avec l'événement ID ${e.id}`
                        );
                    }
                }
            }
        });
        console.log('Conflicts found:', conflicts);
        return conflicts;
    }

    function formatConflict(conflict, type) {
        return `Conflit de type ${type}: ${conflict}`;
    }

    function calculateWeeklyHours(formateurId, date) {
        return store.state.schedule.events
            .filter((e) => e.formateurId === formateurId)
            .reduce((total, e) => {
                const duration = (e.end - e.start) / (1000 * 60 * 60);
                return total + duration;
            }, 0);
    }

    function getEventDuration(event) {
        return (event.end - event.start) / (1000 * 60 * 60);
    }

    function isFormateurDisponible(formateurId) {
        return store.state.schedule.events.every(
            (e) => e.formateurId !== formateurId
        );
    }

    function isEspaceDisponible(espaceId) {
        return store.state.schedule.events.every(
            (e) => e.espaceId !== espaceId
        );
    }

    return {
        checkEventConflicts,
        formatConflict,
        calculateWeeklyHours,
        getEventDuration,
        isFormateurDisponible,
        isEspaceDisponible,
    };
}
