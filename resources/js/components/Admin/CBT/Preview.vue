<template>
    <v-dialog :model-value="modelValue" fullscreen transition="dialog-bottom-transition" persistent>
        <v-app :theme="theme.global.name.value" class="cbt-premium-bg">
            <!-- Header Premium BKN/CAT Style -->
            <v-app-bar elevation="1" class="px-2 px-md-6 border-b" height="80" color="white">
                <v-btn icon @click="$emit('close')" color="error" class="mr-4" variant="tonal">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
                
                <div class="d-flex flex-column justify-center h-100 py-2">
                    <div class="text-subtitle-2 text-grey-darken-1 mb-n1 font-weight-medium">PREVIEW SIMULASI CAT</div>
                    <div class="text-h6 font-weight-bold text-primary text-truncate" style="max-width: 40vw;">
                        {{ quiz.title }}
                    </div>
                </div>
                
                <v-spacer></v-spacer>
                
                <v-chip color="warning" variant="elevated" class="mr-4 font-weight-black d-none d-md-flex elevation-2" prepend-icon="mdi-alert">
                    MODE PRATINJAU - TIDAK DISIMPAN
                </v-chip>

                <v-card variant="outlined" color="primary" class="d-flex align-center px-4 py-2 rounded-pill bg-blue-lighten-5 border-primary">
                    <v-icon color="primary" class="mr-2" size="24">mdi-timer-outline</v-icon>
                    <div class="text-h6 font-weight-black text-primary font-mono tracking-widest">{{ quiz.duration_minutes }}:00</div>
                </v-card>
            </v-app-bar>

            <v-main class="cbt-premium-bg" style="height: 100vh; overflow-y: auto;">
                <v-container class="pa-4 pa-md-8 pb-16 max-w-1200">
                    <v-row v-if="loading">
                         <v-col cols="12" class="text-center pa-12">
                             <v-progress-circular indeterminate color="primary" size="64" width="6"></v-progress-circular>
                             <div class="mt-6 text-h6 text-primary font-weight-medium">Menyinkronkan Bank Soal...</div>
                         </v-col>
                    </v-row>
                    
                    <v-row v-else-if="questions.length === 0">
                        <v-col cols="12" class="text-center pa-12">
                            <v-card class="pa-12 mx-auto rounded-xl elevation-2 text-center" max-width="600">
                                <v-icon size="100" color="grey-lighten-1" icon="mdi-folder-open-outline"></v-icon>
                                <div class="mt-6 text-h5 font-weight-bold text-grey-darken-2">Bank Soal Kosong</div>
                                <div class="text-body-1 text-grey mt-2">Ujian ini belum memiliki pertanyaan untuk dievaluasi.</div>
                                <v-btn color="primary" class="mt-8" size="large" @click="$emit('close')">Kembali ke Manajemen</v-btn>
                            </v-card>
                        </v-col>
                    </v-row>
                    
                    <v-row v-else>
                        <!-- Active Question Area (Left Side) -->
                        <v-col cols="12" md="8" lg="9" class="mb-6 mb-md-0">
                            <v-card rounded="xl" elevation="3" class="min-h-card d-flex flex-column border-t-primary">
                                <!-- Top Bar of Question -->
                                <div class="d-flex justify-space-between align-center px-6 py-4 bg-grey-lighten-4 border-b">
                                    <div class="d-flex align-center">
                                        <v-chip color="primary" size="large" class="font-weight-black rounded-lg mr-4 elevation-1">
                                            SOAL {{ currentQuestionIndex + 1 }}
                                        </v-chip>
                                        <div class="text-subtitle-1 text-grey-darken-2 font-weight-medium">
                                            Dari {{ questions.length }} Soal
                                        </div>
                                    </div>
                                    
                                    <v-chip color="orange-darken-3" variant="outlined" size="small" class="font-weight-bold font-italic rounded-lg">
                                        <v-icon start size="small">mdi-star-circle-outline</v-icon>
                                        Bobot: {{ currentQuestion.points }}
                                    </v-chip>
                                </div>

                                <!-- Question Body -->
                                <div class="pa-6 pa-md-8 flex-grow-1">
                                    
                                    <!-- Image Display -->
                                    <v-img 
                                        v-if="currentQuestion.question_image" 
                                        :src="`/storage/${currentQuestion.question_image}`"
                                        max-height="350"
                                        contain
                                        class="mb-8 rounded-xl bg-grey-lighten-4 border elevation-1"
                                    ></v-img>

                                    <!-- Question Text -->
                                    <div class="text-h6 mb-8 text-black line-height-relaxed html-content font-weight-regular" v-html="currentQuestion.question_text"></div>

                                    <!-- Premium Custom Radio Options -->
                                    <div class="premium-options-container mt-8">
                                        <div 
                                            v-for="opt in availableOptions" 
                                            :key="opt"
                                            class="premium-option-item elevation-1 mb-4 rounded-xl d-flex align-stretch transition-fast-in-fast-out"
                                            :class="getEnhancedOptionClass(opt)"
                                            @click="currentQuestion.preview_answer = opt"
                                        >
                                            <div class="option-label-container d-flex align-center justify-center font-weight-black text-h6 px-6">
                                                {{ opt.toUpperCase() }}
                                            </div>
                                            <div class="option-content-container flex-grow-1 pa-4 pa-md-5 text-body-1 bg-white ml-1">
                                                {{ currentQuestion['option_' + opt] }}
                                            </div>
                                            <div class="icon-indicator-container d-flex align-center justify-center px-4 bg-white rounded-r-xl">
                                                <v-icon v-if="currentQuestion.correct_answer === opt && currentQuestion.preview_answer === opt" color="success" size="32">mdi-check-circle</v-icon>
                                                <v-icon v-else-if="currentQuestion.correct_answer !== opt && currentQuestion.preview_answer === opt" color="error" size="32">mdi-close-circle</v-icon>
                                                <v-icon v-else-if="currentQuestion.preview_answer === null" color="grey-lighten-2" size="32">mdi-radiobox-blank</v-icon>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <!-- Bottom Navigation Bar -->
                                <v-divider></v-divider>
                                <div class="px-6 py-4 bg-grey-lighten-5 rounded-b-xl d-flex justify-space-between align-center">
                                    <v-btn 
                                        prepend-icon="mdi-arrow-left-circle" 
                                        variant="outlined" 
                                        color="primary"
                                        size="large"
                                        rounded="pill"
                                        class="font-weight-bold"
                                        :disabled="currentQuestionIndex === 0"
                                        @click="currentQuestionIndex--"
                                    >
                                        SEBELUMNYA
                                    </v-btn>
                                    
                                    <v-btn 
                                        v-if="currentQuestionIndex < questions.length - 1"
                                        append-icon="mdi-arrow-right-circle" 
                                        color="primary" 
                                        size="large"
                                        rounded="pill"
                                        class="font-weight-bold elevation-2"
                                        @click="currentQuestionIndex++"
                                    >
                                        SELANJUTNYA
                                    </v-btn>
                                    
                                    <v-btn 
                                        v-else
                                        prepend-icon="mdi-flag-checkered" 
                                        color="success" 
                                        size="large"
                                        rounded="pill"
                                        class="font-weight-bold elevation-2"
                                        @click="$emit('close')"
                                    >
                                        SELESAI PREVIEW
                                    </v-btn>
                                </div>
                            </v-card>
                        </v-col>

                        <!-- Right Panel: Question Grid Matrix -->
                        <v-col cols="12" md="4" lg="3">
                            <v-card rounded="xl" elevation="3" class="pa-0 position-sticky border-t-primary" style="top: 24px;">
                                <div class="bg-primary px-4 py-3 text-center rounded-t-xl">
                                    <h3 class="text-subtitle-1 font-weight-bold text-white d-flex align-center justify-center">
                                        <v-icon start>mdi-grid</v-icon> MATRIKS SOAL
                                    </h3>
                                </div>
                                
                                <div class="pa-4 pt-6">
                                    <div class="grid-container">
                                        <div 
                                            v-for="(q, index) in questions" 
                                            :key="q.id" 
                                            class="grid-item d-flex align-center justify-center font-weight-bold text-subtitle-2 elevation-1"
                                            :class="getGridItemClass(index)"
                                            @click="currentQuestionIndex = index"
                                            v-ripple
                                        >
                                            {{ index + 1 }}
                                        </div>
                                    </div>
                                </div>

                                <v-divider class="mx-4"></v-divider>

                                <div class="pa-4 bg-grey-lighten-5">
                                    <div class="text-caption font-weight-bold text-grey-darken-2 mb-2">KETERANGAN WARNA:</div>
                                    <div class="d-flex align-center mb-2">
                                        <div class="color-box bg-success mr-2 rounded elevation-1"></div>
                                        <span class="text-caption">Jawaban Benar</span>
                                    </div>
                                    <div class="d-flex align-center mb-2">
                                        <div class="color-box bg-error mr-2 rounded elevation-1"></div>
                                        <span class="text-caption">Jawaban Salah</span>
                                    </div>
                                    <div class="d-flex align-center mb-2">
                                        <div class="color-box bg-white border mr-2 rounded elevation-1"></div>
                                        <span class="text-caption">Belum Dijawab</span>
                                    </div>
                                    <div class="d-flex align-center">
                                        <div class="color-box bg-primary mr-2 rounded elevation-1" style="border: 2px solid #1976D2;"></div>
                                        <span class="text-caption">Sedang Dilihat</span>
                                    </div>
                                </div>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-container>
            </v-main>
        </v-app>
    </v-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { useTheme } from 'vuetify';

const props = defineProps({
    modelValue: Boolean,
    quiz: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['update:modelValue', 'close']);
const theme = useTheme();

const questions = ref([]);
const loading = ref(false);
const currentQuestionIndex = ref(0);

const currentQuestion = computed(() => {
    return questions.value[currentQuestionIndex.value] || null;
});

const availableOptions = computed(() => {
    if (!currentQuestion.value) return [];
    const opts = ['a', 'b'];
    if (currentQuestion.value.option_c) opts.push('c');
    if (currentQuestion.value.option_d) opts.push('d');
    if (currentQuestion.value.option_e) opts.push('e');
    return opts;
});

// Enhanced styling for options
const getEnhancedOptionClass = (opt) => {
    const isSelected = currentQuestion.value.preview_answer === opt;
    const isCorrect = currentQuestion.value.correct_answer === opt;
    const hasAnswered = currentQuestion.value.preview_answer !== null;

    if (hasAnswered) {
        if (isSelected) {
            return isCorrect ? 'option-selected-correct' : 'option-selected-wrong';
        } else if (isCorrect) {
            return 'option-missed-correct'; // Shows the correct answer if they got it wrong
        }
    }
    return 'option-default';
};

const getGridItemClass = (index) => {
    const q = questions.value[index];
    const isCurrent = currentQuestionIndex.value === index;
    
    let classes = [];
    if (isCurrent) classes.push('grid-current');
    
    if (q.preview_answer) {
        if (q.preview_answer === q.correct_answer) {
            classes.push('grid-correct');
        } else {
            classes.push('grid-wrong');
        }
    } else {
        classes.push('grid-unanswered');
    }
    
    return classes.join(' ');
};

const loadQuestions = async () => {
    if (!props.quiz?.id) return;
    loading.value = true;
    try {
        const response = await axios.get(`api/admin/quizzes/${props.quiz.id}/questions`);
        questions.value = response.data.data.map(q => ({
            ...q,
            preview_answer: null
        }));
        currentQuestionIndex.value = 0;
    } catch (error) {
        console.error('Error fetching preview questions:', error);
    } finally {
        loading.value = false;
    }
};

watch(() => props.modelValue, (val) => {
    if (val) {
        loadQuestions();
    }
}, { immediate: true });
</script>

<style scoped>
/* Typography & Base */
.font-mono {
    font-family: 'Courier New', Courier, monospace;
}
.tracking-widest {
    letter-spacing: 0.1em !important;
}
.cbt-premium-bg {
    background-color: #f0f4f8; /* Soft blue-grey background typical of CAT */
}
.max-w-1200 {
    max-width: 1200px;
    margin: 0 auto;
}
.min-h-card {
    min-height: 600px;
}
.border-t-primary {
    border-top: 4px solid #1976D2 !important;
}

/* Premium Options Layout */
.premium-option-item {
    cursor: pointer;
    overflow: hidden;
    background-color: white;
    border: 2px solid transparent;
}
.premium-option-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}
.option-label-container {
    width: 60px;
    color: white;
    transition: background-color 0.3s ease;
}

/* Option States */
.option-default {
    border-color: #e0e0e0;
}
.option-default .option-label-container {
    background-color: #e0e0e0;
    color: #424242;
}
.option-default:hover {
    border-color: #1976D2;
}
.option-default:hover .option-label-container {
    background-color: #bbdefb;
    color: #1976D2;
}

.option-selected-correct {
    border-color: #4CAF50;
    box-shadow: 0 0 0 1px #4CAF50 !important;
}
.option-selected-correct .option-label-container {
    background-color: #4CAF50;
}
.option-selected-wrong {
    border-color: #FF5252;
    box-shadow: 0 0 0 1px #FF5252 !important;
}
.option-selected-wrong .option-label-container {
    background-color: #FF5252;
}
.option-missed-correct {
    border-color: #81C784;
    border-style: dashed;
}
.option-missed-correct .option-label-container {
    background-color: #81C784;
}

/* Grid Matrix */
.grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(45px, 1fr));
    gap: 12px;
}
.grid-item {
    aspect-ratio: 1;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 2px solid transparent;
}
.grid-item:hover {
    transform: scale(1.05);
}
.grid-unanswered {
    background-color: white;
    color: #424242;
    border-color: #e0e0e0;
}
.grid-correct {
    background-color: #4CAF50;
    color: white;
}
.grid-wrong {
    background-color: #FF5252;
    color: white;
}
.grid-current {
    transform: scale(1.1);
    box-shadow: 0 0 0 2px white, 0 0 0 4px #1976D2 !important;
    z-index: 2;
}

/* Utilities */
.color-box {
    width: 16px;
    height: 16px;
}
.html-content :deep(p) {
    margin-bottom: 1em;
}
.html-content :deep(img) {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
</style>
