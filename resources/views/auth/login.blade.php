@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="page-content-wrapper">
    <div class="main-card" style="max-width: 450px;">
        <div class="image-banner">
            <h2>Welcome Back</h2>
            <p>LOGIN TO YOUR ACCOUNT</p>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <select name="role" required>
                    <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select Your Role</option>
                    <option value="donor"    @selected(old('role') === 'donor')>Donor</option>
                    <option value="hospital" @selected(old('role') === 'hospital')>Hospital</option>
                    <option value="admin"    @selected(old('role') === 'admin')>Admin</option>
                </select>

                <input type="email" name="email" placeholder="Email Address" value="{{ old('email') }}" required>
                <input type="password" name="password" placeholder="Password" required>

                <div style="display:flex; justify-content:space-between; align-items:center; margin:12px 0 16px; font-size:13px;">
                    <label style="display:flex; gap:6px; align-items:center; cursor:pointer;">
                        <input type="checkbox" name="remember" style="width:auto; margin:0;"> Remember me
                    </label>
                    <a href="{{ route('password.request') }}" style="color: var(--main-red); font-weight:600;">Forgot Password?</a>
                </div>

                <button type="submit">LOGIN</button>
            </form>

            <div class="footer-text">
                Don't have an account? <a href="{{ route('register') }}">Create Account</a>
            </div>
        </div>
    </div>
</div>
@endsection
