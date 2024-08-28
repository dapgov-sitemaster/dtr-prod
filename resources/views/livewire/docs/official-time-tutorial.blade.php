<div>
    <x-filament::modal id="official-time-tut" icon-color="info" width="5xl" :close-by-clicking-away="false" class="max-h-screen" sticky-header>
        <x-slot name="heading">
            <span class="text-2xl text-dap-primary font-semibold underline decoration-dap-secondary">Welcome to Official Time module!</span>
        </x-slot>
        <div class="mx-4 overflow-auto">
            <div class="">
                The main improvement of this update are the addition of the <strong>Full Flexitime</strong> and the User Interface/User Interface.
            </div>
            <div class="font-semibold mt-6">I. Setting the Official Time of the Employee (Individual Setting)</div>
            <div class="ml-8">
                <ul class="list-disc">
                    <li>First is to click the <strong>Set Schedule</strong> on the employee that you want to set.</li>
                    <li>
                        There will be a pop-up window, you must fill up the form. You can select a schedule type between <strong>Full Flexitime Schedule</strong> or <strong>Official Work Schedule</strong>
                        <div class="ml-10">
                            <ul class="list-disc">
                                <li><strong>Full Flexitime Schedule</strong>: DAP personnel shall report to work (time-in) at any time from 7:00 AM to 9:30 AM and strall leave work (time-out) at any time from 4:00 PM to 7:00 PM, provided that the weekly and daily required total work hours (8hrs).</li>
                                <li>
                                    <strong>Official Work Schedule</strong>: DAP personnel shall select their official work hours from the following:
                                    <div class="ml-8">
                                        <ul>
                                            <li>7:00 AM to 4:00 PM</li>
                                            <li>7:30 AM to 4:30 PM</li>
                                            <li>8:00 AM to 5:00 PM</li>
                                            <li>8:30 AM to 5:30 PM</li>
                                            <li>9:00 AM to 6:00 PM</li>
                                            <li>9:30 AM to 6:30 PM</li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li>Next is to add Effectivity of the request. As this will be the reference of the system when to apply the changes of the Schedule</li>
                    <li>After that, you must upload the signed document request here as this will be validate by the HR Administrator.</li>
                    <li>After you have done filling up the form, click <strong>Submit</strong> button to submit your request</li>
                    <li>Your request will be reflected if the HR Administrator are done evaluating your request.</li>
                </ul>
            </div>
            <div class="font-semibold mt-6">II. Setting the Official Time of the Employees (Bulk Setting)</div>
            <div class="ml-8">
                <ul class="list-disc">
                    <li>To set the schedule of multiple personnel in one request, just click the box of the personnel that you want to set the schedule.</li>
                    <li>After you are done selecting the personnel, click the button <strong>Bulk Set of Schedule</strong>. Then it will show the form (The same as the form above.)</li>
                    <li>Fill up the form and make sure that the personnel that you have selected are included in the Document Request.</li>
                    <li>After you have done filling up the form, click <strong>Submit</strong> button to submit your request</li>
                    <li>Your request will be reflected if the HR Administrator are done evaluating your request.</li>
                </ul>
            </div>
            <div class="font-semibold mt-6">III. Viewing the Status of the Request</div>
            <div class="ml-8">You can view the status of your change of schedule request by clicking the <strong>View Request</strong> button on the table. It will display the personnel details and status of your request</div>

        </div>
        <x-slot name="footer">
            <div class="font-semibold indent-6">Developer's Note: Please if you have encountered any bugs and error in this feature, kindly fill up this link (<a href="http://bit.ly/edtr-test" target="_blank" class="underline text-blue-700">http://bit.ly/edtr-test</a>). Also if you have any questions and suggestions, you can submit it in the link.</div>
            <div class="font-semibold indent-6 italic">It will help us to improve this feature and the whole system. Thank you.</div>
        </x-slot>
    </x-filament::modal>
</div>
