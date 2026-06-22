<?php

namespace App\Http\Controllers;

use App\Models\InvStockBalance;
use App\Models\InvStockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockBalanceController extends Controller
{
    public function index(Request $request): View
    {
        $balances = InvStockBalance::with('item', 'warehouse')
            ->when($request->warehouse_id, fn ($q, $warehouseId) => $q->where('warehouse_id', $warehouseId))
            ->whereHas('item')
            ->orderBy('warehouse_id')
            ->get()
            ->sortBy(fn (InvStockBalance $balance) => $balance->item->description);

        return view('stock-balances.index', [
            'balances' => $balances,
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'selectedWarehouseId' => $request->integer('warehouse_id') ?: null,
        ]);
    }

    public function show(InvStockBalance $stockBalance): View
    {
        $stockBalance->load('item', 'warehouse');

        $movements = InvStockMovement::where('item_id', $stockBalance->item_id)
            ->where('warehouse_id', $stockBalance->warehouse_id)
            ->orderByDesc('moved_at')
            ->paginate(20);

        return view('stock-balances.show', compact('stockBalance', 'movements'));
    }
}
