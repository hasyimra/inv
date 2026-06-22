<?php

namespace App\Http\Controllers;

use App\Models\InvPhysicalCount;
use App\Models\InvStockBalance;
use App\Models\InvStockMovement;
use App\Models\Item;
use App\Models\Warehouse;
use App\Services\AutoNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PhysicalCountController extends Controller
{
    public function index(): View
    {
        $physicalCounts = InvPhysicalCount::with('warehouse')->orderByDesc('count_date')->paginate(20);

        return view('physical-counts.index', compact('physicalCounts'));
    }

    public function create(): View
    {
        return view('physical-counts.create', [
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'count_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $items = Item::where('is_active', true)
            ->whereHas('itemType', fn ($q) => $q->where('is_inventory', true))
            ->get();

        if ($items->isEmpty()) {
            return back()->with('error', 'Tidak ada item inventory aktif untuk dihitung.');
        }

        $balances = InvStockBalance::where('warehouse_id', $data['warehouse_id'])
            ->pluck('qty_on_hand', 'item_id');

        $physicalCount = DB::transaction(function () use ($data, $items, $balances) {
            $physicalCount = InvPhysicalCount::create([
                'count_no' => app(AutoNumberService::class)->generate('physical_count'),
                'warehouse_id' => $data['warehouse_id'],
                'count_date' => $data['count_date'],
                'notes' => $data['notes'] ?? null,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            $physicalCount->lines()->createMany(
                $items->map(fn (Item $item) => [
                    'item_id' => $item->id,
                    'system_qty' => $balances[$item->id] ?? 0,
                    'counted_qty' => null,
                ])->all()
            );

            return $physicalCount;
        });

        return redirect()->route('physical-counts.edit', $physicalCount)->with('success', 'Physical count dimulai. Silakan isi qty hasil hitung.');
    }

    public function edit(InvPhysicalCount $physicalCount): View
    {
        abort_if($physicalCount->status !== 'draft', 403, 'Hanya physical count draft yang bisa diisi.');

        $physicalCount->load('lines.item');

        return view('physical-counts.edit', compact('physicalCount'));
    }

    public function update(Request $request, InvPhysicalCount $physicalCount): RedirectResponse
    {
        abort_if($physicalCount->status !== 'draft', 403);

        $data = $request->validate([
            'lines' => 'required|array',
            'lines.*.id' => 'required|exists:inv_physical_count_lines,id',
            'lines.*.counted_qty' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($physicalCount, $data) {
            foreach ($data['lines'] as $line) {
                $physicalCount->lines()->where('id', $line['id'])->update([
                    'counted_qty' => $line['counted_qty'] ?? null,
                ]);
            }
            $physicalCount->update(['updated_by' => Auth::id()]);
        });

        return redirect()->route('physical-counts.show', $physicalCount)->with('success', 'Hasil hitung tersimpan.');
    }

    public function show(InvPhysicalCount $physicalCount): View
    {
        $physicalCount->load(['warehouse', 'lines.item', 'createdBy', 'approvedBy']);

        return view('physical-counts.show', compact('physicalCount'));
    }

    public function destroy(InvPhysicalCount $physicalCount): RedirectResponse
    {
        abort_if($physicalCount->status !== 'draft', 403, 'Hanya physical count draft yang bisa dihapus.');

        $physicalCount->delete();

        return redirect()->route('physical-counts.index')->with('success', 'Physical count berhasil dihapus.');
    }

    public function submit(InvPhysicalCount $physicalCount): RedirectResponse
    {
        abort_if($physicalCount->status !== 'draft', 403);

        $physicalCount->update(['status' => 'diajukan', 'updated_by' => Auth::id()]);

        return back()->with('success', 'Physical count diajukan untuk approval.');
    }

    public function approve(InvPhysicalCount $physicalCount): RedirectResponse
    {
        abort_if($physicalCount->status !== 'diajukan', 403);

        DB::transaction(function () use ($physicalCount) {
            $physicalCount->load('lines');

            foreach ($physicalCount->lines as $line) {
                if ($line->counted_qty === null || (float) $line->variance === 0.0) {
                    continue;
                }

                InvStockMovement::create([
                    'item_id' => $line->item_id,
                    'warehouse_id' => $physicalCount->warehouse_id,
                    'qty' => $line->variance,
                    'type' => 'adjustment',
                    'source_type' => 'inv_physical_count',
                    'source_id' => $physicalCount->id,
                    'moved_at' => now(),
                ]);

                $balance = InvStockBalance::firstOrCreate(
                    ['item_id' => $line->item_id, 'warehouse_id' => $physicalCount->warehouse_id],
                    ['qty_on_hand' => 0, 'unit_cost' => 0]
                );
                $balance->increment('qty_on_hand', $line->variance);
            }

            $physicalCount->update([
                'status' => 'selesai',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        });

        return back()->with('success', 'Physical count disetujui, varian sudah diterapkan ke stok.');
    }

    public function reject(InvPhysicalCount $physicalCount): RedirectResponse
    {
        abort_if($physicalCount->status !== 'diajukan', 403);

        $physicalCount->update(['status' => 'ditolak', 'approved_by' => Auth::id(), 'approved_at' => now()]);

        return back()->with('success', 'Physical count ditolak.');
    }
}
