<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    error: {
        type: String,
        default: null
    }
});

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: true,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Login Admin - Ideostore" />

    <div class="min-h-screen bg-slate-50 flex flex-col justify-center items-center p-4 relative overflow-hidden">
        <!-- Background Soft Decorative Orbs -->
        <div class="absolute -top-32 -left-32 w-96 h-96 bg-royal-100/70 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-indigo-100/60 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-md relative z-10">
            <!-- Back to Home -->
            <a href="/" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-royal-700 transition-colors mb-6">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Toko
            </a>

            <!-- Card Box (Light Modern Aesthetic) -->
            <div class="bg-white/90 backdrop-blur-xl border border-slate-200/90 rounded-2xl p-6 sm:p-8 shadow-xl shadow-slate-200/50">
                <!-- Header -->
                <div class="text-center mb-6">
                    <div class="w-12 h-12 bg-royal-50 text-royal-700 border border-royal-200/70 rounded-xl mx-auto flex items-center justify-center mb-3 shadow-xs">
                        <svg class="w-6 h-6 text-royal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Admin Portal</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Masuk untuk mengelola produk & transaksi Ideostore</p>
                </div>

                <!-- Error Alert -->
                <div v-if="error || form.errors.email || form.errors.password" class="mb-5 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2.5">
                    <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ error || form.errors.email || form.errors.password }}</span>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">Email Admin</label>
                        <div class="relative">
                            <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                required
                                autofocus
                                placeholder="admin@ideolog.tech"
                                class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-royal-600 focus:bg-white focus:ring-2 focus:ring-royal-100 transition-all font-medium"
                            />
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">Kata Sandi</label>
                        <div class="relative">
                            <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                required
                                placeholder="••••••••"
                                class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-royal-600 focus:bg-white focus:ring-2 focus:ring-royal-100 transition-all font-medium"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition-colors"
                                aria-label="Toggle password visibility"
                            >
                                <svg v-if="showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.remember"
                                class="w-4 h-4 rounded border-slate-300 text-royal-600 focus:ring-royal-500"
                            />
                            <span class="text-xs text-slate-600 select-none">Ingat saya</span>
                        </label>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full mt-2 py-2.5 px-4 bg-royal-700 hover:bg-royal-800 disabled:opacity-50 text-white text-sm font-semibold rounded-xl shadow-md shadow-royal-700/20 transition-all flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <span v-if="form.processing">Memverifikasi...</span>
                        <span v-else>Masuk ke Panel Admin</span>
                    </button>
                </form>
            </div>

            <!-- Footer info -->
            <p class="text-center text-[11px] text-slate-500 mt-6">
                © {{ new Date().getFullYear() }} Ideostore Security Layer • Akses terbatas
            </p>
        </div>
    </div>
</template>
