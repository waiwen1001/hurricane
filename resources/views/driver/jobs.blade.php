@extends('layouts.app')

@section('content')

@include('driver.header')

<div style="padding: 10px 20px 30px 20px;">
  <div class="row accordion" id="accordion">
    @foreach($pick_up_list as $pick_up)
      @if(count($pick_up->job_list) > 0)
        <div class="col-12" style="margin-bottom: 20px;">
          <div class="jobs_box">

            <div class="card">
              <div class="card-header" id="headingTwo">
                <h5 class="mb-0" data-toggle="collapse" data-target="#collapse_{{ $pick_up->id }}" aria-expanded="true" aria-controls="collapse_{{ $pick_up->id }}" style="cursor: pointer;">
                  Pick up location : {{ $pick_up->name }}
                  @if($pick_up->disabled == 1)
                    <button type="button" class="btn btn-primary arrive_proof" style="float: right;" pick_up_id="{{ $pick_up->id }}" pick_up_name="{{ $pick_up->name }}">Arrive proof</button>
                  @endif
                </h5>
              </div>
              <div id="collapse_{{ $pick_up->id }}" class="collapse show" aria-labelledby="heading_{{ $pick_up->id }}" data-parent="#accordion">
                <div class="card-body">
                  <table class="jobs_table table table-bordered table-striped" pick_up_id="{{ $pick_up->id }}" cellspacing="0" width="100%">
                    <thead>
                      <th></th>
                      <th>Name</th>
                      <th>Contact Number</th>
                      <th>Address</th>
                      <th>Expected Delivery Date Time</th>
                      <th>Job Assigned Date</th>
                      <th>Wallet Value</th>
                      <th style="min-width: 100px;">Action</th>
                    </thead>
                    <tbody>
                      @foreach($pick_up->job_list as $job)
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
                          <td data-order="{{ $job->assigned_at }}">
                            @if($job->assigned_at)
                              {{ date('d M Y h:i A', strtotime($job->assigned_at)) }}
                            @endif
                          </td>
                          <td>S$ {{ number_format($job->price, 2) }}</td>
                          <td>
                            @if($job->status == "new job")
                              <span style="color: red;">Please upload arrive proof before you proceed.</span>
                            @elseif($job->completed == null)
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
                            @else
                              <span style="color: green;">
                                Jobs completed. <br>
                                <span>Completed at : {{ $job->assigned_at_text }}</span>
                              </span>
                            @endif
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

          </div>
        </div>
      @endif
    @endforeach
    
    <div class="col-12" style="margin-bottom: 20px;">
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

        <div class="col-sm-12 col-md-6" style="margin-top: 20px;">
          <a href="{{ route('downloadDriverJobs') }}" type="button" class="btn btn-primary">
            <i class="fas fa-file-export"></i>Export Job list
          </a>
        </div>
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
    <div class="modal-dialog" role="document" style="width: 1200px; max-width: 95%;">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="completeModalLabel">Delivered</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
                <td style="min-width: 250px;">Plan A</td>
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
                  <label style="display: block;">POD (Unit number with items)
                    <span class="required"></span>
                  </label>
                  <input type="file" name="file_pod[]" accept="image/*" required multiple />
                </td>
              </tr>
              <tr>
                <td colspan="3">
                  <label style="display: block;">Customer Signature
                    @if($user->user_type == "driver" && $user->driver_type != "contractor")
                      <span class="required"></span>
                    @endif
                  </label>
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

  <div class="modal fade" id="arriveProofModal" role="dialog" aria-labelledby="arriveProofModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document" style="width: 1200px; max-width: 95%;">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="arriveProofModalLabel">Arrive Proof</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" style="max-height: 500px; overflow-x: hidden; overflow-y: auto;">
          <form method="POST" action="{{ route('submitPickUp') }}">
            @csrf
            <div class="row">
              <div class="col-sm-12">
                <div style="border-bottom: 1px solid #ccc; margin-bottom: 10px; padding-bottom: 10px;">
                  Submit arrive proof to pick up point : <label style="font-weight: bold; margin-bottom: 0px;" id="pick_up_name"></label>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group">
                  <label style="width: 100%;">Unit number</label>
                  <input type="file" name="file_unit_number[]" accept="image/*" required multiple />
                </div>
              </div>

              <div class="col-sm-6">
                <div class="form-group">
                  <label style="width: 100%;">Items</label>
                  <input type="file" name="file_items[]" accept="image/*" required multiple />
                </div>
              </div>

              <div class="col-sm-6" style="clear: both;">
                <div class="form-group">
                  <label style="width: 100%;">Signatory</label>
                  <input type="file" name="file_signatory[]" accept="image/*" required multiple />
                </div>
              </div>

              <div class="col-sm-12">
                <input type="hidden" name="pick_up" id="pick_up_id" />
                <button type="submit" class="btn btn-success">Submit</button>
              </div>
            </div>
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
        <div style="width: 100%; text-align: left;">
          <button class="btn btn-secondary" id="clear_signature">Clear</button>
          <button class="btn btn-primary" id="undo_signature">Undo</button>
          <button class="btn btn-success" id="submit_signature">Sign</button>
          <button class="btn btn-danger" style="float: right;" id="close_signature">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  
  let geocoder;
  let jobs_table = [];
  var driver_jobs = @json($driver_jobs);
  var pick_up_list = @json($pick_up_list);
  var user = @json($user);
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

    $(".jobs_table").each(function(){
      var pick_up_id = $(this).attr("pick_up_id");
      jobs_table['pick_up_'+pick_up_id] = $(".jobs_table[pick_up_id="+pick_up_id+"]").DataTable({
      responsive: true,
        scrollY: '450px',
        scrollCollapse: true,
      });
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

    $("#close_signature").click(function(){
      $(".signature_box").removeClass("active");
      $("#completeModal").modal('show');
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
      if($("input[name='file_pod[]']").val() == "" || !$("input[name='file_pod[]']").val())
      {
        showError("POD images is compulsory.", 0);
        return;
      }

      // if(user.user_type == "driver" && user.driver_type != "contractor")
      // {
      //   if(!signaturePad)
      //   {
      //     showError("Client signature is compulsory.", 0);
      //     return;
      //   }
      //   else
      //   {
      //     if(signaturePad.isEmpty())
      //     {
      //       showError("Client signature is compulsory.", 0);
      //       return;
      //     }
      //     else
      //     {
      //       $("#submitCompleteJobForm").submit();
      //     }
      //   }
      // }
      // else
      // {
      //   $("#submitCompleteJobForm").submit();
      // }

      $("#submitCompleteJobForm").submit();
    });

    initMap_timeout = setTimeout(function(){
      if($("#map").html() == "")
      {
        initMap();
      }
    }, 1000);

    $(".arrive_proof").click(function(){
      $("#pick_up_id").val($(this).attr("pick_up_id"));
      $("#pick_up_name").html($(this).attr("pick_up_name"));
      $("#arriveProofModal").modal('show');
    });
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
    for(var b = 0; b < pick_up_list.length; b++)
    {
      var job_table = jobs_table["pick_up_"+pick_up_list[b].id].clear();
      for(var a = 0; a < pick_up_list[b].job_list.length; a++)
      {
        let job = pick_up_list[b].job_list[a];
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
        html += "<td>"+job.assigned_at_text+"</td>";
        html += '<td>S$ '+job.price_text+'</td>';

        html += "<td>";
        if(job.status == "new job")
        {
          html += "<span style='color: red;'>Please upload arrive proof before you proceed.</span>";
        }
        else if(job.completed == null)
        {
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
              html += '<button class="btn btn-secondary direction" direction="'+job.postal_code+'" onclick="jobDirection(this)">Direction</button>';
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
        }
        else
        {
          html += "<span style='color: green;'>Jobs completed.<br><span>Completed at : "+job.assigned_at_text+"</span></span>";
        }
        
        html += '</td>';
        html += '</tr>';

        job_table.row.add($(html)).node();
      }

      job_table.draw();
    }
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
        pick_up_list = driver_jobs_info.pick_up_list;

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

    $("input[name='file_pod[]']").val("");
    if(signaturePad)
    {
      signaturePad.clear();
      $("#signature_img_box").hide();
      document.getElementById('signature_img').src = "";
    }

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
        pick_up_list = driver_jobs_info.pick_up_list;

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
    console.log(_this);
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