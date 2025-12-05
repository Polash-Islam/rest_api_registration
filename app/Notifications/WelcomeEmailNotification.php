<?php

namespace App\Notifications;

use App\Notifications\GmailApiChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * WelcomeEmailNotification
 *
 * This notification sends a welcome email to newly registered users using Gmail API.
 * It implements ShouldQueue to ensure emails are sent asynchronously
 * without blocking the registration API response.
 * Uses Gmail API OAuth2 with Client ID and Client Secret.
 */
class WelcomeEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * Validates that Google Client credentials are configured
     *
     * @return void
     * @throws \Exception if Gmail API credentials are not configured
     */
    public function __construct()
    {
        // Validate Gmail API credentials are configured
        if (empty(config('services.google.client_id')) || empty(config('services.google.client_secret'))) {
            throw new \Exception('Gmail API credentials (Client ID and Client Secret) must be configured');
        }

        // Validate refresh token is configured
        if (empty(config('services.google.refresh_token'))) {
            throw new \Exception('Gmail API refresh token must be configured. Run: GET /api/gmail/auth to authorize');
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * Uses custom Gmail API channel for OAuth2 authentication
     *
     * @param mixed $notifiable - The entity that is being notified (User)
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return [GmailApiChannel::class];
    }

    /**
     * Send email using Gmail API
     *
     * Builds HTML email content and sends it through Gmail API using OAuth2
     * This method is called by the custom Gmail API notification channel
     *
     * @param mixed $notifiable - The entity that is being notified (User)
     * @return array Email data for Gmail API
     */
    public function toGmailApi($notifiable): array
    {
        $appName = config('app.name');
        $homeUrl = url('/');

        // Build HTML email content
        $htmlContent = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #333;'>Hello {$notifiable->name}!</h2>
                <p style='color: #666; line-height: 1.6;'>
                    Welcome to our platform! We are excited to have you on board.
                </p>
                <p style='color: #666; line-height: 1.6;'>
                    Thank you for registering with us. Your account has been successfully created.
                </p>
                <p style='color: #666; line-height: 1.6;'>
                    You can now start exploring all the features we have to offer.
                </p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$homeUrl}' style='background-color: #4CAF50; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; display: inline-block;'>
                        Get Started
                    </a>
                </div>
                <p style='color: #666; line-height: 1.6;'>
                    If you have any questions, feel free to reach out to our support team.
                </p>
                <p style='color: #666; margin-top: 30px;'>
                    Best regards,<br>
                    {$appName} Team
                </p>
            </div>
        ";

        return [
            'to' => $notifiable->email,
            'subject' => "Welcome to {$appName}",
            'body' => $htmlContent,
        ];
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
