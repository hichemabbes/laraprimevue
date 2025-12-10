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
            v-model="form.contrat_apprentissage_id"
            :options="contratOptions"
            optionLabel="num_contrat"
            optionValue="id"
            placeholder="Contrat"
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
        <Dropdown
            v-model="form.lieu_reunion_id"
            :options="centreOptions"
            optionLabel="nom_fr"
            optionValue="id"
            placeholder="Lieu réunion"
            class="w-full mb-3"
        />
        <Textarea
            v-model="form.membres_comite"
            placeholder="Membres comité (JSON)"
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
            stagiaire_id: null,
            contrat_apprentissage_id: null,
            date_redaction: null,
            resultat_id: null,
            lieu_reunion_id: null,
            membres_comite: '',
            observation: '',
        });

        const stagiaireOptions = ref([]);
        const contratOptions = ref([]);
        const resultatOptions = ref([]);
        const centreOptions = ref([]);

        onMounted(async () => {
            if (props.pv) Object.assign(form.value, props.pv);
            await fetchOptions();
        });

        const fetchOptions = async () => {
            const [stagiaires, contrats, resultats, centres] =
                await Promise.all([
                    axios.get('/api/stagiaires'),
                    axios.get('/api/contrats-apprentissage'),
                    axios.get(
                        '/api/options-listes?type=resultat_apprentissage'
                    ),
                    axios.get('/api/centres'),
                ]);
            stagiaireOptions.value = stagiaires.data;
            contratOptions.value = contrats.data;
            resultatOptions.value = resultats.data;
            centreOptions.value = centres.data;
        };

        const save = () => {
            emit('save', form.value);
        };

        return {
            form,
            stagiaireOptions,
            contratOptions,
            resultatOptions,
            centreOptions,
            save,
        };
    },
};
</script>
