@extends('layouts.app')

@section('content')

@include('delivery.header')

<div class="row">
  <div class="col-12">
    <div style="text-align: center; margin: 10px 0;">
      <h3>Driver name : {{ $driver->name }}</h3>
      <label style="width: 100%;">Report From : {{ date('d M Y', strtotime($date_from)) }} - {{ date('d M Y', strtotime($date_to)) }}</label>
    </div>
  </div>
  <div class="col-12">
    <div class="jobs_box" style="margin: 0 30px;">
      <table id="jobs_table" class="table table-bordered table-striped" cellspacing="0" width="100%">
        <thead>
          <th>Pick Up Location</th>
          <th>Customer Name</th>
          <th>Address</th>
          <th>Wallet Value</th>
          <th>Accepted At</th>
          <th>Status</th>
        </thead>
        <tbody>
          @foreach($driver_jobs as $job)
            <tr>
              <td>{{ $job->pick_up }}</td>
              <td>{{ $job->name }}</td>
              <td>{{ $job->address }}</td>
              <td>S$ {{ number_format($job->price, 2) }}</td>
              <td data-order="{{ $job->driver_accepted_at }}">{{ date('d M Y', strtotime($job->driver_accepted_at)) }}</td>
              <td style="text-transform: capitalize;">{{ $job->status }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  
  $(document).ready(function(){
    $("#jobs_table").DataTable({
      responsive: true,
    });
  });

</script>

@endsection