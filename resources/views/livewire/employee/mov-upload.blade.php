<div>
    <x-loading wire:loading />
    <div class="w-full bg-white rounded-xl mb-10 p-4">
        <div class="flex p-4 font-semibold text-xl items-center">
            <div class="w-full lg:flex lg:justify-between p-2">
                <div class="flex">
                    My Pending MOV upload
                </div>
            </div>
        </div>
        <div class="mx-2 px-4 my-2">
            {{ $this->table }}
        </div>
    </div>
</div>
