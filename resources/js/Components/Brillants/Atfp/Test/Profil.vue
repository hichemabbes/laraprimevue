<template>
    <div class="emploi-temps-app p-4">
        <!-- Barre d'outils principale -->
        <Toolbar class="mb-4 shadow-2">
            <template #start>
                <Breadcrumb :home="breadcrumbHome" :model="breadcrumbItems" />
            </template>
            <template #end>
                <div class="flex gap-2">
                    <Button
                        label="Nouvel Emploi"
                        icon="pi pi-plus"
                        class="p-button-success"
                        @click="openNouvelEmploiDialog"
                    />
                    <Button
                        label="Gérer Contraintes"
                        icon="pi pi-sliders-h"
                        class="p-button-info"
                        @click="openContraintesDialog"
                    />
                    <Button
                        label="Exporter PDF"
                        icon="pi pi-file-pdf"
                        class="p-button-help"
                        @click="exportToPDF"
                    />
                </div>
            </template>
        </Toolbar>

        <!-- Sélecteurs de contexte -->
        <div class="card mb-4 p-4 shadow-2">
            <div class="p-fluid grid">
                <div class="field col-12 md:col-4">
                    <label for="groupe-select">Groupe</label>
                    <Dropdown
                        id="groupe-select"
                        v-model="selectedGroupe"
                        :options="groupes"
                        optionLabel="nom"
                        placeholder="Sélectionnez un groupe"
                        class="w-full"
                        @change="onGroupeChange"
                    >
                        <template #value="slotProps">
                            <div v-if="slotProps.value" class="flex align-items-center">
                                <Avatar :label="slotProps.value.nom.charAt(0)" shape="circle" class="mr-2" />
                                <div>{{ slotProps.value.nom }}</div>
                            </div>
                            <span v-else>{{ slotProps.placeholder }}</span>
                        </template>
                        <template #option="slotProps">
                            <div class="flex align-items-center">
                                <Avatar :label="slotProps.option.nom.charAt(0)" shape="circle" class="mr-2" />
                                <div>{{ slotProps.option.nom }}</div>
                            </div>
                        </template>
                    </Dropdown>
                </div>

                <div class="field col-12 md:col-4">
                    <label for="phase-select">Phase de Formation</label>
                    <Dropdown
                        id="phase-select"
                        v-model="selectedPhase"
                        :options="phasesFiltrees"
                        optionLabel="nom"
                        placeholder="Sélectionnez une phase"
                        class="w-full"
                        :disabled="!selectedGroupe"
                        @change="onPhaseChange"
                    >
                        <template #option="slotProps">
                            <div>
                                <div>{{ slotProps.option.nom }}</div>
                                <div class="text-sm text-color-secondary">
                                    {{ formatDate(slotProps.option.dateDebut) }} - {{ formatDate(slotProps.option.dateFin) }}
                                </div>
                            </div>
                        </template>
                    </Dropdown>
                </div>

                <div class="field col-12 md:col-4">
                    <label for="semaine-select">Semaine</label>
                    <Calendar
                        id="semaine-select"
                        v-model="selectedSemaine"
                        selectionMode="range"
                        :manualInput="false"
                        dateFormat="dd/mm/yy"
                        :showWeek="true"
                        class="w-full"
                        @date-select="onSemaineChange"
                    />
                </div>
            </div>
        </div>

        <!-- Affichage principal - Emploi du temps -->
        <div class="card p-4 shadow-2">
            <div class="flex justify-content-between align-items-center mb-4">
                <h2 v-if="selectedGroupe && selectedPhase" class="text-2xl font-bold">
                    Emploi du temps - {{ selectedGroupe.nom }} - {{ selectedPhase.nom }}
                </h2>
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-plus"
                        label="Ajouter Séance"
                        class="p-button-rounded p-button-outlined"
                        @click="openAjoutSeanceDialog"
                    />
                    <Button
                        icon="pi pi-cog"
                        label="Personnaliser vue"
                        class="p-button-rounded p-button-outlined"
                        @click="openCustomizeViewDialog"
                    />
                </div>
            </div>

            <!-- Contrôles d'affichage -->
            <div class="flex flex-wrap mb-4 gap-3">
                <div class="flex align-items-center">
                    <label for="heure-debut" class="mr-2">Heure début:</label>
                    <Calendar
                        id="heure-debut"
                        v-model="heureDebut"
                        timeOnly
                        hourFormat="24"
                        :showTime="true"
                        :showSeconds="false"
                        @update:modelValue="refreshCalendar"
                    />
                </div>
                <div class="flex align-items-center">
                    <label for="heure-fin" class="mr-2">Heure fin:</label>
                    <Calendar
                        id="heure-fin"
                        v-model="heureFin"
                        timeOnly
                        hourFormat="24"
                        :showTime="true"
                        :showSeconds="false"
                        @update:modelValue="refreshCalendar"
                    />
                </div>
                <div class="flex align-items-center ml-auto">
                    <ToggleButton
                        v-model="showWeekend"
                        onLabel="Weekend visible"
                        offLabel="Weekend masqué"
                        onIcon="pi pi-eye"
                        offIcon="pi pi-eye-slash"
                        class="mr-3"
                        @change="refreshCalendar"
                    />
                    <Button
                        icon="pi pi-refresh"
                        label="Actualiser"
                        class="p-button-rounded p-button-text"
                        @click="refreshCalendar"
                    />
                </div>
            </div>

            <!-- FullCalendar - Vue hebdomadaire -->
            <FullCalendar
                ref="calendar"
                :options="calendarOptions"
            >
                <template #eventContent="arg">
                    <div class="fc-event-main p-2">
                        <div class="flex justify-content-between align-items-center">
                            <strong>{{ arg.event.title }}</strong>
                            <Button
                                icon="pi pi-pencil"
                                class="p-button-rounded p-button-text p-button-sm"
                                @click.stop="editSeance(arg.event)"
                            />
                        </div>
                        <div class="text-sm">{{ arg.event.extendedProps.formateur }}</div>
                        <div class="text-sm">{{ arg.event.extendedProps.salle }}</div>
                        <div class="text-xs mt-1">
                            <Tag :value="arg.event.extendedProps.module" :severity="arg.event.extendedProps.isEvaluation ? 'danger' : 'info'" />
                            <Tag v-if="arg.event.extendedProps.isEvaluationReprise" value="Reprise" severity="warning" class="ml-1" />
                        </div>
                    </div>
                </template>
            </FullCalendar>
        </div>

        <!-- Panneau de statistiques -->
        <div class="grid mt-4">
            <div class="col-12 md:col-8">
                <div class="card p-4 shadow-2">
                    <TabView>
                        <TabPanel header="Statistiques Modules">
                            <DataTable
                                :value="statsModules"
                                :paginator="true"
                                :rows="5"
                                responsiveLayout="scroll"
                                class="p-datatable-sm"
                            >
                                <Column field="module" header="Module" sortable></Column>
                                <Column field="heuresPlanifiees" header="Heures Planifiées" sortable>
                                    <template #body="{data}">
                                        {{ data.heuresPlanifiees }}h
                                    </template>
                                </Column>
                                <Column field="heuresRealisees" header="Heures Réalisées" sortable>
                                    <template #body="{data}">
                                        {{ data.heuresRealisees }}h
                                    </template>
                                </Column>
                                <Column field="tauxRealisation" header="Taux Réalisation" sortable>
                                    <template #body="{data}">
                                        <ProgressBar
                                            :value="data.tauxRealisation"
                                            :showValue="true"
                                            :class="getProgressBarClass(data.tauxRealisation)"
                                        />
                                    </template>
                                </Column>
                                <Column header="Actions">
                                    <template #body="{data}">
                                        <Button
                                            icon="pi pi-chart-line"
                                            class="p-button-rounded p-button-text"
                                            @click="showModuleDetails(data)"
                                        />
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Présences">
                            <DataTable
                                :value="presencesStats"
                                :paginator="true"
                                :rows="5"
                                responsiveLayout="scroll"
                                class="p-datatable-sm"
                            >
                                <Column field="stagiaire" header="Stagiaire" sortable></Column>
                                <Column field="seancesTotal" header="Séances Total" sortable></Column>
                                <Column field="presences" header="Présences" sortable></Column>
                                <Column field="tauxPresence" header="Taux Présence" sortable>
                                    <template #body="{data}">
                                        <ProgressBar
                                            :value="data.tauxPresence"
                                            :showValue="true"
                                            :class="getProgressBarClass(data.tauxPresence)"
                                        />
                                    </template>
                                </Column>
                                <Column header="Actions">
                                    <template #body="{data}">
                                        <Button
                                            icon="pi pi-users"
                                            class="p-button-rounded p-button-text"
                                            @click="openPresenceDialog(data.stagiaireId)"
                                        />
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>
                    </TabView>
                </div>
            </div>
            <div class="col-12 md:col-4">
                <div class="card p-4 shadow-2">
                    <h3 class="p-3 border-bottom-1 surface-border">Prochaines Évaluations</h3>
                    <Timeline :value="prochainesEvaluations" align="alternate" class="p-3">
                        <template #content="slotProps">
                            <Card>
                                <template #title>
                                    {{ slotProps.item.module }}
                                </template>
                                <template #subtitle>
                                    {{ formatDate(slotProps.item.date) }} {{ slotProps.item.isEvaluationReprise ? '(Reprise)' : '' }}
                                </template>
                                <template #content>
                                    <p>Type: {{ slotProps.item.type }}</p>
                                    <p>Formateur: {{ slotProps.item.formateur }}</p>
                                    <p>Salle: {{ slotProps.item.salle }}</p>
                                </template>
                            </Card>
                        </template>
                    </Timeline>
                </div>
            </div>
        </div>

        <!-- Dialogue Ajout/Modification Séance -->
        <Dialog
            v-model:visible="displaySeanceDialog"
            :header="seanceDialogHeader"
            :modal="true"
            :style="{width: '50vw'}"
            :breakpoints="{'960px': '75vw', '640px': '90vw'}"
            class="p-dialog-modern"
        >
            <div class="p-fluid">
                <div class="grid">
                    <div class="field col-12 md:col-6">
                        <label for="module">Module</label>
                        <Dropdown
                            id="module"
                            v-model="currentSeance.module"
                            :options="modules"
                            optionLabel="nom"
                            placeholder="Sélectionnez un module"
                            class="w-full"
                            :class="{'p-invalid': seanceErrors.module}"
                        />
                        <small class="p-error" v-if="seanceErrors.module">{{ seanceErrors.module }}</small>
                    </div>
                    <div class="field col-12 md:col-6">
                        <label for="formateur">Formateur</label>
                        <Dropdown
                            id="formateur"
                            v-model="currentSeance.formateur"
                            :options="formateursDisponibles"
                            optionLabel="nom"
                            placeholder="Sélectionnez un formateur"
                            class="w-full"
                            :class="{'p-invalid': seanceErrors.formateur}"
                            @change="checkFormateurDisponibilite"
                        >
                            <template #option="slotProps">
                                <div>
                                    <div>{{ slotProps.option.nom }}</div>
                                    <div class="text-sm text-color-secondary">
                                        Spécialité: {{ slotProps.option.specialite }}
                                    </div>
                                </div>
                            </template>
                        </Dropdown>
                        <small class="p-error" v-if="seanceErrors.formateur">{{ seanceErrors.formateur }}</small>
                        <small v-if="formateurDisponibilite" class="text-xs" :class="formateurDisponibilite.class">
                            {{ formateurDisponibilite.message }}
                        </small>
                    </div>
                    <div class="field col-12 md:col-6">
                        <label for="salle">Espace Pédagogique</label>
                        <Dropdown
                            id="salle"
                            v-model="currentSeance.salle"
                            :options="sallesDisponibles"
                            optionLabel="nom"
                            placeholder="Sélectionnez une salle"
                            class="w-full"
                            :class="{'p-invalid': seanceErrors.salle}"
                            @change="checkSalleDisponibilite"
                        >
                            <template #option="slotProps">
                                <div>
                                    <div>{{ slotProps.option.nom }}</div>
                                    <div class="text-sm text-color-secondary">
                                        Capacité: {{ slotProps.option.capacite }} places
                                    </div>
                                </div>
                            </template>
                        </Dropdown>
                        <small class="p-error" v-if="seanceErrors.salle">{{ seanceErrors.salle }}</small>
                        <small v-if="salleDisponibilite" class="text-xs" :class="salleDisponibilite.class">
                            {{ salleDisponibilite.message }}
                        </small>
                    </div>
                    <div class="field col-12 md:col-6">
                        <label for="type-seance">Type de Séance</label>
                        <Dropdown
                            id="type-seance"
                            v-model="currentSeance.type"
                            :options="typesSeance"
                            placeholder="Sélectionnez un type"
                            class="w-full"
                            :class="{'p-invalid': seanceErrors.type}"
                        />
                        <small class="p-error" v-if="seanceErrors.type">{{ seanceErrors.type }}</small>
                    </div>
                    <div class="field col-12 md:col-6">
                        <label for="is-evaluation">Évaluation</label>
                        <div class="flex gap-2">
                            <Checkbox
                                id="is-evaluation"
                                v-model="currentSeance.isEvaluation"
                                :binary="true"
                                @change="updateEvaluationType"
                            />
                            <label for="is-evaluation">Évaluation Principale</label>
                            <Checkbox
                                id="is-evaluation-reprise"
                                v-model="currentSeance.isEvaluationReprise"
                                :binary="true"
                                :disabled="!currentSeance.isEvaluation"
                            />
                            <label for="is-evaluation-reprise">Évaluation de Reprise</label>
                        </div>
                    </div>
                    <div class="field col-12 md:col-6">
                        <label for="date-seance">Date</label>
                        <Calendar
                            id="date-seance"
                            v-model="currentSeance.date"
                            dateFormat="dd/mm/yy"
                            :minDate="selectedPhase?.dateDebut"
                            :maxDate="selectedPhase?.dateFin"
                            class="w-full"
                            :class="{'p-invalid': seanceErrors.date}"
                            @update:modelValue="checkFormateurDisponibilite(); checkSalleDisponibilite()"
                        />
                        <small class="p-error" v-if="seanceErrors.date">{{ seanceErrors.date }}</small>
                    </div>
                    <div class="field col-12 md:col-3">
                        <label for="heure-debut-seance">Heure Début</label>
                        <Calendar
                            id="heure-debut-seance"
                            v-model="currentSeance.heureDebut"
                            timeOnly
                            hourFormat="24"
                            class="w-full"
                            :class="{'p-invalid': seanceErrors.heureDebut}"
                            @update:modelValue="checkFormateurDisponibilite(); checkSalleDisponibilite()"
                        />
                        <small class="p-error" v-if="seanceErrors.heureDebut">{{ seanceErrors.heureDebut }}</small>
                    </div>
                    <div class="field col-12 md:col-3">
                        <label for="heure-fin-seance">Heure Fin</label>
                        <Calendar
                            id="heure-fin-seance"
                            v-model="currentSeance.heureFin"
                            timeOnly
                            hourFormat="24"
                            class="w-full"
                            :class="{'p-invalid': seanceErrors.heureFin}"
                            @update:modelValue="checkFormateurDisponibilite(); checkSalleDisponibilite()"
                        />
                        <small class="p-error" v-if="seanceErrors.heureFin">{{ seanceErrors.heureFin }}</small>
                    </div>
                    <div class="field col-12">
                        <label for="description-seance">Description (Optionnel)</label>
                        <Textarea
                            id="description-seance"
                            v-model="currentSeance.description"
                            rows="3"
                            class="w-full"
                        />
                    </div>
                    <div class="field col-12" v-if="isEditMode">
                        <div class="flex align-items-center">
                            <Checkbox
                                id="update-recurrence"
                                v-model="updateRecurrence"
                                :binary="true"
                            />
                            <label for="update-recurrence" class="ml-2">
                                Appliquer les modifications à toutes les séances récurrentes
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="displaySeanceDialog = false"
                />
                <Button
                    label="Enregistrer"
                    icon="pi pi-check"
                    class="p-button-success"
                    @click="saveSeance"
                />
                <Button
                    v-if="isEditMode"
                    label="Supprimer"
                    icon="pi pi-trash"
                    class="p-button-danger"
                    @click="confirmDeleteSeance"
                />
            </template>
        </Dialog>

        <!-- Dialogue Gestion des Présences -->
        <Dialog
            v-model:visible="displayPresenceDialog"
            header="Gestion des Présences"
            :modal="true"
            :style="{width: '50vw'}"
            :breakpoints="{'960px': '75vw', '640px': '90vw'}"
        >
            <div class="p-fluid">
                <h4>Séance: {{ currentPresenceSeance?.module?.nom }} - {{ formatDate(currentPresenceSeance?.date) }}</h4>
                <DataTable
                    :value="currentPresenceData"
                    responsiveLayout="scroll"
                    class="p-datatable-sm"
                >
                    <Column field="stagiaire.nom" header="Stagiaire" sortable></Column>
                    <Column header="Présent">
                        <template #body="{data}">
                            <Checkbox
                                v-model="data.present"
                                :binary="true"
                                @change="updatePresence(data)"
                            />
                        </template>
                    </Column>
                </DataTable>
            </div>
            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="displayPresenceDialog = false"
                />
                <Button
                    label="Enregistrer"
                    icon="pi pi-check"
                    class="p-button-success"
                    @click="savePresences"
                />
            </template>
        </Dialog>

        <!-- Dialogue Nouvel Emploi du Temps -->
        <Dialog
            v-model:visible="displayNouvelEmploiDialog"
            header="Créer un Nouvel Emploi du Temps"
            :modal="true"
            :style="{width: '40vw'}"
            class="p-dialog-modern"
        >
            <div class="p-fluid">
                <div class="field">
                    <label for="nouveau-groupe">Groupe</label>
                    <Dropdown
                        id="nouveau-groupe"
                        v-model="nouvelEmploi.groupe"
                        :options="groupes"
                        optionLabel="nom"
                        placeholder="Sélectionnez un groupe"
                        class="w-full"
                        :class="{'p-invalid': emploiErrors.groupe}"
                    />
                    <small class="p-error" v-if="emploiErrors.groupe">{{ emploiErrors.groupe }}</small>
                </div>
                <div class="field">
                    <label for="nouvelle-phase">Phase de Formation</label>
                    <Dropdown
                        id="nouvelle-phase"
                        v-model="nouvelEmploi.phase"
                        :options="phases"
                        optionLabel="nom"
                        placeholder="Sélectionnez une phase"
                        class="w-full"
                        :class="{'p-invalid': emploiErrors.phase}"
                    />
                    <small class="p-error" v-if="emploiErrors.phase">{{ emploiErrors.phase }}</small>
                </div>
                <div class="field">
                    <label for="date-debut-emploi">Date Début</label>
                    <Calendar
                        id="date-debut-emploi"
                        v-model="nouvelEmploi.dateDebut"
                        dateFormat="dd/mm/yy"
                        class="w-full"
                        :class="{'p-invalid': emploiErrors.dateDebut}"
                    />
                    <small class="p-error" v-if="emploiErrors.dateDebut">{{ emploiErrors.dateDebut }}</small>
                </div>
                <div class="field">
                    <label for="date-fin-emploi">Date Fin</label>
                    <Calendar
                        id="date-fin-emploi"
                        v-model="nouvelEmploi.dateFin"
                        dateFormat="dd/mm/yy"
                        class="w-full"
                        :class="{'p-invalid': emploiErrors.dateFin}"
                    />
                    <small class="p-error" v-if="emploiErrors.dateFin">{{ emploiErrors.dateFin }}</small>
                </div>
            </div>
            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="displayNouvelEmploiDialog = false"
                />
                <Button
                    label="Créer"
                    icon="pi pi-check"
                    class="p-button-success"
                    @click="createNouvelEmploi"
                />
            </template>
        </Dialog>

        <!-- Dialogue Gestion des Contraintes -->
        <Dialog
            v-model:visible="displayContraintesDialog"
            header="Gestion des Contraintes"
            :modal="true"
            :style="{width: '70vw'}"
            class="p-dialog-modern"
        >
            <TabView>
                <TabPanel header="Disponibilités Formateurs">
                    <DataTable
                        :value="formateurs"
                        :paginator="true"
                        :rows="5"
                        responsiveLayout="scroll"
                        class="p-datatable-sm"
                    >
                        <Column field="nom" header="Formateur" sortable></Column>
                        <Column field="specialite" header="Spécialité" sortable></Column>
                        <Column header="Disponibilités">
                            <template #body="{data}">
                                <MultiSelect
                                    v-model="data.disponibilites"
                                    :options="joursSemaine"
                                    optionLabel="label"
                                    placeholder="Jours disponibles"
                                    display="chip"
                                    @change="updateFormateurDisponibilites(data)"
                                />
                            </template>
                        </Column>
                        <Column header="Créneaux Indisponibles">
                            <template #body="{data}">
                                <Button
                                    icon="pi pi-clock"
                                    label="Définir créneaux"
                                    class="p-button-text p-button-sm"
                                    @click="openDisponibilitesFormateur(data)"
                                />
                            </template>
                        </Column>
                    </DataTable>
                </TabPanel>
                <TabPanel header="Disponibilités Salles">
                    <DataTable
                        :value="salles"
                        :paginator="true"
                        :rows="5"
                        responsiveLayout="scroll"
                        class="p-datatable-sm"
                    >
                        <Column field="nom" header="Salle" sortable></Column>
                        <Column field="capacite" header="Capacité" sortable></Column>
                        <Column field="type" header="Type" sortable></Column>
                        <Column header="Indisponibilités">
                            <template #body="{data}">
                                <Button
                                    icon="pi pi-clock"
                                    label="Définir créneaux"
                                    class="p-button-text p-button-sm"
                                    @click="openIndisponibilitesSalle(data)"
                                />
                            </template>
                        </Column>
                    </DataTable>
                </TabPanel>
                <TabPanel header="Jours Fériés">
                    <div class="p-fluid">
                        <div class="field">
                            <label>Ajouter un jour férié</label>
                            <div class="flex gap-2">
                                <Calendar
                                    v-model="nouveauJourFerie.date"
                                    dateFormat="dd/mm/yy"
                                    placeholder="Date"
                                    class="flex-grow-1"
                                    :class="{'p-invalid': jourFerieErrors.date}"
                                />
                                <InputText
                                    v-model="nouveauJourFerie.label"
                                    placeholder="Libellé"
                                    class="flex-grow-1"
                                    :class="{'p-invalid': jourFerieErrors.label}"
                                />
                                <Button
                                    icon="pi pi-plus"
                                    class="p-button-success"
                                    @click="addJourFerie"
                                />
                            </div>
                            <small class="p-error" v-if="jourFerieErrors.date">{{ jourFerieErrors.date }}</small>
                            <small class="p-error" v-if="jourFerieErrors.label">{{ jourFerieErrors.label }}</small>
                        </div>
                        <div class="field">
                            <DataTable
                                :value="joursFeries"
                                :paginator="true"
                                :rows="5"
                                responsiveLayout="scroll"
                                class="p-datatable-sm"
                            >
                                <Column field="date" header="Date" sortable>
                                    <template #body="{data}">
                                        {{ formatDate(data.date) }}
                                    </template>
                                </Column>
                                <Column field="label" header="Libellé" sortable></Column>
                                <Column header="Actions">
                                    <template #body="{data}">
                                        <Button
                                            icon="pi pi-trash"
                                            class="p-button-rounded p-button-text p-button-danger"
                                            @click="removeJourFerie(data)"
                                        />
                                    </template>
                                </Column>
                            </DataTable>
                        </div>
                    </div>
                </TabPanel>
            </TabView>
        </Dialog>

        <!-- Dialogue Personnalisation Vue -->
        <Dialog
            v-model:visible="displayCustomizeViewDialog"
            header="Personnaliser la Vue"
            :modal="true"
            :style="{width: '40vw'}"
            class="p-dialog-modern"
        >
            <div class="p-fluid">
                <div class="field">
                    <label>Affichage des colonnes</label>
                    <MultiSelect
                        v-model="visibleDays"
                        :options="allDays"
                        optionLabel="label"
                        placeholder="Sélectionnez les jours à afficher"
                        display="chip"
                        class="w-full"
                    />
                </div>
                <div class="field">
                    <label>Plage horaire</label>
                    <div class="flex gap-2 align-items-center">
                        <Calendar
                            v-model="heureDebutCustom"
                            timeOnly
                            hourFormat="24"
                            :showTime="true"
                            :showSeconds="false"
                            class="w-full"
                        />
                        <span>à</span>
                        <Calendar
                            v-model="heureFinCustom"
                            timeOnly
                            hourFormat="24"
                            :showTime="true"
                            :showSeconds="false"
                            class="w-full"
                        />
                    </div>
                </div>
                <div class="field">
                    <label>Densité d'affichage</label>
                    <Dropdown
                        v-model="slotDuration"
                        :options="slotDurations"
                        optionLabel="label"
                        optionValue="value"
                        class="w-full"
                    />
                </div>
                <div class="field-checkbox">
                    <Checkbox
                        id="show-week-numbers"
                        v-model="showWeekNumbers"
                        :binary="true"
                    />
                    <label for="show-week-numbers">Afficher les numéros de semaine</label>
                </div>
            </div>
            <template #footer>
                <Button
                    label="Annuler"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="displayCustomizeViewDialog = false"
                />
                <Button
                    label="Appliquer"
                    icon="pi pi-check"
                    class="p-button-success"
                    @click="applyViewCustomization"
                />
            </template>
        </Dialog>

        <!-- Dialogue Détails DocumentProgrammeSpecialite -->
        <Dialog
            v-model:visible="displayModuleDetailsDialog"
            :header="moduleDetailsHeader"
            :modal="true"
            :style="{width: '50vw'}"
            class="p-dialog-modern"
        >
            <div class="grid">
                <div class="col-12 md:col-6">
                    <div class="card p-3">
                        <h4>Statistiques</h4>
                        <div class="grid mt-3">
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="stat-value">{{ currentModuleDetails.heuresPlanifiees }}h</div>
                                    <div class="stat-label">Planifiées</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="stat-value">{{ currentModuleDetails.heuresRealisees }}h</div>
                                    <div class="stat-label">Réalisées</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="stat-value">{{ currentModuleDetails.tauxRealisation }}%</div>
                                    <div class="stat-label">Taux réalisation</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="stat-value">{{ currentModuleDetails.nombreSeances }}</div>
                                    <div class="stat-label">Séances</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 md:col-6">
                    <div class="card p-3">
                        <h4>Progression</h4>
                        <Chart
                            type="bar"
                            :data="moduleProgressChartData"
                            :options="chartOptions"
                            class="h-20rem"
                        />
                    </div>
                </div>
                <div class="col-12">
                    <div class="card p-3">
                        <h4>Dernières Séances</h4>
                        <DataTable
                            :value="currentModuleDetails.seances"
                            :paginator="true"
                            :rows="5"
                            responsiveLayout="scroll"
                            class="p-datatable-sm"
                        >
                            <Column field="date" header="Date" sortable>
                                <template #body="{data}">
                                    {{ formatDate(data.date) }}
                                </template>
                            </Column>
                            <Column field="heureDebut" header="Heure Début"></Column>
                            <Column field="heureFin" header="Heure Fin"></Column>
                            <Column field="formateur" header="Formateur"></Column>
                            <Column field="salle" header="Salle"></Column>
                            <Column header="Présences">
                                <template #body="{data}">
                                    <ProgressBar
                                        :value="data.tauxPresence"
                                        :showValue="false"
                                        style="height: 6px"
                                    />
                                    <small>{{ data.presences }}/{{ data.stagiairesTotal }}</small>
                                </template>
                            </Column>
                            <Column header="Actions">
                                <template #body="{data}">
                                    <Button
                                        icon="pi pi-users"
                                        class="p-button-rounded p-button-text"
                                        @click="openPresenceDialogForSeance(data.id)"
                                    />
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>
            </div>
        </Dialog>

        <!-- Dialogue Confirmation -->
        <Dialog
            v-model:visible="displayConfirmDialog"
            :style="{width: '450px'}"
            :modal="true"
            :header="confirmDialogHeader"
            class="p-dialog-modern"
        >
            <div class="confirmation-content p-4">
                <i :class="confirmDialogIcon" style="font-size: 2rem"></i>
                <span class="ml-2">{{ confirmDialogMessage }}</span>
            </div>
            <template #footer>
                <Button
                    :label="confirmDialogNoLabel"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="displayConfirmDialog = false"
                />
                <Button
                    :label="confirmDialogYesLabel"
                    icon="pi pi-check"
                    class="p-button-success"
                    @click="confirmAction"
                />
            </template>
        </Dialog>
    </div>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import frLocale from '@fullcalendar/core/locales/fr';
import { v4 as uuidv4 } from 'uuid';
import ToastService from 'primevue/toastservice';

export default {
    components: {
        FullCalendar
    },
    setup() {
        // Données simulées pour les tables
        const groupes = ref([
            { id: 1, nom: "Groupe A", specialite: "Informatique" },
            { id: 2, nom: "Groupe B", specialite: "Electronique" },
            { id: 3, nom: "Groupe C", specialite: "Mécanique" }
        ]);

        const phases = ref([
            { id: 1, nom: "Phase Initiale", groupeId: 1, dateDebut: new Date(2025, 0, 1), dateFin: new Date(2025, 2, 31) },
            { id: 2, nom: "Phase Intermédiaire", groupeId: 1, dateDebut: new Date(2025, 3, 1), dateFin: new Date(2025, 5, 30) },
            { id: 3, nom: "Phase Finale", groupeId: 1, dateDebut: new Date(2025, 6, 1), dateFin: new Date(2025, 8, 30) },
            { id: 4, nom: "Phase Initiale", groupeId: 2, dateDebut: new Date(2025, 1, 1), dateFin: new Date(2025, 3, 30) }
        ]);

        const modules = ref([
            { id: 1, nom: "Programmation Web", heuresTotales: 120 },
            { id: 2, nom: "Bases de Données", heuresTotales: 80 },
            { id: 3, nom: "Réseaux Informatiques", heuresTotales: 60 },
            { id: 4, nom: "Systèmes d'Exploitation", heuresTotales: 70 }
        ]);

        const formateurs = ref([
            { id: 1, nom: "Jean Dupont", specialite: "Informatique", disponibilites: ['lundi', 'mardi', 'jeudi'], indisponibilites: [] },
            { id: 2, nom: "Marie Martin", specialite: "Réseaux", disponibilites: ['mardi', 'mercredi', 'vendredi'], indisponibilites: [] },
            { id: 3, nom: "Pierre Durand", specialite: "Bases de données", disponibilites: ['lundi', 'mercredi', 'jeudi'], indisponibilites: [] }
        ]);

        const salles = ref([
            { id: 1, nom: "Salle 101", capacite: 20, type: "Salle de cours", indisponibilites: [] },
            { id: 2, nom: "Salle 102", capacite: 25, type: "Salle de cours", indisponibilites: [] },
            { id: 3, nom: "Labo Informatique", capacite: 15, type: "Laboratoire", indisponibilites: [] },
            { id: 4, nom: "Amphithéâtre", capacite: 50, type: "Amphithéâtre", indisponibilites: [] }
        ]);

        const stagiaires = ref([
            { id: 1, nom: "Stagiaire 1", groupeId: 1 },
            { id: 2, nom: "Stagiaire 2", groupeId: 1 },
            { id: 3, nom: "Stagiaire 3", groupeId: 1 },
            { id: 4, nom: "Stagiaire 4", groupeId: 2 },
            { id: 5, nom: "Stagiaire 5", groupeId: 2 }
        ]);

        const emplois_temps_groupes = ref([
            { id: 1, groupeId: 1, phaseId: 1, dateDebut: new Date(2025, 0, 1), dateFin: new Date(2025, 2, 31) },
            { id: 2, groupeId: 1, phaseId: 2, dateDebut: new Date(2025, 3, 1), dateFin: new Date(2025, 5, 30) },
            { id: 3, groupeId: 2, phaseId: 4, dateDebut: new Date(2025, 1, 1), dateFin: new Date(2025, 3, 30) }
        ]);

        const seances = ref([
            {
                id: 1,
                emploiId: 1,
                groupeId: 1,
                phaseId: 1,
                module: modules.value[0],
                formateur: formateurs.value[0],
                salle: salles.value[0],
                type: "Cours théorique",
                date: new Date(2025, 0, 6),
                heureDebut: "08:00",
                heureFin: "10:00",
                description: "Introduction à HTML/CSS",
                isEvaluation: false,
                isEvaluationReprise: false,
                presences: [
                    { stagiaireId: 1, present: true },
                    { stagiaireId: 2, present: true },
                    { stagiaireId: 3, present: false }
                ]
            },
            {
                id: 2,
                emploiId: 1,
                groupeId: 1,
                phaseId: 1,
                module: modules.value[0],
                formateur: formateurs.value[0],
                salle: salles.value[0],
                type: "TP (Travaux Pratiques)",
                date: new Date(2025, 0, 6),
                heureDebut: "10:30",
                heureFin: "12:30",
                description: "Premiers exercices HTML",
                isEvaluation: false,
                isEvaluationReprise: false,
                presences: [
                    { stagiaireId: 1, present: true },
                    { stagiaireId: 2, present: true },
                    { stagiaireId: 3, present: true }
                ]
            },
            {
                id: 3,
                emploiId: 1,
                groupeId: 1,
                phaseId: 1,
                module: modules.value[1],
                formateur: formateurs.value[2],
                salle: salles.value[2],
                type: "Évaluation",
                date: new Date(2025, 0, 7),
                heureDebut: "09:00",
                heureFin: "12:00",
                description: "Examen Bases de Données",
                isEvaluation: true,
                isEvaluationReprise: false,
                presences: [
                    { stagiaireId: 1, present: true },
                    { stagiaireId: 2, present: true },
                    { stagiaireId: 3, present: true }
                ]
            }
        ]);

        const typesSeance = ref([
            "Cours théorique",
            "TP (Travaux Pratiques)",
            "TD (Travaux Dirigés)",
            "Évaluation",
            "Projet"
        ]);

        const joursSemaine = ref([
            { label: "Lundi", value: "lundi" },
            { label: "Mardi", value: "mardi" },
            { label: "Mercredi", value: "mercredi" },
            { label: "Jeudi", value: "jeudi" },
            { label: "Vendredi", value: "vendredi" },
            { label: "Samedi", value: "samedi" },
            { label: "Dimanche", value: "dimanche" }
        ]);

        const allDays = ref([
            { label: "Lundi", value: 1 },
            { label: "Mardi", value: 2 },
            { label: "Mercredi", value: 3 },
            { label: "Jeudi", value: 4 },
            { label: "Vendredi", value: 5 },
            { label: "Samedi", value: 6 },
            { label: "Dimanche", value: 0 }
        ]);

        const slotDurations = ref([
            { label: "15 minutes", value: "00:15:00" },
            { label: "30 minutes", value: "00:30:00" },
            { label: "1 heure", value: "01:00:00" },
            { label: "2 heures", value: "02:00:00" }
        ]);

        const joursFeries = ref([
            { date: new Date(2025, 0, 1), label: "Nouvel An" },
            { date: new Date(2025, 4, 1), label: "Fête du Travail" }
        ]);

        // Breadcrumb
        const breadcrumbHome = ref({ label: 'Accueil', to: '/' });
        const breadcrumbItems = ref([
            { label: 'Emploi du Temps', to: '/emploi' }
        ]);

        // Sélections
        const selectedGroupe = ref(null);
        const selectedPhase = ref(null);
        const selectedSemaine = ref([new Date(2025, 0, 6), new Date(2025, 0, 12)]);

        const phasesFiltrees = computed(() => {
            if (!selectedGroupe.value) return [];
            return phases.value.filter(phase => phase.groupeId === selectedGroupe.value.id);
        });

        // Statistiques
        const statsModules = computed(() => {
            if (!selectedPhase.value) return [];

            const seancesPhase = seances.value.filter(s => s.phaseId === selectedPhase.value.id);
            const modulesStats = [];

            modules.value.forEach(mod => {
                const seancesModule = seancesPhase.filter(s => s.module.id === mod.id);
                const heuresPlanifiees = seancesModule.reduce((acc, s) => {
                    const [debutHeures, debutMinutes] = s.heureDebut.split(':').map(Number);
                    const [finHeures, finMinutes] = s.heureFin.split(':').map(Number);
                    const heures = finHeures - debutHeures + (finMinutes - debutMinutes) / 60;
                    return acc + heures;
                }, 0);

                const heuresRealisees = seancesModule.filter(s => new Date(s.date) < new Date())
                    .reduce((acc, s) => {
                        const [debutHeures, debutMinutes] = s.heureDebut.split(':').map(Number);
                        const [finHeures, finMinutes] = s.heureFin.split(':').map(Number);
                        const heures = finHeures - debutHeures + (finMinutes - debutMinutes) / 60;
                        return acc + heures;
                    }, 0);

                const tauxRealisation = mod.heuresTotales > 0 ? Math.round((heuresRealisees / mod.heuresTotales) * 100) : 0;

                modulesStats.push({
                    module: mod.nom,
                    heuresPlanifiees: heuresPlanifiees.toFixed(1),
                    heuresRealisees: heuresRealisees.toFixed(1),
                    tauxRealisation,
                    heuresTotales: mod.heuresTotales,
                    nombreSeances: seancesModule.length,
                    moduleId: mod.id
                });
            });

            return modulesStats;
        });

        const presencesStats = computed(() => {
            if (!selectedPhase.value) return [];

            const seancesPhase = seances.value.filter(s => s.phaseId === selectedPhase.value.id);
            const groupeStagiaires = stagiaires.value.filter(st => st.groupeId === selectedGroupe.value?.id);

            return groupeStagiaires.map(st => {
                const presences = seancesPhase.reduce((acc, s) => {
                    const presence = s.presences.find(p => p.stagiaireId === st.id);
                    return acc + (presence?.present ? 1 : 0);
                }, 0);
                const tauxPresence = seancesPhase.length > 0 ? Math.round((presences / seancesPhase.length) * 100) : 0;

                return {
                    stagiaireId: st.id,
                    stagiaire: st.nom,
                    seancesTotal: seancesPhase.length,
                    presences,
                    tauxPresence
                };
            });
        });

        const statsGlobales = computed(() => {
            if (!selectedPhase.value) return { heuresPlanifiees: 0, heuresRealisees: 0, tauxRealisation: 0, tauxPresenceGlobal: 0 };

            const seancesPhase = seances.value.filter(s => s.phaseId === selectedPhase.value.id);
            const heuresPlanifiees = seancesPhase.reduce((acc, s) => {
                const [debutHeures, debutMinutes] = s.heureDebut.split(':').map(Number);
                const [finHeures, finMinutes] = s.heureFin.split(':').map(Number);
                return acc + (finHeures - debutHeures + (finMinutes - debutMinutes) / 60);
            }, 0);

            const heuresRealisees = seancesPhase.filter(s => new Date(s.date) < new Date())
                .reduce((acc, s) => {
                    const [debutHeures, debutMinutes] = s.heureDebut.split(':').map(Number);
                    const [finHeures, finMinutes] = s.heureFin.split(':').map(Number);
                    return acc + (finHeures - debutHeures + (finMinutes - debutMinutes) / 60);
                }, 0);

            const heuresTotalesModules = modules.value.reduce((acc, m) => acc + m.heuresTotales, 0);
            const tauxRealisation = heuresTotalesModules > 0 ? Math.round((heuresRealisees / heuresTotalesModules) * 100) : 0;

            const tauxPresenceGlobal = seancesPhase.length > 0
                ? Math.round(seancesPhase.reduce((acc, s) => {
                    const presences = s.presences.reduce((pAcc, p) => pAcc + (p.present ? 1 : 0), 0);
                    return acc + (presences / s.presences.length);
                }, 0) / seancesPhase.length * 100)
                : 0;

            return {
                heuresPlanifiees: heuresPlanifiees.toFixed(1),
                heuresRealisees: heuresRealisees.toFixed(1),
                tauxRealisation,
                tauxPresenceGlobal
            };
        });

        const prochainesEvaluations = computed(() => {
            if (!selectedPhase.value) return [];

            return seances.value
                .filter(s => s.phaseId === selectedPhase.value.id && (s.isEvaluation || s.isEvaluationReprise) && new Date(s.date) >= new Date())
                .map(s => ({
                    module: s.module.nom,
                    date: s.date,
                    type: s.isEvaluationReprise ? "Évaluation de Reprise" : "Évaluation",
                    formateur: s.formateur.nom,
                    salle: s.salle.nom,
                    isEvaluationReprise: s.isEvaluationReprise
                }))
                .sort((a, b) => new Date(a.date) - new Date(b.date))
                .slice(0, 5);
        });

        // Configuration du calendrier
        const calendar = ref(null);
        const heureDebut = ref("08:00");
        const heureFin = ref("17:00");
        const showWeekend = ref(true);
        const visibleDays = ref([1, 2, 3, 4, 5]); // Lundi à Vendredi
        const slotDuration = ref("01:00:00");
        const showWeekNumbers = ref(false);

        const calendarOptions = computed(() => ({
            plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
            initialView: 'timeGridWeek',
            locale: frLocale,
            headerToolbar: false,
            allDaySlot: false,
            slotMinTime: heureDebut.value,
            slotMaxTime: heureFin.value,
            weekends: showWeekend.value,
            hiddenDays: allDays.value.filter(d => !visibleDays.value.includes(d.value)).map(d => d.value),
            slotDuration: slotDuration.value,
            weekNumbers: showWeekNumbers.value,
            events: getCalendarEvents(),
            editable: true,
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true,
            eventClick: handleEventClick,
            eventDrop: handleEventDrop,
            eventResize: handleEventResize,
            select: handleDateSelect,
            dayHeaderFormat: { weekday: 'long' }, // Afficher Lundi, Mardi, etc.
            firstDay: 1 // Commencer la semaine par Lundi
        }));

        function getCalendarEvents() {
            if (!selectedPhase.value) return [];

            return seances.value
                .filter(s => s.phaseId === selectedPhase.value.id)
                .map(s => ({
                    id: s.id,
                    title: `${s.module.nom} - ${s.type}`,
                    start: `${formatDateForCalendar(s.date)}T${s.heureDebut}:00`,
                    end: `${formatDateForCalendar(s.date)}T${s.heureFin}:00`,
                    extendedProps: {
                        module: s.module.nom,
                        formateur: s.formateur.nom,
                        salle: s.salle.nom,
                        description: s.description,
                        type: s.type,
                        isEvaluation: s.isEvaluation,
                        isEvaluationReprise: s.isEvaluationReprise,
                        presences: s.presences.reduce((acc, p) => acc + (p.present ? 1 : 0), 0),
                        stagiairesTotal: s.presences.length
                    },
                    backgroundColor: getEventColor(s.type, s.isEvaluation, s.isEvaluationReprise),
                    borderColor: getEventColor(s.type, s.isEvaluation, s.isEvaluationReprise),
                    textColor: '#ffffff'
                }));
        }

        function getEventColor(type, isEvaluation, isEvaluationReprise) {
            if (isEvaluation && isEvaluationReprise) return '#F59E0B'; // Jaune pour évaluation de reprise
            if (isEvaluation) return '#EF4444'; // Rouge pour évaluation principale
            const colors = {
                "Cours théorique": "#3B82F6",
                "TP (Travaux Pratiques)": "#10B981",
                "TD (Travaux Dirigés)": "#F59E0B",
                "Évaluation": "#EF4444",
                "Projet": "#8B5CF6"
            };
            return colors[type] || "#6B7280";
        }

        // Gestion des séances
        const displaySeanceDialog = ref(false);
        const currentSeance = ref(initEmptySeance());
        const isEditMode = ref(false);
        const updateRecurrence = ref(false);
        const seanceErrors = ref({});
        const formateurDisponibilite = ref(null);
        const salleDisponibilite = ref(null);

        const seanceDialogHeader = computed(() => {
            return isEditMode.value ? "Modifier la Séance" : "Ajouter une Nouvelle Séance";
        });

        const formateursDisponibles = computed(() => {
            return formateurs.value;
        });

        const sallesDisponibles = computed(() => {
            return salles.value;
        });

        function initEmptySeance() {
            return {
                id: null,
                emploiId: null,
                groupeId: null,
                phaseId: null,
                module: null,
                formateur: null,
                salle: null,
                type: null,
                date: null,
                heureDebut: "08:00",
                heureFin: "09:00",
                description: "",
                isEvaluation: false,
                isEvaluationReprise: false,
                presences: []
            };
        }

        function openAjoutSeanceDialog() {
            if (!selectedPhase.value || !selectedGroupe.value) {
                showToast('error', 'Erreur', 'Veuillez sélectionner un groupe et une phase avant d\'ajouter une séance');
                return;
            }

            currentSeance.value = initEmptySeance();
            currentSeance.value.groupeId = selectedGroupe.value.id;
            currentSeance.value.phaseId = selectedPhase.value.id;
            currentSeance.value.emploiId = emplois_temps_groupes.value.find(e => e.phaseId === selectedPhase.value.id)?.id;
            currentSeance.value.presences = stagiaires.value
                .filter(st => st.groupeId === selectedGroupe.value.id)
                .map(st => ({ stagiaireId: st.id, present: false }));
            isEditMode.value = false;
            displaySeanceDialog.value = true;
        }

        function editSeance(event) {
            const seanceId = event.id;
            const seance = seances.value.find(s => s.id === seanceId);

            if (seance) {
                currentSeance.value = { ...seance, date: new Date(seance.date) };
                isEditMode.value = true;
                displaySeanceDialog.value = true;
                checkFormateurDisponibilite();
                checkSalleDisponibilite();
            }
        }

        function saveSeance() {
            if (validateSeance()) {
                if (isEditMode.value) {
                    // Mise à jour de la séance existante
                    const index = seances.value.findIndex(s => s.id === currentSeance.value.id);
                    if (index !== -1) {
                        seances.value[index] = {
                            ...currentSeance.value,
                            presences: currentSeance.value.presences.length > 0
                                ? currentSeance.value.presences
                                : seances.value[index].presences
                        };
                    }
                } else {
                    // Ajout d'une nouvelle séance
                    const newId = uuidv4();
                    seances.value.push({
                        ...currentSeance.value,
                        id: newId,
                        presences: stagiaires.value
                            .filter(st => st.groupeId === selectedGroupe.value.id)
                            .map(st => ({ stagiaireId: st.id, present: false }))
                    });
                }

                displaySeanceDialog.value = false;
                refreshCalendar();
                showToast('success', 'Succès', 'Séance enregistrée avec succès');
            }
        }

        function validateSeance() {
            let isValid = true;
            seanceErrors.value = {};

            if (!currentSeance.value.module) {
                seanceErrors.value.module = "DocumentProgrammeSpecialite requis";
                isValid = false;
            }

            if (!currentSeance.value.formateur) {
                seanceErrors.value.formateur = "Formateur requis";
                isValid = false;
            }

            if (!currentSeance.value.salle) {
                seanceErrors.value.salle = "Salle requise";
                isValid = false;
            }

            if (!currentSeance.value.type) {
                seanceErrors.value.type = "Type de séance requis";
                isValid = false;
            }

            if (!currentSeance.value.date) {
                seanceErrors.value.date = "Date requise";
                isValid = false;
            } else if (selectedPhase.value &&
                (currentSeance.value.date < selectedPhase.value.dateDebut ||
                    currentSeance.value.date > selectedPhase.value.dateFin)) {
                seanceErrors.value.date = "La date doit être dans la période de la phase";
                isValid = false;
            } else if (joursFeries.value.some(jf => jf.date.toDateString() === currentSeance.value.date.toDateString())) {
                seanceErrors.value.date = "La date est un jour férié";
                isValid = false;
            }

            if (!currentSeance.value.heureDebut) {
                seanceErrors.value.heureDebut = "Heure de début requise";
                isValid = false;
            }

            if (!currentSeance.value.heureFin) {
                seanceErrors.value.heureFin = "Heure de fin requise";
                isValid = false;
            } else if (currentSeance.value.heureDebut &&
                currentSeance.value.heureFin <= currentSeance.value.heureDebut) {
                seanceErrors.value.heureFin = "L'heure de fin doit être après l'heure de début";
                isValid = false;
            }

            return isValid;
        }

        function checkFormateurDisponibilite() {
            formateurDisponibilite.value = null;
            if (!currentSeance.value.formateur || !currentSeance.value.date || !currentSeance.value.heureDebut || !currentSeance.value.heureFin) {
                return;
            }

            const jourSemaine = currentSeance.value.date.toLocaleDateString('fr-FR', { weekday: 'long' }).toLowerCase();
            if (!currentSeance.value.formateur.disponibilites.includes(jourSemaine)) {
                formateurDisponibilite.value = {
                    message: `Le formateur n'est pas disponible le ${jourSemaine}`,
                    class: 'text-red-500'
                };
                return;
            }

            const conflits = seances.value.filter(s =>
                s.formateur.id === currentSeance.value.formateur.id &&
                s.id !== currentSeance.value.id &&
                s.date.toDateString() === currentSeance.value.date.toDateString() &&
                ((s.heureDebut >= currentSeance.value.heureDebut && s.heureDebut < currentSeance.value.heureFin) ||
                    (s.heureFin > currentSeance.value.heureDebut && s.heureFin <= currentSeance.value.heureFin) ||
                    (s.heureDebut <= currentSeance.value.heureDebut && s.heureFin >= currentSeance.value.heureFin))
            );

            if (conflits.length > 0) {
                formateurDisponibilite.value = {
                    message: `Conflit d'horaire avec ${conflits.length} séance(s)`,
                    class: 'text-red-500'
                };
            } else {
                formateurDisponibilite.value = {
                    message: "Formateur disponible",
                    class: 'text-green-500'
                };
            }
        }

        function checkSalleDisponibilite() {
            salleDisponibilite.value = null;
            if (!currentSeance.value.salle || !currentSeance.value.date || !currentSeance.value.heureDebut || !currentSeance.value.heureFin) {
                return;
            }

            const conflits = seances.value.filter(s =>
                s.salle.id === currentSeance.value.salle.id &&
                s.id !== currentSeance.value.id &&
                s.date.toDateString() === currentSeance.value.date.toDateString() &&
                ((s.heureDebut >= currentSeance.value.heureDebut && s.heureDebut < currentSeance.value.heureFin) ||
                    (s.heureFin > currentSeance.value.heureDebut && s.heureFin <= currentSeance.value.heureFin) ||
                    (s.heureDebut <= currentSeance.value.heureDebut && s.heureFin >= currentSeance.value.heureFin))
            );

            if (conflits.length > 0) {
                salleDisponibilite.value = {
                    message: `Conflit d'horaire avec ${conflits.length} séance(s)`,
                    class: 'text-red-500'
                };
            } else {
                salleDisponibilite.value = {
                    message: "Salle disponible",
                    class: 'text-green-500'
                };
            }
        }

        function confirmDeleteSeance() {
            showConfirmDialog(
                "Confirmer la suppression",
                "Voulez-vous vraiment supprimer cette séance?",
                "pi pi-exclamation-triangle",
                () => {
                    seances.value = seances.value.filter(s => s.id !== currentSeance.value.id);
                    displaySeanceDialog.value = false;
                    refreshCalendar();
                    showToast('success', 'Succès', 'Séance supprimée avec succès');
                },
                "Non",
                "Oui, supprimer"
            );
        }

        // Gestion des présences
        const displayPresenceDialog = ref(false);
        const currentPresenceSeance = ref(null);
        const currentPresenceData = ref([]);

        function openPresenceDialog(stagiaireId) {
            const seancesPhase = seances.value.filter(s => s.phaseId === selectedPhase.value.id);
            currentPresenceData.value = seancesPhase.map(s => ({
                seanceId: s.id,
                module: s.module.nom,
                date: s.date,
                presences: s.presences.find(p => p.stagiaireId === stagiaireId)?.present || false
            }));
            displayPresenceDialog.value = true;
        }

        function openPresenceDialogForSeance(seanceId) {
            const seance = seances.value.find(s => s.id === seanceId);
            if (seance) {
                currentPresenceSeance.value = seance;
                currentPresenceData.value = stagiaires.value
                    .filter(st => st.groupeId === selectedGroupe.value.id)
                    .map(st => ({
                        stagiaireId: st.id,
                        stagiaire: stagiaires.value.find(s => s.id === st.id),
                        present: seance.presences.find(p => p.stagiaireId === st.id)?.present || false
                    }));
                displayPresenceDialog.value = true;
            }
        }

        function updatePresence(data) {
            const seance = seances.value.find(s => s.id === currentPresenceSeance.value.id);
            if (seance) {
                const presence = seance.presences.find(p => p.stagiaireId === data.stagiaireId);
                if (presence) {
                    presence.present = data.present;
                } else {
                    seance.presences.push({ stagiaireId: data.stagiaireId, present: data.present });
                }
            }
        }

        function savePresences() {
            displayPresenceDialog.value = false;
            refreshCalendar();
            showToast('success', 'Succès', 'Présences enregistrées avec succès');
        }

        // Gestion des événements du calendrier
        function handleEventClick(info) {
            editSeance(info.event);
        }

        function handleEventDrop(info) {
            const seanceId = info.event.id;
            const seance = seances.value.find(s => s.id === seanceId);

            if (seance) {
                const newDate = new Date(info.event.start);
                const heureDebut = info.event.start.toTimeString().substring(0, 5);
                const heureFin = info.event.end.toTimeString().substring(0, 5);

                if (joursFeries.value.some(jf => jf.date.toDateString() === newDate.toDateString())) {
                    info.revert();
                    showToast('error', 'Erreur', 'Impossible de déplacer vers un jour férié');
                    return;
                }

                seance.date = newDate;
                seance.heureDebut = heureDebut;
                seance.heureFin = heureFin;

                checkFormateurDisponibilite();
                checkSalleDisponibilite();
                if (formateurDisponibilite.value?.class === 'text-red-500' || salleDisponibilite.value?.class === 'text-red-500') {
                    info.revert();
                    showToast('error', 'Erreur', 'Conflit d\'horaire détecté');
                    return;
                }

                refreshCalendar();
                showToast('success', 'Succès', 'Séance déplacée avec succès');
            }
        }

        function handleEventResize(info) {
            const seanceId = info.event.id;
            const seance = seances.value.find(s => s.id === seanceId);

            if (seance) {
                const heureFin = info.event.end.toTimeString().substring(0, 5);
                seance.heureFin = heureFin;

                checkFormateurDisponibilite();
                checkSalleDisponibilite();
                if (formateurDisponibilite.value?.class === 'text-red-500' || salleDisponibilite.value?.class === 'text-red-500') {
                    info.revert();
                    showToast('error', 'Erreur', 'Conflit d\'horaire détecté');
                    return;
                }

                -refreshCalendar();
                showToast('success', 'Succès', 'Durée de séance modifiée avec succès');
            }
        }

        function handleDateSelect(info) {
            if (!selectedPhase.value || !selectedGroupe.value) {
                showToast('error', 'Erreur', 'Veuillez sélectionner un groupe et une phase avant d\'ajouter une séance');
                return;
            }

            if (joursFeries.value.some(jf => jf.date.toDateString() === info.start.toDateString())) {
                showToast('error', 'Erreur', 'Impossible d\'ajouter une séance sur un jour férié');
                return;
            }

            currentSeance.value = initEmptySeance();
            currentSeance.value.groupeId = selectedGroupe.value.id;
            currentSeance.value.phaseId = selectedPhase.value.id;
            currentSeance.value.emploiId = emplois_temps_groupes.value.find(e => e.phaseId === selectedPhase.value.id)?.id;
            currentSeance.value.date = new Date(info.start);
            currentSeance.value.heureDebut = info.start.toTimeString().substring(0, 5);
            currentSeance.value.heureFin = info.end.toTimeString().substring(0, 5);
            currentSeance.value.presences = stagiaires.value
                .filter(st => st.groupeId === selectedGroupe.value.id)
                .map(st => ({ stagiaireId: st.id, present: false }));

            isEditMode.value = false;
            displaySeanceDialog.value = true;
        }

        // Gestion des emplois du temps
        const displayNouvelEmploiDialog = ref(false);
        const nouvelEmploi = ref({
            groupe: null,
            phase: null,
            dateDebut: null,
            dateFin: null
        });
        const emploiErrors = ref({});

        function openNouvelEmploiDialog() {
            nouvelEmploi.value = {
                groupe: null,
                phase: null,
                dateDebut: null,
                dateFin: null
            };
            emploiErrors.value = {};
            displayNouvelEmploiDialog.value = true;
        }
        function createNouvelEmploi() {
            let isValid = true;
            emploiErrors.value = {};

            if (!nouvelEmploi.value.groupe) {
                emploiErrors.value.groupe = "Groupe requis";
                isValid = false;
            }

            if (!nouvelEmploi.value.phase) {
                emploiErrors.value.phase = "Phase requise";
                isValid = false;
            }

            if (!nouvelEmploi.value.dateDebut) {
                emploiErrors.value.dateDebut = "Date de début requise";
                isValid = false;
            }

            if (!nouvelEmploi.value.dateFin) {
                emploiErrors.value.dateFin = "Date de fin requise";
                isValid = false;
            } else if (nouvelEmploi.value.dateDebut && nouvelEmploi.value.dateFin <= nouvelEmploi.value.dateDebut) {
                emploiErrors.value.dateFin = "La date de fin doit être après la date de début";
                isValid = false;
            }

            if (isValid) {
                const newId = uuidv4();
                emplois_temps_groupes.value.push({
                    id: newId,
                    groupeId: nouvelEmploi.value.groupe.id,
                    phaseId: nouvelEmploi.value.phase.id,
                    dateDebut: new Date(nouvelEmploi.value.dateDebut),
                    dateFin: new Date(nouvelEmploi.value.dateFin)
                });

                // Mettre à jour la phase avec les nouvelles dates
                const phase = phases.value.find(p => p.id === nouvelEmploi.value.phase.id);
                if (phase) {
                    phase.dateDebut = new Date(nouvelEmploi.value.dateDebut);
                    phase.dateFin = new Date(nouvelEmploi.value.dateFin);
                }

                displayNouvelEmploiDialog.value = false;
                showToast('success', 'Succès', 'Emploi du temps créé avec succès');
                onGroupeSelect();
            }
        }

// Gestion des contraintes
        const displayContraintesDialog = ref(false);
        const nouveauJourFerie = ref({ date: null, label: '' });
        const jourFerieErrors = ref({});

        function openContraintesDialog() {
            displayContraintesDialog.value = true;
        }

        function updateFormateurDisponibilites(formateur, data) {
            const index = formateurs.value.findIndex(f => f.id === formateur.id);
            if (index !== -1) {
                formateurs.value[index].disponibilites = data.disponibilites;
                showToast('success', 'Succès', 'Disponibilités du formateur mises à jour');
            }
        }

        function openDisponibilitesFormateur(formateur) {
            currentFormateur.value = { ...formateur };
            displayDisponibilitesFormateurDialog.value = true;
        }

        function openIndisponibilitesSalle(salle) {
            currentSalle.value = { ...salle };
            displayIndisponibilitesSalleDialog.value = true;
        }

        function addJourFerie() {
            let isValid = true;
            jourFerieErrors.value = {};

            if (!nouveauJourFerie.value.date) {
                jourFerieErrors.value.date = "Date requise";
                isValid = false;
            }

            if (!nouveauJourFerie.value.label) {
                jourFerieErrors.value.label = "Libellé requis";
                isValid = false;
            }

            if (isValid) {
                joursFeries.value.push({
                    date: new Date(nouveauJourFerie.value.date),
                    label: nouveauJourFerie.value.label
                });
                nouveauJourFerie.value = { date: null, label: '' };
                showToast('success', 'Succès', 'Jour férié ajouté avec succès');
            }
        }

        function removeJourFerie(jourFerie) {
            showConfirmDialog(
                "Confirmer la suppression",
                "Voulez-vous vraiment supprimer ce jour férié ?",
                "pi pi-exclamation-triangle",
                () => {
                    joursFeries.value = joursFeries.value.filter(jf => jf.date !== jourFerie.date || jf.label !== jourFerie.label);
                    showToast('success', 'Succès', 'Jour férié supprimé avec succès');
                },
                "Non",
                "Oui, supprimer"
            );
        }

// Personnalisation de la vue
        const displayCustomizeViewDialog = ref(false);
        const heureDebutCustom = ref("08:00");
        const heureFinCustom = ref("17:00");

        function openCustomizeViewDialog() {
            heureDebutCustom.value = heureDebut.value;
            heureFinCustom.value = heureFin.value;
            displayCustomizeViewDialog.value = true;
        }

        function applyViewCustomization() {
            heureDebut.value = heureDebutCustom.value;
            heureFin.value = heureFinCustom.value;
            refreshCalendar();
            displayCustomizeViewDialog.value = false;
            showToast('success', 'Succès', 'Vue personnalisée appliquée');
        }

// Détails du module
        const displayModuleDetailsDialog = ref(false);
        const currentModuleDetails = ref({});
        const moduleDetailsHeader = computed(() => `Détails du module: ${currentModuleDetails.value.module || ''}`);

        function showModuleDetails(data) {
            const seancesModule = seances.value.filter(s => s.module.id === data.moduleId && s.phaseId === selectedPhase.value.id);
            currentModuleDetails.value = {
                module: data.module,
                heuresPlanifiees: data.heuresPlanifiees,
                heuresRealisees: data.heuresRealisees,
                tauxRealisation: data.tauxRealisation,
                nombreSeances: data.nombreSeances,
                seances: seancesModule.map(s => ({
                    id: s.id,
                    date: s.date,
                    heureDebut: s.heureDebut,
                    heureFin: s.heureFin,
                    formateur: s.formateur.nom,
                    salle: s.salle.nom,
                    presences: s.presences.reduce((acc, p) => acc + (p.present ? 1 : 0), 0),
                    stagiairesTotal: s.presences.length,
                    tauxPresence: s.presences.length > 0 ? Math.round((s.presences.reduce((acc, p) => acc + (p.present ? 1 : 0), 0) / s.presences.length) * 100) : 0
                }))
            };
            displayModuleDetailsDialog.value = true;
        }

        const moduleProgressChartData = computed(() => ({
            labels: ['Planifiées', 'Réalisées'],
            datasets: [{
                data: [currentModuleDetails.value.heuresPlanifiees, currentModuleDetails.value.heuresRealisees],
                backgroundColor: ['#3B82F6', '#10B981'],
                borderColor: ['#3B82F6', '#10B981'],
                borderWidth: 1
            }]
        }));

        const chartOptions = ref({
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            }
        });

// Export PDF
        function exportToPDF() {
            showToast('info', 'Information', 'Fonctionnalité d\'export PDF non implémentée dans cette démo');
        }

// Utilitaires
        function formatDate(date) {
            return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
        }

        function formatDateForCalendar(date) {
            return date.toISOString().split('T')[0];
        }

        function getProgressBarClass(value) {
            if (value >= 80) return 'p-progressbar-success';
            if (value >= 50) return 'p-progressbar-warning';
            return 'p-progressbar-danger';
        }

        function refreshCalendar() {
            if (calendar.value) {
                calendar.value.getApi().refetchEvents();
                calendar.value.getApi().gotoDate(selectedSemaine.value[0]);
            }
        }

        function showToast(severity, summary, detail) {
            ToastService.show({ severity, summary, detail, life: 3000 });
        }

        function showConfirmDialog(header, message, icon, confirmCallback, noLabel = "Non", yesLabel = "Oui") {
            confirmDialogHeader.value = header;
            confirmDialogMessage.value = message;
            confirmDialogIcon.value = icon;
            confirmDialogNoLabel.value = noLabel;
            confirmDialogYesLabel.value = yesLabel;
            confirmAction.value = confirmCallback;
            displayConfirmDialog.value = true;
        }

// Gestion des sélections
        function onGroupeChange() {
            selectedPhase.value = null;
            selectedSemaine.value = [new Date(2025, 0, 6), new Date(2025, 0, 12)];
            refreshCalendar();
        }

        function onPhaseChange() {
            const emploi = emplois_temps_groupes.value.find(e => e.phaseId === selectedPhase.value.id);
            if (emploi) {
                selectedSemaine.value = [new Date(emploi.dateDebut), new Date(emploi.dateDebut.setDate(emploi.dateDebut.getDate() + 6))];
            }
            refreshCalendar();
        }

        function onSemaineChange() {
            refreshCalendar();
        }

        function updateEvaluationType() {
            if (!currentSeance.value.isEvaluation) {
                currentSeance.value.isEvaluationReprise = false;
            }
        }

// Dialogues de confirmation
        const displayConfirmDialog = ref(false);
        const confirmDialogHeader = ref('');
        const confirmDialogMessage = ref('');
        const confirmDialogIcon = ref('');
        const confirmDialogNoLabel = ref('');
        const confirmDialogYesLabel = ref('');
        const confirmAction = ref(() => {});

        onMounted(() => {
            refreshCalendar();
        });

        return {
            groupes,
            phases,
            modules,
            formateurs,
            salles,
            stagiaires,
            emplois_temps_groupes,
            seances,
            typesSeance,
            joursSemaine,
            allDays,
            slotDurations,
            joursFeries,
            breadcrumbHome,
            breadcrumbItems,
            selectedGroupe,
            selectedPhase,
            selectedSemaine,
            phasesFiltrees,
            statsModules,
            presencesStats,
            statsGlobales,
            prochainesEvaluations,
            calendar,
            calendarOptions,
            heureDebut,
            heureFin,
            showWeekend,
            visibleDays,
            slotDuration,
            showWeekNumbers,
            displaySeanceDialog,
            currentSeance,
            isEditMode,
            updateRecurrence,
            seanceErrors,
            formateurDisponibilite,
            salleDisponibilite,
            seanceDialogHeader,
            formateursDisponibles,
            sallesDisponibles,
            displayPresenceDialog,
            currentPresenceSeance,
            currentPresenceData,
            displayNouvelEmploiDialog,
            nouvelEmploi,
            emploiErrors,
            displayContraintesDialog,
            nouveauJourFerie,
            jourFerieErrors,
            displayCustomizeViewDialog,
            heureDebutCustom,
            heureFinCustom,
            displayModuleDetailsDialog,
            currentModuleDetails,
            moduleDetailsHeader,
            moduleProgressChartData,
            chartOptions,
            displayConfirmDialog,
            confirmDialogHeader,
            confirmDialogMessage,
            confirmDialogIcon,
            confirmDialogNoLabel,
            confirmDialogYesLabel,
            openAjoutSeanceDialog,
            editSeance,
            saveSeance,
            validateSeance,
            checkFormateurDisponibilite,
            checkSalleDisponibilite,
            confirmDeleteSeance,
            openPresenceDialog,
            openPresenceDialogForSeance,
            updatePresence,
            savePresences,
            openNouvelEmploiDialog,
            createNouvelEmploi,
            openContraintesDialog,
            updateFormateurDisponibilites,
            openDisponibilitesFormateur,
            openIndisponibilitesSalle,
            addJourFerie,
            removeJourFerie,
            openCustomizeViewDialog,
            applyViewCustomization,
            showModuleDetails,
            exportToPDF,
            formatDate,
            getProgressBarClass,
            refreshCalendar,
            onGroupeChange,
            onPhaseChange,
            onSemaineChange,
            updateEvaluationType
        };
    }
};
</script>

<style scoped>
.emploi-temps-app {
    max-width: 1400px;
    margin: 0 auto;
    font-family: 'Roboto', sans-serif;
}

.p-dialog-modern :deep(.p-dialog-content) {
    border-radius: 8px;
    padding: 1.5rem;
}

.stat-card {
    text-align: center;
    padding: 1rem;
    background: var(--surface-a);
    border-radius: 8px;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--primary-color);
}

.stat-label {
    font-size: 0.875rem;
    color: var(--text-color-secondary);
}

:deep(.fc .fc-timegrid-slot) {
    height: 2rem;
}

:deep(.fc .fc-event) {
    border-radius: 4px;
    padding: 2px 4px;
    cursor: pointer;
    transition: transform 0.2s;
}

:deep(.fc .fc-event:hover) {
    transform: scale(1.02);
}

:deep(.p-progressbar-success .p-progressbar-value) {
    background: var(--green-500);
}

:deep(.p-progressbar-warning .p-progressbar-value) {
    background: var(--yellow-500);
}

:deep(.p-progressbar-danger .p-progressbar-value) {
    background: var(--red-500);
}

@media (max-width: 768px) {
    .emploi-temps-app {
        padding: 1rem;
    }

    .p-dialog-modern {
        width: 90vw !important;
    }
}
</style>
