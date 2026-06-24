@extends('layouts.admin')

@section('title', $physicalCount->count_no)
@section('breadcrumb', $physicalCount->count_no)

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $physicalCount->count_no }}</h5>
                        <div>
                            <a href="{{ route('physical-counts.count-sheet', $physicalCount) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Cetak Count Sheet</a>
                            <span class="badge bg-{{ $physicalCount->status_color }}">{{ $physicalCount->status_label }}</span>
                        </div>
                    </div>
                    <table class="table table-borderless mb-0">
                        <tr><td class="text-muted" style="width:160px">Gudang</td><td>{{ $physicalCount->warehouse->name }}</td></tr>
                        <tr><td class="text-muted">Tanggal</td><td>{{ $physicalCount->count_date->format('d/m/Y') }}</td></tr>
                        <tr><td class="text-muted">Catatan</td><td>{{ $physicalCount->notes ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Dibuat oleh</td><td>{{ $physicalCount->createdBy?->name ?? '-' }}</td></tr>
                        @if($physicalCount->approved_by)
                            <tr><td class="text-muted">Diproses oleh</td><td>{{ $physicalCount->approvedBy?->name }} pada {{ $physicalCount->approved_at?->format('d/m/Y H:i') }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h6>Item</h6>
                    <table class="table">
                        <thead>
                            <tr><th>Item</th><th>System Qty</th><th>Counted Qty</th><th>Variance</th></tr>
                        </thead>
                        <tbody>
                            @foreach($physicalCount->lines as $line)
                                <tr>
                                    <td>{{ $line->item->item_no }} - {{ $line->item->description }}</td>
                                    <td>{{ rtrim(rtrim(number_format($line->system_qty, 4), '0'), '.') }}</td>
                                    <td>{{ $line->counted_qty === null ? '-' : rtrim(rtrim(number_format($line->counted_qty, 4), '0'), '.') }}</td>
                                    <td class="{{ $line->variance < 0 ? 'text-danger' : ($line->variance > 0 ? 'text-success' : '') }}">
                                        {{ $line->variance === null ? '-' : (($line->variance > 0 ? '+' : '').rtrim(rtrim(number_format($line->variance, 4), '0'), '.')) }}
                                    </td>
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

                    @if($physicalCount->status === 'draft')
                        @canWrite
                            <a href="{{ route('physical-counts.edit', $physicalCount) }}" class="btn btn-outline-secondary">Isi Hasil Hitung</a>
                            <form method="POST" action="{{ route('physical-counts.submit', $physicalCount) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100">Ajukan untuk Approval</button>
                            </form>
                            <form method="POST" action="{{ route('physical-counts.destroy', $physicalCount) }}" onsubmit="return confirm('Hapus physical count ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100">Hapus</button>
                            </form>
                        @endcanWrite
                    @endif

                    @if($physicalCount->status === 'diajukan')
                        @canApprove
                            <form method="POST" action="{{ route('physical-counts.approve', $physicalCount) }}">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">Setujui (terapkan varian ke stok)</button>
                            </form>
                            <form method="POST" action="{{ route('physical-counts.reject', $physicalCount) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100">Tolak</button>
                            </form>
                        @endcanApprove
                    @endif

                    <a href="{{ route('physical-counts.index') }}" class="btn btn-link">&larr; Kembali ke daftar</a>
                </div>
            </div>
        </div>
    </div>
@endsection
