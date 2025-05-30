<?php

namespace App\Http\Controllers;

use App\Models\Mercaderia;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MercaderiaController extends Controller
{
    /**
     * Display a listing of the mercaderia.
     */
    public function index()
    {
        $mercaderias = Mercaderia::latest()->paginate(10);
        return view('mercaderia.index', compact('mercaderias'));
    }

    /**
     * Show the form for creating a new mercaderia.
     */
    public function create()
    {
        return view('mercaderia.create');
    }

    /**
     * Store a newly created mercaderia in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'producto' => 'required|string|max:255',
            'cantidad' => 'required|numeric|min:1',
            'proveedor' => 'required|string|max:255',
            'fecha_ingreso' => 'required|date',
            'numero_guia' => 'required|string|max:50',
            'observaciones' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Create mercaderia record
            $mercaderia = Mercaderia::create($validated);

            // Update inventory
            $inventario = Inventario::firstOrCreate(
                ['producto' => $validated['producto']],
                ['cantidad' => 0]
            );
            
            $inventario->increment('cantidad', $validated['cantidad']);

            DB::commit();

            return redirect()
                ->route('mercaderia.index')
                ->with('success', 'Mercadería registrada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Error al registrar la mercadería. ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified mercaderia.
     */
    public function show(Mercaderia $mercaderia)
    {
        return view('mercaderia.show', compact('mercaderia'));
    }

    /**
     * Show the form for editing the specified mercaderia.
     */
    public function edit(Mercaderia $mercaderia)
    {
        return view('mercaderia.edit', compact('mercaderia'));
    }

    /**
     * Update the specified mercaderia in storage.
     */
    public function update(Request $request, Mercaderia $mercaderia)
    {
        $validated = $request->validate([
            'producto' => 'required|string|max:255',
            'cantidad' => 'required|numeric|min:1',
            'proveedor' => 'required|string|max:255',
            'fecha_ingreso' => 'required|date',
            'numero_guia' => 'required|string|max:50',
            'observaciones' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Calculate quantity difference
            $diferencia_cantidad = $validated['cantidad'] - $mercaderia->cantidad;

            // Update mercaderia
            $mercaderia->update($validated);

            // Update inventory if quantity changed
            if ($diferencia_cantidad != 0) {
                $inventario = Inventario::where('producto', $validated['producto'])->first();
                if ($inventario) {
                    $inventario->increment('cantidad', $diferencia_cantidad);
                }
            }

            DB::commit();

            return redirect()
                ->route('mercaderia.index')
                ->with('success', 'Mercadería actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Error al actualizar la mercadería. ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified mercaderia from storage.
     */
    public function destroy(Mercaderia $mercaderia)
    {
        try {
            DB::beginTransaction();

            // Update inventory before deleting
            $inventario = Inventario::where('producto', $mercaderia->producto)->first();
            if ($inventario) {
                $inventario->decrement('cantidad', $mercaderia->cantidad);
            }

            // Delete mercaderia
            $mercaderia->delete();

            DB::commit();

            return redirect()
                ->route('mercaderia.index')
                ->with('success', 'Mercadería eliminada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al eliminar la mercadería. ' . $e->getMessage()]);
        }
    }
}
