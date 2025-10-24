<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class loginNeedsVerfication extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
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
        return ['TwilioChannel::class'];
    }

    public function toTwilio(object $notifiable)
    {

        $loginCode = rand(1000, 9999);

        $notifiable->update([
            'login_code' => $loginCode
        ])

        return (new TwilioSmsMessage())
            ->content("Your login code is: $loginCode, Don't share this code with anyone.");
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
