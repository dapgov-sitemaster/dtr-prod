<div wire:loading {{ $attributes->merge() }}
    x-cloak
    x-transition:enter="transition duration-150"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    <div class="w-full h-full flex m-auto z-50 fixed top-0 left-0 bg-gray-600 opacity-75">
        <img src="{{ asset('image/applogo.png') }}" class="w-32 h-32 spin-dap-logo m-auto">
    </div>
</div>
