@if (setting('payment_payfs_status') == 1)
    <x-plugins-payment::payment-method
        :name="PAYFS_PAYMENT_METHOD_NAME"
        paymentName="PayFS"
    />
@endif
