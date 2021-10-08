@extends('layouts.app')

@section('content')

@include('delivery.header')

<div class="container">
  <div class="row">
    <div class="col-12" style="margin-bottom: 20px;">
      <div class="row">
        <div class="col-sm-6 col-md-4">
          <div class="jobs_status_box">
            <div class="jobs_status_content">
              <div class="status_icon" style="background: #ccc"></div>
              <label>Pending</label>
            </div>
            <span>{{ $total_pending }}</span>
          </div>
        </div>

        <div class="col-sm-6 col-md-4">
          <div class="jobs_status_box">
            <div class="jobs_status_content">
              <div class="status_icon" style="background: #00bcd4"></div>
              <label>Accepted</label>
            </div>
            <span>{{ $total_accepted }}</span>
          </div>
        </div>

        <div class="col-sm-6 col-md-4">
          <div class="jobs_status_box">
            <div class="jobs_status_content">
              <div class="status_icon" style="background: #8bc34a"></div>
              <label>Completed</label>
            </div>
            <span>{{ $total_completed }}</span>
          </div>
        </div>

        <div class="col-sm-6 col-md-4">
          <div class="jobs_status_box">
            <div class="jobs_status_content">
              <div class="status_icon" style="background: #ff0000;"></div>
              <label>Overdue</label>
            </div>
            <span>{{ $total_overdue }}</span>
          </div>
        </div>
      </div>

      <hr style="margin-bottom: 5px;" />
      <div class="row">
        <div class="col-sm-12 col-md-6">
          <div class="jobs_status_box">
            <div class="jobs_status_content">
              <div class="status_icon" style="background:#ff0000"></div>
              <label>Urgent ( within 2 hours )</label>
            </div>
            <span id="total_urgent_hours_two">{{ $urgent_two }}</span>
          </div>
        </div>

        <div class="col-sm-12 col-md-6">
          <div class="jobs_status_box">
            <div class="jobs_status_content">
              <div class="status_icon" style="background:#ff9800"></div>
              <label>Urgent ( within 4 hours )</label>
            </div>
            <span id="total_urgent_hours_four">{{ $urgent_four }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12" style="margin-bottom: 20px;">
      <div class="jobs_box">
        <table id="jobs_table" class="table table-bordered table-striped" cellspacing="0" width="100%">
          <thead>
            <th></th>
            <th>Status</th>
            <th>Driver</th>
            <th>Pick Up Location</th>
            <th>Name</th>
            <th>Contact Number</th>
            <th>Address</th>
            <th>Expected Delivery Date Time</th>
            <th>Wallet Value</th>
          </thead>
          <tbody>
            @foreach($driver_jobs as $job)
              <tr class="{{ $job->urgent == 1 ? 'red' : '' }}">
                <td data-order="{{ $job->status == 'starting' ? '0' : '1' }}">
                  <div class="status_icon" style="background: {{ $job->color }}"></div>
                </td>
                <td style="text-transform: capitalize;">{{ $job->status }}</td>
                <td>{{ $job->driver }}</td>
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
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <div class="col-12">
      <div class="jobs_box">
        <table id="drivers_table" class="table table-bordered table-striped" cellspacing="0" width="100%">
          <thead>
            <th>Online</th>
            <th>Driver Name</th>
            <th>Total Accepted</th>
            <th>Total Completed</th>
            <th>Total Overdue</th>
          </thead>
          <tbody>
            @foreach($driver_list as $driver)
              <tr>
                <td>
                  @if($driver->online == 1)
                    <div class="status_icon" style="background: #8bc34a"></div>
                  @else
                    <div class="status_icon" style="background: #ff0000"></div>
                  @endif
                </td>
                <td>{{ $driver->name }}</td>
                <td>{{ $driver->total_accepted }}</td>
                <td>{{ $driver->total_completed }}</td>
                <td>{{ $driver->total_overdue }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12" style="margin-top: 20px; margin-bottom: 20px;">
      <div id="map" style="height: 500px;"></div>
    </div>
  </div>
</div>

<script>
  
  $(document).ready(function(){

    $("#jobs_table, #drivers_table").DataTable({
      responsive: true,
      scrollY: '450px',
      scrollCollapse: true,
    });

    refresh_driver_location();
  });

  function initMap()
  {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          var my_pos = {
            lat: position.coords.latitude,
            lng: position.coords.longitude,
          };

          var map_option = {
            zoom: 12,
            center: my_pos,
            styles: [{"elementType":"labels","stylers":[{"visibility":"off"}]}]
          };

          map = new google.maps.Map(document.getElementById("map"), map_option);
        },
        () => {
          showError("Please enable your location", 0);
        }
      );
    } else {
      // Browser doesn't support Geolocation
      showError("Your browser is not support Geolocation", 0);
    }
  }

  function refresh_driver_location()
  {
    console.log("hello");
  }

</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCNp9DSX6ILJhAQMtZhd2IDd0KPJOVOLN8&callback=initMap&v=weekly" async ></script>
@endsection