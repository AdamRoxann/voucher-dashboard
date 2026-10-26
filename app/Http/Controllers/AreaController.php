<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AreaController extends Controller
{
    public function index()
    {
        $areas = Area::withCount('stores')->orderBy('name')->paginate(20);

        return view('areas.index', compact('areas'));
    }

    public function create()
    {
        $area = new Area();

        return view('areas.form', compact('area'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:areas,name'],
        ]);

        Area::create(['name' => trim($data['name'])]);

        return redirect()->route('areas.index')->with('success', 'Area added.');
    }

    public function edit(Area $area)
    {
        return view('areas.form', compact('area'));
    }

    public function update(Request $request, Area $area)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('areas', 'name')->ignore($area->id),
            ],
        ]);

        $area->update(['name' => trim($data['name'])]);

        return redirect()->route('areas.index')->with('success', 'Area updated.');
    }

    public function destroy(Area $area)
    {
        if ($area->stores()->exists()) {
            return back()->withErrors([
                'area' => 'This area still has stores. Move those stores to another area before deleting it.',
            ]);
        }

        $area->delete();

        return redirect()->route('areas.index')->with('success', 'Area deleted.');
    }
}
