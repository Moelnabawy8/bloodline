@extends('layouts.app')

@section('title', 'Urgent Requests')

@section('content')
<div class="container-bl py-4">
    <div style="background:linear-gradient(135deg,var(--main-red),#b30000);color:white;padding:36px 24px;border-radius:16px;margin-bottom:22px;text-align:center;">
        <h1 style="font-weight:bold;"><i class="fas fa-bolt"></i> Urgent Blood Cases</h1>
        <p style="opacity:0.9;margin:8px 0 0;">These patients need help right now.</p>
    </div>

    <form method="GET" style="background:var(--card-bg);padding:20px;border-radius:12px;margin-bottom:22px;box-shadow:0 4px 10px rgba(0,0,0,0.04);">
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px,1fr));gap:14px;">
            <div>
                <label style="font-size:12px;font-weight:bold;color:#666;display:block;margin-bottom:4px;">Blood Type</label>
                <select name="blood_type" style="margin:0;">
                    <option value="">Any</option>
                    @foreach ($bloodTypes as $bt)
                        <option value="{{ $bt }}" @selected(request('blood_type') === $bt)>{{ $bt }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:12px;font-weight:bold;color:#666;display:block;margin-bottom:4px;">City</label>
                <select name="location" style="margin:0;">
                    <option value="">Any</option>
                    @foreach ($locations as $loc)
                        <option value="{{ $loc }}" @selected(request('location') === $loc)>{{ $loc }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>&nbsp;</label>
                <button type="submit" style="margin:0;">Filter</button>
            </div>
        </div>
    </form>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
        @forelse ($bloodRequests as $req)
            <div style="background:var(--card-bg);padding:20px;border-radius:12px;border-left:5px solid var(--main-red);box-shadow:0 4px 10px rgba(0,0,0,0.05);position:relative;">
                <div style="position:absolute;top:18px;right:18px;background:var(--main-red);color:white;padding:6px 12px;border-radius:14px;font-weight:bold;font-size:14px;">{{ $req->blood_type }}</div>
                <h4 style="font-weight:bold;padding-right:60px;">{{ $req->hospital->hospital_name ?? '—' }}</h4>
                <p style="color:#666;margin:6px 0;font-size:13px;"><i class="fas fa-map-marker-alt" style="color:var(--main-red);"></i> {{ $req->location ?? $req->hospital->district ?? '—' }}</p>
                <p style="color:#666;margin:6px 0;font-size:13px;"><i class="fas fa-tint" style="color:var(--main-red);"></i> {{ $req->quantity_required }} Unit(s) needed</p>
                <p style="color:#666;margin:6px 0;font-size:13px;"><i class="fas fa-clock" style="color:var(--main-red);"></i> {{ $req->created_at->diffForHumans() }}</p>
                <a href="{{ route('urgent.show', $req) }}" style="background:var(--main-red);color:white;padding:8px 16px;border-radius:18px;text-decoration:none;font-weight:bold;font-size:13px;display:inline-block;margin-top:10px;">Respond Now</a>
            </div>
        @empty
            <p style="grid-column:1/-1;text-align:center;color:#999;padding:40px;">No urgent cases right now.</p>
        @endforelse
    </div>
    <div style="margin-top:24px;">{{ $bloodRequests->links() }}</div>
</div>
@endsection
