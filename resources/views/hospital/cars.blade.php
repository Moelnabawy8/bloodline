@extends('layouts.app')

@section('title', 'Manage Blood Cars')

@section('content')
<div class="container-bl py-4">
    <h2 style="font-weight:bold;margin-bottom:20px;"><i class="fas fa-ambulance" style="color:#0d6efd;"></i> Blood Cars</h2>

    <div style="background:var(--card-bg);padding:22px;border-radius:14px;margin-bottom:24px;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
        <h5 style="font-weight:bold;margin-bottom:14px;">Add New Car</h5>
        <form method="POST" action="{{ route('hospital.cars.store') }}">
            @csrf
            <div class="flex-row">
                <input type="text" name="vehicle_name" placeholder="Vehicle Name / Plate" required>
                <input type="text" name="driver_name" placeholder="Driver Name" required>
                <select name="status" required>
                    <option value="available">Available</option>
                    <option value="on_mission">On Mission</option>
                </select>
            </div>
            <button type="submit" style="max-width:200px;">Add Car</button>
        </form>
    </div>

    <div style="background:var(--card-bg);border-radius:14px;padding:22px;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
        <table style="width:100%;border-collapse:collapse;">
            <thead><tr style="background:#fafafa;"><th style="padding:11px;text-align:left;">Vehicle</th><th style="padding:11px;text-align:left;">Driver</th><th style="padding:11px;text-align:left;">Status</th><th style="padding:11px;text-align:left;">Actions</th></tr></thead>
            <tbody>
                @forelse ($cars as $car)
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:11px;font-weight:bold;">{{ $car->vehicle_name }}</td>
                        <td style="padding:11px;">{{ $car->driver_name }}</td>
                        <td style="padding:11px;">
                            <form method="POST" action="{{ route('hospital.cars.status', $car) }}" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" style="width:auto;padding:5px 8px;margin:0;">
                                    <option value="available" @selected($car->status === 'available')>Available</option>
                                    <option value="on_mission" @selected($car->status === 'on_mission')>On Mission</option>
                                </select>
                            </form>
                        </td>
                        <td style="padding:11px;">
                            <form method="POST" action="{{ route('hospital.cars.destroy', $car) }}" onsubmit="return confirm('Delete?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background:none;color:#dc3545;border:1px solid #dc3545;padding:4px 10px;border-radius:6px;cursor:pointer;width:auto;font-size:12px;"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;color:#999;padding:20px;">No cars yet</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top:18px;">{{ $cars->links() }}</div>
    </div>
</div>
@endsection
