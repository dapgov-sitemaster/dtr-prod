<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>eDTR {{ $title ?? 'Page Title' }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js" integrity="sha512-+NqPlbbtM1QqiK8ZAo4Yrj2c4lNQoGv8P79DPtKzj++l5jnN39rHA/xsqn8zE9l0uSoxaCdrOgFs6yjyfbBxSg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.css"/>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.js"></script>
        <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css" />
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
        @stack('scripts')
    </head>
    <body>
        @livewire('notifications')
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <main>
                <div class="flex w-full min-h-screen overflow-x-clip">
                    <div x-data="{}" x-cloak x-show="$store.sidebar.isOpen" x-transition.opacity.500ms
                        x-on:click="$store.sidebar.close()"
                        class="filament-sidebar-close-overlay fixed inset-0 z-20 w-full h-full bg-gray-900/50 lg:hidden">
                    </div>

                    <x-sidebar />

                    <div x-data="{}"
                        x-bind:class="{
                            'lg:pl-[var(--collapsed-sidebar-width)] rtl:lg:pr-[var(--collapsed-sidebar-width)]': !$store
                                .sidebar.isOpen,
                            'filament-main-sidebar-open lg:pl-[var(--sidebar-width)] rtl:lg:pr-[var(--sidebar-width)]': $store
                                .sidebar.isOpen,
                        }"
                        x-bind:style="'display: flex'" class="flex-col gap-y-6 w-screen flex-1 rtl:lg:pl-0">
                        <header class="sticky top-0 z-10 flex h-[5rem] w-full shrink-0 items-center border-b bg-gray-100">
                            <div class="flex items-center w-full px-2 sm:px-4 md:px-6 lg:px-8">
                                <button x-cloak x-data="{}"
                                    x-on:click="$store.sidebar.isOpen ? $store.sidebar.close() : $store.sidebar.open()"
                                    class="shrink-0 flex items-center justify-center w-10 h-10 text-primary-500 rounded-full hover:bg-gray-500/5 focus:bg-primary-500/10 focus:outline-none lg:hidden">

                                    <x-filament::icon
                                        class="text-dap-primary"
                                        icon="heroicon-m-bars-3"
                                    />
                                </button>

                                <div class="flex items-center justify-end  flex-1">
                                    {{-- <x-layouts.topbar.breadcrumbs /> --}}
                                    <x-topbar.app-user />
                                </div>
                            </div>
                        </header>
                        <div class="flex-1 w-full px-4 mx-auto md:px-14 lg:px-24">

                            <x-filament::modal slide-over id="generate-qr" icon-color="info" width="xl">
                                <x-slot name="heading">
                                    <span class="text-xl text-dap-primary font-semibold underline decoration-dap-secondary">QR Code</span>
                                </x-slot>
                                <div>
                                    <div class="text-center">
                                        <img src="data:image/jpg;base64, {!! base64_encode(QrCode::errorCorrection('H')->format('png')->merge(public_path('image/applogo.jpg'), .1, true)->size(300)->generate(Crypt::encryptString(auth()->user()->hris_number))) !!}" width="300" class="mx-auto border-2" draggable="false" />
                                        <div class="mx-auto text-base md:text-lg font-semibold">{{ auth()->user()->hris_number. ' - ' .auth()->user()->employee->full_name }}</div>
                                        <a href="{{ route('pdf.empqrcode') }}" class="text-center" target="_blank">
                                            <x-button color="primary" class="py-1 my-10">
                                                Download ID Card
                                            </x-button>
                                        </a>
                                    </div>
                                </div>
                            </x-filament::modal>

                            <x-filament::modal slide-over id="identity-photo" icon="heroicon-o-photo" icon-color="info" width="3xl">
                                <x-slot name="heading">
                                    <span class="text-xl text-dap-primary font-semibold underline decoration-dap-secondary">Identity Photo</span>
                                </x-slot>
                                @livewire('profile.identity-photo')
                            </x-filament::modal>

                            <x-filament::modal id="e-signature" icon-color="info" width="3xl">
                                <x-slot name="heading">
                                    <span class="text-xl text-dap-primary font-semibold underline decoration-dap-secondary">E-Signature</span>
                                </x-slot>
                                @livewire('profile.electronic-signature')
                            </x-filament::modal>
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </body>
    @stack('scripts')
    @livewireScriptConfig
    @filamentScripts
</html>
