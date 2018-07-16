@extends('layouts.master')

@section('styles')
    <link rel="stylesheet"
          href="{{ asset('bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
@endsection

@section('page_title')
    Game List
@endsection

@section('content')

    @include('games.datatable')

@endsection

@section('scripts')
    @include('games.js_datatable')
@endsection
