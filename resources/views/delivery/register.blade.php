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
                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" autocomplete="off" required autofocus />
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
                <input type="text" class="form-control @error('username') is-invalid @enderror" name="username" autocomplete="off" required />
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
                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" autocomplete="off" required />
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
          </div>
        </div>
        <input type="hidden" name="user_type" value="delivery" />
        <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
      </form>
    </div>
  </div>

@endsection