<x-mail::message>
# Good day {{ $data->employee->first_name .' '.$data->employee->last_name }}!

We would like to notify you that our eDTR system has recorded a change in your official working hours, effective from **{{ $data->effectivity_date->format('F d, Y') }}**.

**Updated Official Time**
Schedule Type: **{{ $data->schedule_type->getLabel() }}**
@if($data->schedule_type == \App\Enums\ScheduleType::FULLFLEXI)
Start Time: **7:00AM - 9:30AM**
End Time: **4:00PM - 6:30PM**
@else
Start Time: **{{ Carbon\Carbon::parse($data->time_in)->format('g:i A') }}**
End Time: **{{ Carbon\Carbon::parse($data->time_in)->addHours(9)->format('g:i A') }}**
@endif

These changes have been automatically updated in the eDTR, and all future timekeeping will reflect the new schedule. Please ensure you follow the updated working hours moving forward.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
