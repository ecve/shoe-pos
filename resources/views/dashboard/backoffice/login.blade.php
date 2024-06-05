

@extends('layouts.layout')

@section('content')
    <div class="container-fluid pt-5" style="padding-bottom: 10%;>
        <div class="row">
        <div class="col-md-4 offset-md-4" style="margin-top: 45px;">
            <div class="card">
                <div class="card-title">
                    <h4 class="p-3 text-center">Login</h4>
                    <hr>
                </div>
                <div class="card-body">

                    <form action="{{ route('backoffice.check') }}" method="post" autocomplete="off">
                        @if (Session::get('fail'))
                            <div class="alert alert-danger">
                                {{ Session::get('fail') }}
                            </div>
                        @endif
                        @csrf
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="text" value="sajjadhossain121360@gmail.com" class="form-control my-2" name="email" placeholder="Enter email address"
                                value="{{ old('email') }}">
                            <span class="text-danger">
                                @error('email')
                                    {{ $message }}
                                @enderror
                            </span>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" value="123456789" class="form-control my-2" name="password" placeholder="Enter password"
                                value="{{ old('password') }}">
                            <span class="text-danger">
                                @error('password')
                                    {{ $message }}
                                @enderror
                            </span>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                        <br>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
