<aside x-data="{}" x-cloak
    x-bind:class="$store.sidebar.isOpen ? 'translate-x-0 max-w-[20em] lg:max-w-[var(--sidebar-width)]' :
        '-translate-x-full lg:translate-x-0 lg:max-w-[var(--collapsed-sidebar-width)] rtl:lg:-translate-x-0 rtl:translate-x-full'"
    class="fixed inset-y-0 left-0 rtl:left-auto rtl:right-0 z-20 flex flex-col h-screen overflow-hidden shadow-2xl transition-all bg-gray-100  rtl:lg:border-r-0 rtl:lg:border-l w-[var(--sidebar-width)] lg:z-0 lg:translate-x-0 rtl:lg:-translate-x-0 rtl:translate-x-full">
    <header class="h-[5rem] shrink-0 flex items-center justify-center">
        <div x-show="$store.sidebar.isOpen ? 'hidden' : ''" class="flex items-center jusify-center px-8 w-full">
            <button x-data="{}"
                x-on:click.stop="$store.sidebar.isOpen ? $store.sidebar.close() : $store.sidebar.open()"
                class=" flex items-center">
                <img class="w-11 h-11 rotate-back" src="{{ asset('image/applogo.png') }}" />
                <img class="h-8 ml-2" src="{{ asset('image/dap-label.png') }}" />
            </button>
        </div>
        <button x-data="{}" x-show="$store.sidebar.isOpen ? '' : 'hidden'"
            x-on:click.stop="$store.sidebar.isOpen ? $store.sidebar.close() : $store.sidebar.open()"
            class="rounded-full">
            <img class="w-11 h-11 rotate" src="{{ asset('image/applogo.png') }}" />
        </button>

    </header>
    <nav class="flex-1 py-6 overflow-y-auto">
        <ul class="px-6 space-y-6">
            <x-sidebar.group>
                <x-sidebar.item tooltip="Home" :url="route('home')" :active="request()->routeIs('home')">
                    <x-filament::icon
                        icon="heroicon-m-home"
                        class="w-6 shrink-0"
                    />
                    <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                        Home
                    </div>
                </x-sidebar.item>
            </x-sidebar.group>
            <li>
                <div @class(['border-t -mr-6 rtl:-mr-auto rtl:-ml-6'])></div>
            </li>
            <x-sidebar.group>
                <x-sidebar.item tooltip="ICT Equipment" :url="route('equipment.index')" :active="request()->routeIs('equipment.*')">
                    <x-filament::icon
                        icon="heroicon-m-cpu-chip"
                        class="w-6 shrink-0"
                    />
                    <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                        ICT Equipment
                    </div>
                </x-sidebar.item>
            </x-sidebar.group>
            <li>
                <div @class(['border-t -mr-6 rtl:-mr-auto rtl:-ml-6'])></div>
            </li>
            <x-sidebar.group>
                <x-sidebar.item tooltip="Diagnosis" :url="route('diagnosis.index')" :active="request()->routeIs('diagnosis.*')">
                    <x-filament::icon
                        icon="heroicon-m-clipboard-document-list"
                        class="w-6 shrink-0"
                    />
                    <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                        Diagnosis
                    </div>
                </x-sidebar.item>
            </x-sidebar.group>
            <li>
                <div @class(['border-t -mr-6 rtl:-mr-auto rtl:-ml-6'])></div>
            </li>
            <x-sidebar.group>
                <x-sidebar.item tooltip="Clearance" :url="route('clearance.index')" :active="request()->routeIs('clearance.*')">
                    <x-filament::icon
                        icon="heroicon-m-clipboard-document-check"
                        class="w-6 shrink-0"
                    />
                    <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                        Clearance
                    </div>
                </x-sidebar.item>
            </x-sidebar.group>
        </ul>
    </nav>
</aside>
