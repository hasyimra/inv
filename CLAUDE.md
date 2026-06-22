# inv — Aplikasi Inventory (Suite ERP DKM)

> Aplikasi Laravel berdiri sendiri untuk modul **Inventory**: Stock Balance, Adjustment, Physical Count (stock opname). App keempat dalam suite ERP DKM, setelah `sls`, `ar`, `prc`. Lihat `C:\Project\Web\sls\CLAUDE.md` untuk konteks suite secara umum dan `C:\Project\Web\erp-schema\MODULES-ROADMAP.md` untuk rencana modul lain.

## Cakupan — Lebih Sempit dari Rencana Awal

Scope `inv` **menyempit** dibanding rencana awal di `MODULES-ROADMAP.md`: karena `prc` sudah menggabungkan Purchase Order + Receiving (keputusan saat `prc` dibangun), `inv` **tidak** menangani penerimaan barang dari PO. Cakupan `inv` murni:
- **Stock Balance** — saldo qty + weighted-average cost per item+warehouse, read-only display dengan drill-down riwayat pergerakan.
- **Adjustment** — koreksi stok manual (qty bisa +/-), approval flow standar.
- **Physical Count** — "mulai hitung" (snapshot saldo sistem per item aktif `is_inventory` di gudang terpilih) → isi qty hasil hitung fisik → approve (varian otomatis jadi movement, saldo diperbarui). Tidak ada langkah "apply variances" terpisah seperti BS1 — sudah dilebur jadi bagian dari approval.

## Integrasi Lintas-App (Penting!)

**Ini app pertama di suite yang benar-benar terintegrasi dengan app lain secara real-time.** `inv_stock_balances`/`inv_stock_movements` dimiliki schema-nya oleh `inv`, tapi **ditulis juga oleh `prc` dan `ar`**:
- **`prc\ReceiptController::approve()`** — menulis movement masuk (`type='receipt'`) dan **mengubah weighted-average cost** balance saat Receipt disetujui.
- **`ar\ArInvoiceController::approve()`** — menulis movement keluar (`type='sale'`, qty negatif) saat Invoice disetujui (hanya kalau invoice punya `warehouse_id` — invoice billing jasa tanpa warehouse diabaikan total). **Tidak mengubah** weighted-average cost (cost cuma berubah saat barang masuk).
- Kedua app punya model `InvStockBalance`/`InvStockMovement`/`ItemType` sendiri (duplikasi kecil, bukan shared package — konsisten dengan setiap app di suite ini berdiri sendiri tanpa dependency lintas-app).
- Hanya item dengan `item_types.is_inventory = true` yang dapat movement — item non-inventory (jasa, dll) dilewati sama sekali.

**Keterbatasan yang disengaja (bukan terlewat):**
- **Tidak ada backfill retroaktif** — Receipt/Invoice yang sudah disetujui SEBELUM retrofit ini ditambahkan tidak akan punya movement. Hanya transaksi baru (setelah deploy ini) yang tercatat.
- **Stok negatif tidak diblokir** saat approve Invoice — ini kali pertama stok benar-benar dilacak, belum ada validasi cukup/tidak cukup stok.
- Tidak ada filter `item_type` saat "mulai count" (snapshot semua item `is_inventory` aktif) — cukup untuk skala katalog saat ini.
- Tidak ada `qty_committed`/"available vs on-hand" (BS1's `QtyShippedNotYetUpd`) — butuh hook ke status shipment `sls` yang belum dibangun keterkaitannya.

## Gotcha yang Ditemukan Saat Build Ini

**Bug nyata ditemukan & diperbaiki di `prc`** saat testing retrofit: `ReceiptController::store()` cuma validasi `prc_purchase_order_line_id` ada di tabel (`exists:prc_purchase_order_lines,id`), **tidak** mengecek baris itu benar-benar milik PO yang sedang dibuatkan receipt-nya. Diperbaiki dengan `Rule::exists(...)->where('prc_purchase_order_id', $purchaseOrder->id)`. Worth dicek pola serupa di app lain (`ar`'s payment allocation ke invoice, `ar`'s credit note ke invoice line) kalau ada waktu — belum diaudit menyeluruh.

## RBAC & Struktur

Identik dengan `sls`/`ar`/`prc`: role `sso_admin|admin|user|approval|viewer`, sidebar `@unless(isSsoAdmin())` dari awal, SSO lokal sungguhan dari awal (`INV_DEV_LOGIN_ENABLED=false`), icon SSO `https://img.icons8.com/fluency/96/warehouse.png` (diverifikasi sebelum dipakai), role default pre-seeded (`hasyim.ra`=admin, `subowo`=viewer, `asyari`/`ilham`=user). `AutoNumberService` prefix: `ADJ` (adjustment), `CNT` (physical count).

## Deployment

✅ **Live di production**: `https://inv.dkmapps.com`. Repo: `github.com/hasyimra/inv` + `github.com/Dharma-Karyatama-Mulia/inv`. Deploy ini juga me-redeploy `prc` dan `ar` (pull + cache refresh, **tanpa migration** di kedua app itu karena mereka tidak memiliki skema yang berubah — `inv` yang punya migrasinya).

## Status & Verifikasi

✅ Dicoba lokal via curl (SSO lokal sungguhan): Adjustment +50 → approve → balance jadi 50. Physical Count snapshot 50 → isi counted 45 → approve → balance jadi 45 (variance -5 diterapkan benar). Retrofit `prc`: Receipt 20 unit @70000 → approve → movement `receipt` tercatat, balance 45→65, weighted-avg cost terhitung benar `(45×0 + 20×70000)/65 = 21538.46`. Retrofit `ar`: Invoice 3 unit → approve → movement `sale` (-3) tercatat, balance 65→62, cost tidak berubah (tetap 21538.46).

⏳ Belum dicoba: tampilan visual di browser asli (baru HTTP/curl).
