<?php

namespace App\Http\Controllers;

use App\Models\BloodRequest;
use Illuminate\Http\Request;

class UrgentController extends Controller
{
    private const BLOOD_TYPES = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    private const LOCATIONS = ['Cairo', 'Giza', 'Alexandria', 'Luxor', 'Aswan', 'Mansoura', 'Qena', 'Sohag'];

    public function index(Request $request)
    {
        $query = BloodRequest::with('hospital')
            ->whereIn('status', ['active', 'pending'])
            ->where('urgency_level', 'high')
            ->latest();

        if ($request->filled('blood_type')) {
            $query->where('blood_type', $request->blood_type);
        }
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        return view('urgent.index', [
            'bloodRequests' => $query->paginate(12)->withQueryString(),
            'bloodTypes'    => self::BLOOD_TYPES,
            'locations'     => self::LOCATIONS,
        ]);
    }

    public function show(BloodRequest $bloodRequest)
    {
        $bloodRequest->load('hospital');
        return view('urgent.show', compact('bloodRequest'));
    }
}
