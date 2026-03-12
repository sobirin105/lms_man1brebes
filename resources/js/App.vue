<template>
    <v-app>
        <router-view></router-view>
    </v-app>
</template>

<script setup>
import { onMounted } from 'vue';
import axios from 'axios';

onMounted(async () => {
    try {
        const response = await axios.get('/api/settings');
        if (response.data.success) {
            const settings = response.data.data;
            
            // Update Favicon
            if (settings.school_logo) {
                const link = document.querySelector("link[rel*='icon']") || document.createElement('link');
                link.type = 'image/x-icon';
                link.rel = 'shortcut icon';
                link.href = '/storage/' + settings.school_logo;
                document.getElementsByTagName('head')[0].appendChild(link);
            }

            // Update Page Title
            if (settings.app_name) {
                document.title = settings.app_name;
            }
        }
    } catch (error) {
        console.error('Error fetching global settings:', error);
    }
});
</script>

<style>
/* Global styles */
</style>
