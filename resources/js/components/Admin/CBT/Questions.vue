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
                    <v-col cols="12" md="4">
                        <v-card class="mb-4">
                            <v-card-title>Tambah Soal Baru</v-card-title>
                            <v-card-text>
                                <v-form ref="form" @submit.prevent="saveQuestion">
                                    <v-textarea
                                        v-model="questionForm.question_text"
                                        label="Pertanyaan"
                                        variant="outlined"
                                        rows="3"
                                        :rules="[v => !!v || 'Pertanyaan wajib diisi']"
                                    ></v-textarea>

                                    <v-row>
                                        <v-col cols="12">
                                            <v-text-field
                                                v-model="questionForm.option_a"
                                                label="Opsi A"
                                                prepend-inner-icon="mdi-alpha-a-circle"
                                                variant="outlined"
                                                density="compact"
                                                :rules="[v => !!v || 'Wajib diisi']"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12">
                                            <v-text-field
                                                v-model="questionForm.option_b"
                                                label="Opsi B"
                                                prepend-inner-icon="mdi-alpha-b-circle"
                                                variant="outlined"
                                                density="compact"
                                                :rules="[v => !!v || 'Wajib diisi']"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12">
                                            <v-text-field
                                                v-model="questionForm.option_c"
                                                label="Opsi C"
                                                prepend-inner-icon="mdi-alpha-c-circle"
                                                variant="outlined"
                                                density="compact"
                                                :rules="[v => !!v || 'Wajib diisi']"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12">
                                            <v-text-field
                                                v-model="questionForm.option_d"
                                                label="Opsi D"
                                                prepend-inner-icon="mdi-alpha-d-circle"
                                                variant="outlined"
                                                density="compact"
                                                :rules="[v => !!v || 'Wajib diisi']"
                                            ></v-text-field>
                                        </v-col>
                                    </v-row>

                                    <v-select
                                        v-model="questionForm.correct_answer"
                                        :items="answerOptions"
                                        label="Kunci Jawaban"
                                        variant="outlined"
                                        item-title="text"
                                        item-value="value"
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
                            <v-card-title class="d-flex align-start text-subtitle-1">
                                <span class="font-weight-bold mr-2">{{ index + 1 }}.</span>
                                <span>{{ q.question_text }}</span>
                                <v-spacer></v-spacer>
                                <div>
                                    <v-btn icon size="small" variant="text" color="primary" @click="editQuestion(q)">
                                        <v-icon>mdi-pencil</v-icon>
                                    </v-btn>
                                    <v-btn icon size="small" variant="text" color="error" @click="deleteQuestion(q)">
                                        <v-icon>mdi-delete</v-icon>
                                    </v-btn>
                                </div>
                            </v-card-title>
                            <v-card-text>
                                <v-list density="compact">
                                    <v-list-item :class="{'text-success font-weight-bold': q.correct_answer === 'a'}">
                                        <template v-slot:prepend>
                                            <v-icon :icon="q.correct_answer === 'a' ? 'mdi-check-circle' : 'mdi-alpha-a-circle'" :color="q.correct_answer === 'a' ? 'success' : ''"></v-icon>
                                        </template>
                                        A. {{ q.option_a }}
                                    </v-list-item>
                                    <v-list-item :class="{'text-success font-weight-bold': q.correct_answer === 'b'}">
                                        <template v-slot:prepend>
                                            <v-icon :icon="q.correct_answer === 'b' ? 'mdi-check-circle' : 'mdi-alpha-b-circle'" :color="q.correct_answer === 'b' ? 'success' : ''"></v-icon>
                                        </template>
                                        B. {{ q.option_b }}
                                    </v-list-item>
                                    <v-list-item :class="{'text-success font-weight-bold': q.correct_answer === 'c'}">
                                        <template v-slot:prepend>
                                            <v-icon :icon="q.correct_answer === 'c' ? 'mdi-check-circle' : 'mdi-alpha-c-circle'" :color="q.correct_answer === 'c' ? 'success' : ''"></v-icon>
                                        </template>
                                        C. {{ q.option_c }}
                                    </v-list-item>
                                    <v-list-item :class="{'text-success font-weight-bold': q.correct_answer === 'd'}">
                                        <template v-slot:prepend>
                                            <v-icon :icon="q.correct_answer === 'd' ? 'mdi-check-circle' : 'mdi-alpha-d-circle'" :color="q.correct_answer === 'd' ? 'success' : ''"></v-icon>
                                        </template>
                                        D. {{ q.option_d }}
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
import { ref, watch } from 'vue';
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

const questionForm = ref({
    question_text: '',
    option_a: '',
    option_b: '',
    option_c: '',
    option_d: '',
    correct_answer: null
});

const answerOptions = [
    { text: 'A', value: 'a' },
    { text: 'B', value: 'b' },
    { text: 'C', value: 'c' },
    { text: 'D', value: 'd' },
];

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
        if (isEditing.value) {
            await axios.put(`/api/admin/quizzes/${props.quiz.id}/questions/${editingId.value}`, questionForm.value);
            showSuccess('Soal berhasil diperbarui');
        } else {
            await axios.post(`/api/admin/quizzes/${props.quiz.id}/questions`, questionForm.value);
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
    questionForm.value = { ...question };
};

const deleteQuestion = async (question) => {
    const confirmed = await showConfirm('Hapus Soal?', 'Soal yang dihapus tidak dapat dikembalikan.');
    if (confirmed) {
        try {
            await axios.delete(`/api/admin/quizzes/${props.quiz.id}/questions/${question.id}`);
            await loadQuestions();
            showSuccess('Soal berhasil dihapus');
        } catch (error) {
            showError('Gagal menghapus soal');
        }
    }
};

const resetForm = () => {
    isEditing.value = false;
    editingId.value = null;
    questionForm.value = {
        question_text: '',
        option_a: '',
        option_b: '',
        option_c: '',
        option_d: '',
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
