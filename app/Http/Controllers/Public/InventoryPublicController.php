<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\InventoryVehicle;
use App\Models\Lead;

class InventoryPublicController extends Controller
{
    public function index()
    {
        $vehicles = InventoryVehicle::with('images')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('public.estoque.index', [
            'vehicles' => $vehicles
        ]);
    }

    public function show($id)
    {
        $vehicle = InventoryVehicle::with('images')->findOrFail($id);

        return view('public.estoque.show', [
            'vehicle' => $vehicle
        ]);
    }

    public function interesse(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:inventory_vehicles,id',
            'nome' => 'required|string|max:255',
            'telefone' => 'required|string|max:50',
            'mensagem' => 'nullable|string'
        ]);

        Lead::create([
            'tenant_id' => 1,
            'nome' => $request->nome,
            'telefone' => $request->telefone,
            'origem' => 'site_teste',
            'dados_extras' => json_encode([
                'vehicle_id' => $request->vehicle_id,
                'mensagem' => $request->mensagem
            ])
        ]);

        return redirect()
            ->route('estoque.show', $request->vehicle_id)
            ->with('success', 'Interesse enviado com sucesso!');
    }
}
