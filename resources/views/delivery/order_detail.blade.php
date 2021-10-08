@extends('layouts.app')

@section('content')

<div class="order_box">
  <h3 style="margin-bottom: 20px; width: 100%; text-align: center;">{{ $restaurant_detail->name }}</h3>
  <div class="row">
    <div class="col-12">
      <table id="order_table" class="table table-bordered table-striped" cellspacing="0" width="100%">
        <thead>
          <tr>
            <th>RESTAURANT</th>
            <th>ORDER NO</th>
            <th>ADDRESS</th>
            <th>POSTAL</th>
            <th>NAME</th>
            <th>PHONE</th>
            <th>DATE TIME</th>
            <th>REMARKS</th>
            <th>PRICE</th>
            <th>ORDER DATE</th>
          </tr>
        </thead>
        <tbody>
          @foreach($order_list as $order)
            <tr>
              <td>{{ $order->restaurant_name }}</td>
              <td>{{ $order->order_no }}</td>
              <td>{{ $order->address }}</td>
              <td>{{ $order->postal }}</td>
              <td>{{ $order->name }}</td>
              <td>{{ $order->phone }}</td>
              <td data-order="{{ $order->date_time }}">{{ date('Y M d h:i A', strtotime($order->date_time)) }}</td>
              <td>{{ $order->remarks }}</td>
              <td>S$ {{ $order->price }}</td>
              <td data-order="{{ $order->created_at }}">{{ date('Y M d h:i A', strtotime($order->created_at)) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  
  $(document).ready(function(){
    $("#order_table").DataTable( {
      responsive: true,
      order: [[ 9, "desc" ]]
    });
  });

</script>

@endsection