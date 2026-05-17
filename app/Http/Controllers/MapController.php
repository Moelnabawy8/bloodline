<?php

namespace App\Http\Controllers;

use App\Models\Hospital;

class MapController extends Controller
{
    public function index()
    {
        $hospitals = Hospital::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->withCount([
                'bloodRequests as active_requests' => fn ($q) =>
                    $q->whereIn('status', ['pending', 'active']),
            ])
            ->get(['id', 'hospital_name', 'district', 'latitude', 'longitude']);

        $markers = $hospitals->map(fn ($h) => [
            'id'       => $h->id,
            'name'     => $h->hospital_name,
            'district' => $h->district,
            'lat'      => (float) $h->latitude,
            'lng'      => (float) $h->longitude,
            'active'   => $h->active_requests,
        ])->values();

        return view('pages.map', compact('markers'));
    }
}
