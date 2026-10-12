<script setup>
import { motion } from 'motion-v';

const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="w-full bg-white border-t border-slate-200/80 py-8 sm:py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col items-center justify-center text-center gap-3">
            <!-- Brand / Logo Center -->
            <a href="/" class="flex items-center gap-2 group">
                <motion.div
                    :while-hover="{ rotate: 8, scale: 1.05 }"
                    :while-tap="{ scale: 0.92 }"
                    class="w-7 h-7 rounded-lg bg-gradient-to-tr from-royal-800 to-royal-600 flex items-center justify-center text-white shadow-xs shadow-royal-900/25 transition-transform"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </motion.div>
                <span class="text-base font-bold tracking-tight text-slate-900">
                    Ideo<span class="text-royal-700">store</span>
                </span>
            </a>

            <!-- Tagline Ringkas -->
            <p class="text-xs text-slate-500 max-w-sm">
                Toko aplikasi & lisensi digital premium terpercaya di Indonesia.
            </p>

            <!-- Copyright Center -->
            <p class="text-[11px] sm:text-xs text-slate-400 mt-1">
                © {{ currentYear }} Ideostore. All rights reserved.
            </p>
        </div>
    </footer>
</template>
