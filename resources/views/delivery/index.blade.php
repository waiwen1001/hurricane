@extends('layouts.app')

@section('content')

<div style="overflow-x: hidden;">
  <div class="header">
    <div class="row">
      <div class="col-6">
        <h4 style="margin: 0px; line-height: 38px;">{{ $user->name }}</h4>
      </div>
      <div class="col-6">
        <div style="float: right;">
          <input id="active_toggle" type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="danger" data-on="ON" data-off="OFF" {{ $active == 1 ? 'checked' : '' }} />
          <a href="{{ route('manual_logout') }}" class="btn btn-secondary" style="margin-left: 30px;">LOGOUT</a>
        </div>
      </div>
    </div>
  </div>
  <div class="content">
    <div class="row">
      <div class="col-sm-12 col-md-8">
        <div class="restaurant_selection">
          <label>RESTAURANT</label>
          <select class="form-control" id="restaurant_list">
            <option value="0">Please select</option>
            @foreach($restaurant_list as $restaurant)
              <option value="{{ $restaurant->id }}">{{ $restaurant->name }}</option>
            @endforeach
          </select>
          <button class="btn btn-primary" id="view_order_btn" disabled>View Orders</button>
        </div>
        <div class="selected_restaurant">
          <label>SELECTED RESTAURANT : <span>RESTAURANT A</span></label>
        </div>

        <div class="restaurant_setting_box">
          <div class="restaurant_setting">
            <div class="restaurant_setting_content">
              <label class="bold">SET TIER</label>
              <form id="tier_form">
                @csrf
                @foreach($tier_list as $tier)
                  <div class="form-group tier_list">
                    <label style="margin: 0px; flex: 1;">{{ $tier['name'] }}</label>
                    <div class="tier_box">
                      <div class="checkbox icheck" style="display: inline-block;">
                        <label style="margin-bottom: 0px;">
                          <input class="form-check-input tier_checkbox" type="checkbox" name="{{ $tier['input'] }}" value="{{ $tier['value'] }}" />
                        </label>
                      </div>
                      <div style="width: calc(100% - 50px);" class="tier_price_box">
                        <input type="number" class="form-control tier_price" name='tier_price_{{ $tier['value'] }}' value="" placeholder="Tier Price" />
                      </div>
                    </div>
                  </div>
                @endforeach
                <input type="hidden" name="tier_restaurant_id" value="" />
              </form>
            </div>
            <button type="button" class="btn btn-success" id="save_tier_btn">
              <i class="far fa-save"></i>
              SAVE
            </button>
          </div>
          <div class="restaurant_setting" style="margin-left: 30px;">
            <div style="">
              <div class="restaurant_rebate form-group">
                <label class="bold">SET REBATE</label>
                <input type="number" class="form-control" name="rebate" /> %
              </div>
              <button type="button" class="btn btn-success" id="save_rebate_btn">
                <i class="far fa-save"></i>
                SAVE
              </button>
            </div>
          </div>
        </div>
        <div class="restaurant_export_box">
          <label>Export Order</label>
          <form method="POST" action="{{ route('exportOrder') }}">
            @csrf
            <div class="row">
              <div class="col-6">
                <div class="input_box">
                  <label>Date from</label>
                  <input type="date" class="form-control" name="export_start" value="{{ $date_start }}" />
                </div>
              </div>
              <div class="col-6">
                <div class="input_box">
                  <label>Date To</label>
                  <input type="date" class="form-control" name="export_end" value="{{ $date_end }}" />
                </div>
              </div>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top: 20px;">
              <i class="fas fa-download"></i>
              Download
            </button>
          </div>
        </form>
      </div>
      <div class="col-sm-12 col-md-4">
        <div style="margin-right: 10px;">
          <div class="restaurant_setting" style="margin: 10px 0px 0 0; height: 640px">
            <form method="POST" id="create_restaurant_form">
              @csrf
              <label style="margin-bottom: 10px;" class="bold">CREATE</label>
              <div class="form-group">
                <label>Restaurant Name : </label>
                <input type="text" name="restaurant_name" class="form-control" required autocomplete="off" />
              </div>
              <div class="form-group">
                <label>Address : </label>
                <input type="text" name="restaurant_address" class="form-control" required autocomplete="off" />
              </div>
              <div class="form-group">
                <label>Postal : </label>
                <input type="text" name="restaurant_postal" class="form-control" required autocomplete="off" />
              </div>
              <div class="form-group">
                <label>Email : </label>
                <input type="email" name="restaurant_email" class="form-control" required autocomplete="off" />
              </div>
              <div class="form-group">
                <label>Login username : </label>
                <input type="text" name="restaurant_username" class="form-control" required autocomplete="off" />
              </div>
              <div class="form-group">
                <label>Login password : </label>
                <input type="password" name="restaurant_password" class="form-control" required autocomplete="off" />
              </div>
              <div class="form-group">
                <label>Login password comfirmation : </label>
                <input type="password" name="restaurant_password_2" class="form-control" required autocomplete="off" />
              </div>

              <button type="submit" class="btn btn-success" id="create_restaurant_btn">
                <i class="fas fa-plus"></i>
                CREATE
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>

  var restaurant_list = @json($restaurant_list);
  var active = "{{ $active }}";
  
  $(document).ready(function(){
    $("#create_restaurant_form").submit(function(e){
      e.preventDefault();

      $("#create_restaurant_btn").attr("disabled", true);

      let restaurant_password = $("input[name='restaurant_password']").val();
      let restaurant_password_2 = $("input[name='restaurant_password_2']").val();

      if(restaurant_password != restaurant_password_2)
      {
        Swal.fire({
          title: "Password and password comfirmation must be same.",
          icon: 'error',
          confirmButtonText: 'OK',
        });

        $("#create_restaurant_btn").attr("disabled", false);
        return;
      }

      if(restaurant_password.length < 8)
      {
        Swal.fire({
          title: "Password must at least 8 character.",
          icon: 'error',
          confirmButtonText: 'OK',
        });

        $("#create_restaurant_btn").attr("disabled", false);
        return;
      }

      $.post("{{ route('createRestaurant') }}", $("#create_restaurant_form").serialize(), function(result){
        if(result.error == 1)
        {
          Swal.fire({
            title: result.message,
            icon: 'error',
            confirmButtonText: 'OK',
          });

          $("#create_restaurant_btn").attr("disabled", false);
          return;
        }
        else
        {
          refreshAlert('success', result.message);
        }
      }).fail(function(xhr){
        $("#create_restaurant_btn").attr("disabled", false);
        if(xhr.status == 401)
        {
          loggedOutError();
        }
      })
    });

    $("#restaurant_list").on('change', function(){
      let restaurant_id = $(this).val();
      let restaurant_detail = null;
      $("input[name='rebate']").val("");
      $("input.tier_checkbox").iCheck("uncheck");
      $("input.tier_price").val("");
      $("#view_order_btn").attr("disabled", true);

      for(var a = 0; a < restaurant_list.length; a++)
      {
        if(restaurant_list[a].id == restaurant_id)
        {
          restaurant_detail = restaurant_list[a];
          break;
        }
      }

      if(restaurant_detail)
      {
        if(restaurant_detail.rebate)
        {
          $("input[name='rebate']").val(restaurant_detail.rebate);
        }

        if(restaurant_detail.tier_list.length > 0)
        {
          for(var a = 0; a < restaurant_detail.tier_list.length; a++)
          {
            let restaurant_tier = restaurant_detail.tier_list[a];
            if(restaurant_tier.active == 1)
            {
              $("input.tier_checkbox[value='"+restaurant_tier.tier+"']").iCheck("check");
              let tier_name = "tier_price_"+restaurant_tier.tier;
              $("input.tier_price[name='"+tier_name+"']").val(restaurant_tier.tier_price);
            }
          }
        }

        $("#view_order_btn").attr("disabled", false);
      }
    });

    $("#save_tier_btn").click(function(){
      var restaurant_id = $("#restaurant_list").val();

      if(restaurant_id == "0")
      {
        Swal.fire({
          title: 'Please select restaurant before proceed.',
          icon: 'error',
          confirmButtonText: 'OK',
        });

        return;
      }

      $("input[name='tier_restaurant_id']").val(restaurant_id);

      var tier_count = 0;
      var tier_price_empty = null;

      $("input.tier_checkbox").each(function(){
        if($(this).is(":checked"))
        {
          tier_count++;
          let tier_price = $(this).parents().eq(2).siblings(".tier_price_box").find("input.tier_price").val();
          if(tier_price == 0 || tier_price == "")
          {
            tier_price_empty = 1;
          }
        }
      });

      if(tier_count == 0)
      {
        Swal.fire({
          title: 'Restaurant tier cannot be empty.',
          icon: 'error',
          confirmButtonText: 'OK',
        });

        return;
      }

      if(tier_price_empty == 1)
      {
        Swal.fire({
          title: 'Restaurant tier price cannot be empty.',
          icon: 'error',
          confirmButtonText: 'OK',
        });

        return;
      }

      $("#save_tier_btn").attr("disabled", true);

      $.post("{{ route('saveRestaurantTier') }}", $("#tier_form").serialize(), function(result){

        $("#save_tier_btn").attr("disabled", false);

        if(result.error == 0)
        {
          Swal.fire({
            title: result.message,
            icon: 'success',
            confirmButtonText: 'OK',
          });
        }
      }).fail(function(xhr){
        $("#save_tier_btn").attr("disabled", false);
        if(xhr.status == 401)
        {
          loggedOutError();
        }
      });
    });

    $("#save_rebate_btn").click(function(){

      var restaurant_id = $("#restaurant_list").val();

      if(restaurant_id == "0")
      {
        Swal.fire({
          title: 'Please select restaurant before proceed.',
          icon: 'error',
          confirmButtonText: 'OK',
        });

        return;
      }

      var rebate = $("input[name='rebate']").val();
      if(rebate == "")
      {
        Swal.fire({
          title: 'Restaurant rebate cannot be empty.',
          icon: 'error',
          confirmButtonText: 'OK',
        });

        return;
      }

      $("#save_rebate_btn").attr("disabled", true);

      $.post("{{ route('saveRestaurantRebate') }}", { "_token" : "{{ csrf_token() }}", "restaurant_id" : restaurant_id, "rebate" : rebate }, function(result){

        $("#save_rebate_btn").attr("disabled", false);

        if(result.error == 0)
        {
          Swal.fire({
            title: result.message,
            icon: 'success',
            confirmButtonText: 'OK',
          });
        }
      }).fail(function(xhr){
        $("#save_rebate_btn").attr("disabled", false);
        if(xhr.status == 401)
        {
          loggedOutError();
        }
      });

    });

    $("#view_order_btn").click(function(){
      let restaurant_id = $("#restaurant_list").val();
      if(restaurant_id != 0)
      {
        var route_url = "{{ route('getOrderDetail', ['id' => '_id']) }}";
        route_url = route_url.replace('_id', restaurant_id);
        window.open(route_url);
      }
    });

    $("#active_toggle").change(function(){
      let active_toggle = $(this).is(":checked");

      if(active_toggle == true)
      {
        active = 1;
      }
      else if(active_toggle == false)
      {
        active = 0;
      }

      $.post("{{ route('updateSystemStatus') }}", { "_token" : "{{ csrf_token() }}", "active" : active }, function(result){
        if(result.error == 0)
        {
          console.log("done");
        }
      }).fail(function(xhr){
        if(xhr.status == 401)
        {
          loggedOutError();
        }
      })
    });

  });

  function loggedOutError()
  {
    Swal.fire({
      title: 'Your account was logged out, please login again.',
      icon: 'error',
      confirmButtonText: 'OK',
    }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.isConfirmed) {
        location.reload();
      }
    })
  }

  function refreshAlert(type, message)
  {
    Swal.fire({
      title: message,
      icon: type,
      confirmButtonText: 'OK',
    }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.isConfirmed) {
        location.reload();
      }
    });
  }

</script>

@endsection