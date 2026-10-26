<?php

namespace App\Http\Controllers;

use App\Exports\ClaimsExport;
use App\Http\Requests\StoreClaimRequest;
use App\Models\Claim;
use App\Models\Area;
use App\Models\ClaimVoucher;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class ClaimController extends Controller
{
    public function show(Claim $claim)
    {
        $claim->load(['store.area', 'vouchers']);
        return view('claims.show', compact('claim'));
    }

    public function create()
    {
        $claim = new Claim();
        $areas = Area::orderBy('name')->get();
        $stores = Store::with('area')->where('is_active', true)->whereNotNull('area_id')->orderBy('name')->get();

        return view('claims.form', compact('claim', 'areas', 'stores'));
    }
    public function store(StoreClaimRequest $request)
    {
        $data = $request->validated();
        unset($data['proof_image'], $data['area_id']);
        $voucherCodes = $this->parseCodes($data['voucher_codes'] ?? []);
        unset($data['voucher_codes']);
        $data['total_claimed'] = (
            count($voucherCodes['A'] ?? [])
            + count($voucherCodes['B'] ?? [])
        ) * 50000;
        $data['proof_image_path'] = $request->file('proof_image')->store('voucher-proofs', 'public');
        // $data['transfer_status'] = $data['transfer_status'] ?? 'pending';
        // if ($data['transfer_status'] === 'pending') $data['transfer_date'] = null;
        $data['transfer_status'] = 'pending';
        $data['transfer_date'] = now()->toDateString();
        $claim = DB::transaction(function () use ($data, $voucherCodes) {
            $claim = Claim::create($data);
            foreach ($voucherCodes as $type => $codes) foreach ($codes as $code) $claim->vouchers()->create(['voucher_type'=>$type,'voucher_code'=>$code]);
            return $claim;
        });
        return redirect()->route('dashboard')->with('success', 'Claim saved successfully.');
    }
    public function edit(Claim $claim)
    {
        $claim->load(['vouchers', 'store.area']);
        $areas = Area::orderBy('name')->get();
        $stores = Store::with('area')->whereNotNull('area_id')->orderBy('name')->get();

        // Include the claim's current store even if it was deactivated after submission.
        if ($claim->store && !$stores->contains('id', $claim->store_id) && $claim->store->area_id) {
            $stores->push($claim->store);
        }

        return view('claims.form', compact('claim', 'areas', 'stores'));
    }
    public function update(StoreClaimRequest $request, Claim $claim)
    {
        $data = $request->validated(); unset($data['proof_image'], $data['area_id']); $voucherCodes = $this->parseCodes($data['voucher_codes'] ?? []); unset($data['voucher_codes']);
        $data['total_claimed'] = (
            count($voucherCodes['A'] ?? [])
            + count($voucherCodes['B'] ?? [])
        ) * 50000;
        if ($request->hasFile('proof_image')) {
            Storage::disk('public')->delete($claim->proof_image_path);
            $data['proof_image_path'] = $request->file('proof_image')->store('voucher-proofs','public');
        } else { unset($data['proof_image']); }
        // $data['transfer_status'] = $data['transfer_status'] ?? $claim->transfer_status;
        // if ($data['transfer_status'] === 'pending') $data['transfer_date'] = null;
        DB::transaction(function () use ($claim, $data, $voucherCodes) { $claim->update($data); $claim->vouchers()->delete(); foreach ($voucherCodes as $type=>$codes) foreach ($codes as $code) $claim->vouchers()->create(['voucher_type'=>$type,'voucher_code'=>$code]); });
        return redirect()->route('dashboard')->with('success','Claim updated successfully.');
    }
    public function destroy(Claim $claim) { Storage::disk('public')->delete($claim->proof_image_path); $claim->delete(); return back()->with('success','Claim deleted.'); }
    public function updateTransferStatus(Request $request, Claim $claim)
    {
        $data = $request->validate(['transfer_status'=>'required|in:pending,transferred','transfer_date'=>'nullable|date']);
        // if ($data['transfer_status'] === 'transferred' && empty($data['transfer_date'])) $data['transfer_date'] = now()->toDateString();
        // if ($data['transfer_status'] === 'pending') $data['transfer_date'] = null;
        if (empty($data['transfer_date'])) {
            $data['transfer_date'] = now()->toDateString();
        }
        $claim->update($data); return back()->with('success','Transfer status updated.');
    }
    public function export(Request $request)
    {
        $filters = $request->only(['from', 'to', 'area_id', 'store_id', 'voucher_type', 'search']);
        return Excel::download(new ClaimsExport($filters), 'voucher-claims-'.now()->format('Y-m-d_His').'.xlsx');
    }
    private function parseCodes(array $input): array
    {
        $out=[];
        foreach (['A','B','C'] as $type) {
            $raw = preg_split('/[\r\n,;]+/', $input[$type] ?? '', -1, PREG_SPLIT_NO_EMPTY);
            $out[$type] = array_values(array_unique(array_filter(array_map(fn($v)=>trim($v), $raw))));
        }
        return $out;
    }

    public function downloadProofs(Request $request)
    {
        abort_unless(
            auth()->check() && (bool) auth()->user()->is_admin,
            403
        );

        // Apply the same filters as the dashboard.
        $query = Claim::query()
            ->with(['store.area', 'vouchers'])
            ->when($request->filled('from'), function ($q) use ($request) {
                $q->whereDate('claim_date', '>=', $request->input('from'));
            })
            ->when($request->filled('to'), function ($q) use ($request) {
                $q->whereDate('claim_date', '<=', $request->input('to'));
            })
            ->when($request->filled('area_id'), function ($q) use ($request) {
                $q->whereHas('store', fn ($s) => $s->where('area_id', $request->integer('area_id')));
            })
            ->when($request->filled('store_id'), function ($q) use ($request) {
                $q->where('store_id', $request->integer('store_id'));
            })
            ->when($request->filled('voucher_type'), function ($q) use ($request) {
                $q->whereHas('vouchers', function ($v) use ($request) {
                    $v->where('voucher_type', $request->input('voucher_type'));
                });
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');

                $q->where(function ($w) use ($search) {
                    $w->whereHas('store', function ($s) use ($search) {
                        $s->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('store.area', function ($a) use ($search) {
                        $a->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhere('pic_name', 'like', '%' . $search . '%')
                    ->orWhere('pic_phone', 'like', '%' . $search . '%')
                    ->orWhereHas('vouchers', function ($v) use ($search) {
                        $v->where('voucher_code', 'like', '%' . $search . '%');
                    });
                });
            });

        $claims = $query->orderBy('claim_date')
            ->orderBy('id')
            ->get();

        $disk = Storage::disk('public');

        // Keep only claims that have an existing proof image.
        $proofs = $claims->filter(function ($claim) use ($disk) {
            return !empty($claim->proof_image_path)
                && $disk->exists($claim->proof_image_path);
        });

        if ($proofs->isEmpty()) {
            return redirect()
                ->back()
                ->with('error', 'No proof images were found for the selected filters.');
        }

        if (!class_exists(ZipArchive::class)) {
            abort(500, 'The PHP ZIP extension is not enabled on this server.');
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'claim-proofs-');

        if ($zipPath === false) {
            abort(500, 'Could not create the proof download archive.');
        }

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::OVERWRITE) !== true) {
            @unlink($zipPath);
            abort(500, 'Could not create the proof ZIP file.');
        }

        foreach ($proofs as $claim) {
            $relativePath = $claim->proof_image_path;
            $absolutePath = $disk->path($relativePath);

            // Avoid duplicate filenames and keep the claim ID identifiable.
            $originalName = basename($relativePath);
            $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);

            $storeName = preg_replace(
                '/[^A-Za-z0-9._-]/',
                '_',
                $claim->store->name ?? 'store'
            );
            $areaName = preg_replace(
                '/[^A-Za-z0-9._-]/',
                '_',
                $claim->store->area->name ?? 'unassigned-area'
            );

            $zipName = sprintf(
                'claim-%d-%s-%s-%s',
                $claim->id,
                $areaName,
                $storeName,
                $safeName
            );

            $zip->addFile($absolutePath, $zipName);
        }

        $zip->close();

        return response()
            ->download($zipPath, 'claim-proofs-' . now()->format('Ymd-His') . '.zip')
            ->deleteFileAfterSend(true);
    }
}
