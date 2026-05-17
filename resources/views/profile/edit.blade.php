@extends('layouts.app')

@section('title', 'Edit Profile')

@push('scripts')
<script>
document.getElementById('detectLocation')?.addEventListener('click', () => {
    if (!navigator.geolocation) { alert('Geolocation not supported.'); return; }
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            document.getElementById('lat').value = pos.coords.latitude.toFixed(6);
            document.getElementById('lng').value = pos.coords.longitude.toFixed(6);
        },
        () => alert('Unable to retrieve your location.')
    );
});
</script>
@endpush

@section('content')
<div class="page-content-wrapper">
    <div class="main-card">
        <div class="image-banner" style="height: 130px;">
            <h2>Edit Profile</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <div class="section-title">Personal Information</div>
                <input type="text" name="full_name" placeholder="Full Name" value="{{ old('full_name', $user->full_name) }}" required>
                <input type="email" name="email" placeholder="Email" value="{{ old('email', $user->email) }}" required>
                <input type="tel" name="phone" placeholder="Phone" value="{{ old('phone', $user->donor->phone ?? '') }}">

                <div class="flex-row">
                    <select name="blood_type">
                        <option value="">Blood Type</option>
                        @foreach(['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bt)
                            <option value="{{ $bt }}" @selected(old('blood_type', $user->donor->blood_type ?? '') === $bt)>{{ $bt }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="city" placeholder="City" value="{{ old('city', $user->donor->city ?? '') }}">
                </div>

                <div class="flex-row">
                    <input type="number" name="age" placeholder="Age" min="18" max="65" value="{{ old('age', $user->donor->age ?? '') }}">
                    <input type="number" name="weight" placeholder="Weight (kg)" min="50" max="200" value="{{ old('weight', $user->donor->weight ?? '') }}">
                </div>

                @if ($user->donor)
                    <div class="section-title">Location & Notifications</div>
                    <div style="background:#f9f9f9;border:1px dashed #ddd;border-radius:10px;padding:14px;margin-bottom:12px;">
                        <small style="color:#666;display:block;margin-bottom:8px;">📍 We use your coordinates to match you with nearby urgent cases.</small>
                        <div class="flex-row">
                            <input type="text" name="latitude"  id="lat" placeholder="Latitude"  value="{{ old('latitude',  $user->donor->latitude)  }}" pattern="-?[0-9]+(\.[0-9]+)?">
                            <input type="text" name="longitude" id="lng" placeholder="Longitude" value="{{ old('longitude', $user->donor->longitude) }}" pattern="-?[0-9]+(\.[0-9]+)?">
                        </div>
                        <button type="button" id="detectLocation" style="width:auto;background:#eee;color:var(--main-red);padding:7px 14px;font-size:13px;margin:0;">
                            <i class="fas fa-location-arrow"></i> Detect my location
                        </button>
                    </div>

                    <label style="display:flex;align-items:center;gap:8px;font-size:14px;margin-bottom:14px;cursor:pointer;">
                        <input type="hidden" name="notifications_enabled" value="0">
                        <input type="checkbox" name="notifications_enabled" value="1" style="width:auto;margin:0;" @checked($user->donor->notifications_enabled)>
                        <span><i class="fas fa-bell" style="color:var(--main-red);"></i> Email me when an urgent request matches my blood type</span>
                    </label>
                @endif

                <div class="section-title">Change Password (optional)</div>
                <input type="password" name="current_password" placeholder="Current Password">
                <input type="password" name="new_password" placeholder="New Password (min 6 chars)">
                <input type="password" name="new_password_confirmation" placeholder="Confirm New Password">

                <div style="display:flex; gap:12px; margin-top:20px;">
                    <button type="submit" style="flex:2;">SAVE CHANGES</button>
                    <a href="{{ route('profile.show') }}" style="flex:1; background:#eee; color:#333; text-align:center; padding:13px; border-radius:12px; text-decoration:none; font-weight:700;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
