@extends('layouts.app')

@section('title', 'Add Campaign')

@section('content')
<div class="page-content-wrapper">
    <div class="main-card">
        <div class="image-banner" style="height:140px;">
            <h2>New Campaign</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('campaigns.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="text" name="title" placeholder="Campaign Title" value="{{ old('title') }}" required>
                <textarea name="description" placeholder="Description..." rows="4" required>{{ old('description') }}</textarea>
                <div class="flex-row">
                    <input type="text" name="city" placeholder="City" value="{{ old('city') }}" required>
                    <input type="text" name="location" placeholder="Location / Address" value="{{ old('location') }}" required>
                </div>
                <input type="date" name="event_date" value="{{ old('event_date') }}" required>
                <div class="flex-row">
                    <input type="time" name="start_time" value="{{ old('start_time') }}" required>
                    <input type="time" name="end_time" value="{{ old('end_time') }}" required>
                </div>
                <div class="flex-row">
                    <input type="text" name="organizer" placeholder="Organizer (optional)" value="{{ old('organizer') }}">
                    <input type="tel" name="contact_phone" placeholder="Phone (optional)" value="{{ old('contact_phone') }}">
                </div>
                <label style="font-size:13px;color:#666;display:block;margin:8px 0 4px;">Campaign image (optional)</label>
                <input type="file" name="image" accept="image/*" style="padding:8px;">

                <button type="submit">CREATE CAMPAIGN</button>
            </form>
        </div>
    </div>
</div>
@endsection
