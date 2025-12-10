<template>
    <!-- Start cards -->
    <div class="grid">
        <div class="col-12 lg:col-6 xl:col-3">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-2"
                            >Orders</span
                        >
                        <div class="text-900 font-medium text-xl">152</div>
                    </div>
                    <div
                        class="flex align-items-center justify-content-center bg-blue-100 border-round"
                        style="width: 2.5rem; height: 2.5rem"
                    >
                        <i
                            class="pi pi-shopping-cart text-blue-500 text-xl"
                        ></i>
                    </div>
                </div>
                <span class="text-green-500 font-medium">24 new </span>
                <span class="text-500">since last visit</span>
            </div>
        </div>
        <div class="col-12 lg:col-6 xl:col-3">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-2"
                            >Revenue</span
                        >
                        <div class="text-900 font-medium text-xl">$2.100</div>
                    </div>
                    <div
                        class="flex align-items-center justify-content-center bg-orange-100 border-round"
                        style="width: 2.5rem; height: 2.5rem"
                    >
                        <i class="pi pi-map-marker text-orange-500 text-xl"></i>
                    </div>
                </div>
                <span class="text-green-500 font-medium">%52+ </span>
                <span class="text-500">since last week</span>
            </div>
        </div>
        <div class="col-12 lg:col-6 xl:col-3">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-2"
                            >Customers</span
                        >
                        <div class="text-900 font-medium text-xl">28441</div>
                    </div>
                    <div
                        class="flex align-items-center justify-content-center bg-cyan-100 border-round"
                        style="width: 2.5rem; height: 2.5rem"
                    >
                        <i class="pi pi-inbox text-cyan-500 text-xl"></i>
                    </div>
                </div>
                <span class="text-green-500 font-medium">520 </span>
                <span class="text-500">newly registered</span>
            </div>
        </div>
        <div class="col-12 lg:col-6 xl:col-3">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-2"
                            >Comments</span
                        >
                        <div class="text-900 font-medium text-xl">
                            152 Unread
                        </div>
                    </div>
                    <div
                        class="flex align-items-center justify-content-center bg-purple-100 border-round"
                        style="width: 2.5rem; height: 2.5rem"
                    >
                        <i class="pi pi-comment text-purple-500 text-xl"></i>
                    </div>
                </div>
                <span class="text-green-500 font-medium">85 </span>
                <span class="text-500">responded</span>
            </div>
        </div>
    </div>
    <!-- End cards -->

    <!-- Start Table -->
    <div class="card">
        <DataTable
            v-model:filters="filters"
            showGridlines
            stripedRows
            v-model:selection="selectedCustomers"
            size="small"
            :value="customers"
            paginator
            :rows="10"
            dataKey="id"
            filterDisplay="menu"
            :globalFilterFields="[
                'name',
                'country.name',
                'representative.name',
                'balance',
                'status',
            ]"
        >
            <template #header>
                <div
                    class="flex justify-content-between mb-2 align-items-center"
                >
                    <!-- Section gauche -->
                    <div class="flex align-items-center">
                        <Button
                            icon="pi pi-plus"
                            severity="success"
                            size="small"
                            class="mr-2"
                            @click="showPopup"
                        />
                        <span class="p-input-icon-left">
                            <i class="pi pi-search" />
                            <InputText
                                v-model="filters['global'].value"
                                size="small"
                                placeholder="Keyword Search"
                                style="width: 100%"
                            />
                        </span>
                    </div>

                    <!-- Section droite -->
                    <div class="flex align-items-center">
                        <Button
                            type="button"
                            icon="pi pi-filter-slash"
                            size="small"
                            class="mr-2"
                            label="Clear"
                            outlined
                            @click="clearFilter()"
                        />
                        <SplitButton
                            label="Save"
                            icon="pi pi-check"
                            size="small"
                            class="mr-2"
                            menuButtonIcon="pi pi-cog"
                            @click="save"
                            :model="actions"
                        />
                    </div>
                </div>
            </template>
            <template #empty> No customers found. </template>
            <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
            <Column
                field="name"
                header="Name"
                sortable
                style="min-width: 14rem"
            >
                <template #body="{ data }">
                    {{ data.name }}
                </template>
                <template #filter="{ filterModel }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Search by name"
                    />
                </template>
            </Column>
            <Column
                header="Country"
                sortable
                sortField="country.name"
                filterField="country.name"
                style="min-width: 14rem"
            >
                <template #body="{ data }">
                    <div class="flex align-items-center gap-2">
                        <img
                            alt="flag"
                            src="https://primefaces.org/cdn/primevue/images/flag/flag_placeholder.png"
                            :class="`flag flag-${data.country.code}`"
                            style="width: 24px"
                        />
                        <span>{{ data.country.name }}</span>
                    </div>
                </template>
                <template #filter="{ filterModel }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Search by country"
                    />
                </template>
            </Column>
            <Column
                header="Agent"
                sortable
                sortField="representative.name"
                filterField="representative"
                :showFilterMatchModes="false"
                :filterMenuStyle="{ width: '14rem' }"
                style="min-width: 14rem"
            >
                <template #body="{ data }">
                    <div class="flex align-items-center gap-2">
                        <img
                            :alt="data.representative.name"
                            :src="`https://primefaces.org/cdn/primevue/images/avatar/${data.representative.image}`"
                            style="width: 32px"
                        />
                        <span>{{ data.representative.name }}</span>
                    </div>
                </template>
                <template #filter="{ filterModel }">
                    <MultiSelect
                        v-model="filterModel.value"
                        :options="representatives"
                        optionLabel="name"
                        placeholder="Any"
                        class="p-column-filter"
                    >
                        <template #option="slotProps">
                            <div class="flex align-items-center gap-2">
                                <img
                                    :alt="slotProps.option.name"
                                    :src="`https://primefaces.org/cdn/primevue/images/avatar/${slotProps.option.image}`"
                                    style="width: 32px"
                                />
                                <span>{{ slotProps.option.name }}</span>
                            </div>
                        </template>
                    </MultiSelect>
                </template>
            </Column>
            <Column
                field="date"
                header="Date"
                sortable
                filterField="date"
                dataType="date"
                style="min-width: 10rem"
            >
                <template #body="{ data }">
                    {{ formatDate(data.date) }}
                </template>
                <template #filter="{ filterModel }">
                    <Calendar
                        v-model="filterModel.value"
                        dateFormat="mm/dd/yy"
                        placeholder="mm/dd/yyyy"
                        mask="99/99/9999"
                    />
                </template>
            </Column>
            <Column
                field="balance"
                header="Balance"
                sortable
                filterField="balance"
                dataType="numeric"
                style="min-width: 10rem"
            >
                <template #body="{ data }">
                    {{ formatCurrency(data.balance) }}
                </template>
                <template #filter="{ filterModel }">
                    <InputNumber
                        v-model="filterModel.value"
                        mode="currency"
                        currency="USD"
                        locale="en-US"
                    />
                </template>
            </Column>
            <Column
                header="Status"
                field="status"
                sortable
                :filterMenuStyle="{ width: '14rem' }"
                style="min-width: 12rem"
            >
                <template #body="{ data }">
                    <Tag
                        :value="data.status"
                        :severity="getSeverity(data.status)"
                    />
                </template>
                <template #filter="{ filterModel }">
                    <Dropdown
                        v-model="filterModel.value"
                        :options="statuses"
                        placeholder="Select One"
                        class="p-column-filter"
                        showClear
                    >
                        <template #option="slotProps">
                            <Tag
                                :value="slotProps.option"
                                :severity="getSeverity(slotProps.option)"
                            />
                        </template>
                    </Dropdown>
                </template>
            </Column>
        </DataTable>
    </div>
    <!-- End Table -->

    <!-- Popup AddEvent -->
    <addEventPopup ref="addEventPopup" />
    <!-- End AddEvent -->
</template>

<script>
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import addEventPopup from '@/Popup/WizardAddEvent.vue';

export default {
    components: { addEventPopup },
    data() {
        return {
            openNewDialog: false,
            customers: [
                {
                    id: 1,
                    name: 'John Doe',
                    country: { name: 'USA', code: 'us' },
                    representative: {
                        name: 'Amy Elsner',
                        image: 'amyelsner.png',
                    },
                    date: new Date(),
                    balance: 2000,
                    status: 'qualified',
                    activity: 75,
                },
                {
                    id: 1,
                    name: 'John Doe',
                    country: { name: 'USA', code: 'us' },
                    representative: {
                        name: 'Amy Elsner',
                        image: 'amyelsner.png',
                    },
                    date: new Date(),
                    balance: 2000,
                    status: 'qualified',
                    activity: 75,
                },
                {
                    id: 1,
                    name: 'John Doe',
                    country: { name: 'USA', code: 'us' },
                    representative: {
                        name: 'Amy Elsner',
                        image: 'amyelsner.png',
                    },
                    date: new Date(),
                    balance: 2000,
                    status: 'qualified',
                    activity: 75,
                },
                {
                    id: 1,
                    name: 'John Doe',
                    country: { name: 'USA', code: 'us' },
                    representative: {
                        name: 'Amy Elsner',
                        image: 'amyelsner.png',
                    },
                    date: new Date(),
                    balance: 2000,
                    status: 'qualified',
                    activity: 75,
                },
                {
                    id: 2,
                    name: 'Jane Smith',
                    country: { name: 'Canada', code: 'ca' },
                    representative: {
                        name: 'Anna Fali',
                        image: 'annafali.png',
                    },
                    date: new Date(),
                    balance: 3000,
                    status: 'new',
                    activity: 60,
                },
                // Ajoutez plus de données si nécessaire
            ],
            selectedCustomers: null,
            filters: null,
            representatives: [
                { name: 'Amy Elsner', image: 'amyelsner.png' },
                { name: 'Anna Fali', image: 'annafali.png' },
            ],
            statuses: [
                'unqualified',
                'qualified',
                'new',
                'negotiation',
                'renewal',
                'proposal',
            ],
        };
    },
    created() {
        this.initFilters();
    },
    methods: {
        formatDate(value) {
            return value.toLocaleDateString('en-US', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            });
        },

        showPopup() {
            this.$refs.addEventPopup.open();
        },

        formatCurrency(value) {
            return value.toLocaleString('en-US', {
                style: 'currency',
                currency: 'USD',
            });
        },
        clearFilter() {
            this.initFilters();
        },
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                name: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                'country.name': {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                representative: { value: null, matchMode: FilterMatchMode.IN },
                date: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.DATE_IS },
                    ],
                },
                balance: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
                status: {
                    operator: FilterOperator.OR,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.EQUALS },
                    ],
                },
            };
        },
        getSeverity(status) {
            switch (status) {
                case 'unqualified':
                    return 'danger';
                case 'qualified':
                    return 'danger';
                case 'new':
                    return 'success';
                case 'negotiation':
                    return 'warning';
                default:
                    return null;
            }
        },
    },
};
</script>
<style></style>
