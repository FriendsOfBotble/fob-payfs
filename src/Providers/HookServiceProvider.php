<?php

namespace FriendsOfBotble\PayFS\Providers;

use Botble\Ecommerce\Models\Order;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Facades\PaymentMethods;
use FriendsOfBotble\PayFS\Forms\PayFSPaymentMethodForm;
use FriendsOfBotble\PayFS\PayFS;
use FriendsOfBotble\PayFS\Services\Gateways\PayFSPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, [$this, 'registerPayFSMethod'], 2, 2);

        add_filter(PAYMENT_METHODS_SETTINGS_PAGE, [$this, 'addPaymentSettings'], 2);

        add_filter(PAYMENT_FILTER_PAYMENT_INFO_DETAIL, function ($data, $payment) {
            if ($payment->payment_channel == PAYFS_PAYMENT_METHOD_NAME && $payment->metadata) {
                return view('plugins/fob-payfs::detail', compact('payment'));
            }

            return $data;
        }, 20, 2);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == PaymentMethodEnum::class) {
                $values['PAYFS'] = PAYFS_PAYMENT_METHOD_NAME;
            }

            return $values;
        }, 2, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == PAYFS_PAYMENT_METHOD_NAME) {
                $value = 'PayFS';
            }

            return $value;
        }, 2, 2);

        add_filter(PAYMENT_FILTER_AFTER_POST_CHECKOUT, [$this, 'checkoutWithPayFS'], 11, 2);

        add_filter('ecommerce_thank_you_customer_info', function (?string $html, Collection|Order $orders) {
            if (! $orders instanceof Collection) {
                $collection = new Collection();
                $collection->add($orders);
                $orders = $collection;
            }

            $payment = $orders->first()->payment;

            if (
                ! $payment
                || $payment->payment_channel->getValue() !== PAYFS_PAYMENT_METHOD_NAME
            ) {
                return $html;
            }

            $orderAmount = 0;

            foreach ($orders as $item) {
                $orderAmount += $item->amount;
            }

            $chargeId = $payment->charge_id;
            $paymentCurrency = $payment->currency;

            // Convert to VND if payment is not in VND
            $vndAmount = $orderAmount;
            $originalAmount = $orderAmount;
            $originalCurrency = $paymentCurrency;

            if ($paymentCurrency !== 'VND') {
                // Try to get VND currency
                $vndCurrency = get_all_currencies()->firstWhere('title', 'VND');

                if ($vndCurrency) {
                    // Convert from payment currency to VND
                    // Amount in payment currency * VND exchange rate = Amount in VND
                    $vndAmount = $orderAmount * $vndCurrency->exchange_rate;

                    // Round to nearest integer (VND doesn't use decimals)
                    $vndAmount = round($vndAmount);
                }
            }

            $html .= view(
                'plugins/fob-payfs::bank-info',
                [
                    'orderAmount' => $vndAmount,
                    'originalAmount' => $originalAmount,
                    'originalCurrency' => $originalCurrency,
                    'imageUrl' => PayFS::getQRCodeUrl($vndAmount, $chargeId),
                    'bank' => PayFS::getBankById(get_payment_setting('bank', PAYFS_PAYMENT_METHOD_NAME)),
                    'bankAccountNumber' => get_payment_setting('account_number', PAYFS_PAYMENT_METHOD_NAME),
                    'bankAccountHolder' => get_payment_setting('account_holder', PAYFS_PAYMENT_METHOD_NAME),
                    'chargeId' => $chargeId,
                    'payment' => $payment,
                ]
            )->render();

            return $html;
        }, 9999, 2);
    }

    public function registerPayFSMethod(?string $html, array $data): ?string
    {
        // Support old versions
        if (! view()->exists('plugins/payment::components.payment-method')) {
            return $html . view('plugins/fob-payfs::support-old-versions.payment-method', $data)->render();
        }

        PaymentMethods::method(PAYFS_PAYMENT_METHOD_NAME, [
            'html' => view('plugins/fob-payfs::payments.methods', $data)->render(),
        ]);

        return $html;
    }

    public function addPaymentSettings(?string $settings): string
    {
        return $settings . PayFSPaymentMethodForm::create()->renderForm();
    }

    public function checkoutWithPayFS(array $data, Request $request): array
    {
        if ($data['type'] !== PAYFS_PAYMENT_METHOD_NAME) {
            return $data;
        }

        $paymentData = apply_filters(PAYMENT_FILTER_PAYMENT_DATA, [], $request);

        $data['charge_id'] = (new PayFSPaymentService())->execute($paymentData);

        return $data;
    }
}
