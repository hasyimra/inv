@extends('layouts.admin')

@section('title', 'Adjustment / Count History')
@section('breadcrumb', 'Adjustment / Count History')

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
                                    <th>No. Dokumen</th>
                                    <th>Tipe</th>
                                    <th>Tanggal</th>
                                    <th class="text-end">Jumlah Baris</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($row['rows'] as $document)
                                    <tr style="cursor:pointer" onclick="window.location='{{ $document['route'] }}'">
                                        <td>{{ $document['no'] }}</td>
                                        <td>{{ $document['type_label'] }}</td>
                                        <td>{{ $document['date']->format('d/m/Y') }}</td>
                                        <td class="text-end">{{ $document['lines_count'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <p class="text-center text-muted">Tidak ada adjustment/physical count selesai di rentang tanggal ini.</p>
            @endforelse
        </div>
    </div>
@endsection
