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
            v-model="form.groupe_id"
            :options="groupeOptions"
            optionLabel="nom"
            optionValue="id"
            placeholder="Groupe"
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
            v-model="form.modules_repete"
            placeholder="Modules répétés (JSON)"
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
            groupe_id: null,
            date_redaction: null,
            resultat_id: null,
            modules_repete: '',
            observation: '',
        });

        const stagiaireOptions = ref([]);
        const groupeOptions = ref([]);
        const resultatOptions = ref([]);

        onMounted(async () => {
            if (props.pv) Object.assign(form.value, props.pv);
            await fetchOptions();
        });

        const fetchOptions = async () => {
            const [stagiaires, groupes, resultats] = await Promise.all([
                axios.get('/api/stagiaires'),
                axios.get('/api/groupes'),
                axios.get('/api/options-listes?type=resultat_controle'),
            ]);
            stagiaireOptions.value = stagiaires.data;
            groupeOptions.value = groupes.data;
            resultatOptions.value = resultats.data;
        };

        const save = () => {
            emit('save', form.value);
        };

        return { form, stagiaireOptions, groupeOptions, resultatOptions, save };
    },
};
</script>
