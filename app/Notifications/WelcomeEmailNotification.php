<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * WelcomeEmailNotification
 *
 * This notification sends a welcome email to newly registered users.
 * It implements ShouldQueue to ensure emails are sent asynchronously
 * without blocking the registration API response.
 */
class WelcomeEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Constructor can be used to pass additional data if needed
    }

    /**
     * Get the notification's delivery channels.
     *
     * Specifies that this notification should be delivered via email.
     *
     * @param mixed $notifiable - The entity that is being notified (User)
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * Builds and returns the email message that will be sent to the user.
     * Uses Gmail SMTP to send the email as configured in .env file.
     *
     * @param mixed $notifiable - The entity that is being notified (User)
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Welcome to our platform! We are excited to have you on board.')
            ->line('Thank you for registering with us. Your account has been successfully created.')
            ->line('You can now start exploring all the features we have to offer.')
            ->action('Get Started', url('/'))
            ->line('If you have any questions, feel free to reach out to our support team.')
            ->salutation('Best regards, ' . config('app.name') . ' Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * This method can be used for database notifications if needed.
     *
     * @param mixed $notifiable - The entity that is being notified (User)
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'message' => 'Welcome email sent to ' . $notifiable->email,
        ];
    }
}
