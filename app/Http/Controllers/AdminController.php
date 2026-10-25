<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    // public function index()
    // {
    //     $settings = DB::table('campaign_settings')->pluck('setting_value', 'setting_key');
    //     $stores = Store::withCount('claims')->orderBy('name')->get();
    //     $claims = Claim::with(['store', 'vouchers'])->latest('claim_date')->latest('id')->paginate(30);
    //     $counts = [
    //         'claims' => Claim::count(),
    //         'pending' => Claim::where('transfer_status', 'pending')->count(),
    //         'transferred' => Claim::where('transfer_status', 'transferred')->count(),
    //         'stores' => Store::where('is_active', true)->count(),
    //     ];
    //     return view('admin.index', compact('settings', 'stores', 'claims', 'counts'));
    // }
    
    public function index(Request $request)
    {
        $settings = DB::table('campaign_settings')
            ->pluck('setting_value', 'setting_key');

        $stores = Store::withCount('claims')
            ->orderBy('name')
            ->get();

        // Match the public Overview filters.
        $query = Claim::query()
            ->with(['store', 'vouchers'])
            ->when($request->filled('from'), fn ($q) =>
                $q->whereDate('claim_date', '>=', $request->date('from'))
            )
            ->when($request->filled('to'), fn ($q) =>
                $q->whereDate('claim_date', '<=', $request->date('to'))
            )
            ->when($request->filled('store_id'), fn ($q) =>
                $q->where('store_id', $request->integer('store_id'))
            )
            ->when($request->filled('voucher_type'), fn ($q) =>
                $q->whereHas('vouchers', fn ($v) =>
                    $v->where('voucher_type', $request->voucher_type)
                )
            )
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;

                $q->where(function ($w) use ($search) {
                    $w->whereHas('store', fn ($s) =>
                        $s->where('name', 'like', '%' . $search . '%')
                    )
                    ->orWhere('pic_name', 'like', '%' . $search . '%')
                    ->orWhereHas('vouchers', fn ($v) =>
                        $v->where('voucher_code', 'like', '%' . $search . '%')
                    );
                });
            });

        $claims = (clone $query)
            ->latest('claim_date')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        // Keep the admin summary cards as overall totals.
        $counts = [
            'claims' => Claim::count(),
            'pending' => Claim::where('transfer_status', 'pending')->count(),
            'transferred' => Claim::where('transfer_status', 'transferred')->count(),
            'stores' => Store::where('is_active', true)->count(),
        ];

        return view('admin.index', compact(
            'settings',
            'stores',
            'claims',
            'counts'
        ));
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
            DB::table('campaign_settings')->updateOrInsert(['setting_key' => $key], ['setting_value' => $value, 'updated_at' => now(), 'created_at' => now()]);
        }
        return redirect()->route('admin.index')->with('success', 'Campaign settings saved.');
    }

    public function addStore(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:150', 'unique:stores,name']]);
        Store::create(['name' => trim($data['name']), 'is_active' => true]);
        return redirect()->route('admin.index')->with('success', 'Store added.');
    }

    public function toggleStore(Store $store)
    {
        $store->update(['is_active' => ! $store->is_active]);
        return redirect()->route('admin.index')->with('success', $store->is_active ? 'Store activated.' : 'Store deactivated.');
    }

    public function updateTransfer(Request $request, Claim $claim)
    {
        $data = $request->validate([
            'transfer_status' => ['required', Rule::in(['pending', 'transferred'])],
            'transfer_date' => ['nullable', 'date'],
        ]);
        $data['transfer_date'] = $data['transfer_status'] === 'transferred' ? ($data['transfer_date'] ?? now()->toDateString()) : null;
        $claim->update($data);
        return redirect()->route('admin.index', ['#' => 'transfers'])->with('success', 'Transfer status updated.');
    }
}
