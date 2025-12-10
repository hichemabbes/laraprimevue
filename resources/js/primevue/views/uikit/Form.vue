<template>
    <div class="flex h-screen bg-gray-900 text-white">
        <!-- Header -->

        <!-- Partie gauche (Sidebar des champs et design) -->
        <div class="w-1/4 bg-gray-800 p-4 border-r border-gray-700 mt-16">
            <!-- Onglets -->
            <TabView>
                <TabPanel header="Fields">
                    <!-- Champs prédéfinis du système -->
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold mb-2">
                            System Fields
                        </h4>
                        <div class="space-y-2">
                            <div
                                v-for="field in systemFields"
                                :key="field.id"
                                class="p-2 bg-gray-700 border border-gray-600 rounded cursor-move"
                                draggable="true"
                                @dragstart="onDragStart($event, field)"
                            >
                                <i :class="field.icon" class="mr-2"></i>
                                {{ field.label }}
                            </div>
                        </div>
                    </div>

                    <!-- Champs personnalisés -->
                    <div>
                        <h4 class="text-lg font-semibold mb-2">
                            Custom Fields
                        </h4>
                        <div class="space-y-2">
                            <div
                                v-for="field in customFields"
                                :key="field.id"
                                class="p-2 bg-gray-700 border border-gray-600 rounded cursor-move"
                                draggable="true"
                                @dragstart="onDragStart($event, field)"
                            >
                                <i :class="field.icon" class="mr-2"></i>
                                {{ field.label }}
                            </div>
                        </div>
                    </div>
                </TabPanel>

                <TabPanel header="Design">
                    <!-- Personnalisation du design -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Background Color</label
                            >
                            <input
                                type="color"
                                v-model="formDesign.backgroundColor"
                                class="w-full"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Background Opacity</label
                            >
                            <InputNumber
                                v-model="formDesign.backgroundOpacity"
                                :min="0"
                                :max="1"
                                :step="0.1"
                                class="w-full"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Font Family</label
                            >
                            <Dropdown
                                v-model="formDesign.fontFamily"
                                :options="[
                                    'Arial',
                                    'Helvetica',
                                    'Times New Roman',
                                    'Courier New',
                                    'Verdana',
                                ]"
                                class="w-full"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Text Color</label
                            >
                            <input
                                type="color"
                                v-model="formDesign.textColor"
                                class="w-full"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Button Background</label
                            >
                            <input
                                type="color"
                                v-model="formDesign.buttonBackground"
                                class="w-full"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Button Text Color</label
                            >
                            <input
                                type="color"
                                v-model="formDesign.buttonTextColor"
                                class="w-full"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Container Background</label
                            >
                            <input
                                type="color"
                                v-model="formDesign.containerBackground"
                                class="w-full"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Container Opacity</label
                            >
                            <InputNumber
                                v-model="formDesign.containerOpacity"
                                :min="0"
                                :max="1"
                                :step="0.1"
                                class="w-full"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Container Width (px)</label
                            >
                            <InputNumber
                                v-model="formDesign.containerWidth"
                                :min="100"
                                :max="1200"
                                class="w-full"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Left Padding (px)</label
                            >
                            <InputNumber
                                v-model="formDesign.leftPadding"
                                :min="0"
                                :max="100"
                                class="w-full"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Right Padding (px)</label
                            >
                            <InputNumber
                                v-model="formDesign.rightPadding"
                                :min="0"
                                :max="100"
                                class="w-full"
                            />
                        </div>
                    </div>
                </TabPanel>
            </TabView>
        </div>

        <!-- Partie centrale (Zone de conception du formulaire) -->
        <div class="w-1/2 p-4 mt-16">
            <h3 class="text-xl font-bold mb-4">Form Design Area</h3>

            <!-- Zone de drag-and-drop -->
            <div
                ref="formArea"
                class="border-2 border-dashed border-gray-600 rounded-lg p-4"
                :style="{
                    backgroundColor: `rgba(${hexToRgb(formDesign.backgroundColor)}, ${formDesign.backgroundOpacity})`,
                    fontFamily: formDesign.fontFamily,
                    color: formDesign.textColor,
                }"
                @drop="onDrop"
                @dragover.prevent
                @dragenter.prevent
            >
                <!-- Conteneurs -->
                <div
                    v-for="(container, containerIndex) in formContainers"
                    :key="containerIndex"
                    class="mb-6 p-4 rounded-lg"
                    :style="{
                        backgroundColor: `rgba(${hexToRgb(formDesign.containerBackground)}, ${formDesign.containerOpacity})`,
                        width: `${formDesign.containerWidth}px`,
                        paddingLeft: `${formDesign.leftPadding}px`,
                        paddingRight: `${formDesign.rightPadding}px`,
                    }"
                >
                    <!-- Blocs dans le conteneur -->
                    <div
                        v-for="(block, blockIndex) in container.blocks"
                        :key="blockIndex"
                        class="mb-4 p-4 rounded-lg"
                    >
                        <!-- Champs dans le bloc -->
                        <div
                            v-for="(field, fieldIndex) in block.fields"
                            :key="fieldIndex"
                            class="mb-2 p-2 rounded cursor-move"
                            draggable="true"
                            @dragstart="onDragStart($event, field)"
                            @click="
                                selectElement(
                                    'field',
                                    containerIndex,
                                    blockIndex,
                                    fieldIndex
                                )
                            "
                            :style="{
                                backgroundColor: formDesign.buttonBackground,
                                color: formDesign.buttonTextColor,
                            }"
                        >
                            <i :class="field.icon" class="mr-2"></i>
                            {{ field.label }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Partie droite (Configuration des éléments) -->
        <div class="w-1/4 bg-gray-800 p-4 border-l border-gray-700 mt-16">
            <h3 class="text-xl font-bold mb-4">Configuration</h3>

            <!-- Configuration des éléments sélectionnés -->
            <div v-if="selectedElement">
                <div v-if="selectedElement.type === 'field'">
                    <h4 class="text-lg font-semibold mb-2">
                        Field Configuration
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Label</label
                            >
                            <InputText
                                v-model="selectedElement.data.label"
                                class="w-full"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Description</label
                            >
                            <InputText
                                v-model="selectedElement.data.description"
                                class="w-full"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Required</label
                            >
                            <Checkbox v-model="selectedElement.data.required" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Hidden</label
                            >
                            <Checkbox v-model="selectedElement.data.hidden" />
                        </div>
                    </div>
                </div>

                <div v-else-if="selectedElement.type === 'block'">
                    <h4 class="text-lg font-semibold mb-2">
                        Block Configuration
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Title</label
                            >
                            <InputText
                                v-model="selectedElement.data.title"
                                class="w-full"
                            />
                        </div>
                    </div>
                </div>

                <div v-else-if="selectedElement.type === 'container'">
                    <h4 class="text-lg font-semibold mb-2">
                        Container Configuration
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Title</label
                            >
                            <InputText
                                v-model="selectedElement.data.title"
                                class="w-full"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import InputNumber from 'primevue/inputnumber';

export default {
    components: {
        InputText,
        Checkbox,
        Dropdown,
        Button,
        TabView,
        TabPanel,
        InputNumber,
    },
    setup() {
        // Type de formulaire (Landing ou Wizard)
        const formType = ref('Landing');

        // Champs prédéfinis du système
        const systemFields = ref([
            {
                id: 'firstname',
                label: 'First Name',
                icon: 'pi pi-user',
                type: 'text',
            },
            {
                id: 'lastname',
                label: 'Last Name',
                icon: 'pi pi-user',
                type: 'text',
            },
            {
                id: 'email',
                label: 'Email',
                icon: 'pi pi-envelope',
                type: 'email',
            },
            {
                id: 'organization',
                label: 'Organization',
                icon: 'pi pi-building',
                type: 'text',
            },
            {
                id: 'jobtitle',
                label: 'Job Title',
                icon: 'pi pi-briefcase',
                type: 'text',
            },
            { id: 'phone', label: 'Phone', icon: 'pi pi-phone', type: 'tel' },
            {
                id: 'secondary-email',
                label: 'Secondary Email',
                icon: 'pi pi-envelope',
                type: 'email',
            },
        ]);

        // Champs personnalisés
        const customFields = ref([
            {
                id: 'custom-text',
                label: 'Custom Text',
                icon: 'pi pi-pencil',
                type: 'text',
            },
            {
                id: 'custom-number',
                label: 'Custom Number',
                icon: 'pi pi-hashtag',
                type: 'number',
            },
            {
                id: 'custom-date',
                label: 'Custom Date',
                icon: 'pi pi-calendar',
                type: 'date',
            },
        ]);

        // Conteneurs du formulaire
        const formContainers = ref([
            {
                title: 'Container 1',
                blocks: [
                    {
                        title: 'Block 1',
                        fields: [],
                    },
                ],
            },
        ]);

        // Élément sélectionné
        const selectedElement = ref(null);

        // Design du formulaire
        const formDesign = ref({
            backgroundColor: '#ffffff',
            backgroundOpacity: 1,
            fontFamily: 'Arial',
            textColor: '#000000',
            buttonBackground: '#007bff',
            buttonTextColor: '#ffffff',
            containerBackground: '#f8f9fa',
            containerOpacity: 1,
            containerWidth: 650,
            leftPadding: 10,
            rightPadding: 10,
        });

        // Convertir une couleur hexadécimale en RGB
        const hexToRgb = (hex) => {
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            return `${r}, ${g}, ${b}`;
        };

        // Gestion du drag-and-drop
        const onDragStart = (event, field) => {
            event.dataTransfer.setData('text/plain', JSON.stringify(field));
        };

        const onDrop = (event) => {
            const data = JSON.parse(event.dataTransfer.getData('text/plain'));
            const containerIndex = 0; // Par défaut, ajouter au premier conteneur
            const blockIndex = 0; // Par défaut, ajouter au premier bloc

            formContainers.value[containerIndex].blocks[blockIndex].fields.push(
                {
                    ...data,
                    required: false,
                    hidden: false,
                    description: '',
                }
            );
        };

        // Sélection d'un élément
        const selectElement = (
            type,
            containerIndex,
            blockIndex,
            fieldIndex
        ) => {
            if (type === 'field') {
                selectedElement.value = {
                    type: 'field',
                    data: formContainers.value[containerIndex].blocks[
                        blockIndex
                    ].fields[fieldIndex],
                };
            } else if (type === 'block') {
                selectedElement.value = {
                    type: 'block',
                    data: formContainers.value[containerIndex].blocks[
                        blockIndex
                    ],
                };
            } else if (type === 'container') {
                selectedElement.value = {
                    type: 'container',
                    data: formContainers.value[containerIndex],
                };
            }
        };

        // Prévisualiser le formulaire
        const previewForm = () => {
            alert('Preview Form');
        };

        // Sauvegarder le formulaire
        const saveForm = () => {
            alert('Form Saved');
        };

        return {
            formType,
            systemFields,
            customFields,
            formContainers,
            selectedElement,
            formDesign,
            hexToRgb,
            onDragStart,
            onDrop,
            selectElement,
            previewForm,
            saveForm,
        };
    },
};
</script>

<style scoped>
/* Styles spécifiques à la page Form Builder */
.cursor-move {
    cursor: move;
}
</style>
