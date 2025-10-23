<?php

namespace FriendsOfBotble\PayFS\Http\Controllers;

use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use FriendsOfBotble\PayFS\PayFS;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController
{
    public function __invoke(Request $request): Response
    {
        // Validate PayFS webhook payload structure
        // Required fields: transaction_id, amount, transfer_type, content, transaction_date
        if (
            ! $request->filled('transaction_id')
            || ! $request->filled('amount')
            || ! $request->filled('transfer_type')
            || ! $request->filled('content')
            || ! $request->filled('transaction_date')
        ) {
            Log::warning('PayFS webhook: Invalid payload structure', [
                'payload' => $request->all(),
            ]);

            return response('invalid payload.', 400);
        }

        // Only process credit (incoming) transactions
        if ($request->input('transfer_type') !== 'credit') {
            Log::info('PayFS webhook: Skipping non-credit transaction', [
                'transfer_type' => $request->input('transfer_type'),
                'transaction_id' => $request->input('transaction_id'),
            ]);

            return response('ok');
        }

        $transactionContent = $request->input('content');
        $transactionAmount = (float) $request->input('amount');
        $transactionId = $request->input('transaction_id');

        // Extract charge ID from transaction content
        $chargeId = PayFS::getChargeIdFrom($transactionContent);

        if (! $chargeId || ! is_string($chargeId)) {
            Log::warning('PayFS webhook: Charge ID not found in transaction content', [
                'content' => $transactionContent,
                'transaction_id' => $transactionId,
            ]);

            return response('invalid payload.', 400);
        }

        // Find payment by charge ID
        $payment = Payment::query()->where('charge_id', $chargeId)->first();

        if (! $payment) {
            Log::warning('PayFS webhook: Payment not found', [
                'charge_id' => $chargeId,
                'transaction_id' => $transactionId,
            ]);

            return response('payment not found.', 400);
        }

        // Validate payment channel
        if ($payment->payment_channel->getValue() !== PAYFS_PAYMENT_METHOD_NAME) {
            Log::warning('PayFS webhook: Invalid payment channel', [
                'charge_id' => $chargeId,
                'expected' => PAYFS_PAYMENT_METHOD_NAME,
                'actual' => $payment->payment_channel->getValue(),
            ]);

            return response('invalid payment channel.', 400);
        }

        // Convert payment amount to VND if needed (PayFS transactions are in VND)
        $expectedAmount = $payment->amount;

        if ($payment->currency !== 'VND') {
            $vndCurrency = get_all_currencies()->firstWhere('title', 'VND');

            if ($vndCurrency) {
                // Convert payment amount to VND
                $expectedAmount = round($payment->amount * $vndCurrency->exchange_rate);
            }
        }

        // Validate transaction amount (allow some tolerance for rounding)
        $tolerance = 1000; // Allow 1000 VND difference for rounding

        if ($transactionAmount < ($expectedAmount - $tolerance)) {
            Log::warning('PayFS webhook: Insufficient amount', [
                'charge_id' => $chargeId,
                'expected_vnd' => $expectedAmount,
                'received_vnd' => $transactionAmount,
                'original_amount' => $payment->amount,
                'original_currency' => $payment->currency,
            ]);

            return response('insufficient amount.', 400);
        }

        do_action('payment_before_making_api_request', PAYFS_PAYMENT_METHOD_NAME, []);

        // Check if payment is already completed (idempotency)
        if ($payment->status == PaymentStatusEnum::COMPLETED) {
            Log::info('PayFS webhook: Payment already completed', [
                'charge_id' => $chargeId,
                'transaction_id' => $transactionId,
            ]);

            return response('ok');
        }

        // Update payment status
        $payment->status = PaymentStatusEnum::COMPLETED;
        $payment->metadata = $request->all();
        $payment->save();

        do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
            'charge_id' => $payment->charge_id,
            'order_id' => $payment->order_id,
            'status' => PaymentStatusEnum::COMPLETED,
        ]);

        /**
         * @var Order|null $order
         */
        $order = Order::query()->find($payment->order_id);

        if ($order) {
            OrderHelper::confirmOrder($order);

            Log::info('PayFS webhook: Order confirmed', [
                'order_id' => $order->id,
                'charge_id' => $chargeId,
                'transaction_id' => $transactionId,
                'amount' => $transactionAmount,
            ]);
        }

        do_action('payment_after_api_response', PAYFS_PAYMENT_METHOD_NAME, [], $request->all());

        return response('ok');
    }
}
