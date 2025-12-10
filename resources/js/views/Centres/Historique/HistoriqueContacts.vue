<script setup>
import { ref, onMounted } from 'vue';
import MainLayout from '@/layout/MainLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import axios from '@/axios.js';

const contacts = ref([]);

const fetchContacts = async () => {
    try {
        const response = await axios.get('/api/historique-contacts');
        contacts.value = response.data;
    } catch (err) {
        console.error(
            "Erreur lors du chargement de l'historique des contacts",
            err
        );
    }
};

onMounted(fetchContacts);
</script>

<template>
    <MainLayout>
        <div class="grid">
            <div class="col-12">
                <div class="card">
                    <h1>Historique des Contacts</h1>
                    <DataTable
                        :value="contacts"
                        responsiveLayout="scroll"
                        class="mt-3"
                    >
                        <Column field="id" header="ID" />
                        <Column field="type" header="Type" />
                        <Column field="value" header="Valeur" />
                        <Column
                            field="updated_at"
                            header="Date de mise à jour"
                        />
                        <Column field="user" header="Utilisateur" />
                    </DataTable>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
