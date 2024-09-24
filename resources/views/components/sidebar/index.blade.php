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
            <x-sidebar.group>
                <x-sidebar.item tooltip="My Daily Time Entries" :url="route('employee.time-entries')" :active="request()->routeIs('employee.time-entries')">
                    <x-filament::icon
                        icon="heroicon-m-clock"
                        class="w-6 shrink-0"
                    />
                    <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                        My Daily Time Entries
                    </div>
                </x-sidebar.item>
            </x-sidebar.group>
            <x-sidebar.group>
                <x-sidebar.item tooltip="My DTR Report" :url="route('employee.dtr-report')" :active="request()->routeIs('employee.dtr-report')">
                    <x-filament::icon
                        icon="heroicon-m-document-text"
                        class="w-6 shrink-0"
                    />
                    <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                        My DTR Report
                    </div>
                </x-sidebar.item>
            </x-sidebar.group>
            <x-sidebar.group>
                <x-sidebar.item tooltip="Leave & Flexible Schedule Application" :url="route('employee.leave-flexible-schedule-application.index')" :active="request()->routeIs('employee.leave-flexible-schedule-application.*')">
                    <x-filament::icon
                        icon="heroicon-m-document-plus"
                        class="w-6 shrink-0"
                    />
                    <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                        Leave & Flexi Schedule Application
                    </div>
                </x-sidebar.item>
            </x-sidebar.group>
                @can('has-wfh-schedule')
                <li>
                    <div @class(['border-t -mr-6 rtl:-mr-auto rtl:-ml-6'])></div>
                </li>
                <x-sidebar.group>
                    <x-sidebar.item tooltip="Work from Home" :url="route('employee.work-from-home')" :active="request()->routeIs('employee.work-from-home')">
                        <x-filament::icon
                            icon="heroicon-m-computer-desktop"
                            class="w-6 shrink-0"
                        />
                        <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                            Work from Home
                        </div>
                    </x-sidebar.item>
                </x-sidebar.group>
                @endcan
                @if(Auth::user()->can('view-dapcc'))
                    @can('isAdminCoordinator')
                    <li>
                        <div @class(['border-t -mr-6 rtl:-mr-auto rtl:-ml-6'])></div>
                    </li>
                    <x-sidebar.group label="DAPCC Admin Coordinator Panel">
                        {{-- <x-sidebar.item tooltip="Official Time" :url="route('admin.official-time')" :active="request()->routeIs('admin.official-time')">
                            <x-filament::icon
                                icon="heroicon-m-cog"
                                class="w-6 shrink-0"
                            />
                            <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                                Official Time
                            </div>
                        </x-sidebar.item> --}}
                        <x-sidebar.item tooltip="Event Calendar" :url="route('dapcc.admin.event-calendar')" :active="request()->routeIs('dapcc.admin.event-calendar')">
                            <x-filament::icon
                                icon="heroicon-m-calendar-days"
                                class="w-6 shrink-0"
                            />
                            <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                                Event Calendar
                            </div>
                        </x-sidebar.item>
                        <x-sidebar.item tooltip="Daily Time Records" :url="route('dapcc.admin.dtr.index')" :active="request()->routeIs('dapcc.admin.dtr.*')">
                            <x-filament::icon
                                icon="heroicon-m-document-text"
                                class="w-6 shrink-0"
                            />
                            <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                                Daily Time Records
                            </div>
                        </x-sidebar.item>
                    </x-sidebar.group>
                    @endcan
                    @can('isHrAdmin')
                    <li>
                        <div @class(['border-t -mr-6 rtl:-mr-auto rtl:-ml-6'])></div>
                    </li>
                    <x-sidebar.group label="DAPCC HR Administrator Panel">
                        <x-sidebar.item tooltip="Employee Masterlist" :url="route('dapcc.hr-admin.master-list')" :active="request()->routeIs('dapcc.hr-admin.master-list')">
                            <x-filament::icon
                                icon="heroicon-m-user-group"
                                class="w-6 shrink-0"
                            />
                            <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                                Employee Masterlist
                            </div>
                        </x-sidebar.item>
                        <x-sidebar.item tooltip="Generate DTR Report" :url="route('dapcc.hr-admin.generate-dtr-report')" :active="request()->routeIs('dapcc.hr-admin.generate-dtr-report')">
                            <x-filament::icon
                                icon="heroicon-m-clipboard-document-list"
                                class="w-6 shrink-0"
                            />
                            <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                                Generate DTR Report
                            </div>
                        </x-sidebar.item>
                        <x-sidebar.item tooltip="Event Calendar" :url="route('dapcc.hr-admin.events')" :active="request()->routeIs('dapcc.hr-admin.events')">
                            <x-filament::icon
                                icon="heroicon-m-calendar-days"
                                class="w-6 shrink-0"
                            />
                            <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                                Event Calendar
                            </div>
                        </x-sidebar.item>
                        {{-- <x-sidebar.item tooltip="Official Time - Change Requests" :url="route('hr-admin.official-time-change-req.index')" :active="request()->routeIs('hr-admin.official-time-change-req.*')">
                            <x-filament::icon
                                icon="heroicon-m-cog"
                                class="w-6 shrink-0"
                            />
                            <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                                Official Time - Change Requests
                            </div>
                        </x-sidebar.item> --}}
                        <x-sidebar.item tooltip="Time Entries" :url="route('dapcc.hr-admin.time-entries.index')" :active="request()->routeIs('dapcc.hr-admin.time-entries.*')">
                            <x-filament::icon
                                icon="heroicon-m-clock"
                                class="w-6 shrink-0"
                            />
                            <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                                Time Entries
                            </div>
                        </x-sidebar.item>
                    </x-sidebar.group>
                    @endcan
                @endif
                @if(Auth::user()->can('view-pasig'))
                    @can('isAdminCoordinator')
                    <li>
                        <div @class(['border-t -mr-6 rtl:-mr-auto rtl:-ml-6'])></div>
                    </li>
                    <x-sidebar.group label="Admin Coordinator Panel">
                        <x-sidebar.item tooltip="Official Time" :url="route('admin.official-time')" :active="request()->routeIs('admin.official-time')">
                            <x-filament::icon
                                icon="heroicon-m-cog"
                                class="w-6 shrink-0"
                            />
                            <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                                Official Time
                            </div>
                        </x-sidebar.item>
                        <x-sidebar.item tooltip="Event Calendar" :url="route('admin.event-calendar')" :active="request()->routeIs('admin.event-calendar')">
                            <x-filament::icon
                                icon="heroicon-m-calendar-days"
                                class="w-6 shrink-0"
                            />
                            <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                                Event Calendar
                            </div>
                        </x-sidebar.item>
                        <x-sidebar.item tooltip="Daily Time Records" :url="route('admin.dtr.index')" :active="request()->routeIs('admin.dtr.*')">
                            <x-filament::icon
                                icon="heroicon-m-document-text"
                                class="w-6 shrink-0"
                            />
                            <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                                Daily Time Records
                            </div>
                        </x-sidebar.item>
                    </x-sidebar.group>
                    @endcan
                    @can('isHrAdmin')
                    <li>
                        <div @class(['border-t -mr-6 rtl:-mr-auto rtl:-ml-6'])></div>
                    </li>
                    <x-sidebar.group label="HR Administrator Panel">
                        <x-sidebar.item tooltip="Employee Masterlist" :url="route('hr-admin.master-list')" :active="request()->routeIs('hr-admin.master-list')">
                            <x-filament::icon
                                icon="heroicon-m-user-group"
                                class="w-6 shrink-0"
                            />
                            <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                                Employee Masterlist
                            </div>
                        </x-sidebar.item>
                        <x-sidebar.item tooltip="Generate DTR Report" :url="route('hr-admin.generate-dtr-report.index')" :active="request()->routeIs('hr-admin.generate-dtr-report.*')">
                            <x-filament::icon
                                icon="heroicon-m-clipboard-document-list"
                                class="w-6 shrink-0"
                            />
                            <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                                Generate DTR Report
                            </div>
                        </x-sidebar.item>
                        <x-sidebar.item tooltip="Event Calendar" :url="route('hr-admin.events.index')" :active="request()->routeIs('hr-admin.events.*')">
                            <x-filament::icon
                                icon="heroicon-m-calendar-days"
                                class="w-6 shrink-0"
                            />
                            <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                                Event Calendar
                            </div>
                        </x-sidebar.item>
                        <x-sidebar.item tooltip="Official Time - Change Requests" :url="route('hr-admin.official-time-change-req.index')" :active="request()->routeIs('hr-admin.official-time-change-req.*')">
                            <x-filament::icon
                                icon="heroicon-m-cog"
                                class="w-6 shrink-0"
                            />
                            <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                                Official Time - Change Requests
                            </div>
                        </x-sidebar.item>
                        <x-sidebar.item tooltip="Time Entries" :url="route('hr-admin.time-entries.index')" :active="request()->routeIs('hr-admin.time-entries.*')">
                            <x-filament::icon
                                icon="heroicon-m-clock"
                                class="w-6 shrink-0"
                            />
                            <div class="flex flex-1" x-data="{}" x-show="$store.sidebar.isOpen">
                                Time Entries
                            </div>
                        </x-sidebar.item>
                    </x-sidebar.group>
                    @endcan
                @endif
            <li>
                <div @class(['border-t -mr-6 rtl:-mr-auto rtl:-ml-6'])></div>
            </li>
        </ul>
    </nav>
</aside>
