@extends('layouts.admin')

@section('title', 'Stock Balances')
@section('breadcrumb', 'Stock Balances')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="GET" class="d-flex gap-2 mb-3">
                <select name="warehouse_id" class="form-select" style="width:auto" onchange="this.form.submit()">
                    <option value="">- Semua Gudang -</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected($selectedWarehouseId === $warehouse->id)>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </form>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Gudang</th>
                            <th>Qty On Hand</th>
                            <th>Unit Cost</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($balances as $balance)
                            <tr style="cursor:pointer" onclick="window.location='{{ route('stock-balances.show', $balance) }}'">
                                <td>{{ $balance->item->item_no }} - {{ $balance->item->description }}</td>
                                <td>{{ $balance->warehouse->name }}</td>
                                <td class="{{ $balance->qty_on_hand < 0 ? 'text-danger' : '' }}">{{ rtrim(rtrim(number_format($balance->qty_on_hand, 4), '0'), '.') }}</td>
                                <td>{{ number_format($balance->unit_cost, 4) }}</td>
                                <td>{{ number_format($balance->qty_on_hand * $balance->unit_cost, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Belum ada saldo stok tercatat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
