@extends('layouts.app')

@section('content')

  <div class="login_box">
    <div class="login_wrapper">
      <h4 style="margin-bottom: 30px;">Reset password</h4>
      <form method="POST" action="{{ route('resetPassword') }}">
        @csrf
        <div class="row">
          <div class="col-12">
            <div class="row form-group">
              <div class="col-4">
                <label>OTP</label>
              </div>
              <div class="col-8">
                <input type="text" class="form-control @error('otp') is-invalid @enderror" name="otp" autocomplete="off" required />
                @error('otp')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                @enderror
              </div>
            </div>

            <div class="row form-group">
              <div class="col-4">
                <label>New Username</label>
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
                <label>New Password</label>
              </div>
              <div class="col-8">
                <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="off" required />
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
                <input type="password" class="form-control" name="password_confirmation" autocomplete="off" required />
              </div>
            </div>
          </div>
        </div>
        <input type="hidden" name="reset_user_email" value="{{ $user->email }}" />
        <button type="submit" class="btn btn-primary" style="width: 100%;">Reset</button>
        <a href="{{ route('login') }}" class="btn btn-secondary" style="width: 100%; margin-top: 15px;">Back to login page</a>
      </form>
    </div>
  </div>

@endsection