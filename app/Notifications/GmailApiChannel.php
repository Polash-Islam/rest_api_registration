<?php

namespace App\Notifications;

use App\Services\GmailApiService;
use Illuminate\Notifications\Notification;

/**
 * Gmail API Notification Channel
 *
 * Custom notification channel for sending emails through Gmail API
 * Uses OAuth2 authentication 
 */
class GmailApiChannel
{
    /**
     * @var GmailApiService Gmail API service instance
     */
    protected $gmailService;

    /**
     * Initialize channel with Gmail API service
     *
     * @param GmailApiService $gmailService Injected Gmail service
     */
    public function __construct(GmailApiService $gmailService)
    {
        $this->gmailService = $gmailService;
    }

    /**
     * Send the notification using Gmail API
     *
     * This method is called by Laravel's notification system
     * Extracts email data from notification and sends via Gmail API
     *
     * @param mixed $notifiable Entity being notified (User model)
     * @param Notification $notification Notification instance being sent
     * @return void
     * @throws \Google\Service\Exception If Gmail API request fails
     */
    public function send($notifiable, Notification $notification)
    {
        // Get email data from notification
        $emailData = $notification->toGmailApi($notifiable);

        // Send email through Gmail API using OAuth2 credentials
        $this->gmailService->sendEmail(
            $emailData['to'],
            $emailData['subject'],
            $emailData['body']
        );
    }
}
