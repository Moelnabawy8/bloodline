<?php

namespace App\Http\Controllers;

use App\Models\BloodRequest;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DonationController extends Controller
{
    public function create(?BloodRequest $bloodRequest = null)
    {
        if ($bloodRequest && ! $bloodRequest->exists) {
            $bloodRequest = null;
        }

        $bloodRequest?->load('hospital');
        return view('donations.create', compact('bloodRequest'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'blood_request_id' => ['nullable', 'integer', 'exists:blood_requests,id'],
        ]);

        $donation = DB::transaction(function () use ($data) {
            $user = Auth::user();

            $donation = Donation::create([
                'user_id'          => $user->id,
                'blood_request_id' => $data['blood_request_id'] ?? null,
                'status'           => 'completed',
            ]);

            if ($user->donor) {
                $user->donor->increment('points', 10);
            }

            if (! empty($data['blood_request_id'])) {
                BloodRequest::where('id', $data['blood_request_id'])
                    ->update(['status' => 'completed']);
            }

            return $donation;
        });

        return redirect()->route('donations.confirm', $donation)
            ->with('success', 'Thank you for your donation ❤');
    }

    public function confirm(Donation $donation)
    {
        abort_unless($donation->user_id === Auth::id(), 403);

        $donation->load('bloodRequest.hospital');
        return view('donations.confirm', compact('donation'));
    }
}
