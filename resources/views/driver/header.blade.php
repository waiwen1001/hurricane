<header>
  <div class="restaurant_header">
    <div class="restaurant_menu">
      <div class="menu_icon">
        <i class="fas fa-bars"></i>
      </div>
      <div class="menu">
        <div class="menu_content">
          <div class="menu_btn close_menu">
            <div class="menu_sub_icon">
              <i class="fas fa-chevron-left"></i>
            </div>
            Close menu
          </div>
          @if($user->user_type == "driver")
            <div class="menu_btn">
              <a href="{{ route('getDriverJobs') }}">My Jobs</a>
            </div>
            <div class="menu_btn">
              <a href="{{ route('getDriverPickUp') }}">Pick Up</a>
            </div>
            <div class="menu_btn">
              <a href="{{ route('getDriverJobsList') }}">Jobs List</a>
            </div>
            <div class="menu_btn">
              <a href="{{ route('getDriverCalendar') }}">Calendar</a>
            </div>
            <div class="menu_btn">
              <a href="{{ route('manual_logout') }}">Logout</a>
            </div>

            <div class="menu_footer">
              <div style="background: #4caf50; color: #fff; padding: 0 10px;">
                <i class="fas fa-user"></i>
                <label style="float: right; margin: 0px;">
                  @if(Auth::check())
                    {{ Auth::user()->name }}
                  @endif
                </label>
              </div>
              <div class="user_wallet">
                <i class="fas fa-wallet"></i>
                <label>S$ {{ number_format($user->wallet, 2) }}</label>
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>

    <div style="height: 50px; float: right; display: flex; align-items: center; color: #fff; font-size: 30px;">
      <i class="fas fa-clock"></i>
      <h3 id="clock"></h3>
    </div>
    
  </div>
</header>

<script>
  
  $(document).ready(function(){
    showTime();
  });

  function showTime(){
    var date = new Date();
    var h = date.getHours(); // 0 - 23
    var m = date.getMinutes(); // 0 - 59
    var s = date.getSeconds(); // 0 - 59
    var session = "AM";
    
    if(h == 0){
        h = 12;
    }
    
    if(h > 12){
        h = h - 12;
        session = "PM";
    }
    
    h = (h < 10) ? "0" + h : h;
    m = (m < 10) ? "0" + m : m;
    s = (s < 10) ? "0" + s : s;
    
    var time = h + ":" + m + ":" + s + " " + session;
    document.getElementById("clock").innerText = time;
    document.getElementById("clock").textContent = time;
    
    setTimeout(showTime, 1000);
  }

</script>