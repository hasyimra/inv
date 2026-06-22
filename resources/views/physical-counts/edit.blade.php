@extends('layouts.admin')

@section('title', 'Isi Hasil Hitung — ' . $physicalCount->count_no)
@section('breadcrumb', $physicalCount->count_no)

@section('content')
    <div class="card">
        <div class="card-body">
            <p class="mb-3">Gudang <strong>{{ $physicalCount->warehouse->name }}</strong> — Tanggal {{ $physicalCount->count_date->format('d/m/Y') }}</p>

            <form method="POST" action="{{ route('physical-counts.update', $physicalCount) }}">
                @csrf @method('PUT')

                <table class="table">
                    <thead>
                        <tr><th>Item</th><th>System Qty</th><th style="width:20%">Counted Qty</th></tr>
                    </thead>
                    <tbody>
                        @foreach($physicalCount->lines as $line)
                            <tr>
                                <td>
                                    {{ $line->item->item_no }} - {{ $line->item->description }}
                                    <input type="hidden" name="lines[{{ $loop->index }}][id]" value="{{ $line->id }}">
                                </td>
                                <td>{{ rtrim(rtrim(number_format($line->system_qty, 4), '0'), '.') }}</td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="lines[{{ $loop->index }}][counted_qty]" class="form-control" value="{{ $line->counted_qty }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <button type="submit" class="btn btn-primary">Simpan Hasil Hitung</button>
                <a href="{{ route('physical-counts.show', $physicalCount) }}" class="btn btn-outline-secondary">Batal</a>
            </form>
        </div>
    </div>
@endsection
