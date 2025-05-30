<?php

namespace App\Http\Controllers;

use App\Models\Almacenamiento;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlmacenamientoController extends Controller
{
    /**
     * Display a listing of storage locations.
     */
    public function index()
    {
        $almacenamientos = Almacenamiento::with('inventario')
            ->latest()
            ->paginate(10);
            
        return view('almacenamiento.index', compact('almacenamientos'));
    }

    /**
     * Show the form for creating a new storage location.
     */
    public function create()
    {
        $productos = Inventario::pluck('producto', 'id');
        return view('almacenamiento.create', compact('productos'));
    }

    /**
     * Store a newly created storage location in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ubicacion' => 'required|string|max:50',
            'producto_id' => 'required|exists:inventarios,id',
            'cantidad' => 'required|numeric|min:1',
            'capacidad_maxima' => 'required|numeric|min:1',
            'tipo_almacenamiento' => 'required|string|max:50',
            'condiciones_especiales' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Verify available capacity
            $total_almacenado = Almacenamiento::where('ubicacion', $validated['ubicacion'])
                ->sum('cantidad');
                
            if (($total_almacenado + $validated['cantidad']) > $validated['capacidad_maxima']) {
                throw new \Exception('La cantidad excede la capacidad máxima de la ubicación.');
            }

            // Create storage record
            $almacenamiento = Almacenamiento::create($validated);

            DB::commit();

            return redirect()
                ->route('almacenamiento.index')
                ->with('success', 'Ubicación de almacenamiento creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Error al crear la ubicación. ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified storage location.
     */
    public function show(Almacenamiento $almacenamiento)
    {
        $almacenamiento->load('inventario');
        return view('almacenamiento.show', compact('almacenamiento'));
    }

    /**
     * Show the form for editing the specified storage location.
     */
    public function edit(Almacenamiento $almacenamiento)
    {
        $productos = Inventario::pluck('producto', 'id');
        return view('almacenamiento.edit', compact('almacenamiento', 'productos'));
    }

    /**
     * Update the specified storage location in storage.
     */
    public function update(Request $request, Almacenamiento $almacenamiento)
    {
        $validated = $request->validate([
            'ubicacion' => 'required|string|max:50',
            'producto_id' => 'required|exists:inventarios,id',
            'cantidad' => 'required|numeric|min:1',
            'capacidad_maxima' => 'required|numeric|min:1',
            'tipo_almacenamiento' => 'required|string|max:50',
            'condiciones_especiales' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Verify available capacity (excluding current storage)
            $total_almacenado = Almacenamiento::where('ubicacion', $validated['ubicacion'])
                ->where('id', '!=', $almacenamiento->id)
                ->sum('cantidad');

            if (($total_almacenado + $validated['cantidad']) > $validated['capacidad_maxima']) {
                throw new \Exception('La cantidad excede la capacidad máxima de la ubicación.');
            }

            // Update storage
            $almacenamiento->update($validated);

            DB::commit();

            return redirect()
                ->route('almacenamiento.index')
                ->with('success', 'Ubicación de almacenamiento actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Error al actualizar la ubicación. ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified storage location from storage.
     */
    public function destroy(Almacenamiento $almacenamiento)
    {
        try {
            $almacenamiento->delete();
            return redirect()
                ->route('almacenamiento.index')
                ->with('success', 'Ubicación de almacenamiento eliminada exitosamente.');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Error al eliminar la ubicación. ' . $e->getMessage()]);
        }
    }
}
