<script setup>
import { ref, onMounted } from 'vue';
import MainLayout from '@/layout/MainLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import axios from '@/axios.js';

const addresses = ref([]);

const fetchAddresses = async () => {
    try {
        const response = await axios.get('/api/historique-adresses');
        addresses.value = response.data;
    } catch (err) {
        console.error(
            "Erreur lors du chargement de l'historique des adresses",
            err
        );
    }
};

onMounted(fetchAddresses);
</script>

<template>
    <MainLayout>
        <div class="grid">
            <div class="col-12">
                <div class="card">
                    <h1>Historique des Adresses</h1>
                    <DataTable
                        :value="addresses"
                        responsiveLayout="scroll"
                        class="mt-3"
                    >
                        <Column field="id" header="ID" />
                        <Column field="address" header="Adresse" />
                        <Column field="city" header="Ville" />
                        <Column field="postal_code" header="Code Postal" />
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
