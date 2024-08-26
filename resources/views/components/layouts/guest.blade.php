<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>eDTR {{ $title ?? 'Page Title' }}</title>
        @livewireStyles
        @filamentStyles
        @vite('resources/js/app.js')
        <style>
            [x-cloak] {
                display: none !important
            }

            .spin-dap-logo {
                -moz-animation: spindap 1s ease infinite;
                animation: spindap 1s ease infinite;
            }

            @keyframes spindap {
                0% {
                    transform: rotateY(180deg);
                }

                40% {
                    transform: translateY(-30px);
                }

                60% {
                    transform: translateY(-15px);
                }

                100% {
                    transform: rotateY(0deg);
                }
            }

            .spin-clock-logo {
                -moz-animation: spinclock 1s ease infinite;
                animation: spinclock 1s ease infinite;
            }

            @keyframes spinclock {
                0% {
                    transform: rotateY(180deg);
                }

                40% {
                    transform: translateY(-30px);
                }

                60% {
                    transform: translateY(-15px);
                }

                100% {
                    transform: rotateY(0deg);
                }
            }
            .dot {
                height: 18px;
                width: 18px;
                background-color: #fff;
                border: 1px solid;
                border-radius: 50%;
                display: inline-block;
            }
            .dot-shaded {
                height: 18px;
                width: 18px;
                background-color: #000;
                border-radius: 50%;
                display: inline-block;
            }
        </style>
    </head>
    <body>
        {{ $slot }}
    </body>
    @livewireScripts
    @filamentScripts
</html>
