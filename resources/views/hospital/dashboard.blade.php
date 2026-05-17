@extends('layouts.app')

@section('title', 'Hospital Dashboard')

@push('styles')
<style>
.h-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 26px; }
.h-stat { background: var(--card-bg); border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-left: 4px solid var(--main-red); }
.h-stat h3 { font-size: 26px; margin: 0; font-weight: bold; }
.h-stat small { color: #888; text-transform: uppercase; font-weight: bold; font-size: 11px; letter-spacing: 1px; }
.section-card { background: var(--card-bg); border-radius: 14px; padding: 22px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 22px; }
.section-card h5 { font-weight: bold; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; }
.h-table { width: 100%; border-collapse: collapse; }
.h-table th, .h-table td { padding: 11px; border-bottom: 1px solid #f0f0f0; font-size: 14px; text-align: left; }
.h-table th { background: #fafafa; font-size: 12px; text-transform: uppercase; color: #666; }
.btn-small { padding: 6px 12px; border-radius: 16px; font-size: 12px; font-weight: 600; text-decoration: none; }
.btn-add-small { background: var(--main-red); color: white; padding: 8px 16px; border-radius: 18px; text-decoration: none; font-weight: bold; font-size: 13px; }
.status-pill { padding: 3px 10px; border-radius: 10px; font-size: 11px; font-weight: bold; }
.status-pending { background: #fff3cd; color: #856404; }
.status-active { background: #ffebeb; color: #d00000; }
.status-completed { background: #e8f5e9; color: #2e7d32; }
.status-available { background: #e8f5e9; color: #2e7d32; }
.status-on_mission { background: #fff3cd; color: #856404; }
</style>
@endpush

@section('content')
<div class="container-bl py-4">
    <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:20px;align-items:center;">
        <div>
            <h2 style="font-weight:bold;margin:0;">{{ $hospital->hospital_name }}</h2>
            <small style="color:#888;">{{ $hospital->district }}</small>
        </div>
    </div>

    <div class="h-grid">
        <div class="h-stat">
            <h3>{{ $hospital->blood_requests_count }}</h3>
            <small>Total Requests</small>
        </div>
        <div class="h-stat" style="border-color:#ffc107;">
            <h3>{{ $hospital->pending_count }}</h3>
            <small>Pending / Active</small>
        </div>
        <div class="h-stat" style="border-color:var(--main-green);">
            <h3>{{ $hospital->completed_count }}</h3>
            <small>Completed</small>
        </div>
        <div class="h-stat" style="border-color:#0d6efd;">
            <h3>{{ $hospital->available_cars_count }}/{{ $hospital->blood_cars_count }}</h3>
            <small>Available Cars</small>
        </div>
    </div>

    <div class="section-card">
        <h5>
            <span><i class="fas fa-tint" style="color:var(--main-red);"></i> Blood Requests</span>
            <a href="{{ route('requests.create') }}" class="btn-add-small"><i class="fas fa-plus"></i> New Request</a>
        </h5>
        <table class="h-table">
            <thead><tr><th>ID</th><th>Blood Type</th><th>Qty</th><th>Urgency</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
                @forelse ($requests as $req)
                    <tr>
                        <td>#{{ $req->id }}</td>
                        <td><strong>{{ $req->blood_type }}</strong></td>
                        <td>{{ $req->quantity_required }}</td>
                        <td>{{ ucfirst($req->urgency_level) }}</td>
                        <td><span class="status-pill status-{{ $req->status }}">{{ ucfirst($req->status) }}</span></td>
                        <td>{{ $req->created_at->format('M d') }}</td>
                        <td>
                            <a href="{{ route('requests.show', $req) }}" class="btn-small" style="color:var(--main-red);">View</a>
                            <form method="POST" action="{{ route('requests.destroy', $req) }}" style="display:inline;" onsubmit="return confirm('Delete this request?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background:none;color:#dc3545;border:none;cursor:pointer;width:auto;font-size:13px;padding:0;"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;color:#999;padding:20px;">No requests yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section-card">
        <h5>
            <span><i class="fas fa-ambulance" style="color:#0d6efd;"></i> Blood Cars</span>
            <a href="{{ route('hospital.cars') }}" class="btn-small" style="color:var(--main-red);">Manage all →</a>
        </h5>
        <table class="h-table">
            <thead><tr><th>Vehicle</th><th>Driver</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($cars as $car)
                    <tr>
                        <td><strong>{{ $car->vehicle_name }}</strong></td>
                        <td>{{ $car->driver_name }}</td>
                        <td><span class="status-pill status-{{ $car->status }}">{{ ucfirst(str_replace('_', ' ', $car->status)) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align:center;color:#999;padding:20px;">No cars yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
