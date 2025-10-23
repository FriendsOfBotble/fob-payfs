<?php

namespace FriendsOfBotble\PayFS;

use Illuminate\Support\Arr;

class PayFS
{
    /**
     * Generate QR code URL for bank transfer using VietQR API
     *
     * @param float $amount
     * @param string $chargeId
     * @return string
     */
    public static function getQRCodeUrl(float $amount, string $chargeId): string
    {
        $bankCode = get_payment_setting('bank', PAYFS_PAYMENT_METHOD_NAME);
        $accountNumber = get_payment_setting('account_number', PAYFS_PAYMENT_METHOD_NAME);
        $accountName = get_payment_setting('account_holder', PAYFS_PAYMENT_METHOD_NAME);

        // VietQR API format: https://img.vietqr.io/image/{BANK_ID}-{ACCOUNT_NO}-{TEMPLATE}.jpg
        $baseUrl = sprintf(
            'https://img.vietqr.io/image/%s-%s-compact2.jpg',
            $bankCode,
            $accountNumber
        );

        // Add query parameters
        $params = http_build_query([
            'amount' => $amount,
            'addInfo' => $chargeId,
            'accountName' => $accountName,
        ]);

        return $baseUrl . '?' . $params;
    }

    /**
     * Get list of supported Vietnamese banks
     *
     * @return array
     */
    public static function getBanksList(): array
    {
        return [
            'VCB' => 'Vietcombank',
            'TCB' => 'Techcombank',
            'BIDV' => 'BIDV',
            'VTB' => 'Vietinbank',
            'MBB' => 'MBBank',
            'ACB' => 'ACB',
            'VPB' => 'VPBank',
            'TPB' => 'TPBank',
            'STB' => 'Sacombank',
            'HDB' => 'HDBank',
            'SHB' => 'SHB',
            'EIB' => 'Eximbank',
            'MSB' => 'MSB',
            'CAKE' => 'CAKE by VPBank',
            'Ubank' => 'Ubank by VPBank',
            'OCB' => 'OCB',
            'VIB' => 'VIB',
            'SeABank' => 'SeABank',
            'ABBANK' => 'ABBank',
            'NCB' => 'NCB',
            'SGB' => 'Saigonbank',
            'BVB' => 'BaoVietBank',
            'VietCapitalBank' => 'VietCapital Bank',
            'SCB' => 'SCB',
            'PVcomBank' => 'PVcomBank',
            'Oceanbank' => 'Oceanbank',
            'NamABank' => 'Nam A Bank',
            'IVB' => 'IndovinaBank',
            'KLB' => 'KienLongBank',
            'LPBank' => 'LienVietPostBank',
            'PGB' => 'PG Bank',
            'GPB' => 'GPBank',
            'VRB' => 'VRB',
            'VAB' => 'VietA Bank',
            'Agribank' => 'Agribank',
        ];
    }

    /**
     * Get bank name by bank code
     *
     * @param string $id
     * @return string
     */
    public static function getBankById(string $id): string
    {
        return Arr::get(static::getBanksList(), $id, $id);
    }

    /**
     * Extract charge ID from transaction content
     *
     * @param string $content
     * @return string|null
     */
    public static function getChargeIdFrom(string $content): ?string
    {
        $prefix = get_payment_setting('prefix', PAYFS_PAYMENT_METHOD_NAME, 'SHD');

        preg_match('/(' . preg_quote($prefix, '/') . '\d+)/', $content, $matches);

        if (isset($matches[1])) {
            return $matches[1];
        }

        return null;
    }
}
