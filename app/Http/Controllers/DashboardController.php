<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\ClaimVoucher;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = Claim::query()->with(['store','vouchers'])->when($request->filled('from'), fn($q) => $q->whereDate('claim_date','>=',$request->date('from')))
            ->when($request->filled('to'), fn($q) => $q->whereDate('claim_date','<=',$request->date('to')))
            ->when($request->filled('store_id'), fn($q) => $q->where('store_id',$request->integer('store_id')))
            ->when($request->filled('voucher_type'), fn($q) => $q->whereHas('vouchers', fn($v) => $v->where('voucher_type',$request->voucher_type)))
            ->when($request->filled('search'), fn($q) => $q->where(function($w) use ($request) { $w->whereHas('store', fn($s) => $s->where('name','like','%'.$request->search.'%'))->orWhere('pic_name','like','%'.$request->search.'%')->orWhereHas('vouchers', fn($v) => $v->where('voucher_code','like','%'.$request->search.'%')); }));
        $claims = (clone $query)->latest('claim_date')->latest('id')->paginate(12)->withQueryString();
        $claimIds = (clone $query)->select('claims.id');
        $stats = [
            'voucher_codes' => ClaimVoucher::whereIn('claim_id', (clone $claimIds))->count(),
            'active_stores' => Store::where('is_active',true)->count(),
            'voucher_types' => ClaimVoucher::whereIn('claim_id', (clone $claimIds))->distinct('voucher_type')->count('voucher_type'),
            'claim_days' => (clone $query)->distinct()->count('claim_date'),
        ];
        $daily = Claim::select('claim_date', DB::raw('count(*) as total'))->when($request->filled('from'), fn($q) => $q->whereDate('claim_date','>=',$request->date('from')))->when($request->filled('to'), fn($q) => $q->whereDate('claim_date','<=',$request->date('to')))->groupBy('claim_date')->orderBy('claim_date')->limit(7)->get();
        $typeCounts = ClaimVoucher::whereIn('claim_id', (clone $claimIds))->select('voucher_type', DB::raw('count(*) as total'))->groupBy('voucher_type')->pluck('total','voucher_type');
        $topStores = Store::withCount(['claims as voucher_count' => fn($q) => $q->whereHas('vouchers')])->orderByDesc('voucher_count')->limit(10)->get();
        $stores = Store::orderBy('name')->get();
        return view('dashboard', compact('claims','stats','daily','typeCounts','topStores','stores'));
    }
}
