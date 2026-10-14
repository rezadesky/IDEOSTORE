<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { motion, AnimatePresence } from 'motion-v';
import FloatingWhatsApp from '../Components/FloatingWhatsApp.vue';
import AnimatedThemeToggler from '../Components/AnimatedThemeToggler.vue';
import { contactConfig } from '../config/contact';

const props = defineProps({
    initialQuery: {
        type: String,
        default: ''
    }
});

const searchQuery = ref(props.initialQuery || '');
const loading = ref(false);
const orderResult = ref(null);
const errorMsg = ref('');
const copiedField = ref('');
let pollInterval = null;
let pollStartTime = 0;

const stopPolling = () => {
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
};

const startPolling = (orderId) => {
    stopPolling();
    pollStartTime = Date.now();
    pollInterval = setInterval(async () => {
        // Hentikan otomatis setelah 30 menit (hemat bandwidth & CPU)
        if (Date.now() - pollStartTime > 30 * 60 * 1000) {
            stopPolling();
            return;
        }

        try {
            const res = await fetch(`/api/order/${orderId}`);
            const data = await res.json();
            if (data.ok && data.order) {
                if (orderResult.value) {
                    orderResult.value = {
                        ...orderResult.value,
                        ...data.order,
                        qr_url: data.order.qr_url || data.order.payment?.qr_url || orderResult.value.qr_url,
                        note: data.order.note || ''
                    };
                }
                if (data.order.status === 'success' || data.order.status === 'failed' || data.order.status === 'cancelled') {
                    stopPolling();
                }
            }
        } catch (e) {
            console.error('Polling error:', e);
        }
    }, 4000);
};

const checkingStatus = ref(false);
const statusFeedback = ref('');

const checkTransactionStatus = async () => {
    if (!orderResult.value) return;
    const targetId = orderResult.value.id || orderResult.value.ref || searchQuery.value.trim();
    if (!targetId) return;

    checkingStatus.value = true;
    statusFeedback.value = '';

    try {
        const res = await fetch(`/api/order/${encodeURIComponent(targetId)}`);
        const data = await res.json();
        if (data.ok && data.order) {
            orderResult.value = {
                ...orderResult.value,
                ...data.order,
                qr_url: data.order.qr_url || data.order.payment?.qr_url || orderResult.value.qr_url,
                note: (data.order.note && !data.order.note.startsWith('{')) ? data.order.note : ''
            };
            
            if (data.order.status === 'success') {
                statusFeedback.value = 'Pembayaran terkonfirmasi LUNAS! Data akun kamu sudah siap di bawah.';
                stopPolling();
            } else if (data.order.status === 'processing') {
                statusFeedback.value = 'Pembayaran terkonfirmasi! Akun kamu sedang diproses oleh sistem.';
            } else if (data.order.status === 'cancelled') {
                statusFeedback.value = 'Transaksi pesanan ini telah dibatalkan.';
                stopPolling();
            } else {
                statusFeedback.value = 'Status: Menunggu Pembayaran QRIS. Belum ada pembayaran masuk.';
            }
        } else {
            statusFeedback.value = data.error || 'Gagal mengecek status pembayaran.';
        }
    } catch (err) {
        statusFeedback.value = 'Gangguan jaringan saat mengecek status.';
    } finally {
        checkingStatus.value = false;
        setTimeout(() => {
            statusFeedback.value = '';
        }, 5000);
    }
};

const searchOrder = async () => {
    const q = searchQuery.value.trim();
    if (!q) return;

    stopPolling();
    loading.value = true;
    errorMsg.value = '';

    try {
        const res = await fetch(`/api/check-order?q=${encodeURIComponent(q)}`);
        const data = await res.json();
        if (data.ok && data.order) {
            orderResult.value = {
                ...data.order,
                qr_url: data.order.qr_url || data.order.payment?.qr_url || '',
                note: (data.order.note && !data.order.note.startsWith('{')) ? data.order.note : ''
            };
            // Jika statusnya pending, nyalakan polling realtime
            if (data.order.status === 'pending' && (data.order.id || data.order.ref)) {
                startPolling(data.order.id || data.order.ref);
            }
        } else {
            errorMsg.value = data.error || 'Pesanan tidak ditemukan. Pastikan ID pesanan, nomor WhatsApp, atau email sudah benar.';
            orderResult.value = null;
        }
    } catch (err) {
        errorMsg.value = 'Gagal terhubung ke server. Silakan coba sesaat lagi.';
    } finally {
        loading.value = false;
    }
};

const cancelLoading = ref(false);
const cancelSuccess = ref('');

const cancelOrder = async () => {
    if (!orderResult.value) return;
    const orderIdentifier = orderResult.value.id || orderResult.value.ref;
    if (!orderIdentifier) return;

    if (!confirm('Apakah kamu yakin ingin membatalkan transaksi pesanan ini?')) {
        return;
    }

    cancelLoading.value = true;
    errorMsg.value = '';

    try {
        const res = await fetch(`/api/order/${encodeURIComponent(orderIdentifier)}/cancel`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        const data = await res.json();
        if (data.ok) {
            orderResult.value.status = 'cancelled';
            orderResult.value.note = 'Pesanan telah dibatalkan.';
            cancelSuccess.value = 'Pesanan berhasil dibatalkan.';
            stopPolling();
        } else {
            alert(data.error || 'Gagal membatalkan pesanan.');
        }
    } catch (err) {
        alert('Terjadi kesalahan jaringan saat membatalkan pesanan.');
    } finally {
        cancelLoading.value = false;
    }
};

const copyToClipboard = (text, fieldName) => {
    navigator.clipboard.writeText(text);
    copiedField.value = fieldName;
    setTimeout(() => {
        copiedField.value = '';
    }, 2000);
};

const getStatusBadge = (status) => {
    const s = (status || '').toLowerCase();
    if (s === 'completed' || s === 'sukses' || s === 'success') {
        return {
            label: 'Selesai',
            bg: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            dot: 'bg-emerald-500'
        };
    }
    if (s === 'processing' || s === 'proses') {
        return {
            label: 'Sedang Diproses',
            bg: 'bg-blue-50 text-blue-700 border-blue-200',
            dot: 'bg-blue-500'
        };
    }
    if (s === 'pending' || s === 'menunggu') {
        return {
            label: 'Menunggu Pembayaran',
            bg: 'bg-amber-50 text-amber-700 border-amber-200',
            dot: 'bg-amber-500'
        };
    }
    if (s === 'failed' || s === 'batal' || s === 'cancelled') {
        return {
            label: 'Dibatalkan / Gagal',
            bg: 'bg-rose-50 text-rose-700 border-rose-200',
            dot: 'bg-rose-500'
        };
    }
    return {
        label: status || 'Pending',
        bg: 'bg-slate-50 text-slate-700 border-slate-200',
        dot: 'bg-slate-400'
    };
};

const getWhatsAppUrl = () => {
    if (!orderResult.value) return 'https://wa.me/' + contactConfig.whatsappNumber.replace(/[^0-9]/g, '');
    const num = contactConfig.whatsappNumber.replace(/[^0-9]/g, '');
    const orderId = orderResult.value.ref || orderResult.value.id || '-';
    const prod = orderResult.value.product_name || orderResult.value.name || 'Produk';
    const total = Number(orderResult.value.total || orderResult.value.price || 0).toLocaleString('id-ID');
    const status = orderResult.value.status || 'pending';
    
    let msg = `Halo Admin Ideostore, saya ingin konfirmasi pesanan:\n\n`
            + `• No. Pesanan: ${orderId}\n`
            + `• Produk: ${prod}\n`
            + `• Total: Rp ${total}\n`
            + `• Status: ${status.toUpperCase()}\n\n`
            + `Mohon bantuannya, terima kasih!`;
            
    return `https://wa.me/${num}?text=${encodeURIComponent(msg)}`;
};

onMounted(() => {
    // Cek query parameter di URL jika ada
    const urlParams = new URLSearchParams(window.location.search);
    const q = urlParams.get('q');
    if (q) {
        searchQuery.value = q;
        searchOrder();
    }
});

onUnmounted(() => {
    stopPolling();
});
</script>

<template>
    <Head title="Lacak Pesanan - Ideostore" />

    <div class="min-h-screen bg-[#f8fafc] text-slate-800 flex flex-col font-sans selection:bg-royal-600 selection:text-white">
        <!-- Top Minimal Navbar -->
        <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200/80">
            <div class="max-w-4xl mx-auto px-4 sm:px-6">
                <div class="flex items-center justify-between h-14">
                    <!-- Brand -->
                    <Link href="/" class="flex items-center gap-2.5 group">
                        <img
                            src="/logo.png"
                            alt="Ideostore Logo"
                            class="w-8 h-8 object-contain transition-transform group-hover:scale-105"
                        />
                        <span class="text-base sm:text-lg font-bold tracking-tight text-slate-900">
                            Ideo<span class="text-royal-700">store</span>
                        </span>
                    </Link>

                    <!-- Actions -->
                    <div class="flex items-center gap-2.5">
                        <AnimatedThemeToggler />

                        <Link
                            href="/"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-royal-700 dark:hover:text-royal-400 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Kembali ke Beranda</span>
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 py-8 sm:py-12 flex flex-col">
            <!-- Header Section -->
            <div class="text-center max-w-lg mx-auto mb-8 sm:mb-10">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-royal-50 text-royal-700 border border-royal-100 mb-4 shadow-xs">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Lacak Pesanan Kamu
                </h1>
                <p class="mt-2 text-xs sm:text-sm text-slate-500 leading-relaxed">
                    Masukkan nomor pesanan (ID), nomor WhatsApp, atau email yang digunakan saat pemesanan untuk melihat status aktivasi secara realtime.
                </p>
            </div>

            <!-- Search Form (Clean Minimal Card) -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-4 sm:p-6 mb-8">
                <form @submit.prevent="searchOrder" class="flex flex-col sm:flex-row gap-2.5">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Contoh: IDEO-..., 3656, 081234567890, atau email..."
                            class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-royal-600 focus:border-transparent transition-all"
                            required
                        />
                    </div>
                    <motion.button
                        type="submit"
                        :disabled="loading"
                        :while-hover="{ scale: 1.01 }"
                        :while-tap="{ scale: 0.98 }"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-royal-700 hover:bg-royal-800 active:bg-royal-900 text-white text-sm font-semibold shadow-xs transition-all disabled:opacity-60 cursor-pointer"
                    >
                        <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>{{ loading ? 'Mencari...' : 'Lacak Sekarang' }}</span>
                    </motion.button>
                </form>

                <div class="mt-3 flex items-center gap-2 text-[11px] text-slate-400">
                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Kamu bisa mencari dengan Kode Transaksi, WhatsApp (08xxx), atau Email pembelian.</span>
                </div>
            </div>

            <!-- Error State -->
            <AnimatePresence>
                <motion.div
                    v-if="errorMsg"
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :exit="{ opacity: 0, y: -10 }"
                    class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3.5 rounded-xl text-xs sm:text-sm flex items-start gap-3 mb-8"
                >
                    <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="flex-1">
                        <span class="font-semibold block mb-0.5">Pesanan Tidak Ditemukan</span>
                        <span>{{ errorMsg }}</span>
                    </div>
                </motion.div>
            </AnimatePresence>

            <!-- Order Result Card -->
            <AnimatePresence>
                <motion.div
                    v-if="orderResult"
                    :initial="{ opacity: 0, y: 15 }"
                    :animate="{ opacity: 1, y: 0 }"
                    class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden"
                >
                    <!-- Card Top Header -->
                    <div class="p-5 sm:p-6 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 block uppercase tracking-wider mb-1">
                                Nomor Pesanan
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-base sm:text-lg font-bold text-slate-900 select-all">
                                    {{ orderResult.ref || orderResult.id }}
                                </span>
                                <button
                                    @click="copyToClipboard(orderResult.ref || orderResult.id, 'ref')"
                                    class="p-1 rounded-md text-slate-400 hover:text-royal-700 hover:bg-white border border-transparent hover:border-slate-200 transition-all text-xs"
                                    title="Salin ID Pesanan"
                                >
                                    <span v-if="copiedField === 'ref'" class="text-emerald-600 font-medium text-[11px]">Tersalin!</span>
                                    <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div>
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border"
                                :class="getStatusBadge(orderResult.status).bg"
                            >
                                <span class="w-2 h-2 rounded-full" :class="getStatusBadge(orderResult.status).dot"></span>
                                {{ getStatusBadge(orderResult.status).label }}
                            </span>
                        </div>
                    </div>

                    <!-- Details Body -->
                    <div class="p-5 sm:p-6 flex flex-col gap-6">
                        <!-- Product & Price Information Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">
                                    Produk yang Dipesan
                                </span>
                                <h3 class="text-sm sm:text-base font-bold text-slate-900">
                                    {{ orderResult.product }}
                                </h3>
                                <span class="text-xs text-slate-500 mt-1 block">
                                    Jumlah: <strong>{{ orderResult.qty || 1 }} Akun/Lisensi</strong>
                                </span>
                            </div>

                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex flex-col justify-center">
                                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">
                                    Total Pembayaran
                                </span>
                                <div class="text-lg sm:text-xl font-black text-slate-900">
                                    Rp {{ Number(orderResult.total || orderResult.price || 0).toLocaleString('id-ID') }}
                                </div>
                                <span class="text-[11px] text-emerald-600 font-semibold mt-0.5">
                                    Metode: QRIS Otomatis
                                </span>
                            </div>
                        </div>

                        <!-- License / Credentials Box (Jika Status Selesai) -->
                        <div v-if="orderResult.accounts && orderResult.accounts.length" class="rounded-xl border border-emerald-200 bg-emerald-50/40 p-4 sm:p-5">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-md bg-emerald-600 text-white flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-xs sm:text-sm font-bold text-emerald-950">
                                        Data Akun / Lisensi Kamu
                                    </span>
                                </div>
                                <span class="text-[11px] text-emerald-700 font-medium">Siap Digunakan</span>
                            </div>

                            <div class="space-y-2">
                                <div
                                    v-for="(acc, i) in orderResult.accounts"
                                    :key="i"
                                    class="p-3.5 rounded-lg bg-white border border-emerald-200/80 font-mono text-xs sm:text-sm text-slate-800 whitespace-pre-wrap select-all flex items-start justify-between gap-3 shadow-2xs"
                                >
                                    <span class="break-all">{{ acc }}</span>
                                    <button
                                        @click="copyToClipboard(acc, 'acc_' + i)"
                                        class="shrink-0 p-1.5 rounded-md hover:bg-slate-100 text-slate-500 hover:text-emerald-700 transition-colors"
                                        title="Salin Akun"
                                    >
                                        <span v-if="copiedField === 'acc_' + i" class="text-emerald-600 font-sans font-bold text-[10px]">Disalin</span>
                                        <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Jika status Pending (Tampilkan QRIS & Polling Otomatis) -->
                        <div v-else-if="orderResult.status === 'pending'" class="rounded-2xl border border-royal-200 bg-royal-50/40 p-5 sm:p-6 flex flex-col items-center justify-center gap-4 text-center">
                            <span class="text-xs font-bold text-royal-950 uppercase tracking-wider">Scan QRIS untuk Bayar Otomatis</span>

                            <!-- Gambar QR Code -->
                            <div v-if="orderResult.qr_url" class="p-3 bg-white rounded-2xl shadow-md border border-slate-200">
                                <img :src="orderResult.qr_url" alt="QRIS Pembayaran" class="w-52 h-52 object-contain" />
                            </div>

                            <!-- Nominal Bayar -->
                            <div>
                                <span class="text-xs text-slate-500 block mb-0.5">Total yang Harus Dibayar:</span>
                                <div class="text-2xl font-black text-royal-800">
                                    Rp {{ Number(orderResult.total || orderResult.price || 0).toLocaleString('id-ID') }}
                                </div>
                                <span class="text-[11px] text-slate-400 block mt-1">Mendukung BCA, Mandiri, GoPay, OVO, Dana, ShopeePay, dll</span>
                            </div>

                            <!-- Deteksi Otomatis Live Polling -->
                            <div class="flex items-center gap-2 text-xs text-emerald-600 bg-emerald-50 px-3.5 py-1.5 rounded-full border border-emerald-200 font-medium">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                <span>Mendeteksi status pembayaran realtime...</span>
                            </div>

                            <!-- Notifikasi Feedback Status -->
                            <div v-if="statusFeedback" class="w-full max-w-sm px-3.5 py-2.5 rounded-xl text-xs font-semibold bg-royal-100 text-royal-900 border border-royal-200 transition-all text-center">
                                {{ statusFeedback }}
                            </div>

                            <!-- Tombol Aksi Transaksi -->
                            <div class="flex flex-col sm:flex-row items-center justify-center gap-2.5 w-full max-w-sm pt-1">
                                <button
                                    type="button"
                                    @click="checkTransactionStatus"
                                    :disabled="checkingStatus"
                                    class="w-full sm:flex-1 py-2.5 px-4 rounded-xl bg-royal-700 hover:bg-royal-800 active:bg-royal-900 text-white text-xs font-bold transition-all shadow-2xs flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60"
                                >
                                    <svg v-if="checkingStatus" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span>{{ checkingStatus ? 'Mengecek...' : 'Cek Status Transaksi' }}</span>
                                </button>
                                <button
                                    type="button"
                                    @click="cancelOrder"
                                    :disabled="cancelLoading"
                                    class="w-full sm:flex-1 py-2.5 px-4 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-60"
                                >
                                    <svg v-if="cancelLoading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span>Batalkan Transaksi</span>
                                </button>
                            </div>
                        </div>

                        <!-- Jika status Processing -->
                        <div v-else-if="orderResult.status === 'processing'" class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 sm:p-5 flex items-start gap-3.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 text-royal-700 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="text-xs sm:text-sm text-slate-600">
                                <h4 class="font-bold text-slate-900 mb-1">
                                    Pesanan Sedang Diproses
                                </h4>
                                <p>
                                    Pembayaran berhasil diterima! Akun/lisensi Anda sedang disiapkan dan akan segera tampil di sini begitu proses selesai.
                                </p>
                            </div>
                        </div>

                        <!-- Status Lainnya (Batal/Expired) -->
                        <div v-else class="rounded-xl border border-rose-200 bg-rose-50/60 p-4 sm:p-5 text-xs sm:text-sm text-rose-700">
                            <h4 class="font-bold mb-1">Status: {{ getStatusBadge(orderResult.status).label }}</h4>
                            <p>Pesanan ini telah kedaluwarsa atau dibatalkan. Silakan buat pesanan baru jika ingin melakukan pembelian.</p>
                        </div>

                        <!-- Catatan Tambahan Jika Ada -->
                        <div v-if="orderResult.note && !orderResult.note.startsWith('{')" class="text-xs text-slate-500 bg-slate-50 p-3 rounded-lg border border-slate-200/60">
                            <span class="font-semibold text-slate-700">Catatan:</span> {{ orderResult.note }}
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="p-4 sm:p-5 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                        <span class="text-slate-500 text-center sm:text-left">
                            Mengalami kendala pada pesanan Anda?
                        </span>
                        <a
                            :href="getWhatsAppUrl()"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 font-bold text-royal-700 hover:text-royal-800 transition-colors"
                        >
                            <span>Hubungi Bantuan WhatsApp</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </motion.div>
            </AnimatePresence>
        </main>

        <!-- Floating WhatsApp Button -->
        <FloatingWhatsApp />
    </div>
</template>

