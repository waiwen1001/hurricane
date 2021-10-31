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
          @if($user->user_type == "delivery")
            <div class="menu_btn">
              <a href="{{ route('getAdminHome') }}">Home Page</a>
            </div>
            <div class="menu_btn">
              <a href="{{ route('getRestaurant') }}">Restaurant Page</a>
            </div>
            <div class="menu_btn">
              <a href="{{ route('getAdminDriver') }}">Driver Page</a>
            </div>
            <div class="menu_btn">
              <a href="{{ route('getAdminJobsList') }}">Jobs List</a>
            </div>
            <div class="menu_btn">
              <a href="{{ route('getAdminAutoRoute') }}">Auto Route</a>
            </div>
            <div class="menu_btn">
              <a href="{{ route('getAdminReport') }}">Report</a>
            </div>
          @endif

          <div class="menu_btn">
            <a href="{{ route('manual_logout') }}">Logout</a>
          </div>
        </div>
      </div>
    </div>
    
  </div>
</header>