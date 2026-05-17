@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
<div class="page-content-wrapper">
    <div class="main-card" style="max-width: 420px;">
        <div class="image-banner" style="height: 130px;">
            <h2>Forgot Password</h2>
        </div>
        <div class="card-body">
            <p style="color:#666;font-size:14px;margin-bottom:20px;text-align:center;">
                Enter your email and we'll send you a reset link.
            </p>
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <input type="email" name="email" placeholder="Enter your email" value="{{ old('email') }}" required>
                <button type="submit">SEND RESET LINK</button>
            </form>
            <div class="footer-text">
                <a href="{{ route('login') }}">← Back to Login</a>
            </div>
        </div>
    </div>
</div>
@endsection
