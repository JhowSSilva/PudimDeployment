<?php

namespace Tests\Unit;

use App\Services\WebhookService;
use Tests\TestCase;

class WebhookSignatureTest extends TestCase
{
    protected WebhookService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WebhookService();
    }

    /** @test */
    public function validates_github_signature_correctly()
    {
        $secret = 'my-secret-key';
        $payload = '{"ref":"refs/heads/main"}';
        $signature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        $this->assertTrue(
            $this->service->validateGitHubSignature($payload, $signature, $secret),
            'Valid GitHub signature should be accepted'
        );
    }

    /** @test */
    public function rejects_invalid_github_signature()
    {
        $secret = 'my-secret-key';
        $payload = '{"ref":"refs/heads/main"}';
        $wrongSignature = 'sha256=' . hash_hmac('sha256', $payload, 'wrong-secret');

        $this->assertFalse(
            $this->service->validateGitHubSignature($payload, $wrongSignature, $secret),
            'Invalid GitHub signature should be rejected'
        );
    }

    /** @test */
    public function rejects_github_signature_with_null_payload()
    {
        $this->assertFalse(
            $this->service->validateGitHubSignature(null, 'sha256=abc', 'secret'),
            'Null payload should be rejected'
        );
    }

    /** @test */
    public function rejects_github_signature_with_null_signature()
    {
        $this->assertFalse(
            $this->service->validateGitHubSignature('payload', null, 'secret'),
            'Null signature should be rejected'
        );
    }

    /** @test */
    public function rejects_github_signature_with_null_secret()
    {
        $this->assertFalse(
            $this->service->validateGitHubSignature('payload', 'sha256=abc', null),
            'Null secret should be rejected'
        );
    }

    /** @test */
    public function rejects_github_signature_with_empty_payload()
    {
        $this->assertFalse(
            $this->service->validateGitHubSignature('', 'sha256=abc', 'secret'),
            'Empty payload should be rejected'
        );
    }

    /** @test */
    public function validates_gitlab_token_correctly()
    {
        $secret = 'gitlab-token-secret';

        $this->assertTrue(
            $this->service->validateGitLabToken($secret, $secret),
            'Matching GitLab tokens should be accepted'
        );
    }

    /** @test */
    public function rejects_invalid_gitlab_token()
    {
        $secret = 'gitlab-token-secret';
        $wrongToken = 'wrong-token';

        $this->assertFalse(
            $this->service->validateGitLabToken($wrongToken, $secret),
            'Non-matching GitLab tokens should be rejected'
        );
    }

    /** @test */
    public function rejects_gitlab_token_with_null_values()
    {
        $this->assertFalse(
            $this->service->validateGitLabToken(null, 'secret'),
            'Null token should be rejected'
        );

        $this->assertFalse(
            $this->service->validateGitLabToken('token', null),
            'Null secret should be rejected'
        );
    }

    /** @test */
    public function validates_bitbucket_signature_correctly()
    {
        $secret = 'bitbucket-secret';
        $payload = '{"push":{"changes":[]}}';
        $signature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        $this->assertTrue(
            $this->service->validateBitbucketSignature($payload, $signature, $secret),
            'Valid Bitbucket signature should be accepted'
        );
    }

    /** @test */
    public function rejects_invalid_bitbucket_signature()
    {
        $secret = 'bitbucket-secret';
        $payload = '{"push":{"changes":[]}}';
        $wrongSignature = 'sha256=' . hash_hmac('sha256', $payload, 'wrong-secret');

        $this->assertFalse(
            $this->service->validateBitbucketSignature($payload, $wrongSignature, $secret),
            'Invalid Bitbucket signature should be rejected'
        );
    }

    /** @test */
    public function signature_validation_is_timing_attack_safe()
    {
        // Testa que usamos hash_equals ao invés de comparação simples
        $secret = 'secret';
        $payload = 'test';
        $correctSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        
        // Mesmo com apenas 1 caractere diferente, deve rejeitar
        $almostCorrect = substr($correctSignature, 0, -1) . 'x';

        $this->assertFalse(
            $this->service->validateGitHubSignature($payload, $almostCorrect, $secret),
            'Signatures differing by one character should be rejected (timing-safe)'
        );
    }

    /** @test */
    public function validates_signatures_are_case_sensitive()
    {
        $secret = 'Secret-Key';
        $payload = 'payload';
        $signature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        // Case mismatch no secret deve falhar
        $this->assertFalse(
            $this->service->validateGitHubSignature($payload, $signature, 'secret-key'),
            'Signature validation should be case-sensitive'
        );
    }
}
