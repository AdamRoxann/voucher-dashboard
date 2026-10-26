<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Claim;
use App\Models\ClaimVoucher;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = Claim::query()
            ->with(['store.area', 'vouchers'])
            ->when($request->filled('from'), fn ($q) => $q->whereDate('claim_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('claim_date', '<=', $request->date('to')))
            ->when($request->filled('area_id'), fn ($q) => $q->whereHas('store', fn ($s) => $s->where('area_id', $request->integer('area_id'))))
            ->when($request->filled('store_id'), fn ($q) => $q->where('store_id', $request->integer('store_id')))
            ->when($request->filled('voucher_type'), fn ($q) => $q->whereHas('vouchers', fn ($v) => $v->where('voucher_type', $request->voucher_type)))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($w) use ($search) {
                    $w->whereHas('store', fn ($s) => $s->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('store.area', fn ($a) => $a->where('name', 'like', "%{$search}%"))
                        ->orWhere('pic_name', 'like', "%{$search}%")
                        ->orWhere('pic_phone', 'like', "%{$search}%")
                        ->orWhereHas('vouchers', fn ($v) => $v->where('voucher_code', 'like', "%{$search}%"));
                });
            });

        $claims = (clone $query)->latest('claim_date')->latest('id')->paginate(12)->withQueryString();
        $claimIds = (clone $query)->select('claims.id');

        $stats = [
            'voucher_codes' => ClaimVoucher::whereIn('claim_id', clone $claimIds)->count(),
            'active_stores' => Store::where('is_active', true)->whereNotNull('area_id')->count(),
            'voucher_types' => ClaimVoucher::whereIn('claim_id', clone $claimIds)->distinct('voucher_type')->count('voucher_type'),
            'claim_days' => (clone $query)->distinct()->count('claim_date'),
        ];

        $daily = (clone $query)
            ->setEagerLoads([])
            ->select('claim_date', DB::raw('count(*) as total'))
            ->groupBy('claim_date')
            ->orderBy('claim_date')
            ->limit(7)
            ->get();

        $typeCounts = ClaimVoucher::whereIn('claim_id', clone $claimIds)
            ->select('voucher_type', DB::raw('count(*) as total'))
            ->groupBy('voucher_type')
            ->pluck('total', 'voucher_type');

        $topStores = Store::with('area')
            ->withCount(['claims as voucher_count' => fn ($q) => $q->whereHas('vouchers')])
            ->when($request->filled('area_id'), fn ($q) => $q->where('area_id', $request->integer('area_id')))
            ->when($request->filled('store_id'), fn ($q) => $q->where('id', $request->integer('store_id')))
            ->orderByDesc('voucher_count')
            ->limit(10)
            ->get();

        $areas = Area::withCount('stores')->orderBy('name')->get();
        $stores = Store::with('area')->orderBy('name')->get();

        return view('dashboard', compact('claims', 'stats', 'daily', 'typeCounts', 'topStores', 'stores', 'areas'));
    }
}
