<?php

namespace App\Mail\Event;

use App\Enums\Role;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class EvaluationResult extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public $data, public $note = null)
    {
        // $admin_coord = Employee::whereHas('user', fn($query) => $query->whereIn('role', [Role::ADMINCOORD, Role::CENTERADMINCOORD, Role::GROUPADMINCOORD]))->where('department_id', $this->data->employee->department_id)->get();

        // $admin_coord = User::whereHas('employee', fn($query) => $query->where('department_id', $this->data->employee->department_id))->whereIn('role', [Role::ADMINCOORD, Role::CENTERADMINCOORD, Role::GROUPADMINCOORD])->get();
        // $this->cc = $admin_coord->pluck('email')->toArray();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->data->hris_number . ' ' . str($this->data->employee->first_name)->title() . ' - ' . $this->data->start->format('m/d/Y') . ' ' . $this->data->tag->getLabel() . ' Request',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.event.evaluation-result',
            with: ['data' => $this->data, 'note' => $this->note],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
