<template>
    <div class="flex flex-column h-full layout-sidebar-container">

        <!-- Menu Principal -->
        <div v-if="!isNarrowMenuVisible" class="overflow-y-auto">
            <ul class="list-none p-3 m-0">

                <!-- Dashboard Principal -->
                <li class="mb-2">
                    <router-link
                        to="/"
                        active-class="active-link"
                        exact-active-class="exact-active-link"
                        class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors w-full"
                    >
                        <i class="pi pi-home mr-3"></i>
                        <span class="font-medium">Dashboard</span>
                    </router-link>
                </li>

                <!-- Dashboard Centre (uniquement si rôle centre) -->
                <li class="mb-2" v-if="isCentreRole">
                    <router-link
                        to="/dashboard-centres"
                        active-class="active-link"
                        exact-active-class="exact-active-link"
                        class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors w-full"
                    >
                        <i class="pi pi-chart-line mr-3"></i>
                        <span class="font-medium">Dashboard Centre</span>
                    </router-link>
                </li>

                <!-- Calendrier -->
                <li class="mb-2">
                    <router-link
                        to="/calendar"
                        active-class="active-link"
                        exact-active-class="exact-active-link"
                        class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors w-full"
                    >
                        <i class="pi pi-calendar mr-3"></i>
                        <span class="font-medium">Calendrier</span>
                    </router-link>
                </li>

                <div class="my-4 border-top-1 surface-border"></div>

                <!-- Années -->
                <li class="mb-2">
                    <router-link to="/annees-formation" class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors w-full">
                        <i class="pi pi-calendar mr-3"></i>
                        <span class="font-medium">Années de Formation</span>
                    </router-link>
                </li>
                <li class="mb-2">
                    <router-link to="/annees-administrative" class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors w-full">
                        <i class="pi pi-calendar mr-3"></i>
                        <span class="font-medium">Années Administratives</span>
                    </router-link>
                </li>
                <li class="mb-2">
                    <router-link to="/annee-calcul" class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors w-full">
                        <i class="pi pi-calculator mr-3"></i>
                        <span class="font-medium">Années de Calcul</span>
                    </router-link>
                </li>

                <div class="my-4 border-top-1 surface-border"></div>

                <!-- Formations -->
                <li class="mb-2">
                    <router-link to="/specialites" class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors w-full">
                        <i class="pi pi-sitemap mr-3"></i>
                        <span class="font-medium">Spécialités</span>
                    </router-link>
                </li>
                <li class="mb-2">
                    <router-link to="/prog-specialites" class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors w-full">
                        <i class="pi pi-file-edit mr-3"></i>
                        <span class="font-medium">Programmes Spécialités</span>
                    </router-link>
                </li>
                <li class="mb-2">
                    <router-link to="/themes" class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors w-full">
                        <i class="pi pi-tags mr-3"></i>
                        <span class="font-medium">Thèmes</span>
                    </router-link>
                </li>
                <li class="mb-2">
                    <router-link to="/prog-themes" class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors w-full">
                        <i class="pi pi-file-edit mr-3"></i>
                        <span class="font-medium">Programmes Thèmes</span>
                    </router-link>
                </li>
                <li class="mb-2">
                    <router-link to="/sessions" class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors w-full">
                        <i class="pi pi-clock mr-3"></i>
                        <span class="font-medium">Sessions</span>
                    </router-link>
                </li>
                <li class="mb-2">
                    <router-link to="/formations" class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors w-full">
                        <i class="pi pi-graduation-cap mr-3"></i>
                        <span class="font-medium">Formations</span>
                    </router-link>
                </li>

                <div class="my-4 border-top-1 surface-border"></div>

                <!-- Outils & Admin -->
                <li class="mb-2">
                    <router-link to="/listes" class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors w-full">
                        <i class="pi pi-list mr-3"></i>
                        <span class="font-medium">Listes de Choix</span>
                    </router-link>
                </li>
                <li class="mb-2">
                    <router-link to="/roles" class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors w-full">
                        <i class="pi pi-key mr-3"></i>
                        <span class="font-medium">Gestion des Rôles</span>
                    </router-link>
                </li>
                <li class="mb-2">
                    <router-link to="/password-manager" class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors w-full">
                        <i class="pi pi-lock mr-3"></i>
                        <span class="font-medium">Mots de Passe</span>
                    </router-link>
                </li>
                <li class="mb-2">
                    <router-link to="/activity-log" class="flex align-items-center cursor-pointer p-3 border-round text-700 hover:surface-100 transition-duration-150 transition-colors w-full">
                        <i class="pi pi-history mr-3"></i>
                        <span class="font-medium">Journal d'Activité</span>
                    </router-link>
                </li>

            </ul>

            <!-- Profil utilisateur -->
            <div class="mt-auto p-3">
                <hr class="mb-3 mx-3 border-top-1 border-none surface-border" />
                <a v-ripple class="m-3 flex align-items-center cursor-pointer p-3 gap-2 border-round text-700 hover:surface-100 transition-duration-150 transition-colors p-ripple">
                    <Avatar
                        :image="userAvatar"
                        shape="circle"
                        size="large"
                    />
                    <div class="flex flex-column">
                        <span class="font-bold">{{ userName }}</span>
                        <small class="text-500">{{ userRoleLabel }}</small>
                    </div>
                </a>
            </div>
        </div>

        <!-- Menu mobile -->
        <OpenNarrowMenu v-if="isNarrowMenuVisible" />
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import OpenNarrowMenu from '@/layout/NarrowAppSidebarAdmin.vue'
import Avatar from 'primevue/avatar'

// Rôle centre ?
const isCentreRole = computed(() => {
    return localStorage.getItem('is_centre_role') === '1'
})

// Infos utilisateur
const userName = computed(() => {
    return localStorage.getItem('user_nom_fr') || localStorage.getItem('user_prenom_fr') || 'Utilisateur'
})

const userRoleLabel = computed(() => {
    return isCentreRole.value ? 'Centre de Formation' : (localStorage.getItem('myRole') || 'Administrateur')
})

const userAvatar = computed(() => {
    const photo = localStorage.getItem('user_photo')
    return photo
        ? `/storage/Photos/Personnels/${photo}`
        : '/storage/Photos/Personnels/avatar-default.png'
})

const isNarrowMenuVisible = ref(false)
</script>

<style scoped>
.layout-sidebar-container {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 260px;
    z-index: 999;
    background: var(--surface-a);
    padding-top: 60px;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

/* Couleurs exactement comme ton premier menu d'origine */
.active-link,
.exact-active-link {
    background-color: #6366f1 !important;
    color: white !important;
}

.active-link i,
.exact-active-link i {
    color: white !important;
}

.single-line-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Animation d'entrée douce */
@keyframes sidebarEntry {
    from { transform: translateX(-100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
.layout-sidebar-container {
    animation: sidebarEntry 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) both;
}
</style>
