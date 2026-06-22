@extends('layouts.admin')

@section('title', 'Mulai Physical Count')
@section('breadcrumb', 'Mulai Physical Count')

@section('content')
    <div class="card">
        <div class="card-body">
            <p class="text-muted">Sistem akan snapshot saldo stok saat ini untuk semua item inventory aktif di gudang yang dipilih. Anda bisa isi qty hasil hitung fisik setelah ini dibuat.</p>
            <form method="POST" action="{{ route('physical-counts.store') }}">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Gudang</label>
                        <select name="warehouse_id" class="form-select" required>
                            <option value="">- Pilih Gudang -</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Hitung</label>
                        <input type="date" name="count_date" class="form-control" required value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Catatan</label>
                        <input type="text" name="notes" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Mulai Hitung</button>
                <a href="{{ route('physical-counts.index') }}" class="btn btn-outline-secondary">Batal</a>
            </form>
        </div>
    </div>
@endsection
