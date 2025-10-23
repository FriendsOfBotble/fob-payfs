<?php

namespace FriendsOfBotble\PayFS\Forms;

use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Payment\Forms\PaymentMethodForm;
use FriendsOfBotble\PayFS\Http\Requests\PaymentRequest;
use FriendsOfBotble\PayFS\PayFS;

class PayFSPaymentMethodForm extends PaymentMethodForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setValidatorClass(PaymentRequest::class)
            ->paymentId(PAYFS_PAYMENT_METHOD_NAME)
            ->paymentName('PayFS - Thanh toán chuyển khoản qua ngân hàng QR code')
            ->paymentDescription('Giải pháp quản lý biến động số dư thông minh cho doanh nghiệp Việt Nam. Real-time tracking, webhook API, tích hợp 15+ ngân hàng.')
            ->paymentLogo(url('vendor/core/plugins/fob-payfs/images/payfs.png'))
            ->paymentUrl('https://payfs.vn')
            ->add(
                get_payment_setting_key('bank', PAYFS_PAYMENT_METHOD_NAME),
                SelectField::class,
                SelectFieldOption::make()
                    ->searchable()
                    ->choices(PayFS::getBanksList())
                    ->selected(get_payment_setting('bank', PAYFS_PAYMENT_METHOD_NAME))
                    ->label('Ngân hàng')
                    ->toArray()
            )
            ->add(
                get_payment_setting_key('account_number', PAYFS_PAYMENT_METHOD_NAME),
                TextField::class,
                TextFieldOption::make()
                    ->label('Số tài khoản')
                    ->value(get_payment_setting('account_number', PAYFS_PAYMENT_METHOD_NAME))
                    ->toArray()
            )
            ->add(
                get_payment_setting_key('account_holder', PAYFS_PAYMENT_METHOD_NAME),
                TextField::class,
                TextFieldOption::make()
                    ->label('Chủ tài khoản')
                    ->value(get_payment_setting('account_holder', PAYFS_PAYMENT_METHOD_NAME))
                    ->toArray()
            )
            ->add(
                get_payment_setting_key('prefix', PAYFS_PAYMENT_METHOD_NAME),
                TextField::class,
                TextFieldOption::make()
                    ->value(get_payment_setting('prefix', PAYFS_PAYMENT_METHOD_NAME, 'SDH'))
                    ->label('Tiền tố mã thanh toán')
                    ->helperText('Chỉ được phép chứa chữ cái và số, không dấu và không khoảng trắng. Ví dụ: SDH')
            )
            ->add(
                get_payment_setting_key('api_key', PAYFS_PAYMENT_METHOD_NAME),
                TextField::class,
                TextFieldOption::make()
                    ->label('API Key')
                    ->value(get_payment_setting('api_key', PAYFS_PAYMENT_METHOD_NAME))
                    ->helperText('API Key của PayFS (X-Client-API-Key). Lấy từ PayFS dashboard.')
                    ->toArray()
            )
            ->add(
                get_payment_setting_key('webhook_secret', PAYFS_PAYMENT_METHOD_NAME),
                TextField::class,
                TextFieldOption::make()
                    ->label('Webhook Secret')
                    ->value(get_payment_setting('webhook_secret', PAYFS_PAYMENT_METHOD_NAME))
                    ->helperText('Webhook secret của PayFS (tùy chọn, dùng để xác thực chữ ký webhook). Lấy từ PayFS dashboard.')
                    ->toArray()
            )
            ->add(
                'payfs_webhook_info',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->view('plugins/fob-payfs::webhook-info')
            )
        ;
    }
}
