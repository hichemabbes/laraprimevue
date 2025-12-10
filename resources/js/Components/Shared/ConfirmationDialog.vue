<template>
    <Dialog
        header="Confirmation"
        :visible="visible"
        :style="{ width: '20rem' }"
        :modal="true"
        @update:visible="$emit('update:visible', $event)"
    >
        <p>{{ message }}</p>
        <template #footer>
            <Button
                label="Annuler"
                icon="pi pi-times"
                @click="$emit('update:visible', false)"
                class="p-button-text"
            />
            <Button
                label="Confirmer"
                icon="pi pi-check"
                @click="$emit('confirm')"
            />
        </template>
    </Dialog>
</template>

<script>
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';

export default {
    props: {
        visible: {
            type: Boolean,
            default: false,
        },
        message: {
            type: String,
            default: '',
        },
    },
    emits: ['update:visible', 'confirm'], // Ajout de 'confirm' pour une action explicite
    components: {
        Dialog,
        Button,
    },
};
</script>
