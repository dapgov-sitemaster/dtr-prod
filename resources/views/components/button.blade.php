{{-- <button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button> --}}
@props([
    'disabled' => 'false',
    'color' => '',
    'coloring' => [
        'bg' => [
            'primary' => 'bg-dap-primary',
            'secondary' => 'bg-dap-secondary',
            'default' => 'bg-gray-800',
        ],
        'hover:text' => [
            'primary' => 'hover:text-dap-primary',
            'secondary' => 'hover:text-dap-secondary',
            'default' => 'hover:text-gray-700',
        ],
        'hover:border' => [
            'primary' => 'hover:border-dap-primary',
            'secondary' => 'hover:border-dap-secondary',
            'default' => 'hover:border-gray-500',
        ],
        'focus:bg' => [
            'primary' => 'focus:bg-dap-primary focus:ring-dap-primary',
            'secondary' => 'focus:bg-dap-secondary focus:ring-dap-secondary',
            'default' => 'focus:bg-gray-700 focus:ring-gray-500',
        ],
        'active:bg' => [
            'primary' => 'active:bg-dap-primary',
            'secondary' => 'active:bg-dap-secondary',
            'default' => 'active:bg-gray-900',
        ],
    ],
])

@php
    $is_disabled = $disabled == 'true' ? 'disabled:opacity-50 disabled:cursor-not-allowed' : '';
    $colors = $color ? $coloring['bg'][$color] . ' ' . $coloring['hover:text'][$color] . ' ' . $coloring['hover:border'][$color] : '';
@endphp

<button
    {{ $attributes->merge(['class' => $colors . ' ' . $is_disabled . ' hover:bg-transparent text-white border-transparent border-2 inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2  focus:ring-offset-2 transition ease-in-out duration-150 my-auto']) }}>
    {{ $slot }}
</button>
