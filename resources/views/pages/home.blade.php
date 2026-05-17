@extends('layouts.app')

@section('title', 'Home')

@push('styles')
<style>
.hero { background: linear-gradient(rgba(139,0,0,0.7), rgba(0,0,0,0.7)), url('{{ asset('assets/images/im2.jpg') }}'); background-size: cover; background-position: center; color: white; text-align: center; padding: 100px 20px 80px; border-bottom-left-radius: 40px; border-bottom-right-radius: 40px; }
.hero h1 { font-size: 48px; font-weight: 800; margin-bottom: 16px; }
.hero p { font-size: 18px; opacity: 0.9; max-width: 600px; margin: 0 auto 28px; }
.hero .cta { background: white; color: var(--main-red); padding: 14px 32px; border-radius: 28px; font-weight: bold; text-decoration: none; display: inline-block; font-size: 16px; margin: 0 6px; }
.hero .cta.outline { background: transparent; color: white; border: 2px solid white; }
.stats-strip { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 18px; max-width: 900px; margin: -50px auto 30px; padding: 0 20px; }
.stat-pill { background: var(--card-bg); padding: 24px; border-radius: 14px; text-align: center; box-shadow: 0 10px 24px rgba(0,0,0,0.07); }
.stat-pill h2 { color: var(--main-red); font-weight: bold; font-size: 32px; margin: 0; }
.stat-pill small { color: #888; text-transform: uppercase; font-weight: bold; }
.urgent-section { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
.urgent-section h2 { color: var(--main-red); font-weight: bold; text-align: center; margin-bottom: 24px; }
.urgent-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px; }
.u-card { background: var(--card-bg); padding: 22px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border-left: 4px solid var(--main-red); }
.u-card h4 { color: var(--main-red); font-weight: bold; }
.u-card a { color: var(--main-red); text-decoration: none; font-weight: bold; }
</style>
@endpush

@section('content')
<section class="hero">
    <h1>Save Lives. Donate Blood.</h1>
    <p>Every drop counts. Join thousands of donors across Egypt connecting with patients in urgent need.</p>
    <div>
        <a href="{{ route('register') }}" class="cta">Become a Donor</a>
        <a href="{{ route('urgent.index') }}" class="cta outline">View Urgent Cases</a>
    </div>
</section>

<div class="stats-strip">
    <div class="stat-pill"><h2>{{ $donorsCount }}</h2><small>Registered Donors</small></div>
    <div class="stat-pill"><h2>{{ $urgentCount }}</h2><small>Urgent Cases</small></div>
    <div class="stat-pill"><h2>{{ $campaignsCount }}</h2><small>Upcoming Campaigns</small></div>
</div>

<section class="urgent-section">
    <h2><i class="fas fa-bolt"></i> Latest Urgent Requests</h2>
    <div class="urgent-cards">
        @forelse ($latestUrgent as $req)
            <div class="u-card">
                <h4>{{ $req->blood_type }}</h4>
                <p style="margin:6px 0;color:#666;font-size:14px;">{{ $req->hospital->hospital_name ?? '—' }} — {{ $req->location ?? $req->hospital->district ?? '—' }}</p>
                <a href="{{ route('requests.show', $req) }}">View details →</a>
            </div>
        @empty
            <p style="grid-column:1/-1;text-align:center;color:#999;padding:30px;">No urgent cases right now. Stay safe!</p>
        @endforelse
    </div>
</section>
@endsection
