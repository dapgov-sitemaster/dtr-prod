<div>
    <x-filament::modal id="calendar-tut" icon-color="info" width="5xl" :close-by-clicking-away="false" class="max-h-screen" sticky-header>
        <x-slot name="heading">
            <span class="text-2xl text-dap-primary font-semibold underline decoration-dap-secondary">Welcome to Schedule Calendar!</span>
        </x-slot>
        <div class="mx-4 overflow-auto">
            <div class="">
                The main improvement of this update are the User Interface for the calendar. Please see below on how to use properly the Calendar.
            </div>
            <div class="font-bold mt-6">I. Creating an Event</div>
            <div class="ml-8">
                <ul class="list-decimal">
                    {{-- <li>First you have to click the <strong>New Event</strong> button to the top right to create an event.</li> --}}
                    <li>First you have to click the <strong>Specific Date</strong> to create an event.</li>
                    {{-- <li>Select the <strong>Date</strong> of the event. <span class="italic">Note: You can not select the date beyond the current Date</span></li> --}}
                    <li>Select the <strong>Type</strong> of the event.</li>
                    <div class="ml-4">3.1. These are the types of the Event that you can schedule.
                        <div class="ml-16">
                            <ul class="list-disc">
                                <li>Work from Home (<span class="italic">This can only be selected on <strong>Fridays</strong></span>)</li>
                                <li>Hybrid Work from Home (<span class="italic">This can only be selected on <strong>Fridays</strong></span>)</li>
                                <li>Official Leave. You can select the type of Official Leave. <br/> The different types are listed below:<br/>
                                    <ul class="list-decimal ml-8">
                                        <li>Vacation Leave</li>
                                        <li>Mandatory/Forced Leave</li>
                                        <li>Sick Leave</li>
                                        <li>Maternity Leave</li>
                                        <li>Paternity Leave</li>
                                        <li>Special Privilege Leave</li>
                                        <li>Solo Parent Leave</li>
                                        <li>Study Leave</li>
                                        <li>Rehabilitation Privilege</li>
                                        <li>Special Leave Benefits for Women</li>
                                        <li>Special Emergency (Calamity) Leave</li>
                                        <li>Adoption Leave</li>
                                    </ul>
                                </li>
                                <li>Official Business</li>
                                <li>Compensatory Day-off</li>
                            </ul>
                        </div>
                    </div>

                    <li>Next is to Select Employees. You can multi-select the employee that you want to schedule.</li>
                    <li>After you have filled all the required fields, you can now click the <strong>Create</strong> button.</li>
                </ul>

            </div>
            <div class="font-bold mt-4">II. Viewing an Event</div>
            <div class="indent-6">To view an Event, simply click the Event on the calendar and it will pop-up a window that previewing the details of the Event. <span class="line-through">Some of the events like Official Leave and Official Business have an option to upload an MOV, you can simply click the button to Upload the MOV (It will relfect also to the DTR Report of the Employee).</span></div>
            <div class="font-bold mt-4">III. Updating an Event</div>
            <div class="indent-6 italic">Note: You can not edit the events beyond the current Date</div>
            <div class="ml-6">
                <ul class="list-disc">
                    <li>To update an Event, click the event that you want to edit, it will pop-up the window that previewing the details of the Event. On the bottom, you can see the <strong>Edit</strong> button.</li>
                    <li>It will pop-up the same form on the create Event. Simply, edit the details.</li>
                    <li>After you are done editing the event. Just click <strong>Save Changes</strong> button to update the event</li>
                    <li class="line-through">In some types of event (e.g. Official Leave and Official Business), you can also upload the MOV of that event. You can do this by clicking the <strong>Upload MOV</strong> button and it will pop-up a window with an upload form. Simply upload the document and click <strong>Submit</strong> to add MOV to that event.</li>
                </ul>
            </div>
            {{-- <div class="indent-8 font-semibold italic">Tip: You can also edit the Event by dragging the event and drop it on another Date.</div> --}}
            <div class="font-bold mt-4">IV. Deleting an Event</div>
            {{-- <div class="indent-6 italic">Note: You can not delete the events beyond the current Date</div> --}}
            <div class="indent-6">To delete an Event, click the event that you want to delete and the preview window will pop-up. On the bottom, you can see the <strong>Delete</strong> button. Simply click the delete button and the confirmation window will pop-up to confirm you action. Just click the <strong>Confirm</strong> button to confirm the deletion of the Event.</div>
            
            <div class="font-bold mt-4">IV. Evaluating an Employee's Event Application</div>
            <div class="indent-6">To evaluate the employee's request of event, you can do that in both List and Calendar mode.</div>
            <div class="list-disc">
                <li>
                    <span class="font-semibold">Table mode</span>
                    <div class="indent-6">Simply click the <strong>: Action</strong> button to the selected employee to evaluate. Next click the Evaluate request to proceed. A pop-up window will show and you will have a choice if you want to approve the request or to disapprove the request. Approving the request will tag the event as approve and it will be shown to the DTR Report remarks. When disapproving a request, you can indicate a reason or note for disapproval. The evaluation will be also be emailed to you and the requestor.</div>
                </li>
                <li>
                    <span class="font-semibold">Calendar mode</span>
                    <div class="indent-6">Just simply go the event date and all of the employee that has schedule in that date will be listed. Pending request will be only to evaluate. Simply click the <strong>: Action</strong> button to the selected employee to evaluate. Next click the Evaluate request to proceed. A pop-up window will show and you will have a choice if you want to approve the request or to disapprove the request. Approving the request will tag the event as approve and it will be shown to the DTR Report remarks. When disapproving a request, you can indicate a reason or note for disapproval. The evaluation will be also be emailed to you and the requestor.</div>
                </li>
            </div>
            <div class="indent-6">Please do note that the edit and delete function will be available in all evaluated requests and pending requests.</div>
        </div>
        <x-slot name="footer">
            <div class="font-semibold indent-6">Developer's Note: Please if you have encountered any bugs and error in this feature, kindly fill up this link (<a href="https://forms.gle/4jdH3KnLu1HKnx6L8" target="_blank" class="underline text-blue-700">https://forms.gle/4jdH3KnLu1HKnx6L8</a>). Also if you have any questions and suggestions, you can submit it in the link.</div>
            <div class="font-semibold indent-6 italic">It will help us to improve this feature and the whole system. Thank you.</div>
        </x-slot>
    </x-filament::modal>
</div>
