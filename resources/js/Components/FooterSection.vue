<script setup>
import { motion } from 'motion-v';

const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="w-full bg-white border-t border-slate-200/80 py-8 sm:py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col items-center justify-center text-center gap-3">
            <!-- Brand / Logo Center -->
            <a href="/" class="flex items-center gap-2 group">
                <motion.img
                    src="/logo.png"
                    alt="Ideostore Logo"
                    :while-hover="{ rotate: 6, scale: 1.05 }"
                    :while-tap="{ scale: 0.92 }"
                    class="w-8 h-8 object-contain transition-transform"
                />
                <span class="text-base font-bold tracking-tight text-slate-900">
                    Ideo<span class="text-royal-700">store</span>
                </span>
            </a>

            <!-- Tagline Ringkas -->
            <p class="text-xs text-slate-600 max-w-sm">
                Toko aplikasi & lisensi digital premium terpercaya di Indonesia.
            </p>

            <!-- Copyright Center -->
            <p class="text-[11px] sm:text-xs text-slate-600 mt-1">
                © {{ currentYear }} Ideostore. All rights reserved.
            </p>
        </div>
    </footer>
</template>
