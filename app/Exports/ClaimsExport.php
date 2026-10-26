<?php

namespace App\Exports;

use App\Models\Claim;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClaimsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private array $filters = [])
    {
    }

    public function query()
    {
        return Claim::query()
            ->with(['store.area', 'vouchers'])
            ->when(!empty($this->filters['from']), fn ($q) => $q->whereDate('claim_date', '>=', $this->filters['from']))
            ->when(!empty($this->filters['to']), fn ($q) => $q->whereDate('claim_date', '<=', $this->filters['to']))
            ->when(!empty($this->filters['area_id']), fn ($q) => $q->whereHas('store', fn ($s) => $s->where('area_id', $this->filters['area_id'])))
            ->when(!empty($this->filters['store_id']), fn ($q) => $q->where('store_id', $this->filters['store_id']))
            ->when(!empty($this->filters['voucher_type']), fn ($q) => $q->whereHas('vouchers', fn ($v) => $v->where('voucher_type', $this->filters['voucher_type'])))
            ->when(!empty($this->filters['search']), function ($q) {
                $search = $this->filters['search'];
                $q->where(function ($w) use ($search) {
                    $w->whereHas('store', fn ($s) => $s->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('store.area', fn ($a) => $a->where('name', 'like', "%{$search}%"))
                        ->orWhere('pic_name', 'like', "%{$search}%")
                        ->orWhere('pic_phone', 'like', "%{$search}%")
                        ->orWhereHas('vouchers', fn ($v) => $v->where('voucher_code', 'like', "%{$search}%"));
                });
            })
            ->orderBy('claim_date')
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'Claim ID', 'Claim Date', 'Area', 'Store', 'PIC Name', 'PIC Phone',
            'Total Claimed (IDR)', 'Mandiri Account Number', 'Account Holder',
            'Transaction Date', 'Voucher Type', 'Voucher Code', 'Transfer Status',
            'Transfer Date', 'Proof Image Path', 'Notes',
        ];
    }

    public function map($claim): array
    {
        $base = [
            $claim->id,
            $claim->claim_date?->format('Y-m-d'),
            $claim->store->area?->name ?? 'Unassigned',
            $claim->store->name,
            $claim->pic_name,
            $claim->pic_phone,
            $claim->total_claimed,
            $claim->mandiri_account_number,
            $claim->account_holder_name,
            $claim->transaction_date?->format('Y-m-d'),
        ];

        if ($claim->vouchers->isEmpty()) {
            return [
                ...$base,
                '-', '-', $claim->transfer_status,
                $claim->transfer_date?->format('Y-m-d'),
                $claim->proof_image_path, $claim->notes,
            ];
        }

        return $claim->vouchers->map(fn ($voucher) => [
            ...$base,
            $voucher->voucher_type,
            $voucher->voucher_code,
            $claim->transfer_status,
            $claim->transfer_date?->format('Y-m-d'),
            $claim->proof_image_path,
            $claim->notes,
        ])->all();
    }
}
