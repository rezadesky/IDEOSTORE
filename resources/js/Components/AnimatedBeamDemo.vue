<script setup>
import { ref } from 'vue';
import AnimatedBeam from './AnimatedBeam.vue';

const containerRef = ref(null);
const div1Ref = ref(null);
const div2Ref = ref(null);
const div3Ref = ref(null);
const div4Ref = ref(null);
const div5Ref = ref(null);
const div6Ref = ref(null);
const div7Ref = ref(null);
</script>

<template>
    <div
        ref="containerRef"
        class="relative flex h-[350px] sm:h-[400px] w-full max-w-2xl items-center justify-center p-2 sm:p-6"
    >
        <!-- 3 Columns: Left (3), Center (1 Core), Right (3) persis demo Magic UI -->
        <div class="flex size-full max-h-[260px] items-stretch justify-between relative z-10">
            <!-- Kolom Kiri: Google Drive, Notion, WhatsApp -->
            <div class="flex flex-col justify-between items-center py-1">
                <!-- Google Drive -->
                <div
                    ref="div1Ref"
                    class="z-10 flex size-12 sm:size-14 items-center justify-center rounded-full bg-white p-3 shadow-[0_4px_25px_-5px_rgba(0,0,0,0.12),0_8px_10px_-6px_rgba(0,0,0,0.08)] border border-slate-100 transition-transform hover:scale-105"
                >
                    <svg class="size-6 sm:size-7" viewBox="0 0 87.3 78" xmlns="http://www.w3.org/2000/svg">
                        <path d="m6.6 66.85 3.85 6.65c.8 1.4 1.95 2.5 3.3 3.3l13.75-23.8h-27.5c0 1.55.4 3.1 1.2 4.5z" fill="#0066da" />
                        <path d="m43.65 25-13.75-23.8c-1.35.8-2.5 1.9-3.3 3.3l-25.4 44a9.06 9.06 0 0 0 -1.2 4.5h27.5z" fill="#00ac47" />
                        <path d="m73.55 76.8c1.35-.8 2.5-1.9 3.3-3.3l1.6-2.75 7.65-13.25c.8-1.4 1.2-2.95 1.2-4.5h-27.502l5.852 11.5z" fill="#ea4335" />
                        <path d="m43.65 25 13.75-23.8c-1.35-.8-2.9-1.2-4.5-1.2h-18.5c-1.6 0-3.15.45-4.5 1.2z" fill="#00832d" />
                        <path d="m59.8 53h-32.3l-13.75 23.8c1.35.8 2.9 1.2 4.5 1.2h50.8c1.6 0 3.15-.45 4.5-1.2z" fill="#2684fc" />
                        <path d="m73.4 26.5-12.7-22c-.8-1.4-1.95-2.5-3.3-3.3l-13.75 23.8 16.15 28h27.45c0-1.55-.4-3.1-1.2-4.5z" fill="#ffba00" />
                    </svg>
                </div>

                <!-- Notion -->
                <div
                    ref="div2Ref"
                    class="z-10 flex size-12 sm:size-14 items-center justify-center rounded-full bg-white p-3 shadow-[0_4px_25px_-5px_rgba(0,0,0,0.12),0_8px_10px_-6px_rgba(0,0,0,0.08)] border border-slate-100 transition-transform hover:scale-105"
                >
                    <svg class="size-6 sm:size-7" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6.017 4.313l55.333 -4.087c6.797 -0.583 8.543 -0.19 12.817 2.917l17.663 12.443c2.913 2.14 3.883 2.723 3.883 5.053v68.243c0 4.277 -1.553 6.807 -6.99 7.193L24.467 99.967c-4.08 0.193 -6.023 -0.39 -8.16 -3.113L3.3 79.94c-2.333 -3.113 -3.3 -5.443 -3.3 -8.167V11.113c0 -3.497 1.553 -6.413 6.017 -6.8z" fill="#000" />
                        <path d="M25.92 19.523c-5.247 0.353 -6.437 0.433 -9.417 -1.99L8.927 11.507c-0.77 -0.78 -0.383 -1.753 1.557 -1.947l53.193 -3.887c4.467 -0.39 6.793 1.167 8.54 2.527l9.123 6.61c0.39 0.197 1.36 1.36 0.193 1.36l-54.933 3.307 -0.68 0.047z" fill="#fff" />
                    </svg>
                </div>

                <!-- WhatsApp -->
                <div
                    ref="div3Ref"
                    class="z-10 flex size-12 sm:size-14 items-center justify-center rounded-full bg-white p-3 shadow-[0_4px_25px_-5px_rgba(0,0,0,0.12),0_8px_10px_-6px_rgba(0,0,0,0.08)] border border-slate-100 transition-transform hover:scale-105"
                >
                    <svg class="size-6 sm:size-7" viewBox="0 0 24 24" fill="#25D366" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2ZM12.05 20.15C10.57 20.15 9.12 19.75 7.85 19L7.55 18.82L4.43 19.64L5.26 16.6L5.07 16.3C4.24 14.98 3.81 13.46 3.81 11.91C3.81 7.37 7.51 3.68 12.05 3.68C14.25 3.68 16.31 4.54 17.86 6.09C19.41 7.64 20.27 9.71 20.27 11.91C20.27 16.46 16.58 20.15 12.05 20.15Z"/>
                    </svg>
                </div>
            </div>

            <!-- Kolom Tengah: Center Core OpenAI -->
            <div class="flex items-center justify-center">
                <div
                    ref="div4Ref"
                    class="z-10 flex size-16 sm:size-20 items-center justify-center rounded-full bg-white p-3.5 sm:p-4 shadow-[0_10px_35px_-5px_rgba(0,0,0,0.15),0_8px_16px_-6px_rgba(0,0,0,0.1)] border border-slate-100 transition-transform hover:scale-105"
                >
                    <svg class="size-9 sm:size-11 fill-slate-900" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.2819 9.8211a5.9847 5.9847 0 0 0-.5157-4.9108 6.0462 6.0462 0 0 0-6.5098-2.9A6.0651 6.0651 0 0 0 4.9807 4.1818a5.9847 5.9847 0 0 0-3.9977 2.9 6.0462 6.0462 0 0 0 .7427 7.0966 5.98 5.98 0 0 0 .511 4.9107 6.051 6.051 0 0 0 6.5146 2.9001A5.9847 5.9847 0 0 0 13.2599 24a6.0557 6.0557 0 0 0 5.7718-4.2058 5.9894 5.9894 0 0 0 3.9977-2.9001 6.0557 6.0557 0 0 0-.7475-7.0729zm-9.022 12.6081a4.4755 4.4755 0 0 1-2.8764-1.0408l.1419-.0804 4.7783-2.7582a.7948.7948 0 0 0 .3927-.6813v-6.7369l2.02 1.1686a.071.071 0 0 1 .038.052v5.5826a4.504 4.504 0 0 1-4.4945 4.4944zm-9.6607-4.1254a4.4708 4.4708 0 0 1-.5346-3.0137l.142.0852 4.783 2.7582a.7712.7712 0 0 0 .7806 0l5.8428-3.3685v2.3324a.0804.0804 0 0 1-.0332.0615L9.74 19.9502a4.4992 4.4992 0 0 1-6.1408-1.6464zM2.3408 7.8956a4.485 4.485 0 0 1 2.3655-1.9728V11.6a.7664.7664 0 0 0 .3879.6765l5.8144 3.3543-2.0201 1.1685a.0757.0757 0 0 1-.071 0l-4.8303-2.7865A4.504 4.504 0 0 1 2.3408 7.872zm16.5963 3.8558L13.1038 8.364 15.1192 7.2a.0757.0757 0 0 1 .071 0l4.8303 2.7913a4.4944 4.4944 0 0 1-.6765 8.1042v-5.6772a.79.79 0 0 0-.407-.667zm2.0107-3.0231l-.142-.0852-4.7735-2.7818a.7759.7759 0 0 0-.7854 0L9.409 9.2297V6.8974a.0662.0662 0 0 1 .0284-.0615l4.8303-2.7866a4.4992 4.4992 0 0 1 6.6802 4.66zM8.3065 12.863l-2.02-1.1638a.0804.0804 0 0 1-.038-.0567V6.0742a4.4992 4.4992 0 0 1 7.3757-3.4537l-.142.0805L8.704 5.459a.7948.7948 0 0 0-.3927.6813zm1.0976-2.3654l2.602-1.4998 2.6069 1.4998v2.9994l-2.5974 1.4997-2.6067-1.4997Z" />
                    </svg>
                </div>
            </div>

            <!-- Kolom Kanan: Google Docs, Zapier, Messenger -->
            <div class="flex flex-col justify-between items-center py-1">
                <!-- Google Docs -->
                <div
                    ref="div5Ref"
                    class="z-10 flex size-12 sm:size-14 items-center justify-center rounded-full bg-white p-3 shadow-[0_4px_25px_-5px_rgba(0,0,0,0.12),0_8px_10px_-6px_rgba(0,0,0,0.08)] border border-slate-100 transition-transform hover:scale-105"
                >
                    <svg class="size-6 sm:size-7" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V8L14 2Z" fill="#4285F4"/>
                        <path d="M14 2V8H20L14 2Z" fill="#A1C2FA"/>
                        <path d="M8 12H16V13.5H8V12Z" fill="white"/>
                        <path d="M8 15H16V16.5H8V15Z" fill="white"/>
                        <path d="M8 18H13V19.5H8V18Z" fill="white"/>
                    </svg>
                </div>

                <!-- Zapier -->
                <div
                    ref="div6Ref"
                    class="z-10 flex size-12 sm:size-14 items-center justify-center rounded-full bg-white p-3 shadow-[0_4px_25px_-5px_rgba(0,0,0,0.12),0_8px_10px_-6px_rgba(0,0,0,0.08)] border border-slate-100 transition-transform hover:scale-105"
                >
                    <svg class="size-6 sm:size-7" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L14.5 9.5H22L16 14L18.5 21.5L12 17L5.5 21.5L8 14L2 9.5H9.5L12 2Z" fill="#FF4F00"/>
                    </svg>
                </div>

                <!-- Messenger -->
                <div
                    ref="div7Ref"
                    class="z-10 flex size-12 sm:size-14 items-center justify-center rounded-full bg-white p-3 shadow-[0_4px_25px_-5px_rgba(0,0,0,0.12),0_8px_10px_-6px_rgba(0,0,0,0.08)] border border-slate-100 transition-transform hover:scale-105"
                >
                    <svg class="size-6 sm:size-7" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#0084FF" d="M24 4C13 4 4 12.7 4 23.5c0 5.6 2.4 10.7 6.3 14.2V43l6.2-3.4c2.3.6 4.8 1 7.5 1 11 0 20-8.7 20-19.5S35 4 24 4z"/>
                        <path fill="#FFF" d="M12 28l7-10 4 4 7-6-7 10-4-4z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- 6 Animated Beams Persis Magic UI Demo Definition -->
        <!-- Curvature -75, endYOffset -10, reverse dll sama persis dokumentasi -->
        <AnimatedBeam
            :containerRef="containerRef"
            :fromRef="div1Ref"
            :toRef="div4Ref"
            :curvature="-75"
            :endYOffset="-10"
        />
        <AnimatedBeam
            :containerRef="containerRef"
            :fromRef="div2Ref"
            :toRef="div4Ref"
        />
        <AnimatedBeam
            :containerRef="containerRef"
            :fromRef="div3Ref"
            :toRef="div4Ref"
            :curvature="75"
            :endYOffset="10"
        />

        <AnimatedBeam
            :containerRef="containerRef"
            :fromRef="div5Ref"
            :toRef="div4Ref"
            :curvature="-75"
            :endYOffset="-10"
            :reverse="true"
        />
        <AnimatedBeam
            :containerRef="containerRef"
            :fromRef="div6Ref"
            :toRef="div4Ref"
            :reverse="true"
        />
        <AnimatedBeam
            :containerRef="containerRef"
            :fromRef="div7Ref"
            :toRef="div4Ref"
            :curvature="75"
            :endYOffset="10"
            :reverse="true"
        />
    </div>
</template>
