<template>
    <div>
        <InputText
            v-model="form.centre_id"
            placeholder="ID Centre"
            class="w-full mb-3"
        />
        <Dropdown
            v-model="form.specialite_id"
            :options="specialiteOptions"
            optionLabel="nom_fr"
            optionValue="id"
            placeholder="Spécialité"
            class="w-full mb-3"
        />
        <Dropdown
            v-model="form.programme_id"
            :options="programmeOptions"
            optionLabel="version"
            optionValue="id"
            placeholder="Programme"
            class="w-full mb-3"
        />
        <Dropdown
            v-model="form.annee_formation_id"
            :options="anneeOptions"
            optionLabel="intitule"
            optionValue="id"
            placeholder="Année de formation"
            class="w-full mb-3"
        />
        <InputText
            v-model="form.code_groupe"
            placeholder="Code groupe"
            class="w-full mb-3"
        />
        <InputText
            v-model="form.nom"
            placeholder="Nom du groupe"
            class="w-full mb-3"
        />
        <Dropdown
            v-model="form.mode_formation_id"
            :options="modeOptions"
            optionLabel="nom"
            optionValue="id"
            placeholder="Mode de formation"
            class="w-full mb-3"
        />
        <Dropdown
            v-model="form.mode_formation_detail_id"
            :options="modeDetailOptions"
            optionLabel="nom"
            optionValue="id"
            placeholder="Détail mode"
            class="w-full mb-3"
        />
        <InputNumber
            v-model="form.pourcentage_centre"
            placeholder="Pourcentage centre"
            class="w-full mb-3"
        />
        <InputNumber
            v-model="form.pourcentage_entreprise"
            placeholder="Pourcentage entreprise"
            class="w-full mb-3"
        />
        <InputNumber
            v-model="form.heures_semaine_centre"
            placeholder="Heures/semaine centre"
            class="w-full mb-3"
        />
        <Calendar
            v-model="form.date_debut_formation"
            placeholder="Date début formation"
            class="w-full mb-3"
        />
        <Calendar
            v-model="form.date_fin_formation"
            placeholder="Date fin formation"
            class="w-full mb-3"
        />
        <InputNumber
            v-model="form.duree_totale_heures"
            placeholder="Durée totale (heures)"
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
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import Calendar from 'primevue/calendar';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import axios from '@/axios.js';

export default {
    components: {
        InputText,
        Dropdown,
        InputNumber,
        Calendar,
        Textarea,
        Button,
    },
    props: { groupe: { type: Object, default: null } },
    emits: ['save'],
    setup(props, { emit }) {
        const form = ref({
            centre_id: null,
            specialite_id: null,
            programme_id: null,
            annee_formation_id: null,
            code_groupe: '',
            nom: '',
            mode_formation_id: null,
            mode_formation_detail_id: null,
            pourcentage_centre: 100,
            pourcentage_entreprise: 0,
            heures_semaine_centre: null,
            date_debut_formation: null,
            date_fin_formation: null,
            duree_totale_heures: null,
            statut_id: null,
            observation: '',
        });

        const specialiteOptions = ref([]);
        const programmeOptions = ref([]);
        const anneeOptions = ref([]);
        const modeOptions = ref([]);
        const modeDetailOptions = ref([]);
        const statutOptions = ref([]);

        onMounted(async () => {
            if (props.groupe) Object.assign(form.value, props.groupe);
            await fetchOptions();
        });

        const fetchOptions = async () => {
            const [
                specialites,
                programmes,
                annees,
                modes,
                modeDetails,
                statuts,
            ] = await Promise.all([
                axios.get('/api/specialites'),
                axios.get('/api/programmes'),
                axios.get('/api/annees-formation'),
                axios.get('/api/options-listes?type=mode_formation'),
                axios.get('/api/options-listes?type=mode_formation_detail'),
                axios.get('/api/options-listes?type=statut_groupe'),
            ]);
            specialiteOptions.value = specialites.data;
            programmeOptions.value = programmes.data;
            anneeOptions.value = annees.data;
            modeOptions.value = modes.data;
            modeDetailOptions.value = modeDetails.data;
            statutOptions.value = statuts.data;
        };

        const save = () => {
            emit('save', form.value);
        };

        return {
            form,
            specialiteOptions,
            programmeOptions,
            anneeOptions,
            modeOptions,
            modeDetailOptions,
            statutOptions,
            save,
        };
    },
};
</script>
