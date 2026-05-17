@extends('layouts.app')

@section('title', 'FAQs')

@section('content')
<div class="container-bl py-4" style="max-width:800px;">
    <h1 style="color:var(--main-red);font-weight:bold;text-align:center;margin-bottom:24px;">Frequently Asked Questions</h1>

    @foreach ([
        'Who can donate blood?' => 'Anyone aged 18–60, weighing at least 50 kg, in good health, and who hasn\'t donated in the last 3 months can donate.',
        'How often can I donate?' => 'Every 3 months for whole blood donations.',
        'How does BloodLine work?' => 'Register as a donor, browse urgent requests from hospitals, and confirm your donation. You can earn reward points for every donation.',
        'Is my personal information safe?' => 'Yes. We store passwords using bcrypt encryption and never share your contact details without consent.',
        'Can hospitals register?' => 'Hospital accounts are created by the BloodLine admin team after verification. Contact us if you represent a hospital.',
    ] as $q => $a)
        <div style="background:var(--card-bg);padding:22px;border-radius:12px;margin-bottom:14px;box-shadow:0 4px 12px rgba(0,0,0,0.04);">
            <h4 style="color:var(--main-red);font-weight:bold;margin-bottom:8px;font-size:17px;">{{ $q }}</h4>
            <p style="color:#555;margin:0;line-height:1.7;">{{ $a }}</p>
        </div>
    @endforeach
</div>
@endsection
