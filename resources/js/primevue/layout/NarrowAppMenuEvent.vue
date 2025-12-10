<template>
    <div class="sidebar-event">
        <ul class="menu">
            <li v-for="item in menuItems" :key="item.id" class="menu-item">
                <router-link :to="getRoute(item.id)" active-class="active">
                    <p-tooltip
                        :target="`#menu-item-${item.id}`"
                        :content="item.name"
                        position="right"
                        :style="tooltipStyle"
                    >
                        <i
                            :id="`menu-item-${item.id}`"
                            :class="item.icon"
                            class="menu-icon"
                        ></i>
                    </p-tooltip>
                </router-link>
            </li>
        </ul>
    </div>
</template>

<script>
import Button from 'primevue/button';

export default {
    name: 'SidebarEvent',
    components: { Button },
    data() {
        return {
            menuItems: [
                { id: 1, name: 'Event Info', icon: 'pi pi-info-circle' }, // Info sur l'événement
                { id: 2, name: 'Groups', icon: 'pi pi-users' }, // Groupes de personnes
                { id: 3, name: 'Guest List', icon: 'pi pi-list' }, // Liste des invités
                { id: 4, name: 'Badge Builder', icon: 'pi pi-id-card' }, // Création de badges
                { id: 5, name: 'Form Builder', icon: 'pi pi-file-edit' }, // Édition de formulaires
                { id: 6, name: 'Company List', icon: 'pi pi-building' }, // Liste des entreprises
                { id: 7, name: 'Tickets List', icon: 'pi pi-ticket' }, // Liste des tickets
                { id: 8, name: 'Users Tickets', icon: 'pi pi-user-plus' }, // Tickets des utilisateurs
                { id: 9, name: 'Plan Event', icon: 'pi pi-sun' }, // Plan
                { id: 10, name: 'Settings Event', icon: 'pi pi-moon' },
                { id: 11, name: 'Emails Builder', icon: 'pi pi-list' },
                { id: 12, name: 'Emails Builder 2', icon: 'pi pi-list' },
            ],
            eventId:
                this.$route.params.id || this.$route.params.idevent || null,
        };
    },
    computed: {
        tooltipStyle() {
            return {
                'font-size': '10px',
                'background-color': 'rgba(122, 122, 122, 0.17)',
                color: '#fff',
                padding: '4px 5.5px',
                'border-radius': '5px',
                'box-shadow': '0 2px 5px rgba(0, 0, 0, 0.2)',
            };
        },
    },
    methods: {
        getRoute(itemId) {
            const { eventId } = this;

            if (!eventId) {
                console.error('Event ID is undefined!');
                return '/';
            }

            const routes = {
                1: `/profile/${eventId}/event`,
                2: `/event/${eventId}/groups`,
                3: `/event/${eventId}/guests`,
                4: `/event/${eventId}/badge`,
                5: `/event/${eventId}/formbuilder`,
                6: `/event/${eventId}/companies`,
                7: `/event/${eventId}/tickets`,
                8: `/event/${eventId}/users/tickets`,
                9: `/event/${eventId}/plan`,
                10: `/event/${eventId}/settings`,
                11: `/event/${eventId}/emails/builder`,
                12: `/event/${eventId}/emails/templates`,
            };

            return routes[itemId] || '/';
        },
    },
};
</script>

<style scoped>
.sidebar-event {
    height: 100%;
    background-color: #f8f9fa;
    padding: 1rem 0;
}

.menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.menu-item {
    margin: 0.5rem 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.menu-item a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 8px;
    text-decoration: none;
    color: #495057;
    transition: all 0.3s ease;
}

.menu-item a:hover {
    background-color: rgba(0, 0, 0, 0.1);
    color: #007bff;
}

.menu-item a.active {
    background-color: #007bff;
    color: white;
}

.menu-icon {
    font-size: 1.5rem;
}
</style>
