@extends('layouts.app')

@section('content')

<style>
  
  .final { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); width: 100%; text-align: center; }
  .clock { position: absolute; left: 0%; top: 50px; width: 100%; text-align: center; font-size: 30px; color: #00bcd4; }
  .point { position: absolute; left: 0%; top: 100px; width: 100%; text-align: center; font-size: 30px; color: #ff9800; transition: color 1000ms linear; }
  .question { position: absolute; top: 180px; width: 100%; padding: 0 50px; font-size: 25px; }
  .question div div a i { display: none; padding-right: 10px; }

  .highlight { font-weight: bold; color: green; }
  .highlight.red { color: red; }
  a.completed { text-decoration: line-through; color: green; }
  a.completed i { display: inline-block !important; }
  .point.deduct { color: red; }

  .puzzle_box { position: relative; height: 150px; width: 100%; }
  .puzzle_box div { position: absolute; background: #000; }
  .info { position: absolute; top: 60%; display: flex; width: 100%; justify-content: space-around; }

</style>

<div>
  @if($completed == 1)
    <div class="clock" style="display : block;"><span id="hour">{{ date("H", strtotime($total_used_format)) }}</span> : <span id="minute">{{ date("i", strtotime($total_used_format)) }}</span> : <span id="second">{{ date("s", strtotime($total_used_format)) }}</span></div>
  @elseif($duration)
    <div class="clock" style="display : {{ $final ? 'block' : 'none' }};"><span id="hour">{{ date("H", strtotime($duration)) }}</span> : <span id="minute">{{ date("i", strtotime($duration)) }}</span> : <span id="second">{{ date("s", strtotime($duration)) }}</span></div>
  @else
    <div class="clock" style="display : {{ $final ? 'block' : 'none' }};"><span id="hour">00</span> : <span id="minute">00</span> : <span id="second">00</span></div>
  @endif

  <div class="point" style="display : {{ $final ? 'block' : 'none' }};"><span id="point">
    @if($final)
      {{ $final->point }}
    @endif
  </span> Points</div>
  <div class="final">
    @if(!$final)
      <button class="btn btn-success" style="font-size: 30px;" id="start">Start</button>
    @endif
  </div>

  <div class="question" style="display : {{ $final ? 'block' : 'none' }};">
    <div>
      <div>1) <a href="#" id="show_logic" class="{{ $q1 == 1 ? 'completed' : '' }}"><i class="fas fa-check-circle"></i>Logic</a></div>
      <div>2) <a href="#" id="show_math" class="{{ $q2 == 1 ? 'completed' : '' }}"><i class="fas fa-check-circle"></i>Math</a></div>
      <div>3) <a href="#" id="show_puzzle" class="{{ $q3 == 1 ? 'completed' : '' }}"><i class="fas fa-check-circle"></i>Puzzle</a></div>
    </div>
  </div>

  <div class="info">
    <button type="button" class="btn btn-primary" id="rules_btn">Rules</button>
    <button type="button" class="btn btn-success" style="display : {{ $completed == 1 ? 'block' : 'none' }};" id="rewards_btn">Rewards</button>
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
          <li>There will be <span class="highlight">3</span> question to be answer.</li>
          <li>Initial you have <span class="highlight">1000 Point</span>, every <span class="highlight red">10 minutes</span> will <span class="highlight red">deduct 100 points</span>.</li>
          <li>Once you click the <span class="highlight">Start</span> button, the time clock will start counting.</li>
          <li>Usage of points.
            <ul>
              <li>300 point - Bubble Tea</li>
              <li>600 point - Korean food</li>
              <li>1000 point - Haidilao</li>
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

<div class="modal fade" id="logic_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">1) Logic</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div style="box-shadow: 0px 1px 5px 1px #ccc; padding: 10px;">
          小明需要驾驶汽车从A货仓至B货仓取货物, 从A驾驶至B需 80KM/H 和需耗时30分钟。 汽车最大容量为100KG, 若每5KG会导致驾驶速度减少1%。 如果不考虑上货取货等其他时间, 且小明最高时速是80KM/H。 请问搬运1000KG, 小明走了多少KM?
          <br><br>
          Alex needs to drive a car to pick up goods from warehouse A to warehouse B. Its takes 30 minutes to drive from A to B if driving in 80KM/H. The maximum capacity of the car is 100KG. If every 5KG will reduce the driving speed by 1%. If we are not considered the loading goods or any other times consuming factor, and the car maximum speed is 80KM/H. How many KM did Alex need to drive to complete delivered 1000KG of goods?
        </div>

        <div style="margin-top: 10px;">
          <input type="number" id="logic_answer" class="form-control" style="width: calc(100% - 30px); display: inline-block;" /> KM
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success" id="answer_logic">Answer</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="math_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">2) Math</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div style="box-shadow: 0px 1px 5px 1px #ccc; padding: 10px;">
          <p style="margin: 0px;">x = (3y * -y) - 60</p>
          <p style="margin: 0px;">y = 5 - 10</p>
          <br>
          <p style="margin: 0px;">(?)x + 6y = 45</p>
          <br>
          <p style="margin: 0px;">What is value of (?).</p>
        </div>

        <div style="margin-top: 10px;">
          <input type="number" id="math_answer" class="form-control" />
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success" id="answer_math">Answer</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="puzzle_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">3) Puzzle</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div style="box-shadow: 0px 1px 5px 1px #ccc; padding: 20px;">
          <div class="puzzle_box">
            <div style="left: 0px; top: 0px; width: 2px; height: 80px;"></div>
            <div style="left: 0px; top: 40px; width: 40px; height: 2px;"></div>
            <div style="left: 40px; top: 0px; width: 2px; height: 78px;"></div>
            <div style="left: 40px; top: 78px; width: 40px; height: 2px;"></div>

            <div style="left: 80px; top: 0px; width: 40px; height: 2px;"></div>
            <div style="left: 120px; top: 0px; width: 2px; height: 40px;"></div>
            <div style="left: 80px; top: 38px; width: 40px; height: 2px;"></div>

            <div style="left: 160px; top: 0px; width: 40px; height: 2px;"></div>
            <div style="left: 200px; top: 0px; width: 2px; height: 78px;"></div>
            <div style="left: 160px; top: 38px; width: 40px; height: 2px;"></div>
            <div style="left: 160px; top: 38px; width: 2px; height: 40px;"></div>
            <div style="left: 122px; top: 78px; width: 40px; height: 2px;"></div>

            <div style="left: 200px; top: 78px; width: 40px; height: 2px;"></div>
            <div style="left: 240px; top: 0px; width: 2px; height: 80px;"></div>
            <div style="left: 240px; top: 0px; width: 40px; height: 2px;"></div>
            <div style="left: 280px; top: 0px; width: 2px; height: 40px;"></div>
            <div style="left: 240px; top: 38px; width: 40px; height: 2px;"></div>

            <div style="left: 0px; bottom: 0px; background: #fff; color: #333;">
              Puzzle above are represent 4 number, write the number you see in below.
            </div>
          </div>
        </div>

        <div style="margin-top: 10px;">
          <input type="number" id="puzzle_answer" class="form-control" />
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success" id="answer_puzzle">Answer</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="completed_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">YEAH!!!!!</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        You have completed the games !!<br>
        your remaining point : 
          <span id="remaining_point">
            @if($final)
              {{ $final->point }}
            @endif
          </span>points.<br>
        Used time : <span id="used_times">{{ $total_used }}</span><br>
        Redeem your rewards : <br>

        <form id="rewards_form">
          @csrf
          <div class="checkbox icheck">
            <label>
              <input class="form-check-input" type="checkbox" name="rewards[]" value="b_tea" {{ $completed == 1 && $final ? $final->b_tea == 1 ? 'checked' : '' : '' }} /> Bubble Tea ( 300 points )
            </label>
          </div>
          <div class="checkbox icheck">
            <label>
              <input class="form-check-input" type="checkbox" name="rewards[]" value="kr_food" {{ $completed == 1 && $final ? $final->kr_food == 1 ? 'checked' : '' : '' }} /> Korean food ( 600 points )
            </label>
          </div>
          <div class="checkbox icheck">
            <label>
              <input class="form-check-input" type="checkbox" name="rewards[]" value="hdl" {{ $completed == 1 && $final ? $final->hdl == 1 ? 'checked' : '' : '' }} /> Haidilao ( 1000 points )
            </label>
          </div>
        </div>
      </form>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success" id="claim">Claim</button>
      </div>
    </div>
  </div>
</div>

<script>
  
  var duration = "{{ $duration }}";
  var final = @json($final);
  var start_clock;
  var completed = "{{ $completed }}";

  $(document).ready(function(){
    if(final == null)
    {
      $("#rules_modal").modal('show');
    }

    $('.form-check-input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%' /* optional */
    });

    $("#show_logic").click(function(){
      if(!$(this).hasClass("completed"))
      {
        $("#logic_modal").modal('show');
      }
    });

    $("#show_math").click(function(){
      if(!$(this).hasClass("completed"))
      {
        $("#math_modal").modal('show');
      }
    });

    $("#show_puzzle").click(function(){
      if(!$(this).hasClass("completed"))
      {
        $("#puzzle_modal").modal('show');
      }
    });

    $("#start").click(function(){
      $("#start").attr("disabled", true);

      $.post("{{ route('startFinal') }}", {"_token" : "{{ csrf_token() }}"}, function(result){
        $("#start").hide();
        $(".question").show();
        $(".clock").show();
        $(".point").show();
        $("#point").html("1000");
        start_clock = setInterval(startTheClock, 1000);
      });
    });

    if(duration != "" && completed == "")
    {
      start_clock = setInterval(startTheClock, 1000);
    }

    if(completed == 1)
    {
      $("#completed_modal").modal('show');
    }

    $("#answer_logic").click(function(){
      var logic_answer = $("#logic_answer").val();
      if(logic_answer == 800)
      {
        answered("q1");
      }
      else
      {
        Swal.fire({
          title: "Wrong answer.",
          icon: 'error',
          confirmButtonText: 'OK',
        });

        $("#logic_answer").val("");
      }
    });

    $("#answer_math").click(function(){
      var math_answer = $("#math_answer").val();
      if(math_answer == 5)
      {
        answered("q2");
      }
      else
      {
        Swal.fire({
          title: "Wrong answer.",
          icon: 'error',
          confirmButtonText: 'OK',
        });

        $("#math_answer").val("");
      }
    });

    $("#answer_puzzle").click(function(){
      var puzzle_answer = $("#puzzle_answer").val();
      if(puzzle_answer == 4238)
      {
        answered("q3");
      }
      else
      {
        Swal.fire({
          title: "Wrong answer.",
          icon: 'error',
          confirmButtonText: 'OK',
        });

        $("#puzzle_answer").val("");
      }
    });

    $("#rewards_btn").click(function(){
      $("#completed_modal").modal('show');
    });

    $("#rules_btn").click(function(){
      $("#rules_modal").modal('show');
    });

    $("input[name='rewards[]']").on('ifChanged', function(){
      if(completed == 1)
      {
        if(final && $(this).is(":checked"))
        {
          var rewards_type = $(this).val();

          var used_point = 0;
          $("input[name='rewards[]']:checked").each(function(){
            if($(this).val() == "b_tea" && $(this).val() != rewards_type)
            {
              used_point += 300;
            }
            else if($(this).val() == "kr_food" && $(this).val() != rewards_type)
            {
              used_point += 600;
            }
            else if($(this).val() == "hdl" && $(this).val() != rewards_type)
            {
              used_point += 1000;
            }
          });

          var r_point = final.point - used_point;
        
          if(rewards_type == "b_tea" && r_point < 300)
          {
            Swal.fire({
              title: "Ooops, not enough point.",
              icon: 'error',
              confirmButtonText: 'OK',
            });

            setTimeout(function(){
              $("input[name='rewards[]'][value='b_tea']").iCheck('uncheck');
            }, 100);
          }
          else if(rewards_type == "kr_food" && r_point < 600)
          {
            Swal.fire({
              title: "Ooops, not enough point.",
              icon: 'error',
              confirmButtonText: 'OK',
            });

            setTimeout(function(){
              $("input[name='rewards[]'][value='kr_food']").iCheck('uncheck');
            }, 100);
            
          }
          else if(rewards_type == "hdl" && r_point < 1000)
          {
            Swal.fire({
              title: "Ooops, not enough point.",
              icon: 'error',
              confirmButtonText: 'OK',
            });
            
            setTimeout(function(){
              $("input[name='rewards[]'][value='hdl']").iCheck('uncheck');
            }, 100);
          }
        }
      }
      else
      {
        Swal.fire({
          title: "Hey! You haven't completed the games yet.",
          icon: 'error',
          confirmButtonText: 'OK',
        });
      }
    });

    $("#claim").click(function(){
      $.post("{{ route('claimRewards') }}", $("#rewards_form").serialize(), function(){
        Swal.fire({
          title: "Claimed!",
          icon: 'success',
          confirmButtonText: 'OK',
        });

        $("#completed_modal").modal('hide');
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

    if((minute % 10 == 0) && second == 0)
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
    $.post("{{ route('minusPoint') }}", {"_token" : "{{ csrf_token() }}", "point" : points }, function(){
      $(".point").addClass("deduct");
      var current_point = $("#point").html();

      current_point = parseInt(current_point) - parseInt(points);
      $("#point").html(current_point);
      setTimeout(function(){
        $(".point").removeClass("deduct");
      }, 1000)
    });
  }

  function answered(type)
  {
    if(type == "q1")
    {
      $("#answer_logic").attr("disabled", true);
    }
    else if(type == "q2")
    {
      $("#answer_math").attr("disabled", true);
    }

    $.post("{{ route('completedQuestion') }}", {"_token" : "{{ csrf_token() }}", "type" : type }, function(result){
      Swal.fire({
        title: "Correct answer.",
        icon: 'success',
        confirmButtonText: 'OK',
      });

      if(type == "q1")
      {
        $("#show_logic").addClass("completed");
        $("#logic_modal").modal('hide');
      }
      else if(type == "q2")
      {
        $("#show_math").addClass("completed");
        $("#math_modal").modal('hide');
      }
      else if(type == "q3")
      {
        $("#show_puzzle").addClass("completed");
        $("#puzzle_modal").modal('hide');
      }

      if(result.pending == 0)
      {
        $("#remaining_point").html(result.point);
        $("#used_times").html(result.hour+" hour "+result.minute+" minute "+result.seconds+" seconds");
        $("#completed_modal").modal('show');
        $("#rewards_btn").show();
        clearInterval(start_clock);

        completed = 1;
      }
    });
    
  }

</script>

@endsection