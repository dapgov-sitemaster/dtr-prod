@props([
    'url' => '#',
    'tooltip' => null,
    'active' => false
])

<li>
    <a href="{{ $url }}"
        x-data="{ tooltip: {} }"
        x-init="
            Alpine.effect(() => {
                if (Alpine.store('sidebar').isOpen) {
                    tooltip = false
                } else {
                    tooltip = {
                        content: '{{$tooltip}}',
                        placement: 'right'
                    }
                }
            })
        "
        x-tooltip.html="tooltip"
        @class([
            'flex items-center justify-center gap-3 px-3 py-2 rounded-lg font-medium transition ',
            'hover:bg-gray-500/5 focus:bg-gray-500/5 text-gray-600' => ! $active,
            'bg-dap-primary text-white hover:bg-dap-primary/90 text-white' => $active
        ])
    >
        {{ $slot }}
    </a>
</li>
