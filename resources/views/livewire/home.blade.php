<div class="">
    {{-- <div class="w-full grid lg:grid-cols-3 grid-cols-1 lg:gap-4"> --}}
    <div class="w-full ">
        <div class="lg:col-span-2">
            <div class="mb-4">
                <div class="w-full bg-white rounded-xl my-2 p-4">
                    <div class="flex p-4 font-semibold text-xl items-center">
                        <div class="w-full lg:flex lg:justify-between p-2">
                            <div>
                                <div class=" text-md lg:text-2xl">
                                    <span class="lg:block hidden">{{ $now->format('l, F d, Y') }}</span>
                                    <span class="block lg:hidden">{{ $now->format('Y-m-d') }}</span>
                                </div>
                                <div class="font-semibold text-3xl">
                                    {{ $greetings }}
                                </div>
                            </div>
                            <div>
                                <img src="{{ asset('image/paper_plane.png') }}" class="w-20 lg:w-40">
                            </div>
                        </div>
                    </div>
                    {{-- <div class="mx-2 px-4 my-2">
                        test
                    </div> --}}
                </div>
            </div>
            {{-- <div class="mb-4">
                @livewire('employee.mov-upload')
            </div> --}}
            @can('isAdminCoordinator')
                <div class="mb-4">
                    @livewire('admin-coord.daily-time-report')
                </div>
            @endcan
        </div>
        {{-- <div class="w-full">
            <div class="w-full bg-white rounded-xl my-2 p-4">
                <div class="flex p-4 font-semibold text-xl items-center">
                    <div class="w-full lg:flex lg:justify-between p-2">
                        <div class="flex">
                            Work from Home
                        </div>
                        <div>
                            {{ now()->format('F d, Y (D)') }}
                        </div>
                    </div>
                </div>
                <div class="mx-2 px-4 my-2">
                    test
                </div>
            </div>
        </div> --}}
        @can('view-pasig')
        <x-filament::modal
            id="system-changes-notice"
            width="5xl"
            icon="heroicon-o-exclamation-triangle"
            :close-button="false"
            :close-by-escaping="false"
            :close-by-clicking-away="false"
            sticky-header>
            <x-slot name="heading">
                System Changes!!
            </x-slot>
            <div>
                <div class="indent-4 text-xl font-bold mb-6 underline">New Feature Alert!</div>
                <div class="font-semibold indent-6">We are pleased to introduce to you the new <strong>Leave & Flexible Schedule Application</strong></div>
                <div class="my-2">To access the Leave & Flexible Schedule Application, just go to the side bar menu and click the <strong>Leave & Flexible Schedule Application</strong>. There are two modes in the module, it will be the List mode and Calendar Mode.</div>
                <div class="list-disc my-2">
                    <li>
                        <span class="font-semibold">Table Mode</span>
                        <div class="indent-8">To create a request, simply click the <strong>Request Schedule</strong> button.</div>    
                        <div class="indent-8">You can select either a single date or a date range depending on your request. Note: You can only schedule after 2 days on the date today.</div>    
                        <div class="indent-8">After Selecting the date, you can now select which event that you want to request.</div>    
                        <div class="indent-8">Then, you can now submit the form to proceed. You will be get an email notification indicating your request, the admin coordinators and supervisor will also be included in the notification.</div> 
                        <div class="indent-8">Once your request has been approved, you also get an email indicating that your request has been approved. If you request has been disapproved, the admin coordinator who evaluate your request can add a note or description indicating the reason for disapproval.</div>   
                    </li>
                    <li>
                        <span class="font-semibold">Calendar Mode</span>
                        <div class="indent-8">In calendar mode, you will also see the other employee events aside from your event schedules.</div>
                        <div class="indent-8">To make a request, simply select a date and a pop-up window will show up.</div>
                        <div class="indent-8">You just have to provide which event that you want to request.</div>
                        <div class="indent-8">After that, you can submit it right away. You will be get an email notification indicating your request, the admin coordinators and supervisor will also be included in the notification.</div>
                        <div class="indent-8">Once your request has been approved, you also get an email indicating that your request has been approved. If you request has been disapproved, the admin coordinator who evaluate your request can add a note or description indicating the reason for disapproval.</div>   
                        <div class="indent-8">Please do note that you can only edit those events that belong to you and those events that is pending.</div>
                    </li>
                </div>
                
                <div class="font-semibold indent-6 mt-8">You can now get a PDF File of your DTR Report!!</div>
                <div class="my-2">To get a PDF File DTR Report, simply go to <strong>My DTR Report</strong> then select any avaiable Year, Month and Cutoff, then click the <strong>Print</strong> button to get an PDF File of your DTR Report.</div>

                @if(auth()->user()->hasRole(\App\Enums\Role::ADMINCOORD) || auth()->user()->hasRole(\App\Enums\Role::CENTERADMINCOORD) || auth()->user()->hasRole(\App\Enums\Role::GROUPADMINCOORD))

                    <div class="my-6">
                        <div class="indent-4 text-xl font-bold mb-6 underline">Event Calendar Changes!</div>
                        <ul class="list-disc ml-6">
                            <li>
                                <span class="font-bold">Changes in Uploading of MOV</span>
                                <div class="indent-8">Please be advised that we are suspending the <span class="font-semibold">Uploading of MOV</span> as per HR ComBen. The reason for this was that the MOVs are still need to be submitted in hard copy. You can still view the uploaded files for future references.</div>
                                <div class="indent-8">If you have any concerns regarding this changes, please contact our HR ComBen.</div>
                            </li>
                            <li>
                                <span class="font-bold">Calendar User Interface</span>
                                <div class="indent-8">We are reverting back the old Event Calendar. There are multiple reports that the last Event Calendar was too slow to reload when there are too many events was set. We also did improve the display when viewing an Event, and the creation of the Event.</div>
                            </li>
                            <li>
                                <span class="font-bold">Evaluating an Employee's Event Application</span>
                                <div class="indent-8 my-4">To evaluate the employee's request of event, you can do that in both List and Calendar mode.</div>
                                
                                <div class="ml-6 my-4">
                                    <div class="font-semibold">Table mode</div>
                                    <div class="indent-6">Simply click the <strong>: Action</strong> button to the selected employee to evaluate. Next click the Evaluate request to proceed. A pop-up window will show and you will have a choice if you want to approve the request or to disapprove the request. Approving the request will tag the event as approve and it will be shown to the DTR Report remarks. When disapproving a request, you can indicate a reason or note for disapproval. The evaluation will be also be emailed to you and the requestor.</div>
                                    <div class="font-semibold mt-4">Calendar mode</div>
                                    <div class="indent-6">Just simply go the event date and all of the employee that has schedule in that date will be listed. Pending request will be only to evaluate. Simply click the <strong>: Action</strong> button to the selected employee to evaluate. Next click the Evaluate request to proceed. A pop-up window will show and you will have a choice if you want to approve the request or to disapprove the request. Approving the request will tag the event as approve and it will be shown to the DTR Report remarks. When disapproving a request, you can indicate a reason or note for disapproval. The evaluation will be also be emailed to you and the requestor.</div>
                                </div>
                                <div class="indent-6 my-4">In line with this, all the event that the attendance monitor created in this module will be automatically approved. No need for a evaluation.</div>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="my-6">
                        <div class="indent-4 text-xl font-bold mb-6 underline">DTR Report Changes!</div>
                        <div class="font-semibold indent-6">As per the instruction of our HR ComBen, DTR Report will now have a <strong>PM Tardy</strong></div>

                        <div class="my-4 indent-6">The PM Tardy will be a tardy indicator in the Lunch Break. If the Employee exceeds a 1 hour lunch break, it will count as PM Tardy.</div>
                        <div class="my-4 indent-6">Addition to this, the Lunch Break will have a default time of <span class="italic">12:00PM to 1:00PM</span> if the employee were not leaving the DAP premises.</div>

                        <div class="my-4 font-semibold indent-2">Note: If ever you have any questions and concerns with this changes, kindly let the HR ComBen know. Please contact their office first.</div>
                    </div>
                    <div class="my-6">
                        <div class="indent-4 text-xl font-bold mb-6 underline">Official Time Changes!</div>
                        <div class="font-semibold indent-6">As per the instruction of our HR ComBen, Attendance Monitors can now independently change the Official Time of the employees under their office/division.</div>

                        <div class="my-4 indent-6">The changes that the Attendance Monitors in official time, the HR ComBen will be notified via Email Notification system generated.</div>
                    </div>
                @endif

                <div class="indent-8 italic font-semibold my-6">If you have any concerns regarding this changes, please do fill up this link (<a href="https://forms.gle/4jdH3KnLu1HKnx6L8" target="_blank" class="underline text-blue-700">https://forms.gle/4jdH3KnLu1HKnx6L8</a>), for us to document the Bug/Error Report in the system. This will help us track the changes in the system. Thank you.</div>
            </div>
            <x-slot name="footer">
                <div class="flex justify-end">
                    <x-filament::button outlined color="secondary" class="mx-2" onclick="dismissModal()">
                        Do not show this again
                    </x-filament::button>
                    <x-filament::button outlined color="gray" class="mx-2" x-on:click="$dispatch('close-modal', { id: 'system-changes-notice' })">
                        Close
                    </x-filament::button>
                </div>
            </x-slot>
        </x-filament::modal>
        @endcan
    </div>
</div>
@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            if (!localStorage.getItem('leaveflexiNotifDismissed')) {
                Livewire.dispatch('open-modal', { id: 'system-changes-notice' });
            }
        })

        function dismissModal() {
            localStorage.setItem('leaveflexiNotifDismissed', 'true');
            Livewire.dispatch('close-modal', { id: 'system-changes-notice' });
        }
    </script>
@endpush
