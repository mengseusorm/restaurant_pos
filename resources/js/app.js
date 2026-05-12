/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import {createApp} from 'vue';
import DefaultComponent from "./components/DefaultComponent";
import router from './router';
import store from './store';
import axios from 'axios';
import i18n from "./i18n";

import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

import VueSimpleAlert from "vue3-simple-alert";

import VueNextSelect from 'vue-next-select';
import 'vue-next-select/dist/index.css';

import ENV from './config/env';
// import { ElDatePicker, ElSwitch } from 'element-plus';
// import ElementPlus from 'element-plus';
// import 'element-plus/dist/index.css';
// import 'element-plus/es/components/date-picker/style/css';
// import 'element-plus/es/components/switch/style/css';
import internetConnectivityService from './services/internetConnectivityService';
import globalComputedMixin from './mixins/globalComputedMixin';

/* Start tooltip alert code */
const options = {
    timeout: 2000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    closeButton: "button",
    icon: true,
    rtl: false
};
/* End tooltip alert code */


/* Start axios code*/
const API_URL = ENV.API_URL;
const API_KEY = ENV.API_KEY;

axios.defaults.baseURL = API_URL + '/api';
axios.interceptors.request.use(
    config => {
        config.headers['x-api-key'] = API_KEY;
        let language = 'en';
        let token = '';
        
        // Try to get language from localStorage
        const languagePreference = localStorage.getItem('language_code');
        if (languagePreference) {
            language = languagePreference;
        } else if (localStorage.getItem('vuex')) {
            const vuex = JSON.parse(localStorage.getItem('vuex'));
            language = vuex.globalState && vuex.globalState.lists ? vuex.globalState.lists.language_code : 'en';
            token = vuex.auth ? vuex.auth.authToken : null;
        }
        
        if (localStorage.getItem('vuex')) {
            const vuex = JSON.parse(localStorage.getItem('vuex'));
            token = vuex.auth ? vuex.auth.authToken : null;
        }
        
        config.headers['Authorization'] = token ? `Bearer ${token}` : '';
        config.headers['x-localization'] = language;
        return config;
    },
    error => Promise.reject(error),
);

// Add response interceptor to handle network errors
axios.interceptors.response.use(
    response => response,
    error => {
        // Check if it's a network error
        if (!error.response && (error.code === 'NETWORK_ERROR' || error.message === 'Network Error')) {
            // Trigger connectivity check
            internetConnectivityService.forceCheck();

            // Enhance error message
            error.message = 'Network connection error. Please check your internet connection.';
        }
        return Promise.reject(error);
    }
);
/* End axios code */

// Initialize language code in localStorage if not already set
if (!localStorage.getItem('language_code')) {
    localStorage.setItem('language_code', i18n.global.locale.value || 'en');
}

const app = createApp({});
app.component('default-component', DefaultComponent);
app.component('vue-select', VueNextSelect)
// app.component('el-date-picker', ElDatePicker);
// app.component('el-switch', ElSwitch);
// app.use(ElementPlus);
app.mixin(globalComputedMixin)
app.use(router)
app.use(store)
app.use(VueSimpleAlert)
app.use(Toast, options)
app.use(i18n)
app.mount('#app');
