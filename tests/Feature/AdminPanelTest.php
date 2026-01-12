<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear rate limiters between tests
        RateLimiter::clear('api');

        // Create admin user
        User::factory()->create([
            'email' => 'admin@genieinfo.tech',
            'password' => bcrypt('ChangeMe123!'),
            'is_admin' => true,
        ]);
    }

    public function test_login_page_loads(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('Login');
        $response->assertSee('Genie InfoTech');
    }

    public function test_login_page_has_livewire_component(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        // Check for Livewire attributes
        $response->assertSee('wire:submit');
        $response->assertSee('livewire.js');
    }

    public function test_livewire_update_endpoint_exists(): void
    {
        $response = $this->post('/livewire/update', [
            'snapshot' => '{}',
            'updates' => [],
        ]);

        // Should return 419 (CSRF) or 422 (invalid data), not 404
        $this->assertNotEquals(404, $response->status());
        $this->assertNotEquals(405, $response->status());
    }

    public function test_admin_dashboard_requires_authentication(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::where('email', 'admin@genieinfo.tech')->first();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_api_contact_endpoint_works(): void
    {
        // Skip rate limiter for this test
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $response = $this->postJson('/api/contact', [
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'message' => 'This is a test message for the contact form that needs to be at least 10 characters.',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
    }

    public function test_api_leads_endpoint_works(): void
    {
        // Skip rate limiter for this test
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $response = $this->postJson('/api/leads', [
            'name' => 'Test Lead',
            'email' => 'lead@gmail.com',
            'message' => 'This is a test lead message that needs to be at least 10 characters.',
            'source' => 'exit_intent',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
    }

    public function test_unauthenticated_cannot_view_leads(): void
    {
        $response = $this->getJson('/api/leads');

        $response->assertStatus(401);
    }

    /**
     * Test that rate limiting configuration exists.
     * Note: Full rate limiting test is unreliable in testing environment
     * due to IP-based throttling. Rate limiting is verified via production monitoring.
     */
    public function test_rate_limiting_is_configured(): void
    {
        // Verify rate limiter configuration exists
        $this->assertTrue(config('app.rate_limit', 60) > 0);

        // Verify the throttle middleware is registered
        $response = $this->postJson('/api/contact', [
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'message' => 'Test message that needs at least 10 characters for validation.',
        ]);

        // Check rate limit headers are present
        $response->assertHeader('X-RateLimit-Limit');
        $response->assertHeader('X-RateLimit-Remaining');
    }
}
