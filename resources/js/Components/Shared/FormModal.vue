<script setup>
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';

defineProps({
    header: {
        type: String,
        default: 'Formulaire',
    },
    visible: {
        type: Boolean,
        required: true,
    },
    width: {
        type: String,
        default: '25rem',
    },
});

const emit = defineEmits(['update:visible', 'submit']);
</script>

<template>
    <Dialog
        :header="header"
        :visible="visible"
        :style="{ width }"
        :modal="true"
        @update:visible="emit('update:visible', $event)"
    >
        <slot></slot>
        <template #footer>
            <Button
                label="Annuler"
                icon="pi pi-times"
                @click="emit('update:visible', false)"
                class="p-button-text"
            />
            <Button
                label="Enregistrer"
                icon="pi pi-check"
                @click="emit('submit')"
            />
        </template>
    </Dialog>
</template>

<style scoped>
/* Ajouter des styles si nécessaire */
</style>
