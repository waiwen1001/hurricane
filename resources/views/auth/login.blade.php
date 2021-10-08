@extends('layouts.app')

@section('content')
  
  <div class="login_box">
    <div class="login_wrapper">
      <h5 style="text-transform: uppercase; width: 100%; text-align: center;">HURRICANE</h5>
      <h4 style="margin-bottom: 30px;">Please Login</h4>
      <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="row">
          <div class="col-12">
            <div class="row form-group">
              <div class="col-4">
                <label>Username</label>
              </div>
              <div class="col-8">
                <input type="text" class="form-control @error('username') is-invalid @enderror" name="username" autocomplete="off" value="{{ old('username') }}" required />
                @error('username')
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
                <input type="password" class="form-control" name="password" required />
              </div>
            </div>
            <div class="row form-group">
              <div class="col-6">
                <a href="{{ route('getAdminReset') }}">Reset password</a>
              </div>
              
              <div class="col-6" style="text-align: right;">
                <a href="{{ route('getAdminRegister') }}">Register</a>
              </div>
            </div>
          </div>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
      </form>
    </div>
  </div>

@endsection