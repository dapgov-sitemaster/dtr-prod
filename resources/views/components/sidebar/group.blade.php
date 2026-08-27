@props([
    'label' => null,
])

<li class="">
    @if($label)
        <button
            x-on:click.prevent="$store.sidebar.toggleCollapsedGroup('{{ $label }}')"
            x-show="$store.sidebar.isOpen"
            class="flex items-center justify-between w-full">
            <div class="flex items-center gap-4 text-gray-600">
                <p class="flex-1 font-bold uppercase text-xs tracking-wider">
                    {{ $label }}
                </p>
            </div>
            <x-filament::icon
                x-bind:class="$store.sidebar.groupIsCollapsed('{{ $label }}') || '-rotate-180'" x-cloak
                class="w-7 h-7 text-gray-600 transition"
                icon="heroicon-m-chevron-up"
            />
        </button>
    @endif


    <ul
        x-show="! ($store.sidebar.groupIsCollapsed('{{ $label }}') && $store.sidebar.isOpen)"
        x-collapse.duration.200ms
        @class([
            'text-sm space-y-1 -mx-3',
        ])
    >
        {{ $slot }}
    </ul>
</li>
