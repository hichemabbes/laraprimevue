<template>
    <Card>
        <template #title>Activité</template>
        <template #content>
            <div class="grid">
                <div class="col-12 lg:col-6">
                    <div class="activity-card">
                        <div class="activity-icon">
                            <i class="pi pi-sign-in"></i>
                        </div>
                        <div class="activity-content">
                            <h5>Dernière connexion</h5>
                            <p>{{ formatDateTime(user.last_login_at) }}</p>
                            <small class="text-color-secondary"
                                >Adresse IP:
                                {{ user.last_login_ip || 'Inconnue' }}</small
                            >
                        </div>
                    </div>
                </div>

                <div class="col-12 lg:col-6">
                    <div class="activity-card">
                        <div class="activity-icon">
                            <i class="pi pi-calendar-plus"></i>
                        </div>
                        <div class="activity-content">
                            <h5>Date de création</h5>
                            <p>{{ formatDateTime(user.created_at) }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 lg:col-6">
                    <div class="activity-card">
                        <div class="activity-icon">
                            <i class="pi pi-calendar-edit"></i>
                        </div>
                        <div class="activity-content">
                            <h5>Dernière mise à jour</h5>
                            <p>{{ formatDateTime(user.updated_at) }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 lg:col-6">
                    <div class="activity-card">
                        <div class="activity-icon">
                            <i class="pi pi-history"></i>
                        </div>
                        <div class="activity-content">
                            <h5>Activité récente</h5>
                            <p>Aucune activité récente</p>
                        </div>
                    </div>
                </div>
            </div>

            <Divider />

            <h5 class="mb-3">Historique des connexions</h5>
            <DataTable
                :value="loginHistory"
                :paginator="true"
                :rows="5"
                :rowsPerPageOptions="[5, 10, 20]"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                currentPageReportTemplate="Affichage de {first} à {last} sur {totalRecords} entrées"
                responsiveLayout="scroll"
            >
                <Column field="date" header="Date" sortable>
                    <template #body="{ data }">
                        {{ formatDateTime(data.date) }}
                    </template>
                </Column>
                <Column field="ip" header="Adresse IP" sortable></Column>
                <Column field="device" header="Appareil" sortable></Column>
                <Column
                    field="location"
                    header="Localisation"
                    sortable
                ></Column>
            </DataTable>
        </template>
    </Card>
</template>

<script>
import { ref } from 'vue';

export default {
    props: {
        user: Object,
    },
    setup() {
        const loginHistory = ref([
            {
                date: new Date(),
                ip: '192.168.1.1',
                device: 'Chrome (Windows)',
                location: 'Tunis, Tunisia',
            },
            {
                date: new Date(Date.now() - 86400000),
                ip: '192.168.1.2',
                device: 'Firefox (Mac)',
                location: 'Sfax, Tunisia',
            },
            {
                date: new Date(Date.now() - 172800000),
                ip: '192.168.1.3',
                device: 'Safari (iPhone)',
                location: 'Sousse, Tunisia',
            },
        ]);

        const formatDateTime = (date) => {
            if (!date) return 'Jamais';
            return new Date(date).toLocaleString('fr-FR');
        };

        return {
            loginHistory,
            formatDateTime,
        };
    },
};
</script>

<style scoped>
.activity-card {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-radius: 8px;
    background: var(--surface-50);
    transition: all 0.2s;
    height: 100%;

    &:hover {
        background: var(--surface-100);
        transform: translateY(-2px);
    }
}

.activity-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--primary-color);
    color: white;
    font-size: 1.25rem;
    margin-right: 1rem;
}

.activity-content {
    flex: 1;

    h5 {
        margin: 0 0 0.25rem 0;
        font-size: 1rem;
        color: var(--surface-700);
    }

    p {
        margin: 0;
        font-size: 0.875rem;
        color: var(--surface-600);
    }

    small {
        font-size: 0.75rem;
    }
}
</style>
