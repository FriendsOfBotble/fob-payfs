<style>
    .payfs.fob-container {
        margin-top: 2rem;
    }

    .payfs .fob-qr-code {
        text-align: center;
        margin-bottom: 40px;
    }

    .payfs .fob-qr-code img {
        width: 250px;
        height: auto;
        margin: 0;
        padding: 0;
    }

    .payfs .fob-qr-code figcaption {
        margin-top: 10px;
        font-size: 14px;
        color: #666;
    }

    .payfs .fob-qr-intro {
        margin-bottom: 10px;
        font-size: 16px;
    }

    .payfs .transaction-status-done {
        background-color: var(--bs-tertiary-bg);
        border: none;
        color: var(--primary-color);
    }

    .payfs .transaction-status-done .icon {
        width: 40px;
        height: 40px;
    }
</style>

<div id="fob-payfs-bank" class="payfs fob-container">
    @if ($payment->status != \Botble\Payment\Enums\PaymentStatusEnum::COMPLETED)
        <div id="payfs-bank-info">
            <div class="fob-qr-intro">
                Cách 1: Mở app ngân hàng/ Ví để <strong>quét mã QR</strong>
            </div>
            <div class="fob-qr-code">
                <figure>
                    <img src="{{ $imageUrl }}" alt="QR Code">
                </figure>
            </div>

            <div class="fob-qr-intro">
                Cách 2: Chuyển khoản <strong>thủ công</strong> theo thông tin
            </div>
            <div class="fob-qr-information">
                <table class="table table-hover table-striped">
                    <tr>
                        <td>Tên Ngân Hàng</td>
                        <td>
                            <strong>{{ $bank }}</strong>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Chủ Tài Khoản</td>
                        <td>
                            <strong>{{ $bankAccountHolder }}</strong>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Số Tài Khoản</td>
                        <td>
                            <strong>{{ $bankAccountNumber }}</strong>
                        </td>
                        <td class="text-end" style="width: 80px;">
                            <a href="javascript:void(0);" rel="nooper" class="ms-2" type="button" data-clipboard="{{ $bankAccountNumber }}" data-bb-toggle="copy">
                                <x-core::icon name="ti ti-clipboard" />
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Nội Dung Chuyển Khoản</td>
                        <td>
                            <strong>{{ $chargeId }}</strong>
                        </td>
                        <td class="text-end" style="width: 80px;">
                            <a href="javascript:void(0);" rel="nooper" class="ms-2" type="button" data-clipboard="{{ $chargeId }}" data-bb-toggle="copy">
                                <x-core::icon name="ti ti-clipboard" />
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td>Số Tiền Giao Dịch</td>
                        <td>
                            <strong>{{ $formattedOrderAmount = number_format($orderAmount, 0, ',', '.') . ' ₫' }}</strong>
                            @if (isset($originalCurrency) && $originalCurrency !== 'VND')
                                <br>
                                <small class="text-muted">(≈ {{ number_format($originalAmount, 2) }} {{ $originalCurrency }})</small>
                            @endif
                        </td>
                        <td class="text-end" style="width: 80px;">
                            <a href="javascript:void(0);" rel="nooper" class="ms-2" type="button" data-clipboard="{{ $orderAmount }}" data-bb-toggle="copy">
                                <x-core::icon name="ti ti-clipboard" />
                            </a>
                        </td>
                    </tr>
                </table>

                <div class="alert alert-warning">
                    <p>Vui lòng giữ nguyên nội dung chuyển khoản <strong class="text-danger">{{ $chargeId }}</strong> và nhập đúng số tiền <strong class="text-danger">{{ $formattedOrderAmount }}</strong> để được xác nhận thanh toán trực tuyến.</p>
                    @if (isset($originalCurrency) && $originalCurrency !== 'VND')
                        <p class="mt-2 mb-0"><em>Số tiền đã được quy đổi từ {{ $originalCurrency }} sang VND theo tỷ giá hiện tại.</em></p>
                    @endif
                </div>

                <div class="transaction-status text-center" data-bb-toggle="payfs-transaction-status" data-url="{{ route('payfs.transactions.check') }}" data-charge-id="{{ $chargeId }}">
                    Trạng thái chờ thanh toán <img src="{{ url('vendor/core/plugins/fob-payfs/images/loading.gif') }}" width="20" height="20" alt="Loading">
                </div>
            </div>
        </div>
    @endif

    <div @style(['display: none' => $payment->status != \Botble\Payment\Enums\PaymentStatusEnum::COMPLETED])
         id="payfs-transaction-status-done">
        <div class="transaction-status-done card text-center pb-3 pt-2">
            <div class="p-4">
                <div class="mb-2">
                    <x-core::icon name="ti ti-circle-check"/>
                </div>
                <h4>Thanh toán thành công</h4>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const copyButtons = document.querySelectorAll('[data-bb-toggle="copy"]');

        copyButtons.forEach((button) => {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                const textToCopy = this.getAttribute('data-clipboard');
                fobCopyToClipboard(textToCopy);
            })
        })

    })

    let interval = null

    $(document).ready(function() {
        const paymentStatus = $('[data-bb-toggle="payfs-transaction-status"]')

        if (paymentStatus.length) {
            interval = setInterval(() => fetchPaymentStatus(paymentStatus), 3000)
        }
    })

    function fetchPaymentStatus(elm) {
        $.ajax({
            url: elm.data('url'),
            method: 'POST',
            data: {
                charge_id: elm.data('charge-id')
            },
            success: ({ data }) => {
                if (data.status.value === 'completed') {
                    $('#payfs-transaction-status-done').show()
                    $('#payfs-bank-info').remove()

                    let paymentStatusElement = $(document).find('span[data-bb-target="ecommerce-order-payment-status"]');

                    if (paymentStatusElement.length && data.status_html) {
                        paymentStatusElement.html(data.status_html);
                    }

                    clearInterval(interval)
                }
            }
        })
    }

    async function fobCopyToClipboard(textToCopy) {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(textToCopy);
        } else {
            fobUnsecuredCopyToClipboard(textToCopy);
        }

        MainCheckout.showSuccess('Sao chép thành công!');
    }

    function fobUnsecuredCopyToClipboard(textToCopy) {
        const textArea = document.createElement('textarea');
        textArea.value = textToCopy;
        textArea.style.position = 'absolute';
        textArea.style.left = '-999999px';
        document.body.append(textArea);
        textArea.focus();
        textArea.select();

        try {
            document.execCommand('copy');
        } catch (error) {
            console.error('Unable to copy to clipboard', error);
        }

        document.body.removeChild(textArea);
    }
</script>
