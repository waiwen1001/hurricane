@extends('layouts.app')

@section('content')

@include('driver.header')

<div style="padding: 10px 20px 30px 20px;">
  <div class="row">
    <div class="col-sm-12 col-lg-8" style="margin-bottom: 20px;">
      <div class="jobs_box">
        <table id="jobs_table" class="table table-bordered table-striped" cellspacing="0" width="100%">
          <thead>
            <th></th>
            <th>Name</th>
            <th>Contact Number</th>
            <th>Address</th>
            <th>Expected Delivery Date Time</th>
            <th>Wallet Value</th>
            <th style="min-width: 100px;">Action</th>
          </thead>
          <tbody>
            @foreach($driver_jobs as $job)
              <tr>
                <td data-order="{{ $job->status == 'starting' ? '0' : '1' }}">
                  <div class="status_icon" style="background: {{ $job->color }}"></div>
                </td>
                <td>{{ $job->name }}</td>
                <td>{{ $job->contact_number }}</td>
                <td>{{ $job->address }}</td>
                <td>
                  @if($job->est_delivery_from && $job->est_delivery_from)
                    {{ $job->est_delivery_from_text }} - {{ $job->est_delivery_to_text }}
                  @endif
                </td>
                <td>S$ {{ number_format($job->price, 2) }}</td>
                <td>
                  @if(!$have_job)
                    <button class="btn btn-primary select" onclick="selectJob(this)" job_id="{{ $job->id }}">Select</button>
                    <button class="btn btn-danger cancel" onclick="cancelJob(this)" job_id="{{ $job->id }}">Cancel</button>
                  @else
                    @if($job->status == "starting")
                      <button class="btn btn-success complete" job_id="{{ $job->id }}" onclick="completeJob(this)">Complete</button>
                      <button class="btn btn-secondary direction" direction="{{ $job->postal_code }}" onclick="jobDirection(this)">Direction</button>
                    @else
                      <button class="btn btn-primary select" job_id="{{ $job->id }}" disabled onclick="selectJob(this)">Select</button>
                    @endif

                    <button class="btn btn-danger cancel" job_id="{{ $job->id }}" onclick="cancelJob(this)" style="display: {{ $job->status == 'starting' ? 'none' : '' }};" {{ $job->status == 'starting' ? 'disabled' : '' }}>Cancel</button>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="col-sm-12 col-lg-4" style="margin-bottom: 20px;">
      <div class="row" id="driver_status_list">
        @foreach($driver_status_list as $status)
          <div class="col-sm-12 col-md-6">
            <div class="jobs_status_box">
              <div class="jobs_status_content">
                <div class="status_icon" style="background: {{ $status['color'] }}"></div>
                <label>{{ $status['status'] }}</label>
              </div>
              <span>{{ $status['count'] }}</span>
            </div>
          </div>
        @endforeach
      </div>

      <hr style="margin-bottom: 5px;" />
      <div class="row">
        <div class="col-sm-12 col-md-6" style="display: {{ $total_urgent_hours_two == 0 ? 'none' : '' }};" id="urgent_two_box">
          <div class="jobs_status_box">
            <div class="jobs_status_content">
              <div class="status_icon" style="background:#ff0000"></div>
              <label>Urgent ( within 2 hours )</label>
            </div>
            <span id="total_urgent_hours_two">{{ $total_urgent_hours_two }}</span>
          </div>
        </div>

        <div class="col-sm-12 col-md-6" style="display: {{ $total_urgent_hours_four == 0 ? 'none' : '' }};" id="urgent_four_box">
          <div class="jobs_status_box">
            <div class="jobs_status_content">
              <div class="status_icon" style="background:#ff9800"></div>
              <label>Urgent ( within 4 hours )</label>
            </div>
            <span id="total_urgent_hours_four">{{ $total_urgent_hours_four }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div id="map" style="height: 500px;"></div>
    </div>
  </div>

  <div class="modal fade" id="completeModal" role="dialog" aria-labelledby="completeModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document" style="width: 1200px;">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="completeModalLabel">Delivered</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="vcpClose_2">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" style="max-height: 500px; overflow-x: hidden; overflow-y: auto;">
          <form method="POST" action="{{ route('submitCompleteJob') }}" enctype="multipart/form-data" method="post" id="submitCompleteJobForm">
            @csrf
            <table class="custom">
              <tr>
                <td>Name</td>
                <td>:</td>
                <td>Plan A</td>
              </tr>
              <tr>
                <td>Address</td>
                <td>:</td>
                <td>Address A</td>
              </tr>
              <tr>
                <td>Contact number</td>
                <td>:</td>
                <td>1234567</td>
              </tr>
              <tr>
                <td>Expected delivery date time</td>
                <td>:</td>
                <td>A</td>
              </tr>
              <tr>
                <td colspan="3">
                  <p></p>
                </td>
              </tr>
              <tr>
                <td colspan="3">
                  <label style="display: block;">POD (Unit number with items)</label>
                  <input type="file" name="file_pod[]" accept="image/*" required multiple />
                </td>
              </tr>
              <tr>
                <td colspan="3">
                  <label style="display: block;">Customer Signature</label>
                  <button type="button" class="btn btn-primary" id="signature_btn">Signature</button>
                  <div id="signature_img_box" style="display: none; text-align: center; padding: 10px 0;">
                    <div style="display: inline-block; border: 1px solid #000;">
                      <img id="signature_img" style="max-width: 100%; max-height: 150px;" />
                    </div>
                  </div>
                </td>
              </tr>
            </table>
            
            <input type="hidden" name="signature" value="" />
            <input type="hidden" name="job_id" value="" />
            <button type='button' id="submit_complete_job" class='btn btn-success' style="margin-top: 10px;">Submit</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="signature_box">
  <div id="signature-pad" class="signature-pad">
    <div class="signature-pad--body">
      <canvas></canvas>
    </div>

    <div class="signature-pad--footer">
      <div class="description">Sign above</div>

      <div class="signature-pad--actions">
        <div>
          <button class="btn btn-primary" id="clear_signature">Clear</button>
          <button class="btn btn-primary" id="undo_signature">Undo</button>
          <button class="btn btn-success" id="submit_signature">Sign</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  
  let geocoder;
  let jobs_table;
  var driver_jobs = @json($driver_jobs);
  var marker_count = 0;
  var markers = [];
  var marker_array = [];
  var have_job = "{{ $have_job }}";
  var signature_canvas = document.getElementById("signature-pad").querySelector("canvas");
  var signaturePad;
  var initMap_timeout;

  $(document).ready(function(){
    showTime();
    check_online();
    setInterval(check_online, 60000);

    jobs_table = $("#jobs_table").DataTable({
      responsive: true,
      scrollY: '450px',
      scrollCollapse: true,
    });

    $("#clear_signature").click(function(){
      signaturePad.clear();
    });

    $("#undo_signature").click(function(){
      var data = signaturePad.toData();
      if (data) {
        data.pop(); // remove the last dot or line
        signaturePad.fromData(data);
      }
    });

    $("#submit_signature").click(function(){
      if (signaturePad.isEmpty()) {
        showError("Client signature is compulsory.", 0);
      } else {
        var dataURL = signaturePad.toDataURL();
        $("#signature_img_box").show();
        document.getElementById('signature_img').src = dataURL;
        $("input[name='signature']").val(dataURL);
        $(".signature_box").removeClass("active");
        // console.log(dataURL);
        $("#completeModal").modal('show');
      }
    });

    $("#signature_btn").click(function(){
      $(".signature_box").addClass("active");
      initSignature();
      $("#completeModal").modal('hide');
    });

    $("#submit_complete_job").click(function(){
      if($("input[name='file_pdo[]']").val() == "")
      {
        showError("POD images is compulsory.", 0);
        return;
      }

      if(!signaturePad)
      {
        showError("Client signature is compulsory.", 0);
        return;
      }
      else
      {
        if(signaturePad.isEmpty())
        {
          showError("Client signature is compulsory.", 0);
          return;
        }
        else
        {
          $("#submitCompleteJobForm").submit();
        }
      }
    });

    initMap_timeout = setTimeout(function(){
      if($("#map").html() == "")
      {
        initMap();
      }
    }, 2000);
  });

  initialize();

  function initialize()
  {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          my_pos = {
            lat: position.coords.latitude,
            lng: position.coords.longitude,
          };

          initMap();
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

  function initSignature()
  {
    signaturePad = new SignaturePad(signature_canvas, {
      backgroundColor: 'rgb(255, 255, 255)'
    });

    window.onresize = resizeCanvas;
    resizeCanvas();
  }

  function initMap()
  {
    if(my_pos)
    {
      geocoder = new google.maps.Geocoder();
      var map_option = {
        zoom: 12,
        center: my_pos,
        styles: [
          {
            "featureType": "poi.business",
            "stylers": [
              {
                "visibility": "off"
              }
            ]
          },
          {
            "featureType": "poi.park",
            "elementType": "labels.text",
            "stylers": [
              {
                "visibility": "off"
              }
            ]
          }
        ]
      };

      map = new google.maps.Map(document.getElementById("map"), map_option);

      update_location();
      save_location();

      driver_icon = {
        url: "{{ asset('assets/images/truck.png') }}", // url
        scaledSize: new google.maps.Size(30, 30), // scaled size
        origin: new google.maps.Point(0,0), // origin
        anchor: new google.maps.Point(0, 0) // anchor
      };

      my_marker = new google.maps.Marker({
        position: my_pos,
        map: map,
        icon: driver_icon
      });
    } 

    if(geocoder)
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

              markers.push(marker);

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

      calcRoute();
    }

    clearTimeout(initMap_timeout);
  }

  function hideAllInfoWindows(map) {
     markers.forEach(function(marker) {
       marker.infowindow.close(map, marker);
    }); 
  }

  function calcRoute()
  {
    for(var a = 0; a < driver_jobs.length; a++)
    {
      if(driver_jobs[a].status == "starting")
      {
        let postal_code = "Singapore "+driver_jobs[a].postal_code;
        // address = address.replace(/(\r\n|\n|\r)/gm, " ");

        geocoder.geocode( { 'address': postal_code }, function(results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
            if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {

              var start_location = my_pos.lat+" "+my_pos.lng;
              var end_location = results[0].geometry.location.lat()+" "+results[0].geometry.location.lng();

              var directionsDisplay = new google.maps.DirectionsRenderer({
                suppressMarkers: true
              });
              var directionsService = new google.maps.DirectionsService();

              directionsDisplay.setMap(map);

              var request = {
                origin : start_location,
                destination : end_location,
                travelMode : google.maps.TravelMode.DRIVING,
                unitSystem : google.maps.UnitSystem.IMPERIAL
              };

              directionsService.route(request, (result, status) => {
                if(status == google.maps.DirectionsStatus.OK)
                {
                  directionsDisplay.setDirections(result);
                }
                else
                {
                  directionsDisplay.setDirections({ route: []});
                  map.setCenter(my_pos);
                }
              });
            }
          }
        });
        break;
      }
    }
  }

  function generateJobsTable()
  {
    jobs_table.clear();
    for(var a = 0; a < driver_jobs.length; a++)
    {
      let job = driver_jobs[a];
      var html = "";
      html += '<tr>';

      let starting = 1;
      if(job.status == "starting")
        starting = 0;

      if(!job.name)
        job.name = "";

      if(!job.contact_number)
        job.contact_number = "";

      html += '<td data-order="'+starting+'">';
      html += '<div class="status_icon" style="background: '+job.color+'"></div>';
      html += '</td>';
      html += '<td>'+job.name+'</td>';
      html += '<td>'+job.contact_number+'</td>';
      html += '<td>'+job.address+'</td>';
      html += '<td>';
      if(job.est_delivery_from && job.est_delivery_to)
      {
        html += job.est_delivery_from_text+' - '+job.est_delivery_to_text;
      }

      html += '</td>';
      html += '<td>S$ '+job.price_text+'</td>';

      html += "<td>";
      if(!have_job)
      {
        html += '<button class="btn btn-primary select" job_id="'+job.id+'" onclick="selectJob(this)">Select</button>';
        html += '<button class="btn btn-danger cancel" job_id="'+job.id+'" onclick="cancelJob(this)">Cancel</button>';
      }
      else
      {
        if(job.status == "starting")
        {
          html += '<button class="btn btn-success complete" job_id="'+job.id+'" onclick="completeJob(this)">Complete</button>';
          html += '<button class="btn btn-secondary direction" location="'+job.postal_code+'" onclick="jobDirection(this)">Direction</button>';
        }
        else
        {
          html += '<button class="btn btn-primary select" job_id="'+job.id+'" disabled onclick="selectJob(this)">Select</button>';
        }
        let display = "";
        let disabled = "";
        if(job.status == "starting")
        {
          display = "none";
          disabled = "disabled";
        }
        html += '<button class="btn btn-danger cancel" job_id="'+job.id+'" style="display: '+display+';" '+disabled+' onclick="cancelJob(this)">Cancel</button>';
      }
      html += '</td>';
      html += '</tr>';

      jobs_table.row.add($(html)).node();
    }

    jobs_table.draw();
  }

  function generateStatusAlert(driver_status_list, urgent_2, urgent_4)
  {
    var html = "";
    for(var a = 0; a < driver_status_list.length; a ++)
    {
      let driver_status = driver_status_list[a];
      html += '<div class="col-sm-12 col-md-6">';
      html += '<div class="jobs_status_box">';
      html += '<div class="jobs_status_content">';
      html += '<div class="status_icon" style="background: '+driver_status.color+'"></div>';
      html += '<label>'+driver_status.status+'</label>';
      html += '</div>';
      html += '<span>'+driver_status.count+'</span>';
      html += '</div>';
      html += '</div>';
    } 

    $("#driver_status_list").html(html);

    if(urgent_2 > 0)
    {
      $("#urgent_two_box").show();
      $("#total_urgent_hours_two").html(urgent_2);
    }
    else
    {
      $("#urgent_two_box").hide();
    }

    if(urgent_4 > 0)
    {
      $("#urgent_four_box").show();
      $("#total_urgent_hours_four").html(urgent_4);
    }
    else
    {
      $("#urgent_four_box").hide();
    }
  }

  function selectJob(_this)
  {
    var r = confirm("Are you sure you want to proceed this job?");
    if (r == true) {
      var job_id = $(_this).attr("job_id");
      $("button.select").attr("disabled", true);
      $.post("{{ route('driverStartJobs') }}", { "_token" : "{{ csrf_token() }}", "job_id" : job_id }, function(result){
        
        $(_this).removeClass("select, btn-primary").addClass("complete, btn-success").attr("disabled", false).html("Complete");
        $(_this).siblings("select.cancel").hide().attr("disabled", true);

        var driver_jobs_info = result.driver_jobs_info;
        driver_jobs = driver_jobs_info.driver_jobs;
        have_job = driver_jobs_info.have_job;

        generateJobsTable();
        generateStatusAlert(driver_jobs_info.driver_status_list, driver_jobs_info.total_urgent_hours_two, driver_jobs_info.total_urgent_hours_four);
        calcRoute();
      }).fail(function(xhr){
        if(xhr.status == 401)
        {
          loggedOutAlert();
        }
        else
        {
          sendError("Something wrong, please refresh and try again.", 0);
        }
      });
    }
  }

  function completeJob(_this)
  {
    var job_id = $(_this).attr("job_id");
    $("input[name='job_id']").val(job_id);
    $("#completeModal").modal('show');
  }

  function cancelJob(_this)
  {
    var r = confirm("Are you sure you want to remove this job?");
    if (r == true) {
      var job_id = $(_this).attr("job_id");
      $(_this).attr("disabled", true);

      $.post("{{ route('cancelJob') }}", { "_token" : "{{ csrf_token() }}", "job_id" : job_id }, function(result){

        var driver_jobs_info = result.driver_jobs_info;
        driver_jobs = driver_jobs_info.driver_jobs;
        have_job = driver_jobs_info.have_job;

        generateJobsTable();
        generateStatusAlert(driver_jobs_info.driver_status_list, driver_jobs_info.total_urgent_hours_two, driver_jobs_info.total_urgent_hours_four);
      }).fail(function(xhr){
        if(xhr.status == 401)
        {
          loggedOutAlert();
        }
        else
        {
          sendError("Something wrong, please refresh and try again.", 0);
        }
      });
    }
  }

  function jobDirection(_this)
  {
    window.open("https://www.google.com/maps/dir/"+my_pos.lat+" "+my_pos.lng+"/"+$(_this).attr('direction'))+"/am=t/";
  }

  function resizeCanvas()
  {
    var ratio =  Math.max(window.devicePixelRatio || 1, 1);

    // This part causes the canvas to be cleared
    signature_canvas.width = signature_canvas.offsetWidth * ratio;
    signature_canvas.height = signature_canvas.offsetHeight * ratio;
    signature_canvas.getContext("2d").scale(ratio, ratio);

    signaturePad.clear();
  }

</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCNp9DSX6ILJhAQMtZhd2IDd0KPJOVOLN8&v=weekly" async ></script>

@endsection