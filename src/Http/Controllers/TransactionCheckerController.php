<?php

namespace FriendsOfBotble\PayFS\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Http\Controllers\BaseController;
use Botble\Payment\Models\Payment;
use FriendsOfBotble\PayFS\Http\Requests\PaymentStatusRequest;

class TransactionCheckerController extends BaseController
{
    public function __invoke(PaymentStatusRequest $request): BaseHttpResponse
    {
        $payment = Payment::query()
            ->where('charge_id', $request->input('charge_id'))
            ->firstOrFail();

        return $this
            ->httpResponse()
            ->setData([
                'status' => $payment->status,
                'status_html' => $payment->status->toHtml(),
            ]);
    }
}
