@extends('layouts.app')

@section('content')

@include('driver.header')

<div class="container" style="padding-top: 10px;">
  <form method="POST" action="{{ route('driverAcceptJobs') }}" id="accept_job_form">
    @csrf
    <div class="row">
      <div class="col-12">
        <h4>PICK UP AT : {{ $pick_up->name }}</h4>
      </div>
      @foreach($driver_jobs as $job)
        <div class="col-12">
          <div class="job_box">
            <h5>
              <div class="checkbox icheck" style="display: inline-block; margin-right: 10px;">
                <label style="margin-bottom: 0px;">
                  <input class="form-check-input" type="checkbox" name="accept_job[]" value="{{ $job->id }}" />
                </label>
              </div>
              {{ $job->name }}
            </h5>
            <label>Address : {{ $job->address }}</label><br>
            <label>Deliver from  : 
              @if($job->est_delivery_from && $job->est_delivery_to)
                {{ date('d M Y h:i A', strtotime($job->est_delivery_from)) }}
              @else
                -
              @endif
            </label>
            <label>Deliver to  : 
              @if($job->est_delivery_from && $job->est_delivery_to)
                {{ date('d M Y h:i A', strtotime($job->est_delivery_to)) }}
              @else
                -
              @endif
            </label>
            <label>Customer remarks : {{ $job->remarks }}</label>
            <label>Wallet value : $S {{ number_format($job->price, 2) }}</label>
          </div>
        </div>
      @endforeach
    </div>
    <button class="btn btn-success" type="button" id="accept_job_btn" style="margin-top: 15px;">Accept Jobs</button>
  </form>
</div>

<script>
  
  $(document).ready(function(){
    $(".job_box").click(function(){
      var checkbox = $(this).find("input[type='checkbox']");
      if(checkbox.is(":checked"))
      {
        checkbox.iCheck('uncheck');
        $(this).removeClass("selected");
      }
      else
      {
        checkbox.iCheck('check');
        $(this).addClass("selected");
      }
    });

    $("#accept_job_btn").click(function(e){
      if($("input[name='accept_job[]']:checked").length == 0)
      {
        showError("Please select jobs before you proceed.", 0);
        return;
      }
      else
      {
        $("#accept_job_form").submit();
      }
    });
  });

</script>

@endsection