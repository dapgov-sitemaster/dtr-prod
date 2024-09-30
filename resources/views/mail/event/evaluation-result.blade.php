<x-mail::message>
# Good Day {{ $data->employee->first_name }},

Your request has been **{{ $data->status }}**. See the information below:

Date: **{{ $data->start->format('F d, Y') }}**

Event Requested: **{{ $data->tag->getLabel() }}**

@if($data->tag == \App\Enums\Events::ALA)
Type of Official Leave: **{{ \App\Enums\OfficialLeaves::parse($data->description)->getLabel() }}**
@endif

@if($data->tag == \App\Enums\Events::WFH && $data->status == 'approved')
You can Time in using this <a href="{{ route('employee.work-from-home') }}">link</a> for your flexible working arrangement.
@endif

@if($note)
Attendance Monitor's note: **{{ $note }}**
@endif

*If you have any other concern about the system, please contact us through this email: <a href="mailto:ictd@dap.edu.ph">ictd@dap.edu.ph</a>*.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
