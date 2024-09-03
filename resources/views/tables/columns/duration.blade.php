<div>
    {{-- @php
        $requests = \App\Models\OfficialTime::where('hris_number', $getState())->orderBy('created_at', 'desc')->limit(2)->get();
    @endphp
    @if($request->count() > 1)
        {{ $requests->last()->created_at->format }}
    @else

    @endif --}}
</div>
