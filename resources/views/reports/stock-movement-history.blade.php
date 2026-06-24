@extends('layouts.admin')

@section('title', 'Stock Movement History')
@section('breadcrumb', 'Stock Movement History')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <select name="warehouse_id" class="form-select">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected($warehouseId == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-secondary">Filter</button>
                </div>
            </form>

            @forelse($byWarehouse as $row)
                <div class="mb-4">
                    <h6 class="mb-2">{{ $row['warehouse']->name }}</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Item</th>
                                    <th>Tipe</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Unit Cost</th>
                                    <th>Sumber</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($row['movements'] as $movement)
                                    <tr>
                                        <td>{{ $movement->moved_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $movement->item->item_no }} - {{ $movement->item->description }}</td>
                                        <td>{{ $movement->type_label }}</td>
                                        <td class="text-end {{ $movement->qty < 0 ? 'text-danger' : 'text-success' }}">
                                            {{ $movement->qty > 0 ? '+' : '' }}{{ number_format($movement->qty, 2) }}
                                        </td>
                                        <td class="text-end">{{ number_format($movement->unit_cost, 2) }}</td>
                                        <td>{{ $movement->source_type }} #{{ $movement->source_id }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <p class="text-center text-muted">Tidak ada mutasi stok di rentang tanggal ini.</p>
            @endforelse
        </div>
    </div>
@endsection
