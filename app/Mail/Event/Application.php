<?php

namespace App\Mail\Event;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Application extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public $data, public $is_bulk = false)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        if ($this->is_bulk) {
            $subject = str($this->data->employee->last_name)->title() . ' | ' . $this->data->dates[0]->format('m/d/Y') . ' - ' . $this->data->dates[1]->format('m/d/Y') . ' '  . $this->data->tag->getLabel() . ' Schedule Request';
        } else {
            $subject = str($this->data->employee->last_name)->title() . ' | ' . $this->data->start->format('m/d/Y') . ' ' . $this->data->tag->getLabel() . ' Schedule Request';  // 211515 SERRAON - 09/30/2024 Official Leave Request
        }

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.event.application',
            with: ['data' => $this->data, 'is_bulk' => $this->is_bulk],
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
