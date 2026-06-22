@extends('layouts.admin')

@section('title', 'Physical Counts')
@section('breadcrumb', 'Physical Counts')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Physical Counts (Stock Opname)</h5>
                @canWrite
                    <a href="{{ route('physical-counts.create') }}" class="btn btn-primary">
                        <i data-feather="plus"></i> Mulai Hitung
                    </a>
                @endcanWrite
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No. Count</th>
                            <th>Gudang</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($physicalCounts as $physicalCount)
                            <tr style="cursor:pointer" onclick="window.location='{{ route('physical-counts.show', $physicalCount) }}'">
                                <td>{{ $physicalCount->count_no }}</td>
                                <td>{{ $physicalCount->warehouse->name }}</td>
                                <td>{{ $physicalCount->count_date->format('d/m/Y') }}</td>
                                <td><span class="badge bg-{{ $physicalCount->status_color }}">{{ $physicalCount->status_label }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Belum ada physical count.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $physicalCounts->links() }}
        </div>
    </div>
@endsection
