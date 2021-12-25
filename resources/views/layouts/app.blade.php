<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Hurricane</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="{{ asset('assets/bootstrap-4.3.1-dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/front.css') }}">
    <!-- datatables -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/datatables/datatables.min.css') }}">
    <!-- iCheck for checkboxes and radio inputs -->
    <link rel="stylesheet" href="{{ asset('assets/iCheck/all.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/iCheck/square/blue.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/sweetAlert2/sweetalert2.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/boostrap-toggle/bootstrap-toggle.min.css') }}" rel="stylesheet">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/jquery-timepicker/css/jquery.timepicker.min.css') }}">

    <!-- signature -->
    <link rel="stylesheet" href="{{ asset('assets/signature/css/signature-pad.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/jquery-ui/jquery-ui.css') }}">

    <!-- fullCalendar -->
    <link rel="stylesheet" href="{{ asset('assets/fullcalendar/css/main.css') }}">

    <!-- Fontawesome -->
    <script src="https://kit.fontawesome.com/e5dc55166e.js" crossorigin="anonymous"></script>

    <!-- jQuery -->
    <script src="{{ asset('assets/jquery/jquery-3.5.1.min.js') }}"></script>

    <!-- bootstrap -->
    <script src="{{ asset('assets/bootstrap-4.3.1-dist/js/bootstrap.bundle.min.js') }}"></script>
    <!-- datatables -->
    <script src="{{ asset('assets/datatables/datatables.min.js') }}"></script>
    <!-- iCheck 1.0.1 -->
    <script src="{{ asset('assets/iCheck/icheck.min.js') }}"></script>
    <!-- sweet alert 2 -->
    <script src="{{ asset('assets/sweetAlert2/sweetalert2.js') }}"></script>
    <script src="{{ asset('assets/boostrap-toggle/bootstrap-toggle.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('assets/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/signature/js/signature_pad.umd.js') }}"></script>
    <!-- signature -->
    <script src="{{ asset('assets/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/jquery-ui/jquery-ui.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- fullCalendar -->
    <script src="{{ asset('assets/fullcalendar/js/main.js') }}"></script>

  </head>
  <body>
    <div id="app">
      <div class="content-wrapper">
        @yield('content')
      </div>
    </div>
  </body>

  <script>

    var driver_location_update;
    var my_pos;
    var my_marker;
    var map;
    var driver_icon;

    function check_online()
    {
      $.post("{{ route('checkOnline') }}", { "_token" : "{{ csrf_token() }}"}, function(){
        // do ntg
      });
    }

    function update_location()
    {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          (position) => {
            var current_lat_lng = {
              lat: position.coords.latitude,
              lng: position.coords.longitude,
            };

            let update = false;
            if(my_pos && current_lat_lng)
            {
              if(my_pos.lat != current_lat_lng.lat || my_pos.lng != current_lat_lng.lng)
              {
                update = true;
                my_pos = current_lat_lng;

                my_marker.setMap(null);
                my_marker = new google.maps.Marker({
                  position: current_lat_lng,
                  map: map,
                  icon: driver_icon
                });

                my_pos = current_lat_lng;
                map.setCenter(my_pos);
              }
            }
            else
            {
              my_pos = current_lat_lng;
              update = true;
            }

            if(current_lat_lng && !driver_location_update)
            {
              driver_location_update = setInterval(update_location, 10000);
            }

            // if(update == true)
            // {
              save_location();
            // }
            
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

    function save_location()
    {
      $.post("{{ route('updateLocation') }}", { "_token" : "{{ csrf_token() }}", "lat" : my_pos.lat, "lng" : my_pos.lng}, function(){
        // do ntg
      });
    }

  </script>
</html>
