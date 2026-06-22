@extends('layouts.admin')

@section('title', $adjustment->adjustment_no)
@section('breadcrumb', $adjustment->adjustment_no)

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h5>{{ $adjustment->adjustment_no }}</h5>
                        <span class="badge bg-{{ $adjustment->status_color }}">{{ $adjustment->status_label }}</span>
                    </div>
                    <table class="table table-borderless mb-0">
                        <tr><td class="text-muted" style="width:160px">Gudang</td><td>{{ $adjustment->warehouse->name }}</td></tr>
                        <tr><td class="text-muted">Tanggal</td><td>{{ $adjustment->adjustment_date->format('d/m/Y') }}</td></tr>
                        <tr><td class="text-muted">Alasan</td><td>{{ $adjustment->reason ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Dibuat oleh</td><td>{{ $adjustment->createdBy?->name ?? '-' }}</td></tr>
                        @if($adjustment->approved_by)
                            <tr><td class="text-muted">Diproses oleh</td><td>{{ $adjustment->approvedBy?->name }} pada {{ $adjustment->approved_at?->format('d/m/Y H:i') }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h6>Item</h6>
                    <table class="table">
                        <thead>
                            <tr><th>Item</th><th>Qty Adjustment</th><th>Catatan</th></tr>
                        </thead>
                        <tbody>
                            @foreach($adjustment->lines as $line)
                                <tr>
                                    <td>{{ $line->item->item_no }} - {{ $line->item->description }}</td>
                                    <td class="{{ $line->qty_adjusted < 0 ? 'text-danger' : 'text-success' }}">{{ $line->qty_adjusted > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($line->qty_adjusted, 4), '0'), '.') }}</td>
                                    <td>{{ $line->notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body d-flex flex-column gap-2">
                    <h6>Aksi</h6>

                    @if($adjustment->status === 'draft')
                        @canWrite
                            <form method="POST" action="{{ route('adjustments.submit', $adjustment) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100">Ajukan untuk Approval</button>
                            </form>
                            <form method="POST" action="{{ route('adjustments.destroy', $adjustment) }}" onsubmit="return confirm('Hapus adjustment ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100">Hapus</button>
                            </form>
                        @endcanWrite
                    @endif

                    @if($adjustment->status === 'diajukan')
                        @canApprove
                            <form method="POST" action="{{ route('adjustments.approve', $adjustment) }}">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">Setujui (terapkan ke stok)</button>
                            </form>
                            <form method="POST" action="{{ route('adjustments.reject', $adjustment) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100">Tolak</button>
                            </form>
                        @endcanApprove
                    @endif

                    <a href="{{ route('adjustments.index') }}" class="btn btn-link">&larr; Kembali ke daftar</a>
                </div>
            </div>
        </div>
    </div>
@endsection
