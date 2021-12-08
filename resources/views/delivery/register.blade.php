@extends('layouts.app')

@section('content')

  <div class="login_box">
    <div class="login_wrapper">
      <h4 style="margin-bottom: 30px;">Register</h4>
      <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="row">
          <div class="col-12">
            <div class="row form-group">
              <div class="col-4">
                <label>Name</label>
              </div>
              <div class="col-8">
                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" autocomplete="off" required autofocus value="{{ old('name') }}" />
                @error('name')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                @enderror
              </div>
            </div>
            <div class="row form-group">
              <div class="col-4">
                <label>Username</label>
              </div>
              <div class="col-8">
                <input type="text" class="form-control @error('username') is-invalid @enderror" name="username" autocomplete="off" required value="{{ old('username') }}" />
                @error('username')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                @enderror
              </div>
            </div>
            <div class="row form-group">
              <div class="col-4">
                <label>Email</label>
              </div>
              <div class="col-8">
                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" autocomplete="off" required  value="{{ old('email') }}" />
                @error('email')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                @enderror
              </div>
            </div>
            <div class="row form-group">
              <div class="col-4">
                <label>Password</label>
              </div>
              <div class="col-8">
                <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" required />
                @error('password')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                @enderror

              </div>
            </div>
            <div class="row form-group">
              <div class="col-4">
                <label>Password Confirmation</label>
              </div>
              <div class="col-8">
                <input type="password" class="form-control" name="password_confirmation" required />
              </div>
            </div>

            <div class="row form-group">
              <div class="col-4">
                <label>User Type</label>
              </div>
              <div class="col-8">
                <select class="form-control" name="user_type">
                  <option value="delivery">Admin</option>
                  <option value="driver">Driver</option>
                  <option value="restaurant">Restaurant</option>
                </select>
              </div>
            </div>

            <div class="row form-group" id="driver_type" style="display: none;">
              <div class="col-4"></div>
              <div class="col-8">
                <div class="checkbox icheck">
                  <label style="font-weight: bold;">
                    <input class="form-check-input" type="checkbox" name="driver_type" value="contractor" /> Contractor
                  </label>
                </div>
              </div>
            </div>

          </div>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
        <a href="{{ route('login') }}" type="button" class="btn btn-secondary" style="width: 100%; margin-top: 15px;">Back to login page</a>
      </form>
    </div>
  </div>

  <script>
    
    $(document).ready(function(){
      $("select[name='user_type']").change(function(){
        if($(this).val() == "driver")
        {
          $("#driver_type").show();
        }
        else
        {
          $("#driver_type").hide();
        }
      });
    });

  </script>

@endsection