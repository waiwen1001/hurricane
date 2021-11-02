@extends('layouts.app')

@section('content')

<style>
  
  .youtube { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); width: 100%; text-align: center; }
  .youtube label { margin: 0px;  }
  .youtube input { width: 30px ; border: 1px solid #ccc; border-radius: 5px; margin: 0 5px; text-align: center; }

  #link.failed { animation: failed 200ms linear 2; color: red; }
  #result { color: red; display: none; }
  #url a { color: green; }

  @keyframes failed {
    0% { margin-left: 0px; }
    25% { margin-left: -30px; }
    50% { margin-left: 0px; }
    75% { margin-left: 30px; }
    100% { margin-left: 0px; }
  }

</style>

<div class="youtube">
  <div style="height: 100px;">
    <label id="link">
      <span number=1>https://youtu.be/TDv</span>
        <input type="text" class="input" number=1 maxlength="1" />
      <span number=2>iC</span>
        <input type="text" class="input" number=2 maxlength="1" />
      <span number=3>KT</span>
        <input type="text" class="input" number=3 maxlength="1" />
      <span number=4>Q</span>
    </label>
    <br>
    <span id="result" style="width: 100%;">Incorrect input</span>
    <span id="url" style="display: none; width: 100%; text-align: center; float: left;">
      >>> <a href="https://youtu.be/TDvyiCoKTlQ">https://youtu.be/TDvyiCoKTlQ</a> <<<
    </span>
  </div>
</div>

<script>
  
  var val = "https://youtu.be/TDvyiCoKTlQ";
  var timeout;

  $(document).ready(function(){
    $(".input").keyup(function(){
      var num = parseInt($(this).attr("number"));

      if(num == 3)
      { 
        checkResult();
      }
      else
      {
        $(".input[number="+(num + 1)+"]").focus();
      }
    });
  });

  function checkResult()
  {
    var check = $("#link").children("span[number=1]").html() + $("input[number=1]").val() + $("#link").children("span[number=2]").html() + $("input[number=2]").val() + $("#link").children("span[number=3]").html() + $("input[number=3]").val() + $("#link").children("span[number=4]").html();

    if(check != val)
    {
      $("#link").removeClass("failed").addClass("failed");
      $("input").val("");
      $("input[number=1]").focus();
      $("#result").show();

      clearTimeout(timeout);
      timeout = setTimeout(function(){
        $("#link").removeClass("failed");
        $("#result").hide();
      }, 1000);
    }
    else
    {
      $("#url").fadeIn();
    }
  }

</script>

@endsection