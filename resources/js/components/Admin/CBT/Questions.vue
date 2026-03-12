<template>
    <v-dialog :model-value="modelValue" fullscreen transition="dialog-bottom-transition">
        <v-card>
            <v-toolbar color="primary">
                <v-btn icon @click="$emit('close')">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
                <v-toolbar-title>Kelola Soal: {{ quiz.title }}</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-toolbar-items>
                    <v-btn variant="text" @click="$emit('close')">
                        Selesai
                    </v-btn>
                </v-toolbar-items>
            </v-toolbar>

            <v-container>
                <v-row>
                    <!-- Form Tambah/Edit Soal -->
                    <v-col cols="12" md="4">
                        <v-card class="mb-4">
                            <v-card-title>{{ isEditing ? 'Edit Soal' : 'Tambah Soal Baru' }}</v-card-title>
                            <v-card-text>
                                <v-form ref="form" @submit.prevent="saveQuestion">
                                    <v-textarea
                                        v-model="questionForm.question_text"
                                        label="Pertanyaan"
                                        variant="outlined"
                                        rows="3"
                                        :rules="[v => !!v || 'Pertanyaan wajib diisi']"
                                    ></v-textarea>

                                    <!-- Pilih jumlah opsi -->
                                    <v-select
                                        v-model="jumlahOpsi"
                                        :items="pilihanJumlahOpsi"
                                        label="Jumlah Pilihan Jawaban"
                                        variant="outlined"
                                        density="compact"
                                        class="mb-2"
                                        @update:model-value="onJumlahOpsiChange"
                                    ></v-select>

                                    <!-- Opsi A (selalu tampil) -->
                                    <v-text-field
                                        v-model="questionForm.option_a"
                                        label="Opsi A"
                                        prepend-inner-icon="mdi-alpha-a-circle"
                                        variant="outlined"
                                        density="compact"
                                        class="mb-1"
                                        :rules="[v => !!v || 'Wajib diisi']"
                                    ></v-text-field>

                                    <!-- Opsi B (selalu tampil) -->
                                    <v-text-field
                                        v-model="questionForm.option_b"
                                        label="Opsi B"
                                        prepend-inner-icon="mdi-alpha-b-circle"
                                        variant="outlined"
                                        density="compact"
                                        class="mb-1"
                                        :rules="[v => !!v || 'Wajib diisi']"
                                    ></v-text-field>

                                    <!-- Opsi C (tampil jika >= 3) -->
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

                                    <!-- Opsi D (tampil jika >= 4) -->
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

                                    <!-- Opsi E (tampil jika >= 5) -->
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
                                        class="mt-2"
                                        :rules="[v => !!v || 'Pilih kunci jawaban']"
                                    ></v-select>

                                    <v-btn
                                        type="submit"
                                        color="primary"
                                        block
                                        :loading="saving"
                                    >
                                        {{ isEditing ? 'Update Soal' : 'Simpan Soal' }}
                                    </v-btn>
                                    <v-btn
                                        v-if="isEditing"
                                        color="grey"
                                        variant="text"
                                        block
                                        class="mt-2"
                                        @click="resetForm"
                                    >
                                        Batal Edit
                                    </v-btn>
                                </v-form>
                            </v-card-text>
                        </v-card>
                    </v-col>

                    <!-- Daftar Soal -->
                    <v-col cols="12" md="8">
                        <div class="d-flex align-center justify-space-between mb-4">
                            <h3 class="text-h6">Daftar Soal ({{ questions.length }})</h3>
                        </div>

                        <v-alert
                            v-if="questions.length === 0"
                            type="info"
                            variant="tonal"
                            class="mb-4"
                        >
                            Belum ada soal untuk ujian ini.
                        </v-alert>

                        <v-card
                            v-for="(q, index) in questions"
                            :key="q.id"
                            class="mb-4"
                            variant="outlined"
                        >
                            <!-- Judul soal -->
                            <v-card-title class="pa-3 pb-0">
                                <div class="d-flex align-start">
                                    <span class="font-weight-bold text-subtitle-1 mr-2 flex-shrink-0">{{ index + 1 }}.</span>
                                    <span class="text-subtitle-1" style="white-space: normal; word-break: break-word;">
                                        {{ q.question_text }}
                                    </span>
                                </div>
                            </v-card-title>

                            <!-- Tombol aksi terpisah -->
                            <div class="d-flex justify-end px-3 pb-2 pt-1 gap-2">
                                <v-btn
                                    size="small"
                                    variant="tonal"
                                    color="primary"
                                    prepend-icon="mdi-pencil"
                                    @click="editQuestion(q)"
                                >
                                    Edit
                                </v-btn>
                                <v-btn
                                    size="small"
                                    variant="tonal"
                                    color="error"
                                    prepend-icon="mdi-delete"
                                    class="ml-2"
                                    @click="deleteQuestion(q)"
                                >
                                    Hapus
                                </v-btn>
                            </div>

                            <v-divider class="mx-3"></v-divider>

                            <v-card-text>
                                <v-list density="compact">
                                    <v-list-item v-if="q.option_a" :class="{'text-success font-weight-bold': q.correct_answer === 'a'}">
                                        <template v-slot:prepend>
                                            <v-icon :icon="q.correct_answer === 'a' ? 'mdi-check-circle' : 'mdi-alpha-a-circle'" :color="q.correct_answer === 'a' ? 'success' : ''"></v-icon>
                                        </template>
                                        A. {{ q.option_a }}
                                    </v-list-item>
                                    <v-list-item v-if="q.option_b" :class="{'text-success font-weight-bold': q.correct_answer === 'b'}">
                                        <template v-slot:prepend>
                                            <v-icon :icon="q.correct_answer === 'b' ? 'mdi-check-circle' : 'mdi-alpha-b-circle'" :color="q.correct_answer === 'b' ? 'success' : ''"></v-icon>
                                        </template>
                                        B. {{ q.option_b }}
                                    </v-list-item>
                                    <v-list-item v-if="q.option_c" :class="{'text-success font-weight-bold': q.correct_answer === 'c'}">
                                        <template v-slot:prepend>
                                            <v-icon :icon="q.correct_answer === 'c' ? 'mdi-check-circle' : 'mdi-alpha-c-circle'" :color="q.correct_answer === 'c' ? 'success' : ''"></v-icon>
                                        </template>
                                        C. {{ q.option_c }}
                                    </v-list-item>
                                    <v-list-item v-if="q.option_d" :class="{'text-success font-weight-bold': q.correct_answer === 'd'}">
                                        <template v-slot:prepend>
                                            <v-icon :icon="q.correct_answer === 'd' ? 'mdi-check-circle' : 'mdi-alpha-d-circle'" :color="q.correct_answer === 'd' ? 'success' : ''"></v-icon>
                                        </template>
                                        D. {{ q.option_d }}
                                    </v-list-item>
                                    <v-list-item v-if="q.option_e" :class="{'text-success font-weight-bold': q.correct_answer === 'e'}">
                                        <template v-slot:prepend>
                                            <v-icon :icon="q.correct_answer === 'e' ? 'mdi-check-circle' : 'mdi-alpha-e-circle'" :color="q.correct_answer === 'e' ? 'success' : ''"></v-icon>
                                        </template>
                                        E. {{ q.option_e }}
                                    </v-list-item>
                                </v-list>
                            </v-card-text>
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

// Jumlah opsi yang dipilih (default 4 = A-D)
const jumlahOpsi = ref(4);
const pilihanJumlahOpsi = [
    { title: '2 Pilihan (A - B)', value: 2 },
    { title: '3 Pilihan (A - C)', value: 3 },
    { title: '4 Pilihan (A - D)', value: 4 },
    { title: '5 Pilihan (A - E)', value: 5 },
];

// Kunci jawaban yang tersedia (menyesuaikan jumlah opsi)
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

// Reset field opsi yang tidak aktif & kunci jawaban jika tidak valid
const onJumlahOpsiChange = (val) => {
    if (val < 5) questionForm.value.option_e = '';
    if (val < 4) questionForm.value.option_d = '';
    if (val < 3) questionForm.value.option_c = '';

    const aktifKeys = ['a', 'b', 'c', 'd', 'e'].slice(0, val);
    if (!aktifKeys.includes(questionForm.value.correct_answer)) {
        questionForm.value.correct_answer = null;
    }
    // Reset validasi agar error field yang hilang ikut bersih
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
    if (!props.quiz) return;
    loading.value = true;
    try {
        const response = await axios.get(`/api/admin/quizzes/${props.quiz.id}/questions`);
        questions.value = response.data.data;
    } catch (error) {
        showError('Gagal memuat soal');
    } finally {
        loading.value = false;
    }
};

const saveQuestion = async () => {
    const { valid } = await form.value.validate();
    if (!valid) return;

    saving.value = true;
    try {
        // Kirim null untuk field yang tidak aktif
        const payload = {
            question_text: questionForm.value.question_text,
            option_a: questionForm.value.option_a,
            option_b: questionForm.value.option_b,
            option_c: jumlahOpsi.value >= 3 ? questionForm.value.option_c : null,
            option_d: jumlahOpsi.value >= 4 ? questionForm.value.option_d : null,
            option_e: jumlahOpsi.value >= 5 ? questionForm.value.option_e : null,
            correct_answer: questionForm.value.correct_answer,
        };

        if (isEditing.value) {
            await axios.put(`/api/admin/quizzes/${props.quiz.id}/questions/${editingId.value}`, payload);
            showSuccess('Soal berhasil diperbarui');
        } else {
            await axios.post(`/api/admin/quizzes/${props.quiz.id}/questions`, payload);
            showSuccess('Soal berhasil ditambahkan');
        }
        await loadQuestions();
        resetForm();
    } catch (error) {
        showError('Gagal menyimpan soal');
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
    // Deteksi jumlah opsi dari data soal yang ada
    if (question.option_e) jumlahOpsi.value = 5;
    else if (question.option_d) jumlahOpsi.value = 4;
    else if (question.option_c) jumlahOpsi.value = 3;
    else jumlahOpsi.value = 2;
};

const deleteQuestion = async (question) => {
    const confirmed = await showConfirm('Hapus Soal?', 'Soal yang dihapus tidak dapat dikembalikan.');
    if (confirmed) {
        try {
            await axios.delete(`/api/admin/quizzes/${props.quiz.id}/questions/${question.id}`);
            await loadQuestions();
            showSuccess('Soal berhasil dihapus');
        } catch (error) {
            if (error.response && error.response.status === 404) {
                showError('Soal tidak ditemukan', 'Data mungkin sudah dihapus sebelumnya.');
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
