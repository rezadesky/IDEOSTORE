<script setup>
import { ref } from 'vue';
import { motion, AnimatePresence } from 'motion-v';

const faqs = [
    {
        id: 1,
        question: 'Bagaimana cara membeli aplikasi atau lisensi di Ideostore?',
        answer: 'Pilih aplikasi yang kamu butuhkan dari katalog kami di atas, klik produk untuk memilih paket atau durasi, lalu klik tombol pesan. Kamu akan diarahkan langsung ke WhatsApp/checkout otomatis untuk menyelesaikan pembayaran dan menerima detail akun.',
    },
    {
        id: 2,
        question: 'Apakah akun dan lisensi yang dijual bergaransi?',
        answer: 'Ya, seluruh produk kami memiliki garansi penuh sesuai masa aktif yang tertera (mulai dari 7 hari hingga 30 hari penuh). Jika ada kendala login atau akun bermasalah, tim support kami siap membantu perbaikan atau penggantian akun secepatnya.',
    },
    {
        id: 3,
        question: 'Berapa lama proses pengiriman akun setelah pembayaran?',
        answer: 'Sebagian besar produk kami diproses secara otomatis dan instan dalam 1–5 menit setelah konfirmasi pembayaran berhasil. Detail akun atau link aktivasi akan dikirimkan langsung ke nomor WhatsApp kamu.',
    },
    {
        id: 4,
        question: 'Bagaimana cara melacak atau mengecek status pesanan saya?',
        answer: 'Kamu cukup memasukkan nomor pesanan (Order ID) atau nomor WhatsApp yang kamu gunakan saat checkout pada kolom "Cek pesanan kamu" di atas, lalu klik "Lacak Pesanan". Status lisensi dan akun akan langsung tampil realtime.',
    },
    {
        id: 5,
        question: 'Apa perbedaan akun Private dan akun Patungan (Sharing)?',
        answer: 'Akun Private adalah akun eksklusif milik kamu sendiri dengan email/profil khusus tanpa ada pengguna lain. Sedangkan akun Patungan adalah akun bersama dengan slot profil khusus per pengguna, dengan harga yang jauh lebih hemat dan terjangkau.',
    },
    {
        id: 6,
        question: 'Metode pembayaran apa saja yang didukung?',
        answer: 'Kami mendukung berbagai metode pembayaran mudah dan instan di Indonesia, meliputi QRIS (semua e-wallet & m-banking), GoPay, OVO, DANA, ShopeePay, serta transfer bank (BCA, Mandiri, BNI, BRI).',
    },
];

const openFaq = ref(null);

const toggleFaq = (id) => {
    openFaq.value = openFaq.value === id ? null : id;
};
</script>

<template>
    <section class="mt-16 sm:mt-20 w-full bg-white py-12 sm:py-16 lg:py-20 text-slate-900 border-t border-slate-200/80 relative">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col gap-8 sm:gap-10">
            <!-- Section Header (Clean Light Theme) -->
            <div class="flex flex-col items-center text-center max-w-xl mx-auto">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-royal-50 text-royal-700 text-xs font-semibold border border-royal-200/60 shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Pusat Bantuan & Tanya Jawab</span>
                </span>
                <h2 class="mt-3 text-xl sm:text-2xl lg:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Pertanyaan yang Sering Diajukan
                </h2>
                <p class="mt-1.5 text-xs sm:text-sm text-slate-500">
                    Temukan jawaban cepat seputar pemesanan, akun resmi, garansi, dan sistem pengiriman kami.
                </p>
            </div>

            <!-- FAQ Accordion List (Card Warna Royal Blue) -->
            <div class="max-w-3xl w-full mx-auto flex flex-col gap-2.5 sm:gap-3">
                <div
                    v-for="faq in faqs"
                    :key="faq.id"
                    class="rounded-xl sm:rounded-2xl bg-gradient-to-r from-royal-900 via-royal-800 to-royal-900 text-white shadow-md shadow-royal-950/15 border border-royal-700/60 overflow-hidden transition-all duration-200"
                    :class="{ 'ring-2 ring-royal-400 shadow-lg': openFaq === faq.id }"
                >
                    <button
                        type="button"
                        @click="toggleFaq(faq.id)"
                        class="w-full flex items-center justify-between gap-4 p-4 sm:p-5 text-left text-xs sm:text-sm font-bold text-white hover:text-royal-200 transition-colors cursor-pointer select-none"
                    >
                        <span class="flex items-center gap-3">
                            <span
                                class="w-6 h-6 rounded-lg flex items-center justify-center text-[11px] font-bold shrink-0 transition-colors"
                                :class="openFaq === faq.id ? 'bg-white text-royal-900' : 'bg-white/15 text-white'"
                            >
                                {{ faq.id }}
                            </span>
                            <span>{{ faq.question }}</span>
                        </span>
                        <div
                            class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 transition-transform duration-200 text-royal-200"
                            :class="{ 'rotate-180 text-white bg-white/20': openFaq === faq.id }"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </button>

                    <!-- Accordion Answer with Smooth Transition -->
                    <div
                        v-show="openFaq === faq.id"
                        class="px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs sm:text-sm text-royal-100/90 leading-relaxed border-t border-royal-700/60 bg-royal-950/30"
                    >
                        <div class="pt-3 pl-9">
                            {{ faq.answer }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
