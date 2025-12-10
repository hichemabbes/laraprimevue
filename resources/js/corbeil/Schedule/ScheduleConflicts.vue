<template>
    <Card>
        <template #title>Vérification des contraintes</template>
        <template #content>
            <div v-if="hasConflicts" class="conflicts-list">
                <div v-for="(conflictType, type) in conflicts" :key="type">
                    <div v-if="conflictType.length > 0" class="mb-4">
                        <h4 class="capitalize">{{ type }}</h4>
                        <ul class="conflict-items">
                            <li
                                v-for="(conflict, idx) in conflictType"
                                :key="idx"
                            >
                                <i class="pi pi-exclamation-triangle mr-2"></i>
                                {{ formatConflict(conflict, type) }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div v-else class="no-conflicts">
                <i class="pi pi-check-circle text-green-500 mr-2"></i>
                <span>Aucun conflit détecté</span>
            </div>
        </template>
    </Card>
</template>

<script>
import { computed } from 'vue';

export default {
    props: {
        conflicts: {
            type: Object,
            default: () => ({}),
        },
    },
    setup(props) {
        const hasConflicts = computed(() => {
            return Object.values(props.conflicts).some(
                (conflicts) => conflicts.length > 0
            );
        });

        const formatConflict = (conflict, type) => {
            if (typeof conflict === 'string') return conflict;

            switch (type) {
                case 'formateur':
                    return `Formateur déjà occupé: ${conflict.title}`;
                case 'espace':
                    return `Espace déjà réservé: ${conflict.title}`;
                case 'groupe':
                    return `Groupe déjà en séance: ${conflict.title}`;
                case 'horaire':
                    return `Problème horaire: ${conflict}`;
                default:
                    return JSON.stringify(conflict);
            }
        };

        return {
            hasConflicts,
            formatConflict,
        };
    },
};
</script>

<style scoped>
.conflicts-list {
    max-height: 300px;
    overflow-y: auto;
}

.conflict-items {
    list-style-type: none;
    padding-left: 0;
    margin-top: 0.5rem;
}

.conflict-items li {
    padding: 0.5rem;
    border-left: 3px solid var(--red-500);
    margin-bottom: 0.25rem;
    background-color: var(--surface-100);
    border-radius: 0 4px 4px 0;
}

.no-conflicts {
    display: flex;
    align-items: center;
    color: var(--green-500);
    padding: 1rem;
}
</style>
