<template>
    <v-container fluid class="pa-4 pa-md-6">
        <div class="mb-6">
            <h1 class="text-h4 font-weight-bold mb-1">Nilai Saya</h1>
            <p class="text-subtitle-1 text-grey">Pantau perkembangan hasil belajar Anda</p>
        </div>

        <v-card rounded="xl" elevation="2">
            <v-data-table
                :headers="headers"
                :items="grades"
                :loading="loading"
                class="elevation-0"
            >
                <template v-slot:item.index="{ index }">
                    {{ index + 1 }}
                </template>
                <template v-slot:item.score="{ item }">
                    <v-chip
                        :color="getGradeColor(item.score)"
                        variant="flat"
                        size="small"
                        class="font-weight-bold"
                        rounded="lg"
                    >
                        {{ item.score }}
                    </v-chip>
                </template>
                <template v-slot:item.note="{ item }">
                    <span class="text-caption text-grey">{{ item.note || '-' }}</span>
                </template>
                <template v-slot:no-data>
                    <div class="pa-12 text-center text-grey">
                        <v-icon size="64" class="mb-4">mdi-chart-areaspline-variant</v-icon>
                        <div class="text-h6">Belum ada nilai yang diinput</div>
                        <p>Tetap semangat belajar, hasil tidak akan mengkhianati usaha!</p>
                    </div>
                </template>
            </v-data-table>
        </v-card>
    </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const grades = ref([]);
const loading = ref(false);

const headers = [
    { title: 'No.', key: 'index', sortable: false, width: '50px' },
    { title: 'Mata Pelajaran', key: 'subject.name' },
    { title: 'Kategori', key: 'category' },
    { title: 'Nilai', key: 'score', align: 'center' },
    { title: 'Catatan Guru', key: 'note' },
];

const fetchGrades = async () => {
    loading.value = true;
    try {
        const response = await axios.get('api/siswa/grades');
        if (response.data.success) {
            grades.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching grades:', error);
    } finally {
        loading.value = false;
    }
};

const getGradeColor = (score) => {
    if (score >= 80) return 'success';
    if (score >= 70) return 'primary';
    if (score >= 60) return 'warning';
    return 'error';
};

onMounted(fetchGrades);
</script>
