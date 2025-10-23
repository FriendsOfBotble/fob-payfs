<?php

namespace FriendsOfBotble\PayFS\Http\Middleware;

use Closure;
use FriendsOfBotble\PayFS\Services\PayFSWebhookAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PayFSProtector
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-Client-API-Key');
        $signature = $request->header('X-PayFS-Signature');
        $timestamp = (int) $request->header('X-PayFS-Timestamp', 0);

        $expectedApiKey = get_payment_setting('api_key', PAYFS_PAYMENT_METHOD_NAME);
        $webhookSecret = get_payment_setting('webhook_secret', PAYFS_PAYMENT_METHOD_NAME);

        if (! $expectedApiKey) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Webhook not configured.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $authService = new PayFSWebhookAuthService();
        $result = $authService->authenticate(
            $apiKey,
            $signature,
            $timestamp,
            $request->getContent(),
            $expectedApiKey,
            $webhookSecret
        );

        if (! $result['valid']) {
            return new JsonResponse([
                'success' => false,
                'message' => $result['error'] ?? 'Authentication failed.',
                'stage' => $result['stage'] ?? 'unknown',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
