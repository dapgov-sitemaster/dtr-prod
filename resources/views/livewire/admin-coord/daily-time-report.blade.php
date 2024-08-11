<div>
    <div class="w-full bg-white rounded-xl my-2 p-4">
        <div class="flex p-4 font-semibold text-xl items-center">
            <div class="w-full lg:flex lg:justify-between p-2">
                <div class="flex">
                    Division/Office Daily Time Report
                </div>
                <div>
                    {{ now()->format('F d, Y (D)') }}
                </div>
            </div>
        </div>
        <div class="mx-2 px-4 my-2">
            {{ $this->table }}
        </div>
        {{-- <div class="p-4 rounded-b-xl flex justify-between border-t-2 bg-gray-200">

        </div> --}}
    </div>
</div>
