<?php

namespace App\Http\Controllers;

use App\Models\InvAdjustment;
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

class InvAdjustmentController extends Controller
{
    public function index(): View
    {
        $adjustments = InvAdjustment::with('warehouse')->orderByDesc('adjustment_date')->paginate(20);

        return view('adjustments.index', compact('adjustments'));
    }

    public function create(): View
    {
        return view('adjustments.form', [
            'adjustment' => new InvAdjustment(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'items' => Item::where('is_active', true)->orderBy('description')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $adjustment = DB::transaction(function () use ($data) {
            $adjustment = InvAdjustment::create([
                'adjustment_no' => app(AutoNumberService::class)->generate('adjustment'),
                'warehouse_id' => $data['warehouse_id'],
                'adjustment_date' => $data['adjustment_date'],
                'reason' => $data['reason'] ?? null,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            $adjustment->lines()->createMany($data['lines']);

            return $adjustment;
        });

        return redirect()->route('adjustments.show', $adjustment)->with('success', 'Adjustment berhasil dibuat.');
    }

    public function show(InvAdjustment $adjustment): View
    {
        $adjustment->load(['warehouse', 'lines.item', 'createdBy', 'approvedBy']);

        return view('adjustments.show', compact('adjustment'));
    }

    public function destroy(InvAdjustment $adjustment): RedirectResponse
    {
        abort_if($adjustment->status !== 'draft', 403, 'Hanya adjustment draft yang bisa dihapus.');

        $adjustment->delete();

        return redirect()->route('adjustments.index')->with('success', 'Adjustment berhasil dihapus.');
    }

    public function submit(InvAdjustment $adjustment): RedirectResponse
    {
        abort_if($adjustment->status !== 'draft', 403);

        $adjustment->update(['status' => 'diajukan', 'updated_by' => Auth::id()]);

        return back()->with('success', 'Adjustment diajukan untuk approval.');
    }

    public function approve(InvAdjustment $adjustment): RedirectResponse
    {
        abort_if($adjustment->status !== 'diajukan', 403);

        DB::transaction(function () use ($adjustment) {
            $adjustment->load('lines');

            foreach ($adjustment->lines as $line) {
                if ((float) $line->qty_adjusted === 0.0) {
                    continue;
                }

                InvStockMovement::create([
                    'item_id' => $line->item_id,
                    'warehouse_id' => $adjustment->warehouse_id,
                    'qty' => $line->qty_adjusted,
                    'type' => 'adjustment',
                    'source_type' => 'inv_adjustment',
                    'source_id' => $adjustment->id,
                    'moved_at' => now(),
                ]);

                $balance = InvStockBalance::firstOrCreate(
                    ['item_id' => $line->item_id, 'warehouse_id' => $adjustment->warehouse_id],
                    ['qty_on_hand' => 0, 'unit_cost' => 0]
                );
                $balance->increment('qty_on_hand', $line->qty_adjusted);
            }

            // Adjustment langsung dieksekusi saat approve (tidak ada langkah lanjutan setelahnya, beda dari modul lain).
            $adjustment->update([
                'status' => 'selesai',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        });

        return back()->with('success', 'Adjustment disetujui dan stok telah diperbarui.');
    }

    public function reject(InvAdjustment $adjustment): RedirectResponse
    {
        abort_if($adjustment->status !== 'diajukan', 403);

        $adjustment->update(['status' => 'ditolak', 'approved_by' => Auth::id(), 'approved_at' => now()]);

        return back()->with('success', 'Adjustment ditolak.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'adjustment_date' => 'required|date',
            'reason' => 'nullable|string|max:255',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.qty_adjusted' => 'required|numeric',
            'lines.*.notes' => 'nullable|string|max:255',
        ]);
    }
}
