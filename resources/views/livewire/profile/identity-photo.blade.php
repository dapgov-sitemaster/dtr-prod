<div>
    <x-loading wire:loading />
    <div class="my-4">
        <span class=" font-semibold">Upload your 2x2 Photo</span><br/>
        <span>This will be used for your identification upon registering in DTR</span><br/>
        <span>Full-face must be visible</span><br/>
        <span>Not covered by hair. </span><br/>
        <span>Do not wear any head covering/head gear.</span><br/>
        <span>Do not wear any face mask eye glass.</span><br/>
        <span>Do not wear eye glasses.</span><br/>
        <span>The photo has good lighting.</span><br/>
        <span>Maximum of 5MB in file size.</span><br/>
    </div>
    <div class="mx-auto w-full text-center">
        <div wire:loading class="my-2 font-semibold italic text-xl">Loading...</div>
        <div class="w-full" wire:loading.remove>
            @error('photo')
                <span class="text-red-700 text-sm italic">{{ $message }}</span>
            @enderror
            <div class="border-2 {{ ($errors->has('photo')) ? 'border-red-700' : 'border-dap-primary' }} w-96 rounded-lg text-center mx-auto p-2">
                @if($photo)
                    <img src="{{ $photo->temporaryUrl() }}" class=" w-auto mx-auto" draggable="false">
                @elseif($current_image)
                    <img src="data:image/jpeg;base64, {{ base64_encode($current_image) }}" class=" w-auto mx-auto" draggable="false">
                @else
                    <img src="{{ asset('image/default-photo.jpg') }}" class=" w-48 mx-auto" wire:loading.class="hidden" draggable="false">
                @endif
            </div>

            <div class="my-4">
                <label for="image" class=" max-w-full h-auto text-center font-semibold cursor-pointer">
                    <span class=" border-b-2 border-dap-primary">Upload your Photo</span>
                    <input wire:model.live="photo" type="file" class="hidden" id="image"  accept="image/png, image/gif, image/jpeg" />
                </label>
            </div>

            <x-button wire:click="save" color="primary" class="py-1 px-5 mx-1">Save Photo</x-button>
        </div>
        {{-- <div class="w-full" wire:loading.remove>
            @if(auth()->user()->employee->identity_photo_path)
                @if($photo)
                    <img src="{{ $photo->temporaryUrl() }}" class="w-auto mx-auto" draggable="false">
                @else
                    <img src="data:image/jpeg;base64, {{ base64_encode($azure->get($image->image_path)) }}" class="w-auto mx-auto" draggable="false">
                @endif
            @else
            <img :src="src" class=" w-48 mx-auto" wire:loading.class="hidden" draggable="false">
            @endif
            <div class=" my-4">
                <label for="image"  class=" max-w-full h-auto text-center font-semibold cursor-pointer">
                    <span class=" border-b-2 border-primary">Upload your Photo</span>
                    <input wire:model="photo" type="file" class="hidden" id="image"  accept="image/png, image/gif, image/jpeg" @change="src = URL.createObjectURL($event.target.files[0])" />
                </label>

            </div>
        </div>
        @error('photo')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror --}}
        {{-- <div class="flex justify-between my-2">
            <x-button tag="a" href="{{ route('home.index') }}" color="cancel" class="py-1 px-5 border-gray-400 mx-1">Go back</x-button>
            <x-button wire:click="submit" color="primary" class="py-1 px-5 mx-1">Save Photo</x-button>
        </div> --}}
    </div>
</div>
