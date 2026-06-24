@extends('layouts.print')

@section('title', 'Count Sheet ' . $physicalCount->count_no)
@section('doc-title', 'COUNT SHEET')
@section('doc-no', $physicalCount->count_no)

@section('content')
    <table class="info">
        <tr>
            <td><strong>Gudang</strong></td>
            <td>{{ $physicalCount->warehouse->name }}</td>
            <td style="width:30px"></td>
            <td><strong>Tanggal</strong></td>
            <td>{{ $physicalCount->count_date->format('d/m/Y') }}</td>
        </tr>
    </table>

    <table class="lines">
        <thead>
            <tr>
                <th>No</th>
                <th>Item No</th>
                <th>Deskripsi</th>
                <th class="text-end">Qty Fisik</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($physicalCount->lines as $i => $line)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $line->item->item_no }}</td>
                    <td>{{ $line->item->description }}</td>
                    <td class="text-end">&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-notes">
        Dihitung oleh: ________________________&emsp;&emsp;&emsp;Tanda tangan: ________________________
    </div>
@endsection
