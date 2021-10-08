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
            <th>Current Job</th>
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
                <td>
                  @if($driver->current_job)
                    <b>Job name : </b>{{ $driver->current_job->name }} <br>
                    <b>Address : </b>{{ $driver->current_job->address }} <br>
                  @endif
                </td>
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
  
  var map;
  var markers = [];
  var driver_jobs = @json($driver_jobs);
  var marker_array = [];
  var marker_count = 0;
  var jobs_marker = [];
  var initMap_timeout;

  $(document).ready(function(){

    $("#jobs_table, #drivers_table").DataTable({
      responsive: true,
      scrollY: '450px',
      scrollCollapse: true,
    });

    initMap();
    initMap_timeout = setTimeout(function(){
      if($("#map").html() == "")
      {
        initMap();
      }
    }, 2000);

    setInterval(refresh_driver_location, 10000);
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

          show_jobs_location();
          refresh_driver_location();

          var map_option = {
            zoom: 12,
            center: my_pos,
            styles: [{"elementType":"labels","stylers":[{"visibility":"off"}]}]
          };

          map = new google.maps.Map(document.getElementById("map"), map_option);

          driver_icon = {
            url: "{{ asset('assets/images/truck.png') }}", // url
            scaledSize: new google.maps.Size(30, 30), // scaled size
            origin: new google.maps.Point(0,0), // origin
            anchor: new google.maps.Point(0, 0) // anchor
          };

          clearTimeout(initMap_timeout);
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
    for(var a = 0; a < markers.length; a++)
    {
      let old_marker = markers[a];
      old_marker.setMap(null);
    }
    markers = [];
    $.post("{{ route('getDriverLocation') }}", { "_token" : "{{ csrf_token() }}" }, function(result){

      var driver_list = result.driver_list;
      for(var a = 0; a < driver_list.length; a++)
      {
        if(driver_list[a].lat && driver_list[a].lng)
        {
          var infowindow = new google.maps.InfoWindow({ 
            content: "<b>"+driver_list[a].name+"</b>",
            size: new google.maps.Size(150,50)
          });

          var marker = new google.maps.Marker({
            position: { lat : driver_list[a].lat, lng : driver_list[a].lng },
            map: map,
            icon: driver_icon,
            infowindow: infowindow
          });

          markers.push(marker);

          google.maps.event.addListener(marker, 'click', function() {
            hideAllInfoWindows(map);
            this.infowindow.open(map, this);
          });
        }
      }
      
    });
  }

  function hideAllInfoWindows(map) {
    markers.forEach(function(marker) {
      marker.infowindow.close(map, marker);
    }); 

    jobs_marker.forEach(function(marker) {
      marker.infowindow.close(map, marker);
    }); 
  }

  function show_jobs_location()
  {
    let address_info;
    for(var a = 0; a < driver_jobs.length; a++)
    {
      address_info = {
        'job_detail' : driver_jobs[a],
        'address' : driver_jobs[a].address,
        'postal_code' : driver_jobs[a].postal_code,
        'contact_number' : driver_jobs[a].contact_number
      };

      marker_array.push(address_info);

      let postal_code = "Singapore "+driver_jobs[a].postal_code;
      // address = address.replace(/(\r\n|\n|\r)/gm, " ");

      geocoder = new google.maps.Geocoder();
      if(geocoder)
      {
        geocoder.geocode( { 'address': postal_code }, function(results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
            if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
              // map.setCenter(results[0].geometry.location);

              var marker_job_detail = null;
              var html = "";
              for(var a = 0; a < marker_array.length; a++)
              {
                if(a == marker_count)
                {
                  marker_job_detail = marker_array[a].job_detail;
                  html += '<b>'+marker_array[a].address+'</b>';
                  if(marker_array[a].contact_number)
                  {
                    html += '<br/><b>'+marker_array[a].contact_number+'</b>';
                  }

                  break;
                }
              }

              marker_count++;

              var infowindow = new google.maps.InfoWindow({ 
                content: html,
                size: new google.maps.Size(150,50)
              });

              var marker = new google.maps.Marker({
                position: results[0].geometry.location,
                map: map, 
                label: {
                  text: "\ue838",
                  fontFamily: "Material Icons",
                  color: "#ffc107",
                  fontSize: "18px",
                },
                infowindow: infowindow
              });

              jobs_marker.push(marker);

              google.maps.event.addListener(marker, 'click', function() {
                hideAllInfoWindows(map);
                this.infowindow.open(map, this);
              });
            } else {
              showError("No results found", 0);
            }
          } else {
            showError("Geocode was not successful for the following reason: " + status, 0);
          }
        });
      }
      
    }
  }

</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCNp9DSX6ILJhAQMtZhd2IDd0KPJOVOLN8&v=weekly" async ></script>
@endsection