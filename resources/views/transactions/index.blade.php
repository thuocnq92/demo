@extends('layouts.master')

@section('styles')
    <link rel="stylesheet"
          href="{{ asset('bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
@endsection

@section('page_title')
    Transaction List
@endsection

@section('content')

    @include('transactions.datatable')

@endsection

@section('scripts')
    @include('transactions.js_datatable')
@endsection
