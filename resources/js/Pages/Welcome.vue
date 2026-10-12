<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { motion, AnimatePresence } from 'motion-v';
import OrbitingCirclesDemo from '../Components/OrbitingCirclesDemo.vue';
import LineShadowText from '../Components/LineShadowText.vue';
import ProductCatalog from '../Components/ProductCatalog.vue';
import FaqSection from '../Components/FaqSection.vue';
import FooterSection from '../Components/FooterSection.vue';

const headlines = [
    {
        prefix: 'Semua Aplikasi Premium, Satu Tempat di',
        highlight: 'Ideostore',
        description: 'Temukan berbagai aplikasi premium favorit, mulai dari AI, desain grafis, hingga tools produktivitas. Semua kebutuhan digitalmu tersedia dalam satu platform yang praktis dan mudah dijelajahi.'
    },
    {
        prefix: 'Upgrade Produktivitas dengan',
        highlight: 'Aplikasi Premium',
        description: 'Tingkatkan produktivitas dan efisiensi kerja dengan berbagai aplikasi premium pilihan. Dukung aktivitas belajar, pekerjaan, dan proyek kreatifmu dengan tools digital yang sesuai kebutuhan.'
    },
    {
        prefix: 'Aplikasi Premium,',
        highlight: 'Harga Bersahabat',
        description: 'Nikmati berbagai pilihan aplikasi premium dengan harga yang kompetitif. Temukan paket langganan yang sesuai dengan kebutuhan dan anggaranmu tanpa mengorbankan kenyamanan bertransaksi.'
    },
    {
        prefix: 'Solusi Digital Terbaik untuk',
        highlight: 'Kebutuhanmu',
        description: 'Dari kecerdasan buatan hingga desain kreatif, temukan berbagai solusi digital untuk mendukung aktivitas sehari-hari. Ideostore hadir untuk membantu kamu menemukan aplikasi yang tepat dalam satu tempat.'
    },
    {
        prefix: 'Temukan Aplikasi Favoritmu di',
        highlight: 'Ideostore',
        description: 'Jelajahi beragam produk digital dan temukan aplikasi yang paling sesuai dengan kebutuhanmu. Dengan pilihan kategori yang beragam, mencari layanan premium favorit kini menjadi lebih mudah dan praktis.'
    },
    {
        prefix: 'Semua Kebutuhan Digital dalam',
        highlight: 'Satu Platform',
        description: 'Tak perlu bingung mencari layanan digital di berbagai tempat. Ideostore menghadirkan beragam pilihan aplikasi dan langganan premium yang memudahkanmu menemukan produk sesuai kebutuhan.'
    },
    {
        prefix: 'Maksimalkan Potensimu dengan',
        highlight: 'Tools Premium',
        description: 'Kembangkan kreativitas, tingkatkan efisiensi, dan selesaikan pekerjaan dengan lebih optimal. Temukan tools premium yang mendukung produktivitas serta membantumu mencapai hasil terbaik dalam setiap aktivitas.'
    },
    {
        prefix: 'Langganan Aplikasi Jadi',
        highlight: 'Lebih Mudah',
        description: 'Pilih aplikasi favorit, temukan paket yang sesuai, dan lakukan pemesanan melalui proses yang praktis. Ideostore membantu menyederhanakan pengalaman belanja produk digital agar lebih nyaman dan efisien.'
    },
    {
        prefix: 'Pilihan Aplikasi Premium untuk',
        highlight: 'Semua Kebutuhan',
        description: 'Temukan berbagai aplikasi untuk belajar, bekerja, berbisnis, dan berkarya dalam satu katalog. Apa pun kebutuhan digitalmu, jelajahi pilihan produk yang dapat mendukung aktivitasmu sehari-hari.'
    },
    {
        prefix: 'Saatnya Beralih ke Pengalaman',
        highlight: 'Digital Premium',
        description: 'Tingkatkan pengalaman menggunakan teknologi dengan berbagai aplikasi dan layanan digital pilihan. Temukan tools yang tepat untuk mendukung ide, mempercepat pekerjaan, dan membuka lebih banyak peluang produktivitas.'
    }
];

const currentIndex = ref(0);
let timer = null;

onMounted(() => {
    timer = setInterval(() => {
        currentIndex.value = (currentIndex.value + 1) % headlines.length;
    }, 4500);
});

onBeforeUnmount(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
    <div class="min-h-screen w-full max-w-full overflow-x-hidden bg-[#f8fafc] text-slate-800 flex flex-col font-sans selection:bg-royal-600 selection:text-white">
        <!-- Responsive & Mobile-Friendly Navbar -->
        <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200/80 transition-shadow">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="flex items-center justify-between h-14">
                    <!-- Brand / Logo -->
                    <a href="/" class="flex items-center gap-2 group">
                        <motion.div
                            :while-hover="{ rotate: 8, scale: 1.05 }"
                            :while-tap="{ scale: 0.92 }"
                            class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-gradient-to-tr from-royal-800 to-royal-600 flex items-center justify-center text-white shadow-xs shadow-royal-900/25 transition-transform"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </motion.div>
                        <span class="text-base font-bold tracking-tight text-slate-900">
                            Ideo<span class="text-royal-700">store</span>
                        </span>
                    </a>

                    <!-- Action: Pesanan -->
                    <div class="flex items-center">
                        <motion.a
                            href="#"
                            :while-hover="{ scale: 1.03 }"
                            :while-tap="{ scale: 0.94 }"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-royal-800 active:bg-royal-900 text-white text-xs font-semibold shadow-xs shadow-royal-950/20 transition-all select-none touch-manipulation"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span>Pesanan</span>
                        </motion.a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Hero Section: 2 Kolom (Proporsional & Clean) -->
        <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 py-6 sm:py-8 flex flex-col justify-center">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 items-center">
                <!-- Left Column: Rotating Animated Line Shadow Text & Deskripsi -->
                <div class="flex flex-col items-start text-left min-h-[160px] sm:min-h-[180px] lg:min-h-[200px] justify-center relative">
                    <AnimatePresence mode="wait">
                        <motion.div
                            :key="currentIndex"
                            :initial="{ opacity: 0, y: 14, filter: 'blur(3px)' }"
                            :animate="{ opacity: 1, y: 0, filter: 'blur(0px)' }"
                            :exit="{ opacity: 0, y: -14, filter: 'blur(3px)' }"
                            :transition="{ duration: 0.45, ease: 'easeInOut' }"
                            class="w-full"
                        >
                            <h1 class="text-2xl sm:text-3xl lg:text-[2.5rem] font-extrabold tracking-tight text-slate-900 leading-[1.25] sm:leading-[1.22]">
                                {{ headlines[currentIndex].prefix }}
                                <span class="block mt-1 sm:mt-1.5">
                                    <LineShadowText :text="headlines[currentIndex].highlight" shadowColor="#2554ed" class="text-royal-800" />
                                </span>
                            </h1>

                            <p class="mt-3 sm:mt-4 text-xs sm:text-sm text-slate-600 leading-relaxed max-w-xl">
                                {{ headlines[currentIndex].description }}
                            </p>
                        </motion.div>
                    </AnimatePresence>
                </div>

                <!-- Right Column: Orbiting Circles Magic UI -->
                <motion.div
                    :initial="{ opacity: 0, scale: 0.95 }"
                    :animate="{ opacity: 1, scale: 1 }"
                    :transition="{ duration: 0.6, delay: 0.15, ease: 'easeOut' }"
                    class="flex items-center justify-center w-full"
                >
                    <OrbitingCirclesDemo />
                </motion.div>
            </div>

            <!-- Card Cek Pesanan Kamu (Royal Blue Theme) -->
            <motion.div
                :initial="{ opacity: 0, y: 16 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.5, delay: 0.25, ease: 'easeOut' }"
                class="mt-6 sm:mt-8 w-full"
            >
                <div class="relative bg-gradient-to-r from-royal-900 via-royal-800 to-royal-900 rounded-xl sm:rounded-2xl p-4 sm:p-5 lg:p-6 shadow-lg shadow-royal-950/20 border border-royal-700/60 overflow-hidden text-white">
                    <!-- Subtle Glow Accent -->
                    <div class="absolute -top-12 -right-12 w-44 h-44 bg-royal-500/25 rounded-full blur-2xl pointer-events-none"></div>

                    <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-4 sm:gap-6 z-10">
                        <!-- Card Header / Title Info -->
                        <div class="flex items-center gap-3 shrink-0">
                            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-white/10 text-white flex items-center justify-center shrink-0 border border-white/15 backdrop-blur-md shadow-2xs">
                                <svg class="w-4.5 h-4.5 sm:w-5 sm:h-5 text-royal-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-white tracking-tight">
                                    Cek pesanan kamu
                                </h2>
                                <p class="text-xs text-royal-200/80">
                                    Lacak status lisensi & aktivasi secara realtime
                                </p>
                            </div>
                        </div>

                        <!-- Input Form (Clean Proportional Form on Royal Blue) -->
                        <form @submit.prevent class="flex-1 flex flex-col sm:flex-row gap-2 sm:gap-2.5 max-w-2xl">
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    placeholder="Masukkan nomor pesanan atau nomor WhatsApp"
                                    class="w-full pl-9 sm:pl-10 pr-3 py-2 sm:py-2.5 rounded-lg sm:rounded-xl bg-white text-xs sm:text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-royal-400 focus:bg-white shadow-inner/5 transition-all"
                                />
                            </div>
                            <motion.button
                                type="submit"
                                :while-hover="{ scale: 1.02 }"
                                :while-tap="{ scale: 0.96 }"
                                class="inline-flex items-center justify-center gap-1.5 px-5 sm:px-6 py-2 sm:py-2.5 rounded-lg sm:rounded-xl bg-white text-royal-900 hover:bg-royal-50 active:bg-royal-100 text-xs sm:text-sm font-bold shadow-md shadow-royal-950/25 transition-all select-none touch-manipulation cursor-pointer shrink-0"
                            >
                                <span>Lacak Pesanan</span>
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-royal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </motion.button>
                        </form>
                    </div>
                </div>
            </motion.div>

            <!-- Product Catalog & Filter (Sesuai Referensi Gambar) -->
            <ProductCatalog />
        </main>

        <!-- FAQ Section (Full-Width Layar) -->
        <FaqSection />

        <!-- Footer Section -->
        <FooterSection />
    </div>
</template>
