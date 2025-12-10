<template>
    <div class="sidebar-event">
        <ul class="menu">
            <li v-for="item in menuItems" :key="item.id" class="menu-item">
                <router-link
                    :to="getRoute(item.id)"
                    active-class="active-link"
                    exact-active-class="exact-active-link"
                    class="flex align-items-center cursor-pointer border-round text-700 hover:surface-100 transition-duration-150 transition-colors"
                >
                    <p-tooltip
                        :target="`#menu-item-${item.id}`"
                        :content="item.name"
                        position="right"
                        :style="tooltipStyle"
                    >
                        <i :id="`menu-item-${item.id}`" :class="item.icon"></i>
                    </p-tooltip>
                </router-link>
            </li>
        </ul>
    </div>
</template>

<script>
import Button from 'primevue/button';

export default {
    components: { Button },
    data() {
        return {
            menuItems: [
                { id: 1, name: 'Dashboard', icon: 'pi pi-home' },
                { id: 2, name: 'Event List', icon: 'pi pi-calendar' },
                { id: 3, name: 'Profile', icon: 'pi pi-spin pi-cog' },

                // Ajoutez ici d'autres items de menu
            ],
            accountId: this.$route.params.id || this.$route.params.idaccount, // Récupère l'ID de l'événement
        };
    },
    computed: {
        tooltipStyle() {
            return {
                'font-size': '10px',
                'background-color': 'rgba(7,8,14,0.19)', // Couleur de fond du tooltip
                color: '#fff', // Couleur du texte du tooltip
                padding: '4px 5.5px', // Espacement interne
                'border-radius': '5px',
                'box-shadow': '0 2px 5px rgba(0, 0, 0, 0.2)', // Ombre portée pour le tooltip
            };
        },
    },
    methods: {
        getRoute(itemId) {
            const accountId = localStorage.getItem('account_id'); // Récupère l'ID du compte depuis le localStorage
            if (!accountId) {
                console.error('Account ID is undefined!');
                return '/404'; // Retourne une route par défaut si l'ID est manquant
            }

            switch (itemId) {
                case 1:
                    return `/company/${accountId}/dashboard`; // Utilise l'ID du compte pour construire l'URL
                case 2:
                    return `/company/${accountId}/events`; // Utilise l'ID du compte pour construire l'URL
                case 3:
                    return `/company/${accountId}/profile`; // Utilise l'ID du compte pour construire l'URL
                default:
                    return '/'; // Route par défaut
            }
        },
    },
};
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

/* Styles de base pour les éléments du menu */
.menu-item a {
    display: flex;
    align-items: center;
    text-decoration: none;
    transition:
        background-color 0.3s,
        color 0.3s;
}

.menu-item a:hover {
    background-color: rgba(0, 0, 0, 0.1); /* Effet de survol */
}
</style>
