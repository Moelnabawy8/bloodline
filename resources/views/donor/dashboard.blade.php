@extends('layouts.app')

@section('title', 'Donor Dashboard')

@push('styles')
<style>
.stat-card { background: var(--card-bg); border-radius: 14px; padding: 22px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border-left: 5px solid var(--main-red); display: flex; align-items: center; gap: 16px; }
.stat-card i { font-size: 36px; color: var(--main-red); }
.stat-card h4 { font-size: 22px; margin: 0; font-weight: 800; }
.stat-card small { color: #888; }
.cta-banner { background: #fff1f1; border-radius: 14px; padding: 22px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
.cta-banner h5 { color: var(--main-red); font-weight: bold; margin: 0 0 6px; }
.btn-browse { border: 2px solid var(--main-red); color: var(--main-red); padding: 9px 22px; border-radius: 22px; font-weight: bold; text-decoration: none; }
.btn-browse:hover { background: var(--main-red); color: white; }
.req-card { background: var(--card-bg); border-radius: 12px; padding: 14px 18px; margin-bottom: 12px; border-left: 4px solid #ffc107; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.req-card .btn-view { background: var(--main-green); color: white; padding: 6px 16px; border-radius: 18px; font-size: 13px; text-decoration: none; font-weight: 600; }
.section-card { background: var(--card-bg); border-radius: 14px; padding: 22px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 20px; }
.section-card h5 { font-weight: bold; margin-bottom: 16px; }
.history-table { width: 100%; border-collapse: collapse; }
.history-table th, .history-table td { padding: 11px; border-bottom: 1px solid #f0f0f0; font-size: 14px; text-align: left; }
.history-table th { font-size: 12px; text-transform: uppercase; color: #666; letter-spacing: 1px; }
.badge-completed { background: #e7f9ee; color: #28a745; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; border: 1px solid #28a745; }
</style>
@endpush

@section('content')
<div class="container-bl py-4">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
        <div>
            <h2 style="font-weight:bold;margin:0;">Welcome back, {{ $user->full_name }} <span style="color:var(--main-red);">❤</span></h2>
            <p style="color:#888;margin:4px 0 0;">
                @if ($lastDonation)
                    Your last donation was on {{ $lastDonation->donation_date->format('M d, Y') }}.
                @else
                    You haven't donated yet — be a hero today!
                @endif
            </p>
        </div>
    </div>

    <div class="cta-banner">
        <div>
            <h5>Ready to save a life today?</h5>
            <p style="margin:0;color:#666;font-size:14px;">Explore urgent blood requests in your area.</p>
        </div>
        <a href="{{ route('requests.index') }}" class="btn-browse">Browse All Requests</a>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;margin-bottom:30px;">
        <div class="stat-card">
            <i class="fas fa-hand-holding-heart"></i>
            <div><h4>{{ $totalDonations }}</h4><small>Total Donations</small></div>
        </div>
        <div class="stat-card" style="border-left-color:var(--main-green);">
            <i class="fas fa-heartbeat" style="color:var(--main-green);"></i>
            <div><h4 style="color:var(--main-green);">{{ $eligibility }}</h4><small>Eligibility</small></div>
        </div>
        <div class="stat-card" style="border-left-color:#ffc107;">
            <i class="fas fa-award" style="color:#ffc107;"></i>
            <div><h4>{{ $rank }}</h4><small>Donor Rank</small></div>
        </div>
    </div>

    @if ($recommended->isNotEmpty())
        <div class="section-card" style="border-left:5px solid var(--main-red);background:#fff8f8;">
            <h5 style="color:var(--main-red);"><i class="fas fa-bullseye"></i> Recommended for You — matches your blood type ({{ $user->donor->blood_type ?? '' }})</h5>
            @foreach ($recommended as $req)
                <div class="req-card" style="border-left-color:var(--main-red);">
                    <div>
                        <strong>{{ $req->hospital->hospital_name ?? '—' }}</strong>
                        <small style="background:var(--main-red);color:white;padding:2px 8px;border-radius:10px;margin-left:6px;">{{ $req->blood_type }}</small>
                        <p style="margin:4px 0 0;font-size:13px;color:#666;">
                            <i class="fas fa-map-marker-alt"></i>
                            {{ $req->location ?? $req->hospital->district ?? '—' }}
                            @if (! is_null($req->distance_km))
                                · <strong>{{ number_format($req->distance_km, 1) }} km from you</strong>
                            @endif
                            @if ($req->urgency_level === 'high')
                                · <span style="color:var(--main-red);font-weight:bold;">Urgent</span>
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('donations.create', ['bloodRequest' => $req->id]) }}" class="btn-view">Donate</a>
                </div>
            @endforeach
        </div>
    @endif

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;@media(max-width:768px){grid-template-columns:1fr;}">
        <div>
            <div class="section-card">
                <h5><i class="fas fa-bell" style="color:#ffc107;"></i> Recent Requests</h5>
                @forelse ($recentRequests as $req)
                    <div class="req-card">
                        <div>
                            <strong>Blood Type: {{ $req->blood_type }}</strong>
                            <p style="margin:4px 0 0;font-size:13px;color:#888;">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $req->hospital->hospital_name ?? 'Unknown' }}
                                @if ($req->location) — {{ $req->location }} @endif
                                @if ($req->urgency_level === 'high')
                                    | <span style="color:var(--main-red);font-weight:bold;"><i class="fas fa-clock"></i> Urgent</span>
                                @endif
                            </p>
                        </div>
                        <a href="{{ route('requests.show', $req) }}" class="btn-view">View Case</a>
                    </div>
                @empty
                    <p style="color:#999;text-align:center;padding:20px;">No active requests right now.</p>
                @endforelse
            </div>

            <div class="section-card">
                <h5><i class="fas fa-history" style="color:#0d6efd;"></i> Donation History</h5>
                <table class="history-table">
                    <thead><tr><th>Date</th><th>Hospital</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse ($history as $d)
                            <tr>
                                <td>{{ $d->donation_date->format('M d, Y') }}</td>
                                <td>{{ $d->bloodRequest->hospital->hospital_name ?? '—' }}</td>
                                <td><span class="badge-completed">{{ $d->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center;color:#999;padding:20px;">No donation history yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <div class="section-card" style="background:linear-gradient(45deg,#800000,#b30000);color:white;">
                <h6 style="font-weight:bold;"><i class="fas fa-hourglass-half"></i> Eligibility</h6>
                <p style="font-size:14px;opacity:0.9;margin:8px 0;">Status: <strong>{{ $eligibility }}</strong></p>
                <p style="font-size:13px;opacity:0.8;margin:0;">Stay hydrated and eat iron-rich foods to recover faster.</p>
            </div>
            <div class="section-card" style="text-align:center;">
                <small style="color:#888;text-transform:uppercase;font-weight:bold;">Reward Points</small>
                <h2 style="color:#ffc107;margin:8px 0;font-weight:bold;">{{ $user->donor->points ?? 0 }} <i class="fas fa-star"></i></h2>
                <small style="color:#888;">Redeem for health checkup vouchers</small>
            </div>
        </div>
    </div>
</div>
@endsection
