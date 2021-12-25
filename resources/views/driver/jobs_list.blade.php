@extends('layouts.app')

@section('content')

@include('driver.header')

<div style="padding: 10px; border: 1px solid #ccc; margin: 20px; border-radius: 3px;">
  <form method="GET" action="{{ route('getDriverJobsList') }}">
    <div class="row">
      <div class="col-6">
        <div class="form-group">
          <label>Date From</label>
          <input type="date" class="form-control" name="date_from" value="{{ $date_from }}" />
        </div>
      </div>

      <div class="col-6">
        <div class="form-group">
          <label>Date To</label>
          <input type="date" class="form-control" name="date_to" value="{{ $date_to }}" />
        </div>
      </div>

      <div class="col-12">
        <div class="form-group">
          <label>Status</label>
          <select name="status" class="form-control" style="text-transform: capitalize;">
            <option value="0">Select all</option>
            @foreach($driver_status as $status)
              <option value="{{ $status['status'] }}" {{ $status_filter == $status['status'] ? 'selected' : '' }}>{{ $status['status'] }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>

    <button class="btn btn-success" type="submit">Submit</button>
  </form>
</div>

<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="jobs_box" style="margin: 5px;">
        <table id="jobs_table" class="table table-bordered table-striped" cellspacing="0" width="100%">
          <thead>
            <th></th>
            <th>Status</th>
            <th>Pick Up Location</th>
            <th>Name</th>
            <th>Contact Number</th>
            <th>Address</th>
            <th>Expected Delivery Date Time</th>
            <th>Wallet Value</th>
            <th>Created date</th>
          </thead>
          <tbody>
            @foreach($driver_jobs as $job)
              <tr class="{{ $job->urgent == 1 ? 'red' : '' }}">
                <td data-order="{{ $job->status == 'starting' ? '0' : '1' }}">
                  <div class="status_icon" style="background: {{ $job->color }}"></div>
                </td>
                <td style="text-transform: capitalize;">{{ $job->status }}</td>
                <td>{{ $job->pick_up }}</td>
                <td>{{ $job->name }}</td>
                <td>{{ $job->contact_number }}</td>
                <td>{{ $job->address }}</td>
                <td>
                  @if($job->est_delivery_from && $job->est_delivery_from)
                    {{ $job->est_delivery_from_text }} - {{ $job->est_delivery_to_text }}
                  @endif
                </td>
                <td>S$ {{ number_format($job->price, 2) }}</td>
                <td data-order="{{ $job->created_at }}">{{ date('d M Y h:i A', strtotime($job->created_at)) }}</td>
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
    $("#jobs_table").DataTable({
      responsive: true,
    });
  })

</script>

@endsection