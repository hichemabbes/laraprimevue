<template>
    <div>
        <Dropdown
            v-model="form.personnel_id"
            :options="personnelOptions"
            optionLabel="user.nom_fr"
            optionValue="id"
            placeholder="Formateur"
            class="w-full mb-3"
        />
        <Dropdown
            v-model="form.centre_id"
            :options="centreOptions"
            optionLabel="nom_fr"
            optionValue="id"
            placeholder="Centre"
            class="w-full mb-3"
        />
        <Calendar
            v-model="form.date_mission"
            placeholder="Date mission"
            class="w-full mb-3"
        />
        <Textarea
            v-model="form.objectif"
            placeholder="Objectif"
            rows="3"
            class="w-full mb-3"
        />
        <InputText
            v-model="form.document_path"
            placeholder="Chemin document"
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
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import axios from '@/axios.js';

export default {
    components: { Dropdown, Calendar, InputText, Textarea, Button },
    props: { ordre: { type: Object, default: null } },
    emits: ['save'],
    setup(props, { emit }) {
        const form = ref({
            personnel_id: null,
            centre_id: null,
            date_mission: null,
            objectif: '',
            document_path: '',
            observation: '',
        });

        const personnelOptions = ref([]);
        const centreOptions = ref([]);

        onMounted(async () => {
            if (props.ordre) Object.assign(form.value, props.ordre);
            await fetchOptions();
        });

        const fetchOptions = async () => {
            const [personnels, centres] = await Promise.all([
                axios.get('/api/personnels'),
                axios.get('/api/centres'),
            ]);
            personnelOptions.value = personnels.data;
            centreOptions.value = centres.data;
        };

        const save = () => {
            emit('save', form.value);
        };

        return { form, personnelOptions, centreOptions, save };
    },
};
</script>
