@extends('layouts.app')

@section('title', 'Donate Now')

@section('content')
<div class="page-content-wrapper">
    <div class="main-card">
        <div class="image-banner">
            <h2>Donate Blood</h2>
            <p>BE A HERO TODAY</p>
        </div>

        <div class="card-body">
            @if ($bloodRequest)
                <div class="alert alert-info">
                    <strong>You are responding to:</strong><br>
                    Hospital: {{ $bloodRequest->hospital->hospital_name ?? '—' }}<br>
                    Blood Type: <strong>{{ $bloodRequest->blood_type }}</strong>
                    @if ($bloodRequest->location) | Location: {{ $bloodRequest->location }} @endif
                </div>
            @endif

            <form method="POST" action="{{ route('donations.store') }}">
                @csrf
                @if ($bloodRequest)
                    <input type="hidden" name="blood_request_id" value="{{ $bloodRequest->id }}">
                @endif

                <p style="font-size:15px;color:#555;text-align:center;margin:12px 0 22px;">
                    By clicking <strong>Confirm Donation</strong>, you commit to going to the nearest blood bank within 48 hours.
                </p>

                <button type="submit"><i class="fas fa-heart"></i> Confirm Donation</button>
            </form>

            <div class="footer-text">
                <a href="{{ route('requests.index') }}">← Browse other requests</a>
            </div>
        </div>
    </div>
</div>
@endsection
