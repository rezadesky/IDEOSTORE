<script setup>
import { ref, computed } from 'vue';
import { motion } from 'motion-v';

const searchQuery = ref('');
const activeFilter = ref('all');
const sortBy = ref('rekomendasi');

const filterChips = [
    { id: 'terlaris', label: 'Terlaris', count: 3, iconType: 'flame' },
    { id: 'baru', label: 'Baru', count: 7, iconType: 'sparkles' },
    { id: 'hemat', label: 'Hemat', count: 3, iconType: 'tag' },
    { id: 'rating', label: 'Rating ≥4.8', count: 6, iconType: 'star' },
];

const categories = [
    {
        id: 'aplikasi-premium',
        title: 'Aplikasi Premium',
        count: 14,
        products: [
            {
                name: 'CapCut Pro',
                category: 'aplikasi-premium',
                discount: 'HEMAT 40%',
                price: 'Rp25.000',
                originalPrice: 'Rp79.000',
                sold: '2.1rb terjual',
                rating: '4.9',
                reviews: '(7)',
                badge: 'popular',
                isNew: false,
                isJoint: false,
                color: 'from-slate-900 to-slate-800',
                logoType: 'capcut',
            },
            {
                name: 'Apple Music',
                category: 'aplikasi-premium',
                price: 'Rp10.000',
                sold: '1.1rb terjual',
                rating: '5.0',
                reviews: '(3) otomatis',
                isNew: false,
                isJoint: false,
                color: 'from-[#FA2D48] to-[#D61E37]',
                logoType: 'apple-music',
            },
            {
                name: 'Meitu',
                category: 'aplikasi-premium',
                price: 'Rp10.000',
                sold: '430+ terjual',
                rating: '5.0',
                reviews: '(75) otomatis',
                isNew: false,
                isJoint: false,
                color: 'from-[#FF3366] to-[#E60045]',
                logoType: 'meitu',
            },
            {
                name: 'Spotify Premium',
                category: 'aplikasi-premium',
                discount: 'HEMAT 28%',
                garansi: 'Garansi 30 Hari',
                price: 'Rp25.000',
                originalPrice: 'Rp54.990',
                sold: 'Tersedia',
                rating: null,
                isNew: false,
                isJoint: false,
                color: 'from-[#1DB954] to-[#169C46]',
                logoType: 'spotify',
            },
            {
                name: 'Alight Motion',
                category: 'aplikasi-premium',
                price: 'Rp10.000',
                sold: '41 terjual',
                rating: '5.0',
                reviews: '(4) otomatis',
                isNew: false,
                isJoint: false,
                color: 'from-[#0B1E28] to-[#143242]',
                logoType: 'alight-motion',
            },
            {
                name: 'HBO Max',
                category: 'aplikasi-premium',
                price: 'Rp20.000',
                sold: '4 terjual',
                rating: '5.0',
                reviews: '(1) otomatis',
                isNew: false,
                isJoint: false,
                color: 'from-[#0C0B1B] to-[#1E1145]',
                logoType: 'hbo-max',
            },
            {
                name: 'Netflix Premium',
                category: 'aplikasi-premium',
                discount: 'HEMAT 29%',
                price: 'Rp25.000',
                originalPrice: 'Rp54.000',
                sold: '454 terjual',
                rating: '4.6',
                reviews: '(2)',
                isNew: false,
                isJoint: false,
                color: 'from-[#000000] to-[#141414]',
                logoType: 'netflix',
            },
            {
                name: 'YouTube',
                category: 'aplikasi-premium',
                price: 'Rp10.000',
                sold: '171 terjual',
                rating: '4.0',
                reviews: '(3)',
                isNew: false,
                isJoint: false,
                color: 'from-[#FF0000] to-[#CC0000]',
                logoType: 'youtube',
            },
            {
                name: 'Canva',
                category: 'aplikasi-premium',
                price: 'Rp10.000',
                sold: '1.5rb+ terjual',
                rating: '5.0',
                reviews: '(82) otomatis',
                isNew: false,
                isJoint: false,
                color: 'from-[#00C4CC] to-[#00A3A8]',
                logoType: 'canva',
            },
            {
                name: 'Zoom Pro',
                category: 'aplikasi-premium',
                price: 'Rp10.000',
                sold: '127 terjual',
                rating: '5.0',
                reviews: '(16) otomatis',
                isNew: false,
                isJoint: false,
                color: 'from-[#2D8CFF] to-[#0B5CFF]',
                logoType: 'zoom',
            },
            {
                name: 'Wink',
                category: 'aplikasi-premium',
                price: 'Rp10.000',
                sold: '28 terjual',
                rating: '5.0',
                reviews: '(8) otomatis',
                isNew: false,
                isJoint: false,
                color: 'from-[#111111] to-[#222222]',
                logoType: 'wink',
            },
            {
                name: 'Scribd Pro',
                category: 'aplikasi-premium',
                price: 'Rp10.000',
                sold: '3 terjual',
                rating: '5.0',
                reviews: '(1) otomatis',
                isNew: false,
                isJoint: false,
                color: 'from-[#1A7B58] to-[#125A3F]',
                logoType: 'scribd',
            },
            {
                name: 'Vidio',
                category: 'aplikasi-premium',
                price: 'Rp50.000',
                sold: 'Stok habis',
                isNew: true,
                isOutOfStock: true,
                color: 'from-[#FF2965] to-[#E6004C]',
                logoType: 'vidio',
            },
            {
                name: 'Remini Web',
                category: 'aplikasi-premium',
                price: 'Rp10.000',
                sold: '2 terjual',
                rating: '5.0',
                reviews: '(1) otomatis',
                isNew: false,
                color: 'from-[#B25C65] to-[#994750]',
                logoType: 'remini',
            },
        ],
    },
    {
        id: 'ai',
        title: 'AI',
        count: 10,
        products: [
            {
                name: 'ChatGPT',
                category: 'ai',
                price: 'Rp75.000',
                sold: '22 terjual',
                rating: '5.0',
                reviews: '(12) otomatis',
                isNew: true,
                badgeText: 'tanpa garansi',
                color: 'from-[#10A37F] to-[#0E8A6B]',
                logoType: 'chatgpt',
            },
            {
                name: 'Leonardo AI',
                category: 'ai',
                price: 'Rp200.000',
                sold: 'Sisa 2',
                isNew: false,
                color: 'from-[#19113B] to-[#2D1B69]',
                logoType: 'leonardo',
            },
            {
                name: 'Google Gemini',
                category: 'ai',
                price: 'Rp50.000',
                sold: '100 terjual',
                rating: '4.0',
                reviews: '(7)',
                isNew: false,
                color: 'from-[#1E293B] to-[#0F172A]',
                logoType: 'gemini',
            },
            {
                name: 'Perplexity AI',
                category: 'ai',
                price: 'Patungan',
                sold: 'Slot patungan',
                isNew: true,
                isJoint: true,
                color: 'from-[#20B2AA] to-[#17857F]',
                logoType: 'perplexity',
            },
            {
                name: 'Claude AI',
                category: 'ai',
                price: 'Patungan',
                sold: 'Slot patungan',
                isNew: true,
                isJoint: true,
                color: 'from-[#D97757] to-[#BF5E3D]',
                logoType: 'claude',
            },
            {
                name: 'Click AI',
                category: 'ai',
                price: 'Patungan',
                sold: 'Slot patungan',
                isNew: false,
                isJoint: true,
                color: 'from-[#3B82F6] to-[#1D4ED8]',
                logoType: 'click',
            },
            {
                name: 'Civitai AI',
                category: 'ai',
                price: 'Patungan',
                sold: 'Slot patungan',
                isNew: false,
                isJoint: true,
                color: 'from-[#1E293B] to-[#0F172A]',
                logoType: 'civitai',
            },
            {
                name: 'Higgsfield Plus',
                category: 'ai',
                price: 'Rp50.000',
                sold: '2 terjual',
                rating: '5.0',
                reviews: '(1) otomatis',
                isNew: false,
                color: 'from-[#D4E157] to-[#AFB42B]',
                logoType: 'higgsfield',
            },
            {
                name: 'Grok AI',
                category: 'ai',
                price: 'Patungan',
                sold: '14 terjual',
                rating: '5.0',
                reviews: '(10) otomatis',
                isNew: false,
                isJoint: true,
                color: 'from-[#0A0A0A] to-[#171717]',
                logoType: 'grok',
            },
            {
                name: 'Venice AI',
                category: 'ai',
                price: 'Patungan',
                sold: '4 terjual',
                rating: '5.0',
                reviews: '(4) otomatis',
                isNew: false,
                isJoint: true,
                color: 'from-[#0F0F14] to-[#1A1A24]',
                logoType: 'venice',
            },
        ],
    },
    {
        id: 'lainnya',
        title: 'Lainnya',
        count: 3,
        products: [
            {
                name: 'Google Mail',
                category: 'lainnya',
                price: 'Rp250',
                sold: '367 terjual',
                isNew: true,
                badgeText: 'garansi 1 pekan',
                color: 'from-slate-50 to-slate-100',
                textColor: 'text-slate-800',
                logoType: 'gmail',
            },
            {
                name: 'HMA VPN',
                category: 'lainnya',
                price: 'Rp10.000',
                sold: 'Tersedia',
                isNew: true,
                color: 'from-[#0066CC] to-[#004C99]',
                logoType: 'hma',
            },
            {
                name: 'Surfshark VPN',
                category: 'lainnya',
                price: 'Rp10.000',
                sold: '1 terjual',
                isNew: false,
                color: 'from-[#00D1B2] to-[#00A38B]',
                logoType: 'surfshark',
            },
        ],
    },
];

// Computed filtered categories
const filteredCategories = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    return categories
        .map((cat) => {
            let prods = cat.products;

            if (q) {
                prods = prods.filter((p) => p.name.toLowerCase().includes(q));
            }

            if (activeFilter.value === 'terlaris') {
                prods = prods.filter((p) => p.sold.includes('rb') || parseInt(p.sold) >= 100);
            } else if (activeFilter.value === 'baru') {
                prods = prods.filter((p) => p.isNew);
            } else if (activeFilter.value === 'hemat') {
                prods = prods.filter((p) => p.discount);
            } else if (activeFilter.value === 'rating') {
                prods = prods.filter((p) => p.rating && parseFloat(p.rating) >= 4.8);
            }

            return {
                ...cat,
                filteredProducts: prods,
            };
        })
        .filter((cat) => cat.filteredProducts.length > 0);
});
</script>

<template>
    <section class="mt-8 sm:mt-12 w-full flex flex-col gap-5 sm:gap-6">
        <!-- Search & Filter Bar -->
        <div class="flex flex-col gap-3">
            <!-- Search Input (Full Width Modern Bar) -->
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 sm:pl-4 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari produk (mis. netflix, canva)..."
                    class="w-full pl-10 sm:pl-11 pr-4 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-white border border-slate-200 text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-royal-600 focus:border-royal-600 shadow-2xs transition-all"
                />
            </div>

            <!-- Filter Badges & Sort Bar -->
            <div class="flex flex-wrap items-center justify-between gap-2.5">
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <!-- Urutkan Button -->
                    <div class="relative inline-block text-left">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg sm:rounded-xl bg-white border border-slate-200 text-xs font-medium text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors"
                        >
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                            </svg>
                            <span>Urutkan: <strong>Rekomendasi</strong></span>
                            <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>

                    <!-- Quick Filter Chips with Clean SVG Icons -->
                    <button
                        v-for="chip in filterChips"
                        :key="chip.id"
                        type="button"
                        @click="activeFilter = activeFilter === chip.id ? 'all' : chip.id"
                        :class="[
                            'inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg sm:rounded-xl text-xs font-medium border transition-all cursor-pointer',
                            activeFilter === chip.id
                                ? 'bg-royal-50 border-royal-300 text-royal-700 font-semibold shadow-2xs'
                                : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50 shadow-2xs'
                        ]"
                    >
                        <!-- Icon Flame (Terlaris) -->
                        <svg v-if="chip.iconType === 'flame'" class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.316.492-.533 1.05-.694 1.62-.27.954-.42 2.05-.532 3.167-.028.275-.055.55-.083.824a7.97 7.97 0 00-2.316-.763 1 1 0 00-.916.388c-.287.41-.448.9-.472 1.41a6.002 6.002 0 002.327 4.962C7.03 15.688 8.442 16.5 10 16.5c2.317 0 4.316-1.39 5.215-3.418.525-1.185.576-2.525.132-3.76-.328-.916-.893-1.722-1.637-2.336a1 1 0 00-.773-.233 1.002 1.002 0 00-.766.452c-.283.428-.466.907-.563 1.408a1 1 0 11-1.966-.381 7.218 7.218 0 00.753-2.079c.144-.704.148-1.403.003-2.062a4.992 4.992 0 00-.97-1.788z" clip-rule="evenodd" />
                        </svg>

                        <!-- Icon Sparkles (Baru) -->
                        <svg v-else-if="chip.iconType === 'sparkles'" class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>

                        <!-- Icon Tag (Hemat) -->
                        <svg v-else-if="chip.iconType === 'tag'" class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V5a2 2 0 012-2z" />
                        </svg>

                        <!-- Icon Star (Rating) -->
                        <svg v-else-if="chip.iconType === 'star'" class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>

                        <span>{{ chip.label }}</span>
                        <span class="text-[10px] px-1 py-0.2 rounded-full" :class="activeFilter === chip.id ? 'bg-royal-200/70 text-royal-800' : 'bg-slate-100 text-slate-500'">{{ chip.count }}</span>
                    </button>
                </div>

                <!-- View Toggle Buttons -->
                <div class="hidden sm:flex items-center gap-1 bg-white p-1 rounded-xl border border-slate-200 shadow-2xs">
                    <button type="button" class="p-1.5 rounded-lg bg-slate-900 text-white shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </button>
                    <button type="button" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Product Sections by Category -->
        <div v-for="category in filteredCategories" :key="category.id" class="flex flex-col gap-3 pt-2">
            <!-- Category Header -->
            <div class="flex items-center gap-2 border-b border-slate-200/80 pb-2">
                <h3 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight">
                    {{ category.title }}
                </h3>
                <span class="text-[11px] font-semibold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded-md">
                    {{ category.filteredProducts.length }}
                </span>
            </div>

            <!-- Product Grid: 2 cols on mobile, 3 on tablet, 6 cols on desktop -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2.5 sm:gap-3">
                <motion.div
                    v-for="(product, idx) in category.filteredProducts"
                    :key="product.name"
                    :while-hover="{ y: -3, transition: { duration: 0.18 } }"
                    class="group bg-white rounded-xl sm:rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-md hover:border-royal-200 transition-all flex flex-col overflow-hidden cursor-pointer p-2.5 sm:p-3"
                >
                    <!-- Logo / Brand Banner -->
                    <div
                        :class="[
                            'relative w-full aspect-[4/3] rounded-lg sm:rounded-xl bg-gradient-to-tr flex items-center justify-center p-3 shadow-inner/10 overflow-hidden',
                            product.color
                        ]"
                    >
                        <!-- New Badge or Otomatis badge -->
                        <div
                            v-if="product.isNew"
                            class="absolute top-1.5 left-1.5 text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-500 text-white shadow-xs uppercase tracking-wider"
                        >
                            Baru
                        </div>

                        <!-- Brand Logos Custom Render -->
                        <!-- 1. CapCut -->
                        <svg v-if="product.logoType === 'capcut'" class="w-8 h-8 sm:w-10 sm:h-10 fill-white drop-shadow-md" viewBox="0 0 512 512">
                            <path d="M387.56 109.56l-67.6 67.6-64-64 67.6-67.6c12.5-12.5 32.8-12.5 45.3 0l21.3 21.3c12.5 12.5 12.5 32.8 0 45.3zM233.96 177.16l-67.6 67.6-140.2-140.2c-12.5-12.5-12.5-32.8 0-45.3l21.3-21.3c12.5-12.5 32.8-12.5 45.3 0l141.2 141.2zm-141.2 141.2l67.6-67.6 64 64-67.6 67.6c-12.5 12.5-32.8 12.5-45.3 0l-21.3-21.3c-12.5-12.5-12.5-32.8 0-45.3zm153.6-67.6l67.6-67.6 140.2 140.2c12.5 12.5 12.5 32.8 0 45.3l-21.3 21.3c-12.5 12.5-32.8 12.5-45.3 0l-141.2-141.2z" />
                        </svg>

                        <!-- 2. Apple Music -->
                        <svg v-else-if="product.logoType === 'apple-music'" class="w-8 h-8 sm:w-10 sm:h-10 fill-white drop-shadow-md" viewBox="0 0 24 24">
                            <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.44c.66-.82 1.11-1.96.99-3.1-.96.04-2.11.64-2.79 1.45-.59.69-1.11 1.83-.97 2.93 1.07.08 2.16-.54 2.77-1.28z" />
                        </svg>

                        <!-- 3. Meitu -->
                        <div v-else-if="product.logoType === 'meitu'" class="w-10 h-10 rounded-full border-2 border-white/90 flex items-center justify-center text-white font-bold text-sm">
                            <span class="tracking-tighter">mt</span>
                        </div>

                        <!-- 4. Spotify -->
                        <svg v-else-if="product.logoType === 'spotify'" class="w-9 h-9 sm:w-11 sm:h-11 fill-white drop-shadow-md" viewBox="0 0 24 24">
                            <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z" />
                        </svg>

                        <!-- 5. Alight Motion -->
                        <div v-else-if="product.logoType === 'alight-motion'" class="w-9 h-9 rounded-full border-2 border-teal-400 flex items-center justify-center">
                            <div class="w-4 h-4 rounded-full bg-teal-400 animate-pulse"></div>
                        </div>

                        <!-- 6. HBO Max -->
                        <div v-else-if="product.logoType === 'hbo-max'" class="text-white font-black text-xs sm:text-sm tracking-wider">
                            HBO<span class="text-purple-400 font-extrabold ml-0.5">max</span>
                        </div>

                        <!-- 7. Netflix -->
                        <svg v-else-if="product.logoType === 'netflix'" class="h-8 sm:h-9 w-auto drop-shadow-md" viewBox="0 0 24 24" fill="none">
                            <path d="M5.5 0V24L9.5 22.8V0H5.5Z" fill="#B81D24" />
                            <path d="M14.5 0V22.8L18.5 24V0H14.5Z" fill="#B81D24" />
                            <path d="M5.5 0H9.5L18.5 24H14.5L5.5 0Z" fill="#E50914" />
                        </svg>

                        <!-- 8. YouTube -->
                        <svg v-else-if="product.logoType === 'youtube'" class="w-9 h-9 sm:w-10 sm:h-10 fill-white drop-shadow-md" viewBox="0 0 24 24">
                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814z" />
                            <path d="M9.545 15.568V8.432L15.818 12l-6.273 3.568z" fill="#FF0000" />
                        </svg>

                        <!-- 9. Canva -->
                        <div v-else-if="product.logoType === 'canva'" class="text-white font-serif italic font-black text-xl sm:text-2xl drop-shadow-sm">
                            Canva
                        </div>

                        <!-- 10. Zoom -->
                        <div v-else-if="product.logoType === 'zoom'" class="text-white font-bold text-base sm:text-lg tracking-tight">
                            zoom
                        </div>

                        <!-- 11. Wink -->
                        <div v-else-if="product.logoType === 'wink'" class="w-8 h-8 rounded-full border-2 border-white flex items-center justify-center text-white text-xs font-bold">
                            ;)
                        </div>

                        <!-- 12. Scribd -->
                        <div v-else-if="product.logoType === 'scribd'" class="text-white font-serif font-black text-lg">
                            [s]
                        </div>

                        <!-- 13. Vidio -->
                        <div v-else-if="product.logoType === 'vidio'" class="text-white font-black text-xl italic">
                            v
                        </div>

                        <!-- 14. Remini -->
                        <div v-else-if="product.logoType === 'remini'" class="text-white font-mono font-bold text-xs tracking-widest uppercase">
                            remini
                        </div>

                        <!-- 15. ChatGPT -->
                        <svg v-else-if="product.logoType === 'chatgpt'" class="w-8 h-8 sm:w-10 sm:h-10 fill-white drop-shadow-md" viewBox="0 0 24 24">
                            <path d="M22.2819 9.8211a5.9847 5.9847 0 0 0-.5157-4.9108 6.0462 6.0462 0 0 0-6.5098-2.9A6.0651 6.0651 0 0 0 4.9807 4.1818a5.9847 5.9847 0 0 0-3.9977 2.9 6.0462 6.0462 0 0 0 .7427 7.0966 5.98 5.98 0 0 0 .511 4.9107 6.051 6.051 0 0 0 6.5146 2.9001A5.9847 5.9847 0 0 0 13.2599 24a6.0557 6.0557 0 0 0 5.7718-4.2058 5.9894 5.9894 0 0 0 3.9977-2.9001 6.0557 6.0557 0 0 0-.7475-7.0729zm-9.022 12.6081a4.4755 4.4755 0 0 1-2.8764-1.0408l.1419-.0804 4.7783-2.7582a.7948.7948 0 0 0 .3927-.6813v-6.7369l2.02 1.1686a.071.071 0 0 1 .038.052v5.5826a4.504 4.504 0 0 1-4.4945 4.4944zm-9.6607-4.1254a4.4708 4.4708 0 0 1-.5346-3.0137l.142.0852 4.783 2.7582a.7712.7712 0 0 0 .7806 0l5.8428-3.3685v2.3324a.0804.0804 0 0 1-.0332.0615L9.74 19.9502a4.4992 4.4992 0 0 1-6.1408-1.6464zM2.3408 7.8956a4.485 4.485 0 0 1 2.3655-1.9728V11.6a.7664.7664 0 0 0 .3879.6765l5.8144 3.3543-2.0201 1.1685a.0757.0757 0 0 1-.071 0l-4.8303-2.7865A4.504 4.504 0 0 1 2.3408 7.872zm16.5963 3.8558L13.1038 8.364 15.1192 7.2a.0757.0757 0 0 1 .071 0l4.8303 2.7913a4.4944 4.4944 0 0 1-.6765 8.1042v-5.6772a.79.79 0 0 0-.407-.667zm2.0107-3.0231l-.142-.0852-4.7735-2.7818a.7759.7759 0 0 0-.7854 0L9.409 9.2297V6.8974a.0662.0662 0 0 1 .0284-.0615l4.8303-2.7866a4.4992 4.4992 0 0 1 6.6802 4.66zM8.3065 12.863l-2.02-1.1638a.0804.0804 0 0 1-.038-.0567V6.0742a4.4992 4.4992 0 0 1 7.3757-3.4537l-.142.0805L8.704 5.459a.7948.7948 0 0 0-.3927.6813zm1.0976-2.3654l2.602-1.4998 2.6069 1.4998v2.9994l-2.5974 1.4997-2.6067-1.4997Z" />
                        </svg>

                        <!-- 16. Leonardo AI -->
                        <div v-else-if="product.logoType === 'leonardo'" class="w-10 h-10 rounded-full border border-purple-400/50 bg-purple-900/40 flex items-center justify-center text-purple-200 font-bold text-xs">
                            LEO
                        </div>

                        <!-- 17. Gemini -->
                        <svg v-else-if="product.logoType === 'gemini'" class="w-8 h-8 sm:w-10 sm:h-10" viewBox="0 0 24 24">
                            <defs>
                                <linearGradient id="gemini-cat" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#1BA1E3" />
                                    <stop offset="50%" stop-color="#5468FF" />
                                    <stop offset="100%" stop-color="#9C40FF" />
                                </linearGradient>
                            </defs>
                            <path fill="url(#gemini-cat)" d="M12 24C12 17.3726 6.62742 12 0 12C6.62742 12 12 6.62742 12 0C12 6.62742 17.3726 12 24 12C17.3726 12 12 17.3726 12 24Z" />
                        </svg>

                        <!-- 18. Perplexity -->
                        <div v-else-if="product.logoType === 'perplexity'" class="w-8 h-8 text-white font-mono font-bold text-lg flex items-center justify-center">
                            *
                        </div>

                        <!-- 19. Claude -->
                        <div v-else-if="product.logoType === 'claude'" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white font-black text-sm">
                            *
                        </div>

                        <!-- 20. Click AI -->
                        <div v-else-if="product.logoType === 'click'" class="w-8 h-8 text-white font-bold text-base">
                            C
                        </div>

                        <!-- 21. Civitai -->
                        <div v-else-if="product.logoType === 'civitai'" class="w-8 h-8 text-blue-400 font-black text-base">
                            CV
                        </div>

                        <!-- 22. Higgsfield -->
                        <div v-else-if="product.logoType === 'higgsfield'" class="w-8 h-8 text-slate-900 font-bold text-base">
                            ~
                        </div>

                        <!-- 23. Grok -->
                        <div v-else-if="product.logoType === 'grok'" class="w-8 h-8 text-white font-bold text-base">
                            /
                        </div>

                        <!-- 24. Venice -->
                        <div v-else-if="product.logoType === 'venice'" class="w-8 h-8 text-white font-bold text-base">
                            V
                        </div>

                        <!-- 25. Gmail -->
                        <div v-else-if="product.logoType === 'gmail'" class="text-red-500 font-black text-2xl">
                            M
                        </div>

                        <!-- 26. HMA -->
                        <div v-else-if="product.logoType === 'hma'" class="text-white font-black text-sm">
                            HMA
                        </div>

                        <!-- 27. Surfshark -->
                        <div v-else-if="product.logoType === 'surfshark'" class="text-white font-black text-base">
                            S
                        </div>
                    </div>

                    <!-- Details Area -->
                    <div class="flex flex-col flex-1 justify-between pt-2">
                        <div>
                            <!-- Product Title -->
                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 truncate">
                                {{ product.name }}
                            </h4>

                            <!-- Badges / Tags -->
                            <div class="flex flex-wrap items-center gap-1 mt-0.5">
                                <span
                                    v-if="product.discount"
                                    class="text-[9px] font-bold text-emerald-600 bg-emerald-50 px-1 py-0.2 rounded"
                                >
                                    {{ product.discount }}
                                </span>
                                <span
                                    v-if="product.garansi"
                                    class="text-[9px] font-semibold text-royal-700 bg-royal-50 px-1 py-0.2 rounded"
                                >
                                    {{ product.garansi }}
                                </span>
                                <span
                                    v-if="product.badgeText"
                                    class="text-[9px] text-slate-500 bg-slate-100 px-1 py-0.2 rounded"
                                >
                                    {{ product.badgeText }}
                                </span>
                            </div>

                            <!-- Price Display -->
                            <div class="mt-1 flex items-baseline gap-1.5 flex-wrap">
                                <span class="text-xs sm:text-sm font-extrabold text-slate-900">
                                    {{ product.price }}
                                </span>
                                <span
                                    v-if="product.originalPrice"
                                    class="text-[10px] text-slate-400 line-through"
                                >
                                    {{ product.originalPrice }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Footer: Sold & Rating -->
                        <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-500">
                            <span class="truncate">{{ product.sold }}</span>
                            <span v-if="product.rating" class="flex items-center gap-1 text-amber-500 font-semibold shrink-0">
                                <svg class="w-3 h-3 fill-amber-400" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <span>{{ product.rating }}</span>
                            </span>
                        </div>
                    </div>
                </motion.div>
            </div>
        </div>
    </section>
</template>
