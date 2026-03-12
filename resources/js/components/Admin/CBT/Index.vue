<template>
    <v-container fluid>
        <v-card class="mb-6" elevation="2">
            <v-card-title class="d-flex align-center py-4 px-6">
                <v-icon color="primary" class="mr-3">mdi-laptop-account</v-icon>
                <span class="text-h5">Manajemen CBT / Ujian ({{ quizzes.length }})</span>
                <v-spacer></v-spacer>
                <v-btn icon color="grey" class="mr-2" @click="loadQuizzes" :loading="loading" title="Refresh">
                    <v-icon>mdi-refresh</v-icon>
                </v-btn>
                <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog">
                    Tambah Ujian
                </v-btn>
            </v-card-title>

            <v-data-table
                :headers="headers"
                :items="quizzes"
                :loading="loading"
                class="elevation-0"
            >
                <template v-slot:item.index="{ index }">
                    {{ index + 1 }}
                </template>
                <template v-slot:item.start_time="{ item }">
                    {{ formatDate(item.start_time) }}
                </template>
                <template v-slot:item.end_time="{ item }">
                    {{ formatDate(item.end_time) }}
                </template>
                <template v-slot:item.is_active="{ item }">
                    <v-chip
                        :color="item.is_active ? 'success' : 'grey'"
                        size="small"
                    >
                        {{ item.is_active ? 'Aktif' : 'Non-Aktif' }}
                    </v-chip>
                </template>
                <template v-slot:item.actions="{ item }">
                    <v-btn
                        icon
                        size="small"
                        color="info"
                        class="mr-2"
                        @click="manageQuestions(item)"
                        title="Kelola Soal"
                    >
                        <v-icon>mdi-format-list-checks</v-icon>
                    </v-btn>
                    <v-btn
                        icon
                        size="small"
                        color="warning"
                        class="mr-2"
                        @click="editItem(item)"
                    >
                        <v-icon>mdi-pencil</v-icon>
                    </v-btn>
                    <v-btn
                        icon
                        size="small"
                        color="error"
                        @click="deleteItem(item)"
                    >
                        <v-icon>mdi-delete</v-icon>
                    </v-btn>
                </template>
            </v-data-table>
        </v-card>

        <!-- Quiz Dialog -->
        <v-dialog v-model="dialog" max-width="600px">
            <v-card>
                <v-card-title>
                    <span class="text-h5">{{ formTitle }}</span>
                </v-card-title>

                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col cols="12" sm="6">
                                <v-select
                                    v-model="editedItem.class_id"
                                    :items="classes"
                                    item-title="name"
                                    item-value="id"
                                    label="Kelas"
                                    variant="outlined"
                                    required
                                ></v-select>
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-select
                                    v-model="editedItem.subject_id"
                                    :items="subjects"
                                    item-title="name"
                                    item-value="id"
                                    label="Mata Pelajaran"
                                    variant="outlined"
                                    required
                                ></v-select>
                            </v-col>
                            <v-col cols="12">
                                <v-text-field
                                    v-model="editedItem.title"
                                    label="Judul Ujian"
                                    variant="outlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-textarea
                                    v-model="editedItem.description"
                                    label="Deskripsi"
                                    variant="outlined"
                                    rows="3"
                                ></v-textarea>
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="editedItem.duration_minutes"
                                    label="Durasi (Menit)"
                                    type="number"
                                    variant="outlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-switch
                                    v-model="editedItem.is_active"
                                    label="Status Aktif"
                                    color="success"
                                ></v-switch>
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="editedItem.start_time"
                                    label="Waktu Mulai"
                                    type="datetime-local"
                                    variant="outlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="editedItem.end_time"
                                    label="Waktu Selesai"
                                    type="datetime-local"
                                    variant="outlined"
                                ></v-text-field>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue-darken-1" variant="text" @click="close">
                        Batal
                    </v-btn>
                    <v-btn color="blue-darken-1" variant="text" @click="save" :loading="saving">
                        Simpan
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Questions Drawer/Dialog placeholder -->
        <question-manager
            v-if="selectedQuiz"
            v-model="questionsDialog"
            :quiz="selectedQuiz"
            @close="closeQuestions"
        ></question-manager>

    </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useAlert } from '../../../composables/useAlert';
import QuestionManager from './Questions.vue';

const { showSuccess, showError, showConfirm } = useAlert();

const quizzes = ref([]);
const classes = ref([]);
const subjects = ref([]);
const loading = ref(false);
const saving = ref(false);
const dialog = ref(false);
const questionsDialog = ref(false);
const selectedQuiz = ref(null);
const editedIndex = ref(-1);

const editedItem = ref({
    class_id: null,
    subject_id: null,
    title: '',
    description: '',
    duration_minutes: 60,
    start_time: '',
    end_time: '',
    is_active: true
});

const defaultItem = {
    class_id: null,
    subject_id: null,
    title: '',
    description: '',
    duration_minutes: 60,
    start_time: '',
    end_time: '',
    is_active: true
};

const headers = [
    { title: 'No.', key: 'index', sortable: false, width: '50px' },
    { title: 'Judul Ujian', align: 'start', key: 'title' },
    { title: 'Kelas', key: 'class.name' },
    { title: 'Mata Pelajaran', key: 'subject.name' },
    { title: 'Durasi', key: 'duration_minutes' },
    { title: 'Waktu Mulai', key: 'start_time' },
    { title: 'Waktu Selesai', key: 'end_time' },
    { title: 'Status', key: 'is_active' },
    { title: 'Aksi', key: 'actions', sortable: false },
];

const formTitle = computed(() => {
    return editedIndex.value === -1 ? 'Tambah Ujian Baru' : 'Edit Ujian';
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('id-ID');
};

const loadQuizzes = async () => {
    loading.value = true;
    try {
        const response = await axios.get('api/admin/quizzes');
        if (response.data.success) {
            quizzes.value = response.data.data;
        }
    } catch (error) {
        showError('Gagal memuat data ujian');
    } finally {
        loading.value = false;
    }
};

const openDialog = () => {
    editedIndex.value = -1;
    editedItem.value = Object.assign({}, defaultItem);
    dialog.value = true;
};

const editItem = (item) => {
    editedIndex.value = quizzes.value.indexOf(item);
    
    // Format dates for datetime-local input (YYYY-MM-DDTHH:mm)
    const formatForInput = (dateStr) => {
        const d = new Date(dateStr);
        return new Date(d.getTime() - (d.getTimezoneOffset() * 60000)).toISOString().slice(0, 16);
    };

    editedItem.value = Object.assign({}, item);
    editedItem.value.start_time = formatForInput(item.start_time);
    editedItem.value.end_time = formatForInput(item.end_time);
    editedItem.value.is_active = Boolean(item.is_active); // Ensure boolean
    
    dialog.value = true;
};

const deleteItem = async (item) => {
    const confirmed = await showConfirm('Apakah Anda yakin ingin menghapus ujian ini?');
    if (confirmed) {
        try {
            await axios.delete(`/api/admin/quizzes/${item.id}`);
            quizzes.value = quizzes.value.filter(q => q.id !== item.id);
            showSuccess('Ujian berhasil dihapus');
        } catch (error) {
            if (error.response && error.response.status === 404) {
                showError('Ujian tidak ditemukan', 'Data mungkin sudah dihapus sebelumnya.');
                loadQuizzes();
            } else {
                showError('Gagal menghapus ujian');
            }
        }
    }
};

const loadClasses = async () => {
    try {
        const response = await axios.get('api/admin/classes');
        if (response.data.success) {
            classes.value = response.data.data;
        }
    } catch (error) {
        console.error('Error loading classes:', error);
    }
};

const loadSubjects = async () => {
    try {
        const response = await axios.get('api/admin/subjects');
        if (response.data.success) {
            subjects.value = response.data.data;
        }
    } catch (error) {
        console.error('Error loading subjects:', error);
    }
};

const save = async () => {
    try {
        saving.value = true;
        if (editedIndex.value > -1) {
            const response = await axios.put(`/api/admin/quizzes/${editedItem.value.id}`, editedItem.value);
            Object.assign(quizzes.value[editedIndex.value], response.data.data);
            showSuccess('Ujian berhasil diperbarui');
        } else {
            const response = await axios.post('api/admin/quizzes', editedItem.value);
            quizzes.value.push(response.data.data);
            showSuccess('Ujian berhasil ditambahkan');
        }
        close();
    } catch (error) {
        showError(error.response?.data?.message || 'Gagal menyimpan data ujian');
        console.error(error);
    } finally {
        saving.value = false;
    }
};

const close = () => {
    dialog.value = false;
    setTimeout(() => {
        editedItem.value = Object.assign({}, defaultItem);
        editedIndex.value = -1;
    }, 300);
};

const manageQuestions = (item) => {
    selectedQuiz.value = item;
    questionsDialog.value = true;
};

const closeQuestions = () => {
    questionsDialog.value = false;
    selectedQuiz.value = null;
};

onMounted(() => {
    loadQuizzes();
    loadClasses();
    loadSubjects();
});
</script>
