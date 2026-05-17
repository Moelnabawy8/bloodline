<?php

use Illuminate\Support\Facades\Route;

// API endpoints can be added here in the future (e.g. for a mobile app).
// All web functionality is currently handled via routes/web.php using Laravel
// sessions and Blade views.

Route::get('/health', fn () => response()->json(['status' => 'ok']));
