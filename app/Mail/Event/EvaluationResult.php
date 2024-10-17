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

class EvaluationResult extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $evaluator;

    /**
     * Create a new message instance.
     */
    public function __construct(public $data, public $note = null)
    {
        //
        $this->evaluator = auth()->user()->employee->full_name;
        // if (auth()->user()->hris_number == '111111') {

        // }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: str($this->data->employee->last_name)->title() . ' - ' . $this->data->start->format('m/d/Y') . ' ' . $this->data->tag->getLabel() . ' Schedule Request',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.event.evaluation-result',
            with: ['data' => $this->data, 'note' => $this->note, 'evaluator' => $this->evaluator],
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
