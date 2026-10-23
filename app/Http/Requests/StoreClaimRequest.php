<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClaimRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'claim_date'=>'required|date', 'store_id'=>'required|exists:stores,id',
            'pic_name'=>'required|string|max:150', 'pic_phone'=>'required|string|max:40',
            'total_claimed'=>'required|numeric|min:0.01|max:9999999999999.99',
            'mandiri_account_number'=>'required|string|max:50', 'account_holder_name'=>'required|string|max:150',
            'transaction_date'=>'required|date', 'proof_image'=>($this->isMethod('post') ? 'required' : 'nullable').'|image|mimes:jpg,jpeg,png,webp,heic|max:5120',
            'voucher_codes'=>'nullable|array', 'voucher_codes.A'=>'nullable|string|max:10000',
            'voucher_codes.B'=>'nullable|string|max:10000', 'voucher_codes.C'=>'nullable|string|max:10000',
            'transfer_status'=>'nullable|in:pending,transferred', 'transfer_date'=>'nullable|date', 'notes'=>'nullable|string|max:2000',
        ];
    }
}
