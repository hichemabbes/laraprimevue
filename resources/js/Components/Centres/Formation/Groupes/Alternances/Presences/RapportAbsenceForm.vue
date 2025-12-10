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
            v-model="form.stagiaire_id"
            :options="stagiaireOptions"
            optionLabel="user.nom_fr"
            optionValue="id"
            placeholder="Stagiaire"
            class="w-full mb-3"
        />
        <InputNumber
            v-model="form.total_heures_absence"
            placeholder="Total heures absence"
            class="w-full mb-3"
        />
        <InputNumber
            v-model="form.taux_absence"
            placeholder="Taux d'absence (%)"
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
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import axios from '@/axios.js';

export default {
    components: { Dropdown, InputNumber, Textarea, Button },
    props: { rapport: { type: Object, default: null } },
    emits: ['save'],
    setup(props, { emit }) {
        const form = ref({
            phase_formation_id: null,
            stagiaire_id: null,
            total_heures_absence: 0,
            taux_absence: 0.0,
            observation: '',
        });

        const phaseOptions = ref([]);
        const stagiaireOptions = ref([]);

        onMounted(async () => {
            if (props.rapport) Object.assign(form.value, props.rapport);
            await fetchOptions();
        });

        const fetchOptions = async () => {
            const [phases, stagiaires] = await Promise.all([
                axios.get('/api/phases-formation'),
                axios.get('/api/stagiaires'),
            ]);
            phaseOptions.value = phases.data;
            stagiaireOptions.value = stagiaires.data;
        };

        const save = () => {
            emit('save', form.value);
        };

        return { form, phaseOptions, stagiaireOptions, save };
    },
};
</script>
