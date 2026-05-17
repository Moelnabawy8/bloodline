@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
<div class="page-content-wrapper">
    <div class="main-card">
        <div class="image-banner" style="height:130px;">
            <h2>Contact Us</h2>
        </div>
        <div class="card-body">
            <p style="color:#666;font-size:14px;margin-bottom:20px;">Have a question or feedback? Send us a message.</p>
            <form method="POST" action="{{ route('contact.send') }}">
                @csrf
                <div class="flex-row">
                    <input type="text" name="first_name" placeholder="First Name" value="{{ old('first_name') }}" required>
                    <input type="text" name="last_name" placeholder="Last Name" value="{{ old('last_name') }}" required>
                </div>
                <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
                <input type="text" name="subject" placeholder="Subject" value="{{ old('subject') }}" required>
                <textarea name="message" placeholder="Your message..." rows="5" required>{{ old('message') }}</textarea>
                <button type="submit">SEND MESSAGE</button>
            </form>
        </div>
    </div>
</div>
@endsection
