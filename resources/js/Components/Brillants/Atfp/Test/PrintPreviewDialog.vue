<template>
    <Dialog
        :visible="modelValue"
        @update:visible="$emit('update:modelValue', $event)"
        header="Aperçu d'Impression"
        :modal="true"
        :style="{ width: '80vw', maxWidth: '1000px' }"
        :breakpoints="{ '960px': '90vw', '640px': '95vw' }"
    >
        <div id="print-content" class="p-4 bg-white">
            <div class="flex justify-content-between align-items-center mb-4">
                <img src="/logo.png" alt="Logo" class="print-logo" />
                <div class="text-right">
                    <h3 class="m-0">
                        Agence Tunisienne de la Formation Professionnelle
                    </h3>
                    <p class="arabic-font m-0">
                        الوكالة التونسية للتكوين المهني
                    </p>
                </div>
            </div>

            <h2 class="text-center mb-2">Profil Utilisateur</h2>
            <p class="text-center text-600 mb-4">
                {{ formatDate(new Date()) }}
            </p>

            <div class="grid">
                <!-- Left Column -->
                <div class="col-12 md:col-4">
                    <h3 class="border-bottom-1 surface-border pb-2">
                        Informations de Base
                    </h3>

                    <div class="flex flex-column align-items-center gap-2 mb-4">
                        <Avatar
                            :image="user.photo || '/default-avatar.png'"
                            shape="circle"
                            size="xlarge"
                            class="mb-3"
                        />
                        <h4 class="m-0">
                            {{ user.nom_fr || 'Non défini' }}
                            {{ user.prenom_fr || '' }}
                        </h4>
                        <div class="flex gap-2">
                            <Tag
                                :value="user.roles?.[0]?.name || 'Aucun rôle'"
                                severity="info"
                            />
                            <Tag
                                :value="user.statut || 'Inconnu'"
                                :severity="
                                    user.statut === 'Actif'
                                        ? 'success'
                                        : 'danger'
                                "
                            />
                        </div>
                    </div>

                    <table class="print-table mb-4">
                        <tr>
                            <th>Matricule</th>
                            <td>{{ user.matricule || 'Non défini' }}</td>
                        </tr>
                        <tr>
                            <th>CIN</th>
                            <td>{{ user.cin || 'Non défini' }}</td>
                        </tr>
                        <tr>
                            <th>Date de Naissance</th>
                            <td>{{ formatDate(user.date_naissance) }}</td>
                        </tr>
                        <tr>
                            <th>Genre</th>
                            <td>{{ user.genre_fr || 'Non défini' }}</td>
                        </tr>
                        <tr>
                            <th>Nationalité</th>
                            <td>{{ user.nationalite_fr || 'Tunisienne' }}</td>
                        </tr>
                        <tr>
                            <th>Centre</th>
                            <td>
                                {{
                                    user.centres?.[0]?.nom_fr || 'Aucun centre'
                                }}
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Right Column -->
                <div class="col-12 md:col-8">
                    <h3 class="border-bottom-1 surface-border pb-2">Détails</h3>

                    <table class="print-table mb-4">
                        <tr>
                            <th width="30%">Nom (FR)</th>
                            <td>{{ user.nom_fr || 'Non défini' }}</td>
                        </tr>
                        <tr>
                            <th>Prénom (FR)</th>
                            <td>{{ user.prenom_fr || 'Non défini' }}</td>
                        </tr>
                        <tr>
                            <th>Nom (AR)</th>
                            <td class="arabic-font">
                                {{ user.nom_ar || 'غير محدد' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Prénom (AR)</th>
                            <td class="arabic-font">
                                {{ user.prenom_ar || 'غير محدد' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ user.email || 'Non défini' }}</td>
                        </tr>
                        <tr>
                            <th>Statut</th>
                            <td>{{ user.statut || 'Inconnu' }}</td>
                        </tr>
                    </table>

                    <h3 class="border-bottom-1 surface-border pb-2">
                        Activité
                    </h3>

                    <table class="print-table">
                        <tr>
                            <th width="30%">Dernière Connexion</th>
                            <td>{{ formatDateTime(user.last_login_at) }}</td>
                        </tr>
                        <tr>
                            <th>Adresse IP</th>
                            <td>{{ user.last_login_ip || 'Inconnue' }}</td>
                        </tr>
                        <tr>
                            <th>Date de Création</th>
                            <td>{{ formatDateTime(user.created_at) }}</td>
                        </tr>
                        <tr>
                            <th>Dernière Mise à Jour</th>
                            <td>{{ formatDateTime(user.updated_at) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <template #footer>
            <Button
                label="Imprimer"
                icon="pi pi-print"
                @click="$emit('print')"
            />
            <Button
                label="Fermer"
                icon="pi pi-times"
                text
                @click="$emit('update:modelValue', false)"
            />
        </template>
    </Dialog>
</template>

<script>
export default {
    props: {
        modelValue: Boolean,
        user: Object,
    },
    emits: ['update:modelValue', 'print'],
    setup() {
        const formatDate = (date) => {
            if (!date) return 'Non défini';
            return new Date(date).toLocaleDateString('fr-FR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
        };

        const formatDateTime = (date) => {
            if (!date) return 'Jamais';
            return new Date(date).toLocaleString('fr-FR');
        };

        return {
            formatDate,
            formatDateTime,
        };
    },
};
</script>

<style scoped>
.print-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1.5rem;
}

.print-table th,
.print-table td {
    border: 1px solid #dee2e6;
    padding: 0.75rem;
    text-align: left;
}

.print-table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.arabic-font {
    font-family: 'Amiri', serif;
    direction: rtl;
    text-align: right;
}

.print-logo {
    max-height: 60px;
}

.border-bottom-1 {
    border-bottom-width: 1px;
    border-bottom-style: solid;
}

@media print {
    @page {
        size: A4 portrait;
        margin: 1cm;
    }

    body {
        margin: 0;
        padding: 0;
        background: white;
        font-size: 12pt;
    }

    .print-table {
        page-break-inside: avoid;
    }

    #print-content {
        padding: 0;
        margin: 0;
    }
}
</style>
