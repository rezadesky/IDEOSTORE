<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Ideostore - Pusat langganan aplikasi premium terpercaya, akun AI, produktivitas, dan hiburan dengan sistem otomatis QRIS.">
        <link rel="canonical" href="{{ url()->current() }}">
        <meta name="robots" content="index, follow">

        <!-- Open Graph / WhatsApp / Facebook -->
        <meta property="og:site_name" content="Ideostore">
        <meta property="og:title" content="Ideostore - Pusat Layanan Aplikasi Premium Terpercaya">
        <meta property="og:description" content="Pusat langganan aplikasi premium terpercaya, akun AI, produktivitas, dan hiburan dengan sistem otomatis QRIS instan.">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:type" content="website">
        <meta property="og:image" content="{{ asset('logo.png') }}">
        <meta property="og:image:alt" content="Ideostore Logo">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Ideostore - Pusat Layanan Aplikasi Premium">
        <meta name="twitter:description" content="Langganan aplikasi premium terpercaya, akun AI, produktivitas, dan hiburan otomatis QRIS.">
        <meta name="twitter:image" content="{{ asset('logo.png') }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/png" href="/favicon.png">

        <script>
            (function() {
                try {
                    var theme = localStorage.getItem('theme');
                    if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                } catch(e) {}
            })();
        </script>

        <!-- Google Fonts: Plus Jakarta Sans -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-[#f8fafc] text-slate-800 min-h-screen w-full overflow-x-hidden selection:bg-royal-600 selection:text-white">
        @inertia
    </body>
</html>
