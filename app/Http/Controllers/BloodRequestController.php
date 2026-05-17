<?php

namespace App\Http\Controllers;

use App\Models\BloodRequest;
use App\Notifications\UrgentBloodRequestNotification;
use App\Services\DonorMatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class BloodRequestController extends Controller
{
    private const BLOOD_TYPES = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
    private const LOCATIONS = ['Cairo', 'Giza', 'Alexandria', 'Luxor', 'Aswan', 'Mansoura', 'Qena', 'Sohag'];

    public function index(Request $request)
    {
        $query = BloodRequest::with('hospital')->latest();

        if ($request->filled('blood_type') && $request->blood_type !== 'All Types') {
            $query->where('blood_type', $request->blood_type);
        }
        if ($request->filled('location') && $request->location !== 'All Cities') {
            $query->where('location', $request->location);
        }
        if ($request->filled('hospital')) {
            $query->whereHas('hospital', fn ($q) => $q->where('hospital_name', 'like', '%'.$request->hospital.'%'));
        }

        $requests = $query->paginate(12)->withQueryString();

        return view('requests.index', [
            'requests'   => $requests,
            'bloodTypes' => self::BLOOD_TYPES,
            'locations'  => self::LOCATIONS,
        ]);
    }

    public function show(BloodRequest $request)
    {
        $request->load('hospital');
        return view('requests.show', ['bloodRequest' => $request]);
    }

    public function create()
    {
        return view('requests.create', [
            'bloodTypes' => self::BLOOD_TYPES,
            'locations'  => self::LOCATIONS,
        ]);
    }

    public function store(Request $request, DonorMatcher $matcher)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless($user->isHospital() && $user->hospital, 403);

        $data = $request->validate([
            'blood_type'        => ['required', Rule::in(self::BLOOD_TYPES)],
            'location'          => ['nullable', Rule::in(self::LOCATIONS)],
            'quantity_required' => ['required', 'integer', 'min:1', 'max:50'],
            'urgency_level'     => ['required', Rule::in(['low', 'medium', 'high'])],
            'patient_name'      => ['nullable', 'string', 'max:255'],
            'patient_info'      => ['nullable', 'string', 'max:1000'],
        ]);

        $data['hospital_id'] = $user->hospital->id;
        $data['status']      = 'active';

        $bloodRequest = BloodRequest::create($data);
        $bloodRequest->load('hospital');

        $notifiedCount = $this->notifyMatchingDonors($bloodRequest, $matcher);

        if ($notifiedCount > 0) {
            $msg = "Blood request created. {$notifiedCount} matching donor(s) notified.";
        } else {
            $msg = 'Blood request created. No matching donors to notify yet. '
                 . 'Try: php artisan donors:debug-match ' . $bloodRequest->id;
        }

        return redirect()->route('hospital.dashboard')->with('success', $msg);
    }

    /**
     * Find matching donors and send notifications.
     * Failures are logged but do not break the request flow.
     */
    private function notifyMatchingDonors(BloodRequest $bloodRequest, DonorMatcher $matcher): int
    {
        try {
            $donors = $matcher->matchFor($bloodRequest);
        } catch (\Throwable $e) {
            Log::error('Donor matching failed', ['err' => $e->getMessage(), 'request_id' => $bloodRequest->id]);
            return 0;
        }

        $notifiables = $donors->map(fn ($d) => $d->user)->filter();
        if ($notifiables->isEmpty()) {
            return 0;
        }

        try {
            foreach ($donors as $donor) {
                $distance = isset($donor->distance_km) ? (float) $donor->distance_km : null;
                $donor->user->notify(new UrgentBloodRequestNotification($bloodRequest, $distance));
            }
        } catch (\Throwable $e) {
            Log::error('Notification dispatch failed', ['err' => $e->getMessage(), 'request_id' => $bloodRequest->id]);
        }

        return $donors->count();
    }

    public function updateStatus(Request $request, BloodRequest $bloodRequest)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'active', 'completed', 'cancelled'])],
        ]);

        $bloodRequest->update($data);
        return back()->with('success', 'Status updated.');
    }

    public function destroy(BloodRequest $bloodRequest)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless(
            $user->isAdmin() || ($user->isHospital() && $bloodRequest->hospital_id === $user->hospital?->id),
            403
        );

        $bloodRequest->delete();
        return back()->with('success', 'Request deleted.');
    }
}
