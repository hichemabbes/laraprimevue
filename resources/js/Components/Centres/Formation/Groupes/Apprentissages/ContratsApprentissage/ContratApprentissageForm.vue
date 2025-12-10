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
        <Dropdown
            v-model="form.groupe_id"
            :options="groupeOptions"
            optionLabel="nom"
            optionValue="id"
            placeholder="Groupe"
            class="w-full mb-3"
        />
        <InputText
            v-model="form.num_contrat"
            placeholder="Numéro contrat"
            class="w-full mb-3"
        />
        <Calendar
            v-model="form.date_debut"
            placeholder="Date début"
            class="w-full mb-3"
        />
        <Calendar
            v-model="form.date_fin_initiale"
            placeholder="Date fin initiale"
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
        <InputNumber
            v-model="form.heures_semaine_centre"
            placeholder="Heures/semaine centre"
            class="w-full mb-3"
        />
        <InputNumber
            v-model="form.salaire"
            placeholder="Salaire"
            mode="decimal"
            :minFractionDigits="2"
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
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import axios from '@/axios.js';

export default {
    components: {
        Dropdown,
        Calendar,
        InputText,
        InputNumber,
        Textarea,
        Button,
    },
    props: { contrat: { type: Object, default: null } },
    emits: ['save'],
    setup(props, { emit }) {
        const form = ref({
            stagiaire_id: null,
            entreprise_id: null,
            tuteur_id: null,
            groupe_id: null,
            num_contrat: '',
            date_debut: null,
            date_fin_initiale: null,
            statut_id: null,
            heures_semaine_centre: null,
            salaire: null,
            observation: '',
        });

        const stagiaireOptions = ref([]);
        const entrepriseOptions = ref([]);
        const tuteurOptions = ref([]);
        const groupeOptions = ref([]);
        const statutOptions = ref([]);

        onMounted(async () => {
            if (props.contrat) Object.assign(form.value, props.contrat);
            await fetchOptions();
        });

        const fetchOptions = async () => {
            const [stagiaires, entreprises, tuteurs, groupes, statuts] =
                await Promise.all([
                    axios.get('/api/stagiaires'),
                    axios.get('/api/entreprises'),
                    axios.get('/api/tuteurs'),
                    axios.get('/api/groupes'),
                    axios.get(
                        '/api/options-listes?type=statut_contrat_apprentissage'
                    ),
                ]);
            stagiaireOptions.value = stagiaires.data;
            entrepriseOptions.value = entreprises.data;
            tuteurOptions.value = tuteurs.data;
            groupeOptions.value = groupes.data;
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
            groupeOptions,
            statutOptions,
            save,
        };
    },
};
</script>
