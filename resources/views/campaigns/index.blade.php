@extends('layouts.app')

@section('title', 'Campaigns')

@section('content')
<div class="container-bl py-4">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:22px;">
        <h2 style="color:var(--main-red);font-weight:bold;margin:0;"><i class="fas fa-bullhorn"></i> Blood Donation Campaigns</h2>
        @auth
            @if (auth()->user()->isAdmin() || auth()->user()->isHospital())
                <a href="{{ route('campaigns.create') }}" class="btn-primary-bl" style="width:auto;padding:9px 18px;">+ Add Campaign</a>
            @endif
        @endauth
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px;">
        @forelse ($campaigns as $c)
            <a href="{{ route('campaigns.show', $c) }}" style="text-decoration:none;color:inherit;">
                <div style="background:var(--card-bg);border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);transition:0.3s;">
                    @if ($c->image)
                        <img src="{{ asset('storage/'.$c->image) }}" alt="{{ $c->title }}" style="width:100%;height:160px;object-fit:cover;">
                    @else
                        <div style="height:160px;background:linear-gradient(135deg,var(--main-red),#b30000);display:flex;align-items:center;justify-content:center;color:white;font-size:48px;"><i class="fas fa-tint"></i></div>
                    @endif
                    <div style="padding:18px;">
                        <h4 style="color:var(--main-red);font-weight:bold;margin-bottom:6px;font-size:17px;">{{ $c->title }}</h4>
                        <p style="color:#666;font-size:13px;margin-bottom:10px;"><i class="fas fa-map-marker-alt"></i> {{ $c->location }}, {{ $c->city }}</p>
                        <p style="color:#666;font-size:13px;margin:0;"><i class="fas fa-calendar"></i> {{ $c->event_date->format('M d, Y') }} — {{ $c->start_time }} to {{ $c->end_time }}</p>
                    </div>
                </div>
            </a>
        @empty
            <p style="grid-column:1/-1;text-align:center;color:#999;padding:40px;">No upcoming campaigns. Check back soon!</p>
        @endforelse
    </div>
    <div style="margin-top:24px;">{{ $campaigns->links() }}</div>
</div>
@endsection
