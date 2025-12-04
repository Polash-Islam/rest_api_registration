<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\WelcomeEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Registration API Feature Tests
 *
 * This test suite validates the user registration functionality including:
 * - Successful registration with valid data
 * - Validation error handling
 * - Email notification queuing
 * - Database integrity
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a user can successfully register with valid data
     *
     * This test verifies:
     * - API returns 201 status code
     * - Response contains success message and user data
     * - User is saved to database
     * - Password is hashed
     *
     * @return void
     */
    public function test_user_can_register_with_valid_data(): void
    {
        // Fake the queue to prevent actual email sending
        Queue::fake();

        // Prepare valid registration data
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        // Send POST request to registration endpoint
        $response = $this->postJson('/api/register', $userData);

        // Assert response status and structure
        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'User registered successfully. A welcome email has been sent.'
                 ])
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'user' => [
                             'id',
                             'name',
                             'email',
                             'created_at'
                         ]
                     ]
                 ]);

        // Assert user exists in database
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        // Assert password is hashed (not plain text)
        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotEquals('password123', $user->password);
    }

    /**
     * Test that registration fails when email already exists
     *
     * This test ensures duplicate email validation works correctly
     *
     * @return void
     */
    public function test_registration_fails_with_duplicate_email(): void
    {
        // Create an existing user
        User::factory()->create([
            'email' => 'existing@example.com'
        ]);

        // Try to register with same email
        $response = $this->postJson('/api/register', [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        // Assert validation error response
        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Validation failed'
                 ])
                 ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test that registration fails with invalid email format
     *
     * @return void
     */
    public function test_registration_fails_with_invalid_email(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test that registration fails with short password
     *
     * Password must be at least 8 characters
     *
     * @return void
     */
    public function test_registration_fails_with_short_password(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123',
            'password_confirmation' => '123'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test that registration fails when password confirmation doesn't match
     *
     * @return void
     */
    public function test_registration_fails_with_password_mismatch(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test that registration fails when required fields are missing
     *
     * @return void
     */
    public function test_registration_fails_with_missing_fields(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * Test that registration fails when name is too long
     *
     * Name must not exceed 255 characters
     *
     * @return void
     */
    public function test_registration_fails_with_long_name(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => str_repeat('a', 256), // 256 characters
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test that welcome email notification is queued after registration
     *
     * This ensures email sending doesn't block the registration process
     *
     * @return void
     */
    public function test_welcome_email_is_queued_after_registration(): void
    {
        // Fake the notification instead of queue
        Notification::fake();

        // Register a user
        $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        // Get the created user
        $user = User::where('email', 'test@example.com')->first();

        // Assert that the welcome email notification was sent and queued
        Notification::assertSentTo(
            $user,
            WelcomeEmailNotification::class
        );
    }    /**
     * Test that notification is sent to the correct user
     *
     * @return void
     */
    public function test_notification_is_sent_to_correct_user(): void
    {
        // Fake notifications
        Notification::fake();

        // Register a user
        $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        // Get the created user
        $user = User::where('email', 'test@example.com')->first();

        // Assert notification was sent to the user
        Notification::assertSentTo(
            $user,
            WelcomeEmailNotification::class
        );
    }

    /**
     * Test that API returns proper JSON structure for validation errors
     *
     * @return void
     */
    public function test_validation_errors_have_correct_structure(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123'
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'errors' => [
                         'name',
                         'email',
                         'password'
                     ]
                 ]);
    }

    /**
     * Test that user count increases after successful registration
     *
     * @return void
     */
    public function test_user_count_increases_after_registration(): void
    {
        Queue::fake();

        // Get initial user count
        $initialCount = User::count();

        // Register a user
        $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        // Assert user count increased by 1
        $this->assertEquals($initialCount + 1, User::count());
    }

    /**
     * Test that registration with special characters in name works
     *
     * @return void
     */
    public function test_registration_works_with_special_characters_in_name(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/register', [
            'name' => "O'Brien-Smith",
            'email' => 'obrien@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'name' => "O'Brien-Smith",
            'email' => 'obrien@example.com'
        ]);
    }
}
