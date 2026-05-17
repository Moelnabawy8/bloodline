@extends('layouts.app')

@section('title', 'Blood Requests')

@push('styles')
<style>
.req-header { background: var(--card-bg); padding: 24px; border-radius: 14px; box-shadow: 0 4px 10px rgba(0,0,0,0.04); margin-bottom: 22px; }
.req-header h1 { color: var(--main-red); font-size: 22px; margin-bottom: 18px; }
.filters { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px; }
.filters label { display: block; font-size: 12px; font-weight: bold; color: #666; margin-bottom: 4px; }
.filters select, .filters input, .filters button { width: 100%; padding: 9px; border: 1px solid #ddd; border-radius: 7px; font-size: 14px; margin-bottom: 0; }
.req-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 18px; }
.req-card { background: var(--card-bg); border-radius: 12px; padding: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); position: relative; border-left: 5px solid #ddd; transition: 0.3s; }
.req-card:hover { transform: translateY(-3px); box-shadow: 0 8px 18px rgba(0,0,0,0.08); }
.req-card.urgent { border-left-color: var(--main-red); }
.blood-circle { position: absolute; top: 18px; right: 18px; width: 44px; height: 44px; background: #fff1f1; color: var(--main-red); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 15px; border: 1px solid var(--main-red); }
.hospital-name { font-weight: bold; font-size: 17px; margin-bottom: 4px; display: block; padding-right: 50px; }
.info-item { font-size: 13px; color: #555; margin: 6px 0; display: flex; gap: 8px; align-items: center; }
.info-item i { color: var(--main-red); width: 14px; text-align: center; }
.card-footer { margin-top: 14px; border-top: 1px solid #eee; padding-top: 12px; display: flex; justify-content: space-between; align-items: center; }
.pill { font-size: 11px; font-weight: bold; text-transform: uppercase; padding: 3px 8px; border-radius: 4px; }
.pill.urgent { background: #ffebeb; color: #d00000; }
.pill.ok { background: #e8f5e9; color: #2e7d32; }
.pill.warn { background: #fff3cd; color: #856404; }
.btn-view { background: var(--main-red); color: white; padding: 7px 14px; border-radius: 18px; font-size: 12px; font-weight: bold; text-decoration: none; }
</style>
@endpush

@section('content')
<div class="container-bl py-4">
    <div class="req-header">
        <h1><i class="fas fa-hand-holding-medical"></i> Available Blood Requests</h1>
        <form method="GET" class="filters">
            <div>
                <label>Blood Type</label>
                <select name="blood_type">
                    <option value="All Types">All Types</option>
                    @foreach ($bloodTypes as $bt)
                        <option value="{{ $bt }}" @selected(request('blood_type') === $bt)>{{ $bt }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>City</label>
                <select name="location">
                    <option value="All Cities">All Cities</option>
                    @foreach ($locations as $loc)
                        <option value="{{ $loc }}" @selected(request('location') === $loc)>{{ $loc }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Search Hospital</label>
                <input type="text" name="hospital" placeholder="Hospital name..." value="{{ request('hospital') }}">
            </div>
            <div>
                <label>&nbsp;</label>
                <button type="submit" style="background:var(--main-red);color:white;border:none;font-weight:bold;cursor:pointer;">Search</button>
            </div>
        </form>
    </div>

    <div class="req-grid">
        @forelse ($requests as $req)
            <div class="req-card {{ in_array($req->status, ['active','pending']) ? 'urgent' : '' }}">
                <div class="blood-circle">{{ $req->blood_type }}</div>
                <span class="hospital-name">{{ $req->hospital->hospital_name ?? 'Unknown Hospital' }}</span>
                @if ($req->patient_name)
                    <small style="color:#888;display:block;margin-bottom:8px;">Patient: {{ $req->patient_name }}</small>
                @endif

                <div class="info-item"><i class="fas fa-map-marker-alt"></i> {{ $req->location ?? $req->hospital->district ?? '—' }}</div>
                <div class="info-item"><i class="fas fa-tint"></i> {{ $req->quantity_required }} Blood Unit(s)</div>
                <div class="info-item"><i class="fas fa-calendar-alt"></i> {{ $req->created_at->format('M d, Y') }}</div>

                <div class="card-footer">
                    @if ($req->status === 'active')
                        <span class="pill urgent">Active</span>
                    @elseif ($req->status === 'completed')
                        <span class="pill ok">Completed</span>
                    @elseif ($req->status === 'cancelled')
                        <span class="pill warn">Cancelled</span>
                    @else
                        <span class="pill warn">Pending</span>
                    @endif
                    <a href="{{ route('requests.show', $req) }}" class="btn-view">View Details</a>
                </div>
            </div>
        @empty
            <p style="grid-column:1/-1;text-align:center;color:#999;padding:40px;">No blood requests match your filters.</p>
        @endforelse
    </div>

    <div style="margin-top:25px;">{{ $requests->links() }}</div>
</div>
@endsection
