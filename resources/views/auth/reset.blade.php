@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<div class="page-content-wrapper">
    <div class="main-card" style="max-width: 420px;">
        <div class="image-banner" style="height: 130px;">
            <h2>Set New Password</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="password" name="password" placeholder="New Password" required>
                <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
                <button type="submit">CHANGE PASSWORD</button>
            </form>
        </div>
    </div>
</div>
@endsection
