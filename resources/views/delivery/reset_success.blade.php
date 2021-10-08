@extends('layouts.app')

@section('content')

  <div class="login_box">
    <div class="login_wrapper" style="text-align: center;">
      <i class="fas fa-check-circle" style="font-size: 50px; color: green;"></i>
      <h4 style="margin: 30px 0;">Password reset successful.</h4>

      <a href="{{ route('login') }}" class="btn btn-secondary" style="margin-bottom: 10px;">Back to Login page</a>
      <p>Redirect back to login back in <span id="seconds">5</span> seconds.</p>
    </div>
  </div>

  <script>
    var seconds = 5;
    var secondsInt;

    secondsInt = setInterval(function(){
      seconds--;
      $("#seconds").html(seconds);
      if(seconds == 0)
      {
        clearInterval(secondsInt);
      }
    }, 1000);

    setTimeout(function(){
      window.location = "{{ route('login') }}";
    }, 5000);

  </script>

@endsection