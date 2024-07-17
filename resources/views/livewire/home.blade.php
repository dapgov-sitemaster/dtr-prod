<div class="flex w-full">
    <div class="flex-1 w-full pb-4 px-4 mx-auto md:px-6 lg:px-8 max-w-8xl">
        <div class="w-full">
            <div class="w-full bg-white lg:p-8 p-4 rounded-xl">
                {{-- header --}}
                <div class="w-full">
                    <div class="flex justify-between">
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
            </div>
        </div>
    </div>
</div>
