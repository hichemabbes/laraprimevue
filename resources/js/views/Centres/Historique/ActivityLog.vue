<script setup>
import { ref, onMounted } from 'vue';
import MainLayout from '@/layout/MainLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import axios from '@/axios.js';

const logs = ref([]);

const fetchLogs = async () => {
    try {
        const response = await axios.get('/api/activity-log');
        logs.value = response.data;
    } catch (err) {
        console.error("Erreur lors du chargement du journal d'activités", err);
    }
};

onMounted(fetchLogs);
</script>

<template>
    <MainLayout>
        <div class="grid">
            <div class="col-12">
                <div class="card">
                    <h1>Journal d'Activités</h1>
                    <DataTable
                        :value="logs"
                        responsiveLayout="scroll"
                        class="mt-3"
                    >
                        <Column field="id" header="ID" />
                        <Column field="description" header="Description" />
                        <Column field="created_at" header="Date" />
                        <Column field="user" header="Utilisateur" />
                    </DataTable>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
