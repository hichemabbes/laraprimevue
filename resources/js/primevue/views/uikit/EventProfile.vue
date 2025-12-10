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
            v-model:selection="selectedEvents"
            size="small"
            :value="events"
            paginator
            :rows="10"
            dataKey="id"
            filterDisplay="menu"
            :globalFilterFields="[
                'eventName',
                'location',
                'status',
                'startDate',
                'endDate',
                'totalGuest',
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
                            @click="clearFilter"
                        />
                        <SplitButton
                            label="Save"
                            icon="pi pi-check"
                            size="small"
                            class="mr-2"
                            :disabled="
                                !selectedEvents || selectedEvents.length === 0
                            "
                            :model="actions"
                            @click="save"
                        />
                    </div>
                </div>
            </template>
            <template #empty> No events found. </template>
            <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
            <Column
                field="eventName"
                header="Event Name"
                sortable
                style="min-width: 14rem"
            >
                <template #body="{ data }">
                    {{ data.eventName }}
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
                field="startDate"
                header="Start Date"
                sortable
                filterField="startDate"
                dataType="date"
                style="min-width: 10rem"
            >
                <template #body="{ data }">
                    {{ formatDate(data.startDate) }}
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
                field="endDate"
                header="End Date"
                sortable
                filterField="endDate"
                dataType="date"
                style="min-width: 10rem"
            >
                <template #body="{ data }">
                    {{ formatDate(data.endDate) }}
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
                field="location"
                header="Location"
                sortable
                style="min-width: 14rem"
            >
                <template #body="{ data }">
                    {{ data.location }}
                </template>
                <template #filter="{ filterModel }">
                    <InputText
                        v-model="filterModel.value"
                        type="text"
                        class="p-column-filter"
                        placeholder="Search by location"
                    />
                </template>
            </Column>
            <Column
                field="totalGuest"
                header="Total Guests"
                sortable
                filterField="totalGuest"
                dataType="numeric"
                style="min-width: 10rem"
            >
                <template #body="{ data }">
                    {{ data.totalGuest }}
                </template>
                <template #filter="{ filterModel }">
                    <InputNumber v-model="filterModel.value" mode="decimal" />
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
            events: [
                {
                    id: 1,
                    eventName: 'Event 01',
                    startDate: new Date(),
                    endDate: new Date(),
                    location: 'Tunisia,Sousse',
                    totalGuest: 750,
                    status: 'Live',
                },
                {
                    id: 2,
                    eventName: 'Event 02',
                    startDate: new Date(),
                    endDate: new Date(),
                    location: 'Qatar,Doha',
                    totalGuest: 1245,
                    status: 'Upcoming',
                },
                {
                    id: 3,
                    eventName: 'Event 03',
                    startDate: new Date(),
                    endDate: new Date(),
                    location: 'Lusail',
                    totalGuest: 750,
                    status: 'Live',
                },
            ],
            selectedEvents: null,
            filters: null,
            statuses: ['Live', 'Upcoming', 'Archived'],
            actions: [
                {
                    label: 'Update',
                    icon: 'pi pi-refresh',
                    command: () => this.updateSelectedEvents(),
                },
                {
                    label: 'Restore',
                    icon: 'pi pi-replay',
                    command: () => this.restoreSelectedEvents(),
                },
                {
                    label: 'Archived',
                    icon: 'pi pi-trash',
                    command: () => this.archiveSelectedEvents(),
                },
                {
                    label: 'Export',
                    icon: 'pi pi-download',
                    command: () => this.exportSelectedEvents(),
                },
                {
                    label: 'Duplicate',
                    icon: 'pi pi-copy',
                    command: () => this.duplicateSelectedEvents(),
                },
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
        clearFilter() {
            this.initFilters();
        },
        initFilters() {
            this.filters = {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                eventName: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                location: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    ],
                },
                startDate: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.DATE_IS },
                    ],
                },
                endDate: {
                    operator: FilterOperator.AND,
                    constraints: [
                        { value: null, matchMode: FilterMatchMode.DATE_IS },
                    ],
                },
                totalGuest: {
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
                case 'Live':
                    return 'success';
                case 'Upcoming':
                    return 'warning';
                case 'Archived':
                    return 'danger';
                default:
                    return null;
            }
        },
        save() {
            // Logique pour sauvegarder les événements sélectionnés
            console.log('Saving selected events:', this.selectedEvents);
        },
        updateSelectedEvents() {
            // Logique pour mettre à jour les événements sélectionnés
            console.log('Updating selected events:', this.selectedEvents);
        },
        restoreSelectedEvents() {
            // Logique pour restaurer les événements sélectionnés
            console.log('Restoring selected events:', this.selectedEvents);
        },
        archiveSelectedEvents() {
            // Logique pour archiver les événements sélectionnés
            console.log('Archiving selected events:', this.selectedEvents);
        },
        exportSelectedEvents() {
            // Logique pour exporter les événements sélectionnés
            console.log('Exporting selected events:', this.selectedEvents);
        },
        duplicateSelectedEvents() {
            // Logique pour dupliquer les événements sélectionnés
            console.log('Duplicating selected events:', this.selectedEvents);
        },
    },
};
</script>

<style>
/* Vos styles ici */
</style>
