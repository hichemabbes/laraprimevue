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
            v-model="form.module_id"
            :options="moduleOptions"
            optionLabel="titre_fr"
            optionValue="id"
            placeholder="Module"
            class="w-full mb-3"
        />
        <Dropdown
            v-model="form.phase_formation_id"
            :options="phaseOptions"
            optionLabel="id"
            optionValue="id"
            placeholder="Phase (optionnel)"
            class="w-full mb-3"
        />
        <Dropdown
            v-model="form.type_evaluation_id"
            :options="typeEvaluationOptions"
            optionLabel="nom"
            optionValue="id"
            placeholder="Type d'évaluation"
            class="w-full mb-3"
        />
        <Calendar
            v-model="form.date_evaluation"
            placeholder="Date évaluation"
            class="w-full mb-3"
        />
        <InputNumber
            v-model="form.note"
            placeholder="Note"
            mode="decimal"
            :minFractionDigits="2"
            class="w-full mb-3"
        />
        <Checkbox
            v-model="form.autorisee"
            :binary="true"
            label="Autorisée"
            class="mb-3"
        />
        <Checkbox
            v-model="form.elimine"
            :binary="true"
            label="Éliminé"
            class="mb-3"
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
import Checkbox from 'primevue/checkbox';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import axios from '@/axios.js';

export default {
    components: { Dropdown, Calendar, InputNumber, Checkbox, Textarea, Button },
    props: { evaluation: { type: Object, default: null } },
    emits: ['save'],
    setup(props, { emit }) {
        const form = ref({
            stagiaire_id: null,
            module_id: null,
            phase_formation_id: null,
            type_evaluation_id: null,
            date_evaluation: null,
            note: null,
            autorisee: true,
            elimine: false,
            observation: '',
        });

        const stagiaireOptions = ref([]);
        const moduleOptions = ref([]);
        const phaseOptions = ref([]);
        const typeEvaluationOptions = ref([]);

        onMounted(async () => {
            if (props.evaluation) Object.assign(form.value, props.evaluation);
            await fetchOptions();
        });

        const fetchOptions = async () => {
            const [stagiaires, modules, phases, types] = await Promise.all([
                axios.get('/api/stagiaires'),
                axios.get('/api/modules'),
                axios.get('/api/phases-formation'),
                axios.get('/api/options-listes?type=type_evaluation'),
            ]);
            stagiaireOptions.value = stagiaires.data;
            moduleOptions.value = modules.data;
            phaseOptions.value = phases.data;
            typeEvaluationOptions.value = types.data;
        };

        const save = () => {
            emit('save', form.value);
        };

        return {
            form,
            stagiaireOptions,
            moduleOptions,
            phaseOptions,
            typeEvaluationOptions,
            save,
        };
    },
};
</script>
