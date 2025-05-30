<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Almacenamiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    /**
     * Display a listing of inventory items.
     */
    public function index()
    {
        $inventarios = Inventario::with(['almacenamientos'])
            ->withCount(['mercaderias', 'despachos'])
            ->paginate(10);

        return view('inventario.index', compact('inventarios'));
    }

    /**
     * Show the form for creating a new inventory item.
     */
    public function create()
    {
        return view('inventario.create');
    }

    /**
     * Store a newly created inventory item in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'producto' => 'required|string|max:255|unique:inventarios',
            'descripcion' => 'required|string',
            'cantidad' => 'required|numeric|min:0',
            'cantidad_minima' => 'required|numeric|min:0',
            'unidad_medida' => 'required|string|max:50',
            'categoria' => 'required|string|max:100',
            'codigo_producto' => 'required|string|max:50|unique:inventarios',
        ]);

        try {
            $inventario = Inventario::create($validated);

            return redirect()
                ->route('inventario.index')
                ->with('success', 'Producto agregado al inventario exitosamente.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Error al agregar el producto al inventario. ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified inventory item.
     */
    public function show(Inventario $inventario)
    {
        $inventario->load(['almacenamientos', 'mercaderias', 'despachos']);
        
        // Get movement history
        $movimientos = DB::table(DB::raw('
            (SELECT fecha_ingreso as fecha, cantidad, "ingreso" as tipo 
             FROM mercaderias 
             WHERE producto_id = :producto_id1
             UNION ALL
             SELECT fecha_salida as fecha, cantidad * -1, "salida" 
             FROM despachos 
             WHERE producto_id = :producto_id2) as movimientos
        '))
        ->setBindings(['producto_id1' => $inventario->id, 'producto_id2' => $inventario->id])
        ->orderBy('fecha', 'desc')
        ->get();

        return view('inventario.show', compact('inventario', 'movimientos'));
    }

    /**
     * Show the form for editing the specified inventory item.
     */
    public function edit(Inventario $inventario)
    {
        return view('inventario.edit', compact('inventario'));
    }

    /**
     * Update the specified inventory item in storage.
     */
    public function update(Request $request, Inventario $inventario)
    {
        $validated = $request->validate([
            'producto' => 'required|string|max:255|unique:inventarios,producto,' . $inventario->id,
            'descripcion' => 'required|string',
            'cantidad' => 'required|numeric|min:0',
            'cantidad_minima' => 'required|numeric|min:0',
            'unidad_medida' => 'required|string|max:50',
            'categoria' => 'required|string|max:100',
            'codigo_producto' => 'required|string|max:50|unique:inventarios,codigo_producto,' . $inventario->id,
        ]);

        try {
            $inventario->update($validated);

            return redirect()
                ->route('inventario.index')
                ->with('success', 'Producto actualizado exitosamente.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Error al actualizar el producto. ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified inventory item from storage.
     */
    public function destroy(Inventario $inventario)
    {
        try {
            // Check if there are related records
            if ($inventario->mercaderias()->exists() || 
                $inventario->despachos()->exists() || 
                $inventario->almacenamientos()->exists()) {
                throw new \Exception('No se puede eliminar el producto porque tiene registros relacionados.');
            }

            $inventario->delete();

            return redirect()
                ->route('inventario.index')
                ->with('success', 'Producto eliminado exitosamente.');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Error al eliminar el producto. ' . $e->getMessage()]);
        }
    }

    /**
     * Display inventory alerts.
     */
    public function alerts()
    {
        $alertas = Inventario::where('cantidad', '<=', DB::raw('cantidad_minima'))
            ->get();

        return view('inventario.alerts', compact('alertas'));
    }

    /**
     * Generate inventory report.
     */
    public function report(Request $request)
    {
        $query = Inventario::query();

        // Apply filters
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('stock_bajo')) {
            $query->where('cantidad', '<=', DB::raw('cantidad_minima'));
        }

        $inventarios = $query->get();

        return view('inventario.report', compact('inventarios'));
    }
}
