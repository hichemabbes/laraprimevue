<template>
    <div class="narrow-sidebar">
        <ul class="menu">
            <li v-for="item in menuItems" :key="item.id" class="menu-item">
                <router-link
                    :to="item.route"
                    class="p-menuitem-link"
                    :title="item.name"
                >
                    <i :class="item.icon"></i>
                    <span class="menu-tooltip">{{ item.name }}</span>
                </router-link>
            </li>
        </ul>
    </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
    data() {
        return {
            menuItems: [
                {
                    id: 1,
                    name: 'Dashboard',
                    icon: 'pi pi-chart-line',
                    route: '/',
                },
                {
                    id: 2,
                    name: 'Dashboard Centre',
                    icon: 'pi pi-building',
                    route: '/dashboard-centre',
                },
                {
                    id: 3,
                    name: 'Dashboard FormateursCentres',
                    icon: 'pi pi-users',
                    route: '/dashboard-formateurs',
                },
                {
                    id: 4,
                    name: 'Dashboard StagiairesCentres',
                    icon: 'pi pi-user',
                    route: '/dashboard-stagiaires',
                },
                {
                    id: 5,
                    name: 'Profile',
                    icon: 'pi pi-id-card',
                    route: '/profile',
                },
                {
                    id: 6,
                    name: 'Profile List',
                    icon: 'pi pi-list',
                    route: '/profile-list',
                },
                {
                    id: 7,
                    name: 'Profil Personnel ATFP',
                    icon: 'pi pi-briefcase',
                    route: '/profil-personnel-atfp',
                },
                {
                    id: 8,
                    name: 'Profil Personnel Centre',
                    icon: 'pi pi-briefcase',
                    route: '/profil-personnel-centre',
                },
                {
                    id: 9,
                    name: 'Profil Formateur',
                    icon: 'pi pi-user-edit',
                    route: '/profil-formateur',
                },
                {
                    id: 10,
                    name: 'Profil Stagiaire',
                    icon: 'pi pi-graduation-cap',
                    route: '/profil-stagiaire',
                },
                {
                    id: 11,
                    name: 'Profil Centre',
                    icon: 'pi pi-building',
                    route: '/profil-centre',
                },
                {
                    id: 12,
                    name: 'Calendar',
                    icon: 'pi pi-calendar',
                    route: '/calendar',
                },
                { id: 13, name: 'Users', icon: 'pi pi-users', route: '/users' },
                {
                    id: 14,
                    name: 'Roles',
                    icon: 'pi pi-shield',
                    route: '/roles',
                },
                {
                    id: 15,
                    name: 'Types Listes',
                    icon: 'pi pi-tags',
                    route: '/types-categories',
                },
                {
                    id: 16,
                    name: 'Options Listes',
                    icon: 'pi pi-list-check',
                    route: '/options-listes',
                },
                {
                    id: 17,
                    name: 'Annees Formation',
                    icon: 'pi pi-calendar',
                    route: '/annees-formation',
                },
                {
                    id: 18,
                    name: 'Periodes Repos',
                    icon: 'pi pi-moon',
                    route: '/periodes-repos',
                },
                {
                    id: 19,
                    name: 'Secteurs',
                    icon: 'pi pi-sitemap',
                    route: '/secteurs',
                },
                {
                    id: 20,
                    name: 'Sous Secteurs',
                    icon: 'pi pi-sitemap',
                    route: '/sous-secteurs',
                },
                {
                    id: 21,
                    name: 'Specialites',
                    icon: 'pi pi-book',
                    route: '/specialites',
                },
                {
                    id: 22,
                    name: 'Infos Specialites',
                    icon: 'pi pi-info-circle',
                    route: '/infos-specialites',
                },
                {
                    id: 23,
                    name: 'Docs Specialites',
                    icon: 'pi pi-file-pdf',
                    route: '/docs-specialites',
                },
                {
                    id: 24,
                    name: 'Programmes',
                    icon: 'pi pi-folder',
                    route: '/programmes',
                },
                {
                    id: 25,
                    name: 'Modules',
                    icon: 'pi pi-folder-open',
                    route: '/modules',
                },
                {
                    id: 26,
                    name: 'Decoupages Programmes',
                    icon: 'pi pi-sliders-h',
                    route: '/decoupages-programmes',
                },
                {
                    id: 27,
                    name: 'Centres',
                    icon: 'pi pi-building',
                    route: '/centres',
                },
                {
                    id: 28,
                    name: 'PersonnelsDirectionCentrale',
                    icon: 'pi pi-users',
                    route: '/personnels',
                },
                {
                    id: 29,
                    name: 'StagiairesCentres',
                    icon: 'pi pi-user',
                    route: '/stagiaires',
                },
                {
                    id: 30,
                    name: 'Entreprises',
                    icon: 'pi pi-briefcase',
                    route: '/entreprises',
                },
                {
                    id: 31,
                    name: 'Tuteurs',
                    icon: 'pi pi-user-plus',
                    route: '/tuteurs',
                },
                {
                    id: 32,
                    name: 'Groupes',
                    icon: 'pi pi-users',
                    route: '/groupes',
                },
                {
                    id: 33,
                    name: 'PersonnelsDirectionCentrale Centres',
                    icon: 'pi pi-users',
                    route: '/personnels-centres',
                },
                {
                    id: 34,
                    name: 'Contrats PersonnelsDirectionCentrale',
                    icon: 'pi pi-file-signature',
                    route: '/contrats-personnels',
                },
                {
                    id: 35,
                    name: 'Niveaux',
                    icon: 'pi pi-sort-numeric-up',
                    route: '/niveaux',
                },
                {
                    id: 36,
                    name: 'Salaires',
                    icon: 'pi pi-money-bill',
                    route: '/salaires',
                },
                {
                    id: 37,
                    name: 'Comptes Bancaires',
                    icon: 'pi pi-credit-card',
                    route: '/comptes-bancaires',
                },
                {
                    id: 38,
                    name: 'Regimes Horaires',
                    icon: 'pi pi-clock',
                    route: '/regimes-horaires',
                },
                {
                    id: 39,
                    name: 'Docs PersonnelsDirectionCentrale',
                    icon: 'pi pi-file-export',
                    route: '/docs-personnels',
                },
                {
                    id: 40,
                    name: 'Concours',
                    icon: 'pi pi-trophy',
                    route: '/concours',
                },
                {
                    id: 41,
                    name: 'Formations PersonnelsDirectionCentrale',
                    icon: 'pi pi-bookmark',
                    route: '/formations-personnels',
                },
                {
                    id: 42,
                    name: 'Primes Trimestrielles',
                    icon: 'pi pi-wallet',
                    route: '/primes-trimestrielles',
                },
                {
                    id: 43,
                    name: 'Notes Annuelles',
                    icon: 'pi pi-star',
                    route: '/notes-annuelles',
                },
                {
                    id: 44,
                    name: 'FormateursCentres Specialites',
                    icon: 'pi pi-user-edit',
                    route: '/formateurs-specialites',
                },
                {
                    id: 45,
                    name: 'Groupes Centres',
                    icon: 'pi pi-users',
                    route: '/groupes-centres',
                },
                {
                    id: 46,
                    name: 'StagiairesCentres Groupes',
                    icon: 'pi pi-user-plus',
                    route: '/stagiaires-groupes',
                },
                {
                    id: 47,
                    name: 'Groupes Historique',
                    icon: 'pi pi-history',
                    route: '/groupes-historique',
                },
                {
                    id: 48,
                    name: 'Gestion Groupes',
                    icon: 'pi pi-cog',
                    route: '/gestion-groupes',
                },
                {
                    id: 49,
                    name: 'StagiairesCentres Centres',
                    icon: 'pi pi-user',
                    route: '/stagiaires-centres',
                },
                {
                    id: 50,
                    name: 'Cartes StagiairesCentres',
                    icon: 'pi pi-id-card',
                    route: '/cartes-stagiaires',
                },
                {
                    id: 51,
                    name: 'Phases Formation',
                    icon: 'pi pi-step-forward',
                    route: '/phases-formation',
                },
                {
                    id: 52,
                    name: 'Emplois Temps Groupes',
                    icon: 'pi pi-calendar-plus',
                    route: '/emplois-temps-groupes',
                },
                {
                    id: 53,
                    name: 'Emplois Temps FormateursCentres',
                    icon: 'pi pi-calendar-plus',
                    route: '/emplois-temps-formateurs',
                },
                {
                    id: 54,
                    name: 'Historique Emplois Groupes',
                    icon: 'pi pi-history',
                    route: '/historique-emplois-groupes',
                },
                {
                    id: 55,
                    name: 'Historique Emplois FormateursCentres',
                    icon: 'pi pi-history',
                    route: '/historique-emplois-formateurs',
                },
                {
                    id: 56,
                    name: 'Presences',
                    icon: 'pi pi-check-circle',
                    route: '/presences',
                },
                {
                    id: 57,
                    name: 'Rapports Absences',
                    icon: 'pi pi-chart-bar',
                    route: '/rapports-absences',
                },
                {
                    id: 58,
                    name: 'Evaluations',
                    icon: 'pi pi-star-half',
                    route: '/evaluations',
                },
                {
                    id: 59,
                    name: 'PVs Fin Formation',
                    icon: 'pi pi-file-excel',
                    route: '/pvs-fin-formation',
                },
                {
                    id: 60,
                    name: 'PVs Controle',
                    icon: 'pi pi-file-excel',
                    route: '/pvs-controle',
                },
                {
                    id: 61,
                    name: 'PVs Fin Apprentissage',
                    icon: 'pi pi-file-excel',
                    route: '/pvs-fin-apprentissage',
                },
                {
                    id: 62,
                    name: 'Stages',
                    icon: 'pi pi-briefcase',
                    route: '/stages',
                },
                {
                    id: 63,
                    name: 'Contrats Apprentissage',
                    icon: 'pi pi-file-contract',
                    route: '/contrats-apprentissage',
                },
                {
                    id: 64,
                    name: 'Suivis Pedagogiques',
                    icon: 'pi pi-book',
                    route: '/suivis-pedagogiques',
                },
                {
                    id: 65,
                    name: 'Ordres Mission',
                    icon: 'pi pi-send',
                    route: '/ordres-mission',
                },
                {
                    id: 66,
                    name: 'Historique Adresses',
                    icon: 'pi pi-map-marker',
                    route: '/historique-adresses',
                },
                {
                    id: 67,
                    name: 'Historique Contacts',
                    icon: 'pi pi-phone',
                    route: '/historique-contacts',
                },
                {
                    id: 68,
                    name: 'Activity Log',
                    icon: 'pi pi-history',
                    route: '/activity-log',
                },
            ],
        };
    },
});
</script>

<style scoped>
.narrow-sidebar {
    width: 70px;
    height: calc(100vh - 60px);
    position: fixed;
    top: 60px;
    left: 0;
    background: var(--surface-a);
    z-index: 1001;
    overflow-y: auto;
    transition: all 0.3s ease;
}

.menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.menu-item {
    padding: 1rem 0;
    text-align: center;
    position: relative;
}

.p-menuitem-link {
    color: #4b5563; /* Gris foncé pour le thème light */
    text-decoration: none;
    display: block;
    padding: 0.5rem;
    transition: all 0.2s ease;
    border-radius: 4px;
    margin: 0 0.5rem;
}

.p-menuitem-link:hover {
    background: #818cf8; /* Violet indigo au survol */
    color: #ffffff; /* Blanc au survol */
}

.p-menuitem-link i {
    font-size: 1.2rem;
    display: block;
    margin: 0 auto;
}

.menu-tooltip {
    position: absolute;
    left: 80px;
    top: 50%;
    transform: translateY(-50%);
    background: #818cf8; /* Violet indigo */
    color: #ffffff; /* Texte blanc */
    padding: 0.5rem 1rem;
    border-radius: 4px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease;
    z-index: 1000;
    font-size: 0.8rem;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.menu-item:hover .menu-tooltip {
    opacity: 1;
    visibility: visible;
    left: 90px;
}

/* Adaptation pour le thème dark */
body.dark .narrow-sidebar .p-menuitem-link {
    color: #d1d5db; /* Gris clair pour le thème dark */
}

body.dark .narrow-sidebar .p-menuitem-link:hover {
    background: #818cf8; /* Violet indigo au survol */
    color: #ffffff; /* Blanc au survol */
}
</style>
