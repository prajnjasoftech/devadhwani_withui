<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\OtpLog;
use App\Models\Temple;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_send_otp_to_phone_number()
    {
        $response = $this->postJson('/api/send-otp', [
            'phone' => '+919876543001',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'OTP sent successfully']);

        $this->assertDatabaseHas('otp_logs', [
            'phone' => '+919876543001',
            'is_verified' => false,
        ]);
    }

    /** @test */
    public function it_requires_phone_number_to_send_otp()
    {
        $response = $this->postJson('/api/send-otp', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    /** @test */
    public function it_can_verify_otp_and_register_new_temple()
    {
        $otp = OtpLog::create([
            'phone' => '+919876543002',
            'otp' => '654321',
            'is_verified' => false,
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/verify-otp', [
            'phone' => '+919876543002',
            'otp' => '654321',
            'temple_name' => 'Test Temple',
            'temple_address' => '123 Temple Street',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'temple' => ['id', 'temple_name', 'temple_address', 'phone'],
                'token',
                'role',
            ]);

        $this->assertDatabaseHas('temples', [
            'phone' => '+919876543002',
            'temple_name' => 'Test Temple',
        ]);
    }

    /** @test */
    public function it_rejects_invalid_otp()
    {
        OtpLog::create([
            'phone' => '+919876543003',
            'otp' => '654321',
            'is_verified' => false,
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/verify-otp', [
            'phone' => '+919876543003',
            'otp' => '000000',
            'temple_name' => 'Test Temple',
            'temple_address' => '123 Temple Street',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => false,
                'error' => 'Invalid or expired OTP',
            ]);
    }

    /** @test */
    public function it_rejects_expired_otp()
    {
        OtpLog::create([
            'phone' => '+919876543004',
            'otp' => '654321',
            'is_verified' => false,
            'expires_at' => now()->subMinutes(10),
        ]);

        $response = $this->postJson('/api/verify-otp', [
            'phone' => '+919876543004',
            'otp' => '654321',
            'temple_name' => 'Test Temple',
            'temple_address' => '123 Temple Street',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_login_existing_temple()
    {
        $temple = Temple::factory()->create([
            'phone' => '+919876543005',
        ]);

        OtpLog::create([
            'phone' => '+919876543005',
            'otp' => '654321',
            'is_verified' => false,
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/login', [
            'phone' => '+919876543005',
            'otp' => '654321',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'temple',
                'token',
                'role',
            ]);
    }

    /** @test */
    public function it_can_login_existing_member()
    {
        $temple = Temple::factory()->create();
        $member = Member::factory()->create([
            'temple_id' => $temple->id,
            'phone' => '+919876543006',
        ]);

        OtpLog::create([
            'phone' => '+919876543006',
            'otp' => '654321',
            'is_verified' => false,
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/login', [
            'phone' => '+919876543006',
            'otp' => '654321',
        ]);

        $response->assertStatus(200)
            ->assertJson(['login_as' => 'member']);
    }

    /** @test */
    public function it_returns_error_for_unregistered_phone()
    {
        OtpLog::create([
            'phone' => '+919999999999',
            'otp' => '654321',
            'is_verified' => false,
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/login', [
            'phone' => '+919999999999',
            'otp' => '654321',
        ]);

        $response->assertStatus(404)
            ->assertJson(['error' => 'No account found for this number']);
    }

    /** @test */
    public function it_can_logout()
    {
        $temple = Temple::factory()->create();
        $token = $temple->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out']);
    }
}
