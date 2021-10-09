@extends('main')
@section('breadcrumb')
{{ Breadcrumbs::render('inventory.itemCreateByExcel', request()->store_id, request()->branch_id) }}
@endsection
@section('main-content')
<h1>excel</h1>
@endsection