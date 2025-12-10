<template>
    <div class="stagiaires-list">
        <div class="search-bar">
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Rechercher par nom, prénom ou CIN..."
                @input="handleSearch"
            />
            <select v-model="selectedStatus" @change="handleFilter">
                <option value="">Tous les statuts</option>
                <option
                    v-for="status in statusOptions"
                    :key="status.id"
                    :value="status.id"
                >
                    {{ status.nom }}
                </option>
            </select>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th @click="sort('matricule')">
                            Matricule <i :class="sortIcon('matricule')"></i>
                        </th>
                        <th @click="sort('nom_fr')">
                            Nom <i :class="sortIcon('nom_fr')"></i>
                        </th>
                        <th @click="sort('prenom_fr')">
                            Prénom <i :class="sortIcon('prenom_fr')"></i>
                        </th>
                        <th @click="sort('cin')">
                            CIN <i :class="sortIcon('cin')"></i>
                        </th>
                        <th @click="sort('date_inscription')">
                            Date Inscription
                            <i :class="sortIcon('date_inscription')"></i>
                        </th>
                        <th @click="sort('statut')">
                            Statut <i :class="sortIcon('statut')"></i>
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="stagiaire in paginatedData"
                        :key="stagiaire.id"
                        @click="selectStagiaire(stagiaire)"
                    >
                        <td>{{ stagiaire.matricule }}</td>
                        <td>{{ stagiaire.nom_fr }}</td>
                        <td>{{ stagiaire.prenom_fr }}</td>
                        <td>{{ stagiaire.cin }}</td>
                        <td>{{ formatDate(stagiaire.date_inscription) }}</td>
                        <td>
                            <span
                                class="status-badge"
                                :class="getStatusClass(stagiaire.statut_id)"
                            >
                                {{ getStatusName(stagiaire.statut_id) }}
                            </span>
                        </td>
                        <td class="actions">
                            <button
                                @click.stop="editStagiaire(stagiaire)"
                                class="btn-edit"
                            >
                                <i class="fas fa-edit"></i>
                            </button>
                            <button
                                @click.stop="deleteStagiaire(stagiaire)"
                                class="btn-delete"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="loading" class="loading">
                <i class="fas fa-spinner fa-spin"></i> Chargement...
            </div>

            <div
                v-if="!loading && filteredData.length === 0"
                class="no-results"
            >
                Aucun stagiaire trouvé
            </div>
        </div>

        <div class="pagination" v-if="totalPages > 1">
            <button @click="prevPage" :disabled="currentPage === 1">
                <i class="fas fa-chevron-left"></i>
            </button>
            <span v-for="page in displayedPages" :key="page">
                <button
                    @click="goToPage(page)"
                    :class="{ active: currentPage === page }"
                >
                    {{ page }}
                </button>
            </span>
            <button @click="nextPage" :disabled="currentPage === totalPages">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import { format } from 'date-fns';

export default {
    props: {
        stagiaires: {
            type: Array,
            required: true,
        },
        loading: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['select', 'edit', 'delete', 'filter', 'search'],
    setup(props, { emit }) {
        const store = useStore();
        const searchQuery = ref('');
        const selectedStatus = ref('');
        const statusOptions = ref([]);
        const sortField = ref('date_inscription');
        const sortDirection = ref('desc');
        const currentPage = ref(1);
        const itemsPerPage = 10;

        onMounted(() => {
            fetchStatusOptions();
        });

        const fetchStatusOptions = async () => {
            try {
                const response = await axios.get('/api/options-listes', {
                    params: {
                        type_categorie_id: 'StatutStagiaire',
                    },
                });
                statusOptions.value = response.data;
            } catch (error) {
                console.error('Error fetching status options:', error);
            }
        };

        const filteredData = computed(() => {
            return props.stagiaires
                .filter((stagiaire) => {
                    return (
                        (!searchQuery.value ||
                            stagiaire.nom_fr
                                .toLowerCase()
                                .includes(searchQuery.value.toLowerCase()) ||
                            stagiaire.prenom_fr
                                .toLowerCase()
                                .includes(searchQuery.value.toLowerCase()) ||
                            stagiaire.cin.includes(searchQuery.value)) &&
                        (!selectedStatus.value ||
                            stagiaire.statut_id === selectedStatus.value)
                    );
                })
                .sort((a, b) => {
                    const fieldA = a[sortField.value];
                    const fieldB = b[sortField.value];

                    if (fieldA < fieldB)
                        return sortDirection.value === 'asc' ? -1 : 1;
                    if (fieldA > fieldB)
                        return sortDirection.value === 'asc' ? 1 : -1;
                    return 0;
                });
        });

        const totalPages = computed(() => {
            return Math.ceil(filteredData.value.length / itemsPerPage);
        });

        const paginatedData = computed(() => {
            const start = (currentPage.value - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            return filteredData.value.slice(start, end);
        });

        const displayedPages = computed(() => {
            const pages = [];
            const maxVisible = 5;
            let start = Math.max(
                1,
                currentPage.value - Math.floor(maxVisible / 2)
            );
            let end = Math.min(totalPages.value, start + maxVisible - 1);

            if (end - start + 1 < maxVisible) {
                start = Math.max(1, end - maxVisible + 1);
            }

            for (let i = start; i <= end; i++) {
                pages.push(i);
            }

            return pages;
        });

        const formatDate = (dateString) => {
            return dateString ? format(new Date(dateString), 'dd/MM/yyyy') : '';
        };

        const getStatusName = (statusId) => {
            const status = statusOptions.value.find((s) => s.id === statusId);
            return status ? status.nom : 'Inconnu';
        };

        const getStatusClass = (statusId) => {
            const status = statusOptions.value.find((s) => s.id === statusId);
            if (!status) return '';

            switch (status.code) {
                case 'ACTIF':
                    return 'active';
                case 'INACTIF':
                    return 'inactive';
                case 'SUSPENDU':
                    return 'suspended';
                case 'ABANDON':
                    return 'abandoned';
                case 'DIPLOME':
                    return 'graduated';
                default:
                    return '';
            }
        };

        const sort = (field) => {
            if (sortField.value === field) {
                sortDirection.value =
                    sortDirection.value === 'asc' ? 'desc' : 'asc';
            } else {
                sortField.value = field;
                sortDirection.value = 'asc';
            }
        };

        const sortIcon = (field) => {
            if (sortField.value !== field) return 'fas fa-sort';
            return sortDirection.value === 'asc'
                ? 'fas fa-sort-up'
                : 'fas fa-sort-down';
        };

        const selectStagiaire = (stagiaire) => {
            emit('select', stagiaire);
        };

        const editStagiaire = (stagiaire) => {
            emit('edit', stagiaire);
        };

        const deleteStagiaire = (stagiaire) => {
            emit('delete', stagiaire);
        };

        const handleFilter = () => {
            emit('filter', { statut: selectedStatus.value });
        };

        const handleSearch = () => {
            emit('search', searchQuery.value);
        };

        const prevPage = () => {
            if (currentPage.value > 1) {
                currentPage.value--;
            }
        };

        const nextPage = () => {
            if (currentPage.value < totalPages.value) {
                currentPage.value++;
            }
        };

        const goToPage = (page) => {
            currentPage.value = page;
        };

        return {
            searchQuery,
            selectedStatus,
            statusOptions,
            filteredData,
            paginatedData,
            totalPages,
            displayedPages,
            currentPage,
            formatDate,
            getStatusName,
            getStatusClass,
            sort,
            sortIcon,
            selectStagiaire,
            editStagiaire,
            deleteStagiaire,
            handleFilter,
            handleSearch,
            prevPage,
            nextPage,
            goToPage,
        };
    },
};
</script>

<style scoped>
.stagiaires-list {
    padding: 20px;
}

.search-bar {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.search-bar input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.search-bar select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    min-width: 200px;
}

.table-container {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.table th {
    background-color: #f5f5f5;
    cursor: pointer;
    position: relative;
}

.table th:hover {
    background-color: #e9e9e9;
}

.table tbody tr:hover {
    background-color: #f9f9f9;
    cursor: pointer;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.active {
    background-color: #e6f7e6;
    color: #2e7d32;
}

.status-badge.inactive {
    background-color: #fff3e0;
    color: #e65100;
}

.status-badge.suspended {
    background-color: #ffebee;
    color: #c62828;
}

.status-badge.abandoned {
    background-color: #f5f5f5;
    color: #616161;
}

.status-badge.graduated {
    background-color: #e3f2fd;
    color: #1565c0;
}

.actions {
    display: flex;
    gap: 8px;
}

.actions button {
    padding: 6px 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    background: none;
}

.btn-edit {
    color: #1976d2;
}

.btn-edit:hover {
    background-color: #e3f2fd;
}

.btn-delete {
    color: #d32f2f;
}

.btn-delete:hover {
    background-color: #ffebee;
}

.loading,
.no-results {
    padding: 20px;
    text-align: center;
    color: #666;
}

.pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
    gap: 5px;
}

.pagination button {
    padding: 8px 12px;
    border: 1px solid #ddd;
    background-color: white;
    cursor: pointer;
    border-radius: 4px;
}

.pagination button.active {
    background-color: #1976d2;
    color: white;
    border-color: #1976d2;
}

.pagination button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
