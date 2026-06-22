@extends('layouts.admin')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Adjustment Draft/Diajukan</h6>
                    <h3>{{ $adjustmentCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Physical Count Draft/Diajukan</h6>
                    <h3>{{ $physicalCountCount }}</h3>
                </div>
            </div>
        </div>
    </div>
@endsection
