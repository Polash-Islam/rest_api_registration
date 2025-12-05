<?php

namespace App\Services;

use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;

/**
 * Gmail API Service
 *
 * Handles Gmail API OAuth2 authentication and email sending
 * Uses Google Client credentials to send emails through Gmail API
 */
class GmailApiService
{
    /**
     * @var Client Google API Client instance
     */
    protected $client;

    /**
     * @var Gmail Gmail service instance
     */
    protected $service;

    /**
     * Initialize Gmail API client with OAuth2 credentials
     *
     * Sets up Google Client with application credentials from environment variables
     * Configures OAuth2 access type and required Gmail API scopes
     */
    public function __construct()
    {
        $this->client = new Client();
        $this->client->setApplicationName('Laravel REST API Registration');
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect_uri'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->setScopes([Gmail::GMAIL_SEND]);

        // Set refresh token if available
        if ($refreshToken = config('services.google.refresh_token')) {
            $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
        }

        $this->service = new Gmail($this->client);
    }

    /**
     * Send email through Gmail API
     *
     * Constructs and sends an email message using Gmail API
     * Message is formatted in RFC 2822 format and base64url encoded
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject line
     * @param string $messageBody HTML or plain text email body
     * @return \Google\Service\Gmail\Message Sent message response from Gmail API
     * @throws \Google\Service\Exception If API request fails
     */
    public function sendEmail($to, $subject, $messageBody)
    {
        // Construct RFC 2822 formatted email
        $emailContent = "To: {$to}\r\n";
        $emailContent .= "Subject: {$subject}\r\n";
        $emailContent .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
        $emailContent .= $messageBody;

        // Create Gmail message object with base64url encoded content
        $message = new Message();
        $message->setRaw($this->base64UrlEncode($emailContent));

        // Send email through Gmail API using authenticated user (me)
        return $this->service->users_messages->send('me', $message);
    }

    /**
     * Encode string in base64url format
     *
     * Gmail API requires message content to be base64url encoded
     * This is different from standard base64 encoding
     *
     * @param string $data String to encode
     * @return string Base64url encoded string
     */
    protected function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Get authorization URL for OAuth2 flow
     *
     * Generates the Google OAuth2 authorization URL
     * User must visit this URL to grant application access to Gmail
     *
     * @return string Google OAuth2 authorization URL
     */
    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Exchange authorization code for access token
     *
     * After user authorizes the application, exchange the code for tokens
     * Stores the refresh token for future API requests
     *
     * @param string $code Authorization code from OAuth2 callback
     * @return array Token information including access_token and refresh_token
     */
    public function authenticate($code)
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);
        $this->client->setAccessToken($token);
        return $token;
    }
}
