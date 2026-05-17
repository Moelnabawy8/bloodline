<?php

namespace App\Http\Controllers;

use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'donors'     => Donor::count(),
            'hospitals'  => Hospital::count(),
            'active'     => BloodRequest::whereIn('status', ['pending', 'active'])->count(),
            'completed'  => BloodRequest::where('status', 'completed')->count(),
        ];

        $recentHospitals = Hospital::with('user')->latest()->take(8)->get();

        return view('admin.dashboard', compact('stats', 'recentHospitals'));
    }

    public function storeHospital(Request $request)
    {
        $data = $request->validate([
            'hospital_name' => ['required', 'string', 'max:255'],
            'district'      => ['required', 'string', 'max:100'],
            'email'         => ['required', 'email', 'unique:users,email'],
            'password'      => ['required', 'string', 'min:6'],
            'username'      => ['required', 'string', 'max:100', 'unique:hospitals,username'],
            'latitude'      => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'     => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'full_name' => $data['hospital_name'],
                'email'     => strtolower($data['email']),
                'password'  => $data['password'],
                'role'      => 'hospital',
            ]);

            Hospital::create([
                'user_id'       => $user->id,
                'hospital_name' => $data['hospital_name'],
                'district'      => $data['district'],
                'username'      => $data['username'],
                'latitude'      => $data['latitude']  ?? null,
                'longitude'     => $data['longitude'] ?? null,
            ]);
        });

        return back()->with('success', 'Hospital account created.');
    }

    public function destroyHospital(Hospital $hospital)
    {
        $hospital->user?->delete();
        return back()->with('success', 'Hospital deleted.');
    }
}
