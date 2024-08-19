<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>eDTR - Log in</title>
        @livewireStyles
        @filamentStyles
        @vite('resources/js/app.js')
    </head>
    <body>
        {{ $slot }}
    </body>
    @livewireScripts
    @filamentScripts
</html>
