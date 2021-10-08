@extends('layouts.app')

@section('content')

<div class="admin_selection">
  <a href="{{ route('getAdminDriver') }}">
    <div class="selection left">
      <i class="fas fa-shuttle-van"></i>
      Delivery
    </div>
  </a>

  <a href="{{ route('getAdminRestaurant') }}">
    <div class="selection right">
      <i class="fas fa-utensils"></i>
      Restaurant
    </div>
  </a>
</div>

@endsection