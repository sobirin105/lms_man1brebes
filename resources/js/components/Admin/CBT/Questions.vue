<template>
    <v-dialog :model-value="modelValue" fullscreen transition="dialog-bottom-transition" persistent>
        <v-card color="grey-lighten-4">
            <v-toolbar color="primary" elevation="4">
                <v-btn icon @click="$emit('close')">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
                <v-toolbar-title class="font-weight-bold">Kelola Soal: {{ quiz.title }}</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-toolbar-items>
                    <v-btn variant="text" @click="$emit('close')" prepend-icon="mdi-check">
                        Selesai
                    </v-btn>
                </v-toolbar-items>
            </v-toolbar>

            <v-container fluid class="pa-4">
                <v-row>
                    <!-- Form Tambah/Edit Soal (Sticky on desktop) -->
                    <v-col cols="12" md="4">
                        <v-card class="mb-4 sticky-form" elevation="3" border>
                            <v-card-title class="bg-primary-lighten-5 py-3 d-flex align-center">
                                <v-icon start :color="isEditing ? 'warning' : 'primary'">
                                    {{ isEditing ? 'mdi-pencil-box' : 'mdi-plus-box' }}
                                </v-icon>
                                <span class="font-weight-black">{{ isEditing ? 'Edit Soal' : 'Tambah Soal Baru' }}</span>
                                <v-spacer></v-spacer>
                                <v-btn v-if="isEditing" icon size="x-small" variant="tonal" color="grey" @click="resetForm">
                                    <v-icon>mdi-close</v-icon>
                                </v-btn>
                            </v-card-title>
                            
                            <v-divider></v-divider>

                            <v-card-text class="pt-4">
                                <v-form ref="form" @submit.prevent="saveQuestion">
                                    <v-textarea
                                        v-model="questionForm.question_text"
                                        label="Pertanyaan"
                                        variant="outlined"
                                        rows="4"
                                        persistent-placeholder
                                        placeholder="Tuliskan pertanyaan di sini..."
                                        :rules="[v => !!v || 'Pertanyaan wajib diisi']"
                                        class="mb-2"
                                    ></v-textarea>

                                    <!-- Pilih jumlah opsi -->
                                    <v-select
                                        v-model="jumlahOpsi"
                                        :items="pilihanJumlahOpsi"
                                        label="Jumlah Pilihan Jawaban"
                                        variant="outlined"
                                        density="compact"
                                        prepend-inner-icon="mdi-format-list-numbered"
                                        @update:model-value="onJumlahOpsiChange"
                                        class="mb-2"
                                    ></v-select>

                                    <v-divider class="mb-4"></v-divider>

                                    <!-- Opsi A -->
                                    <v-text-field
                                        v-model="questionForm.option_a"
                                        label="Opsi A"
                                        prepend-inner-icon="mdi-alpha-a-circle"
                                        variant="outlined"
                                        density="compact"
                                        class="mb-1"
                                        :rules="[v => !!v || 'Wajib diisi']"
                                    ></v-text-field>

                                    <!-- Opsi B -->
                                    <v-text-field
                                        v-model="questionForm.option_b"
                                        label="Opsi B"
                                        prepend-inner-icon="mdi-alpha-b-circle"
                                        variant="outlined"
                                        density="compact"
                                        class="mb-1"
                                        :rules="[v => !!v || 'Wajib diisi']"
                                    ></v-text-field>

                                    <!-- Opsi C -->
                                    <v-text-field
                                        v-if="jumlahOpsi >= 3"
                                        v-model="questionForm.option_c"
                                        label="Opsi C"
                                        prepend-inner-icon="mdi-alpha-c-circle"
                                        variant="outlined"
                                        density="compact"
                                        class="mb-1"
                                        :rules="jumlahOpsi >= 3 ? [v => !!v || 'Wajib diisi'] : []"
                                    ></v-text-field>

                                    <!-- Opsi D -->
                                    <v-text-field
                                        v-if="jumlahOpsi >= 4"
                                        v-model="questionForm.option_d"
                                        label="Opsi D"
                                        prepend-inner-icon="mdi-alpha-d-circle"
                                        variant="outlined"
                                        density="compact"
                                        class="mb-1"
                                        :rules="jumlahOpsi >= 4 ? [v => !!v || 'Wajib diisi'] : []"
                                    ></v-text-field>

                                    <!-- Opsi E -->
                                    <v-text-field
                                        v-if="jumlahOpsi >= 5"
                                        v-model="questionForm.option_e"
                                        label="Opsi E"
                                        prepend-inner-icon="mdi-alpha-e-circle"
                                        variant="outlined"
                                        density="compact"
                                        class="mb-1"
                                        :rules="jumlahOpsi >= 5 ? [v => !!v || 'Wajib diisi'] : []"
                                    ></v-text-field>

                                    <v-select
                                        v-model="questionForm.correct_answer"
                                        :items="kunciJawabanOptions"
                                        label="Kunci Jawaban"
                                        variant="outlined"
                                        item-title="text"
                                        item-value="value"
                                        class="mt-4"
                                        prepend-inner-icon="mdi-check-circle"
                                        :rules="[v => !!v || 'Pilih kunci jawaban']"
                                    ></v-select>

                                    <v-btn
                                        type="submit"
                                        :color="isEditing ? 'warning' : 'primary'"
                                        block
                                        size="large"
                                        class="mt-4 font-weight-bold"
                                        :loading="saving"
                                        elevation="2"
                                    >
                                        {{ isEditing ? 'Update Soal' : 'Simpan Soal' }}
                                    </v-btn>
                                    
                                    <v-btn
                                        v-if="isEditing"
                                        color="grey-darken-1"
                                        variant="outlined"
                                        block
                                        class="mt-3"
                                        @click="resetForm"
                                    >
                                        Batal & Tambah Soal Baru
                                    </v-btn>
                                </v-form>
                            </v-card-text>
                        </v-card>
                    </v-col>

                    <!-- Daftar Soal -->
                    <v-col cols="12" md="8">
                        <div class="d-flex align-center justify-space-between mb-4 px-2">
                            <h3 class="text-h6 font-weight-bold d-flex align-center">
                                <v-icon start color="primary">mdi-format-list-bulleted</v-icon>
                                Daftar Soal ({{ questions.length }})
                            </h3>
                            <v-btn 
                                color="success" 
                                prepend-icon="mdi-plus" 
                                variant="elevated" 
                                v-if="isEditing"
                                @click="resetForm"
                            >
                                Tambah Soal Baru
                            </v-btn>
                        </div>

                        <v-alert
                            v-if="questions.length === 0"
                            type="info"
                            variant="tonal"
                            class="mb-4"
                            border="start"
                        >
                            Belum ada soal untuk ujian ini. Silakan gunakan form di samping untuk menambah soal.
                        </v-alert>

                        <v-card
                            v-for="(q, index) in questions"
                            :key="q.id"
                            class="mb-4 question-card"
                            elevation="2"
                            :class="{ 'border-primary': editingId === q.id }"
                        >
                            <v-card-title class="pa-4 bg-white">
                                <div class="d-flex align-start">
                                    <v-chip color="primary" variant="flat" size="small" class="mr-3 mt-1 font-weight-bold">
                                        {{ index + 1 }}
                                    </v-chip>
                                    <span class="text-subtitle-1 font-weight-medium flex-grow-1" style="white-space: normal; line-height: 1.5;">
                                        {{ q.question_text }}
                                    </span>
                                </div>
                            </v-card-title>

                            <v-divider></v-divider>

                            <v-card-text class="bg-white">
                                <v-list density="compact" class="pa-0">
                                    <v-list-item 
                                        v-for="opt in getActiveOptions(q)" 
                                        :key="opt.key"
                                        :class="{'bg-success-lighten-5 rounded': q.correct_answer === opt.key}"
                                        class="mb-1"
                                    >
                                        <template v-slot:prepend>
                                            <v-icon 
                                                :icon="q.correct_answer === opt.key ? 'mdi-check-circle' : `mdi-alpha-${opt.key}-circle`" 
                                                :color="q.correct_answer === opt.key ? 'success' : 'grey-darken-1'"
                                            ></v-icon>
                                        </template>
                                        <span :class="{'text-success font-weight-bold': q.correct_answer === opt.key}">
                                            {{ opt.label }}. {{ opt.text }}
                                        </span>
                                    </v-list-item>
                                </v-list>
                            </v-card-text>

                            <v-divider></v-divider>

                            <v-card-actions class="pa-3 bg-grey-lighten-5">
                                <v-spacer></v-spacer>
                                <v-btn
                                    size="small"
                                    variant="outlined"
                                    color="primary"
                                    prepend-icon="mdi-pencil"
                                    @click="editQuestion(q)"
                                    :disabled="saving"
                                >
                                    Edit
                                </v-btn>
                                <v-btn
                                    size="small"
                                    variant="outlined"
                                    color="error"
                                    prepend-icon="mdi-delete"
                                    class="ml-2"
                                    @click="deleteQuestion(q)"
                                    :disabled="saving"
                                >
                                    Hapus
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-card>
    </v-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { useAlert } from '../../../composables/useAlert';

const props = defineProps({
    modelValue: Boolean,
    quiz: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['update:modelValue', 'close']);

const { showSuccess, showError, showConfirm } = useAlert();

const questions = ref([]);
const loading = ref(false);
const saving = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const form = ref(null);

const jumlahOpsi = ref(4);
const pilihanJumlahOpsi = [
    { title: '2 Pilihan (A - B)', value: 2 },
    { title: '3 Pilihan (A - C)', value: 3 },
    { title: '4 Pilihan (A - D)', value: 4 },
    { title: '5 Pilihan (A - E)', value: 5 },
];

const kunciJawabanOptions = computed(() => {
    const semua = [
        { text: 'A', value: 'a' },
        { text: 'B', value: 'b' },
        { text: 'C', value: 'c' },
        { text: 'D', value: 'd' },
        { text: 'E', value: 'e' },
    ];
    return semua.slice(0, jumlahOpsi.value);
});

const getActiveOptions = (q) => {
    const opts = [];
    if (q.option_a) opts.push({ key: 'a', label: 'A', text: q.option_a });
    if (q.option_b) opts.push({ key: 'b', label: 'B', text: q.option_b });
    if (q.option_c) opts.push({ key: 'c', label: 'C', text: q.option_c });
    if (q.option_d) opts.push({ key: 'd', label: 'D', text: q.option_d });
    if (q.option_e) opts.push({ key: 'e', label: 'E', text: q.option_e });
    return opts;
};

const onJumlahOpsiChange = (val) => {
    if (val < 5) questionForm.value.option_e = '';
    if (val < 4) questionForm.value.option_d = '';
    if (val < 3) questionForm.value.option_c = '';

    const aktifKeys = ['a', 'b', 'c', 'd', 'e'].slice(0, val);
    if (!aktifKeys.includes(questionForm.value.correct_answer)) {
        questionForm.value.correct_answer = null;
    }
    if (form.value) form.value.resetValidation();
};

const questionForm = ref({
    question_text: '',
    option_a: '',
    option_b: '',
    option_c: '',
    option_d: '',
    option_e: '',
    correct_answer: null
});

const loadQuestions = async () => {
    if (!props.quiz?.id) return;
    loading.value = true;
    try {
        const response = await axios.get(`/api/admin/quizzes/${props.quiz.id}/questions`);
        questions.value = response.data.data;
    } catch (error) {
        showError('Gagal memuat soal', 'Silakan refresh halaman.');
    } finally {
        loading.value = false;
    }
};

const saveQuestion = async () => {
    const { valid } = await form.value.validate();
    if (!valid) return;

    saving.value = true;
    try {
        const payload = {
            question_text: questionForm.value.question_text,
            option_a: questionForm.value.option_a,
            option_b: questionForm.value.option_b,
            option_c: jumlahOpsi.value >= 3 ? (questionForm.value.option_c || null) : null,
            option_d: jumlahOpsi.value >= 4 ? (questionForm.value.option_d || null) : null,
            option_e: jumlahOpsi.value >= 5 ? (questionForm.value.option_e || null) : null,
            correct_answer: questionForm.value.correct_answer,
        };

        let response;
        if (isEditing.value && editingId.value) {
            response = await axios.put(`/api/admin/quizzes/${props.quiz.id}/questions/${editingId.value}`, payload);
            showSuccess('Berhasil!', 'Soal berhasil diperbarui.');
        } else {
            response = await axios.post(`/api/admin/quizzes/${props.quiz.id}/questions`, payload);
            showSuccess('Berhasil!', 'Soal baru berhasil ditambahkan.');
        }

        // Segera refresh list dari server
        await loadQuestions();
        
        // Reset form ke mode "Tambah"
        resetForm();
    } catch (error) {
        console.error('Error saving question:', error);
        showError('Gagal menyimpan soal', error.response?.data?.message || 'Terjadi kesalahan sistem.');
    } finally {
        saving.value = false;
    }
};

const editQuestion = (question) => {
    isEditing.value = true;
    editingId.value = question.id;
    questionForm.value = {
        question_text: question.question_text || '',
        option_a: question.option_a || '',
        option_b: question.option_b || '',
        option_c: question.option_c || '',
        option_d: question.option_d || '',
        option_e: question.option_e || '',
        correct_answer: question.correct_answer || null,
    };
    
    // Auto-detect jumlah opsi
    if (question.option_e) jumlahOpsi.value = 5;
    else if (question.option_d) jumlahOpsi.value = 4;
    else if (question.option_c) jumlahOpsi.value = 3;
    else jumlahOpsi.value = 2;

    // Scroll form ke atas agar terlihat (terutama di mobile)
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const deleteQuestion = async (question) => {
    const confirmed = await showConfirm('Hapus Soal?', 'Soal yang dihapus tidak dapat dikembalikan.');
    if (confirmed) {
        try {
            await axios.delete(`/api/admin/quizzes/${props.quiz.id}/questions/${question.id}`);
            await loadQuestions();
            showSuccess('Terhapus!', 'Soal berhasil dihapus.');
            
            // Jika sedang mengedit soal yang dihapus, reset form
            if (editingId.value === question.id) {
                resetForm();
            }
        } catch (error) {
            if (error.response && error.response.status === 404) {
                showError('Soal tidak ditemukan', 'Mungkin sudah dihapus sebelumnya.');
                loadQuestions();
            } else {
                showError('Gagal menghapus soal');
            }
        }
    }
};

const resetForm = () => {
    isEditing.value = false;
    editingId.value = null;
    jumlahOpsi.value = 4;
    questionForm.value = {
        question_text: '',
        option_a: '',
        option_b: '',
        option_c: '',
        option_d: '',
        option_e: '',
        correct_answer: null
    };
    if (form.value) form.value.resetValidation();
};

watch(() => props.modelValue, (val) => {
    if (val) {
        loadQuestions();
        resetForm();
    }
});
</script>

<style scoped>
@media (min-width: 960px) {
    .sticky-form {
        position: sticky;
        top: 20px;
    }
}
.border-primary {
    border: 2px solid #1976D2 !important;
}
.bg-primary-lighten-5 {
    background-color: #E3F2FD;
}
.bg-success-lighten-5 {
    background-color: #E8F5E9;
}
.question-card {
    transition: all 0.2s ease-in-out;
}
.question-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}
</style>
