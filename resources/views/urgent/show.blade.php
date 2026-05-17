@extends('layouts.app')

@section('title', 'Urgent Case')

@section('content')
<div class="page-content-wrapper">
    <div class="main-card">
        <div class="image-banner" style="background:linear-gradient(135deg,var(--main-red),#600000);">
            <h2><i class="fas fa-exclamation-triangle"></i> Urgent</h2>
            <p>{{ $bloodRequest->blood_type }} NEEDED</p>
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;font-size:14px;">
                <div><strong>Hospital:</strong><br>{{ $bloodRequest->hospital->hospital_name ?? '—' }}</div>
                <div><strong>Location:</strong><br>{{ $bloodRequest->location ?? $bloodRequest->hospital->district ?? '—' }}</div>
                <div><strong>Blood Type:</strong><br><span style="color:var(--main-red);font-weight:bold;font-size:18px;">{{ $bloodRequest->blood_type }}</span></div>
                <div><strong>Units Needed:</strong><br>{{ $bloodRequest->quantity_required }}</div>
                @if ($bloodRequest->patient_name)
                    <div style="grid-column:1/-1;"><strong>Patient:</strong><br>{{ $bloodRequest->patient_name }}</div>
                @endif
                @if ($bloodRequest->patient_info)
                    <div style="grid-column:1/-1;"><strong>Details:</strong><br>{{ $bloodRequest->patient_info }}</div>
                @endif
            </div>

            @auth
                @if (auth()->user()->isDonor())
                    <a href="{{ route('donations.create', ['bloodRequest' => $bloodRequest->id]) }}" class="btn-primary-bl" style="margin-top:22px;">
                        <i class="fas fa-heart"></i> I Want To Help
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn-primary-bl" style="margin-top:22px;">Login to help</a>
            @endauth

            <div class="footer-text"><a href="{{ route('urgent.index') }}">← Back to urgent cases</a></div>
        </div>
    </div>
</div>
@endsection
