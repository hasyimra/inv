<?php

namespace App\Http\Controllers;

use App\Models\GlJournal;
use App\Models\GlSetting;
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
            $adjustment->load('lines.item');

            $costs = [];

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
                $costs[$line->id] = (float) $balance->unit_cost;
                $balance->increment('qty_on_hand', $line->qty_adjusted);
            }

            $this->postAdjustmentJournal($adjustment, $costs);

            // Adjustment langsung dieksekusi saat approve (tidak ada langkah lanjutan setelahnya, beda dari modul lain).
            $adjustment->update([
                'status' => 'selesai',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        });

        return back()->with('success', 'Adjustment disetujui dan stok telah diperbarui.');
    }

    /**
     * Dr Persediaan / Cr Selisih Persediaan untuk kenaikan stok (qty_adjusted positif),
     * dibalik untuk penurunan. Amount = abs(qty_adjusted) x unit_cost SAAT approval — diambil
     * dari $costs (di-capture sebelum increment di loop atas; unit_cost weighted-average
     * sendiri tidak diubah oleh adjustment, hanya qty_on_hand, jadi nilainya sama saja, tapi
     * tetap diambil sebelum demi konsistensi kalau logic itu berubah nanti).
     */
    private function postAdjustmentJournal(InvAdjustment $adjustment, array $costs): void
    {
        $varianceAccountId = GlSetting::where('key', 'inventory_adjustment')->first()?->gl_account_id;

        $lines = [];

        foreach ($adjustment->lines as $line) {
            $qty = (float) $line->qty_adjusted;
            $unitCost = $costs[$line->id] ?? null;
            if ($qty === 0.0 || $unitCost === null) {
                continue;
            }

            $amount = round(abs($qty) * $unitCost, 2);
            if ($amount <= 0) {
                continue;
            }

            $inventoryAccountId = $line->item->inventory_gl_account_id;
            if (! $inventoryAccountId) {
                throw new \RuntimeException("Item {$line->item->description} belum punya GL Account Persediaan, lengkapi dulu di master item.");
            }
            if (! $varianceAccountId) {
                throw new \RuntimeException('GL Account untuk Selisih Persediaan belum diatur di GL Settings.');
            }

            $description = 'Adjustment - '.($line->item->description ?? '');

            if ($qty > 0) {
                $lines[] = ['gl_account_id' => $inventoryAccountId, 'debit' => $amount, 'credit' => 0, 'description' => $description];
                $lines[] = ['gl_account_id' => $varianceAccountId, 'debit' => 0, 'credit' => $amount, 'description' => $description];
            } else {
                $lines[] = ['gl_account_id' => $varianceAccountId, 'debit' => $amount, 'credit' => 0, 'description' => $description];
                $lines[] = ['gl_account_id' => $inventoryAccountId, 'debit' => 0, 'credit' => $amount, 'description' => $description];
            }
        }

        if (empty($lines)) {
            return;
        }

        GlJournal::postBalanced([
            'journal_date' => $adjustment->adjustment_date,
            'description' => 'Inventory Adjustment '.$adjustment->adjustment_no,
            'source_type' => 'inv_adjustment',
            'source_id' => $adjustment->id,
            'created_by' => Auth::id(),
        ], $lines);
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
