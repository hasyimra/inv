@extends('layouts.admin')

@section('title', 'Buat Adjustment')
@section('breadcrumb', 'Buat Adjustment')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('adjustments.store') }}">
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
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="adjustment_date" class="form-control" required value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Alasan</label>
                        <input type="text" name="reason" class="form-control">
                    </div>
                </div>

                <h6>Item</h6>
                <p class="text-muted small">Qty boleh negatif (mengurangi stok) atau positif (menambah stok).</p>
                <table class="table" id="line-table">
                    <thead>
                        <tr>
                            <th style="width:35%">Item</th>
                            <th style="width:20%">Qty Adjustment</th>
                            <th style="width:35%">Catatan</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <button type="button" id="add-row" class="btn btn-sm btn-outline-secondary mb-3">
                    <i data-feather="plus"></i> Tambah Baris
                </button>

                <div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('adjustments.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <template id="line-row-template">
        <tr class="line-row">
            <td>
                <select name="lines[][item_id]" class="form-select" required>
                    <option value="">- Pilih Item -</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->item_no }} - {{ $item->description }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" step="0.01" name="lines[][qty_adjusted]" class="form-control" value="0" required></td>
            <td><input type="text" name="lines[][notes]" class="form-control" placeholder="(opsional)"></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i data-feather="trash-2"></i></button></td>
        </tr>
    </template>

    @push('scripts')
    <script>
        document.getElementById('add-row').addEventListener('click', function () {
            const template = document.getElementById('line-row-template').content.cloneNode(true);
            document.querySelector('#line-table tbody').appendChild(template);
            if (window.feather) feather.replace();
        });
        document.getElementById('line-table').addEventListener('click', function (e) {
            if (e.target.closest('.remove-row')) {
                e.target.closest('.line-row').remove();
            }
        });
        document.getElementById('add-row').click();
    </script>
    @endpush
@endsection
