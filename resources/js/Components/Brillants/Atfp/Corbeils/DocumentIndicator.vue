<template>
    <div
        class="document-indicator"
        :class="{
            'document-active': hasActiveDocument,
            'document-inactive': hasInactiveDocument,
            'document-missing': !hasDocument,
        }"
        v-tooltip="tooltipText"
    ></div>
</template>

<script>
export default {
    props: {
        specialite: {
            type: Object,
            required: true,
        },
        documentType: {
            type: String,
            required: true,
        },
    },
    computed: {
        document() {
            if (!this.specialite.documents) return null;
            return this.specialite.documents.find(
                (d) => d.type_doc_fr?.libelle === this.documentType
            );
        },
        hasDocument() {
            return !!this.document;
        },
        hasActiveDocument() {
            return this.document?.statut_fr === 'Actif';
        },
        hasInactiveDocument() {
            return this.document?.statut_fr === 'Inactif';
        },
        tooltipText() {
            if (!this.hasDocument)
                return `${this.documentType}: Non disponible`;
            return `${this.documentType}: ${this.document.statut_fr} (Version ${this.document.version})`;
        },
    },
};
</script>

<style scoped>
.document-indicator {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    margin: 0 auto;
}

.document-active {
    background-color: #4caf50; /* Vert */
}

.document-inactive {
    background-color: #ffc107; /* Jaune */
}

.document-missing {
    background-color: #f44336; /* Rouge */
}
</style>
