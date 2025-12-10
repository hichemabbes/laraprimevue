<template>
    <Dialog
        :visible="modelValue"
        @update:visible="$emit('update:modelValue', $event)"
        header="Changer la Photo de Profil"
        :modal="true"
        :style="{ width: '30rem' }"
        :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
    >
        <div class="flex flex-column align-items-center gap-3">
            <div v-if="previewImage" class="cropper-container">
                <Cropper
                    :src="previewImage"
                    :stencil-props="{
                        aspectRatio: 1,
                        movable: true,
                        resizable: true,
                        handlers: {},
                        handlersWrapperClass: 'cropper-handlers-wrapper',
                        resizeHandlers: {
                            enabled: true,
                            positions: ['bottom-right'],
                        },
                        style: {
                            border: '2px solid #3b82f6',
                            borderRadius: '50%',
                            background: 'transparent',
                        },
                        initialSize: { width: 200, height: 200 },
                    }"
                    :canvas="{ minWidth: 100, minHeight: 100 }"
                    @change="onCropChange"
                    @ready="onCropperReady"
                />
            </div>
            <div v-else class="flex flex-column align-items-center gap-2">
                <Avatar
                    :image="user.photo || '/default-avatar.png'"
                    shape="circle"
                    size="xlarge"
                />
                <small class="text-error" v-if="imageError">{{
                    imageError
                }}</small>
            </div>
            <FileUpload
                mode="basic"
                accept="image/*"
                :maxFileSize="2000000"
                chooseLabel="Choisir une Image"
                @select="onAvatarSelect"
                class="w-full"
            />
            <small class="text-500">Taille max: 2MB (JPG, PNG)</small>
        </div>
        <template #footer>
            <Button label="Annuler" icon="pi pi-times" text @click="cancel" />
            <Button
                label="Enregistrer"
                icon="pi pi-check"
                :disabled="!selectedFile"
                :loading="uploading"
                @click="save"
            />
        </template>
    </Dialog>
</template>

<script>
import { ref } from 'vue';
import { useToast } from 'primevue/usetoast';

export default {
    props: {
        modelValue: Boolean,
        user: Object,
        uploading: Boolean,
    },
    emits: ['update:modelValue', 'upload', 'cancel'],
    setup(props, { emit }) {
        const toast = useToast();
        const selectedFile = ref(null);
        const previewImage = ref(null);
        const croppedImage = ref(null);
        const imageError = ref(null);

        const onAvatarSelect = (event) => {
            try {
                imageError.value = null;
                const file = event.files[0];

                if (!file) {
                    throw new Error('Aucun fichier sélectionné');
                }

                if (file.size > 2000000) {
                    imageError.value =
                        "La taille de l'image ne doit pas dépasser 2 Mo";
                    return;
                }

                if (!file.type.match('image/(jpg|jpeg|png)')) {
                    imageError.value =
                        'Veuillez sélectionner une image valide (JPG, PNG)';
                    return;
                }

                selectedFile.value = file;
                previewImage.value = URL.createObjectURL(file);
            } catch (err) {
                console.error('Error selecting avatar:', err);
                imageError.value =
                    err.message || "Erreur lors de la sélection de l'image";
                resetFile();
            }
        };

        const onCropChange = ({ canvas }) => {
            try {
                croppedImage.value = canvas.toDataURL('image/png');
            } catch (err) {
                console.error('Error cropping image:', err);
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "Erreur lors du recadrage de l'image",
                    life: 5000,
                });
            }
        };

        const onCropperReady = () => {
            console.log('Cropper ready');
        };

        const save = () => {
            if (!selectedFile.value) return;
            emit('upload', croppedImage.value || previewImage.value);
        };

        const cancel = () => {
            resetFile();
            emit('cancel');
        };

        const resetFile = () => {
            if (previewImage.value) {
                URL.revokeObjectURL(previewImage.value);
            }
            selectedFile.value = null;
            previewImage.value = null;
            croppedImage.value = null;
            imageError.value = null;
        };

        return {
            selectedFile,
            previewImage,
            croppedImage,
            imageError,
            onAvatarSelect,
            onCropChange,
            onCropperReady,
            save,
            cancel,
        };
    },
};
</script>

<style scoped>
.cropper-container {
    width: 300px;
    height: 300px;
    position: relative;
    overflow: hidden;
    background: transparent;
}

.text-error {
    color: var(--red-500);
}

:deep(.cropper-viewport) {
    background: transparent !important;
    border: none !important;
}

:deep(.cropper-face) {
    background: transparent !important;
}

:deep(.cropper-handler.bottom-right) {
    display: block !important;
    background: #3b82f6 !important;
    border: 2px solid #ffffff !important;
    width: 16px !important;
    height: 16px !important;
    border-radius: 50%;
    cursor: se-resize;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
}

@media (max-width: 640px) {
    .cropper-container {
        width: 250px;
        height: 250px;
    }
}
</style>
