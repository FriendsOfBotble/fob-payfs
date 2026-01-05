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

        $baseUrl = sprintf(
            'https://img.vietqr.io/image/%s-%s-compact2.jpg',
            $bankCode,
            $accountNumber
        );

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
            'ABB' => 'ABBANK',
            'ACB' => 'ACB',
            'BAB' => 'BacABank',
            'BIDV' => 'BIDV',
            'BVB' => 'BaoVietBank',
            'CAKE' => 'CAKE',
            'CIMB' => 'CIMB',
            'COOPBANK' => 'COOPBANK',
            'EIB' => 'Eximbank',
            'HDB' => 'HDBank',
            'ICB' => 'VietinBank',
            'KBank' => 'KBank',
            'KLB' => 'KienLongBank',
            'LPB' => 'LienVietPostBank',
            'MB' => 'MBBank',
            'MSB' => 'MSB',
            'NAB' => 'NamABank',
            'NCB' => 'NCB',
            'OCB' => 'OCB',
            'Oceanbank' => 'Oceanbank',
            'PGB' => 'PGBank',
            'PVCB' => 'PVcomBank',
            'SCB' => 'SCB',
            'SEAB' => 'SeABank',
            'SGICB' => 'SaigonBank',
            'SHB' => 'SHB',
            'SHBVN' => 'ShinhanBank',
            'STB' => 'Sacombank',
            'TCB' => 'Techcombank',
            'TIMO' => 'Timo',
            'TPB' => 'TPBank',
            'Ubank' => 'Ubank',
            'VAB' => 'VietABank',
            'VBA' => 'Agribank',
            'VCB' => 'Vietcombank',
            'VCCB' => 'VietCapitalBank',
            'VIB' => 'VIB',
            'VIETBANK' => 'VietBank',
            'VPB' => 'VPBank',
            'WVN' => 'Woori',
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

        // Match prefix + optional separator (hyphen, space, or none) + digits
        preg_match('/(' . preg_quote($prefix, '/') . ')[\-\s]?(\d+)/', $content, $matches);

        if (isset($matches[1]) && isset($matches[2])) {
            // Return normalized format: PREFIX + DIGITS (no separator)
            // This matches how charge_id is generated in PayFSPaymentService
            return $matches[1] . $matches[2];
        }

        return null;
    }
}
