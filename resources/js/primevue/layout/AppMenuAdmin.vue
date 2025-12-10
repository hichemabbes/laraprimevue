<template>
    <div class="flex flex-column h-full">
        <!-- Menu principal -->
        <div v-if="!isNarrowMenuVisible" class="overflow-y-auto">
            <ul class="list-none p-2 m-0">
                <!-- Dashboard en haut -->
                <li>
                    <router-link
                        to="/"
                        active-class="active-link"
                        exact-active-class="exact-active-link"
                        class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors"
                    >
                        <i class="pi pi-home mr-2"></i>
                        <span class="font-medium">Dashboard</span>
                    </router-link>
                </li>

                <!-- Section ATFP -->
                <li>
                    <div
                        v-ripple
                        v-styleclass="{
                            selector: '@next',
                            enterClass: 'hidden',
                            enterActiveClass: 'slidedown',
                            leaveToClass: 'hidden',
                            leaveActiveClass: 'slideup',
                        }"
                        class="p-3 flex align-items-center justify-content-between text-600 cursor-pointer p-ripple"
                    >
                        <div class="flex align-items-center">
                            <i class="pi pi-folder mr-2"></i>
                            <span class="font-medium single-line-text"
                                >ATFP</span
                            >
                        </div>
                        <i class="pi pi-chevron-down"></i>
                    </div>
                    <ul class="list-none p-0 m-0 overflow-hidden">
                        <li>
                            <router-link
                                to="/annees"
                                active-class="active-link"
                                exact-active-class="exact-active-link"
                                class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors"
                            >
                                <i class="pi pi-calendar mr-2"></i>
                                <span class="font-medium single-line-text"
                                    >Années de formation</span
                                >
                            </router-link>
                        </li>
                        <!-- Menu Spécialités -->
                        <li>
                            <div
                                v-ripple
                                v-styleclass="{
                                    selector: '@next',
                                    enterClass: 'hidden',
                                    enterActiveClass: 'slidedown',
                                    leaveToClass: 'hidden',
                                    leaveActiveClass: 'slideup',
                                }"
                                class="p-3 flex align-items-center justify-content-between text-600 cursor-pointer p-ripple"
                            >
                                <div class="flex align-items-center">
                                    <i class="pi pi-folder mr-2"></i>
                                    <span class="font-medium single-line-text"
                                        >Spécialités</span
                                    >
                                </div>
                                <i class="pi pi-chevron-down"></i>
                            </div>
                            <ul class="list-none p-0 m-0 overflow-hidden">
                                <li>
                                    <router-link
                                        to="/specialites"
                                        active-class="active-link"
                                        exact-active-class="exact-active-link"
                                        class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors"
                                    >
                                        <i class="pi pi-sitemap mr-2"></i>
                                        <span
                                            class="font-medium single-line-text"
                                            >Spécialités Liste</span
                                        >
                                    </router-link>
                                </li>
                                <li>
                                    <router-link
                                        to="/specialitesP"
                                        active-class="active-link"
                                        exact-active-class="exact-active-link"
                                        class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors"
                                    >
                                        <i class="pi pi-sitemap mr-2"></i>
                                        <span
                                            class="font-medium single-line-text"
                                            >Spécialités Liste</span
                                        >
                                    </router-link>
                                </li>
                                <li>
                                    <router-link
                                        to="/programmes"
                                        active-class="active-link"
                                        exact-active-class="exact-active-link"
                                        class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors"
                                    >
                                        <i class="pi pi-book mr-2"></i>
                                        <span
                                            class="font-medium single-line-text"
                                            >Programmes</span
                                        >
                                    </router-link>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <router-link
                                to="/centres"
                                active-class="active-link"
                                exact-active-class="exact-active-link"
                                class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors"
                            >
                                <i class="pi pi-calendar mr-2"></i>
                                <span class="font-medium single-line-text"
                                    >Centres111</span
                                >
                            </router-link>
                        </li>
                    </ul>
                </li>
            </ul>

            <!-- Section APPLICATION -->
            <ul class="list-none p-3 m-0">
                <li>
                    <div
                        v-ripple
                        v-styleclass="{
                            selector: '@next',
                            enterClass: 'hidden',
                            enterActiveClass: 'slidedown',
                            leaveToClass: 'hidden',
                            leaveActiveClass: 'slideup',
                        }"
                        class="p-3 flex align-items-center justify-content-between text-600 cursor-pointer p-ripple"
                    >
                        <span class="font-medium">APPLICATION</span>
                        <i class="pi pi-chevron-down"></i>
                    </div>
                    <ul class="list-none p-0 m-0 overflow-hidden">
                        <li>
                            <a
                                v-ripple
                                class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors p-ripple"
                            >
                                <i class="pi pi-folder mr-2"></i>
                                <span class="font-medium">Projects</span>
                            </a>
                        </li>
                        <li>
                            <a
                                v-ripple
                                class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors p-ripple"
                            >
                                <i class="pi pi-chart-bar mr-2"></i>
                                <span class="font-medium">Performance</span>
                            </a>
                        </li>
                        <li>
                            <a
                                v-ripple
                                class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors p-ripple"
                            >
                                <i class="pi pi-cog mr-2"></i>
                                <span class="font-medium">Settings</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

        <!-- Section utilisateur -->
        <div class="mt-auto">
            <hr class="mb-3 mx-3 border-top-1 border-none surface-border" />
            <a
                v-ripple
                class="m-3 flex align-items-center cursor-pointer p-3 gap-2 border-round text-700 hover:surface-100 transition-duration-150 transition-colors p-ripple"
            >
                <Avatar
                    image="https://primefaces.org/cdn/primevue/images/avatar/amyelsner.png"
                    shape="circle"
                />
                <span class="font-bold">Amy Elsner</span>
            </a>
        </div>
    </div>

    <!-- Menu étroit -->
    <OpenNarrowMenu v-if="isNarrowMenuVisible" ref="OpenNarrowMenu" />
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useLayout } from '@/primevue/layout/composables/layout';
import OpenNarrowMenu from '@/primevue/layout/NarrowAppSidebarAdmin.vue';

const eventId = 2;

const { layoutConfig } = useLayout();
const outsideClickListener = ref(null);
const topbarMenuActive = ref(false);
const isNarrowMenuVisible = ref(false);

const onMenuToggle = () => {
    isNarrowMenuVisible.value = !isNarrowMenuVisible.value;
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
        sidebarEl?.contains(event.target) || topbarEl?.contains(event.target)
    );
};

onMounted(() => {
    bindOutsideClickListener();
});

onBeforeUnmount(() => {
    unbindOutsideClickListener();
});
</script>

<style scoped>
/* Styles pour les liens actifs */
.active-link,
.exact-active-link {
    background-color: var(--primary-color) !important;
    color: white !important;
    border-radius: var(--border-radius);
}

.active-link i,
.exact-active-link i {
    color: white !important;
}

.single-line-text {
    white-space: nowrap; /* Empêche le texte de passer à la ligne */
    overflow: hidden; /* Cache le texte débordant */
    text-overflow: ellipsis; /* Affiche des points de suspension (...) si le texte dépasse */
    max-width: 100%; /* Assure que le texte reste dans les limites du conteneur */
}
</style>
