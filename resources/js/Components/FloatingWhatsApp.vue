<script setup>
import { computed } from 'vue';
import { contactConfig } from '../config/contact';

const props = defineProps({
    phoneNumber: {
        type: String,
        default: () => contactConfig.whatsappNumber
    },
    message: {
        type: String,
        default: () => contactConfig.defaultMessage
    }
});

const whatsappUrl = computed(() => {
    const cleanNumber = props.phoneNumber.replace(/[^0-9]/g, '');
    const encodedMsg = encodeURIComponent(props.message);
    return `https://wa.me/${cleanNumber}?text=${encodedMsg}`;
});
</script>

<template>
    <!-- Floating Wrapper: Posisikan di pojok kanan bawah -->
    <div class="fixed bottom-5 right-5 z-40 select-none">
        <!-- Tombol Utama dengan Efek Subtle Floating (Mengapung Naik-Turun Lembut) -->
        <a
            :href="whatsappUrl"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Hubungi kami via WhatsApp"
            title="Chat WhatsApp Admin"
            class="relative flex items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg shadow-black/25 hover:scale-110 active:scale-95 transition-all duration-300 ease-out cursor-pointer w-[54px] h-[54px] sm:w-[60px] sm:h-[60px] focus:outline-none focus:ring-4 focus:ring-[#25D366]/40 animate-float"
        >
            <!-- WhatsApp SVG Icon -->
            <svg
                class="w-7 h-7 sm:w-8 sm:h-8 fill-current drop-shadow-xs"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    d="M17.472 14.382c-.301-.15-1.78-.878-2.056-.978-.275-.1-.476-.15-.677.15-.201.301-.778.979-.954 1.18-.175.201-.351.226-.652.075-.301-.15-1.272-.469-2.423-1.496-.895-.798-1.5-1.785-1.676-2.086-.175-.301-.019-.464.132-.614.135-.135.301-.351.451-.527.151-.175.201-.301.301-.502.1-.201.05-.376-.025-.526-.075-.15-.677-1.632-.928-2.234-.244-.587-.492-.507-.677-.516-.175-.009-.376-.01-.577-.01-.201 0-.526.075-.802.376-.275.301-1.053 1.028-1.053 2.508 0 1.48 1.079 2.909 1.229 3.11.15.201 2.122 3.24 5.141 4.544.718.31 1.279.496 1.716.635.722.23 1.38.197 1.9.12.58-.087 1.78-.727 2.03-1.43.25-.703.25-1.305.175-1.43-.075-.126-.276-.201-.577-.352zM12.04 2C6.54 2 2.08 6.46 2.08 11.96c0 1.77.46 3.49 1.34 5.01L2 22l5.18-1.36c1.47.8 3.13 1.23 4.86 1.23 5.5 0 9.96-4.46 9.96-9.96C22 6.46 17.54 2 12.04 2zm0 18.24c-1.54 0-3.04-.41-4.36-1.19l-.31-.18-3.24.85.86-3.16-.2-.32a8.204 8.204 0 01-1.26-4.28c0-4.57 3.72-8.29 8.29-8.29 4.57 0 8.29 3.72 8.29 8.29 0 4.57-3.72 8.28-8.27 8.28z"
                />
            </svg>
        </a>
    </div>
</template>

<style scoped>
@keyframes float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-6px);
    }
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}
</style>
