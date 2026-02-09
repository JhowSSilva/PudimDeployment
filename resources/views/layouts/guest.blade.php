<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('paw.svg') }}">
        <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-neutral-900">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-neutral-900 via-neutral-800 to-neutral-900">
            <div class=\"flex flex-col items-center gap-3 mb-2\">
                <div class="w-16 h-16 bg-gradient-to-br from-primary-600 to-primary-700 rounded-2xl flex items-center justify-center shadow-2xl shadow-primary-600/30">
                    <svg class="w-9 h-9 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C10.9 2 10 2.9 10 4C10 4.3 10.1 4.6 10.2 4.9C8.9 5.5 8 6.7 8 8.1C8 9 8.4 9.8 9 10.3C8.4 10.8 8 11.5 8 12.4C8 13.1 8.3 13.7 8.7 14.2C7.7 14.9 7 16 7 17.3C7 19.3 8.7 21 10.7 21C11.1 21 11.5 20.9 11.9 20.8C12.2 20.9 12.6 21 13 21C15.2 21 17 19.2 17 17C17 15.9 16.5 14.9 15.7 14.2C16.1 13.7 16.4 13.1 16.4 12.4C16.4 11.5 16 10.8 15.4 10.3C16 9.8 16.4 9 16.4 8.1C16.4 6.7 15.5 5.5 14.2 4.9C14.3 4.6 14.4 4.3 14.4 4C14.4 2.9 13.5 2 12.4 2H12Z"/>
                        <ellipse cx="10" cy="8" rx="1.2" ry="1.5" fill="white" opacity="0.3"/>
                        <ellipse cx="14" cy="8" rx="1.2" ry="1.5" fill="white" opacity="0.3"/>
                        <ellipse cx="9" cy="13" rx="1.2" ry="1.5" fill="white" opacity="0.3"/>
                        <ellipse cx="15" cy="13" rx="1.2" ry="1.5" fill="white" opacity="0.3"/>
                    </svg>
                </div>
                <a href="/">
                    <h1 class="text-3xl font-bold text-primary-700 dark:text-primary-400">Pudim Deployment</h1>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-8 py-8 bg-neutral-800/90 backdrop-blur-sm shadow-2xl overflow-hidden sm:rounded-2xl border border-neutral-700/50">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
