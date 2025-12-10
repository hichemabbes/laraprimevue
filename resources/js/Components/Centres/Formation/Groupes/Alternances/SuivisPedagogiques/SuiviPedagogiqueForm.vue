<template>
    <div>
        <Dropdown
            v-model="form.stagiaire_id"
            :options="stagiaireOptions"
            optionLabel="user.nom_fr"
            optionValue="id"
            placeholder="Stagiaire"
            class="w-full mb-3"
        />
        <Dropdown
            v-model="form.entreprise_id"
            :options="entrepriseOptions"
            optionLabel="nom_fr"
            optionValue="id"
            placeholder="Entreprise"
            class="w-full mb-3"
        />
        <Dropdown
            v-model="form.tuteur_id"
            :options="tuteurOptions"
            optionLabel="nom_fr"
            optionValue="id"
            placeholder="Tuteur"
            class="w-full mb-3"
        />
        <Calendar
            v-model="form.date_suivi"
            placeholder="Date suivi"
            class="w-full mb-3"
        />
        <Dropdown
            v-model="form.type_suivi_id"
            :options="typeSuiviOptions"
            optionLabel="nom"
            optionValue="id"
            placeholder="Type de suivi"
            class="w-full mb-3"
        />
        <Textarea
            v-model="form.rapport"
            placeholder="Rapport"
            rows="3"
            class="w-full mb-3"
        />
        <Dropdown
            v-model="form.statut_id"
            :options="statutOptions"
            optionLabel="nom"
            optionValue="id"
            placeholder="Statut"
            class="w-full mb-3"
        />
        <Textarea
            v-model="form.observation"
            placeholder="Observation"
            rows="3"
            class="w-full mb-3"
        />
        <Button label="Enregistrer" @click="save" class="p-button-success" />
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import axios from '@/axios.js';

export default {
    components: { Dropdown, Calendar, Textarea, Button },
    props: { suivi: { type: Object, default: null } },
    emits: ['save'],
    setup(props, { emit }) {
        const form = ref({
            stagiaire_id: null,
            entreprise_id: null,
            tuteur_id: null,
            date_suivi: null,
            type_suivi_id: null,
            rapport: '',
            statut_id: null,
            observation: '',
        });

        const stagiaireOptions = ref([]);
        const entrepriseOptions = ref([]);
        const tuteurOptions = ref([]);
        const typeSuiviOptions = ref([]);
        const statutOptions = ref([]);

        onMounted(async () => {
            if (props.suivi) Object.assign(form.value, props.suivi);
            await fetchOptions();
        });

        const fetchOptions = async () => {
            const [stagiaires, entreprises, tuteurs, types, statuts] =
                await Promise.all([
                    axios.get('/api/stagiaires'),
                    axios.get('/api/entreprises'),
                    axios.get('/api/tuteurs'),
                    axios.get('/api/options-listes?type=type_suivi'),
                    axios.get('/api/options-listes?type=statut_suivi'),
                ]);
            stagiaireOptions.value = stagiaires.data;
            entrepriseOptions.value = entreprises.data;
            tuteurOptions.value = tuteurs.data;
            typeSuiviOptions.value = types.data;
            statutOptions.value = statuts.data;
        };

        const save = () => {
            emit('save', form.value);
        };

        return {
            form,
            stagiaireOptions,
            entrepriseOptions,
            tuteurOptions,
            typeSuiviOptions,
            statutOptions,
            save,
        };
    },
};
</script>
