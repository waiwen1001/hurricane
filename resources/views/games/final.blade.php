@extends('layouts.app')

@section('content')

<style>
  
  .final { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); width: 100%; text-align: center; }
  .clock { position: absolute; left: 0%; top: 50px; width: 100%; text-align: center; font-size: 30px; color: #00bcd4; }
  .point { position: absolute; left: 0%; top: 100px; width: 100%; text-align: center; font-size: 30px; color: #ff9800; }
  .question { position: absolute; top: 180px; width: 100%; padding: 0 50px; font-size: 25px; }

  .highlight { font-weight: bold; color: green; }
  .highlight.red { color: red; }

</style>

<div>
  <div class="clock"><span id="hour">00</span> : <span id="minute">00</span> : <span id="second">00</span></div>
  @if($final)
    <div class="point"><span id="point">{{ $final->point }}</span> Points</div>
  @endif
  <div class="final">
    @if(!$final)
      <button class="btn btn-success" style="font-size: 30px;" id="start">Start</button>
    @endif
  </div>

  <div class="question">
    <div>
      1) <a href="#">Question 1</a>
    </div>

  </div>

</div>

<div class="modal fade" id="rules_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Rules and Rewards</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <ol>
          <li>There will be <span class="highlight">4</span> question to be answer.</li>
          <li>Initial you have <span class="highlight">1000 Point</span>, every <span class="highlight red">10 min</span> will <span class="highlight red">deduct 100 points</span>.</li>
          <li>Once you click the <span class="highlight">Start</span> button, the time clock will start counting.</li>
          <li>Usage of points.
            <ul>
              <li>50 point - Tips</li>
              <li>200 point - Bubble Tea</li>
              <li>500 point - Korean food</li>
              <li>850 point - Haidilao</li>
            </ul>
          </li>
        </ol>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-dismiss="modal">Okay</button>
      </div>
    </div>
  </div>
</div>

<script>
  
  $(document).ready(function(){
    // $("#rules_modal").modal('show');

    $("#start").click(function(){
      $("#start").attr("disabled", true);

      $.post("{{ route('startFinal') }}", {"_token" : "{{ csrf_token() }}"}, function(result){
        $("#start").hide();
        setInterval(startTheClock, 100);
      });

    });
  });

  function startTheClock()
  {
    var hour = parseInt($("#hour").html());
    var minute = parseInt($("#minute").html());
    var second = parseInt($("#second").html());

    second = parseInt(second) + 1;
    if(second == 60)
    {
      second = 0;
      minute = parseInt(minute) + 1;
    }

    if(second < 10)
    {
      second = "0"+second;
    }

    if(minute == 10)
    {
      minusPoint(100);
    }

    if(minute == 60)
    {
      minute = 0;
      hour = parseInt(hour) + 1;
    }

    if(minute < 10)
    {
      minute = "0"+minute;
    }

    if(hour < 10)
    {
      hour = "0"+hour;
    }

    $("#hour").html(hour);
    $("#minute").html(minute);
    $("#second").html(second);
  }

  function minusPoint(points)
  {
    $.post("{{ route('minusPoint') }}", {"_token" : "{{ csrf_token() }}", "point" : point }, function(){

    });
  }

</script>

@endsection