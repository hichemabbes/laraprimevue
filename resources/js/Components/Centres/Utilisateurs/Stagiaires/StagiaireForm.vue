<template>
    <div>
        <InputText
            v-model="form.user_id"
            placeholder="ID Utilisateur"
            class="w-full mb-3"
        />
        <InputText
            v-model="form.num_extrait_naissance"
            placeholder="Numéro extrait naissance"
            class="w-full mb-3"
        />
        <Calendar
            v-model="form.date_cin"
            placeholder="Date CIN"
            class="w-full mb-3"
        />
        <InputText
            v-model="form.lieu_cin_fr"
            placeholder="Lieu CIN (FR)"
            class="w-full mb-3"
        />
        <InputText
            v-model="form.lieu_cin_ar"
            placeholder="Lieu CIN (AR)"
            class="w-full mb-3"
        />
        <Dropdown
            v-model="form.etat_civil_id"
            :options="etatCivilOptions"
            optionLabel="nom"
            optionValue="id"
            placeholder="État civil"
            class="w-full mb-3"
        />
        <InputNumber
            v-model="form.nombre_enfants"
            placeholder="Nombre d'enfants"
            class="w-full mb-3"
        />
        <InputText
            v-model="form.niveau_etude"
            placeholder="Niveau d'étude"
            class="w-full mb-3"
        />
        <InputText
            v-model="form.specialite_diplome_fr"
            placeholder="Spécialité diplôme (FR)"
            class="w-full mb-3"
        />
        <InputText
            v-model="form.specialite_diplome_ar"
            placeholder="Spécialité diplôme (AR)"
            class="w-full mb-3"
        />
        <Calendar
            v-model="form.date_inscription"
            placeholder="Date inscription"
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
            v-model="form.visites_minimum"
            placeholder="Visites minimum"
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
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import axios from '@/axios.js';

export default {
    components: {
        InputText,
        Dropdown,
        Calendar,
        InputNumber,
        Textarea,
        Button,
    },
    props: { stagiaire: { type: Object, default: null } },
    emits: ['save'],
    setup(props, { emit }) {
        const form = ref({
            user_id: null,
            num_extrait_naissance: '',
            date_cin: null,
            lieu_cin_fr: '',
            lieu_cin_ar: '',
            etat_civil_id: null,
            nombre_enfants: 0,
            niveau_etude: '',
            specialite_diplome_fr: '',
            specialite_diplome_ar: '',
            date_inscription: null,
            statut_id: null,
            visites_minimum: 0,
            observation: '',
        });

        const etatCivilOptions = ref([]);
        const statutOptions = ref([]);

        onMounted(async () => {
            if (props.stagiaire) Object.assign(form.value, props.stagiaire);
            await fetchOptions();
        });

        const fetchOptions = async () => {
            const [etatCivil, statuts] = await Promise.all([
                axios.get('/api/options-listes?type=etat_civil'),
                axios.get('/api/options-listes?type=statut_stagiaire'),
            ]);
            etatCivilOptions.value = etatCivil.data;
            statutOptions.value = statuts.data;
        };

        const save = () => {
            emit('save', form.value);
        };

        return { form, etatCivilOptions, statutOptions, save };
    },
};
</script>
