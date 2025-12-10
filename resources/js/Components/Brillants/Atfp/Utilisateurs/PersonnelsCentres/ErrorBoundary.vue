<template>
    <div>
        <!-- Render child components if no error -->
        <slot v-if="!hasError"></slot>
        <!-- Render fallback UI if an error occurs -->
        <div v-else class="error-boundary">
            <h2>Oops ! Une erreur s'est produite.</h2>
            <p>{{ errorMessage }}</p>
            <button @click="resetError">Réessayer</button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ErrorBoundary',
    data() {
        return {
            hasError: false,
            errorMessage: '',
        };
    },
    methods: {
        resetError() {
            this.hasError = false;
            this.errorMessage = '';
        },
    },
    errorCaptured(err, vm, info) {
        console.error('Error captured:', err, vm, info);
        this.hasError = true;
        this.errorMessage =
            err.message || "Une erreur inattendue s'est produite";
        return false;
    },
};
</script>

<style scoped>
.error-boundary {
    padding: 20px;
    border: 1px solid #ff0000;
    background-color: #ffe6e6;
    border-radius: 5px;
    text-align: center;
}

button {
    margin-top: 10px;
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

button:hover {
    background-color: #0056b3;
}
</style>
