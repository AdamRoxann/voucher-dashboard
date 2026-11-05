<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Claim;
use App\Models\ClaimVoucher;
use Illuminate\Validation\Rule;

class StoreClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $storeRule = Rule::exists('stores', 'id')
            ->where('area_id', $this->input('area_id'));

        // New submissions may only use an active store. Admin edits can preserve
        // a previously selected inactive store, but must still match its area.
        if ($this->isMethod('post')) {
            $storeRule->where('is_active', true);
        }

        return [
            'claim_date' => ['required', 'date'],
            'area_id' => ['required', 'integer', 'exists:areas,id'],
            'store_id' => ['required', 'integer', $storeRule],
            'pic_name' => ['required', 'string', 'max:150'],
            'pic_phone' => ['required', 'string', 'max:40'],
            // 'total_claimed' => ['required', 'numeric', 'min:0.01', 'max:9999999999999.99'],
            'mandiri_account_number' => ['required', 'string', 'max:50'],
            'account_holder_name' => ['required', 'string', 'max:150'],
            'transaction_date' => ['required', 'date'],
            // Validate this as a file first. The processor checks the actual
            // image contents and converts supported formats (including HEIC) to JPEG.
            'proof_image' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'file',
                'max:15360',
            ],
            'voucher_codes' => ['nullable', 'array'],
            'voucher_codes.A' => ['nullable', 'string', 'max:10000'],
            'voucher_codes.B' => ['nullable', 'string', 'max:10000'],
            'voucher_codes.C' => ['nullable', 'string', 'max:10000'],
            'voucher_codes.D' => ['nullable', 'string', 'max:10000'],
            'voucher_codes.E' => ['nullable', 'string', 'max:10000'],
            'transfer_status' => ['nullable', 'in:pending,approved,transferred,rejected'],
            'transfer_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }


    public function messages(): array
    {
        return [
            'proof_image.required' => 'Please attach a proof image before submitting the claim.',
            'proof_image.file' => 'The proof image upload did not complete. Please try again with a supported image up to 15 MB.',
            'proof_image.max' => 'The proof image must not be larger than 15 MB.',
        ];
    }

    /**
     * A voucher code may only be used once across the entire campaign,
     * regardless of voucher type. Editing a claim may retain its own codes.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Let the regular rules report malformed payloads first.
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $voucherInput = (array) $this->input('voucher_codes', []);
            $seenInForm = [];
            $codeTypes = [];

            foreach (['A', 'B', 'C', 'D', 'E'] as $type) {
                $rawCodes = preg_split(
                    '/[\r\n,;]+/',
                    (string) ($voucherInput[$type] ?? ''),
                    -1,
                    PREG_SPLIT_NO_EMPTY
                );

                foreach ($rawCodes as $rawCode) {
                    $code = strtoupper(trim($rawCode));

                    if ($code === '') {
                        continue;
                    }

                    if (isset($seenInForm[$code])) {
                        $validator->errors()->add(
                            "voucher_codes.{$type}",
                            "Voucher code {$code} appears more than once in this form. Each voucher code can only be used once."
                        );
                        continue;
                    }

                    $seenInForm[$code] = true;
                    $codeTypes[$code] = $type;
                }
            }

            if ($seenInForm === []) {
                return;
            }

            $existingQuery = ClaimVoucher::query()
                ->whereIn('voucher_code', array_keys($seenInForm));

            $currentClaim = $this->route('claim');

            if ($currentClaim instanceof Claim) {
                $existingQuery->where('claim_id', '<>', $currentClaim->getKey());
            }

            $alreadyUsedCodes = $existingQuery->pluck('voucher_code')
                ->map(fn ($code) => strtoupper(trim($code)))
                ->unique();

            foreach ($alreadyUsedCodes as $code) {
                $type = $codeTypes[$code] ?? 'A';

                $validator->errors()->add(
                    "voucher_codes.{$type}",
                    "Voucher code {$code} has already been claimed. Each voucher code can only be used in one claim."
                );
            }
        });
    }
}
