<script setup>
import OrbitingCircles from './OrbitingCircles.vue';
</script>

<template>
    <!-- Container Proporsional & Pas: h-[320px] sm:h-[360px] lg:h-[380px] -->
    <div class="relative flex h-[320px] sm:h-[360px] lg:h-[380px] w-full max-w-lg items-center justify-center overflow-hidden">
        <!-- Center Core / Logo -->
        <span class="pointer-events-none whitespace-pre-wrap bg-gradient-to-b from-royal-950 via-royal-800 to-royal-600 bg-clip-text text-center text-3xl sm:text-4xl font-extrabold leading-none text-transparent select-none tracking-tight">
            Ideo
        </span>

        <!-- Inner Orbit (Radius 85) - 3 Icon: ChatGPT, Gemini, Canva -->
        <OrbitingCircles
            :radius="85"
            :duration="22"
            :iconSize="42"
        >
            <!-- 1. ChatGPT -->
            <div class="flex size-10 sm:size-11 items-center justify-center rounded-full bg-white p-2 shadow-md border border-slate-100 hover:scale-110 transition-transform cursor-pointer" title="ChatGPT Plus">
                <svg class="size-5 sm:size-6 fill-[#10A37F]" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.2819 9.8211a5.9847 5.9847 0 0 0-.5157-4.9108 6.0462 6.0462 0 0 0-6.5098-2.9A6.0651 6.0651 0 0 0 4.9807 4.1818a5.9847 5.9847 0 0 0-3.9977 2.9 6.0462 6.0462 0 0 0 .7427 7.0966 5.98 5.98 0 0 0 .511 4.9107 6.051 6.051 0 0 0 6.5146 2.9001A5.9847 5.9847 0 0 0 13.2599 24a6.0557 6.0557 0 0 0 5.7718-4.2058 5.9894 5.9894 0 0 0 3.9977-2.9001 6.0557 6.0557 0 0 0-.7475-7.0729zm-9.022 12.6081a4.4755 4.4755 0 0 1-2.8764-1.0408l.1419-.0804 4.7783-2.7582a.7948.7948 0 0 0 .3927-.6813v-6.7369l2.02 1.1686a.071.071 0 0 1 .038.052v5.5826a4.504 4.504 0 0 1-4.4945 4.4944zm-9.6607-4.1254a4.4708 4.4708 0 0 1-.5346-3.0137l.142.0852 4.783 2.7582a.7712.7712 0 0 0 .7806 0l5.8428-3.3685v2.3324a.0804.0804 0 0 1-.0332.0615L9.74 19.9502a4.4992 4.4992 0 0 1-6.1408-1.6464zM2.3408 7.8956a4.485 4.485 0 0 1 2.3655-1.9728V11.6a.7664.7664 0 0 0 .3879.6765l5.8144 3.3543-2.0201 1.1685a.0757.0757 0 0 1-.071 0l-4.8303-2.7865A4.504 4.504 0 0 1 2.3408 7.872zm16.5963 3.8558L13.1038 8.364 15.1192 7.2a.0757.0757 0 0 1 .071 0l4.8303 2.7913a4.4944 4.4944 0 0 1-.6765 8.1042v-5.6772a.79.79 0 0 0-.407-.667zm2.0107-3.0231l-.142-.0852-4.7735-2.7818a.7759.7759 0 0 0-.7854 0L9.409 9.2297V6.8974a.0662.0662 0 0 1 .0284-.0615l4.8303-2.7866a4.4992 4.4992 0 0 1 6.6802 4.66zM8.3065 12.863l-2.02-1.1638a.0804.0804 0 0 1-.038-.0567V6.0742a4.4992 4.4992 0 0 1 7.3757-3.4537l-.142.0805L8.704 5.459a.7948.7948 0 0 0-.3927.6813zm1.0976-2.3654l2.602-1.4998 2.6069 1.4998v2.9994l-2.5974 1.4997-2.6067-1.4997Z" />
                </svg>
            </div>

            <!-- 2. Gemini -->
            <div class="flex size-10 sm:size-11 items-center justify-center rounded-full bg-white p-2 shadow-md border border-slate-100 hover:scale-110 transition-transform cursor-pointer" title="Google Gemini Advanced">
                <svg class="size-5 sm:size-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="gemini-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#1BA1E3" />
                            <stop offset="50%" stop-color="#5468FF" />
                            <stop offset="100%" stop-color="#9C40FF" />
                        </linearGradient>
                    </defs>
                    <path fill="url(#gemini-grad)" d="M12 24C12 17.3726 6.62742 12 0 12C6.62742 12 12 6.62742 12 0C12 6.62742 17.3726 12 24 12C17.3726 12 12 17.3726 12 24Z" />
                </svg>
            </div>

            <!-- 3. Canva (Official Script Wordmark in Gradient Circle) -->
            <div class="flex size-10 sm:size-11 items-center justify-center rounded-full bg-white p-1.5 shadow-md border border-slate-100 hover:scale-110 transition-transform cursor-pointer" title="Canva Pro">
                <div class="size-7 sm:size-8 rounded-full bg-gradient-to-tr from-[#00C4CC] to-[#7D2AE8] flex items-center justify-center text-white shadow-2xs">
                    <span class="font-serif italic font-extrabold text-sm sm:text-base tracking-tight -translate-y-0.5">C</span>
                </div>
            </div>
        </OrbitingCircles>

        <!-- Outer Orbit (Radius 148) with Reverse Direction - 4 Icon: Netflix, Spotify, CapCut, YouTube -->
        <OrbitingCircles
            :radius="148"
            :duration="28"
            :iconSize="44"
            :reverse="true"
        >
            <!-- 4. Netflix (Official Bold Red N) -->
            <div class="flex size-10 sm:size-11 items-center justify-center rounded-full bg-white p-2 shadow-lg border border-slate-100 hover:scale-110 transition-transform cursor-pointer" title="Netflix Premium">
                <svg class="h-5 sm:h-5.5 w-auto" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Left Bar -->
                    <path d="M5.5 0V24L9.5 22.8V0H5.5Z" fill="#B81D24" />
                    <!-- Right Bar -->
                    <path d="M14.5 0V22.8L18.5 24V0H14.5Z" fill="#B81D24" />
                    <!-- Slanted Ribbon Bar (Bright Red on Top) -->
                    <path d="M5.5 0H9.5L18.5 24H14.5L5.5 0Z" fill="#E50914" />
                </svg>
            </div>

            <!-- 5. Spotify -->
            <div class="flex size-10 sm:size-11 items-center justify-center rounded-full bg-white p-2 shadow-lg border border-slate-100 hover:scale-110 transition-transform cursor-pointer" title="Spotify Premium">
                <svg class="size-6 sm:size-6.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="11" fill="#1DB954" />
                    <path d="M16.8 16.2c-.2.3-.6.4-.9.2-2.5-1.5-5.7-1.9-9.4-1-.3.1-.7-.1-.8-.4-.1-.3.1-.7.4-.8 4.1-1 7.6-.5 10.5 1.2.3.1.4.5.2.8zm1.3-2.9c-.2.4-.7.5-1.1.3-2.9-1.8-7.3-2.3-10.7-1.3-.4.1-.9-.1-1-.5s.1-.9.5-1c3.9-1.2 8.7-.6 12 1.4.4.2.5.7.3 1.1zm.1-3.1c-3.5-2.1-9.2-2.3-12.6-1.2-.5.2-1.1-.1-1.2-.6-.2-.5.1-1.1.6-1.2 3.8-1.2 10.1-.9 14.1 1.5.5.3.6.9.3 1.4-.3.4-.9.5-1.2.1z" fill="#ffffff" />
                </svg>
            </div>

            <!-- 6. CapCut (Official Overlapping Bowtie Scissors Logo) -->
            <div class="flex size-10 sm:size-11 items-center justify-center rounded-full bg-white p-2 shadow-lg border border-slate-100 hover:scale-110 transition-transform cursor-pointer" title="CapCut Pro">
                <svg class="size-5 sm:size-6 fill-slate-900" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                    <path d="M387.56 109.56l-67.6 67.6-64-64 67.6-67.6c12.5-12.5 32.8-12.5 45.3 0l21.3 21.3c12.5 12.5 12.5 32.8 0 45.3zM233.96 177.16l-67.6 67.6-140.2-140.2c-12.5-12.5-12.5-32.8 0-45.3l21.3-21.3c12.5-12.5 32.8-12.5 45.3 0l141.2 141.2zm-141.2 141.2l67.6-67.6 64 64-67.6 67.6c-12.5 12.5-32.8 12.5-45.3 0l-21.3-21.3c-12.5-12.5-12.5-32.8 0-45.3zm153.6-67.6l67.6-67.6 140.2 140.2c12.5 12.5 12.5 32.8 0 45.3l-21.3 21.3c-12.5 12.5-32.8 12.5-45.3 0l-141.2-141.2z" />
                </svg>
            </div>

            <!-- 7. YouTube -->
            <div class="flex size-10 sm:size-11 items-center justify-center rounded-full bg-white p-2 shadow-lg border border-slate-100 hover:scale-110 transition-transform cursor-pointer" title="YouTube Premium">
                <svg class="size-6 sm:size-6.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814z" fill="#FF0000" />
                    <path d="M9.545 15.568V8.432L15.818 12l-6.273 3.568z" fill="#ffffff" />
                </svg>
            </div>
        </OrbitingCircles>
    </div>
</template>
