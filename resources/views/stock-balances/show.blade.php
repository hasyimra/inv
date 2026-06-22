@extends('layouts.admin')

@section('title', $stockBalance->item->description)
@section('breadcrumb', $stockBalance->item->description)

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <table class="table table-borderless mb-0">
                <tr><td class="text-muted" style="width:160px">Item</td><td>{{ $stockBalance->item->item_no }} - {{ $stockBalance->item->description }}</td></tr>
                <tr><td class="text-muted">Gudang</td><td>{{ $stockBalance->warehouse->name }}</td></tr>
                <tr><td class="text-muted">Qty On Hand</td><td>{{ rtrim(rtrim(number_format($stockBalance->qty_on_hand, 4), '0'), '.') }}</td></tr>
                <tr><td class="text-muted">Unit Cost (Weighted Avg)</td><td>{{ number_format($stockBalance->unit_cost, 4) }}</td></tr>
                <tr><td class="text-muted">Nilai Persediaan</td><td>{{ number_format($stockBalance->qty_on_hand * $stockBalance->unit_cost, 2) }}</td></tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h6>Riwayat Pergerakan Stok</h6>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr><th>Tanggal</th><th>Tipe</th><th>Qty</th><th>Unit Cost</th><th>Sumber</th></tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $movement)
                            <tr>
                                <td>{{ $movement->moved_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $movement->type_label }}</td>
                                <td class="{{ $movement->qty < 0 ? 'text-danger' : 'text-success' }}">{{ $movement->qty > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($movement->qty, 4), '0'), '.') }}</td>
                                <td>{{ number_format($movement->unit_cost, 4) }}</td>
                                <td>{{ $movement->source_type }} #{{ $movement->source_id }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Belum ada pergerakan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $movements->links() }}
        </div>
    </div>
@endsection
