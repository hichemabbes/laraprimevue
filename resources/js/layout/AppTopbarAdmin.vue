<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useLayout } from '@/layout/composables/layout';
import NarrowAppSidebarAdmin from '@/layout/NarrowAppSidebarAdmin.vue';
import NavLink from '@/Components/NavLink.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import axios from '@/axios.js';

const { layoutConfig, onMenuToggle } = useLayout();
const router = useRouter();
const toast = useToast();

const outsideClickListener = ref(null);
const topbarMenuActive = ref(false);
const isNarrowSidebarActive = ref(false);

const logoUrl = computed(() => {
    return `layout/images/${layoutConfig.darkTheme.value ? 'logo-white' : 'logo-dark'}.svg`;
});

const onTopBarMenuButton = () => {
    topbarMenuActive.value = !topbarMenuActive.value;
};

const logout = async () => {
    try {
        // Appeler l'API de déconnexion
        await axios.post('/api/logout');

        // Vider localStorage
        localStorage.clear();

        // Afficher une notification de succès
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Déconnexion réussie',
            life: 3000,
        });

        // Rediriger vers la page de connexion
        router.push({ name: 'login' });
    } catch (error) {
        console.error(
            'Erreur lors de la déconnexion :',
            error.response?.data || error.message
        );
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Échec de la déconnexion',
            life: 3000,
        });
    }
};

const onSettingsClick = () => {
    topbarMenuActive.value = false;
};

const topbarMenuClasses = computed(() => {
    return {
        'layout-topbar-menu-mobile-active': topbarMenuActive.value,
    };
});

const toggleSidebarMode = () => {
    isNarrowSidebarActive.value = !isNarrowSidebarActive.value;
    onMenuToggle();
};

const bindOutsideClickListener = () => {
    if (!outsideClickListener.value) {
        outsideClickListener.value = (event) => {
            if (isOutsideClicked(event)) {
                topbarMenuActive.value = false;
            }
        };
        document.addEventListener('click', outsideClickListener.value);
    }
};

const unbindOutsideClickListener = () => {
    if (outsideClickListener.value) {
        document.removeEventListener('click', outsideClickListener.value);
        outsideClickListener.value = null;
    }
};

const isOutsideClicked = (event) => {
    if (!topbarMenuActive.value) return false;

    const sidebarEl = document.querySelector('.layout-topbar-menu');
    const topbarEl = document.querySelector('.layout-topbar-menu-button');

    return !(
        sidebarEl?.isSameNode(event.target) ||
        sidebarEl?.contains(event.target) ||
        topbarEl?.isSameNode(event.target) ||
        topbarEl?.contains(event.target)
    );
};

onMounted(() => {
    bindOutsideClickListener();
});

onBeforeUnmount(() => {
    unbindOutsideClickListener();
});

const home = { icon: 'pi pi-home' };
</script>

<template>
    <div class="layout-topbar">
        <div
            class="flex align-items-top justify-content-between px-0 pt-1.5 flex-shrink-0"
        >
            <span
                class="inline-flex align-items-center gap-1"
                style="margin-left: 10px"
            >
                <svg
                    width="35"
                    height="40"
                    viewBox="0 0 35 40"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        d="M25.87 18.05L23.16 17.45L25.27 20.46V29.78L32.49 23.76V13.53L29.18 14.73L25.87 18.04V18.05ZM25.27 35.49L29.18 31.58V27.67L25.27 30.98V35.49ZM20.16 17.14H20.03H20.17H20.16ZM30.1 5.19L34.89 4.81L33.08 12.33L24.1 15.67L30.08 5.2L30.1 5.19ZM5.72 14.74L2.41 13.54V23.77L9.63 29.79V20.47L11.74 17.46L9.03 18.06L5.72 14.75V14.74ZM9.63 30.98L5.72 27.67V31.58L9.63 35.49V30.98ZM4.8 5.2L10.78 15.67L1.81 12.33L0 4.81L4.79 5.19L4.8 5.2ZM24.37 21.05V34.59L22.56 37.29L20.46 39.4H14.44L12.34 37.29L10.53 34.59V21.05L12.42 18.23L17.45 26.8L22.48 18.23L24.37 21.05ZM22.85 0L22.57 0.69L17.45 13.08L12.33 0.69L12.05 0H22.85Z"
                        fill="var(--primary-color)"
                    />
                    <path
                        d="M30.69 4.21L24.37 4.81L22.57 0.69L22.86 0H26.48L30.69 4.21ZM23.75 5.67L22.66 3.08L18.05 14.24V17.14H19.7H20.03H20.16H20.2L24.1 15.7L30.11 5.19L23.75 5.67ZM4.21002 4.21L10.53 4.81L12.33 0.69L12.05 0H8.43002L4.22002 4.21H4.21002ZM21.9 17.4L20.6 18.2H14.3L13 17.4L12.4 18.2L12.42 18.23L17.45 26.8L22.48 18.23L22.5 18.2L21.9 17.4ZM4.79002 5.19L10.8 15.7L14.7 17.14H14.74H15.2H16.85V14.24L12.24 3.09L11.15 5.68L4.79002 5.2V5.19Z"
                        fill="var(--text-color)"
                    />
                </svg>
                <span class="font-semibold text-2xl text-primary">
                    Digiform
                </span>
            </span>
        </div>

        <!-- Conteneur pour le bouton menu -->
        <div class="flex align-items-center" style="margin-left: 20px">
            <button
                class="p-link layout-menu-button layout-topbar-button"
                @click="toggleSidebarMode"
            >
                <i class="pi pi-bars"></i>
            </button>
        </div>
        <div class="flex-grow-1 flex justify-content-end">
            <IconField iconPosition="left">
                <InputIcon>
                    <i class="pi pi-search" />
                </InputIcon>
                <InputText placeholder="Keyword Search" size="small" />
            </IconField>
        </div>

        <div class="layout-topbar-menu" :class="topbarMenuClasses">
            <button
                @click="onTopBarMenuButton()"
                class="p-link layout-topbar-button"
            >
                <i class="pi pi-calendar"></i>
                <span>Calendar</span>
            </button>
            <button
                @click="onTopBarMenuButton()"
                class="p-link layout-topbar-button"
            >
                <i class="pi pi-user"></i>
                <span>Profile</span>
            </button>
            <button
                @click="onSettingsClick()"
                class="p-link layout-topbar-button"
            >
                <i class="pi pi-cog"></i>
                <span>Settings</span>
            </button>
            <button @click="logout" class="p-link layout-topbar-button">
                <i class="pi pi-sign-out"></i>
                <span>Logout</span>
            </button>
        </div>

        <NarrowAppSidebarAdmin v-if="isNarrowSidebarActive" />
    </div>
</template>

<style lang="scss" scoped>
.layout-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1rem;
    height: 60px;
    background: var(--surface-a);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
}

.layout-topbar-menu {
    display: flex;
    align-items: center;
}

.layout-topbar-menu-mobile-active {
    display: block;
}
</style>
