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
                    <img class="h-8 w-8 rounded-full object-cover" src="https://source.boringavatars.com/beam/120/{{ Auth::user()->email }}" alt="">
                </button>
            </x-slot>

            <x-slot name="content">
                <!-- Account Management -->
                <div class="block px-4 py-2 text-xs text-gray-400">
                    {{ __('Manage Account') }}
                </div>

                <div class="border-t border-gray-200 dark:border-gray-600"></div>

                <!-- Authentication -->

                {{-- <x-dropdown-link href="{{ route('user.profile') }}">
                    {{ __('Profile') }}
                </x-dropdown-link> --}}
                <button @click="$dispatch('open-modal', { id: 'generate-qr' })" class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out">
                    {{ __('Generate QR Code') }}
                </button>
                <button @click="$dispatch('open-modal', { id: 'identity-photo' })" class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out">
                    {{ __('Identity Photo') }}
                </button>
                {{-- <x-dropdown-link href="{{ route('user.photo') }}">
                    {{ __('Identity Photo') }}
                </x-dropdown-link> --}}
                <x-dropdown-link href="{{ route('logout') }}">
                    {{ __('Log Out') }}
                </x-dropdown-link>
            </x-slot>
        </x-dropdown>
    </div>
</div>
