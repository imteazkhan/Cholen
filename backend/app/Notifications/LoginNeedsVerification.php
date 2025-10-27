<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\TwilioSmsMessage;
use App\Notifications\TwilioChannel;
use Illuminate\Support\Facades\Log; // 👈 লগিংয়ের জন্য ইম্পোর্ট করা হয়েছে

class LoginNeedsVerification extends Notification
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
    public function via($notifiable): array
    {
        // 👇 পরিবর্তন ১: ডেভেলপমেন্টে SMS না পাঠিয়ে শুধু লগ করবে
        if (app()->environment('local', 'development')) {
            // ডেভেলপমেন্টে শুধু লগ করবে, কিন্তু কোড সেভ করবে
            $this->generateAndSaveCode($notifiable);
            Log::info("Verification code for {$notifiable->phone}: {$notifiable->login_code}");
            return []; // কোনো চ্যানেল নয় → SMS পাঠাবে না
        }

        // প্রোডাকশনে Twilio ব্যবহার করবে
        return [TwilioChannel::class];
    }

    /**
     * Generate and save a secure 4-digit login code to the user model.
     *
     * 👇 পরিবর্তন ২: কোড জেনারেশন আলাদা মেথডে রাখা হয়েছে (DRY principle)
     */
    protected function generateAndSaveCode($notifiable)
    {
        // 👇 পরিবর্তন ৩: rand() এর বদলে random_int() ব্যবহার (more secure)
        $loginCode = random_int(1000, 9999);

        $notifiable->update([
            'login_code' => $loginCode
        ]);
    }

    public function toTwilio($notifiable)
    {
        // 👇 পরিবর্তন ৪: প্রোডাকশনে Twilio-এর আগে কোড সেভ করা হবে
        $this->generateAndSaveCode($notifiable);

        return (new TwilioSmsMessage())
            ->content("Your login code is: {$notifiable->login_code}. Don't share this code with anyone.");
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}