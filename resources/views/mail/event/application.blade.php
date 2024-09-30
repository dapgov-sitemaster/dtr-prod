<x-mail::message>
# Good Day {{ $data->employee->first_name }},

Your request has been submitted. Please see the information below.

@if($is_bulk)
Date from **{{ $data->dates[0]->format('F d, Y') .' to '. $data->dates[1]->format('F d, Y') }}**
@else
Date: **{{ $data->start->format('F d, Y') }}**
@endif

Event Requested: **{{ $data->tag->getLabel() }}**

@if($data->tag == \App\Enums\Events::ALA)
Type of Official Leave: **{{ \App\Enums\OfficialLeaves::parse($data->description)->getLabel() }}**
@endif

Kindly wait for the Attendance Monitor to evaluate your request.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
