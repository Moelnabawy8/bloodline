<?php

namespace App\Services;

use App\Models\BloodRequest;
use App\Models\Donor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DonorMatcher
{
    /** Earth's radius in km — used by Haversine distance. */
    public const EARTH_RADIUS_KM = 6371;

    /** Max number of donors notified per request (sync mail safety). */
    public const MAX_NOTIFY = 50;

    /** Default geo-match radius when hospital coords are present (km). */
    public const DEFAULT_RADIUS_KM = 100;

    /** Pre-filter cap before applying distance calculation in PHP. */
    private const PREFILTER_LIMIT = 1000;

    /**
     * Find donors matching a blood request.
     *
     * Strategy (in order):
     *  1. Always require: same blood type + eligible (age 18-65, weight >= 50) + notifications on
     *  2. If hospital has coords → try Haversine geo-radius match
     *     If geo match returns 0 → fall through to text fallback (NEW)
     *  3. Fallback: governorate/city text match using request.location
     *  4. Last resort: same blood type only (capped)
     *
     * @return Collection<int, Donor>
     */
    public function matchFor(BloodRequest $request, ?int $radiusKm = null, ?int $limit = null): Collection
    {
        $radiusKm = $radiusKm ?? (int) config('bloodline.match_radius_km', self::DEFAULT_RADIUS_KM);
        $limit    = $limit    ?? self::MAX_NOTIFY;

        // 1) Geo branch — when hospital has coords
        if ($request->hospital && $request->hospital->hasCoordinates()) {
            $geoMatches = $this->geoMatch($request, $radiusKm, $limit);
            if ($geoMatches->isNotEmpty()) {
                return $geoMatches;
            }
            // fall through to text match if geo returned nothing
        }

        // 2) Text fallback — when request has a location
        if ($request->location) {
            $textMatches = $this->textMatch($request, $limit);
            if ($textMatches->isNotEmpty()) {
                return $textMatches;
            }
        }

        // 3) Last resort — same blood type only (helps tiny datasets / demo)
        return $this->baseQuery($request)->limit($limit)->get();
    }

    /** Donors with coordinates inside the radius around the hospital. */
    private function geoMatch(BloodRequest $request, int $radiusKm, int $limit): Collection
    {
        $hospital = $request->hospital;

        $candidates = $this->baseQuery($request)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->limit(self::PREFILTER_LIMIT)
            ->get();

        return $candidates
            ->map(function (Donor $d) use ($hospital) {
                $d->distance_km = self::distanceKm(
                    (float) $d->latitude, (float) $d->longitude,
                    (float) $hospital->latitude, (float) $hospital->longitude,
                );
                return $d;
            })
            ->filter(fn (Donor $d) => $d->distance_km <= $radiusKm)
            ->sortBy('distance_km')
            ->take($limit)
            ->values();
    }

    /** Donors whose governorate or city matches the request.location text. */
    private function textMatch(BloodRequest $request, int $limit): Collection
    {
        $loc = strtolower($request->location);

        return $this->baseQuery($request)
            ->where(function ($q) use ($loc) {
                $q->whereRaw('LOWER(governorate) = ?', [$loc])
                  ->orWhereRaw('LOWER(city) = ?', [$loc]);
            })
            ->limit($limit)
            ->get();
    }

    /** Shared base query — blood type, eligibility, notifications. */
    private function baseQuery(BloodRequest $request): Builder
    {
        return Donor::query()
            ->with('user')
            ->where('blood_type', $request->blood_type)
            ->where('notifications_enabled', true)
            ->whereBetween('age', [18, 65])
            ->where('weight', '>=', 50);
    }

    /**
     * Calculate distance in km between two coord pairs (Haversine, pure PHP).
     */
    public static function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $latFrom = deg2rad($lat1);
        $lngFrom = deg2rad($lng1);
        $latTo   = deg2rad($lat2);
        $lngTo   = deg2rad($lng2);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $a = sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lngDelta / 2) ** 2;

        return self::EARTH_RADIUS_KM * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
