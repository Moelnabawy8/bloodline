@extends('layouts.app')

@section('title', 'Admin Dashboard')

@push('styles')
<style>
.admin-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 28px; }
.stat-card { background: var(--card-bg); border-radius: 14px; padding: 22px; text-align: center; box-shadow: 0 5px 14px rgba(0,0,0,0.06); border-bottom: 4px solid var(--main-red); }
.stat-card h3 { font-weight: bold; font-size: 28px; margin: 0; }
.stat-card small { color: #888; text-transform: uppercase; font-weight: bold; letter-spacing: 1px; }
.action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; flex-wrap: wrap; gap: 10px; }
.btn-add { background: var(--main-red); color: white; padding: 10px 22px; border-radius: 22px; text-decoration: none; font-weight: bold; }
.table-wrap { background: var(--card-bg); border-radius: 14px; padding: 22px; box-shadow: 0 5px 14px rgba(0,0,0,0.05); }
.hospitals-table { width: 100%; border-collapse: collapse; }
.hospitals-table th, .hospitals-table td { padding: 12px; text-align: left; }
.hospitals-table thead { background: #fafafa; }
.hospitals-table tbody tr { border-bottom: 1px solid #f0f0f0; }
.modal-bl { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center; padding:20px; }
.modal-bl.open { display:flex; }
.modal-content { background: var(--card-bg); border-radius:18px; max-width:500px; width:100%; padding:30px; }
.modal-content h5 { color: var(--main-red); font-weight: bold; margin-bottom: 18px; }
</style>
@endpush

@section('content')
<div class="container-bl py-4">
    <div class="action-bar">
        <div>
            <h2 style="font-weight:bold;margin:0;">System Control</h2>
            <small style="color:#888;">Welcome back, Admin.</small>
        </div>
        <a href="#" class="btn-add" onclick="document.getElementById('hospitalModal').classList.add('open');return false;">
            <i class="fas fa-plus-circle"></i> Register Hospital
        </a>
    </div>

    <div class="admin-grid">
        <div class="stat-card" style="border-color:#0d6efd;">
            <h3>{{ $stats['donors'] }}</h3>
            <small>Total Donors</small>
        </div>
        <div class="stat-card" style="border-color:var(--main-green);">
            <h3>{{ $stats['hospitals'] }}</h3>
            <small>Hospitals</small>
        </div>
        <div class="stat-card" style="border-color:#ffc107;">
            <h3>{{ $stats['active'] }}</h3>
            <small>Active Requests</small>
        </div>
        <div class="stat-card">
            <h3>{{ $stats['completed'] }}</h3>
            <small>Completed</small>
        </div>
    </div>

    <div class="table-wrap">
        <h5 style="font-weight:bold;margin-bottom:14px;"><i class="fas fa-list"></i> Recent Hospital Registrations</h5>
        <table class="hospitals-table">
            <thead><tr><th>Hospital</th><th>District</th><th>Username</th><th>Created</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse ($recentHospitals as $h)
                    <tr>
                        <td style="font-weight:bold;">{{ $h->hospital_name }}</td>
                        <td>{{ $h->district }}</td>
                        <td><code style="color:#0d6efd;">{{ $h->username }}</code></td>
                        <td>{{ $h->created_at->format('M d, Y') }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.hospitals.destroy', $h) }}" onsubmit="return confirm('Delete this hospital?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background:transparent;color:#dc3545;border:1px solid #dc3545;width:auto;padding:4px 10px;border-radius:6px;font-size:12px;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;color:#999;padding:20px;">No hospitals yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal-bl" id="hospitalModal">
    <div class="modal-content">
        <h5><i class="fas fa-hospital-user"></i> Create Hospital Account</h5>
        <form method="POST" action="{{ route('admin.hospitals.store') }}">
            @csrf
            <input type="text" name="hospital_name" placeholder="Hospital Name" required>
            <input type="text" name="username" placeholder="Username (unique)" required>
            <select name="district" required>
                <option value="">Select District</option>
                <option value="East Bank">East Bank</option>
                <option value="West Bank">West Bank</option>
                <option value="Esna">Esna</option>
                <option value="Armant">Armant</option>
                <option value="Other">Other</option>
            </select>
            <div class="flex-row">
                <input type="email" name="email" placeholder="hospital@email.com" required>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <small style="color:#666;font-size:12px;">📍 Coordinates (optional — improves geo matching)</small>
            <div class="flex-row">
                <input type="text" name="latitude"  placeholder="Latitude (e.g. 25.6872)"  pattern="-?[0-9]+(\.[0-9]+)?">
                <input type="text" name="longitude" placeholder="Longitude (e.g. 32.6396)" pattern="-?[0-9]+(\.[0-9]+)?">
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" style="flex:2;">Create</button>
                <button type="button" onclick="document.getElementById('hospitalModal').classList.remove('open');" style="flex:1;background:#eee;color:#333;">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection
