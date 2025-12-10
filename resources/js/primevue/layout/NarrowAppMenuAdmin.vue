<template>
    <div class="sidebar">
        <ul class="menu">
            <li v-for="item in menuItems" :key="item.id" class="menu-item">
                <p-tooltip
                    :target="`#menu-item-${item.id}`"
                    :content="item.name"
                    position="right"
                    :style="tooltipStyle"
                >
                    <i :id="`menu-item-${item.id}`" :class="item.icon"></i>
                </p-tooltip>
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
                { id: 2, name: 'Settings', icon: 'pi pi-cog' },
                { id: 3, name: 'Orders', icon: 'pi pi-box' },
                { id: 4, name: 'Users', icon: 'pi pi-users' },
                { id: 5, name: 'Reports', icon: 'pi pi-chart-line' },
                { id: 6, name: 'Calendar', icon: 'pi pi-calendar' },
                { id: 7, name: 'Messages', icon: 'pi pi-envelope' },
                // Ajoutez ici d'autres items de menu
            ],
        };
    },
    computed: {
        tooltipStyle() {
            return {
                'font-size': '10px',
                'background-color': 'rgba(18,18,18,0.17)', // Couleur de fond du tooltip
                color: '#fff', // Couleur du texte du tooltip
                padding: '5px 6px', // Espacement interne
                'border-radius': '5px',
                'box-shadow': '0 2px 5px rgba(0, 0, 0, 0.2)', // Ombre portée pour le tooltip
            };
        },
    },
};
</script>

<style scoped></style>
