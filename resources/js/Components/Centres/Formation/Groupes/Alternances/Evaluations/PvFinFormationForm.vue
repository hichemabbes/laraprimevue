<template>
    <div>
        <Dropdown
            v-model="form.groupe_id"
            :options="groupeOptions"
            optionLabel="nom"
            optionValue="id"
            placeholder="Groupe"
            class="w-full mb-3"
        />
        <Dropdown
            v-model="form.stagiaire_id"
            :options="stagiaireOptions"
            optionLabel="user.nom_fr"
            optionValue="id"
            placeholder="Stagiaire"
            class="w-full mb-3"
        />
        <Calendar
            v-model="form.date_redaction"
            placeholder="Date rédaction"
            class="w-full mb-3"
        />
        <Dropdown
            v-model="form.resultat_id"
            :options="resultatOptions"
            optionLabel="nom"
            optionValue="id"
            placeholder="Résultat"
            class="w-full mb-3"
        />
        <Textarea
            v-model="form.modules_a_repeter"
            placeholder="Modules à répéter (JSON)"
            rows="3"
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
    props: { pv: { type: Object, default: null } },
    emits: ['save'],
    setup(props, { emit }) {
        const form = ref({
            groupe_id: null,
            stagiaire_id: null,
            date_redaction: null,
            resultat_id: null,
            modules_a_repeter: '',
            observation: '',
        });

        const groupeOptions = ref([]);
        const stagiaireOptions = ref([]);
        const resultatOptions = ref([]);

        onMounted(async () => {
            if (props.pv) Object.assign(form.value, props.pv);
            await fetchOptions();
        });

        const fetchOptions = async () => {
            const [groupes, stagiaires, resultats] = await Promise.all([
                axios.get('/api/groupes'),
                axios.get('/api/stagiaires'),
                axios.get('/api/options-listes?type=resultat_formation'),
            ]);
            groupeOptions.value = groupes.data;
            stagiaireOptions.value = stagiaires.data;
            resultatOptions.value = resultats.data;
        };

        const save = () => {
            emit('save', form.value);
        };

        return { form, groupeOptions, stagiaireOptions, resultatOptions, save };
    },
};
</script>
