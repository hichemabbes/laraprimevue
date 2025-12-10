<template>
    <div>
        <Dropdown
            v-model="form.phase_formation_id"
            :options="phaseOptions"
            optionLabel="id"
            optionValue="id"
            placeholder="Phase de formation"
            class="w-full mb-3"
        />
        <Dropdown
            v-model="form.module_id"
            :options="moduleOptions"
            optionLabel="titre_fr"
            optionValue="id"
            placeholder="Module"
            class="w-full mb-3"
        />
        <Dropdown
            v-model="form.personnel_id"
            :options="personnelOptions"
            optionLabel="user.nom_fr"
            optionValue="id"
            placeholder="Formateur"
            class="w-full mb-3"
        />
        <Dropdown
            v-model="form.type_seance_id"
            :options="typeSeanceOptions"
            optionLabel="nom"
            optionValue="id"
            placeholder="Type de séance"
            class="w-full mb-3"
        />
        <Calendar
            v-model="form.date_heure_debut"
            showTime
            placeholder="Date et heure début"
            class="w-full mb-3"
        />
        <Calendar
            v-model="form.date_heure_fin"
            showTime
            placeholder="Date et heure fin"
            class="w-full mb-3"
        />
        <InputText v-model="form.lieu" placeholder="Lieu" class="w-full mb-3" />
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
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import axios from '@/axios.js';

export default {
    components: { Dropdown, Calendar, InputText, Textarea, Button },
    props: { emploi: { type: Object, default: null } },
    emits: ['save'],
    setup(props, { emit }) {
        const form = ref({
            phase_formation_id: null,
            module_id: null,
            personnel_id: null,
            type_seance_id: null,
            date_heure_debut: null,
            date_heure_fin: null,
            lieu: '',
            observation: '',
        });

        const phaseOptions = ref([]);
        const moduleOptions = ref([]);
        const personnelOptions = ref([]);
        const typeSeanceOptions = ref([]);

        onMounted(async () => {
            if (props.emploi) Object.assign(form.value, props.emploi);
            await fetchOptions();
        });

        const fetchOptions = async () => {
            const [phases, modules, personnels, types] = await Promise.all([
                axios.get('/api/phases-formation'),
                axios.get('/api/modules'),
                axios.get('/api/personnels'),
                axios.get('/api/options-listes?type=type_seance'),
            ]);
            phaseOptions.value = phases.data;
            moduleOptions.value = modules.data;
            personnelOptions.value = personnels.data;
            typeSeanceOptions.value = types.data;
        };

        const save = () => {
            emit('save', form.value);
        };

        return {
            form,
            phaseOptions,
            moduleOptions,
            personnelOptions,
            typeSeanceOptions,
            save,
        };
    },
};
</script>
