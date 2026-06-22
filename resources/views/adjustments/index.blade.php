@extends('layouts.admin')

@section('title', 'Adjustments')
@section('breadcrumb', 'Adjustments')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Stock Adjustments</h5>
                @canWrite
                    <a href="{{ route('adjustments.create') }}" class="btn btn-primary">
                        <i data-feather="plus"></i> Buat Adjustment
                    </a>
                @endcanWrite
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No. Adjustment</th>
                            <th>Gudang</th>
                            <th>Tanggal</th>
                            <th>Alasan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adjustments as $adjustment)
                            <tr style="cursor:pointer" onclick="window.location='{{ route('adjustments.show', $adjustment) }}'">
                                <td>{{ $adjustment->adjustment_no }}</td>
                                <td>{{ $adjustment->warehouse->name }}</td>
                                <td>{{ $adjustment->adjustment_date->format('d/m/Y') }}</td>
                                <td>{{ $adjustment->reason ?? '-' }}</td>
                                <td><span class="badge bg-{{ $adjustment->status_color }}">{{ $adjustment->status_label }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Belum ada adjustment.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $adjustments->links() }}
        </div>
    </div>
@endsection
