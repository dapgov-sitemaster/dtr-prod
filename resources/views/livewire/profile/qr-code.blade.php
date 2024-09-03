<div>
    <x-loading wire:loading />
    <div class="w-full bg-white p-8 rounded-xl mb-10">
        <div class="text-3xl font-bold text-dap-primary underline decoration-dap-secondary/50">
            QR Code
        </div>
        <div class="mt-6 text-center">
            <div class="text-center">
                <img src="data:image/jpg;base64, {!! base64_encode(QrCode::errorCorrection('H')->format('png')->merge(public_path('image/applogo.jpg'), .1, true)->size(300)->generate(Crypt::encryptString(auth()->user()->hris_number))) !!}" width="300" class="mx-auto border-2" draggable="false" />
                <div class="mx-auto text-base md:text-lg font-semibold">{{ auth()->user()->hris_number. ' - ' .auth()->user()->employee->full_name }}</div>
                <a href="{{ route('pdf.empqrcode') }}" class="text-center" target="_blank">
                    <x-button color="primary" class="py-1 my-10">
                        Download ID Card
                    </x-button>
                </a>
            </div>
        </div>
    </div>
</div>
