<?php

namespace App\Mail;

use App\Enums\Role;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class OfficialTimeChanges extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public $data, public $file = null, public $filename = null)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // if ($this->is_bulk) {
        //     $user = auth()->user();
        //     $departments = match ($user->role) {
        //         Role::CENTERADMINCOORD => $user->employee->department->group . '/' . $user->employee->department->center,
        //         Role::GROUPADMINCOORD => $user->employee->department->group,
        //         default => $user->employee->department->description,
        //     };

        //     $subject = $departments . ' | Official Time Changes'; // OSVP-S/ADMIN/ICTD | Official Time Changes
        // } else {
        //     $subject = str($this->data->employee->last_name)->title() . ' | Official Time Changes';  // SERRAON | Official Time Changes
        // }

        return new Envelope(
            subject: $this->data->hris_number . ' ' . str($this->data->employee->last_name)->title() . ' | Official Time Changes',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // $with = null;
        // if ($this->is_bulk) {
        // } else {
        //     $with = ['employee' => $this->data->employee, 'data' => $this->data, 'is_bulk' => $this->is_bulk];
        // }

        return new Content(
            markdown: 'mail.official-time-changes',
            with: ['employee' => $this->data->employee, 'data' => $this->data],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments()
    {
        return Attachment::fromData(fn() => $this->file, $this->filename);
    }
}
