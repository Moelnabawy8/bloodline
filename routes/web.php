<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BloodRequestController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\DonorController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UrgentController;
use Illuminate\Support\Facades\Route;

// ── Public Pages ──────────────────────────────────────────
Route::get('/',         [PageController::class, 'home'])->name('home');
Route::get('/about',    [PageController::class, 'about'])->name('about');
Route::get('/faqs',     [PageController::class, 'faqs'])->name('faqs');

Route::get('/contact',  [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.send');

// Blood requests (public listing + detail by numeric id)
Route::get('/requests', [BloodRequestController::class, 'index'])->name('requests.index');
Route::get('/requests/{request}', [BloodRequestController::class, 'show'])
    ->where('request', '[0-9]+')
    ->name('requests.show');

// Urgent (public)
Route::get('/urgent', [UrgentController::class, 'index'])->name('urgent.index');
Route::get('/urgent/{bloodRequest}', [UrgentController::class, 'show'])
    ->where('bloodRequest', '[0-9]+')
    ->name('urgent.show');

// Campaigns (public listing + detail by numeric id)
Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'])
    ->where('campaign', '[0-9]+')
    ->name('campaigns.show');

Route::get('/donors/top', [DonorController::class, 'top'])->name('donors.top');

Route::get('/map', [MapController::class, 'index'])->name('map');

// ── Guest Routes ──────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class, 'login']);
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/password/forgot',        [PasswordResetController::class, 'showForgot'])->name('password.request');
    Route::post('/password/forgot',       [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/password/reset/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/password/reset',        [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ── Authenticated Routes ──────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile',      [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',      [ProfileController::class, 'update'])->name('profile.update');

    // Donor area
    Route::middleware('role:donor')->group(function () {
        Route::get('/donor/dashboard', [DonorController::class, 'dashboard'])->name('donor.dashboard');

        Route::get('/donate/{bloodRequest?}', [DonationController::class, 'create'])
            ->where('bloodRequest', '[0-9]+')
            ->name('donations.create');
        Route::post('/donate', [DonationController::class, 'store'])->name('donations.store');
        Route::get('/donations/{donation}/confirm', [DonationController::class, 'confirm'])
            ->where('donation', '[0-9]+')
            ->name('donations.confirm');
    });

    // Hospital area
    Route::middleware('role:hospital')->group(function () {
        Route::get('/hospital/dashboard', [HospitalController::class, 'dashboard'])->name('hospital.dashboard');
        Route::get('/hospital/cars',      [HospitalController::class, 'cars'])->name('hospital.cars');
        Route::post('/hospital/cars',     [HospitalController::class, 'storeCar'])->name('hospital.cars.store');
        Route::patch('/hospital/cars/{car}/status', [HospitalController::class, 'updateCarStatus'])
            ->where('car', '[0-9]+')
            ->name('hospital.cars.status');
        Route::delete('/hospital/cars/{car}', [HospitalController::class, 'destroyCar'])
            ->where('car', '[0-9]+')
            ->name('hospital.cars.destroy');

        // Hospital creates blood requests (must be defined; uses numeric constraint on public show to avoid clash)
        Route::get('/requests/create', [BloodRequestController::class, 'create'])->name('requests.create');
        Route::post('/requests',       [BloodRequestController::class, 'store'])->name('requests.store');
        Route::patch('/requests/{bloodRequest}/status', [BloodRequestController::class, 'updateStatus'])
            ->where('bloodRequest', '[0-9]+')
            ->name('requests.status');
        Route::delete('/requests/{bloodRequest}', [BloodRequestController::class, 'destroy'])
            ->where('bloodRequest', '[0-9]+')
            ->name('requests.destroy');
    });

    // Admin area
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard',          [AdminController::class, 'dashboard'])->name('dashboard');
        Route::post('/hospitals',         [AdminController::class, 'storeHospital'])->name('hospitals.store');
        Route::delete('/hospitals/{hospital}', [AdminController::class, 'destroyHospital'])
            ->where('hospital', '[0-9]+')
            ->name('hospitals.destroy');
    });

    // Campaign creation (hospital or admin)
    Route::middleware('role:hospital,admin')->group(function () {
        Route::get('/campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
        Route::post('/campaigns',       [CampaignController::class, 'store'])->name('campaigns.store');
    });
});
