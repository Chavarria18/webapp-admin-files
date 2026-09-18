<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AreaController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Area::class);

        $areas = Area::withCount([
            'usuarios as estandar_count' => fn ($query) => $query->where('role', 'estandar'),
            'usuarios as jefes_area_count' => fn ($query) => $query->where('role', 'jefe_area'),
            'gerentes',
        ])->paginate(10);

        return view('areas.index', compact('areas'));
    }

    public function create(): View
    {
        $this->authorize('create', Area::class);

        return view('areas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Area::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:areas,name'],
        ]);

        Area::create($validated);

        return redirect()->route('areas.index')->with('success', 'Área creada.');
    }

    public function edit(Area $area): View
    {
        $this->authorize('update', $area);

        return view('areas.edit', compact('area'));
    }

    public function update(Request $request, Area $area): RedirectResponse
    {
        $this->authorize('update', $area);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:areas,name,'.$area->id],
        ]);

        $area->update($validated);

        return redirect()->route('areas.index')->with('success', 'Área actualizada.');
    }

    public function destroy(Area $area): RedirectResponse
    {
        $this->authorize('delete', $area);

        if ($area->usuarios()->exists() || $area->gerentes()->exists()) {
            return redirect()->route('areas.index')
                ->withErrors(['area' => 'No se puede eliminar el área porque tiene usuarios o gerentes asignados.']);
        }

        $area->delete();

        return redirect()->route('areas.index')->with('success', 'Área eliminada.');
    }
}
