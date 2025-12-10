import { createRouter, createWebHistory } from 'vue-router';
import axios from '@/axios.js';

// ──────────────────────────────────────────────────────────────
// Auth
// ──────────────────────────────────────────────────────────────
import Login            from '@/views/Auth/Login.vue';
import Register         from '@/views/Auth/Inscription.vue';
import ForgotPassword   from '@/views/Auth/ForgotPassword.vue';
import ResetPassword    from '@/views/Auth/ResetPassword.vue';
import AccessDenied     from '@/views/Auth/Access.vue';
import ErrorPage        from '@/views/Auth/Error.vue';
import Logout           from '@/views/Auth/Logout.vue';

// ──────────────────────────────────────────────────────────────
// Dashboards
// ──────────────────────────────────────────────────────────────
import DashboardAtfp        from '@/views/Dashboards/DashboardPersonnelsAtfp.vue';
import DashboardCentres     from '@/views/Dashboards/DashboardPersonnelsCentres.vue';
import DashboardFormateurs  from '@/views/Dashboards/DashboardFormateurs.vue';
import DashboardStagiaires  from '@/views/Dashboards/DashboardStagiaires.vue';

// ──────────────────────────────────────────────────────────────
// Tools
// ──────────────────────────────────────────────────────────────
import Calendar         from '@/views/Tools/Calendar.vue';
import PasswordManager  from '@/views/Tools/PasswordManager.vue';

// ──────────────────────────────────────────────────────────────
// Années
// ──────────────────────────────────────────────────────────────
import AnneesFormation      from '@/views/Annees/AnneesFormation.vue';
import AnneesAdministrative from '@/views/Annees/AnneesAministrative.vue';
import AnneeCalcul          from '@/views/Annees/AnneeCalcul.vue';

// ──────────────────────────────────────────────────────────────
// Autres pages
// ──────────────────────────────────────────────────────────────
import Listes       from '@/views/Listes.vue';
import Roles        from '@/views/Roles/Roles.vue';
import ActivityLog  from '@/views/Centres/Historique/ActivityLog.vue';

// ──────────────────────────────────────────────────────────────
// Formations / Spécialités
// ──────────────────────────────────────────────────────────────
import Specialites      from '@/views/Formations/Specialites.vue';      // ← corrigé
import ProgSpecialites  from '@/views/Formations/ProgSpecialites.vue';  // ← corrigé

import Themes          from '@/views/Formations/Themes.vue';
import ProgThemes      from '@/views/Formations/ProgThemes.vue';

import Sessions        from '@/views/Formations/Sessions.vue';
import Formations      from '@/views/Formations/Formations.vue';

// ──────────────────────────────────────────────────────────────
// Définition des routes
// ──────────────────────────────────────────────────────────────
const routes = [
    // ── Auth ─────────────────────────────────────
    { path: '/login',           component: Login,           name: 'login' },
    { path: '/register',        component: Register,        name: 'register' },
    { path: '/logout',          component: Logout,          name: 'logout' },
    { path: '/forgot-password', component: ForgotPassword,  name: 'forgot-password' },
    { path: '/reset-password',  component: ResetPassword,   name: 'reset-password' },
    { path: '/access-denied',   component: AccessDenied,    name: 'access-denied' },
    { path: '/error',           component: ErrorPage,       name: 'error' },

    // ── Dashboards ───────────────────────────────
    { path: '/',                     component: DashboardAtfp,       name: 'dashboard',           meta: { requiresAuth: true } },
    { path: '/dashboard-centres',    component: DashboardCentres,    name: 'dashboard-centres',   meta: { requiresAuth: true, requiresCentreRole: true } },
    { path: '/dashboard-formateurs', component: DashboardFormateurs, name: 'dashboard-formateurs',meta: { requiresAuth: true } },
    { path: '/dashboard-stagiaires', component: DashboardStagiaires, name: 'dashboard-stagiaires',meta: { requiresAuth: true } },

    // ── Tools ────────────────────────────────────
    { path: '/calendar',        component: Calendar,        name: 'calendar',        meta: { requiresAuth: true } },
    { path: '/password-manager',component: PasswordManager, name: 'password-manager',meta: { requiresAuth: true } },

    // ── Années ───────────────────────────────────
    { path: '/annees-formation',      component: AnneesFormation,      name: 'annees-formation',      meta: { requiresAuth: true } },
    { path: '/annees-administrative', component: AnneesAdministrative, name: 'annees-administrative', meta: { requiresAuth: true } },
    { path: '/annee-calcul',          component: AnneeCalcul,          name: 'annee-calcul',          meta: { requiresAuth: true } },

    // ── Autres ───────────────────────────────────
    { path: '/listes',       component: Listes,       name: 'listes',       meta: { requiresAuth: true } },
    { path: '/roles',        component: Roles,        name: 'roles',        meta: { requiresAuth: true } },
    { path: '/activity-log', component: ActivityLog,  name: 'activity-log', meta: { requiresAuth: true } },

    // ── Formations ───────────────────────────────
    { path: '/specialites',       component: Specialites,      name: 'specialites',       meta: { requiresAuth: true } },
    { path: '/prog-specialites',  component: ProgSpecialites,  name: 'prog-specialites',  meta: { requiresAuth: true } },
    { path: '/themes',            component: Themes,           name: 'themes',            meta: { requiresAuth: true } },
    { path: '/prog-themes',       component: ProgThemes,       name: 'prog-themes',       meta: { requiresAuth: true } },
    { path: '/sessions',          component: Sessions,         name: 'sessions',          meta: { requiresAuth: true } },
    { path: '/formations',        component: Formations,       name: 'formations',        meta: { requiresAuth: true } },

    // ── 404 ──────────────────────────────────────
    { path: '/:pathMatch(.*)*', redirect: { name: 'error' } },
];

// ──────────────────────────────────────────────────────────────
// Création du routeur
// ──────────────────────────────────────────────────────────────
const router = createRouter({
    history: createWebHistory(),
    routes,
});

// ──────────────────────────────────────────────────────────────
// Garde de navigation globale
// ──────────────────────────────────────────────────────────────
router.beforeEach(async (to, from, next) => {
    const token         = localStorage.getItem('token');
    const userId        = localStorage.getItem('user_id');
    const isCentreRole  = localStorage.getItem('is_centre_role') === '1';
    const centreId      = localStorage.getItem('centre_id');
    const isAuthenticated = !!token;

    // Vérification si la base est vide (premier lancement)
    try {
        const { data } = await axios.get('/check-initial-setup');
        if (data.is_empty && to.name !== 'register') {
            // Nettoyage du localStorage avant redirection vers l'inscription initiale
            [
                'token', 'user_id', 'user_email', 'user_nom_fr', 'user_nom_ar',
                'user_prenom_fr', 'myRole', 'is_centre_role', 'centre_id'
            ].forEach(key => localStorage.removeItem(key));

            return next({ name: 'register' });
        }
    } catch (err) {
        console.error('Erreur check-initial-setup :', err.response?.data || err.message);
    }

    // Routes protégées
    if (to.meta.requiresAuth && !isAuthenticated) {
        return next({ name: 'login' });
    }

    // Routes réservées aux rôles "centre" → besoin d'un centre_id
    if (to.meta.requiresCentreRole && isCentreRole && !centreId) {
        return next({ name: 'access-denied' });
    }

    // Super-admin (is_centre_role = 0) peut accéder aux routes "requiresCentreRole"
    if (to.meta.requiresCentreRole && !isCentreRole) {
        return next();
    }

    // Si déjà authentifié → on empêche l'accès aux pages login/register
    if ((to.name === 'login' || to.name === 'register') && isAuthenticated) {
        const target = isCentreRole ? 'dashboard-centres' : 'dashboard';
        return next({ name: target });
    }

    next(); // tout est OK
});

export default router;
