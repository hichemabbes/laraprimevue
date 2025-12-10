<template>
    <div>
        <!-- Le composant Dialog de PrimeVue avec la gestion de la visibilité -->
        <Dialog
            v-model:visible="visible"
            :style="{ width: '50rem' }"
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
            modal
            :pt="{
                root: 'border-none',
                mask: {
                    style: 'backdrop-filter: blur(2px)',
                },
            }"
        >
            <template #container="{ closeCallback }">
                <div class="relative">
                    <!-- Bouton de fermeture en haut à droite -->
                    <i
                        class="pi pi-times absolute cursor-pointer text-primary-50 text-xl"
                        style="top: 10px; right: 10px"
                        @click="closeCallback"
                    ></i>

                    <div
                        class="flex flex-column px-5 py-5 gap-4"
                        style="
                            border-radius: 12px;
                            background-image: radial-gradient(
                                circle at left top,
                                var(--primary-400),
                                var(--primary-700)
                            );
                        "
                    >
                        <!-- Ajout du titre -->
                        <h3
                            class="text-center text-primary-50 font-bold text-2xl mt-3"
                        >
                            Create New Event wizard
                        </h3>

                        hhhhhh

                        <Button
                            label="Cancel"
                            severity="success"
                            @click="closeCallback"
                            text
                            class="p-3 w-full text-primary-50 border-1 border-white-alpha-30 hover:bg-white-alpha-10"
                        ></Button>
                        <Button
                            label="Save"
                            @click="saveEvent"
                            text
                            class="p-3 w-full text-primary-50 border-1 border-white-alpha-30 hover:bg-white-alpha-10"
                        ></Button>
                    </div>
                </div>
            </template>
        </Dialog>
    </div>
</template>

<script>
export default {
    data() {
        return {
            visible: false, // Contrôle de la visibilité du popup
            startdate: null, // Valeur pour la date de début
            enddate: null, // Valeur pour la date de fin
        };
    },
    methods: {
        open() {
            this.visible = true;
        },
        close() {
            this.visible = false;
        },
        // Méthode pour sauvegarder l'événement
        saveEvent() {
            console.log('Événement sauvegardé !');
            // Ajoutez ici la logique pour sauvegarder l'événement
            this.close(); // Ferme le popup après l'enregistrement
        },
    },
};
</script>

<style scoped>
/* Style spécifique à votre popup, ajustez selon vos besoins */
</style>
