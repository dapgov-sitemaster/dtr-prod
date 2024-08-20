<div class="flex justify-between w-full h-full px-2 sm:pr-2 md:pr-3 lg:pr-5 z-0">
    {{-- <x-filament.notifications.database-notifications-trigger /> --}}
    <div class="my-auto">
        <a href="{{ route('home') }}"><img class=" w-56" src="{{ asset('image/edtr-banner.png') }}" draggable="false" /></a>
    </div>
    <div class="flex justify-between">
        {{-- <div class="my-auto">@livewire('database-notifications')</div> --}}
        <x-dropdown align="right" width="48" class="my-auto">

            <x-slot name="trigger">
                <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                    <img class="h-8 w-8 rounded-full object-cover" src="https://ui-avatars.com/api/?name={{ auth()->user()->employee->first_name.' '.auth()->user()->employee->last_name }}&background=random" alt="">
                </button>
            </x-slot>

            <x-slot name="content">
                <!-- Account Management -->
                <div class="block px-4 py-2 text-xs text-gray-400">
                    {{ __('User Menu') }}
                </div>

                <div class="border-t border-gray-200 dark:border-gray-600"></div>

                <!-- Authentication -->
                <x-dropdown-link href="{{ route('employee.qr-code') }}">
                    {{ __('Generate QR Code') }}
                </x-dropdown-link>
                <x-dropdown-link href="{{ route('employee.identity-photo') }}">
                    {{ __('Identity Photo') }}
                </x-dropdown-link>
                <x-dropdown-link href="{{ route('employee.electronic-signature') }}">
                    {{ __('E-Signature') }}
                </x-dropdown-link>
                <div class="border-t border-gray-200 dark:border-gray-600"></div>
                <x-dropdown-link href="{{ route('logout') }}">
                    {{ __('Log Out') }}
                </x-dropdown-link>
            </x-slot>
        </x-dropdown>
    </div>
</div>
