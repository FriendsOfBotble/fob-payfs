<?php

namespace FriendsOfBotble\PayFS\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Http\Controllers\BaseController;
use Botble\Payment\Models\Payment;
use Exception;
use FriendsOfBotble\PayFS\Http\Requests\PaymentStatusRequest;

class TransactionCheckerController extends BaseController
{
    public function __invoke(PaymentStatusRequest $request): BaseHttpResponse
    {
        try {
            $payment = Payment::query()
                ->where('charge_id', $request->input('charge_id'))
                ->first();

            if (! $payment) {
                return $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage('Payment not found');
            }

            return $this
                ->httpResponse()
                ->setData([
                    'status' => $payment->status,
                    'status_html' => $payment->status->toHtml(),
                ]);
        } catch (Exception $e) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage($e->getMessage());
        }
    }
}
