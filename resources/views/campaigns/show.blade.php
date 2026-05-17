@extends('layouts.app')

@section('title', $campaign->title)

@section('content')
<div class="page-content-wrapper">
    <div class="main-card">
        @if ($campaign->image)
            <img src="{{ asset('storage/'.$campaign->image) }}" alt="{{ $campaign->title }}" style="width:100%;height:280px;object-fit:cover;">
        @else
            <div class="image-banner">
                <h2>{{ $campaign->title }}</h2>
            </div>
        @endif
        <div class="card-body">
            <h2 style="color:var(--main-red);margin-bottom:10px;">{{ $campaign->title }}</h2>
            <p style="color:#666;line-height:1.8;margin-bottom:20px;">{{ $campaign->description }}</p>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;font-size:14px;">
                <div><strong>Date:</strong> {{ $campaign->event_date->format('l, M d, Y') }}</div>
                <div><strong>Time:</strong> {{ $campaign->start_time }} — {{ $campaign->end_time }}</div>
                <div><strong>City:</strong> {{ $campaign->city }}</div>
                <div><strong>Location:</strong> {{ $campaign->location }}</div>
                @if ($campaign->organizer)
                    <div><strong>Organizer:</strong> {{ $campaign->organizer }}</div>
                @endif
                @if ($campaign->contact_phone)
                    <div><strong>Phone:</strong> {{ $campaign->contact_phone }}</div>
                @endif
            </div>

            <div class="footer-text">
                <a href="{{ route('campaigns.index') }}">← All campaigns</a>
            </div>
        </div>
    </div>
</div>
@endsection
