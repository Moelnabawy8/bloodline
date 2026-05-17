<?php

namespace App\Http\Controllers;

use App\Models\BloodRequest;
use App\Models\Campaign;
use App\Models\Donor;

class PageController extends Controller
{
    public function home()
    {
        return view('pages.home', [
            'donorsCount'     => Donor::count(),
            'urgentCount'     => BloodRequest::where('urgency_level', 'high')->whereIn('status', ['active', 'pending'])->count(),
            'campaignsCount'  => Campaign::where('status', 'upcoming')->count(),
            'latestUrgent'    => BloodRequest::with('hospital')->where('urgency_level', 'high')->whereIn('status', ['active', 'pending'])->latest()->take(3)->get(),
        ]);
    }

    public function about()
    {
        return view('pages.about');
    }

    public function faqs()
    {
        return view('pages.faqs');
    }
}
