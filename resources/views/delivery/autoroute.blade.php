@extends('layouts.app')

@section('content')

@include('delivery.header')

<style>
  
  .connectedSortable {
    border: 1px solid #eee;
    width: 142px;
    min-height: 20px;
    list-style-type: none;
    margin: 0;
    padding: 5px 0 0 0;
    float: left;
    margin-right: 10px;
    width: 100%;
  }
  .connectedSortable li {
    margin: 0 5px 5px 5px;
    padding: 5px;
    font-size: 1.2em;
    width: calc(100% - 10px);
  }

</style>

<div class="autoroute">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-8 col-sm-12">
        <div style="overflow: auto; max-height: 470px; position: relative; width: 100%; height: 100%; padding-top: 5px;">
          @foreach($driver_list as $key => $driver)
            <div class="driver_jobs" style="left: calc({{ $key }} * 270px);">
              <label style="width: 100%; text-align: center; margin: 10px 0;">{{ $driver->name }}</label>

              <ul id="sortable_{{ $driver->id }}" class="connectedSortable" driver_id="{{ $driver->id }}">
                @foreach($driver->job_list as $job)
                  <li class="ui-state-default" id="job_{{ $job->id }}" lat_lng="" job_id="{{ $job->id }}" style="position: relative;">
                    {{ $job->name }}<br>
                    <p style="font-size: 13px; font-weight: bold; margin-bottom: 5px;">{{ $job->pick_up }}</p>
                    <p style="font-size: 13px; margin-bottom: 0px;">{{ $job->address }}</p>
                    <div class="delete_job" job_id="{{ $job->id }}">
                      <i class="fa fa-times"></i>
                    </div>
                  </li>
                @endforeach
              </ul>
            </div>
          @endforeach
        </div>
        <div style="margin-top: 6px;">
          <button class="btn btn-success" id="assign_driver">Assign</button>
        </div>
      </div>
      <div class="col-md-4 col-sm-12">
        <div id="autoroute_joblist_box" style="border: 20px solid #ccc; border-bottom-width: 50px; border-radius: 3px; display: inline-block;  width: 100%; overflow-y: auto; position: relative;">
          <div style="height: 450px;">
            <div style="height: 100%;">
              <ul class="connectedSortable" style="width: 100%; height: 100%; display: inline-block; overflow-y: auto;">
                @foreach($no_driver_job_list as $job)
                  <li class="ui-state-default" id="job_{{ $job->id }}" lat_lng="" job_id="{{ $job->id }}" style="position: relative;">
                    {{ $job->name }}<br>
                    <p style="font-size: 13px; font-weight: bold; margin-bottom: 5px;">{{ $job->pick_up }}</p>
                    <p style="font-size: 13px; margin-bottom: 0px;">{{ $job->address }}</p>
                    <div class="delete_job" job_id="{{ $job->id }}">
                      <i class="fa fa-times"></i>
                    </div>
                  </li>
                @endforeach
              </ul>
            </div>
          </div>
          <div class="autoroute_joblist">
            <p>List of jobs found here</p>
            <div style="width: calc(100% - 50px); height: 1px; background: #ccc; margin-bottom: 1em;"></div>
            <p style="text-align: center;">Can drag jobs into this box to reschedule for next day.</p>
          </div>
        </div>

        <div class="autoroute_joblist_btn">
          <form method="POST" action="{{ route('importNewJobs') }}" enctype="multipart/form-data" id="import_file_form">
            @csrf
            <input type="file" style="display: none;" name="file" id="import_file_input" accept=".xlsx, .xlsm, .csv, .xls" required />
          </form>
          <button class="btn btn-success" id="import_file">Import List</button>
          @if($autoroute == null)
            <button class="btn btn-success" id="auto_route_btn">Auto Route</button>
          @else
            <button class="btn btn-success" id="revert_route_btn">Revert Route</button>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container">
  <div class="row">
    <div class="col-12" style="margin-top: 20px; margin-bottom: 20px;">
      <div id="map" style="height: 500px;"></div>
    </div>
  </div>
</div>

<script>
  
  var initMap_timeout;
  var job_list = @json($job_list);
  var driver_list = @json($driver_list);
  var jobs_marker = [];
  var marker_array = [];
  var marker_count = 0;
  var check_route_interval;
  var directionsDisplay;
  var service;
  var path, poly, polylines = [];

  $(document).ready(function(){
    $( ".connectedSortable" ).sortable({
      connectWith: ".connectedSortable",
      update: function( event, ui ) {
        $(".connectedSortable").removeClass("hovering");
        var driver_id = $(this).attr("driver_id");
        for(var a = 0; a < driver_list.length; a++)
        {
          if(driver_list[a].id == driver_id)
          {
            var new_job_list = [];
            var driver_lat_lng = [];
            $(this).children("li").each(function(){
              var job_id = $(this).attr("job_id");
              for(var b = 0; b < job_list.length; b++)
              {
                if(job_list[b].id == job_id)
                {
                  new_job_list.push(job_list[b]);

                  for(i = 0; i < jobs_marker.length; i++)
                  {
                    if(jobs_marker[i].job_id == job_id)
                    {
                      jobs_marker[i].setMap(null);
                      jobs_marker[i].label = driver_list[a].name;

                      var marker = new google.maps.Marker({
                        position: jobs_marker[i].position,
                        map: map, 
                        label: jobs_marker[i].label,
                        infowindow: jobs_marker[i].infowindow,
                        job_id : jobs_marker[i].job_id,
                      });

                      google.maps.event.addListener(marker, 'click', function() {
                        hideAllInfoWindows(map);
                        this.infowindow.open(map, this);
                      });

                      var lat_lng = jobs_marker[i].position.lat()+" "+jobs_marker[i].position.lng();
                      driver_lat_lng.push(lat_lng);

                      jobs_marker.splice(i, 1);
                      jobs_marker.push(marker);

                      break;
                    }
                  }
                  break;
                }
              }
            });

            driver_list[a].job_list = new_job_list;
            driver_list[a].lat_lng = driver_lat_lng;
            break;
          }
        }

        for (var c = 0; c < polylines.length; c++)
        {
          polylines[c].setMap(null);
        }
        generate_direction();
      },
      over: function(){
        $(".connectedSortable").removeClass("hovering");
        $(this).addClass('hovering');
      }
    }).disableSelection();

    initMap();
    initMap_timeout = setTimeout(function(){
      if($("#map").html() == "")
      {
        initMap();
      }
    }, 1000);

    $("#import_file").click(function(){
      $("#import_file_input").click();
    });

    $("#import_file_input").change(function(){
      if($("#import_file_input").val())
      {
        $("#import_file_form").submit();
      }
    });

    $("#assign_driver").click(function(){
      assignDriver();
    });

    $("#auto_route_btn").click(function(){
      window.location.href = "{{ route('getAdminAutoRoute', ['autoroute' => 1]) }}";
    });

    $("#revert_route_btn").click(function(){
      window.location.href = "{{ route('getAdminAutoRoute') }}";
    });

    $(".delete_job").click(function(){
      var _this = $(this);
      var r = confirm("Are you sure you want to delete this job?");
      if (r == true) {
        $.post("{{ route('deleteJob') }}", { "_token" : "{{ csrf_token() }}", "job_id" : $(this).attr("job_id") }, function(result){
          if(result.error == 0)
          {
            _this.parent("li").remove();
            showCompleted(result.message, 0);
          }
          else
          {
            showError("Delete job failed, please try again.", 0);
          }
        });
      }
    });
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

          directionsDisplay = new google.maps.DirectionsRenderer({
            suppressMarkers: true
          });
          service = new google.maps.DirectionsService();

          clearTimeout(initMap_timeout);

          show_jobs_location();

          check_route_interval = setInterval(generate_direction, 1000);
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

  function hideAllInfoWindows(map) {
    jobs_marker.forEach(function(marker) {
      marker.infowindow.close(map, marker);
    }); 
  }

  function show_jobs_location()
  {
    let address_info;
    for(var c = 0; c < driver_list.length; c++)
    {
      var driver_jobs = driver_list[c]['job_list'];
      for(var b = 0; b < driver_jobs.length; b++)
      {
        address_info = {
          'job_detail' : driver_jobs[b],
          'address' : driver_jobs[b].address,
          'postal_code' : driver_jobs[b].postal_code,
          'contact_number' : driver_jobs[b].contact_number,
          'driver_id' : driver_list[c].id,
          'driver_name' : driver_list[c].name
        };

        marker_array.push(address_info);

        let postal_code = "Singapore "+driver_jobs[b].postal_code;
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

                var infowindow = new google.maps.InfoWindow({ 
                  content: html,
                  size: new google.maps.Size(150,50)
                });

                var marker = new google.maps.Marker({
                  position: results[0].geometry.location,
                  map: map, 
                  label: marker_array[marker_count].driver_name,
                  infowindow: infowindow,
                  job_id : marker_array[marker_count].job_detail.id
                });

                var lat_lng = results[0].geometry.location.lat()+" "+results[0].geometry.location.lng();
                for(var d = 0; d <= driver_list.length; d++)
                {
                  if(driver_list[d].id == marker_array[marker_count].driver_id)
                  {
                    driver_list[d].lat_lng.push(lat_lng);
                    break;
                  }
                }

                $("#job_"+marker_array[marker_count].job_detail.id).attr("lat_lng", lat_lng);

                jobs_marker.push(marker);

                google.maps.event.addListener(marker, 'click', function() {
                  hideAllInfoWindows(map);
                  this.infowindow.open(map, this);
                });

                marker_count++;
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
  }

  function generate_direction()
  {
    var total_lat_lng = 0;
    for(var c = 0; c < driver_list.length; c++)
    {
      total_lat_lng += parseInt(driver_list[c].lat_lng.length);
    }

    if(total_lat_lng == job_list.length)
    {
      for(var c = 0; c < driver_list.length; c++)
      {
        for (var i = 0; i < driver_list[c].lat_lng.length; i++)
        {
          if ((i + 1) < driver_list[c].lat_lng.length)
          {
            var src = driver_list[c].lat_lng[i];
            var des = driver_list[c].lat_lng[i + 1];
            // path.push(src);

            service.route({
              origin: src,
              destination: des,
              travelMode: google.maps.DirectionsTravelMode.DRIVING
            }, function(result, status) {
              if (status == google.maps.DirectionsStatus.OK) {

                //Initialize the Path Array
                path = new google.maps.MVCArray();
                //Set the Path Stroke Color
                poly = new google.maps.Polyline({
                  map: map,
                  strokeColor: '#4986E7'
                });
                poly.setPath(path);

                polylines.push(poly);

                for (var i = 0, len = result.routes[0].overview_path.length; i < len; i++) {
                  path.push(result.routes[0].overview_path[i]);
                }
              }
            });
          }
        }
      }

      clearInterval(check_route_interval);
    }
  }

  function assignDriver()
  {
    var form_data = "_token={{ csrf_token() }}";
    $(".driver_jobs").each(function(){
      var driver_id = $(this).children(".connectedSortable").attr("driver_id");
      $(this).children(".connectedSortable").children("li").each(function(){
        var job_id = $(this).attr("job_id");
        form_data += "&job_id[]="+job_id+"&job_id_"+job_id+"="+driver_id;
      });
    });

    $.post("{{ route('assignDriver') }}", form_data, function(result){
      if(result.error == 0)
      {
        window.location.href = "{{ route('getAdminAutoRoute') }}";
      }
    });
  }

</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCNp9DSX6ILJhAQMtZhd2IDd0KPJOVOLN8&v=weekly" async ></script>

@endsection