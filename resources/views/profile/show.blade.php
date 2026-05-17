@extends('layouts.app')

@section('title', 'My Profile')

@push('styles')
<style>
.profile-grid { display: grid; grid-template-columns: 300px 1fr; gap: 25px; max-width: 1000px; margin: 30px auto; padding: 0 20px; }
.profile-sidebar, .profile-main { background: var(--card-bg); padding: 30px; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.profile-sidebar { text-align: center; }
.profile-img { width: 110px; height: 110px; border-radius: 50%; background: #fff5f5; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; font-size: 48px; color: var(--main-red); border: 4px solid #fff; }
.blood-badge { background: var(--main-red); color: white; padding: 5px 14px; border-radius: 18px; font-weight: bold; display: inline-block; margin: 6px 0 18px; }
.stats-box { display: flex; justify-content: space-around; border-top: 1px solid #eee; padding-top: 18px; }
.stat-item span { display: block; font-size: 18px; font-weight: bold; color: var(--main-red); }
.stat-item label { font-size: 12px; color: #888; }
.info-section { margin-bottom: 28px; }
.info-section h3 { color: var(--main-red); border-bottom: 2px solid #f4f4f4; padding-bottom: 10px; margin-bottom: 18px; font-size: 18px; }
.details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
.detail-field label { display: block; font-size: 13px; color: #888; margin-bottom: 4px; }
.detail-field p { margin: 0; font-weight: 500; }
.history-table { width: 100%; border-collapse: collapse; }
.history-table th, .history-table td { padding: 12px; border-bottom: 1px solid #f0f0f0; font-size: 14px; text-align: left; }
.history-table th { background: #fafafa; color: #666; font-weight: 600; }
.status-pill { padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; background: #e8f5e9; color: #2e7d32; }
.edit-btn { background: var(--main-red); color: white; padding: 9px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; float: right; }
@media (max-width: 768px) { .profile-grid { grid-template-columns: 1fr; } .details-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="profile-grid">
    <aside class="profile-sidebar">
        <div class="profile-img"><i class="fas fa-user"></i></div>
        <h2 style="font-size:20px;">{{ $user->full_name }}</h2>
        @if ($user->donor)
            <div class="blood-badge">Type {{ $user->donor->blood_type }}</div>
        @endif
        <div class="stats-box">
            <div class="stat-item">
                <span>{{ $totalDonations }}</span>
                <label>Donations</label>
            </div>
            <div class="stat-item">
                <span>{{ $user->donor->points ?? 0 }}</span>
                <label>Points</label>
            </div>
        </div>
        <p style="font-size:13px;color:#888;margin-top:18px;">Member since {{ $user->created_at->format('M Y') }}</p>
    </aside>

    <section class="profile-main">
        <a href="{{ route('profile.edit') }}" class="edit-btn">Edit Profile</a>

        <div class="info-section">
            <h3><i class="fas fa-address-card"></i> Personal Information</h3>
            <div class="details-grid">
                <div class="detail-field"><label>Full Name</label><p>{{ $user->full_name }}</p></div>
                <div class="detail-field"><label>Email</label><p>{{ $user->email }}</p></div>
                <div class="detail-field"><label>Phone</label><p>{{ $user->donor->phone ?? '—' }}</p></div>
                <div class="detail-field"><label>City</label><p>{{ $user->donor->city ?? '—' }}</p></div>
                <div class="detail-field"><label>Age</label><p>{{ $user->donor->age ?? '—' }}</p></div>
                <div class="detail-field"><label>Weight</label><p>{{ $user->donor->weight ?? '—' }} kg</p></div>
            </div>
        </div>

        <div class="info-section">
            <h3><i class="fas fa-history"></i> Recent Donations</h3>
            <table class="history-table">
                <thead>
                    <tr><th>Hospital</th><th>Date</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse ($history as $donation)
                        <tr>
                            <td>{{ $donation->bloodRequest->hospital->hospital_name ?? '—' }}</td>
                            <td>{{ $donation->donation_date->format('M d, Y') }}</td>
                            <td><span class="status-pill">{{ $donation->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align:center;color:#999;padding:20px;">No donations yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
