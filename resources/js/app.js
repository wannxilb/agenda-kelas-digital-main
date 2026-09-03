// resources/js/app.js
import './bootstrap';
import './network-loading';
import './polling';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import TomSelect from 'tom-select';

Alpine.plugin(collapse);

window.Alpine = Alpine;
window.TomSelect = TomSelect;
Alpine.start();
