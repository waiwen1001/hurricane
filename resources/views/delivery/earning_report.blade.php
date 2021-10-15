@extends('layouts.app')

@section('content')

@include('delivery.header')

<div style="padding: 20px; margin: 15px; border: 1px solid #333;">
  <form method="GET" action="{{ route('getDriverEarningReport') }}">
    <div class="row">
      <div class="col-md-12 col-lg-6">
        <label>Date From</label>
        <input type="date" name="date_from" class="form-control" value="{{ $date_from }}" />
      </div>
      <div class="col-md-12 col-lg-6">
        <label>Date To</label>
        <input type="date" name="date_to" class="form-control" value="{{ $date_to }}" />
      </div>
    </div> 

    <button type="submit" class="btn btn-success" style="margin-top: 20px;">Submit</button>
  </form>
</div>

<div class="container-fluid">
  <div class="row">
    <div class="col-md-6 col-lg-4">
      <div class="report_count">
        <div style="font-size: 30px; padding-right: 30px;">
          <i class="fas fa-shuttle-van"></i>
        </div>
        <div style="flex: 1;">
          <label style="width: 100%;">Total Driver</label>
        </div>
        <div style="flex: 1; text-align: right;">
          <span style="font-weight: bold; font-size: 30px;">{{ count($driver_list) }}</span>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-lg-4">
      <div class="report_count">
        <div style="font-size: 30px; padding-right: 30px;">
          <i class="fas fa-dollar-sign"></i>
        </div>
        <div style="flex: 1;">
          <label style="width: 100%;">Total Earning</label>
        </div>
        <div style="flex: 1; text-align: right;">
          <span style="font-weight: bold; font-size: 25px;">S$ {{ number_format($total, 2) }}</span>
        </div>
      </div>
    </div> 

    <div class="col-12">
      <div class="jobs_box">
        <table id="drivers_table" class="table table-bordered table-striped" cellspacing="0" width="100%">
          <thead>
            <th>Driver name</th>
            <th>Total Accepted</th>
            <th>Total Completed</th>
            <th>Total Earning</th>
            <th>Detail</th>
          </thead>
          <tbody>
            @foreach($driver_list as $driver)
              <tr>
                <td>{{ $driver->name }}</td>
                <td>{{ count($driver->jobs) }}</td>
                <td>{{ $driver->total_completed }}</td>
                <td>{{ $driver->total }}</td>
                <td>
                  <a href="{{ route('getDriverEarningDetail', ['date_from' => $date_from, 'date_to' => $date_to, 'driver_id' => $driver->id]) }}" class="btn btn-primary">Detail</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  
  $(document).ready(function(){
    $("#drivers_table").DataTable({
      responsive: true,
    });
  });

</script>

@endsection