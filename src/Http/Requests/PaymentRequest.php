<?php

namespace FriendsOfBotble\PayFS\Http\Requests;

use Botble\Support\Http\Requests\Request;
use FriendsOfBotble\PayFS\PayFS;
use Illuminate\Validation\Rule;

class PaymentRequest extends Request
{
    public function rules(): array
    {
        return [
            'payfs_bank' => ['required', 'string', Rule::in(array_keys(PayFS::getBanksList()))],
            'payfs_bank_account_number' => ['required', 'string'],
            'payfs_bank_account_holder' => ['required', 'string'],
            'payfs_pay_code_prefix' => ['required', 'string', 'alpha_num'],
            'payfs_api_key' => ['required', 'string'],
            'payfs_webhook_secret' => ['nullable', 'string'],
        ];
    }
}
