<?php

namespace App\Exports;

use App\Models\Claim;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Storage;

class ClaimsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
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
            ->when(!empty($this->filters['transfer_status']), fn ($q) => $q->where('transfer_status', $this->filters['transfer_status']))
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
            'Transfer Date', 'Proof Image Link', 'Notes',
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
                $this->proofUrl($claim->proof_image_path), $claim->notes,
            ];
        }

        return $claim->vouchers->map(fn ($voucher) => [
            ...array_replace($base, [6 => self::VOUCHER_AMOUNTS[$voucher->voucher_type] ?? 0]),
            $voucher->voucher_type,
            $voucher->voucher_code,
            $claim->transfer_status,
            $claim->transfer_date?->format('Y-m-d'),
            $this->proofUrl($claim->proof_image_path),
            $claim->notes,
        ])->all();
    }

    /**
     * Convert the stored public-disk path into an absolute URL so the
     * exported workbook can link directly to the proof image.
     */
    private function proofUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $url = Storage::disk('public')->url($path);

        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        return rtrim((string) config('app.url'), '/') . '/' . ltrim($url, '/');
    }

    /**
     * Turn the proof URL in column O into a real Excel hyperlink.
     * The displayed text stays short, while the hyperlink target is the
     * absolute https://claimlivinmarkt.com/storage/... URL.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                for ($row = 2; $row <= $lastRow; $row++) {
                    $cell = $sheet->getCell("O{$row}");
                    $url = $cell->getValue();

                    if (!is_string($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                        $cell->setValue('—');
                        continue;
                    }

                    $cell->setValue('Open proof');
                    $cell->getHyperlink()->setUrl($url);
                    $cell->getHyperlink()->setTooltip('Open proof image');
                    $cell->getStyle()->getFont()
                        ->setUnderline(true)
                        ->getColor()->setARGB('FF0563C1');
                }
            },
        ];
    }

}
