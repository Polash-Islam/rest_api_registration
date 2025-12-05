<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GmailApiService;
use Illuminate\Http\Request;

/**
 * Gmail OAuth2 Authentication Controller
 *
 * Handles OAuth2 authorization flow for Gmail API
 * Allows application to obtain refresh token for sending emails
 */
class GmailAuthController extends Controller
{
    /**
     * @var GmailApiService Gmail API service instance
     */
    protected $gmailService;

    /**
     * Initialize controller with Gmail API service
     *
     * @param GmailApiService $gmailService Injected Gmail API service
     */
    public function __construct(GmailApiService $gmailService)
    {
        $this->gmailService = $gmailService;
    }

    /**
     * Redirect to Google OAuth2 authorization page
     *
     * Generates and returns the Google authorization URL
     * User must visit this URL to grant Gmail access
     *
     * @return \Illuminate\Http\JsonResponse Authorization URL in JSON format
     */
    public function redirectToGoogle()
    {
        $authUrl = $this->gmailService->getAuthUrl();

        return response()->json([
            'authorization_url' => $authUrl,
            'message' => 'Visit this URL to authorize Gmail API access'
        ]);
    }

    /**
     * Handle OAuth2 callback from Google
     *
     * Exchanges authorization code for access and refresh tokens
     * Refresh token should be stored in .env for future use
     *
     * @param Request $request HTTP request containing authorization code
     * @return \Illuminate\Http\JsonResponse Token information in JSON format
     */
    public function handleGoogleCallback(Request $request)
    {
        // Validate authorization code is present
        if (!$request->has('code')) {
            return response()->json([
                'error' => 'Authorization code not provided'
            ], 400);
        }

        try {
            // Exchange code for tokens
            $token = $this->gmailService->authenticate($request->code);

            return response()->json([
                'message' => 'Authorization successful',
                'refresh_token' => $token['refresh_token'] ?? null,
                'note' => 'Add the refresh_token to your .env file as GOOGLE_REFRESH_TOKEN'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Authentication failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
