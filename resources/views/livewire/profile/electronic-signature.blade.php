<div>
    <x-loading wire:loading />
    <div class="w-full bg-white p-8 rounded-xl mb-10">
        <div class="text-3xl font-bold text-dap-primary underline decoration-dap-secondary/50">
            e-Signature
        </div>
        <div class="mt-6 text-center">
            <div class="flex justify-between">
                <input type="file" name="upload_image" id="upload_image" accept="image/png"
                    class="w-full border-2 border-primary p-2 rounded-lg">
                <x-button color="primary" class="saveSignature hidden w-64 py-1 px-5 mx-1 my-auto justify-center">
                    <x-filament::icon
                        icon="heroicon-m-arrow-down-tray"
                        class="h-5 w-5 text-gray-500 dark:text-gray-400 my-auto mr-2"
                    /> Save Signature
                </x-button>
            </div>
            <div>
                <div class="preview my-2 hidden">
                    <div class="m-1 flex justify-end">
                        <x-button id="reset_crop" color="default" class="py-1 px-2 mx-1 flex">
                            <x-filament::icon
                                icon="heroicon-m-arrow-path"
                                class="h-5 w-5 text-gray-500 dark:text-gray-400"
                            /> Reset
                        </x-button>
                        <x-button id="crop_image" color="primary" class="py-1 px-2 mx-1">Crop Image</x-button>
                    </div>
                    <div class="result "></div>
                </div>
                <div class="text-center w-full img-result hidden"></div>
                @if(auth()->user()->employee->signature_path)
                <div class="w-full text-center my-2 current">
                    <div>Current e-Signature</div>
                    <img src="data:image/jpeg;base64, {{ $current_image }}" class="mx-auto" alt="" draggable="false">
                </div>
                @endif
            </div>
        </div>
    </div>


</div>
@push('scripts')
<script type="text/javascript">
    let result = document.querySelector('.result'),
            img_result = document.querySelector('.img-result'),
            cropped = document.querySelector('.cropped'),
            cropper = "";

    $('#upload_image').change(function(event) {
        if(document.getElementById("upload_image").files.length > 0) {
            $('.img-result').addClass('hidden');
            $('.current').addClass('hidden');
            $('.preview').removeClass('hidden');
            $('.result').empty();
            $('.img-result').empty();
            const reader = new FileReader();
            reader.onload = (event) => {
                if(event.target.result) {
                    let img = document.createElement('img');
                    img.id = "image";
                    img.src = event.target.result;
                    result.innerHtml = "";
                    result.appendChild(img);
                    cropper = new Cropper(img, {
                        viewMode: 0,
                        dragMode: 'move',
                        aspectRatio : 6/3,
                        autoCropArea:0.9
                    });
                }
                else {
                    $('.img-result').removeClass('hidden');
                    $('.preview').addClass('hidden');
                    $('.result').empty();
                    $('.img-result').empty();
                }
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    });

    $('#reset_crop').click(function() {
        cropper.reset();
    });

    $('#crop_image').click(function() {
        var croppedImage = cropper.getCroppedCanvas({
            width:400,
            height:400
        });
        $('.preview').addClass('hidden');
        let res =  document.querySelector('.img-result');
        $('.img-result').removeClass('hidden');
        $('.saveSignature').removeClass('hidden');
        $('.saveSignature').addClass('flex');
        croppedImage.toBlob(function(blob) {
            url = URL.createObjectURL(blob);
            var reader = new FileReader();
            reader.readAsDataURL(blob);
            reader.onloadend = function() {
                var base64data = reader.result;

                let img = document.createElement('img');
                img.id = "signature_result";
                img.className = "mx-auto border-2 border-red-800";
                img.src = event.target.result;
                res.innerHtml = "";
                res.appendChild(img);
            };
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        $('.saveSignature').click(function() {
            var croppedImage = cropper.getCroppedCanvas({
                width:400,
                height:400
            });

            croppedImage.toBlob(function(blob) {
                var result = null;
                url = URL.createObjectURL(blob);
                var reader = new FileReader();
                reader.readAsDataURL(blob);
                reader.onloadend = function() {
                    var base64data = reader.result;
                    result = event.target.result;
                    @this.call('saveSignature', result)
                    $('#upload_image').val('')
                };
            });
        });
    });
</script>
@endpush
