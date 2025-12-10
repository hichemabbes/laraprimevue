import axios from 'axios';

const instance = axios.create({
    baseURL: 'http://127.0.0.1:8000',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
    withCredentials: true,
});

instance.interceptors.request.use(
    async (config) => {
        // Ne pas ajouter de token pour les routes publiques
        if (config.url.includes('/sanctum/csrf-cookie') || config.url.includes('/api/login')) {
            return config;
        }

        try {
            await instance.get('/sanctum/csrf-cookie');
        } catch (error) {
            console.warn('Erreur lors de la récupération du CSRF cookie:', error);
        }

        const userId = typeof window !== 'undefined' ? localStorage.getItem('user_id') : null;
        const centreId = typeof window !== 'undefined' ? localStorage.getItem('centre_id') : null;
        const token = typeof window !== 'undefined' ? localStorage.getItem('token') : null;

        if (userId) {
            config.headers['X-User-ID'] = userId;
        }
        if (centreId) {
            config.headers['X-Centre-ID'] = centreId;
        }
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        } else {
            console.warn(`Aucun token trouvé dans localStorage pour ${config.url}`);
            // Ne pas rejeter la requête, laisser le serveur gérer
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

instance.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            console.log('Erreur 401 : Déconnexion et redirection vers login');
            [
                'token',
                'user_id',
                'user_email',
                'user_nom_fr',
                'user_nom_ar',
                'user_prenom_fr',
                'myRole',
                'is_centre_role',
                'centre_id',
            ].forEach((key) => localStorage.removeItem(key));
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

export default instance;
