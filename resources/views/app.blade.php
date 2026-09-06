<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'light') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Kiln defaults to warm paper. Dark is only applied when the user chose it. --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "light" }}';

                if (appearance === 'dark') {
                    document.documentElement.classList.add('dark');
                } else if (appearance === 'system') {
                    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                        document.documentElement.classList.add('dark');
                    }
                } else {
                    document.documentElement.classList.remove('dark');
                }
            })();
        </script>

        <style>
            html {
                background-color: #F3EFE8;
                color: #1C1916;
                color-scheme: light;
            }

            html.dark {
                background-color: #161412;
                color: #EDE6DB;
                color-scheme: dark;
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link
            rel="stylesheet"
            href="https://fonts.bunny.net/css?family=ibm-plex-sans:400,500,600|noto-sans-bengali:400,500"
        />

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="bg-[#F3EFE8] font-sans text-[14px] text-[#1C1916] antialiased">
        <x-inertia::app />
    </body>
</html>
