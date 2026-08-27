<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta charset="UTF-8">
    <title>{{ 'edtr-qrcode-idcard.pdf' }}</title>
    <!-- Styles -->
    <style>
        html { margin-top: 155px;padding-top:155px;margin-bottom: 0px; }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
{{-- <body style="font-size: 11px">
    <div class="bg-image">
    </div> --}}

    @foreach($employees as $employee)
    <div style="display: inline-block;">
        <div style="display: inline-block;text-align: center;" >
            <div style="position: relative; top:42;">
                <img src="{{ public_path().'/image/id-front.jpg' }}" style="" width="202px" height="322px"  />
                <div style="position: absolute; top: 4; left:28;">
                    <img src="data:image/jpg;base64, {!! base64_encode(SimpleSoftwareIO\QrCode\Facades\QrCode::errorCorrection('H')->format('png')->merge(public_path('image/applogo.jpg'), .1, true)->size(300)->margin(0)->generate(Crypt::encryptString($employee->hris_number))) !!}"  width="130px" height="131px" class="mx-auto" />
                </div>
                @php
                    $char = strlen($employee->full_name);
                @endphp
                <div style="position: absolute; top: @if($char > 32) 106 @elseif($char > 25) 105 @elseif($char > 20) 102 @else 100 @endif; right: 8px; width:100%;font-size: @if($char > 32) 10px @elseif($char > 25) 11px  @elseif($char > 20) 13px @else 15px @endif;">
                    <p style="color: white;font-family: Arial, Helvetica, sans-serif; font-weight: bold;  ">
                        {{$employee->full_name}}
                    </p>
                </div>
                <div style="position: absolute; top: 124; left:57; ">
                    <p style="color: white;font-family: Arial, Helvetica, sans-serif; font-weight: bold; ">
                        {{$employee->hris_number}}
                    </p>
                </div>
            </div>
            <img src="{{ public_path().'/image/id-back.jpg' }}" width="202px" height="322px" style="margin-right: 12px;margin-top: -8px" />
        </div>
    </div>
    @endforeach
    {{-- <img src="{{ public_path().'/image/id-front.jpg' }}" width="200px" style="margin-right: 10px;" />
    <img src="{{ public_path().'/image/id-front.jpg' }}" width="200px" style="margin-right: 10px;" />

    <img src="{{ public_path().'/image/id-back.jpg' }}" width="200px" style="margin-right: 10px;" />
    <img src="{{ public_path().'/image/id-back.jpg' }}" width="200px" style="margin-right: 10px;" />
    <img src="{{ public_path().'/image/id-back.jpg' }}" width="200px" style="margin-right: 10px;" /> --}}
</body>
</html>
