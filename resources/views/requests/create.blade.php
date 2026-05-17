@extends('layouts.app')

@section('title', 'Create Blood Request')

@section('content')
<div class="page-content-wrapper">
    <div class="main-card">
        <div class="image-banner">
            <h2>New Blood Request</h2>
            <p>HOSPITAL REQUEST</p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('requests.store') }}">
                @csrf
                <div class="section-title">Request Details</div>

                <div class="flex-row">
                    <select name="blood_type" required>
                        <option value="">Blood Type</option>
                        @foreach ($bloodTypes as $bt)
                            <option value="{{ $bt }}" @selected(old('blood_type') === $bt)>{{ $bt }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="quantity_required" placeholder="Units" min="1" max="50" value="{{ old('quantity_required', 1) }}" required>
                </div>

                <div class="flex-row">
                    <select name="location">
                        <option value="">Location</option>
                        @foreach ($locations as $loc)
                            <option value="{{ $loc }}" @selected(old('location') === $loc)>{{ $loc }}</option>
                        @endforeach
                    </select>
                    <select name="urgency_level" required>
                        <option value="low" @selected(old('urgency_level') === 'low')>Low</option>
                        <option value="medium" @selected(old('urgency_level', 'medium') === 'medium')>Medium</option>
                        <option value="high" @selected(old('urgency_level') === 'high')>High</option>
                    </select>
                </div>

                <input type="text" name="patient_name" placeholder="Patient Name (optional)" value="{{ old('patient_name') }}">
                <textarea name="patient_info" placeholder="Additional details..." rows="3">{{ old('patient_info') }}</textarea>

                <button type="submit">CREATE REQUEST</button>
            </form>
        </div>
    </div>
</div>
@endsection
