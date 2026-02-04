<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0f1729">

        <title>{{ config('app.name', 'Curso') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-alura-text antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-alura-dark">
            <div class="mb-8">
                <a href="/" class="text-alura-accent font-bold text-3xl">
                    {{ config('app.name', 'Curso') }}
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-8 alura-card shadow-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
