@extends('layouts.app')

@section('title', 'Top Donors')

@push('styles')
<style>
.header-section { background: linear-gradient(rgba(139,0,0,0.85), rgba(0,0,0,0.85)); color: white; padding: 80px 0; text-align: center; border-bottom-left-radius: 40px; border-bottom-right-radius: 40px; }
.podium { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; max-width: 900px; margin: -50px auto 30px; padding: 0 20px; align-items: end; }
.podium-card { background: var(--card-bg); border-radius: 22px; text-align: center; padding: 30px 18px; box-shadow: 0 15px 35px rgba(0,0,0,0.08); position: relative; }
.first-place { border: 2px solid var(--gold); padding-top: 50px; }
.rank-badge { position: absolute; top: -22px; left: 50%; transform: translateX(-50%); width: 44px; height: 44px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1); font-size: 22px; }
.gold-bg { color: var(--gold); }
.silver-bg { color: var(--silver); }
.bronze-bg { color: var(--bronze); }
.donor-img { width: 90px; height: 90px; border-radius: 50%; background: #fff5f5; display: inline-flex; align-items: center; justify-content: center; font-size: 40px; color: var(--main-red); margin-bottom: 12px; border: 4px solid #fff; outline: 2px solid #eee; }
.first-place .donor-img { width: 110px; height: 110px; outline-color: var(--gold); }
.blood-pill { background: #fff5f5; color: var(--main-red); padding: 5px 12px; border-radius: 12px; font-weight: bold; display: inline-block; margin-top: 8px; }
.table-container { background: var(--card-bg); border-radius: 22px; padding: 30px; max-width: 1100px; margin: 30px auto; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
.lb-table { width: 100%; border-collapse: collapse; }
.lb-table thead { background: var(--main-red); color: white; }
.lb-table th, .lb-table td { padding: 14px; text-align: left; }
.lb-table tbody tr { border-bottom: 1px solid #f0f0f0; }
@media (max-width: 768px) { .podium { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<section class="header-section">
    <h1 style="font-size:38px;font-weight:bold;">Our Lifesaver Heroes</h1>
    <p style="opacity:0.8;">Recognizing the amazing people who donate regularly.</p>
</section>

<div class="podium">
    <div class="podium-card">
        <div class="rank-badge silver-bg"><i class="fas fa-medal"></i></div>
        <div class="donor-img"><i class="fas fa-user"></i></div>
        <h5 style="font-weight:bold;">{{ $top2->full_name ?? '—' }}</h5>
        <p style="color:#888;font-size:13px;">{{ $top2->donations_count ?? 0 }} Donations</p>
        @if ($top2 && $top2->blood_type) <span class="blood-pill">{{ $top2->blood_type }}</span> @endif
    </div>

    <div class="podium-card first-place">
        <div class="rank-badge gold-bg" style="font-size:28px;width:54px;height:54px;top:-27px;"><i class="fas fa-crown"></i></div>
        <div class="donor-img"><i class="fas fa-user"></i></div>
        <h4 style="font-weight:bold;">{{ $top1->full_name ?? '—' }}</h4>
        <p style="color:#888;">{{ $top1->donations_count ?? 0 }} Donations</p>
        @if ($top1 && $top1->blood_type) <span class="blood-pill" style="background:var(--main-red);color:white;">{{ $top1->blood_type }}</span> @endif
    </div>

    <div class="podium-card">
        <div class="rank-badge bronze-bg"><i class="fas fa-medal"></i></div>
        <div class="donor-img"><i class="fas fa-user"></i></div>
        <h5 style="font-weight:bold;">{{ $top3->full_name ?? '—' }}</h5>
        <p style="color:#888;font-size:13px;">{{ $top3->donations_count ?? 0 }} Donations</p>
        @if ($top3 && $top3->blood_type) <span class="blood-pill">{{ $top3->blood_type }}</span> @endif
    </div>
</div>

<div class="table-container">
    <h4 style="font-weight:bold;margin-bottom:18px;"><i class="fas fa-chart-line" style="color:var(--main-red);"></i> Leaderboard</h4>
    <table class="lb-table">
        <thead><tr><th>Rank</th><th>Donor Name</th><th>Blood Type</th><th>Donations</th><th>City</th></tr></thead>
        <tbody>
            @forelse ($rest as $i => $d)
                <tr>
                    <td><strong>#{{ $i + 4 }}</strong></td>
                    <td style="font-weight:bold;">{{ $d->full_name }}</td>
                    <td>@if ($d->blood_type) <span class="blood-pill">{{ $d->blood_type }}</span> @endif</td>
                    <td>{{ $d->donations_count }}</td>
                    <td>{{ $d->city ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;color:#999;padding:24px;">No more donors yet</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
