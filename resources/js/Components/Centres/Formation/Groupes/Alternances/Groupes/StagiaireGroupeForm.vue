<template>
    <div>
        <InputText
            v-model="form.stagiaire_id"
            placeholder="ID Stagiaire"
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
        <Dropdown
            v-model="form.statut_id"
            :options="statutOptions"
            optionLabel="nom"
            optionValue="id"
            placeholder="Statut"
            class="w-full mb-3"
        />
        <InputText
            v-model="form.groupe_precedent_id"
            placeholder="ID Groupe précédent"
            class="w-full mb-3"
        />
        <Calendar
            v-model="form.date_transfert"
            placeholder="Date transfert"
            class="w-full mb-3"
        />
        <Textarea
            v-model="form.motif_transfert"
            placeholder="Motif transfert"
            rows="3"
            class="w-full mb-3"
        />
        <Textarea
            v-model="form.modules_a_repeter"
            placeholder="Modules à répéter (JSON)"
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
    props: { stagiaireGroupe: { type: Object, default: null } },
    emits: ['save'],
    setup(props, { emit }) {
        const form = ref({
            stagiaire_id: null,
            groupe_id: null,
            date_debut: null,
            date_fin: null,
            statut_id: null,
            groupe_precedent_id: null,
            date_transfert: null,
            motif_transfert: '',
            modules_a_repeter: '',
            observation: '',
        });

        const groupeOptions = ref([]);
        const statutOptions = ref([]);

        onMounted(async () => {
            if (props.stagiaireGroupe)
                Object.assign(form.value, props.stagiaireGroupe);
            await fetchOptions();
        });

        const fetchOptions = async () => {
            const [groupes, statuts] = await Promise.all([
                axios.get('/api/groupes'),
                axios.get('/api/options-listes?type=statut_stagiaire_groupe'),
            ]);
            groupeOptions.value = groupes.data;
            statutOptions.value = statuts.data;
        };

        const save = () => {
            emit('save', form.value);
        };

        return { form, groupeOptions, statutOptions, save };
    },
};
</script>
