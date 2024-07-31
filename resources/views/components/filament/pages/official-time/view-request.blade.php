<div>
    @php
        $time_in = Carbon\Carbon::parse($record->time_in);
    @endphp
    <div class="mb-4">
        <div class="font-bold text-sm">HRIS Number</div>
        <div>{{ $record->hris_number }}</div>
    </div>
    <div class="my-4">
        <div class="font-bold text-sm">Name</div>
        <div>{{ $record->employee->full_name }}</div>
    </div>
    <div class="my-4">
        <div class="font-bold text-sm">Requested Official Time</div>
        <div>{{ $time_in->format('g:i A'). ' - ' .$time_in->copy()->addHours(9)->format('g:i A') }}</div>
    </div>
    <div class="my-4">
        <div class="font-bold text-sm">Status</div>
        @if($record->status === "pending")
        <div>Change request is still in process. Kindly wait for the approval from HR Admin.</div>
        @elseif($record->status === "disapproved")
        <div>Change request has been disapproved. You can submit again for approval to HR Admin.</div>
        @endif
    </div>
</div>
