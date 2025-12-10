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
            v-model="form.emploi_temps_id"
            :options="emploiOptions"
            optionLabel="id"
            optionValue="id"
            placeholder="Emploi du temps"
            class="w-full mb-3"
        />
        <Checkbox
            v-model="form.present"
            :binary="true"
            label="Présent"
            class="mb-3"
        />
        <Textarea
            v-model="form.motif_absence"
            placeholder="Motif d'absence"
            rows="3"
            class="w-full mb-3"
            v-if="!form.present"
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
import Checkbox from 'primevue/checkbox';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import axios from '@/axios.js';

export default {
    components: { Dropdown, Checkbox, Textarea, Button },
    props: { presence: { type: Object, default: null } },
    emits: ['save'],
    setup(props, { emit }) {
        const form = ref({
            stagiaire_id: null,
            emploi_temps_id: null,
            present: false,
            motif_absence: '',
            observation: '',
        });

        const stagiaireOptions = ref([]);
        const emploiOptions = ref([]);

        onMounted(async () => {
            if (props.presence) Object.assign(form.value, props.presence);
            await fetchOptions();
        });

        const fetchOptions = async () => {
            const [stagiaires, emplois] = await Promise.all([
                axios.get('/api/stagiaires'),
                axios.get('/api/emplois-temps'),
            ]);
            stagiaireOptions.value = stagiaires.data;
            emploiOptions.value = emplois.data;
        };

        const save = () => {
            emit('save', form.value);
        };

        return { form, stagiaireOptions, emploiOptions, save };
    },
};
</script>
