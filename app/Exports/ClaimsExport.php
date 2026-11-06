<?php

namespace App\Exports;

use App\Models\Claim;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClaimsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private const VOUCHER_AMOUNTS = [
        'A' => 50000,
        'B' => 50000,
        'C' => 50000,
        'D' => 50000,
        'E' => 100000,
    ];

    public function __construct(private array $filters = [])
    {
    }

    public function query()
    {
        return Claim::query()
            ->with([
                'store.area',
                'vouchers' => fn ($v) => $v->when(
                    !empty($this->filters['voucher_type']),
                    fn ($q) => $q->where('voucher_type', $this->filters['voucher_type'])
                ),
            ])
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
            'Voucher Amount (IDR)', 'Mandiri Account Number', 'Account Holder',
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
            null, // Replaced with the individual voucher amount for each exported row.
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
            ...array_replace($base, [6 => self::VOUCHER_AMOUNTS[$voucher->voucher_type] ?? 0]),
            $voucher->voucher_type,
            $voucher->voucher_code,
            $claim->transfer_status,
            $claim->transfer_date?->format('Y-m-d'),
            $claim->proof_image_path,
            $claim->notes,
        ])->all();
    }
}
