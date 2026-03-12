<template>
    <v-container>
        <v-row>
            <v-col cols="12">
                <div class="d-flex align-center mb-6">
                    <v-btn icon="mdi-arrow-left" variant="text" @click="$router.back()" class="mr-4"></v-btn>
                    <div>
                        <h1 class="text-h4 font-weight-black mb-1">REKAP NILAI</h1>
                        <p class="text-grey">Manajemen dan rekapitulasi nilai seluruh siswa</p>
                    </div>
                </div>

                <v-card class="elevation-0 border border-dashed pa-12 text-center" rounded="lg">
                    <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-file-percent-outline</v-icon>
                    <h3 class="text-h5 font-weight-bold mb-2">Modul Sedang Dikembangkan</h3>
                    <p class="text-grey mb-6">Fitur manajemen nilai admin akan segera hadir untuk memudahkan rekapitulasi data.</p>
                    <v-btn color="primary" rounded="lg" to="/admin">Kembali ke Dashboard</v-btn>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup>
// Placeholder logic
</script>

<style scoped>
.border-dashed {
    border: 2px dashed #e0e0e0 !important;
}
</style>
