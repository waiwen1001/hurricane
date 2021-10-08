@extends('layouts.app')

@section('content')

@include('driver.header')

<div class="container" style="padding-top: 10px;">
  <div class="row">
    <div class="col-12">
      <div class="pick_up_box">
        <form method="POST" action="{{ route('submitPickUp') }}" enctype="multipart/form-data">
          @csrf
          <div class="form-group">
            <label>Select Location</label>
            <select class="form-control" name="pick_up" required>
              @foreach($pick_up_list as $pick_up)
                <option value="{{ $pick_up->id }}">{{ $pick_up->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="row">
            <div class="col-md-4 col-sm-12">
              <div class="form-group">
                <label style="width: 100%;">Unit number</label>
                <input type="file" name="file_unit_number[]" accept="image/*" required multiple />
              </div>
            </div>

            <div class="col-md-4 col-sm-12">
              <div class="form-group">
                <label style="width: 100%;">Items</label>
                <input type="file" name="file_items[]" accept="image/*" required multiple />
              </div>
            </div>

            <div class="col-md-4 col-sm-12">
              <div class="form-group">
                <label style="width: 100%;">Signatory</label>
                <input type="file" name="file_signatory[]" accept="image/*" required multiple />
              </div>
            </div>
          </div>
      
          <button class="btn btn-success">Arrival</button>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection