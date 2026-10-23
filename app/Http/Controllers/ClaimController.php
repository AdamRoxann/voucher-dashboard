<?php

namespace App\Http\Controllers;

use App\Exports\ClaimsExport;
use App\Http\Requests\StoreClaimRequest;
use App\Models\Claim;
use App\Models\ClaimVoucher;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ClaimController extends Controller
{
    public function show(Claim $claim)
    {
        $claim->load(['store', 'vouchers']);
        return view('claims.show', compact('claim'));
    }

    public function create() { return view('claims.form', ['claim'=>new Claim(), 'stores'=>Store::where('is_active',true)->orderBy('name')->get()]); }
    public function store(StoreClaimRequest $request)
    {
        $data = $request->validated();
        unset($data['proof_image']);
        $voucherCodes = $this->parseCodes($data['voucher_codes'] ?? []);
        unset($data['voucher_codes']);
        $data['proof_image_path'] = $request->file('proof_image')->store('voucher-proofs', 'public');
        $data['transfer_status'] = $data['transfer_status'] ?? 'pending';
        if ($data['transfer_status'] === 'pending') $data['transfer_date'] = null;
        $claim = DB::transaction(function () use ($data, $voucherCodes) {
            $claim = Claim::create($data);
            foreach ($voucherCodes as $type => $codes) foreach ($codes as $code) $claim->vouchers()->create(['voucher_type'=>$type,'voucher_code'=>$code]);
            return $claim;
        });
        return redirect()->route('dashboard')->with('success', 'Claim saved successfully.');
    }
    public function edit(Claim $claim) { $claim->load('vouchers'); return view('claims.form', ['claim'=>$claim, 'stores'=>Store::orderBy('name')->get()]); }
    public function update(StoreClaimRequest $request, Claim $claim)
    {
        $data = $request->validated(); unset($data['proof_image']); $voucherCodes = $this->parseCodes($data['voucher_codes'] ?? []); unset($data['voucher_codes']);
        if ($request->hasFile('proof_image')) {
            Storage::disk('public')->delete($claim->proof_image_path);
            $data['proof_image_path'] = $request->file('proof_image')->store('voucher-proofs','public');
        } else { unset($data['proof_image']); }
        $data['transfer_status'] = $data['transfer_status'] ?? $claim->transfer_status;
        if ($data['transfer_status'] === 'pending') $data['transfer_date'] = null;
        DB::transaction(function () use ($claim, $data, $voucherCodes) { $claim->update($data); $claim->vouchers()->delete(); foreach ($voucherCodes as $type=>$codes) foreach ($codes as $code) $claim->vouchers()->create(['voucher_type'=>$type,'voucher_code'=>$code]); });
        return redirect()->route('dashboard')->with('success','Claim updated successfully.');
    }
    public function destroy(Claim $claim) { Storage::disk('public')->delete($claim->proof_image_path); $claim->delete(); return back()->with('success','Claim deleted.'); }
    public function updateTransferStatus(Request $request, Claim $claim)
    {
        $data = $request->validate(['transfer_status'=>'required|in:pending,transferred','transfer_date'=>'nullable|date']);
        if ($data['transfer_status'] === 'transferred' && empty($data['transfer_date'])) $data['transfer_date'] = now()->toDateString();
        if ($data['transfer_status'] === 'pending') $data['transfer_date'] = null;
        $claim->update($data); return back()->with('success','Transfer status updated.');
    }
    public function export(Request $request)
    {
        $filters = $request->only(['from','to','store_id','voucher_type','search']);
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
}
