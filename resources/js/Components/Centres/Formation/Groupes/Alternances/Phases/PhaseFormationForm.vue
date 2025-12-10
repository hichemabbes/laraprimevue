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
            v-model="form.type_phase_id"
            :options="typePhaseOptions"
            optionLabel="nom"
            optionValue="id"
            placeholder="Type de phase"
            class="w-full mb-3"
        />
        <Calendar
            v-model="form.date_debut"
            placeholder="Date début"
            class="w-full mb-3"
        />
        <Calendar
            v-model="form.date_fin"
            placeholder="Date fin"
            class="w-full mb-3"
        />
        <InputNumber
            v-model="form.duree_heures"
            placeholder="Durée (heures)"
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
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import axios from '@/axios.js';

export default {
    components: { Dropdown, Calendar, InputNumber, Textarea, Button },
    props: { phase: { type: Object, default: null } },
    emits: ['save'],
    setup(props, { emit }) {
        const form = ref({
            groupe_id: null,
            type_phase_id: null,
            date_debut: null,
            date_fin: null,
            duree_heures: null,
            observation: '',
        });

        const groupeOptions = ref([]);
        const typePhaseOptions = ref([]);

        onMounted(async () => {
            if (props.phase) Object.assign(form.value, props.phase);
            await fetchOptions();
        });

        const fetchOptions = async () => {
            const [groupes, types] = await Promise.all([
                axios.get('/api/groupes'),
                axios.get('/api/options-listes?type=type_phase'),
            ]);
            groupeOptions.value = groupes.data;
            typePhaseOptions.value = types.data;
        };

        const save = () => {
            emit('save', form.value);
        };

        return { form, groupeOptions, typePhaseOptions, save };
    },
};
</script>
