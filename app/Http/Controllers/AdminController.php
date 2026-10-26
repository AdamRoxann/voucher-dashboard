<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Claim;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $settings = DB::table('campaign_settings')->pluck('setting_value', 'setting_key');
        $areas = Area::withCount('stores')->orderBy('name')->get();
        $stores = Store::with('area')->withCount('claims')->orderBy('name')->get();

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

        $claims = (clone $query)
            ->latest('claim_date')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        $counts = [
            'claims' => Claim::count(),
            'pending' => Claim::where('transfer_status', 'pending')->count(),
            'transferred' => Claim::where('transfer_status', 'transferred')->count(),
            'stores' => Store::where('is_active', true)->whereNotNull('area_id')->count(),
        ];

        return view('admin.index', compact('settings', 'areas', 'stores', 'claims', 'counts'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'event_name' => ['required', 'string', 'max:180'],
            'event_subtitle' => ['nullable', 'string', 'max:255'],
            'event_start' => ['required', 'date'],
            'event_end' => ['required', 'date', 'after_or_equal:event_start'],
        ]);

        foreach ($data as $key => $value) {
            DB::table('campaign_settings')->updateOrInsert(
                ['setting_key' => $key],
                ['setting_value' => $value, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        return redirect()->route('admin.index')->with('success', 'Campaign settings saved.');
    }

    public function addStore(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:stores,name'],
            'area_id' => ['required', 'integer', 'exists:areas,id'],
        ]);

        Store::create([
            'name' => trim($data['name']),
            'area_id' => $data['area_id'],
            'is_active' => true,
        ]);

        return redirect()->route('admin.index', ['#' => 'stores'])->with('success', 'Store added.');
    }

    public function toggleStore(Store $store)
    {
        $store->update(['is_active' => ! $store->is_active]);

        return redirect()->route('admin.index', ['#' => 'stores'])
            ->with('success', $store->is_active ? 'Store activated.' : 'Store deactivated.');
    }

    // public function updateTransfer(Request $request, Claim $claim)
    // {
    //     $data = $request->validate([
    //         'transfer_status' => ['required', Rule::in(['pending', 'transferred'])],
    //         'transfer_date' => ['nullable', 'date'],
    //     ]);
    //     $data['transfer_date'] = $data['transfer_status'] === 'transferred'
    //         ? ($data['transfer_date'] ?? now()->toDateString())
    //         : null;
    //     $claim->update($data);

    //     return redirect()->route('admin.index', ['#' => 'transfers'])->with('success', 'Transfer status updated.');
    // }
    public function updateTransfer(Request $request, Claim $claim)
    {
        $data = $request->validate([
            'transfer_status' => [
                'required',
                Rule::in(['pending', 'transferred', 'rejected']),
            ],
            'transfer_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        // Use today's Jakarta date if the admin leaves the date blank.
        if (empty($data['transfer_date'])) {
            $data['transfer_date'] = now()->toDateString();
        }

        // Save status, date, and notes together.
        $claim->update($data);

        return redirect()
            ->route('admin.index', ['#' => 'transfers'])
            ->with('success', 'Claim status and notes updated.');
    }
}
