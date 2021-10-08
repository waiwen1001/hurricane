@extends('layouts.app')

@section('content')

@include('delivery.header')

<div style="padding: 10px; border: 1px solid #ccc; margin: 20px; border-radius: 3px;">
  <form method="GET" action="{{ route('getAdminJobsList') }}">
    <div class="row">
      <div class="col-6">
        <div class="form-group">
          <label>Date from</label>
          <input type="date" class="form-control" name="date_from" value="{{ $date_from }}" />
        </div>
      </div>

      <div class="col-6">
        <div class="form-group">
          <label>Date To</label>
          <input type="date" class="form-control" name="date_to" value="{{ $date_to }}" />
        </div>
      </div>
    </div>

    <button class="btn btn-success" type="submit">Submit</button>
  </form>
</div>
@endsection