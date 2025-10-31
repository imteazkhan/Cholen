import './bootstrap';

import { createApp } from 'vue';
import MapComponent from './components/Map.vue';

const app = createApp({});
app.component('map-component', MapComponent);
app.mount('#app');