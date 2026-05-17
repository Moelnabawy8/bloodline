@extends('layouts.app')

@section('title', 'Request Details')

@section('content')
<div class="page-content-wrapper">
    <div class="main-card">
        <div class="image-banner">
            <h2>Blood Request #{{ $bloodRequest->id }}</h2>
            <p>{{ $bloodRequest->blood_type }}</p>
        </div>
        <div class="card-body">
            <div class="section-title">Request Information</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div><strong>Hospital:</strong><br>{{ $bloodRequest->hospital->hospital_name ?? '—' }}</div>
                <div><strong>District:</strong><br>{{ $bloodRequest->hospital->district ?? '—' }}</div>
                <div><strong>Blood Type:</strong><br><span style="color:var(--main-red);font-weight:bold;">{{ $bloodRequest->blood_type }}</span></div>
                <div><strong>Quantity:</strong><br>{{ $bloodRequest->quantity_required }} unit(s)</div>
                <div><strong>Urgency:</strong><br>{{ ucfirst($bloodRequest->urgency_level) }}</div>
                <div><strong>Status:</strong><br>{{ ucfirst($bloodRequest->status) }}</div>
                @if ($bloodRequest->patient_name)
                    <div style="grid-column:1/-1;"><strong>Patient:</strong><br>{{ $bloodRequest->patient_name }}</div>
                @endif
                @if ($bloodRequest->patient_info)
                    <div style="grid-column:1/-1;"><strong>Notes:</strong><br>{{ $bloodRequest->patient_info }}</div>
                @endif
                <div style="grid-column:1/-1;"><strong>Requested on:</strong> {{ $bloodRequest->created_at->format('M d, Y H:i') }}</div>
            </div>

            @auth
                @if (auth()->user()->isDonor() && in_array($bloodRequest->status, ['pending', 'active']))
                    <div style="margin-top:24px;">
                        <a href="{{ route('donations.create', ['bloodRequest' => $bloodRequest->id]) }}" class="btn-primary-bl">
                            <i class="fas fa-heart"></i> I Want To Donate
                        </a>
                    </div>
                @endif
            @else
                <div style="margin-top:24px;">
                    <a href="{{ route('login') }}" class="btn-primary-bl">Login to donate</a>
                </div>
            @endauth

            <div class="footer-text">
                <a href="{{ route('requests.index') }}">← Back to all requests</a>
            </div>
        </div>
    </div>
</div>
@endsection
