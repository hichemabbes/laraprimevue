<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :style="{ width: '50vw' }"
        header="Gestion des modules"
        :modal="true"
    >
        <div>
            <div class="flex flex-column gap-3">
                <Button
                    label="Ajouter un module"
                    icon="pi pi-plus"
                    class="p-button-success"
                    @click="openModuleForm"
                />
                <DataTable :value="modules" :rows="10" :paginator="true">
                    <Column field="code" header="Code"></Column>
                    <Column
                        field="heures_theorique"
                        header="Heures Théoriques"
                    ></Column>
                    <Column
                        field="heures_pratiques"
                        header="Heures Pratiques"
                    ></Column>
                    <Column
                        field="heures_evaluation"
                        header="Heures d'Évaluation"
                    ></Column>
                    <Column
                        field="heures_totales"
                        header="Heures Totales"
                    ></Column>
                    <Column field="contenu" header="Contenu"></Column>
                    <Column field="observation" header="Observation"></Column>
                    <Column header="Actions">
                        <template #body="{ data }">
                            <Button
                                icon="pi pi-pencil"
                                class="p-button-rounded p-button-text p-button-success mr-2"
                                @click="editModule(data)"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-rounded p-button-text p-button-danger"
                                @click="deleteModule(data)"
                            />
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </Dialog>

    <Dialog
        v-model:visible="moduleFormVisible"
        :style="{ width: '40vw' }"
        header="Formulaire Module"
        :modal="true"
    >
        <div>
            <form @submit.prevent="saveModule">
                <div class="p-fluid">
                    <div class="p-field">
                        <label for="code">Code</label>
                        <InputText v-model="moduleForm.code" />
                    </div>
                    <div class="p-field">
                        <label for="heures_theorique">Heures Théoriques</label>
                        <InputNumber v-model="moduleForm.heures_theorique" />
                    </div>
                    <div class="p-field">
                        <label for="heures_pratiques">Heures Pratiques</label>
                        <InputNumber v-model="moduleForm.heures_pratiques" />
                    </div>
                    <div class="p-field">
                        <label for="heures_evaluation"
                            >Heures d'Évaluation</label
                        >
                        <InputNumber v-model="moduleForm.heures_evaluation" />
                    </div>
                    <div class="p-field">
                        <label for="heures_totales">Heures Totales</label>
                        <InputNumber v-model="moduleForm.heures_totales" />
                    </div>
                    <div class="p-field">
                        <label for="contenu">Fichier</label>
                        <FileUpload
                            mode="basic"
                            name="contenu"
                            :auto="true"
                            :customUpload="true"
                            @uploader="handleFileUpload"
                        />
                    </div>
                    <div class="p-field">
                        <label for="observation">Observation</label>
                        <Textarea v-model="moduleForm.observation" rows="3" />
                    </div>
                </div>
                <div class="flex justify-content-end mt-3">
                    <Button
                        label="Annuler"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="moduleFormVisible = false"
                    />
                    <Button
                        type="submit"
                        label="Enregistrer"
                        icon="pi pi-check"
                        class="p-button-success"
                    />
                </div>
            </form>
        </div>
    </Dialog>
</template>

<script>
import axios from 'axios';

export default {
    props: {
        specialiteId: {
            type: Number,
            required: true,
        },
        selectedAnnee: {
            type: Number,
            required: true,
        },
        visible: {
            type: Boolean,
            required: true,
        },
    },
    data() {
        return {
            modules: [],
            moduleFormVisible: false,
            moduleForm: {
                code: null,
                heures_theorique: null,
                heures_pratiques: null,
                heures_evaluation: null,
                heures_totales: null,
                contenu: null,
                observation: null,
            },
            moduleToEdit: null,
        };
    },
    methods: {
        async fetchModules() {
            try {
                const response = await axios.get('/api/modules', {
                    params: {
                        specialite_id: this.specialiteId,
                        annee_id: this.selectedAnnee,
                    },
                });
                this.modules = response.data;
            } catch (error) {
                console.error(
                    'Erreur lors de la récupération des modules :',
                    error
                );
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Impossible de charger les modules.',
                    life: 3000,
                });
            }
        },
        openModuleForm() {
            this.moduleForm = {
                code: null,
                heures_theorique: null,
                heures_pratiques: null,
                heures_evaluation: null,
                heures_totales: null,
                contenu: null,
                observation: null,
            };
            this.moduleToEdit = null;
            this.moduleFormVisible = true;
        },
        editModule(module) {
            this.moduleForm = { ...module };
            this.moduleToEdit = module;
            this.moduleFormVisible = true;
        },
        async saveModule() {
            try {
                const formData = new FormData();
                Object.keys(this.moduleForm).forEach((key) => {
                    if (this.moduleForm[key] !== null) {
                        formData.append(key, this.moduleForm[key]);
                    }
                });

                if (this.moduleToEdit) {
                    await axios.put(
                        `/api/modules/${this.moduleToEdit.id}`,
                        formData
                    );
                } else {
                    await axios.post('/api/modules', formData);
                }

                this.fetchModules();
                this.moduleFormVisible = false;
                this.$toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'ModuleGeneral enregistré avec succès.',
                    life: 3000,
                });
            } catch (error) {
                console.error(
                    "Erreur lors de l'enregistrement du module :",
                    error
                );
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Erreur lors de l'enregistrement du module.",
                    life: 3000,
                });
            }
        },
        async deleteModule(module) {
            try {
                await axios.delete(`/api/modules/${module.id}`);
                this.fetchModules();
                this.$toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'ModuleGeneral supprimé avec succès.',
                    life: 3000,
                });
            } catch (error) {
                console.error(
                    'Erreur lors de la suppression du module :',
                    error
                );
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors de la suppression du module.',
                    life: 3000,
                });
            }
        },
        handleFileUpload(event) {
            this.moduleForm.contenu = event.files[0];
        },
    },
    watch: {
        visible(newVal) {
            if (newVal) {
                this.fetchModules();
            }
        },
    },
};
</script>
