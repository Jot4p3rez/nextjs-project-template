<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mercaderia;
use App\Models\Despacho;
use App\Models\Inventario;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get dashboard statistics
        $stats = [
            'total_mercaderia' => Mercaderia::count(),
            'pendientes_despacho' => Despacho::where('estado', 'pendiente')->count(),
            'productos_bajos' => Inventario::where('cantidad', '<=', 'cantidad_minima')->count(),
            'despachos_hoy' => Despacho::whereDate('created_at', today())->count(),
        ];

        // Get recent activities
        $actividades_recientes = [
            'mercaderias' => Mercaderia::latest()->take(5)->get(),
            'despachos' => Despacho::latest()->take(5)->get(),
        ];

        // Get pending tasks
        $tareas_pendientes = Despacho::where('estado', 'pendiente')
            ->orderBy('fecha_programada')
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'actividades_recientes', 'tareas_pendientes'));
    }
}
