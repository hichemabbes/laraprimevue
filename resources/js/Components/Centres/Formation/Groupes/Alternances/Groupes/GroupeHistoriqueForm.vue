<template>
    <div>
        <InputText
            v-model="form.groupe_id"
            placeholder="ID Groupe"
            class="w-full mb-3"
        />
        <Dropdown
            v-model="form.action_id"
            :options="actionOptions"
            optionLabel="nom"
            optionValue="id"
            placeholder="Action"
            class="w-full mb-3"
        />
        <InputText
            v-model="form.groupe_source_id"
            placeholder="ID Groupe source"
            class="w-full mb-3"
        />
        <InputText
            v-model="form.groupe_destination_id"
            placeholder="ID Groupe destination"
            class="w-full mb-3"
        />
        <Calendar
            v-model="form.date_action"
            placeholder="Date action"
            class="w-full mb-3"
        />
        <Textarea
            v-model="form.stagiaires_affectes"
            placeholder="Stagiaires affectés (JSON)"
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
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import axios from '@/axios.js';

export default {
    components: { InputText, Dropdown, Calendar, Textarea, Button },
    props: { historique: { type: Object, default: null } },
    emits: ['save'],
    setup(props, { emit }) {
        const form = ref({
            groupe_id: null,
            action_id: null,
            groupe_source_id: null,
            groupe_destination_id: null,
            date_action: null,
            stagiaires_affectes: '',
            observation: '',
        });

        const actionOptions = ref([]);

        onMounted(async () => {
            if (props.historique) Object.assign(form.value, props.historique);
            const response = await axios.get(
                '/api/options-listes?type=action_groupe'
            );
            actionOptions.value = response.data;
        });

        const save = () => {
            emit('save', form.value);
        };

        return { form, actionOptions, save };
    },
};
</script>
