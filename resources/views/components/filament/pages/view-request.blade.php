<div>
    @php
        $request = App\Models\OfficialTime::where('hris_number', $hris_number)->where('status', '<>', 'approved')->latest()->first();
        $time_in = Carbon\Carbon::parse($request->time_in);
    @endphp
    <div class="mb-4">
        <div class="font-bold text-sm">HRIS Number</div>
        <div>{{ $request->hris_number }}</div>
    </div>
    <div class="my-4">
        <div class="font-bold text-sm">Name</div>
        <div>{{ $request->employee->full_name }}</div>
    </div>
    <div class="my-4">
        <div class="font-bold text-sm">Requested Official Time</div>
        <div>{{ $time_in->format('g:i A'). ' - ' .$time_in->copy()->addHours(9)->format('g:i A') }}</div>
    </div>
    <div class="my-4">
        <div class="font-bold text-sm">Status</div>
        @if($request->status === "pending")
        <div>Change request is still in process. Kindly wait for the approval from HR Admin.</div>
        @elseif($request->status === "disapproved")
        <div>Change request has been disapproved. You can submit again for approval to HR Admin.</div>
        @endif
    </div>
</div>
