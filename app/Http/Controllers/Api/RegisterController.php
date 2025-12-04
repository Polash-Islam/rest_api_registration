<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\WelcomeEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /**
     * Register a new user and send a welcome email
     *
     * This endpoint handles user registration by validating the input,
     * creating a new user record in the database, and dispatching a
     * queued notification to send a welcome email asynchronously.
     *
     * @param Request $request - The HTTP request containing user registration data
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            // Validate the incoming request data
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            // If validation fails, return error response
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create a new user in the database
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Dispatch the welcome email notification to the queue
            // This ensures that email sending doesn't block the registration response
            $user->notify(new WelcomeEmailNotification());

            // Return success response with user data
            return response()->json([
                'success' => true,
                'message' => 'User registered successfully. A welcome email has been sent.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'created_at' => $user->created_at,
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
