<?php

namespace App\Http\Controllers;

use App\Models\BloodCar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class HospitalController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $hospital = $user->hospital;
        abort_unless($hospital, 404, 'Hospital profile missing.');

        $hospital->loadCount([
            'bloodRequests',
            'bloodRequests as pending_count' => fn ($q) => $q->whereIn('status', ['pending', 'active']),
            'bloodRequests as completed_count' => fn ($q) => $q->where('status', 'completed'),
            'bloodCars',
            'bloodCars as available_cars_count' => fn ($q) => $q->where('status', 'available'),
        ]);

        $requests = $hospital->bloodRequests()->latest()->take(10)->get();
        $cars     = $hospital->bloodCars()->latest()->take(10)->get();

        return view('hospital.dashboard', compact('hospital', 'requests', 'cars'));
    }

    public function cars()
    {
        $hospital = Auth::user()->hospital;
        abort_unless($hospital, 404);

        $cars = $hospital->bloodCars()->latest()->paginate(15);
        return view('hospital.cars', compact('cars', 'hospital'));
    }

    public function storeCar(Request $request)
    {
        $hospital = Auth::user()->hospital;
        abort_unless($hospital, 404);

        $data = $request->validate([
            'vehicle_name' => ['required', 'string', 'max:255'],
            'driver_name'  => ['required', 'string', 'max:255'],
            'status'       => ['required', Rule::in(['available', 'on_mission'])],
        ]);

        $hospital->bloodCars()->create($data);

        return back()->with('success', 'Blood car added.');
    }

    public function updateCarStatus(Request $request, BloodCar $car)
    {
        abort_unless($car->hospital_id === Auth::user()->hospital?->id, 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(['available', 'on_mission'])],
        ]);

        $car->update($data);
        return back()->with('success', 'Car status updated.');
    }

    public function destroyCar(BloodCar $car)
    {
        abort_unless($car->hospital_id === Auth::user()->hospital?->id, 403);
        $car->delete();
        return back()->with('success', 'Car deleted.');
    }
}
