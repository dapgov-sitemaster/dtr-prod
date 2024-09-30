<x-mail::message>
# Good Day {{ $data->employee->first_name }},

Your request has been submitted. Please see the information below.

Date: **{{ $data->start->format('F d, Y') }}**

Event Requested: **{{ $data->tag->getLabel() }}**

@if($data->tag == \App\Enums\Events::ALA)
Type of Official Leave: **{{ \App\Enums\OfficialLeaves::parse($data->description)->getLabel() }}**
@endif

Kindly wait for the Attendance Monitor to evaluate your request.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
