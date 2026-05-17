@extends('layouts.app')

@section('title', 'Donation Confirmed')

@section('content')
<div class="page-content-wrapper">
    <div class="main-card" style="max-width:480px;">
        <div class="card-body" style="text-align:center;padding:50px 36px;">
            <div style="font-size:70px;color:var(--main-green);margin-bottom:14px;"><i class="fas fa-check-circle"></i></div>
            <h2 style="color:var(--main-red);margin-bottom:10px;">Thank You!</h2>
            <p style="color:#666;font-size:15px;margin-bottom:20px;">Your donation has been recorded ❤️</p>

            <div style="text-align:left;background:#fdfdfd;padding:18px;border-radius:10px;border:1px solid #eee;font-size:14px;">
                <p><strong>Donation ID:</strong> #{{ $donation->id }}</p>
                <p><strong>Date:</strong> {{ $donation->donation_date->format('M d, Y H:i') }}</p>
                @if ($donation->bloodRequest)
                    <p><strong>Hospital:</strong> {{ $donation->bloodRequest->hospital->hospital_name ?? '—' }}</p>
                    <p><strong>Blood Type:</strong> {{ $donation->bloodRequest->blood_type }}</p>
                @endif
                <p style="margin:0;"><strong>Status:</strong> <span style="color:var(--main-green);font-weight:bold;">{{ $donation->status }}</span></p>
            </div>

            <a href="{{ route('donor.dashboard') }}" class="btn-primary-bl" style="margin-top:22px;">Back to Dashboard</a>
        </div>
    </div>
</div>
@endsection
