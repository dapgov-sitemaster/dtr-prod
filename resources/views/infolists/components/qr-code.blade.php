<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div>
        <img src="data:image/jpg;base64, {!! base64_encode(QrCode::errorCorrection('H')->format('png')->merge(public_path('image/applogo.jpg'), .1, true)->size(300)->generate(Crypt::encryptString($getState()))) !!}" width="300" class="mx-auto border-2" draggable="false" />
    </div>
    <div class="mx-auto text-center my-4">{{ $getAction('download-qr-code') }}</div>
</x-dynamic-component>
