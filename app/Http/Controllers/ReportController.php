<?php

namespace App\Http\Controllers;

use App\Models\InvAdjustment;
use App\Models\InvPhysicalCount;
use App\Models\InvStockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function stockMovementHistory(Request $request): View
    {
        $dateFrom = $request->date('date_from') ?? now()->startOfMonth();
        $dateTo = $request->date('date_to') ?? now()->endOfMonth();
        $warehouseId = $request->integer('warehouse_id') ?: null;

        $movements = InvStockMovement::with('item', 'warehouse')
            ->whereBetween('moved_at', [$dateFrom, $dateTo->copy()->endOfDay()])
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->orderBy('moved_at')
            ->get();

        $byWarehouse = $movements->groupBy('warehouse_id')->map(fn ($group) => [
            'warehouse' => $group->first()->warehouse,
            'movements' => $group->values(),
        ])->sortBy(fn ($row) => $row['warehouse']->name)->values();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('reports.stock-movement-history', compact('byWarehouse', 'dateFrom', 'dateTo', 'warehouses', 'warehouseId'));
    }

    public function adjustmentCountHistory(Request $request): View
    {
        $dateFrom = $request->date('date_from') ?? now()->startOfMonth();
        $dateTo = $request->date('date_to') ?? now()->endOfMonth();

        $adjustmentRows = InvAdjustment::with('warehouse')->withCount('lines')
            ->where('status', 'selesai')
            ->whereBetween('adjustment_date', [$dateFrom, $dateTo])
            ->get()
            ->map(fn (InvAdjustment $a) => [
                'warehouse_id' => $a->warehouse_id,
                'warehouse' => $a->warehouse,
                'type' => 'adjustment',
                'type_label' => 'Adjustment',
                'no' => $a->adjustment_no,
                'date' => $a->adjustment_date,
                'lines_count' => $a->lines_count,
                'route' => route('adjustments.show', $a),
            ]);

        $countRows = InvPhysicalCount::with('warehouse')->withCount('lines')
            ->where('status', 'selesai')
            ->whereBetween('count_date', [$dateFrom, $dateTo])
            ->get()
            ->map(fn (InvPhysicalCount $c) => [
                'warehouse_id' => $c->warehouse_id,
                'warehouse' => $c->warehouse,
                'type' => 'count',
                'type_label' => 'Physical Count',
                'no' => $c->count_no,
                'date' => $c->count_date,
                'lines_count' => $c->lines_count,
                'route' => route('physical-counts.show', $c),
            ]);

        $rows = $adjustmentRows->concat($countRows);

        $byWarehouse = $rows->groupBy('warehouse_id')->map(fn ($group) => [
            'warehouse' => $group->first()['warehouse'],
            'rows' => $group->sortBy('date')->values(),
        ])->sortBy(fn ($row) => $row['warehouse']->name)->values();

        return view('reports.adjustment-count-history', compact('byWarehouse', 'dateFrom', 'dateTo'));
    }
}
