<?php

namespace VictorYoalli\MultitenancyImpersonate\Tests\Models;

use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use VictorYoalli\MultitenancyImpersonate\Tests\TestCase;

class ImpersonateTokenTest extends TestCase
{
    #[Test]
    public function it_can_create_a_token(): void
    {
        $token = TestableImpersonateToken::create([
            'token' => Str::uuid(),
            'impersonator_id' => 1,
            'redirect_url' => 'https://example.com/home',
            'expired_at' => now()->addMinutes(5),
            'auth_guard' => 'web',
        ]);

        $this->assertDatabaseHas('impersonate_tokens', [
            'id' => $token->id,
            'impersonator_id' => 1,
        ]);
    }

    #[Test]
    public function it_filters_live_tokens(): void
    {
        $liveToken = TestableImpersonateToken::create([
            'token' => Str::uuid(),
            'impersonator_id' => 1,
            'redirect_url' => 'https://example.com/home',
            'expired_at' => now()->addMinutes(5),
            'auth_guard' => 'web',
        ]);

        TestableImpersonateToken::create([
            'token' => Str::uuid(),
            'impersonator_id' => 2,
            'redirect_url' => 'https://example.com/home',
            'expired_at' => now()->subMinutes(5),
            'auth_guard' => 'web',
        ]);

        TestableImpersonateToken::create([
            'token' => Str::uuid(),
            'impersonator_id' => 3,
            'redirect_url' => 'https://example.com/home',
            'expired_at' => now()->addMinutes(5),
            'auth_guard' => 'web',
            'impersonated_at' => now(),
        ]);

        $liveTokens = TestableImpersonateToken::live()->get();

        $this->assertCount(1, $liveTokens);
        $this->assertEquals($liveToken->id, $liveTokens->first()->id);
    }

    #[Test]
    public function it_excludes_expired_tokens_from_live_scope(): void
    {
        TestableImpersonateToken::create([
            'token' => Str::uuid(),
            'impersonator_id' => 1,
            'redirect_url' => 'https://example.com/home',
            'expired_at' => now()->subSeconds(1),
            'auth_guard' => 'web',
        ]);

        $this->assertCount(0, TestableImpersonateToken::live()->get());
    }

    #[Test]
    public function it_excludes_used_tokens_from_live_scope(): void
    {
        TestableImpersonateToken::create([
            'token' => Str::uuid(),
            'impersonator_id' => 1,
            'redirect_url' => 'https://example.com/home',
            'expired_at' => now()->addMinutes(5),
            'auth_guard' => 'web',
            'impersonated_at' => now(),
        ]);

        $this->assertCount(0, TestableImpersonateToken::live()->get());
    }

    #[Test]
    public function mark_as_used_marks_token_as_used(): void
    {
        $token = TestableImpersonateToken::create([
            'token' => Str::uuid(),
            'impersonator_id' => 1,
            'redirect_url' => 'https://example.com/home',
            'expired_at' => now()->addMinutes(5),
            'auth_guard' => 'web',
        ]);

        $this->assertNull($token->impersonated_at);

        $token->markAsUsed();

        $this->assertNotNull($token->fresh()->impersonated_at);
        $this->assertNotNull($token->fresh()->ip_address);
    }
}
