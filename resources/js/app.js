import './bootstrap';
import '../css/app.css';
import PrimeVue from 'primevue/config';
import '@/primevue/assets/styles.scss';
import 'primeflex/primeflex.css';
import 'primeicons/primeicons.css';
import router from './router';
import { createStore } from 'vuex';
import schedule from '@/stores/modules/schedule';
import { createRouter, createWebHistory } from 'vue-router';
import isBetween from 'dayjs/plugin/isBetween';
import draggable from 'vuedraggable';
import ProgressSpinner from 'primevue/progressspinner';
// Thème PrimeVue
//import 'primevue/resources/themes/saga-blue/theme.css';
//import 'primevue/resources/themes/lara-light-indigo/theme.css';
import 'primevue/resources/themes/lara-dark-indigo/theme.css';
//import 'primevue/resources/themes/lara-dark-purple/theme.css';
//import 'primevue/resources/themes/luna-green/theme.css';
//import 'primevue/resources/themes/mdc-dark-indigo/theme.css';
//import 'primevue/resources/themes/lara-light-indigo/theme.css'; // Thème par défaut

import 'primevue/resources/primevue.min.css';

import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import DialogService from 'primevue/dialogservice';
import VueApexCharts from 'vue3-apexcharts';

import { createApp } from 'vue';
import App from './App.vue';

// Import des composants PrimeVue nécessaires
import interact from 'interactjs';
import ConfirmPopup from 'primevue/confirmpopup';
import Card from 'primevue/card';
import Tree from 'primevue/tree';
import Toolbar from 'primevue/toolbar';
import Accordion from 'primevue/accordion';
import Breadcrumb from 'primevue/breadcrumb';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Avatar from 'primevue/avatar';
import AvatarGroup from 'primevue/avatargroup';
import BadgeDirective from 'primevue/badgedirective';
import Badge from 'primevue/badge';
import Button from 'primevue/button';
import Calendar from 'primevue/calendar';
import Carousel from 'primevue/carousel';
import Chart from 'primevue/chart';
import Checkbox from 'primevue/checkbox';
import Chip from 'primevue/chip';
import Chips from 'primevue/chips';
import Column from 'primevue/column';
import ColorPicker from 'primevue/colorpicker';
import ConfirmDialog from 'primevue/confirmdialog';
import DataTable from 'primevue/datatable';
import DataView from 'primevue/dataview';
import Dialog from 'primevue/dialog';
import Divider from 'primevue/divider';
import Dropdown from 'primevue/dropdown';
import FileUpload from 'primevue/fileupload';
import Galleria from 'primevue/galleria';
import InlineMessage from 'primevue/inlinemessage';
import InputMask from 'primevue/inputmask';
import InputNumber from 'primevue/inputnumber';
import InputSwitch from 'primevue/inputswitch';
import Knob from 'primevue/knob';
import Menu from 'primevue/menu';
import Message from 'primevue/message';
import MultiSelect from 'primevue/multiselect';
import OverlayPanel from 'primevue/overlaypanel';
import Paginator from 'primevue/paginator';
import Password from 'primevue/password';
import ProgressBar from 'primevue/progressbar';
import RadioButton from 'primevue/radiobutton';
import Rating from 'primevue/rating';
import Ripple from 'primevue/ripple';
import Sidebar from 'primevue/sidebar';
import Slider from 'primevue/slider';
import SplitButton from 'primevue/splitbutton';
import SpeedDial from 'primevue/speeddial';
import SelectButton from 'primevue/selectbutton';
import StyleClass from 'primevue/styleclass';
import Skeleton from 'primevue/skeleton';
import TabMenu from 'primevue/tabmenu';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Textarea from 'primevue/textarea';
import ToggleButton from 'primevue/togglebutton';
import Tooltip from 'primevue/tooltip';
import Toast from 'primevue/toast';
import ScrollPanel from 'primevue/scrollpanel';
import Splitter from 'primevue/splitter';
import SplitterPanel from 'primevue/splitterpanel';
import Fieldset from 'primevue/fieldset';

// Import du composant FullCalendar
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import resourceTimeGridPlugin from '@fullcalendar/resource-timegrid';
import resourceTimelinePlugin from '@fullcalendar/resource-timeline';
import frLocale from '@fullcalendar/core/locales/fr';

// Import de dayjs
import dayjs from 'dayjs';
import 'dayjs/locale/fr';

// Configuration de dayjs
dayjs.locale('fr');
dayjs.extend(isBetween);

// Configuration de la locale pour PrimeVue
const primeVueLocale = {
    startsWith: 'Commence par',
    contains: 'Contient',
    notContains: 'Ne contient pas',
    endsWith: 'Se termine par',
    equals: 'Égal à',
    notEquals: 'Différent de',
    noFilter: 'Aucun filtre',
    lt: 'Inférieur à',
    lte: 'Inférieur ou égal à',
    gt: 'Supérieur à',
    gte: 'Supérieur ou égal à',
    dateIs: 'Date est',
    dateIsNot: "Date n'est pas",
    dateBefore: 'Date avant',
    dateAfter: 'Date après',
    clear: 'Effacer',
    apply: 'Appliquer',
    matchAll: 'Correspond à tous',
    matchAny: "Correspond à n'importe lequel",
    addRule: 'Ajouter une règle',
    removeRule: 'Supprimer la règle',
    accept: 'Accepter',
    reject: 'Rejeter',
    choose: 'Choisir',
    upload: 'Télécharger',
    cancel: 'Annuler',
    dayNames: [
        'Dimanche',
        'Lundi',
        'Mardi',
        'Mercredi',
        'Jeudi',
        'Vendredi',
        'Samedi',
    ],
    dayNamesShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
    dayNamesMin: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
    monthNames: [
        'Janvier',
        'Février',
        'Mars',
        'Avril',
        'Mai',
        'Juin',
        'Juillet',
        'Août',
        'Septembre',
        'Octobre',
        'Novembre',
        'Décembre',
    ],
    monthNamesShort: [
        'Jan',
        'Fév',
        'Mar',
        'Avr',
        'Mai',
        'Jun',
        'Jul',
        'Aoû',
        'Sep',
        'Oct',
        'Nov',
        'Déc',
    ],
    today: "Aujourd'hui",
    weekHeader: 'Sem',
    firstDayOfWeek: 1,
    dateFormat: 'dd/mm/yy',
    weak: 'Faible',
    medium: 'Moyen',
    strong: 'Fort',
    passwordPrompt: 'Entrez un mot de passe',
    emptyFilterMessage: 'Aucun résultat trouvé',
    emptyMessage: 'Aucune option disponible',
};

// Créez le store Vuex
const storeInstance = createStore({
    modules: {
        schedule,
    },
});

// Créez l'application Vue
const app = createApp(App);

// Configuration de FullCalendar
app.config.globalProperties.$fullCalendarPlugins = [
    dayGridPlugin,
    timeGridPlugin,
    interactionPlugin,
    resourceTimeGridPlugin,
    resourceTimelinePlugin,
];

app.config.globalProperties.$fullCalendarLocales = {
    fr: frLocale,
};

// Utilisez PrimeVue et les services
app.use(PrimeVue, {
    ripple: true,
    locale: primeVueLocale,
});
app.use(ToastService);
app.use(DialogService);
app.use(ConfirmationService);
app.use(router);
app.use(VueApexCharts);
app.use(storeInstance);

// Directives PrimeVue
app.directive('tooltip', Tooltip);
app.directive('badge', BadgeDirective);
app.directive('ripple', Ripple);
app.directive('styleclass', StyleClass);
app.component('ProgressSpinner', ProgressSpinner);
// Enregistrement des composants PrimeVue
app.component('ConfirmDialog', ConfirmDialog);
app.component('ConfirmPopup', ConfirmPopup);
app.component('Toast', Toast);
app.component('Card', Card);
app.component('DatePicker', Calendar);
app.component('ToggleSwitch', InputSwitch);
app.component('Dialog', Dialog);
app.component('Tree', Tree);
app.component('Toolbar', Toolbar);
app.component('TabView', TabView);
app.component('TabPanel', TabPanel);
app.component('Accordion', Accordion);
app.component('Avatar', Avatar);
app.component('AvatarGroup', AvatarGroup);
app.component('Badge', Badge);
app.component('Breadcrumb', Breadcrumb);
app.component('Button', Button);
app.component('SplitButton', SplitButton);
app.component('Calendar', Calendar);
app.component('Carousel', Carousel);
app.component('Chart', Chart);
app.component('Checkbox', Checkbox);
app.component('Chip', Chip);
app.component('Chips', Chips);
app.component('Column', Column);
app.component('ColorPicker', ColorPicker);
app.component('DataTable', DataTable);
app.component('DataView', DataView);
app.component('Divider', Divider);
app.component('Dropdown', Dropdown);
app.component('FileUpload', FileUpload);
app.component('Galleria', Galleria);
app.component('InlineMessage', InlineMessage);
app.component('InputMask', InputMask);
app.component('InputNumber', InputNumber);
app.component('InputSwitch', InputSwitch);
app.component('InputText', InputText);
app.component('Knob', Knob);
app.component('Menu', Menu);
app.component('Message', Message);
app.component('MultiSelect', MultiSelect);
app.component('OverlayPanel', OverlayPanel);
app.component('Paginator', Paginator);
app.component('Password', Password);
app.component('ProgressBar', ProgressBar);
app.component('RadioButton', RadioButton);
app.component('Rating', Rating);
app.component('Sidebar', Sidebar);
app.component('SelectButton', SelectButton);
app.component('SpeedDial', SpeedDial);
app.component('Slider', Slider);
app.component('Skeleton', Skeleton);
app.component('TabMenu', TabMenu);
app.component('Tag', Tag);
app.component('Textarea', Textarea);
app.component('ToggleButton', ToggleButton);
app.component('ScrollPanel', ScrollPanel);
app.component('Splitter', Splitter);
app.component('SplitterPanel', SplitterPanel);
app.component('Fieldset', Fieldset);

// Enregistrement de FullCalendar comme composant global
app.component('FullCalendar', FullCalendar);

// Montez l'application
app.mount('#app');

export default storeInstance;
