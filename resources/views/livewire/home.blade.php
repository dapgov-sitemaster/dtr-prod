<div class="">
    {{-- <div class="w-full grid lg:grid-cols-3 grid-cols-1 lg:gap-4"> --}}
    <div class="w-full ">
        <div class="lg:col-span-2">
            <div class="mb-4">
                <div class="w-full bg-white rounded-xl my-2 p-4">
                    <div class="flex p-4 font-semibold text-xl items-center">
                        <div class="w-full lg:flex lg:justify-between p-2">
                            <div>
                                <div class=" text-md lg:text-2xl">
                                    <span class="lg:block hidden">{{ $now->format('l, F d, Y') }}</span>
                                    <span class="block lg:hidden">{{ $now->format('Y-m-d') }}</span>
                                </div>
                                <div class="font-semibold text-3xl">
                                    {{ $greetings }}
                                </div>
                            </div>
                            <div>
                                <img src="{{ asset('image/paper_plane.png') }}" class="w-20 lg:w-40">
                            </div>
                        </div>
                    </div>
                    {{-- <div class="mx-2 px-4 my-2">
                        test
                    </div> --}}
                </div>
            </div>
            <div class="mb-4">
                @livewire('employee.mov-upload')
            </div>
            @can('isAdminCoordinator')
                <div class="mb-4">
                    @livewire('admin-coord.daily-time-report')
                </div>
            @endcan
        </div>
        {{-- <div class="w-full">
            <div class="w-full bg-white rounded-xl my-2 p-4">
                <div class="flex p-4 font-semibold text-xl items-center">
                    <div class="w-full lg:flex lg:justify-between p-2">
                        <div class="flex">
                            Work from Home
                        </div>
                        <div>
                            {{ now()->format('F d, Y (D)') }}
                        </div>
                    </div>
                </div>
                <div class="mx-2 px-4 my-2">
                    test
                </div>
            </div>
        </div> --}}
    </div>
</div>
