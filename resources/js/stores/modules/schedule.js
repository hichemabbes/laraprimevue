const state = {
    specialites: [
        { id: 1, nom: 'Informatique' },
        { id: 2, nom: 'Mécanique' },
    ],
    groupes: [
        { id: 1, nom: 'Groupe A', specialiteId: 1 },
        { id: 2, nom: 'Groupe B', specialiteId: 1 },
    ],
    phases: [
        {
            id: 1,
            nom: 'Phase 1',
            groupeId: 1,
            date_debut: '2025-01-01',
            date_fin: '2025-06-30',
        },
        {
            id: 2,
            nom: 'Phase 2',
            groupeId: 1,
            date_debut: '2025-07-01',
            date_fin: '2025-12-31',
        },
    ],
    events: [],
    formateurs: [
        { id: 1, prenom: 'Jean', nom: 'Dupont', regime_horaire_id: 1 },
        { id: 2, prenom: 'Marie', nom: 'Curie', regime_horaire_id: 1 },
    ],
    espaces: [
        { id: 1, nom: 'Salle 101', type: 'Salle', capacite: 30 },
        { id: 2, nom: 'Labo 1', type: 'Laboratoire', capacite: 20 },
    ],
    modules: [
        { id: 1, nom: 'Programmation', duree_totale: 40, duree_seance: 2 },
        { id: 2, nom: 'Réseaux', duree_totale: 30, duree_seance: 1.5 },
    ],
    regimesHoraires: [{ id: 1, heures_semaine: 35 }],
    calendarConfig: {
        visibleDays: [
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
        ],
        timeSlots: [
            { start: new Date(0, 0, 0, 8, 0), end: new Date(0, 0, 0, 9, 0) },
            { start: new Date(0, 0, 0, 9, 0), end: new Date(0, 0, 0, 10, 0) },
            { start: new Date(0, 0, 0, 10, 0), end: new Date(0, 0, 0, 11, 0) },
            { start: new Date(0, 0, 0, 11, 0), end: new Date(0, 0, 0, 12, 0) },
            { start: new Date(0, 0, 0, 12, 0), end: new Date(0, 0, 0, 13, 0) },
            { start: new Date(0, 0, 0, 13, 0), end: new Date(0, 0, 0, 14, 0) },
            { start: new Date(0, 0, 0, 14, 0), end: new Date(0, 0, 0, 15, 0) },
            { start: new Date(0, 0, 0, 15, 0), end: new Date(0, 0, 0, 16, 0) },
            { start: new Date(0, 0, 0, 16, 0), end: new Date(0, 0, 0, 17, 0) },
            { start: new Date(0, 0, 0, 17, 0), end: new Date(0, 0, 0, 18, 0) },
        ],
    },
};

const getters = {
    filteredGroupes: (state) => (specialiteId) => {
        return state.groupes.filter(
            (groupe) => !specialiteId || groupe.specialiteId === specialiteId
        );
    },
    filteredPhases: (state) => (groupeId) => {
        return state.phases.filter(
            (phase) => !groupeId || phase.groupeId === groupeId
        );
    },
};

const mutations = {
    setEvents(state, events) {
        state.events = events;
    },
    updateEvent(state, event) {
        const index = state.events.findIndex((e) => e.id === event.id);
        if (index !== -1) {
            state.events[index] = event;
        } else {
            state.events.push(event);
        }
    },
    deleteEvent(state, eventId) {
        state.events = state.events.filter((e) => e.id !== eventId);
    },
    updateCalendarConfig(state, { visibleDays, timeSlots }) {
        state.calendarConfig.visibleDays = visibleDays;
        state.calendarConfig.timeSlots = timeSlots;
    },
    updateFilters(state, filters) {
        state.filters = filters;
    },
    updateSignatures(state, { directorId, technicalCoordinatorId }) {
        state.directorId = directorId;
        state.technicalCoordinatorId = technicalCoordinatorId;
    },
};

const actions = {
    loadInitialData({ commit }) {
        // Simuler le chargement des données initiales
        console.log('Loading initial data');
        return Promise.resolve();
    },
    fetchEvents({ commit, state }, filters) {
        console.log('Fetching events with filters:', filters);
        // Simuler une requête API ou retourner les événements filtrés
        const filteredEvents = state.events.filter((event) => {
            return (
                (!filters.groupeId || event.groupeId === filters.groupeId) &&
                (!filters.phaseId || event.phaseId === filters.phaseId)
            );
        });
        commit('setEvents', filteredEvents);
        return Promise.resolve(filteredEvents);
    },
    updateEvent({ commit }, event) {
        console.log('Updating event:', event);
        commit('updateEvent', event);
        return Promise.resolve();
    },
    deleteEvent({ commit }, eventId) {
        console.log('Deleting event:', eventId);
        commit('deleteEvent', eventId);
        return Promise.resolve();
    },
    updateCalendarConfig({ commit }, config) {
        console.log('Updating calendar config:', config);
        commit('updateCalendarConfig', config);
        return Promise.resolve();
    },
    updateFilters({ commit }, filters) {
        console.log('Updating filters:', filters);
        commit('updateFilters', filters);
        return Promise.resolve();
    },
    updateSignatures({ commit }, signatures) {
        console.log('Updating signatures:', signatures);
        commit('updateSignatures', signatures);
        return Promise.resolve();
    },
    generateSchedule({ commit }, options) {
        console.log('Generating schedule with options:', options);
        // Simuler la génération d'un emploi du temps
        return Promise.resolve();
    },
};

export default {
    namespaced: true,
    state,
    getters,
    mutations,
    actions,
};
