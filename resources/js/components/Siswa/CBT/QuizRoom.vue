<template>
    <v-app :theme="theme.global.name.value">
        <!-- Quiz App Bar -->
        <v-app-bar elevation="2" color="white" class="px-4">
            <v-toolbar-title class="text-h6 font-weight-bold">
                {{ quizTitle }}
            </v-toolbar-title>
            
            <v-spacer></v-spacer>
            
            <div class="d-flex align-center">
                <v-icon :color="timerColor" class="mr-2">mdi-clock-outline</v-icon>
                <div class="text-h6 font-weight-bold" :class="`text-${timerColor}`">{{ formattedTime }}</div>
            </div>
        </v-app-bar>

        <v-main class="bg-grey-lighten-4">
            <v-container fluid class="pa-4 pa-md-6 pb-12">
                <v-row>
                    <!-- Questions Sidebar (Desktop) / Header (Mobile) -->
                    <v-col cols="12" md="3" order-md="2">
                        <v-card rounded="xl" elevation="2" class="pa-4 mb-4">
                            <h3 class="text-subtitle-1 font-weight-bold mb-4">Navigasi Soal</h3>
                            <v-row no-gutters>
                                <v-col v-for="(q, index) in questions" :key="q.id" cols="3" sm="2" md="3" class="pa-1">
                                    <v-btn
                                        :color="getQuestionNavColor(index)"
                                        :variant="currentQuestionIndex === index ? 'flat' : 'outlined'"
                                        block
                                        class="text-body-2"
                                        size="small"
                                        rounded="lg"
                                        @click="currentQuestionIndex = index"
                                    >
                                        {{ index + 1 }}
                                    </v-btn>
                                </v-col>
                            </v-row>

                            <v-divider class="my-6"></v-divider>

                            <v-btn 
                                block 
                                color="error" 
                                size="large" 
                                rounded="lg"
                                prepend-icon="mdi-check-all"
                                @click="confirmFinish"
                                :loading="submitting"
                            >
                                Selesaikan Ujian
                            </v-btn>
                        </v-card>
                    </v-col>

                    <!-- Active Question Area -->
                    <v-col cols="12" md="9" order-md="1">
                        <v-card v-if="currentQuestion" rounded="xl" elevation="2" class="pa-6 pa-md-10 min-h-card">
                            <div class="d-flex justify-space-between align-center mb-6">
                                <v-chip color="info" label size="small" class="font-weight-bold">
                                    SOAL NO. {{ currentQuestionIndex + 1 }} / {{ questions.length }}
                                </v-chip>
                            </div>

                            <div class="text-h5 mb-8 line-height-relaxed" v-html="currentQuestion.question_text"></div>

                            <v-radio-group v-model="currentQuestion.my_answer" class="quiz-options" @change="saveAnswer">
                                <v-card 
                                    v-for="opt in ['a', 'b', 'c', 'd']" 
                                    :key="opt"
                                    :variant="currentQuestion.my_answer === opt ? 'flat' : 'outlined'"
                                    :color="currentQuestion.my_answer === opt ? 'primary' : 'grey-lighten-2'"
                                    class="mb-4 option-card"
                                    @click="selectOption(opt)"
                                    rounded="lg"
                                >
                                    <v-card-text class="d-flex align-center pa-4">
                                        <div 
                                            class="option-letter mr-4"
                                            :class="currentQuestion.my_answer === opt ? 'bg-white text-primary' : 'bg-grey-lighten-3'"
                                        >
                                            {{ opt.toUpperCase() }}
                                        </div>
                                        <div class="text-body-1" :class="currentQuestion.my_answer === opt ? 'text-white' : ''">
                                            {{ currentQuestion['option_' + opt] }}
                                        </div>
                                    </v-card-text>
                                </v-card>
                            </v-radio-group>

                            <v-divider class="my-8"></v-divider>

                            <div class="d-flex justify-space-between">
                                <v-btn 
                                    prepend-icon="mdi-chevron-left" 
                                    variant="outlined" 
                                    rounded="lg"
                                    :disabled="currentQuestionIndex === 0"
                                    @click="currentQuestionIndex--"
                                >
                                    Sebelumnya
                                </v-btn>
                                <v-btn 
                                    append-icon="mdi-chevron-right" 
                                    color="primary" 
                                    rounded="lg"
                                    v-if="currentQuestionIndex < questions.length - 1"
                                    @click="currentQuestionIndex++"
                                >
                                    Selanjutnya
                                </v-btn>
                                <v-btn 
                                    append-icon="mdi-check-all" 
                                    color="success" 
                                    rounded="lg"
                                    v-else
                                    @click="confirmFinish"
                                >
                                    Selesai
                                </v-btn>
                            </div>
                        </v-card>
                        <v-card v-else-if="loading" rounded="xl" elevation="2" class="pa-10">
                            <v-skeleton-loader type="text, text, list-item-avatar@4, actions"></v-skeleton-loader>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-main>

        <!-- Finish Dialog -->
        <v-dialog v-model="finishDialog" max-width="500">
            <v-card rounded="xl" class="pa-4">
                <v-card-title class="text-h5 font-weight-bold text-center">Selesaikan Ujian?</v-card-title>
                <v-card-text class="text-center py-4">
                    Anda sudah menjawab {{ answeredCount }} dari {{ questions.length }} soal.
                    Setelah diselesaikan, Anda tidak dapat mengubah jawaban lagi.
                </v-card-text>
                <v-card-actions class="justify-center mt-4">
                    <v-btn color="grey-darken-1" variant="text" @click="finishDialog = false" class="mr-2 px-6">Batal</v-btn>
                    <v-btn color="error" variant="flat" @click="submitQuiz" class="px-8" :loading="submitting">Ya, Selesai</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-app>
</template>

<script setup>
import { ref, onMounted, computed, watch, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useTheme } from 'vuetify';

const route = useRoute();
const router = useRouter();
const theme = useTheme();

const attemptId = route.params.attemptId;
const quizTitle = ref('Memuat Ujian...');
const questions = ref([]);
const currentQuestionIndex = ref(0);
const timeLeft = ref(0);
const timerInterval = ref(null);
const loading = ref(true);
const submitting = ref(false);
const finishDialog = ref(false);

const currentQuestion = computed(() => {
    return questions.value[currentQuestionIndex.value] || null;
});

const formattedTime = computed(() => {
    const hours = Math.floor(timeLeft.value / 3600);
    const minutes = Math.floor((timeLeft.value % 3600) / 60);
    const seconds = timeLeft.value % 60;
    
    return [
        hours > 0 ? String(hours).padStart(2, '0') : null,
        String(minutes).padStart(2, '0'),
        String(seconds).padStart(2, '0')
    ].filter(v => v !== null).join(':');
});

const timerColor = computed(() => {
    if (timeLeft.value < 300) return 'error'; // 5 mins
    if (timeLeft.value < 600) return 'warning'; // 10 mins
    return 'primary';
});

const answeredCount = computed(() => {
    return questions.value.filter(q => q.my_answer !== null).length;
});

const getQuestionNavColor = (index) => {
    const q = questions.value[index];
    if (currentQuestionIndex.value === index) return 'primary';
    if (q.my_answer !== null) return 'success';
    return 'grey';
};

const fetchQuestions = async () => {
    try {
        loading.value = true;
        const response = await axios.get(`/api/cbt/attempts/${attemptId}/questions`);
        if (response.data.success) {
            questions.value = response.data.data.questions;
            timeLeft.value = response.data.data.time_left_seconds;
            quizTitle.value = response.data.data.quiz_title;
            startTimer();
        }
    } catch (error) {
        console.error('Error fetching questions:', error);
        alert('Gagal memuat pertanyaan ujian');
        router.push('/siswa/cbt');
    } finally {
        loading.value = false;
    }
};

const startTimer = () => {
    if (timerInterval.value) clearInterval(timerInterval.value);
    timerInterval.value = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            clearInterval(timerInterval.value);
            autoSubmit();
        }
    }, 1000);
};

const selectOption = (opt) => {
    if (!currentQuestion.value) return;
    currentQuestion.value.my_answer = opt;
    saveAnswer();
};

const saveAnswer = async () => {
    if (!currentQuestion.value) return;
    try {
        await axios.post(`/api/cbt/attempts/${attemptId}/answer`, {
            question_id: currentQuestion.value.id,
            answer: currentQuestion.value.my_answer
        });
    } catch (error) {
        console.error('Error saving answer:', error);
    }
};

const confirmFinish = () => {
    finishDialog.value = true;
};

const autoSubmit = () => {
    alert('Waktu ujian telah habis!');
    submitQuiz();
};

const submitQuiz = async () => {
    try {
        submitting.value = true;
        const response = await axios.post(`/api/cbt/attempts/${attemptId}/finish`);
        if (response.data.success) {
            alert(`Ujian selesai! Skor Anda: ${response.data.data.score}`);
            router.push('/siswa/cbt');
        }
    } catch (error) {
        console.error('Error submitting quiz:', error);
        alert('Gagal mengirim jawaban ujian');
    } finally {
        submitting.value = false;
        finishDialog.value = false;
    }
};

onMounted(fetchQuestions);

onUnmounted(() => {
    if (timerInterval.value) clearInterval(timerInterval.value);
});
</script>

<style scoped>
.min-h-card {
    min-height: 500px;
}

.line-height-relaxed {
    line-height: 1.6;
}

.option-card {
    transition: all 0.2s ease;
    cursor: pointer;
}

.option-card:hover:not(.v-card--variant-flat) {
    background-color: #f8fafc;
    border-color: #3b82f6;
}

.option-letter {
    width: 36px;
    height: 36px;
    display: flex;
    align-center: center;
    justify-content: center;
    border-radius: 8px;
    font-weight: bold;
}

.quiz-options :deep(.v-selection-control-group) {
    display: none;
}
</style>
