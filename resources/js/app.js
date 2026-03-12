import './bootstrap';
import { createApp } from 'vue';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';
import router from './router';
import App from './App.vue';

// Vuetify configuration
const vuetify = createVuetify({
    components,
    directives,
    theme: {
        defaultTheme: 'light',
        themes: {
            light: {
                colors: {
                    primary: '#1976D2',
                    secondary: '#424242',
                    accent: '#82B1FF',
                    error: '#FF5252',
                    info: '#2196F3',
                    success: '#4CAF50',
                    warning: '#FFC107',
                },
            },
            dark: {
                colors: {
                    background: '#0f172a',
                    surface: '#1e293b',
                    primary: '#3b82f6',
                    secondary: '#64748b',
                    accent: '#f43f5e',
                    error: '#ef4444',
                    info: '#3b82f6',
                    success: '#10b981',
                    warning: '#f59e0b',
                },
            },
        },
    },
});

// Create Vue app
const app = createApp(App);

app.use(vuetify);
app.use(router);

app.mount('#app');
