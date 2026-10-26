<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::with('area')->withCount('claims')->orderBy('name')->paginate(20);

        return view('stores.index', compact('stores'));
    }

    public function create()
    {
        $store = new Store();
        $areas = Area::orderBy('name')->get();

        return view('stores.form', compact('store', 'areas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:stores,name'],
            'area_id' => ['required', 'integer', 'exists:areas,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Store::create([
            'name' => trim($data['name']),
            'area_id' => $data['area_id'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('stores.index')->with('success', 'Store added.');
    }

    public function edit(Store $store)
    {
        $areas = Area::orderBy('name')->get();

        return view('stores.form', compact('store', 'areas'));
    }

    public function update(Request $request, Store $store)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('stores', 'name')->ignore($store->id),
            ],
            'area_id' => ['required', 'integer', 'exists:areas,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $store->update([
            'name' => trim($data['name']),
            'area_id' => $data['area_id'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('stores.index')->with('success', 'Store updated.');
    }

    public function destroy(Store $store)
    {
        if ($store->claims()->exists()) {
            return back()->withErrors([
                'store' => 'Stores with claims cannot be deleted. Deactivate it instead.',
            ]);
        }

        $store->delete();

        return back()->with('success', 'Store deleted.');
    }
}
