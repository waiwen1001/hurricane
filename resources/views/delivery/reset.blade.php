@extends('layouts.app')

@section('content')

  <div class="login_box">
    <div class="login_wrapper">
      <h4 style="margin-bottom: 30px;">Reset password</h4>
      <form method="POST" action="{{ route('sendReset') }}">
        @csrf
        <div class="row">
          <div class="col-12">
            <div class="row form-group">
              <div class="col-4">
                <label style="margin: 0px; line-height: 38px;">Email</label>
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
          </div>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Get OTP</button>
        <a href="{{ route('login') }}" class="btn btn-secondary" style="width: 100%; margin-top: 15px;">Back to login page</a>
      </form>
    </div>
  </div>

@endsection