@extends('layouts.app')

@section('title', 'Register')

@push('scripts')
<script>
document.getElementById('detectLocation')?.addEventListener('click', () => {
    if (!navigator.geolocation) { alert('Geolocation not supported by your browser.'); return; }
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            document.getElementById('lat').value = pos.coords.latitude.toFixed(6);
            document.getElementById('lng').value = pos.coords.longitude.toFixed(6);
        },
        () => alert('Unable to retrieve your location. Please allow permission or enter manually.')
    );
});
</script>
@endpush

@section('content')
<div class="page-content-wrapper">
    <div class="main-card">
        <div class="image-banner">
            <h2>Join BloodLine</h2>
            <p>DONOR REGISTRATION</p>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="section-title">Personal Information</div>
                <input type="text" name="fullname" placeholder="Full Name" value="{{ old('fullname') }}" required>
                <input type="email" name="email" placeholder="Email Address" value="{{ old('email') }}" required>
                <input type="text" name="national_id" placeholder="National ID (14 digits)" maxlength="14" value="{{ old('national_id') }}" required>

                <div class="flex-row">
                    <input type="password" name="password" placeholder="Password" required>
                    <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
                </div>

                <div class="flex-row">
                    <input type="number" name="age" placeholder="Age" min="18" max="65" value="{{ old('age') }}" required>
                    <select name="gender" required>
                        <option value="">Gender</option>
                        <option value="Male"   @selected(old('gender') === 'Male')>Male</option>
                        <option value="Female" @selected(old('gender') === 'Female')>Female</option>
                    </select>
                </div>
                <input type="tel" name="phone" placeholder="Phone Number" value="{{ old('phone') }}" required>

                <div class="section-title">Location</div>
                <select name="governorate" required>
                    <option value="">Select Governorate</option>
                    @foreach(['cairo','alexandria','giza','luxor','aswan','mansoura','qena','sohag'] as $g)
                        <option value="{{ $g }}" @selected(old('governorate') === $g)>{{ ucfirst($g) }}</option>
                    @endforeach
                </select>
                <div class="flex-row">
                    <input type="text" name="city" placeholder="City" value="{{ old('city') }}">
                    <input type="text" name="address" placeholder="Address" value="{{ old('address') }}" required>
                </div>

                <div style="background:#f9f9f9;border:1px dashed #ddd;border-radius:10px;padding:14px;margin-bottom:12px;">
                    <small style="color:#666;display:block;margin-bottom:8px;">
                        📍 <strong>Your coordinates</strong> (optional) — helps us match you with nearby urgent cases.
                    </small>
                    <div class="flex-row">
                        <input type="text" name="latitude"  id="lat" placeholder="Latitude (e.g. 30.0444)"  value="{{ old('latitude') }}" pattern="-?[0-9]+(\.[0-9]+)?">
                        <input type="text" name="longitude" id="lng" placeholder="Longitude (e.g. 31.2357)" value="{{ old('longitude') }}" pattern="-?[0-9]+(\.[0-9]+)?">
                    </div>
                    <button type="button" id="detectLocation" style="width:auto;background:#eee;color:var(--main-red);padding:7px 14px;font-size:13px;margin:0;">
                        <i class="fas fa-location-arrow"></i> Detect my location
                    </button>
                </div>

                <div class="section-title">Medical Information</div>
                <div class="flex-row">
                    <select name="blood_type" required style="flex:1.5;">
                        <option value="">Blood Type</option>
                        @foreach(['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bt)
                            <option value="{{ $bt }}" @selected(old('blood_type') === $bt)>{{ $bt }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="weight" placeholder="Weight (kg)" min="50" max="200" value="{{ old('weight') }}" required>
                </div>

                <button type="submit">CREATE ACCOUNT</button>
            </form>

            <div class="footer-text">
                Already have an account? <a href="{{ route('login') }}">Login</a>
            </div>
        </div>
    </div>
</div>
@endsection
