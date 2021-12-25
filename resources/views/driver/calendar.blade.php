@extends('layouts.app')

@section('content')

@include('driver.header')

<style>

  #calendar {
    max-width: 900px;
    margin: 0 auto;
  }

</style>

<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div style="box-shadow: 0 1px 5px 3px #ccc; padding: 10px; margin: 10px;">
        <div id='calendar'></div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="jobsModal" role="dialog" aria-labelledby="jobsModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false" style="z-index: 10000">
  <div class="modal-dialog" role="document" style="width: 1200px; max-width: 95%;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="jobsModalLabel">Job Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table id="jobs_table" class="table table-bordered table-striped" cellspacing="0" width="100%">
          <thead>
            <th>Status</th>
            <th>Name</th>
            <th>Contact Number</th>
            <th>Address</th>
            <th>Expected Delivery Date Time</th>
            <th>Job Assigned Date</th>
            <th>Wallet Value</th>
            <th>Job Created Date</th>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>


<script>
  var events = @json($events);
  var driver_jobs = @json($driver_jobs);

  var jobs_table = $("#jobs_table").DataTable( {
    responsive: true,
    order: [[ 7, "desc" ]]
  });
  
  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
      editable: true,
      selectable: true,
      businessHours: true,
      dayMaxEvents: true, // allow "more" link when too many events
      events: events,

      eventClick: function(info) {
        jobs_table.clear().draw();

        for(var a = 0; a < info.event.extendedProps.id_array.length; a++)
        {
          for(var b = 0; b < driver_jobs.length; b++)
          {
            if(driver_jobs[b].id == info.event.extendedProps.id_array[a])
            {
              var data = [
                driver_jobs[b].status_text,
                driver_jobs[b].name,
                driver_jobs[b].contact_number,
                driver_jobs[b].address,
                driver_jobs[b].expected_delivery,
                driver_jobs[b].assigned_at_text,
                driver_jobs[b].price,
                driver_jobs[b].created_at_text,
              ];

              jobs_table.row.add(data);
              break;
            }
          }
        }
        
        jobs_table.draw();
        jobs_table.responsive.recalc();

        $("#jobsModal").modal('show');
      }
    });

    calendar.render();
  });

</script>

@endsection