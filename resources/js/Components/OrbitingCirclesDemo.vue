<script setup>
import OrbitingCircles from './OrbitingCircles.vue';
</script>

<template>
    <!-- Container Proporsional: h-[340px] sm:h-[380px] lg:h-[400px] w-full max-w-lg -->
    <div class="relative flex h-[340px] sm:h-[380px] lg:h-[400px] w-full max-w-lg items-center justify-center overflow-hidden">
        <!-- Center Core / Logo Ideostore -->
        <span class="pointer-events-none whitespace-pre-wrap bg-gradient-to-b from-royal-950 via-royal-800 to-royal-600 bg-clip-text text-center text-3xl sm:text-4xl font-extrabold leading-none text-transparent select-none tracking-tight">
            Ideo
        </span>

        <!-- ORBIT 1: INNER ORBIT (Radius 68, Duration 18s) - 4 Layanan AI & Desain Premium Terpopuler -->
        <OrbitingCircles
            :radius="68"
            :duration="18"
            :iconSize="38"
        >
            <!-- 1. ChatGPT Plus (Official Spiral) -->
            <div
                class="flex size-9 sm:size-10 items-center justify-center rounded-full bg-white p-1.5 shadow-md border border-slate-100 hover:scale-125 transition-transform cursor-pointer"
                title="ChatGPT Plus"
            >
                <svg class="size-5 sm:size-5.5 fill-[#10A37F]" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.2819 9.8211a5.9847 5.9847 0 0 0-.5157-4.9108 6.0462 6.0462 0 0 0-6.5098-2.9A6.0651 6.0651 0 0 0 4.9807 4.1818a5.9847 5.9847 0 0 0-3.9977 2.9 6.0462 6.0462 0 0 0 .7427 7.0966 5.98 5.98 0 0 0 .511 4.9107 6.051 6.051 0 0 0 6.5146 2.9001A5.9847 5.9847 0 0 0 13.2599 24a6.0557 6.0557 0 0 0 5.7718-4.2058 5.9894 5.9894 0 0 0 3.9977-2.9001 6.0557 6.0557 0 0 0-.7475-7.0729zm-9.022 12.6081a4.4755 4.4755 0 0 1-2.8764-1.0408l.1419-.0804 4.7783-2.7582a.7948.7948 0 0 0 .3927-.6813v-6.7369l2.02 1.1686a.071.071 0 0 1 .038.052v5.5826a4.504 4.504 0 0 1-4.4945 4.4944zm-9.6607-4.1254a4.4708 4.4708 0 0 1-.5346-3.0137l.142.0852 4.783 2.7582a.7712.7712 0 0 0 .7806 0l5.8428-3.3685v2.3324a.0804.0804 0 0 1-.0332.0615L9.74 19.9502a4.4992 4.4992 0 0 1-6.1408-1.6464zM2.3408 7.8956a4.485 4.485 0 0 1 2.3655-1.9728V11.6a.7664.7664 0 0 0 .3879.6765l5.8144 3.3543-2.0201 1.1685a.0757.0757 0 0 1-.071 0l-4.8303-2.7865A4.504 4.504 0 0 1 2.3408 7.872zm16.5963 3.8558L13.1038 8.364 15.1192 7.2a.0757.0757 0 0 1 .071 0l4.8303 2.7913a4.4944 4.4944 0 0 1-.6765 8.1042v-5.6772a.79.79 0 0 0-.407-.667zm2.0107-3.0231l-.142-.0852-4.7735-2.7818a.7759.7759 0 0 0-.7854 0L9.409 9.2297V6.8974a.0662.0662 0 0 1 .0284-.0615l4.8303-2.7866a4.4992 4.4992 0 0 1 6.6802 4.66zM8.3065 12.863l-2.02-1.1638a.0804.0804 0 0 1-.038-.0567V6.0742a4.4992 4.4992 0 0 1 7.3757-3.4537l-.142.0805L8.704 5.459a.7948.7948 0 0 0-.3927.6813zm1.0976-2.3654l2.602-1.4998 2.6069 1.4998v2.9994l-2.5974 1.4997-2.6067-1.4997Z" />
                </svg>
            </div>

            <!-- 2. Claude AI (Official Anthropic Claude Starburst) -->
            <div
                class="flex size-9 sm:size-10 items-center justify-center rounded-full bg-white p-1.5 shadow-md border border-slate-100 hover:scale-125 transition-transform cursor-pointer"
                title="Claude Pro"
            >
                <svg class="size-5 sm:size-5.5 fill-[#D97757]" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="m4.7144 15.9555 4.7174-2.6471.079-.2307-.079-.1275h-.2307l-.7893-.0486-2.6956-.0729-2.3375-.0971-2.2646-.1214-.5707-.1215-.5343-.7042.0546-.3522.4797-.3218.686.0608 1.5179.1032 2.2767.1578 1.6514.0972 2.4468.255h.3886l.0546-.1579-.1336-.0971-.1032-.0972L6.973 9.8356l-2.55-1.6879-1.3356-.9714-.7225-.4918-.3643-.4614-.1578-1.0078.6557-.7225.8803.0607.2246.0607.8925.686 1.9064 1.4754 2.4893 1.8336.3643.3035.1457-.1032.0182-.0728-.164-.2733-1.3539-2.4467-1.445-2.4893-.6435-1.032-.17-.6194c-.0607-.255-.1032-.4674-.1032-.7285L6.287.1335 6.6997 0l.9957.1336.419.3642.6192 1.4147 1.0018 2.2282 1.5543 3.0296.4553.8985.2429.8318.091.255h.1579v-.1457l.1275-1.706.2368-2.0947.2307-2.6957.0789-.7589.3764-.9107.7468-.4918.5828.2793.4797.686-.0668.4433-.2853 1.8517-.5586 2.9021-.3643 1.9429h.2125l.2429-.2429.9835-1.3053 1.6514-2.0643.7286-.8196.85-.9046.5464-.4311h1.0321l.759 1.1293-.34 1.1657-1.0625 1.3478-.8804 1.1414-1.2628 1.7-.7893 1.36.0729.1093.1882-.0183 2.8535-.607 1.5421-.2794 1.8396-.3157.8318.3886.091.3946-.3278.8075-1.967.4857-2.3072.4614-3.4364.8136-.0425.0304.0486.0607 1.5482.1457.6618.0364h1.621l3.0175.2247.7892.522.4736.6376-.079.4857-1.2142.6193-1.6393-.3886-3.825-.9107-1.3113-.3279h-.1822v.1093l1.0929 1.0686 2.0035 1.8092 2.5075 2.3314.1275.5768-.3218.4554-.34-.0486-2.2039-1.6575-.85-.7468-1.9246-1.621h-.1275v.17l.4432.6496 2.3436 3.5214.1214 1.0807-.17.3521-.6071.2125-.6679-.1214-1.3721-1.9246L14.38 17.959l-1.1414-1.9428-.1397.079-.674 7.2552-.3156.3703-.7286.2793-.6071-.4614-.3218-.7468.3218-1.4753.3886-1.9246.3157-1.53.2853-1.9004.17-.6314-.0121-.0425-.1397.0182-1.4328 1.9672-2.1796 2.9446-1.7243 1.8456-.4128.164-.7164-.3704.0667-.6618.4008-.5889 2.386-3.0357 1.4389-1.882.929-1.0868-.0062-.1579h-.0546l-6.3385 4.1164-1.1293.1457-.4857-.4554.0608-.7467.2307-.2429 1.9064-1.3114Z"/>
                </svg>
            </div>

            <!-- 3. Google Gemini Advanced (Official 4-point Star with Google Gradient) -->
            <div
                class="flex size-9 sm:size-10 items-center justify-center rounded-full bg-white p-1.5 shadow-md border border-slate-100 hover:scale-125 transition-transform cursor-pointer"
                title="Google Gemini Advanced"
            >
                <svg class="size-5 sm:size-5.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="gemini-grad-hero" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#1BA1E3" />
                            <stop offset="50%" stop-color="#5468FF" />
                            <stop offset="100%" stop-color="#9C40FF" />
                        </linearGradient>
                    </defs>
                    <path fill="url(#gemini-grad-hero)" d="M12 24C12 17.3726 6.62742 12 0 12C6.62742 12 12 6.62742 12 0C12 6.62742 17.3726 12 24 12C17.3726 12 12 17.3726 12 24Z" />
                </svg>
            </div>

            <!-- 4. Canva Pro (Official Script Emblem) -->
            <div
                class="flex size-9 sm:size-10 items-center justify-center rounded-full bg-white p-1 shadow-md border border-slate-100 hover:scale-125 transition-transform cursor-pointer"
                title="Canva Pro"
            >
                <div class="size-7 sm:size-7.5 rounded-full bg-gradient-to-tr from-[#00C4CC] to-[#7D2AE8] flex items-center justify-center text-white shadow-xs">
                    <span class="font-serif italic font-extrabold text-sm sm:text-base tracking-tight -translate-y-0.5">C</span>
                </div>
            </div>
        </OrbitingCircles>

        <!-- ORBIT 2: MIDDLE ORBIT (Radius 118, Duration 24s, Reverse) - 4 Layanan Streaming & Editing Premium -->
        <OrbitingCircles
            :radius="118"
            :duration="24"
            :iconSize="40"
            :reverse="true"
        >
            <!-- 5. CapCut Pro (Official Logo) -->
            <div
                class="flex size-9 sm:size-10 items-center justify-center rounded-full bg-white p-1.5 shadow-md border border-slate-100 hover:scale-125 transition-transform cursor-pointer"
                title="CapCut Pro"
            >
                <svg class="size-5 sm:size-5.5 fill-black" viewBox="0 0 512 509.659" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="nonzero" d="M109.095 181.505c2.223-19.532 18.316-34.578 37.955-35.483l167.194-.001a40.612 40.612 0 0130.095 17.427 42.152 42.152 0 016.39 14.915l49.135-24.364a2.185 2.185 0 013.141 1.674v27.628l.001.096a4.571 4.571 0 01-2.837 4.229 177620.936 177620.936 0 00-135.63 67.336l135.324 66.948a4.695 4.695 0 013.142 4.08v27.685a2.266 2.266 0 01-3.613 1.821c-16.12-8.162-32.464-15.854-48.462-24.18a63.503 63.503 0 01-4.282 11.225 40.813 40.813 0 01-26.098 20.135 44.994 44.994 0 01-11.221.919l-155.833.003c-3.51 0-7.04 0-10.53-.266-18.089-2.705-32.049-17.363-33.869-35.565v-26.77a5.935 5.935 0 014.08-4.879c27.791-13.732 55.521-27.587 83.353-41.258a32412.61 32412.61 0 00-84.17-41.748 5.41 5.41 0 01-3.223-4.918c-.042-8.876-.185-17.792-.042-26.689zm30.975.184c-1.674 3.367-.898 7.263-1.041 10.896 30.608 15.12 60.99 30.321 91.536 45.339 30.185-14.963 60.384-29.927 90.596-44.89 0-2.714.123-5.428 0-8.162a10.203 10.203 0 00-10.096-8.734h-.106l-161.565.001a10.082 10.082 0 00-9.345 5.55h.021zm-1.041 135.406c.142 3.673-.654 7.631 1.122 11.039a10.204 10.204 0 009.284 5.405l161.667.002.081-.001c3.618 0 6.961-1.94 8.754-5.081 2.04-3.57 1.102-7.855 1.305-11.773-30.26-14.936-60.48-30.118-90.801-44.89a43915.126 43915.126 0 00-91.432 45.299h.02z"/>
                </svg>
            </div>

            <!-- 6. Netflix Premium (Official Bold Red Ribbon) -->
            <div
                class="flex size-9 sm:size-10 items-center justify-center rounded-full bg-white p-2 shadow-md border border-slate-100 hover:scale-125 transition-transform cursor-pointer"
                title="Netflix Premium"
            >
                <svg class="h-5 sm:h-5.5 w-auto" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5.5 0V24L9.5 22.8V0H5.5Z" fill="#B81D24" />
                    <path d="M14.5 0V22.8L18.5 24V0H14.5Z" fill="#B81D24" />
                    <path d="M5.5 0H9.5L18.5 24H14.5L5.5 0Z" fill="#E50914" />
                </svg>
            </div>

            <!-- 7. Spotify Premium (Official Soundwave Emblem) -->
            <div
                class="flex size-9 sm:size-10 items-center justify-center rounded-full bg-white p-1.5 shadow-md border border-slate-100 hover:scale-125 transition-transform cursor-pointer"
                title="Spotify Premium"
            >
                <svg class="size-6 sm:size-6.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="11" fill="#1DB954" />
                    <path d="M16.8 16.2c-.2.3-.6.4-.9.2-2.5-1.5-5.7-1.9-9.4-1-.3.1-.7-.1-.8-.4-.1-.3.1-.7.4-.8 4.1-1 7.6-.5 10.5 1.2.3.1.4.5.2.8zm1.3-2.9c-.2.4-.7.5-1.1.3-2.9-1.8-7.3-2.3-10.7-1.3-.4.1-.9-.1-1-.5s.1-.9.5-1c3.9-1.2 8.7-.6 12 1.4.4.2.5.7.3 1.1zm.1-3.1c-3.5-2.1-9.2-2.3-12.6-1.2-.5.2-1.1-.1-1.2-.6-.2-.5.1-1.1.6-1.2 3.8-1.2 10.1-.9 14.1 1.5.5.3.6.9.3 1.4-.3.4-.9.5-1.2.1z" fill="#ffffff" />
                </svg>
            </div>

            <!-- 8. YouTube Premium (Official Red Play Icon) -->
            <div
                class="flex size-9 sm:size-10 items-center justify-center rounded-full bg-white p-1.5 shadow-md border border-slate-100 hover:scale-125 transition-transform cursor-pointer"
                title="YouTube Premium"
            >
                <svg class="size-5.5 sm:size-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814z" fill="#FF0000" />
                    <path d="M9.545 15.568V8.432L15.818 12l-6.273 3.568z" fill="#ffffff" />
                </svg>
            </div>
        </OrbitingCircles>

        <!-- ORBIT 3: OUTER ORBIT (Radius 165, Duration 32s) - 5 Layanan Premium Populer Lainnya -->
        <OrbitingCircles
            :radius="165"
            :duration="32"
            :iconSize="42"
        >
            <!-- 9. Alight Motion Pro (Official Rings & Gradient) -->
            <div
                class="flex size-9 sm:size-10 items-center justify-center rounded-full bg-[#1D2238] p-1.5 shadow-lg border border-slate-700/50 hover:scale-125 transition-transform cursor-pointer"
                title="Alight Motion Pro"
            >
                <svg class="size-5 sm:size-5.5" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="alight-grad-hero" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#05FAA8" />
                            <stop offset="100%" stop-color="#168D72" />
                        </linearGradient>
                    </defs>
                    <path d="M256 110C175.31 110 110 175.31 110 256s65.31 146 146 146 146-65.31 146-146-65.31-146-146-146zm0 240c-51.85 0-94-42.15-94-94s42.15-94 94-94 94 42.15 94 94-42.15 94-94 94z" fill="url(#alight-grad-hero)" />
                    <path d="M256 160c-53.02 0-96 42.98-96 96s42.98 96 96 96 96-42.98 96-96-42.98-96-96-96zm0 144c-26.51 0-48-21.49-48-48s21.49-48 48-48 48 21.49 48 48-21.49 48-48 48z" fill="#05FAA8" opacity="0.85" />
                </svg>
            </div>

            <!-- 10. Apple Music (Official Note Emblem) -->
            <div
                class="flex size-9 sm:size-10 items-center justify-center rounded-full bg-white p-1.5 shadow-lg border border-slate-100 hover:scale-125 transition-transform cursor-pointer"
                title="Apple Music"
            >
                <svg class="size-5 sm:size-5.5 fill-[#FA2D48]" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M23.994 6.124a9.23 9.23 0 00-.24-2.19c-.317-1.31-1.062-2.31-2.18-3.043a5.022 5.022 0 00-1.877-.726 10.496 10.496 0 00-1.564-.15c-.04-.003-.083-.01-.124-.013H5.986c-.152.01-.303.017-.455.026-.747.043-1.49.123-2.193.4-1.336.53-2.3 1.452-2.865 2.78-.192.448-.292.925-.363 1.408-.056.392-.088.785-.1 1.18 0 .032-.007.062-.01.093v12.223c.01.14.017.283.027.424.05.815.154 1.624.497 2.373.65 1.42 1.738 2.353 3.234 2.801.42.127.856.187 1.293.228.555.053 1.11.06 1.667.06h11.03a12.5 12.5 0 001.57-.1c.822-.106 1.596-.35 2.295-.81a5.046 5.046 0 001.88-2.207c.186-.42.293-.87.37-1.324.113-.675.138-1.358.137-2.04-.002-3.8 0-7.595-.003-11.393zm-6.423 3.99v5.712c0 .417-.058.827-.244 1.206-.29.59-.76.962-1.388 1.14-.35.1-.706.157-1.07.173-.95.045-1.773-.6-1.943-1.536a1.88 1.88 0 011.038-2.022c.323-.16.67-.25 1.018-.324.378-.082.758-.153 1.134-.24.274-.063.457-.23.51-.516a.904.904 0 00.02-.193c0-1.815 0-3.63-.002-5.443a.725.725 0 00-.026-.185c-.04-.15-.15-.243-.304-.234-.16.01-.318.035-.475.066-.76.15-1.52.303-2.28.456l-2.325.47-1.374.278c-.016.003-.032.01-.048.013-.277.077-.377.203-.39.49-.002.042 0 .086 0 .13-.002 2.602 0 5.204-.003 7.805 0 .42-.047.836-.215 1.227-.278.64-.77 1.04-1.434 1.233-.35.1-.71.16-1.075.172-.96.036-1.755-.6-1.92-1.544-.14-.812.23-1.685 1.154-2.075.357-.15.73-.232 1.108-.31.287-.06.575-.116.86-.177.383-.083.583-.323.6-.714v-.15c0-2.96 0-5.922.002-8.882 0-.123.013-.25.042-.37.07-.285.273-.448.546-.518.255-.066.515-.112.774-.165.733-.15 1.466-.296 2.2-.444l2.27-.46c.67-.134 1.34-.27 2.01-.403.22-.043.442-.088.663-.106.31-.025.523.17.554.482.008.073.012.148.012.223.002 1.91.002 3.822 0 5.732z"/>
                </svg>
            </div>

            <!-- 11. HBO Max (Official Wordmark Icon) -->
            <div
                class="flex size-9 sm:size-10 items-center justify-center rounded-full bg-white p-1.5 shadow-lg border border-slate-100 hover:scale-125 transition-transform cursor-pointer"
                title="HBO Max"
            >
                <svg class="size-5 sm:size-5.5 fill-[#742FF6]" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3.784 8.716c-.655 0-1.32.29-2.173.946v-.78H0v6.236h1.715V11.24c.749-.592 1.091-.78 1.372-.78.333 0 .551.209.551.729v3.928h1.715V11.23c.748-.582 1.081-.769 1.372-.769.333 0 .55.208.55.728v3.928H8.99v-4.53c0-1.403-.8-1.871-1.57-1.871-.654 0-1.32.27-2.192.936-.28-.697-.894-.936-1.444-.936zm8.689 0c-1.705 0-3.118 1.466-3.118 3.284 0 1.82 1.413 3.285 3.118 3.285.842 0 1.57-.312 2.131-.988v.82h1.632V8.883h-1.632v.822c-.561-.676-1.29-.988-2.131-.988zm4.064.166c.707 1.102 1.507 2.09 2.443 3.077a26.593 26.593 0 0 0-2.443 3.16h2.069a13.603 13.603 0 0 1 1.673-2.183 14.067 14.067 0 0 1 1.632 2.182H24a25.142 25.142 0 0 0-2.432-3.16A23.918 23.918 0 0 0 24 8.883h-2.047a14.65 14.65 0 0 1-1.674 2.11 13.357 13.357 0 0 1-1.674-2.11zm-3.804 1.279c1.018 0 1.84.82 1.84 1.84a1.837 1.837 0 0 1-1.84 1.839c-1.019 0-1.84-.82-1.84-1.84 0-1.018.821-1.84 1.84-1.84zm0 .415c-.78 0-1.414.633-1.414 1.423s.634 1.424 1.413 1.424c.78 0 1.414-.634 1.414-1.424s-.634-1.424-1.414-1.424z"/>
                </svg>
            </div>

            <!-- 12. Zoom Pro (Official Video Camera Icon) -->
            <div
                class="flex size-9 sm:size-10 items-center justify-center rounded-full bg-[#2D8CFF] p-2 shadow-lg border border-blue-400 hover:scale-125 transition-transform cursor-pointer"
                title="Zoom Pro"
            >
                <svg class="size-4.5 sm:size-5 fill-white" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5.033 14.649H.743a.74.74 0 0 1-.686-.458.74.74 0 0 1 .16-.808L3.19 10.41H1.06A1.06 1.06 0 0 1 0 9.35h3.957c.301 0 .57.18.686.458a.74.74 0 0 1-.161.808L1.51 13.59h2.464c.585 0 1.06.475 1.06 1.06zM24 11.338c0-1.14-.927-2.066-2.066-2.066-.61 0-1.158.265-1.537.686a2.061 2.061 0 0 0-1.536-.686c-1.14 0-2.066.926-2.066 2.066v3.311a1.06 1.06 0 0 0 1.06-1.06v-2.251a1.004 1.004 0 0 1 2.013 0v2.251c0 .586.474 1.06 1.06 1.06v-3.311a1.004 1.004 0 0 1 2.012 0v2.251c0 .586.475 1.06 1.06 1.06zM16.265 12a2.728 2.728 0 1 1-5.457 0 2.728 2.728 0 0 1 5.457 0zm-1.06 0a1.669 1.669 0 1 0-3.338 0 1.669 1.669 0 0 0 3.338 0zm-4.82 0a2.728 2.728 0 1 1-5.458 0 2.728 2.728 0 0 1 5.457 0zm-1.06 0a1.669 1.669 0 1 0-3.338 0 1.669 1.669 0 0 0 3.338 0z"/>
                </svg>
            </div>

            <!-- 13. Surfshark VPN (Official Emblem) -->
            <div
                class="flex size-9 sm:size-10 items-center justify-center rounded-full bg-white p-1.5 shadow-lg border border-slate-100 hover:scale-125 transition-transform cursor-pointer"
                title="Surfshark VPN"
            >
                <svg class="size-5 sm:size-5.5 fill-[#1DBAB4]" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11.47 0C7.815.2 6.3 2.293 5.872 3.43c-1.615 4.866-3.127 14.325-3.33 15.662-.201 1.31-.228 2.119-.228 2.119 0 .328.026.705.102 1.059.454 1.286 1.792 2.37 4.768 1.287a192.353 192.353 0 0 0 9.533-4.44c1.387-.807 3.227-2.32 4.236-4.312.404-.807.682-1.716.733-2.65v-.452c-.026-2.295-.052-4.692-.204-7.013 0 0-.125-1.488-.2-2.017-.076-.53-.177-.733-.177-.733C20.626.906 19.693.38 18.71.126 18.23.026 17.7.024 17.095 0Zm4.692 4.44h.252c.277 0 .48.2.48.452V6.53c0 .252-.203.455-.48.455h-.252c-1.589 0-2.875 1.26-2.875 2.8v2.498c0 2.976-2.472 5.37-5.498 5.37h-.254c-.277 0-.478-.2-.478-.452v-1.64c0-.253.226-.454.478-.454h.254c1.589 0 2.875-1.262 2.875-2.8V9.81c0-2.977 2.472-5.373 5.498-5.373z"/>
                </svg>
            </div>
        </OrbitingCircles>
    </div>
</template>
