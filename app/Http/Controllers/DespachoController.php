<?php

namespace App\Http\Controllers;

use App\Models\Despacho;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DespachoController extends Controller
{
    /**
     * Display a listing of dispatches.
     */
    public function index()
    {
        $despachos = Despacho::with(['inventario'])
            ->latest()
            ->paginate(10);

        return view('despacho.index', compact('despachos'));
    }

    /**
     * Show the form for creating a new dispatch.
     */
    public function create()
    {
        $productos = Inventario::where('cantidad', '>', 0)
            ->pluck('producto', 'id');
            
        return view('despacho.create', compact('productos'));
    }

    /**
     * Store a newly created dispatch in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:inventarios,id',
            'cantidad' => 'required|numeric|min:1',
            'cliente' => 'required|string|max:255',
            'fecha_programada' => 'required|date',
            'direccion_entrega' => 'required|string',
            'numero_orden' => 'required|string|max:50|unique:despachos',
            'estado' => 'required|in:pendiente,en_proceso,completado,cancelado',
            'prioridad' => 'required|in:baja,media,alta,urgente',
            'observaciones' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Check inventory availability
            $inventario = Inventario::findOrFail($validated['producto_id']);
            if ($inventario->cantidad < $validated['cantidad']) {
                throw new \Exception('No hay suficiente stock disponible.');
            }

            // Create dispatch record
            $despacho = Despacho::create($validated);

            // Update inventory
            $inventario->decrement('cantidad', $validated['cantidad']);

            DB::commit();

            return redirect()
                ->route('despacho.index')
                ->with('success', 'Despacho creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Error al crear el despacho. ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified dispatch.
     */
    public function show(Despacho $despacho)
    {
        $despacho->load('inventario');
        return view('despacho.show', compact('despacho'));
    }

    /**
     * Show the form for editing the specified dispatch.
     */
    public function edit(Despacho $despacho)
    {
        $productos = Inventario::pluck('producto', 'id');
        return view('despacho.edit', compact('despacho', 'productos'));
    }

    /**
     * Update the specified dispatch in storage.
     */
    public function update(Request $request, Despacho $despacho)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:inventarios,id',
            'cantidad' => 'required|numeric|min:1',
            'cliente' => 'required|string|max:255',
            'fecha_programada' => 'required|date',
            'direccion_entrega' => 'required|string',
            'numero_orden' => 'required|string|max:50|unique:despachos,numero_orden,' . $despacho->id,
            'estado' => 'required|in:pendiente,en_proceso,completado,cancelado',
            'prioridad' => 'required|in:baja,media,alta,urgente',
            'observaciones' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // If quantity or product changed, update inventory
            if ($despacho->producto_id != $validated['producto_id'] || 
                $despacho->cantidad != $validated['cantidad']) {
                
                // Return old quantity to inventory
                $oldInventario = Inventario::findOrFail($despacho->producto_id);
                $oldInventario->increment('cantidad', $despacho->cantidad);

                // Check and update new inventory
                $newInventario = Inventario::findOrFail($validated['producto_id']);
                if ($newInventario->cantidad < $validated['cantidad']) {
                    throw new \Exception('No hay suficiente stock disponible.');
                }
                $newInventario->decrement('cantidad', $validated['cantidad']);
            }

            // Update dispatch
            $despacho->update($validated);

            DB::commit();

            return redirect()
                ->route('despacho.index')
                ->with('success', 'Despacho actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Error al actualizar el despacho. ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified dispatch from storage.
     */
    public function destroy(Despacho $despacho)
    {
        try {
            DB::beginTransaction();

            // Return quantity to inventory
            $inventario = Inventario::findOrFail($despacho->producto_id);
            $inventario->increment('cantidad', $despacho->cantidad);

            // Delete dispatch
            $despacho->delete();

            DB::commit();

            return redirect()
                ->route('despacho.index')
                ->with('success', 'Despacho eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Error al eliminar el despacho. ' . $e->getMessage()]);
        }
    }

    /**
     * Update dispatch status.
     */
    public function updateStatus(Request $request, Despacho $despacho)
    {
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,en_proceso,completado,cancelado',
        ]);

        try {
            $despacho->update($validated);

            return redirect()
                ->route('despacho.show', $despacho)
                ->with('success', 'Estado del despacho actualizado exitosamente.');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Error al actualizar el estado del despacho. ' . $e->getMessage()]);
        }
    }
}
