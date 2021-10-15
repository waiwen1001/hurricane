@extends('layouts.app')

@section('content')

@include('delivery.header')

<div class="container-fluid">
  <div class="row">
    <div class="col-md-6 col-lg-4">
      <div style="padding: 20px">
        <a href="{{ route('getDriverEarningReport') }}" class="btn btn-primary">
          <i class="fas fa-dollar-sign"></i>
          Driver Earning Report
        </a>
      </div>
    </div> 
  </div>
</div>

@endsection