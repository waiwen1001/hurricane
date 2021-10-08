@extends('layouts.app')

@section('content')

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
        <div class="menu_btn">
          Order History
        </div>
        <div class="menu_btn">
          Restaurant Profile
        </div>
        <div class="menu_btn" id="export_order">
          Export order
        </div>
        <div class="menu_btn" id="import_order">
          Import order
        </div>
      </div>
    </div>
  </div>
  <div class="restaurant_logout">
    <!-- <p style="display: inline-block;">WALLET : S$ {{ number_format($restaurant_detail->rebate_wallet, 2) }}</p> -->
    <a href="{{ route('manual_logout') }}" class="btn btn-secondary">LOGOUT</a>
  </div>
</div>
<div class="restaurant_container">
  <div class="icon">
    <img src="{{ asset('assets/images/logo.png') }}" alt="HURRICANE" />
  </div>
  <form method="POST" id="submit_order_form">
    @csrf
    <div class="form-group" style="margin-top: 20px; position: relative;">
      <p style="margin-bottom: 0px;">TODAY CURRENT TOTAL DELIVERIES : 6</p>
      <p style="margin-bottom: 0px;">CURRENT DELIVERY PRICE : S$ 5.00</p>
      <p style="margin-bottom: 0px;">REBATE : S$ 0.25</p>
      <p style="margin-bottom: 0px;">INSURANCE VALUE (5%) : S$ 0.25</p>
      <p style="margin-bottom: 0px;">CURRENT DELIVERY PRICE WITH GST (7%) : S$ 5.62</p>
    </div>
    <div class="form-group" style="position: relative;">
      <textarea class="form-control round_input" name="order_address" placeholder="Order address" required autocomplete="off"></textarea>
      <!-- <i class="fas fa-chevron-circle-right input_icon"></i> -->
    </div>

    <div class="form-group" style="position: relative;">
      <input type="text" class="form-control round_input" name="postal" placeholder="Postal code" required autocomplete="off" />
      <!-- <i class="fas fa-chevron-circle-right input_icon"></i> -->
    </div>

    <div class="form-group" style="position: relative;">
      <input type="text" class="form-control round_input" name="name" placeholder="Name" required autocomplete="off" />
      <!-- <i class="fas fa-chevron-circle-right input_icon"></i> -->
    </div>

    <div class="form-group" style="position: relative;">
      <input type="text" class="form-control round_input" name="phone_number" placeholder="Phone number" required autocomplete="off" />
      <!-- <i class="fas fa-chevron-circle-right input_icon"></i> -->
    </div>

    <div class="form-group" style="position: relative;">
      <input type="date" class="form-control round_input" name="date" placeholder="Date" required />
      <!-- <i class="fas fa-chevron-circle-right input_icon"></i> -->
    </div>

    <div class="form-group" style="position: relative;">
      <input type="text" class="form-control round_input timepicker" name="time" placeholder="Time" required></textarea>
      <!-- <i class="fas fa-chevron-circle-right input_icon"></i> -->
    </div>

    <div class="form-group" style="margin-top: 20px; position: relative;">
      <textarea class="form-control round_input" name="remarks" placeholder="Remarks" autocomplete="off"></textarea>
      <!-- <i class="fas fa-chevron-circle-right input_icon"></i> -->
    </div>

    <input type="hidden" name="restaurant_id" value="{{ $restaurant_detail->id }}" />
    <div class="restaurant_submit">
      <button type="submit" class="btn btn-orange" id="submit_order_btn">SUBMIT ORDER</button>
    </div>
  </form>
</div>

<div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST" action="{{ route('exportRestaurantOrder') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="exportModalLabel">EXPORT ORDER</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-6">
              <label>Export from</label>
              <input type="date" class="form-control" name="export_start" value="{{ $date_start }}" requried />
            </div>
            <div class="col-6">
              <label>Export to</label>
              <input type="date" class="form-control" name="export_end" value="{{ $date_end }}" requried />
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <input type='hidden' name='restaurant_id' value="{{ $restaurant_detail->id }}" />
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Export</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data" action="{{ route('importRestaurantOrder') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="importModalLabel">IMPORT ORDER</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label style="width: 100%;">Download import format</label>
                <a href="{{ route('downloadImportFormat', ['id' => $restaurant_detail->id]) }}" class="btn btn-secondary">Download</a>
              </div>
            </div>
            <div class="col-12">
              <label style="width: 100%;">Import order</label>
              <input type="file" name="file" required />
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  
  var today_date = new Date();
  var today_year = today_date.getFullYear();
  var today_month = today_date.getMonth();
  var today_day = today_date.getDate();

  today_month = parseInt(today_month) + 1;
  if(today_month < 10)
  {
    today_month = "0"+today_month;
  }

  if(today_day < 10)
  {
    today_day = "0"+today_day;
  }

  var today = today_year+"-"+today_month+"-"+today_day;

  $(document).ready(function(){

    $('.timepicker').timepicker({
        timeFormat: 'h:mm p',
        interval: 30,
        defaultTime: 'now',
        dynamic: false,
        dropdown: true,
        scrollbar: true
    });

    $("input[name='date']").val(today);

    $(".input_icon").click(function(){
      $(this).parent().nextAll().children(":not(.readonly):input").eq(0).focus();
    });

    $("#submit_order_form").submit(function(e){
      e.preventDefault();

      $("#submit_order_btn").attr("disabled", true).html('<i class="fas fa-spinner fa-spin"></i>');

      $("#submit_order_form input[name='_token']").val("{{ csrf_token() }}");
      $.post("{{ route('submitOrder') }}", $("#submit_order_form").serialize(), function(result){
        if(result.error == 0)
        {
          Swal.fire({
            title: result.message,
            icon: 'success',
            confirmButtonText: 'OK',
          });

          $("#submit_order_btn").attr("disabled", false).html("SUBMIT ORDER");
          $("#submit_order_form input:not(.timepicker, input[name='date'], input[name='restaurant_id']), #submit_order_form textarea").val("");
        }
        else
        {
          Swal.fire({
            title: result.message,
            icon: 'error',
            confirmButtonText: 'OK',
          }).then((result) => {
            if (result.isConfirmed) {
              location.reload();
            }
          });

          return;
        }
      }).fail(function(xhr){
        $("#submit_order_btn").attr("disabled", false).html("SUBMIT ORDER");
      });
    });

    $("#export_order").click(function(){
      $("#exportModal").modal('show');
    });

    $("#import_order").click(function(){
      $("#importModal").modal('show');
    });

  });

</script>

@endsection