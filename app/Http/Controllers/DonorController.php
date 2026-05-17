<?php

namespace App\Http\Controllers;

use App\Models\BloodRequest;
use App\Models\User;
use App\Services\DonorMatcher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DonorController extends Controller
{
    public function dashboard()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('donor');

        $totalDonations = $user->donations()->count();
        $lastDonation   = $user->donations()->latest('donation_date')->first();

        $eligibility = ($user->donor && $user->donor->isEligible()) ? 'Ready' : 'Not Ready';

        $rank = match (true) {
            $totalDonations >= 10 => 'Gold',
            $totalDonations >= 5  => 'Silver',
            default               => 'Bronze',
        };

        $recentRequests = BloodRequest::with('hospital')
            ->whereIn('status', ['pending', 'active'])
            ->latest()
            ->take(5)
            ->get();

        $history = $user->donations()
            ->with('bloodRequest.hospital')
            ->latest('donation_date')
            ->take(10)
            ->get();

        $recommended = $this->recommendedRequestsFor($user);

        return view('donor.dashboard', compact(
            'user', 'totalDonations', 'lastDonation', 'eligibility', 'rank',
            'recentRequests', 'history', 'recommended'
        ));
    }

    /**
     * Recommend open requests matching the donor's blood type,
     * sorted by distance (when coords available) else by recency.
     */
    private function recommendedRequestsFor(\App\Models\User $user): \Illuminate\Support\Collection
    {
        $donor = $user->donor;
        if (! $donor || ! $donor->blood_type) {
            return collect();
        }

        $base = BloodRequest::query()
            ->with('hospital')
            ->where('blood_type', $donor->blood_type)
            ->whereIn('status', ['pending', 'active'])
            ->latest()
            ->take(20)
            ->get();

        if (! $donor->hasCoordinates()) {
            return $base->take(5)->map(fn ($r) => tap($r, fn ($x) => $x->distance_km = null));
        }

        // Annotate distance + sort by nearest first
        return $base
            ->map(function ($req) use ($donor) {
                $hospital = $req->hospital;
                if ($hospital && $hospital->hasCoordinates()) {
                    $req->distance_km = DonorMatcher::distanceKm(
                        $donor->latitude, $donor->longitude,
                        $hospital->latitude, $hospital->longitude
                    );
                } else {
                    $req->distance_km = null;
                }
                return $req;
            })
            ->sortBy(fn ($r) => $r->distance_km ?? PHP_INT_MAX)
            ->take(5)
            ->values();
    }

    public function top()
    {
        $donors = User::query()
            ->where('users.role', 'donor')
            ->leftJoin('donors', 'donors.user_id', '=', 'users.id')
            ->leftJoin('donations', 'donations.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.full_name',
                'donors.blood_type',
                'donors.city',
                DB::raw('COUNT(donations.id) AS donations_count'),
            )
            ->groupBy('users.id', 'users.full_name', 'donors.blood_type', 'donors.city')
            ->orderByDesc('donations_count')
            ->take(50)
            ->get();

        $top1 = $donors->get(0);
        $top2 = $donors->get(1);
        $top3 = $donors->get(2);
        $rest = $donors->slice(3);

        return view('donor.top', compact('top1', 'top2', 'top3', 'rest'));
    }
}
