<template>
    <div class="surface-ground pt-2 px-1.5 pb-1.5" style="margin-top: 0">
        <!-- Header Section with Navbar -->
        <div
            class="surface-card p-2 border-round border-1 surface-border"
            style="
                position: relative;
                top: -36px;
                box-shadow: none;
                margin-bottom: -32px;
            "
        >
            <div class="flex justify-content-between align-items-center">
                <div class="flex gap-3">
                    <Button
                        label="Accueil"
                        icon="pi pi-home"
                        class="p-button-text p-button-plain"
                        @click="$router.push({ name: 'dashboard' })"
                    />
                    <Button
                        label="Vacances & Jours Fériés"
                        icon="pi pi-calendar"
                        class="p-button-text p-button-plain"
                        @click="
                            $router.push({ name: 'periodes-repos-formation' })
                        "
                    />
                    <Button
                        label="Années de Formation"
                        icon="pi pi-sitemap"
                        class="p-button-text p-button-plain"
                        disabled
                    />
                </div>
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-refresh"
                        class="p-button-text p-button-rounded"
                        v-tooltip.bottom="'Rafraîchir'"
                        @click="fetchEvents"
                    />
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid">
            <!-- Calendar Grid -->
            <div class="col-12 lg:col-8 xl:col-9">
                <div
                    class="surface-card p-3 border-round shadow-2 overflow-x-auto"
                >
                    <div
                        class="flex flex-wrap justify-content-between align-items-center gap-3 mb-4"
                    >
                        <div class="flex flex-wrap gap-2">
                            <InputText
                                v-model="filters.global.value"
                                placeholder="Rechercher..."
                            />
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <Button
                                label="Ajouter séance"
                                icon="pi pi-plus"
                                class="p-button-primary"
                                @click="openNewEventDialog"
                            />
                            <Button
                                label="Configurer"
                                icon="pi pi-cog"
                                class="p-button-secondary"
                                @click="showConfigDialog = true"
                            />
                            <Button
                                label="Générer"
                                icon="pi pi-magic"
                                class="p-button-secondary"
                                @click="showGenerateDialog = true"
                            />
                            <Button
                                label="Exporter"
                                icon="pi pi-file-excel"
                                class="p-button-success"
                                @click="handleExport"
                            />
                        </div>
                    </div>
                    <div class="text-center mb-4">
                        <h1 class="text-3xl font-bold text-primary">
                            Emploi du Temps :
                            {{
                                selectedGroupe
                                    ? selectedGroupe.nom
                                    : 'Non sélectionné'
                            }}
                        </h1>
                        <p
                            v-if="selectedPhase"
                            class="text-lg text-color-secondary"
                        >
                            Période :
                            {{ formatDate(selectedPhase.date_debut) }} -
                            {{ formatDate(selectedPhase.date_fin) }} ({{
                                calculateWeeks(selectedPhase)
                            }}
                            semaines)
                        </p>
                    </div>
                    <table class="schedule-table w-full" ref="scheduleTable">
                        <thead>
                            <tr>
                                <th class="day-column">Jour</th>
                                <th
                                    v-for="slot in visibleTimeSlots"
                                    :key="slot.start"
                                    class="time-column"
                                >
                                    {{ formatTime(slot.start) }}
                                    <div class="sub-columns">
                                        <div
                                            v-for="n in 12"
                                            :key="n"
                                            class="sub-column"
                                        ></div>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="day in visibleDays"
                                :key="day.value"
                                :data-day="day.value"
                            >
                                <td class="day-column vertical-center">
                                    {{ day.label }}
                                </td>
                                <td
                                    v-for="slot in visibleTimeSlots"
                                    :key="`${day.value}-${slot.start.toISOString()}`"
                                    class="event-cell"
                                    :data-day="day.value"
                                    :data-time="formatTime(slot.start)"
                                    @click="
                                        addEventFromCell(day.value, slot.start)
                                    "
                                >
                                    <draggable
                                        v-if="
                                            eventsBySlot[day.value] &&
                                            eventsBySlot[day.value][
                                                slot.start.toISOString()
                                            ]
                                        "
                                        v-model="
                                            eventsBySlot[day.value][
                                                slot.start.toISOString()
                                            ]
                                        "
                                        :group="{
                                            name: 'events',
                                            pull: true,
                                            put: true,
                                        }"
                                        item-key="id"
                                        @end="
                                            handleDragEnd(
                                                $event,
                                                day.value,
                                                slot.start
                                            )
                                        "
                                        :animation="200"
                                        tag="div"
                                        class="event-container"
                                    >
                                        <template #item="{ element: event }">
                                            <div
                                                class="event-box"
                                                :data-event-id="event.id"
                                                :style="getEventStyle(event)"
                                                @click.stop="editEvent(event)"
                                            >
                                                <div
                                                    class="resize-handle resize-left"
                                                    @mousedown="
                                                        startResize(
                                                            $event,
                                                            event,
                                                            'left'
                                                        )
                                                    "
                                                ></div>
                                                <div class="event-content">
                                                    <div>
                                                        {{
                                                            getModuleName(
                                                                event.moduleId
                                                            )
                                                        }}
                                                    </div>
                                                    <div>
                                                        {{
                                                            getEspaceName(
                                                                event.espaceId
                                                            )
                                                        }}
                                                    </div>
                                                    <div>
                                                        {{
                                                            getFormateurName(
                                                                event.formateurId
                                                            )
                                                        }}
                                                    </div>
                                                    <div>
                                                        {{
                                                            formatTime(
                                                                event.start
                                                            )
                                                        }}
                                                        -
                                                        {{
                                                            formatTime(
                                                                event.end
                                                            )
                                                        }}
                                                    </div>
                                                </div>
                                                <div
                                                    class="resize-handle resize-right"
                                                    @mousedown="
                                                        startResize(
                                                            $event,
                                                            event,
                                                            'right'
                                                        )
                                                    "
                                                ></div>
                                            </div>
                                        </template>
                                    </draggable>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sidebar (Filters, Signatures) -->
            <div class="col-12 lg:col-4 xl:col-3">
                <Card class="shadow-1 mb-3">
                    <template #title>Filtres</template>
                    <template #content>
                        <div class="flex flex-column gap-4">
                            <div class="field">
                                <label
                                    for="specialite"
                                    class="block font-medium mb-2"
                                    >Spécialité</label
                                >
                                <Dropdown
                                    id="specialite"
                                    v-model="localFilters.specialiteId"
                                    :options="specialites"
                                    optionLabel="nom"
                                    optionValue="id"
                                    placeholder="Sélectionner une spécialité"
                                    class="w-full"
                                    @change="handleSpecialiteChange"
                                />
                            </div>
                            <div class="field">
                                <label
                                    for="groupe"
                                    class="block font-medium mb-2"
                                    >Groupe</label
                                >
                                <Dropdown
                                    id="groupe"
                                    v-model="localFilters.groupeId"
                                    :options="filteredGroupes"
                                    optionLabel="nom"
                                    optionValue="id"
                                    placeholder="Sélectionner un groupe"
                                    class="w-full"
                                    @change="handleGroupeChange"
                                />
                            </div>
                            <div class="field">
                                <label
                                    for="phase"
                                    class="block font-medium mb-2"
                                    >Phase</label
                                >
                                <Dropdown
                                    id="phase"
                                    v-model="localFilters.phaseId"
                                    :options="filteredPhases"
                                    optionLabel="nom"
                                    optionValue="id"
                                    placeholder="Sélectionner une phase"
                                    class="w-full"
                                    @change="emitFilters"
                                />
                            </div>
                            <div class="field">
                                <label
                                    for="periode"
                                    class="block font-medium mb-2"
                                    >Période</label
                                >
                                <Calendar
                                    id="periode"
                                    v-model="localFilters.dateRange"
                                    selectionMode="range"
                                    :manualInput="false"
                                    dateFormat="dd/mm/yy"
                                    placeholder="Sélectionner une période"
                                    class="w-full"
                                    @date-select="emitFilters"
                                />
                            </div>
                        </div>
                    </template>
                </Card>
                <Card class="shadow-1">
                    <template #title>Signatures</template>
                    <template #content>
                        <div class="grid">
                            <div class="col-12 md:col-6">
                                <label
                                    for="director"
                                    class="block font-medium mb-2"
                                    >Directeur</label
                                >
                                <Dropdown
                                    id="director"
                                    v-model="directorId"
                                    :options="formateurs"
                                    optionLabel="nomComplet"
                                    optionValue="id"
                                    placeholder="Sélectionner le directeur"
                                    class="w-full"
                                    @change="updateSignatures"
                                />
                            </div>
                            <div class="col-12 md:col-6">
                                <label
                                    for="coordinator"
                                    class="block font-medium mb-2"
                                    >Coordinateur Technique</label
                                >
                                <Dropdown
                                    id="coordinator"
                                    v-model="technicalCoordinatorId"
                                    :options="formateurs"
                                    optionLabel="nomComplet"
                                    optionValue="id"
                                    placeholder="Sélectionner le coordinateur"
                                    class="w-full"
                                    @change="updateSignatures"
                                />
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Resources Section -->
            <div class="col-12 lg:col-8 xl:col-9">
                <Card class="shadow-2 mt-3">
                    <template #title>Ressources</template>
                    <template #content>
                        <TabView>
                            <TabPanel header="Formateurs">
                                <DataTable
                                    :value="formateurs"
                                    :paginator="true"
                                    :rows="5"
                                    :rowsPerPageOptions="[5, 10, 25]"
                                    responsiveLayout="scroll"
                                    class="p-datatable-sm"
                                    :rowHover="true"
                                >
                                    <Column
                                        field="nomComplet"
                                        header="Nom"
                                        :sortable="true"
                                    >
                                        <template #body="{ data }">
                                            <div
                                                class="flex align-items-center"
                                            >
                                                <Avatar
                                                    :label="
                                                        data.prenom[0] +
                                                        data.nom[0]
                                                    "
                                                    size="small"
                                                    shape="circle"
                                                    class="mr-2"
                                                />
                                                {{ data.prenom }} {{ data.nom }}
                                            </div>
                                        </template>
                                    </Column>
                                    <Column
                                        field="charge"
                                        header="Charge"
                                        :sortable="true"
                                    >
                                        <template #body="{ data }">
                                            <Chip
                                                :label="`${calculateCharge(data.id)}h`"
                                                :class="
                                                    getChargeSeverity(data.id)
                                                "
                                            />
                                        </template>
                                    </Column>
                                    <Column
                                        field="disponibilite"
                                        header="Disponibilité"
                                        :sortable="true"
                                    >
                                        <template #body="{ data }">
                                            <Tag
                                                :severity="
                                                    isFormateurDisponible(
                                                        data.id
                                                    )
                                                        ? 'success'
                                                        : 'danger'
                                                "
                                                :value="
                                                    isFormateurDisponible(
                                                        data.id
                                                    )
                                                        ? 'Disponible'
                                                        : 'Occupé'
                                                "
                                            />
                                        </template>
                                    </Column>
                                </DataTable>
                            </TabPanel>
                            <TabPanel header="Espaces">
                                <DataTable
                                    :value="espaces"
                                    :paginator="true"
                                    :rows="5"
                                    :rowsPerPageOptions="[5, 10, 25]"
                                    responsiveLayout="scroll"
                                    class="p-datatable-sm"
                                    :rowHover="true"
                                >
                                    <Column
                                        field="nom"
                                        header="Nom"
                                        :sortable="true"
                                    />
                                    <Column
                                        field="type"
                                        header="Type"
                                        :sortable="true"
                                    />
                                    <Column
                                        field="capacite"
                                        header="Capacité"
                                        :sortable="true"
                                    >
                                        <template #body="{ data }">
                                            {{ data.capacite }} places
                                        </template>
                                    </Column>
                                    <Column
                                        field="disponibilite"
                                        header="Disponibilité"
                                        :sortable="true"
                                    >
                                        <template #body="{ data }">
                                            <Tag
                                                :severity="
                                                    isEspaceDisponible(data.id)
                                                        ? 'success'
                                                        : 'danger'
                                                "
                                                :value="
                                                    isEspaceDisponible(data.id)
                                                        ? 'Disponible'
                                                        : 'Occupé'
                                                "
                                            />
                                        </template>
                                    </Column>
                                </DataTable>
                            </TabPanel>
                            <TabPanel header="Modules">
                                <DataTable
                                    :value="modules"
                                    :paginator="true"
                                    :rows="5"
                                    :rowsPerPageOptions="[5, 10, 25]"
                                    responsiveLayout="scroll"
                                    class="p-datatable-sm"
                                    :rowHover="true"
                                >
                                    <Column
                                        field="nom"
                                        header="Nom"
                                        :sortable="true"
                                    />
                                    <Column
                                        field="duree_totale"
                                        header="Durée Totale"
                                        :sortable="true"
                                    >
                                        <template #body="{ data }">
                                            {{ data.duree_totale }} heures
                                        </template>
                                    </Column>
                                    <Column
                                        field="duree_seance"
                                        header="Durée/Séance"
                                        :sortable="true"
                                    >
                                        <template #body="{ data }">
                                            {{ data.duree_seance }} heures
                                        </template>
                                    </Column>
                                    <Column
                                        field="progression"
                                        header="Progression"
                                        :sortable="true"
                                    >
                                        <template #body="{ data }">
                                            <div class="flex flex-column gap-2">
                                                <ProgressBar
                                                    :value="
                                                        calculateModuleProgress(
                                                            data.id
                                                        )
                                                    "
                                                    :showValue="false"
                                                    :class="
                                                        getProgressBarClass(
                                                            data.id
                                                        )
                                                    "
                                                />
                                                <small
                                                    >{{
                                                        calculateModuleDoneHours(
                                                            data.id
                                                        )
                                                    }}h /
                                                    {{
                                                        data.duree_totale
                                                    }}h</small
                                                >
                                            </div>
                                        </template>
                                    </Column>
                                </DataTable>
                            </TabPanel>
                        </TabView>
                    </template>
                </Card>
            </div>

            <!-- Conflicts Section -->
            <div class="col-12 lg:col-4 xl:col-3">
                <Card class="shadow-1 mt-3">
                    <template #title>Vérification des contraintes</template>
                    <template #content>
                        <div v-if="hasConflicts" class="conflicts-list">
                            <div
                                v-for="(conflictType, type) in currentConflicts"
                                :key="type"
                            >
                                <div
                                    v-if="conflictType.length > 0"
                                    class="mb-4"
                                >
                                    <h4 class="text-lg font-medium capitalize">
                                        {{ type }}
                                    </h4>
                                    <ul class="list-none p-0 m-0 mt-2">
                                        <li
                                            v-for="(
                                                conflict, idx
                                            ) in conflictType"
                                            :key="idx"
                                            class="p-3 surface-100 border-round mb-2 flex align-items-center"
                                        >
                                            <i
                                                class="pi pi-exclamation-triangle text-red-500 mr-2"
                                            ></i>
                                            {{ formatConflict(conflict, type) }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="p-3 surface-100 border-round flex align-items-center"
                        >
                            <i
                                class="pi pi-check-circle text-green-500 mr-2"
                            ></i>
                            Aucune contrainte détectée
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Event Dialog -->
            <Dialog
                v-model:visible="showEventDialog"
                :header="
                    currentEvent.id ? 'Modifier la séance' : 'Nouvelle séance'
                "
                :modal="true"
                :style="{ width: 'min(90vw, 500px)' }"
            >
                <div class="p-fluid grid">
                    <div class="col-12">
                        <div class="field">
                            <label for="day">Jour</label>
                            <Dropdown
                                id="day"
                                v-model="currentEvent.day"
                                :options="days"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Sélectionner un jour"
                                class="w-full"
                            />
                        </div>
                    </div>
                    <div class="col-12 md:col-6">
                        <div class="field">
                            <label for="startTime">Heure de début</label>
                            <Calendar
                                id="startTime"
                                v-model="currentEvent.start"
                                showTime
                                hourFormat="24"
                                timeOnly
                                minuteInterval="5"
                                class="w-full"
                                @update:modelValue="updateEndTimeIfNeeded"
                            />
                        </div>
                    </div>
                    <div class="col-12 md:col-6">
                        <div class="field">
                            <label for="endTime">Heure de fin</label>
                            <Calendar
                                id="endTime"
                                v-model="currentEvent.end"
                                showTime
                                hourFormat="24"
                                timeOnly
                                minuteInterval="5"
                                class="w-full"
                            />
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="field">
                            <label for="module">Module</label>
                            <Dropdown
                                id="module"
                                v-model="currentEvent.moduleId"
                                :options="modules"
                                optionLabel="nom"
                                optionValue="id"
                                placeholder="Sélectionner un module"
                                class="w-full"
                            />
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="field">
                            <label for="espace">Espace</label>
                            <Dropdown
                                id="espace"
                                v-model="currentEvent.espaceId"
                                :options="espaces"
                                optionLabel="nom"
                                optionValue="id"
                                placeholder="Sélectionner un espace"
                                class="w-full"
                            />
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="field">
                            <label for="formateur">Formateur</label>
                            <Dropdown
                                id="formateur"
                                v-model="currentEvent.formateurId"
                                :options="formateurs"
                                optionLabel="nomComplet"
                                optionValue="id"
                                placeholder="Sélectionner un formateur"
                                class="w-full"
                            />
                        </div>
                    </div>
                </div>
                <template #footer>
                    <Button
                        v-if="currentEvent.id"
                        label="Supprimer"
                        icon="pi pi-trash"
                        class="p-button-danger"
                        @click="deleteEvent"
                    />
                    <Button
                        label="Annuler"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="showEventDialog = false"
                    />
                    <Button
                        label="Enregistrer"
                        icon="pi pi-check"
                        class="p-button-primary"
                        @click="saveEvent"
                    />
                </template>
            </Dialog>

            <!-- Configuration Dialog -->
            <Dialog
                v-model:visible="showConfigDialog"
                header="Configuration du calendrier"
                :modal="true"
                :style="{ width: 'min(90vw, 600px)' }"
            >
                <div class="p-fluid">
                    <div class="field mb-4">
                        <label class="block font-medium mb-2"
                            >Jours affichés</label
                        >
                        <div class="flex flex-wrap gap-3">
                            <div
                                v-for="day in days"
                                :key="day.value"
                                class="flex align-items-center"
                            >
                                <Checkbox
                                    v-model="visibleDays"
                                    :inputId="day.value"
                                    :value="day"
                                    :binary="false"
                                />
                                <label :for="day.value" class="ml-2">{{
                                    day.label
                                }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <label class="block font-medium mb-2"
                            >Créneaux horaires</label
                        >
                        <DataTable
                            :value="timeSlots"
                            class="p-datatable-sm"
                            responsiveLayout="scroll"
                        >
                            <Column field="start" header="Début">
                                <template #body="{ data, index }">
                                    <Calendar
                                        v-model="timeSlots[index].start"
                                        showTime
                                        hourFormat="24"
                                        timeOnly
                                        minuteInterval="5"
                                        class="w-full"
                                        @update:modelValue="
                                            updateTimeSlot(index)
                                        "
                                    />
                                </template>
                            </Column>
                            <Column field="end" header="Fin">
                                <template #body="{ data, index }">
                                    <Calendar
                                        v-model="timeSlots[index].end"
                                        showTime
                                        hourFormat="24"
                                        timeOnly
                                        minuteInterval="5"
                                        class="w-full"
                                        @update:modelValue="
                                            updateTimeSlot(index)
                                        "
                                    />
                                </template>
                            </Column>
                            <Column header="Actions">
                                <template #body="{ index }">
                                    <Button
                                        icon="pi pi-trash"
                                        class="p-button-danger p-button-sm"
                                        @click="removeTimeSlot(index)"
                                    />
                                </template>
                            </Column>
                        </DataTable>
                        <Button
                            label="Ajouter un créneau"
                            icon="pi pi-plus"
                            class="mt-3 p-button-outlined"
                            @click="addTimeSlot"
                        />
                    </div>
                </div>
                <template #footer>
                    <Button
                        label="Annuler"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="showConfigDialog = false"
                    />
                    <Button
                        label="Appliquer"
                        icon="pi pi-check"
                        class="p-button-primary"
                        @click="applyCalendarConfig"
                    />
                </template>
            </Dialog>

            <!-- Generation Dialog -->
            <Dialog
                v-model:visible="showGenerateDialog"
                header="Génération automatique"
                :modal="true"
                :style="{ width: 'min(90vw, 500px)' }"
            >
                <div class="p-fluid">
                    <div class="field mb-4">
                        <label class="block font-medium mb-2">Options</label>
                        <div class="flex flex-column gap-3">
                            <div class="flex align-items-center">
                                <Checkbox
                                    v-model="
                                        generateOptions.respecterContraintes
                                    "
                                    inputId="contraintes"
                                    :binary="true"
                                />
                                <label for="contraintes" class="ml-2"
                                    >Respecter toutes les contraintes</label
                                >
                            </div>
                            <div class="flex align-items-center">
                                <Checkbox
                                    v-model="
                                        generateOptions.optimiserRessources
                                    "
                                    inputId="ressources"
                                    :binary="true"
                                />
                                <label for="ressources" class="ml-2"
                                    >Optimiser l'utilisation des
                                    ressources</label
                                >
                            </div>
                            <div class="flex align-items-center">
                                <Checkbox
                                    v-model="generateOptions.equilibrerCharge"
                                    inputId="charge"
                                    :binary="true"
                                />
                                <label for="charge" class="ml-2"
                                    >Équilibrer la charge des formateurs</label
                                >
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <label for="repartition" class="block font-medium mb-2"
                            >Répartition</label
                        >
                        <Dropdown
                            id="repartition"
                            v-model="generateOptions.repartition"
                            :options="repartitionOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Sélectionner une répartition"
                            class="w-full"
                        />
                    </div>
                </div>
                <template #footer>
                    <Button
                        label="Annuler"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="showGenerateDialog = false"
                    />
                    <Button
                        label="Générer"
                        icon="pi pi-check"
                        class="p-button-primary"
                        @click="handleGenerate"
                    />
                </template>
            </Dialog>
        </div>
    </div>
</template>

<script>
import { ref, computed, watch } from 'vue';
import dayjs from 'dayjs';
import { useConstraints } from '@/composables/useConstraints.js';
import draggable from 'vuedraggable';

export default {
    name: 'EmploisTempsGroupesList',
    components: {
        draggable,
    },
    setup() {
        const localFilters = ref({
            specialiteId: null,
            groupeId: null,
            phaseId: null,
            dateRange: null,
        });
        const filters = ref({
            global: { value: null },
        });
        const showEventDialog = ref(false);
        const showConfigDialog = ref(false);
        const showGenerateDialog = ref(false);
        const currentEvent = ref({
            id: null,
            day: null,
            start: null,
            end: null,
            moduleId: null,
            espaceId: null,
            formateurId: null,
            groupeId: null,
            phaseId: null,
        });
        const days = ref([
            { label: 'Lundi', value: 'monday', index: 1 },
            { label: 'Mardi', value: 'tuesday', index: 2 },
            { label: 'Mercredi', value: 'wednesday', index: 3 },
            { label: 'Jeudi', value: 'thursday', index: 4 },
            { label: 'Vendredi', value: 'friday', index: 5 },
            { label: 'Samedi', value: 'saturday', index: 6 },
            { label: 'Dimanche', value: 'sunday', index: 0 },
        ]);
        const visibleDays = ref([]);
        const timeSlots = ref([]);
        const generateOptions = ref({
            respecterContraintes: true,
            optimiserRessources: true,
            equilibrerCharge: true,
            repartition: 'quinzaine',
        });
        const repartitionOptions = ref([
            { label: 'Par quinzaine', value: 'quinzaine' },
            { label: 'Hebdomadaire', value: 'hebdo' },
            { label: 'Répartir uniformément', value: 'uniforme' },
        ]);
        const currentConflicts = ref({});
        const directorId = ref(null);
        const technicalCoordinatorId = ref(null);
        const isResizing = ref(false);
        const resizeEvent = ref(null);
        const resizeSide = ref(null);
        const resizeStartX = ref(0);

        return {
            localFilters,
            filters,
            showEventDialog,
            showConfigDialog,
            showGenerateDialog,
            currentEvent,
            days,
            visibleDays,
            timeSlots,
            generateOptions,
            repartitionOptions,
            currentConflicts,
            directorId,
            technicalCoordinatorId,
            isResizing,
            resizeEvent,
            resizeSide,
            resizeStartX,
        };
    },
    computed: {
        specialites() {
            return this.$store.state.schedule.specialites || [];
        },
        groupes() {
            return this.$store.state.schedule.groupes || [];
        },
        phases() {
            return this.$store.state.schedule.phases || [];
        },
        events: {
            get() {
                return this.$store.state.schedule.events || [];
            },
            set(newEvents) {
                this.$store.commit('schedule/setEvents', newEvents);
            },
        },
        formateurs() {
            return (this.$store.state.schedule.formateurs || []).map((f) => ({
                ...f,
                nomComplet: `${f.prenom} ${f.nom}`,
            }));
        },
        espaces() {
            return this.$store.state.schedule.espaces || [];
        },
        modules() {
            return this.$store.state.schedule.modules || [];
        },
        filteredGroupes() {
            return (
                this.$store.getters['schedule/filteredGroupes'](
                    this.localFilters.specialiteId
                ) || []
            );
        },
        filteredPhases() {
            return (
                this.$store.getters['schedule/filteredPhases'](
                    this.localFilters.groupeId
                ) || []
            );
        },
        selectedGroupe() {
            return (
                this.groupes.find((g) => g.id === this.localFilters.groupeId) ||
                null
            );
        },
        selectedPhase() {
            return (
                this.phases.find((p) => p.id === this.localFilters.phaseId) ||
                null
            );
        },
        visibleTimeSlots() {
            const slots = this.timeSlots.filter((slot) => {
                if (!slot.start || !slot.end) return false;
                const startHour =
                    slot.start.getHours() + slot.start.getMinutes() / 60;
                const endHour =
                    slot.end.getHours() + slot.end.getMinutes() / 60;
                return startHour >= 8 && endHour <= 18;
            });
            return slots;
        },
        hasConflicts() {
            return Object.values(this.currentConflicts).some(
                (conflicts) => conflicts.length > 0
            );
        },
        scheduleStartTime() {
            return new Date(1970, 0, 1, 8, 0);
        },
        scheduleEndTime() {
            return new Date(1970, 0, 1, 18, 0);
        },
        eventsBySlot() {
            const slots = {};
            this.visibleDays.forEach((day) => {
                slots[day.value] = {};
                this.visibleTimeSlots.forEach((slot) => {
                    slots[day.value][slot.start.toISOString()] =
                        this.getEventsStartingInSlot(day.value, slot.start);
                });
            });
            return slots;
        },
    },
    methods: {
        handleSpecialiteChange() {
            this.localFilters.groupeId = null;
            this.localFilters.phaseId = null;
            if (this.filteredGroupes.length > 0) {
                this.localFilters.groupeId = this.filteredGroupes[0].id;
                if (this.filteredPhases.length > 0) {
                    this.localFilters.phaseId = this.filteredPhases[0].id;
                }
            }
            this.emitFilters();
        },
        handleGroupeChange() {
            this.localFilters.phaseId = null;
            if (this.filteredPhases.length > 0) {
                this.localFilters.phaseId = this.filteredPhases[0].id;
            }
            this.emitFilters();
        },
        emitFilters() {
            console.log('Emitting filters:', this.localFilters);
            this.$store.dispatch('schedule/updateFilters', {
                ...this.localFilters,
            });
            this.fetchEvents();
        },
        addTimeSlot() {
            const lastSlot = this.timeSlots[this.timeSlots.length - 1];
            const newStart = lastSlot
                ? new Date(lastSlot.end)
                : new Date(1970, 0, 1, 8, 0);
            const newEnd = new Date(newStart.getTime() + 60 * 60 * 1000);
            this.timeSlots.push({ start: newStart, end: newEnd });
        },
        removeTimeSlot(index) {
            this.timeSlots.splice(index, 1);
        },
        updateTimeSlot(index) {
            const slot = this.timeSlots[index];
            if (slot.start >= slot.end) {
                slot.end = new Date(slot.start.getTime() + 60 * 60 * 1000);
            }
        },
        applyCalendarConfig() {
            console.log('Applying calendar config:', {
                visibleDays: this.visibleDays,
                timeSlots: this.timeSlots,
            });
            this.$store.dispatch('schedule/updateCalendarConfig', {
                visibleDays: this.visibleDays.map((day) => day.value),
                timeSlots: this.timeSlots,
            });
            localStorage.setItem(
                'calendarConfig',
                JSON.stringify({
                    visibleDays: this.visibleDays.map((day) => day.value),
                    timeSlots: this.timeSlots.map((slot) => ({
                        start: slot.start.toISOString(),
                        end: slot.end.toISOString(),
                    })),
                })
            );
            this.showConfigDialog = false;
            this.fetchEvents();
        },
        openNewEventDialog() {
            if (!this.localFilters.groupeId || !this.localFilters.phaseId) {
                this.$toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: "Veuillez sélectionner un groupe et une phase avant d'ajouter une séance.",
                    life: 3000,
                });
                return;
            }
            this.currentEvent = {
                id: null,
                day: this.visibleDays[0]?.value || 'monday',
                start: new Date(1970, 0, 1, 8, 0),
                end: new Date(1970, 0, 1, 9, 0),
                moduleId: this.modules[0]?.id || null,
                espaceId: this.espaces[0]?.id || null,
                formateurId: this.formateurs[0]?.id || null,
                groupeId: this.localFilters.groupeId,
                phaseId: this.localFilters.phaseId,
            };
            console.log('Opening new event dialog:', this.currentEvent);
            this.showEventDialog = true;
        },
        addEventFromCell(day, startTime) {
            if (!this.localFilters.groupeId || !this.localFilters.phaseId) {
                this.$toast.add({
                    severity: 'warn',
                    summary: 'Attention',
                    detail: "Veuillez sélectionner un groupe et une phase avant d'ajouter une séance.",
                    life: 3000,
                });
                return;
            }
            const snappedStart = this.snapToFiveMinutes(startTime);
            const snappedEnd = new Date(
                snappedStart.getTime() + 60 * 60 * 1000
            );
            console.log('Adding event from cell:', {
                day,
                startTime: this.formatTime(snappedStart),
                endTime: this.formatTime(snappedEnd),
            });
            this.currentEvent = {
                id: null,
                day,
                start: snappedStart,
                end: snappedEnd,
                moduleId: this.modules[0]?.id || null,
                espaceId: this.espaces[0]?.id || null,
                formateurId: this.formateurs[0]?.id || null,
                groupeId: this.localFilters.groupeId,
                phaseId: this.localFilters.phaseId,
            };
            this.showEventDialog = true;
        },
        editEvent(event) {
            this.currentEvent = {
                ...event,
                start: new Date(
                    1970,
                    0,
                    1,
                    event.start.getHours(),
                    event.start.getMinutes()
                ),
                end: new Date(
                    1970,
                    0,
                    1,
                    event.end.getHours(),
                    event.end.getMinutes()
                ),
            };
            console.log('Editing event:', this.currentEvent);
            this.showEventDialog = true;
        },
        saveEvent() {
            console.log('Saving event, currentEvent:', this.currentEvent);
            if (
                !this.currentEvent.day ||
                !this.currentEvent.start ||
                !this.currentEvent.end ||
                !this.currentEvent.moduleId ||
                !this.currentEvent.espaceId ||
                !this.currentEvent.formateurId
            ) {
                console.log('Validation failed:', {
                    day: this.currentEvent.day,
                    start: this.currentEvent.start,
                    end: this.currentEvent.end,
                    moduleId: this.currentEvent.moduleId,
                    espaceId: this.currentEvent.espaceId,
                    formateurId: this.currentEvent.formateurId,
                });
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Veuillez remplir tous les champs obligatoires.',
                    life: 3000,
                });
                return;
            }
            if (this.currentEvent.start >= this.currentEvent.end) {
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: "L'heure de fin doit être après l'heure de début.",
                    life: 3000,
                });
                return;
            }
            const snappedStart = this.snapToFiveMinutes(
                this.currentEvent.start
            );
            const snappedEnd = this.snapToFiveMinutes(this.currentEvent.end);
            const event = {
                ...this.currentEvent,
                id: this.currentEvent.id || this.generateId(),
                groupeId: this.localFilters.groupeId,
                phaseId: this.localFilters.phaseId,
                start: new Date(
                    1970,
                    0,
                    1,
                    snappedStart.getHours(),
                    snappedStart.getMinutes()
                ),
                end: new Date(
                    1970,
                    0,
                    1,
                    snappedEnd.getHours(),
                    snappedEnd.getMinutes()
                ),
                conflicts: this.checkEventConflicts({
                    ...this.currentEvent,
                    start: snappedStart,
                    end: snappedEnd,
                }),
            };
            console.log('Dispatching event to store:', event);
            this.$store
                .dispatch('schedule/updateEvent', event)
                .then(() => {
                    console.log('Event saved, current events:', this.events);
                    this.checkConflicts();
                    this.showEventDialog = false;
                    this.fetchEvents();
                    this.$toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Séance enregistrée avec succès.',
                        life: 3000,
                    });
                })
                .catch((error) => {
                    console.error('Error saving event:', error);
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Erreur lors de l'enregistrement de la séance.",
                        life: 3000,
                    });
                });
        },
        deleteEvent() {
            if (!this.currentEvent.id) {
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Aucun événement sélectionné pour la suppression.',
                    life: 3000,
                });
                return;
            }
            console.log('Deleting event ID:', this.currentEvent.id);
            this.$store
                .dispatch('schedule/deleteEvent', this.currentEvent.id)
                .then(() => {
                    console.log('Event deleted, current events:', this.events);
                    this.checkConflicts();
                    this.showEventDialog = false;
                    this.fetchEvents();
                    this.$toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Séance supprimée avec succès.',
                        life: 3000,
                    });
                })
                .catch((error) => {
                    console.error('Error deleting event:', error);
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Erreur lors de la suppression de la séance.',
                        life: 3000,
                    });
                });
        },
        updateEndTimeIfNeeded() {
            if (!this.currentEvent.start) return;
            if (
                !this.currentEvent.end ||
                this.currentEvent.start >= this.currentEvent.end
            ) {
                const newEnd = new Date(
                    this.currentEvent.start.getTime() + 60 * 60 * 1000
                );
                this.currentEvent.end = new Date(
                    1970,
                    0,
                    1,
                    newEnd.getHours(),
                    newEnd.getMinutes()
                );
            }
        },
        handleDragEnd(event, targetDay, targetTime) {
            const { oldIndex, newIndex, to } = event;
            console.log('Drag end:', {
                oldIndex,
                newIndex,
                targetDay,
                targetTime: this.formatTime(targetTime),
            });
            if (!to || oldIndex === newIndex) {
                console.log('No drag or same position');
                return;
            }

            const movedEvent = this.events.find(
                (e) => e.id === event.item.dataset.eventId
            );
            if (!movedEvent) {
                console.log(
                    'No event found for drag:',
                    event.item.dataset.eventId
                );
                return;
            }

            const targetCell = to.closest('.event-cell') || to;
            if (
                !targetCell ||
                !targetCell.dataset.day ||
                !targetCell.dataset.time
            ) {
                console.log('Invalid target cell:', targetCell);
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Cible de déplacement invalide.',
                    life: 3000,
                });
                return;
            }

            const newDay = targetCell.dataset.day;
            const newTime = dayjs(targetCell.dataset.time, 'HH:mm').toDate();
            const snappedTime = this.snapToFiveMinutes(newTime);

            if (!this.visibleDays.some((day) => day.value === newDay)) {
                console.log('Invalid drop target day:', newDay);
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Jour cible invalide pour le déplacement.',
                    life: 3000,
                });
                return;
            }

            const duration = dayjs(movedEvent.end).diff(
                movedEvent.start,
                'minute'
            );
            const newStart = new Date(
                1970,
                0,
                1,
                snappedTime.getHours(),
                snappedTime.getMinutes()
            );
            const newEnd = new Date(newStart.getTime() + duration * 60 * 1000);

            if (
                newStart < this.scheduleStartTime ||
                newEnd > this.scheduleEndTime
            ) {
                console.log('Drop outside calendar bounds:', {
                    newStart,
                    newEnd,
                });
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Le déplacement est hors des limites du calendrier (08:00–18:00).',
                    life: 3000,
                });
                return;
            }

            const updatedEvent = {
                ...movedEvent,
                day: newDay,
                start: newStart,
                end: newEnd,
                conflicts: this.checkEventConflicts({
                    ...movedEvent,
                    day: newDay,
                    start: newStart,
                    end: newEnd,
                }),
            };

            console.log('Moved event:', updatedEvent);
            this.$store
                .dispatch('schedule/updateEvent', updatedEvent)
                .then(() => {
                    console.log('Event moved, current events:', this.events);
                    this.checkConflicts();
                    this.fetchEvents();
                    this.$toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Séance déplacée avec succès.',
                        life: 3000,
                    });
                })
                .catch((error) => {
                    console.error('Error moving event:', error);
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Erreur lors du déplacement de la séance.',
                        life: 3000,
                    });
                });
        },
        snapToFiveMinutes(time) {
            const minutes = dayjs(time).minute();
            const roundedMinutes = Math.round(minutes / 5) * 5;
            return dayjs(time)
                .minute(roundedMinutes)
                .second(0)
                .millisecond(0)
                .toDate();
        },
        startResize(event, resizeEvent, side) {
            event.preventDefault();
            event.stopPropagation();
            this.isResizing = true;
            this.resizeEvent = { ...resizeEvent };
            this.resizeSide = side;
            this.resizeStartX = event.clientX;

            document.addEventListener('mousemove', this.handleResizeMove);
            document.addEventListener('mouseup', this.stopResize);
            console.log('Starting resize:', {
                resizeEvent,
                side,
                startX: this.resizeStartX,
            });
        },
        handleResizeMove(event) {
            if (!this.isResizing || !this.resizeEvent) return;

            const columnWidthPx = 100;
            const subColumnWidthPx = columnWidthPx / 12;
            const deltaX = event.clientX - this.resizeStartX;
            const deltaMinutes = Math.round(deltaX / subColumnWidthPx) * 5;

            let newStart = new Date(this.resizeEvent.start);
            let newEnd = new Date(this.resizeEvent.end);

            if (this.resizeSide === 'left') {
                newStart = new Date(
                    this.resizeEvent.start.getTime() + deltaMinutes * 60 * 1000
                );
                if (newStart >= newEnd - 5 * 60 * 1000) {
                    newStart = new Date(newEnd.getTime() - 5 * 60 * 1000);
                }
                if (newStart < this.scheduleStartTime) {
                    newStart = new Date(this.scheduleStartTime);
                }
            } else {
                newEnd = new Date(
                    this.resizeEvent.end.getTime() + deltaMinutes * 60 * 1000
                );
                if (newEnd <= newStart.getTime() + 5 * 60 * 1000) {
                    newEnd = new Date(newStart.getTime() + 5 * 60 * 1000);
                }
                if (newEnd > this.scheduleEndTime) {
                    newEnd = new Date(this.scheduleEndTime);
                }
            }

            this.resizeEvent = {
                ...this.resizeEvent,
                start: new Date(
                    1970,
                    0,
                    1,
                    newStart.getHours(),
                    newStart.getMinutes()
                ),
                end: new Date(
                    1970,
                    0,
                    1,
                    newEnd.getHours(),
                    newEnd.getMinutes()
                ),
            };
        },
        stopResize() {
            if (!this.isResizing || !this.resizeEvent) return;

            const updatedEvent = {
                ...this.resizeEvent,
                start: this.snapToFiveMinutes(this.resizeEvent.start),
                end: this.snapToFiveMinutes(this.resizeEvent.end),
                conflicts: this.checkEventConflicts({
                    ...this.resizeEvent,
                    start: this.resizeEvent.start,
                    end: this.resizeEvent.end,
                }),
            };

            console.log('Resizing event:', updatedEvent);
            this.$store
                .dispatch('schedule/updateEvent', updatedEvent)
                .then(() => {
                    console.log('Event resized, current events:', this.events);
                    this.checkConflicts();
                    this.fetchEvents();
                    this.$toast.add({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Séance redimensionnée avec succès.',
                        life: 3000,
                    });
                })
                .catch((error) => {
                    console.error('Error resizing event:', error);
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Erreur lors du redimensionnement de la séance.',
                        life: 3000,
                    });
                });

            document.removeEventListener('mousemove', this.handleResizeMove);
            document.removeEventListener('mouseup', this.stopResize);
            this.isResizing = false;
            this.resizeEvent = null;
            this.resizeSide = null;
            this.resizeStartX = 0;
        },
        handleGenerate() {
            console.log(
                'Generating schedule with options:',
                this.generateOptions
            );
            this.$store.dispatch('schedule/generateSchedule', {
                ...this.generateOptions,
            });
            this.showGenerateDialog = false;
            this.fetchEvents();
        },
        checkConflicts() {
            console.log('Checking conflicts for events:', this.events);
            const conflicts = {};
            this.events.forEach((event) => {
                const eventConflicts = this.checkEventConflicts(event);
                if (Object.values(eventConflicts).flat().length > 0) {
                    conflicts[event.id] = eventConflicts;
                }
            });
            this.currentConflicts = conflicts;
            console.log('Conflicts checked:', this.currentConflicts);
        },
        calculateCharge(formateurId) {
            return this.calculateWeeklyHours(formateurId, new Date());
        },
        calculateModuleDoneHours(moduleId) {
            return this.events
                .filter((event) => event.moduleId === moduleId)
                .reduce(
                    (total, event) =>
                        total + (this.getEventDuration(event) || 0),
                    0
                );
        },
        calculateModuleProgress(moduleId) {
            const module = this.modules.find((m) => m.id === moduleId);
            if (!module) return 0;
            const done = this.calculateModuleDoneHours(moduleId);
            return Math.min((done / module.duree_totale) * 100, 100);
        },
        getProgressBarClass(moduleId) {
            const progress = this.calculateModuleProgress(moduleId);
            if (progress >= 100) return 'progress-complete';
            if (progress >= 75) return 'progress-high';
            if (progress >= 50) return 'progress-medium';
            if (progress >= 25) return 'progress-low';
            return 'progress-very-low';
        },
        getChargeSeverity(formateurId) {
            const charge = this.calculateCharge(formateurId);
            const formateur = this.formateurs.find((f) => f.id === formateurId);
            const regime = formateur
                ? this.$store.state.schedule.regimesHoraires.find(
                      (r) => r.id === formateur.regime_horaire_id
                  )
                : null;
            const maxHours = regime?.heures_semaine || 35;
            if (charge >= maxHours) return 'p-chip-danger';
            if (charge >= maxHours * 0.8) return 'p-chip-warning';
            return 'p-chip-success';
        },
        formatTime(time) {
            return time ? dayjs(time).format('HH:mm') : '';
        },
        formatDate(date) {
            return date ? dayjs(date).format('DD/MM/YYYY') : '-';
        },
        getModuleName(moduleId) {
            return this.modules.find((m) => m.id === moduleId)?.nom || '-';
        },
        getEspaceName(espaceId) {
            return this.espaces.find((e) => e.id === espaceId)?.nom || '-';
        },
        getFormateurName(formateurId) {
            return (
                this.formateurs.find((f) => f.id === formateurId)?.nomComplet ||
                '-'
            );
        },
        generateId() {
            const maxId = Math.max(0, ...this.events.map((e) => e.id || 0));
            return maxId + 1;
        },
        calculateWeeks(phase) {
            if (!phase?.date_debut || !phase?.date_fin) return 0;
            return Math.ceil(
                dayjs(phase.date_fin).diff(phase.date_debut, 'week', true)
            );
        },
        getEventsStartingInSlot(day, slotStart) {
            console.log(
                `Checking slot: day=${day}, time=${dayjs(slotStart).format('HH:mm')}`
            );
            if (!this.localFilters.groupeId || !this.localFilters.phaseId) {
                console.log('No groupeId or phaseId, returning empty events');
                return [];
            }
            const slotStartTime = dayjs(slotStart)
                .set('year', 1970)
                .set('month', 0)
                .set('date', 1)
                .set('second', 0)
                .set('millisecond', 0);
            const filteredEvents = this.events.filter((event) => {
                if (!event || event.day !== day) {
                    console.log(
                        `Event ID ${event?.id || 'undefined'} skipped: day=${event?.day || 'undefined'} does not match ${day}`
                    );
                    return false;
                }
                const eventStart = dayjs(event.start)
                    .set('year', 1970)
                    .set('month', 0)
                    .set('date', 1)
                    .set('second', 0)
                    .set('millisecond', 0);
                const startsInSlot = eventStart.isSame(slotStartTime, 'minute');
                console.log(
                    `Checking event ID ${event.id}: day=${event.day}, start=${eventStart.format('HH:mm')}, slot=${slotStartTime.format('HH:mm')}, startsInSlot=${startsInSlot}`
                );
                return startsInSlot;
            });
            console.log(
                `Events for ${day} at ${slotStartTime.format('HH:mm')}:`,
                filteredEvents
            );
            return filteredEvents;
        },
        getEventStyle(event) {
            if (!event || !event.start || !event.end) {
                console.log('Invalid event data:', event);
                return {};
            }

            const eventStart = dayjs(event.start);
            const eventEnd = dayjs(event.end);
            const eventDuration = eventEnd.diff(eventStart, 'minute');

            const scheduleStart = dayjs(this.scheduleStartTime);
            if (!scheduleStart.isValid()) {
                console.log(
                    'Invalid schedule start time:',
                    this.scheduleStartTime
                );
                return {};
            }

            const offsetMinutes = eventStart.diff(scheduleStart, 'minute');
            const columnWidthPx = 100;
            const subColumnWidthPx = columnWidthPx / 12; // 12 sub-columns per hour (5 minutes each)
            const eventWidthPx = Math.round(
                (eventDuration / 5) * subColumnWidthPx
            );
            const eventLeftPx = Math.round(
                (offsetMinutes / 5) * subColumnWidthPx
            );

            const isConflicting =
                event.conflicts &&
                Object.values(event.conflicts).flat().length > 0;

            console.log(`Style for event ID ${event.id}:`, {
                offsetMinutes,
                eventDuration,
                width: `${eventWidthPx}px`,
                left: `${eventLeftPx}px`,
                start: eventStart.format('HH:mm'),
                end: eventEnd.format('HH:mm'),
            });

            return {
                width: `${eventWidthPx}px`,
                left: `${eventLeftPx}px`,
                backgroundColor: isConflicting
                    ? 'rgba(255, 0, 0, 0.05)'
                    : 'rgba(0, 122, 217, 0.05)',
                border: isConflicting
                    ? '1px solid #ff0000'
                    : '1px solid #007ad9',
                borderRadius: '4px',
                padding: '5px',
                cursor: 'move',
                display: 'flex',
                flexDirection: 'column',
                justifyContent: 'center',
                fontSize: '0.875rem',
                color: 'var(--text-color)',
                position: 'absolute',
                zIndex: 20,
                top: '2px',
                bottom: '2px',
                overflow: 'visible',
                boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)',
            };
        },
        handleExport() {
            console.log('Exporting schedule');
        },
        fetchEvents() {
            console.log('Fetching events with filters:', this.localFilters);
            this.$store
                .dispatch('schedule/fetchEvents', this.localFilters)
                .then(() => {
                    console.log('Events fetched:', this.events);
                    this.checkConflicts();
                })
                .catch((error) => {
                    console.error('Error fetching events:', error);
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Erreur lors du chargement des événements.',
                        life: 3000,
                    });
                });
        },
        updateSignatures() {
            console.log('Signatures updated:', {
                directorId: this.directorId,
                technicalCoordinatorId: this.technicalCoordinatorId,
            });
            this.$store.dispatch('schedule/updateSignatures', {
                directorId: this.directorId,
                technicalCoordinatorId: this.technicalCoordinatorId,
            });
        },
    },
    created() {
        const {
            checkEventConflicts,
            formatConflict,
            calculateWeeklyHours,
            getEventDuration,
            isFormateurDisponible,
            isEspaceDisponible,
        } = useConstraints();
        this.checkEventConflicts = checkEventConflicts;
        this.formatConflict = formatConflict;
        this.calculateWeeklyHours = calculateWeeklyHours;
        this.getEventDuration = getEventDuration;
        this.isFormateurDisponible = isFormateurDisponible;
        this.isEspaceDisponible = isEspaceDisponible;

        const savedConfig = localStorage.getItem('calendarConfig');
        if (savedConfig) {
            const { visibleDays, timeSlots } = JSON.parse(savedConfig);
            this.visibleDays = this.days.filter((day) =>
                visibleDays.includes(day.value)
            );
            this.timeSlots = timeSlots.map((slot) => ({
                start: new Date(slot.start),
                end: new Date(slot.end),
            }));
        } else {
            this.visibleDays = this.days.filter((day) =>
                (
                    this.$store.state.schedule.calendarConfig.visibleDays || [
                        'monday',
                        'tuesday',
                        'wednesday',
                        'thursday',
                        'friday',
                        'saturday',
                    ]
                ).includes(day.value)
            );
            this.timeSlots = [
                {
                    start: new Date(1970, 0, 1, 8, 0),
                    end: new Date(1970, 0, 1, 9, 0),
                },
                {
                    start: new Date(1970, 0, 1, 9, 0),
                    end: new Date(1970, 0, 1, 10, 0),
                },
                {
                    start: new Date(1970, 0, 1, 10, 0),
                    end: new Date(1970, 0, 1, 11, 0),
                },
                {
                    start: new Date(1970, 0, 1, 11, 0),
                    end: new Date(1970, 0, 1, 12, 0),
                },
                {
                    start: new Date(1970, 0, 1, 12, 0),
                    end: new Date(1970, 0, 1, 13, 0),
                },
                {
                    start: new Date(1970, 0, 1, 13, 0),
                    end: new Date(1970, 0, 1, 14, 0),
                },
                {
                    start: new Date(1970, 0, 1, 14, 0),
                    end: new Date(1970, 0, 1, 15, 0),
                },
                {
                    start: new Date(1970, 0, 1, 15, 0),
                    end: new Date(1970, 0, 1, 16, 0),
                },
                {
                    start: new Date(1970, 0, 1, 16, 0),
                    end: new Date(1970, 0, 1, 17, 0),
                },
                {
                    start: new Date(1970, 0, 1, 17, 0),
                    end: new Date(1970, 0, 1, 18, 0),
                },
            ];
        }
    },
    async mounted() {
        console.log('Component mounted, loading initial data...');
        await this.$store
            .dispatch('schedule/loadInitialData')
            .catch((error) => {
                console.error('Error loading initial data:', error);
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des données initiales.',
                    life: 3000,
                });
            });
        if (this.specialites.length > 0) {
            this.localFilters.specialiteId = this.specialites[0].id;
            if (this.filteredGroupes.length > 0) {
                this.localFilters.groupeId = this.filteredGroupes[0].id;
                if (this.filteredPhases.length > 0) {
                    this.localFilters.phaseId = this.filteredPhases[0].id;
                }
            }
            this.emitFilters();
        }
        this.fetchEvents();
    },
};
</script>

<style scoped>
.schedule-table {
    border-collapse: collapse;
    min-width: 100%;
    position: relative;
}

.day-column {
    width: 120px;
    text-align: center;
    vertical-align: middle;
    background-color: var(--surface-50);
    border: 1px solid var(--surface-border);
    font-weight: 500;
    color: var(--text-color);
}

.time-column {
    width: 100px;
    text-align: center;
    background-color: var(--surface-50) !important;
    border: 1px solid var(--surface-border);
    font-size: 0.875rem;
    color: var(--text-color);
    position: relative;
    height: 80px;
}

.sub-columns {
    display: flex;
    height: 4px;
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
}

.sub-column {
    flex: 1;
    border-right: 1px solid var(--surface-200);
}

.sub-column:last-child {
    border-right: none;
}

.event-cell {
    height: 75px;
    border: 1px solid var(--surface-border);
    position: relative;
    cursor: pointer;
    background-color: var(--surface-ground);
    overflow: visible;
}

.event-container {
    position: relative;
    height: 100%;
}

.event-box {
    position: absolute;
    top: 2px;
    bottom: 2px;
    z-index: 10 !important;
    background-color: rgba(0, 122, 217, 0.05) !important;
    color: var(--text-color) !important;
}

.event-box:hover {
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
}

.event-content {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    text-overflow: ellipsis;
}

.event-content > div {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.resize-handle {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 6px;
    background: transparent;
    cursor: ew-resize;
    z-index: 30;
}

.resize-left {
    left: -3px;
}

.resize-right {
    right: -3px;
}

.resize-handle:hover {
    background: rgba(0, 0, 0, 0.2);
}

.vertical-center {
    vertical-align: middle;
}

.conflicts-list {
    max-height: 200px;
    overflow-y: auto;
}

.conflicts-list li {
    border-left: 3px solid var(--red-500);
    border-radius: 0 4px 4px 0;
    color: var(--text-color);
}

.progress-complete .p-progressbar-value {
    background-color: var(--green-500);
}

.progress-high .p-progressbar-value {
    background-color: var(--green-400);
}

.progress-medium .p-progressbar-value {
    background-color: var(--yellow-500);
}

.progress-low .p-progressbar-value {
    background-color: var(--orange-500);
}

.progress-very-low .p-progressbar-value {
    background-color: var(--red-500);
}

@media (max-width: 768px) {
    .day-column,
    .time-column {
        font-size: 0.75rem;
    }

    .event-box {
        font-size: 0.75rem;
    }

    .resize-handle {
        width: 8px;
    }

    .resize-left {
        left: -4px;
    }

    .resize-right {
        right: -4px;
    }
}
</style>
