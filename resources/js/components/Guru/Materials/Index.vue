<template>
    <v-container fluid class="pa-6">
        <div class="d-flex align-center justify-space-between mb-6">
            <div>
                <h1 class="text-h4 font-weight-bold mb-1">Materi Pelajaran</h1>
                <p class="text-subtitle-1 text-grey">Kelola bahan ajar untuk siswa Anda</p>
            </div>
            <v-btn
                color="success"
                prepend-icon="mdi-plus"
                size="large"
                rounded="lg"
                elevation="2"
                @click="openDialog()"
            >
                Tambah Materi
            </v-btn>
        </div>

        <v-card rounded="xl" elevation="2">
            <v-data-table
                :headers="headers"
                :items="materials"
                :loading="loading"
                class="elevation-0"
            >
                <template v-slot:item.index="{ index }">
                    {{ index + 1 }}
                </template>
                <template v-slot:item.file_path="{ item }">
                    <v-btn
                        variant="text"
                        color="primary"
                        prepend-icon="mdi-download"
                        size="small"
                        :href="item.file_path"
                        target="_blank"
                    >
                        Unduh File
                    </v-btn>
                </template>
                <template v-slot:item.actions="{ item }">
                    <v-btn icon color="info" variant="text" size="small" @click="openDialog(item)">
                        <v-icon>mdi-pencil</v-icon>
                    </v-btn>
                    <v-btn icon color="error" variant="text" size="small" @click="confirmDelete(item)">
                        <v-icon>mdi-delete</v-icon>
                    </v-btn>
                </template>
            </v-data-table>
        </v-card>

        <!-- Dialog Formulir -->
        <v-dialog v-model="dialog" max-width="600px" persistent>
            <v-card rounded="xl">
                <v-card-title class="pa-6 d-flex justify-space-between align-center">
                    <span class="text-h5 font-weight-bold">
                        {{ editedItem.id ? 'Edit Materi' : 'Tambah Materi' }}
                    </span>
                    <v-btn icon variant="text" @click="closeDialog">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>

                <v-divider></v-divider>

                <v-card-text class="pa-6">
                    <v-form ref="form" v-model="valid">
                        <v-text-field
                            v-model="editedItem.title"
                            label="Judul Materi"
                            placeholder="Contoh: Matematika Bab 1 - Aljabar"
                            variant="outlined"
                            rounded="lg"
                            required
                            :rules="[v => !!v || 'Judul wajib diisi']"
                            class="mb-4"
                        ></v-text-field>

                        <v-select
                            v-model="editedItem.class_id"
                            :items="classes"
                            item-title="name"
                            item-value="id"
                            label="Kelas"
                            variant="outlined"
                            rounded="lg"
                            required
                            :rules="[v => !!v || 'Kelas wajib diisi']"
                            class="mb-4"
                        ></v-select>

                        <v-select
                            v-model="editedItem.subject_id"
                            :items="subjects"
                            item-title="name"
                            item-value="id"
                            label="Mata Pelajaran"
                            variant="outlined"
                            rounded="lg"
                            required
                            :rules="[v => !!v || 'Mata pelajaran wajib diisi']"
                            class="mb-4"
                        ></v-select>

                        <v-textarea
                            v-model="editedItem.description"
                            label="Deskripsi (Opsional)"
                            variant="outlined"
                            rounded="lg"
                            rows="3"
                            class="mb-4"
                        ></v-textarea>

                        <v-file-input
                            v-model="editedItem.file"
                            label="Upload File Materi"
                            variant="outlined"
                            rounded="lg"
                            prepend-icon="mdi-paperclip"
                            class="mb-2"
                        ></v-file-input>
                    </v-form>
                </v-card-text>

                <v-card-actions class="pa-6 pt-0">
                    <v-spacer></v-spacer>
                    <v-btn variant="text" rounded="lg" @click="closeDialog" :disabled="saving">Batal</v-btn>
                    <v-btn color="success" variant="flat" rounded="lg" @click="save" :loading="saving" :disabled="!valid">Simpan</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const materials = ref([]);
const classes = ref([]);
const subjects = ref([]);
const loading = ref(false);
const dialog = ref(false);
const saving = ref(false);
const valid = ref(false);
const form = ref(null);

const editedItem = ref({
    id: null,
    title: '',
    class_id: null,
    subject_id: null,
    description: '',
    file: null
});

const defaultItem = {
    id: null,
    title: '',
    class_id: null,
    subject_id: null,
    description: '',
    file: null
};

const headers = [
    { title: 'No.', key: 'index', sortable: false, width: '50px' },
    { title: 'Judul', key: 'title' },
    { title: 'Kelas', key: 'class.name' },
    { title: 'Mapel', key: 'subject.name' },
    { title: 'File', key: 'file_path', sortable: false },
    { title: 'Aksi', key: 'actions', sortable: false, align: 'end' },
];

const fetchMaterials = async () => {
    loading.value = true;
    try {
        const response = await axios.get('api/guru/materials');
        if (response.data.success) {
            materials.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching materials:', error);
    } finally {
        loading.value = false;
    }
};

const fetchInitialData = async () => {
    try {
        const [classRes, subjectRes] = await Promise.all([
            axios.get('api/guru/classes'),
            axios.get('api/guru/subjects')
        ]);
        classes.value = classRes.data.data;
        subjects.value = subjectRes.data.data;
    } catch (error) {
        console.error('Error fetching initial data:', error);
    }
};

const openDialog = (item = null) => {
    if (item) {
        editedItem.value = { ...item };
    } else {
        editedItem.value = { ...defaultItem };
    }
    dialog.value = true;
};

const closeDialog = () => {
    dialog.value = false;
    editedItem.value = { ...defaultItem };
};

const save = async () => {
    if (!valid.value) return;
    saving.value = true;
    try {
        const formData = new FormData();
        Object.keys(editedItem.value).forEach(key => {
            if (editedItem.value[key] !== null) {
                formData.append(key, editedItem.value[key]);
            }
        });

        const url = editedItem.value.id 
            ? `/api/guru/materials/${editedItem.value.id}` 
            : 'api/guru/materials';
        
        const response = await axios({
            method: 'post', // Usually post even for update when dealing with FormData/Files
            url,
            data: formData,
            headers: { 'Content-Type': 'multipart/form-data ' }
        });

        if (response.data.success) {
            fetchMaterials();
            closeDialog();
        }
    } catch (error) {
        console.error('Error saving material:', error);
    } finally {
        saving.value = false;
    }
};

const confirmDelete = async (item) => {
    if (confirm('Apakah Anda yakin ingin menghapus materi ini?')) {
        try {
            await axios.delete(`/api/guru/materials/${item.id}`);
            fetchMaterials();
        } catch (error) {
            console.error('Error deleting material:', error);
        }
    }
};

onMounted(() => {
    fetchMaterials();
    fetchInitialData();
});
</script>
