<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\HtmlString;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class EventRequestEvaluated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public array $data)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {

        return (new MailMessage)
            ->subject(str($this->data['evaluation_result'])->title() . ' ' . $this->data['event'] . ' request')
            ->greeting('Good Day ' . $notifiable->employee->first_name . '!')
            ->line('Your ' . $this->data['event'] . ' request has been **' . $this->data['evaluation_result'] . '** by ' . auth()->user()->employee->full_name)
            ->lineIf($this->data['note'] != null, 'Remarks from Attendance Monitor:')
            ->lineIf($this->data['note'] != null, new HtmlString('<div style="text-align: center;font-weight: 700;font-style: italic;">' . $this->data['note'] . '</div><br/>'))
            ->line('This notification is just for your information!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
