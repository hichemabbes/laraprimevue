<template>
    <div class="event-profile">
        <!-- Bannière en haut avec effet de parallaxe -->
        <div v-if="!hideBanner" class="banner-container">
            <div class="banner-image relative">
                <!-- Bannière avec drag-and-drop -->
                <div v-if="bannerImage" class="banner-image-wrapper">
                    <img
                        :src="bannerImage"
                        alt="Banner"
                        class="w-full h-15rem border-round"
                    />
                    <div class="overlay"></div>
                    <div class="banner-actions absolute top-0 right-0 p-2">
                        <Button
                            icon="pi pi-pencil"
                            class="p-button-rounded p-button-text p-button-sm"
                            @click="editBanner"
                        />
                        <Button
                            icon="pi pi-trash"
                            class="p-button-rounded p-button-text p-button-sm ml-2"
                            @click="deleteBanner"
                        />
                    </div>
                </div>
                <div
                    v-else
                    class="banner-placeholder flex align-items-center justify-content-center bg-white-alpha-10 h-15rem border-round"
                    @dragover.prevent="handleDragOver"
                    @drop.prevent="handleDrop"
                >
                    <label
                        for="banner-upload"
                        class="cursor-pointer text-primary-50"
                    >
                        <lord-icon
                            src="https://cdn.lordicon.com/xxdqfhbi.json"
                            trigger="hover"
                            colors="primary:#ffffff"
                            style="width: 50px; height: 50px"
                        ></lord-icon>
                        <span class="ml-2"
                            >Drag & drop or click to upload banner</span
                        >
                    </label>
                    <input
                        id="banner-upload"
                        type="file"
                        accept="image/*"
                        @change="uploadBanner"
                        class="hidden"
                    />
                </div>

                <!-- Logo superposé à gauche avec animation -->
                <div
                    class="logo-overlay absolute left-0 bottom-0 transform translate-y-50"
                >
                    <div v-if="logoImage" class="logo-image">
                        <img
                            :src="logoImage"
                            alt="Logo"
                            class="w-8rem h-8rem border-round hover-zoom"
                        />
                        <div class="logo-actions absolute top-0 right-0 p-2">
                            <Button
                                icon="pi pi-pencil"
                                class="p-button-rounded p-button-text p-button-sm"
                                @click="editLogo"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-rounded p-button-text p-button-sm ml-2"
                                @click="deleteLogo"
                            />
                        </div>
                    </div>
                    <div
                        v-else
                        class="logo-placeholder flex align-items-center justify-content-center bg-white-alpha-10 w-8rem h-8rem border-round"
                    >
                        <label
                            for="logo-upload"
                            class="cursor-pointer text-primary-50"
                        >
                            <lord-icon
                                src="https://cdn.lordicon.com/hbvyhtse.json"
                                trigger="hover"
                                colors="primary:#ffffff"
                                style="width: 50px; height: 50px"
                            ></lord-icon>
                        </label>
                        <input
                            id="logo-upload"
                            type="file"
                            accept="image/*"
                            @change="uploadLogo"
                            class="hidden"
                        />
                    </div>
                </div>

                <!-- Nom de l'événement et détails au centre de la bannière -->
                <div
                    class="event-name-overlay absolute top-50 left-50 transform translate-x--50 translate-y--50 text-center w-full max-w-md"
                >
                    <h4 class="text-primary-50 font-bold text-xl animate-text">
                        {{ eventName }}
                    </h4>
                    <p class="text-primary-50 mt-2 animate-text">
                        {{ startDate }} - {{ endDate }}
                    </p>
                    <p class="text-primary-50 animate-text">{{ location }}</p>
                </div>
            </div>
        </div>

        <!-- Tabs de navigation avec icônes animées -->
        <div class="card">
            <div class="flex align-items-center justify-content-between">
                <!-- Onglets de navigation -->
                <TabMenu :model="tabItems" />

                <!-- Bouton pour cacher la bannière et basculer le thème -->
                <div class="flex align-items-center gap-4">
                    <InputSwitch v-model="hideBanner" />
                    <label class="ml-2">Hide Banner</label>
                </div>
            </div>
        </div>

        <!-- Contenu des onglets -->
        <div class="tab-content">
            <div v-if="activeTab === 'Statistics'">
                <div class="stats">
                    <Card
                        v-for="stat in statistics"
                        :key="stat.title"
                        class="stat-card hover-scale"
                    >
                        <template #title>{{ stat.title }}</template>
                        <template #content>
                            <p>{{ stat.value }}</p>
                        </template>
                    </Card>
                </div>
            </div>
            <div v-if="activeTab === 'Event Info'">
                <div class="event-info">
                    <p>{{ eventDescription }}</p>
                </div>
            </div>
            <div v-if="activeTab === 'Agenda'">
                <div class="agenda">
                    <!-- Bouton pour ajouter un événement -->
                    <Button
                        label="Add Event"
                        icon="pi pi-plus"
                        class="mb-4"
                        @click="openEventDialog"
                    />

                    <!-- Timeline des événements -->
                    <Timeline :value="agendaItems" align="alternate">
                        <template #content="slotProps">
                            <div class="timeline-item hover-scale">
                                <h3>{{ slotProps.item.time }}</h3>
                                <p>
                                    <strong>{{ slotProps.item.title }}</strong>
                                </p>
                                <p>{{ slotProps.item.description }}</p>
                                <p>
                                    <i class="pi pi-user"></i>
                                    {{ slotProps.item.speaker }}
                                </p>
                            </div>
                        </template>
                    </Timeline>
                </div>
            </div>
        </div>

        <!-- Dialog pour ajouter un événement -->
        <Dialog
            v-model:visible="eventDialogVisible"
            header="Add Event"
            :modal="true"
            class="p-fluid"
        >
            <div class="p-field">
                <label for="event-time">Time</label>
                <InputText
                    id="event-time"
                    v-model="newEvent.time"
                    placeholder="HH:MM"
                />
            </div>
            <div class="p-field">
                <label for="event-title">Title</label>
                <InputText
                    id="event-title"
                    v-model="newEvent.title"
                    placeholder="Event Title"
                />
            </div>
            <div class="p-field">
                <label for="event-description">Description</label>
                <Textarea
                    id="event-description"
                    v-model="newEvent.description"
                    placeholder="Event Description"
                />
            </div>
            <div class="p-field">
                <label for="event-speaker">Speaker</label>
                <InputText
                    id="event-speaker"
                    v-model="newEvent.speaker"
                    placeholder="Speaker Name"
                />
            </div>
            <template #footer>
                <Button
                    label="Cancel"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="closeEventDialog"
                />
                <Button label="Add" icon="pi pi-check" @click="addEvent" />
            </template>
        </Dialog>

        <!-- Toast pour les notifications -->
        <Toast />
    </div>
</template>

<script>
import { ref } from 'vue';
import TabMenu from 'primevue/tabmenu';
import Card from 'primevue/card';
import Timeline from 'primevue/timeline';
import InputSwitch from 'primevue/inputswitch';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

export default {
    components: {
        TabMenu,
        Card,
        Timeline,
        InputSwitch,
        Button,
        Dialog,
        InputText,
        Textarea,
        Toast,
    },
    setup() {
        const toast = useToast();
        const hideBanner = ref(false);
        const bannerImage = ref('');
        const logoImage = ref('');
        const eventName = ref('Event Name');
        const startDate = ref('01/01/2023');
        const endDate = ref('02/01/2023');
        const location = ref('Paris, France');
        const eventDescription = ref('Event description...');
        const statistics = ref([
            { title: 'Participants', value: '1200' },
            { title: 'Workshops', value: '15' },
            { title: 'Sponsors', value: '10' },
        ]);
        const agendaItems = ref([
            {
                time: '09:00',
                title: 'Welcome Session',
                description: 'Welcome coffee and registration.',
                speaker: 'John Doe',
            },
            {
                time: '10:00',
                title: 'Keynote: Vue.js 3',
                description: 'Introduction to Vue.js 3 features.',
                speaker: 'Jane Smith',
            },
            {
                time: '12:00',
                title: 'Lunch Break',
                description: 'Buffet lunch for all participants.',
                speaker: '',
            },
            {
                time: '14:00',
                title: 'Workshop: PrimeVue',
                description: 'Learn how to use PrimeVue for modern UIs.',
                speaker: 'Alice Johnson',
            },
        ]);

        const eventDialogVisible = ref(false);
        const newEvent = ref({
            time: '',
            title: '',
            description: '',
            speaker: '',
        });

        const tabItems = ref([
            {
                label: 'Statistics',
                icon: 'pi pi-chart-bar',
                command: () => setActiveTab('Statistics'),
            },
            {
                label: 'Event Info',
                icon: 'pi pi-info-circle',
                command: () => setActiveTab('Event Info'),
            },
            {
                label: 'Agenda',
                icon: 'pi pi-calendar',
                command: () => setActiveTab('Agenda'),
            },
        ]);

        const activeTab = ref('Statistics');
        const isDarkMode = ref(false);

        const setActiveTab = (tab) => {
            activeTab.value = tab;
        };

        const uploadBanner = (event) => {
            const file = event.target.files[0] || event.dataTransfer.files[0];
            if (file) {
                bannerImage.value = URL.createObjectURL(file);
                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: 'Banner uploaded successfully!',
                    life: 3000,
                });
            }
        };

        const uploadLogo = (event) => {
            const file = event.target.files[0];
            if (file) {
                logoImage.value = URL.createObjectURL(file);
                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: 'Logo uploaded successfully!',
                    life: 3000,
                });
            }
        };

        const handleDragOver = (event) => {
            event.preventDefault();
        };

        const handleDrop = (event) => {
            event.preventDefault();
            uploadBanner(event);
        };

        const openEventDialog = () => {
            eventDialogVisible.value = true;
        };

        const closeEventDialog = () => {
            eventDialogVisible.value = false;
            newEvent.value = {
                time: '',
                title: '',
                description: '',
                speaker: '',
            };
        };

        const addEvent = () => {
            if (newEvent.value.time && newEvent.value.title) {
                agendaItems.value.push({ ...newEvent.value });
                closeEventDialog();
                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: 'Event added successfully!',
                    life: 3000,
                });
            }
        };

        const toggleDarkMode = () => {
            isDarkMode.value = !isDarkMode.value;
        };

        const editBanner = () => {
            // Logique pour éditer la bannière
            toast.add({
                severity: 'info',
                summary: 'Info',
                detail: 'Edit banner feature coming soon!',
                life: 3000,
            });
        };

        const deleteBanner = () => {
            bannerImage.value = '';
            toast.add({
                severity: 'warn',
                summary: 'Warning',
                detail: 'Banner deleted!',
                life: 3000,
            });
        };

        const editLogo = () => {
            // Logique pour éditer le logo
            toast.add({
                severity: 'info',
                summary: 'Info',
                detail: 'Edit logo feature coming soon!',
                life: 3000,
            });
        };

        const deleteLogo = () => {
            logoImage.value = '';
            toast.add({
                severity: 'warn',
                summary: 'Warning',
                detail: 'Logo deleted!',
                life: 3000,
            });
        };

        return {
            hideBanner,
            bannerImage,
            logoImage,
            eventName,
            startDate,
            endDate,
            location,
            eventDescription,
            statistics,
            agendaItems,
            eventDialogVisible,
            newEvent,
            tabItems,
            activeTab,
            isDarkMode,
            setActiveTab,
            uploadBanner,
            uploadLogo,
            handleDragOver,
            handleDrop,
            openEventDialog,
            closeEventDialog,
            addEvent,
            toggleDarkMode,
            editBanner,
            deleteBanner,
            editLogo,
            deleteLogo,
        };
    },
};
</script>

<style scoped>
.event-profile {
    margin: 0 auto;
    transition:
        background-color 0.3s,
        color 0.3s;
}

.dark-mode {
    background-color: #1a1a1a;
    color: #ffffff;
}

.banner-container {
    position: relative;
    overflow: hidden;
}

.banner-image {
    height: 15rem;
    border-radius: 10px;
    overflow: hidden;
    transition: transform 0.3s ease-out;
}

.banner-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.banner-placeholder {
    border: 2px dashed var(--primary-color);
    transition: border-color 0.3s;
}

.banner-placeholder:hover {
    border-color: var(--primary-400);
}

.overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
}

.logo-overlay {
    z-index: 1;
}

.logo-image img {
    width: 8rem;
    height: 8rem;
    object-fit: cover;
    transition: transform 0.3s;
}

.logo-image img:hover {
    transform: scale(1.1);
}

.logo-placeholder {
    border: 2px dashed var(--primary-color);
    transition: border-color 0.3s;
}

.logo-placeholder:hover {
    border-color: var(--primary-400);
}

.toggle-banner {
    position: absolute;
    left: 20px;
    top: 20px;
    z-index: 2;
    display: flex;
    align-items: center;
    gap: 10px;
}

.event-name-overlay {
    z-index: 1;
}

.animate-text {
    animation: fadeIn 1s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.stats {
    display: flex;
    gap: 20px;
    margin-top: 20px;
}

.stat-card {
    flex: 1;
}

.stat-card:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.timeline-item {
    padding: 15px;
    background-color: var(--surface-card);
    border-radius: 10px;
    margin-bottom: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition:
        transform 0.3s,
        box-shadow 0.3s;
}

.timeline-item:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.tab-content {
    margin-top: 20px;
}
</style>
