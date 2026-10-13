<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    duration: {
        type: Number,
        default: 450
    }
});

const isDark = ref(false);
const isTransitioning = ref(false);

const updateThemeState = () => {
    isDark.value = document.documentElement.classList.contains('dark');
};

onMounted(() => {
    // Check saved theme or system preference
    const saved = localStorage.getItem('theme');
    if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    updateThemeState();
});

const toggleTheme = (event) => {
    if (isTransitioning.value) return;

    const newDark = !isDark.value;

    const applyTheme = () => {
        if (newDark) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
        isDark.value = newDark;
    };

    // If browser does not support View Transitions API, toggle directly
    if (!document.startViewTransition) {
        applyTheme();
        return;
    }

    // Circular clip-path transition originating from click point
    const x = event?.clientX ?? window.innerWidth / 2;
    const y = event?.clientY ?? window.innerHeight / 2;
    const endRadius = Math.hypot(
        Math.max(x, window.innerWidth - x),
        Math.max(y, window.innerHeight - y)
    );

    isTransitioning.value = true;
    const transition = document.startViewTransition(() => {
        applyTheme();
    });

    transition.ready.then(() => {
        const clipPath = [
            `circle(0px at ${x}px ${y}px)`,
            `circle(${endRadius}px at ${x}px ${y}px)`
        ];

        document.documentElement.animate(
            {
                clipPath: newDark ? clipPath : [...clipPath].reverse()
            },
            {
                duration: props.duration,
                easing: 'ease-in-out',
                pseudoElement: newDark ? '::view-transition-new(root)' : '::view-transition-old(root)'
            }
        ).onfinish = () => {
            isTransitioning.value = false;
        };
    }).catch(() => {
        isTransitioning.value = false;
    });
};
</script>

<template>
    <button
        type="button"
        @click="toggleTheme"
        class="relative inline-flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-xl border border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 text-slate-700 dark:text-amber-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors shadow-2xs cursor-pointer select-none"
        title="Ganti Tema (Light / Dark)"
        aria-label="Toggle Theme"
    >
        <!-- Sun Icon (Tampil saat Dark) -->
        <svg
            v-if="isDark"
            class="w-4 h-4 sm:w-4.5 sm:h-4.5 transition-transform duration-300 rotate-0 scale-100"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>

        <!-- Moon Icon (Tampil saat Light) -->
        <svg
            v-else
            class="w-4 h-4 sm:w-4.5 sm:h-4.5 transition-transform duration-300 rotate-0 scale-100"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
    </button>
</template>

<style>
::view-transition-old(root),
::view-transition-new(root) {
    animation: none;
    mix-blend-mode: normal;
}
</style>
