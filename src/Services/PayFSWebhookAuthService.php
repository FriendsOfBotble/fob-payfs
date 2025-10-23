<?php

namespace FriendsOfBotble\PayFS\Services;

class PayFSWebhookAuthService
{
    /**
     * Authenticate webhook request using API key and optional signature verification
     *
     * @param string|null $receivedApiKey
     * @param string|null $signature
     * @param int $timestamp
     * @param string $payload
     * @param string $expectedApiKey
     * @param string|null $webhookSecret
     * @return array
     */
    public function authenticate(
        ?string $receivedApiKey,
        ?string $signature,
        int $timestamp,
        string $payload,
        string $expectedApiKey,
        ?string $webhookSecret = null
    ): array {
        // Validate API Key (Required)
        $apiKeyResult = $this->validateApiKey($receivedApiKey, $expectedApiKey);
        if (! $apiKeyResult['valid']) {
            return [
                'valid' => false,
                'error' => 'API Key validation failed',
                'stage' => 'api_key',
            ];
        }

        // Signature verification (Optional but recommended)
        if ($signature && $webhookSecret) {
            $signatureResult = $this->verifySignature(
                $payload,
                $signature,
                $timestamp,
                $webhookSecret
            );

            if (! $signatureResult['valid']) {
                return [
                    'valid' => false,
                    'error' => $signatureResult['error'] ?? 'Signature verification failed',
                    'stage' => 'signature',
                ];
            }

            return [
                'valid' => true,
                'apiKeyValid' => true,
                'signatureValid' => true,
            ];
        }

        return [
            'valid' => true,
            'apiKeyValid' => true,
            'signatureValid' => false,
        ];
    }

    /**
     * Validate API key using constant-time comparison
     *
     * @param string|null $receivedKey
     * @param string $expectedKey
     * @return array
     */
    protected function validateApiKey(?string $receivedKey, string $expectedKey): array
    {
        if (! $receivedKey) {
            return [
                'valid' => false,
                'error' => 'Missing X-Client-API-Key header',
            ];
        }

        if (! hash_equals($expectedKey, $receivedKey)) {
            return [
                'valid' => false,
                'error' => 'Invalid API key',
            ];
        }

        return ['valid' => true];
    }

    /**
     * Verify webhook signature using HMAC-SHA256
     *
     * @param string $payload
     * @param string $signature
     * @param int $timestamp
     * @param string $webhookSecret
     * @return array
     */
    protected function verifySignature(
        string $payload,
        string $signature,
        int $timestamp,
        string $webhookSecret
    ): array {
        try {
            // Validate timestamp (5-minute window)
            $currentTime = time();
            if ($currentTime - $timestamp > 300) {
                return [
                    'valid' => false,
                    'error' => 'Signature timestamp too old (>5 minutes)',
                ];
            }

            // Parse and sort payload
            $parsedPayload = json_decode($payload, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'valid' => false,
                    'error' => 'Invalid JSON payload',
                ];
            }

            $sortedPayload = json_encode(
                $this->sortObjectKeys($parsedPayload),
                JSON_UNESCAPED_SLASHES
            );

            // Create signature data: timestamp.sortedPayload
            $data = $timestamp . '.' . $sortedPayload;

            // Compute expected signature
            $expectedSignature = hash_hmac('sha256', $data, $webhookSecret);

            // Constant-time comparison to prevent timing attacks
            if (hash_equals($expectedSignature, $signature)) {
                return ['valid' => true];
            }

            return [
                'valid' => false,
                'error' => 'Invalid signature',
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => 'Signature verification failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Recursively sort object keys alphabetically
     *
     * @param mixed $obj
     * @return mixed
     */
    protected function sortObjectKeys($obj)
    {
        if (! is_array($obj)) {
            return $obj;
        }

        // Check if it's an indexed array (not associative)
        if (! $this->isAssociativeArray($obj)) {
            return array_map([$this, 'sortObjectKeys'], $obj);
        }

        // Sort associative array by keys
        $sorted = [];
        ksort($obj);

        foreach ($obj as $key => $value) {
            $sorted[$key] = $this->sortObjectKeys($value);
        }

        return $sorted;
    }

    /**
     * Check if array is associative
     *
     * @param array $array
     * @return bool
     */
    protected function isAssociativeArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }
}
