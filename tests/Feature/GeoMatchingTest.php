<?php

namespace Tests\Feature;

use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\Hospital;
use App\Models\User;
use App\Notifications\UrgentBloodRequestNotification;
use App\Services\DonorMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class GeoMatchingTest extends TestCase
{
    use RefreshDatabase;

    private function makeDonorUser(
        string $email,
        string $blood = 'O+',
        ?float $lat = null,
        ?float $lng = null,
        string $gov = 'cairo',
        ?string $city = 'Cairo',
        bool $notifications = true,
        int $age = 28,
        int $weight = 75,
    ): User {
        $user = User::create([
            'full_name' => "Donor {$email}",
            'email'     => $email,
            'password'  => Hash::make('secret123'),
            'role'      => 'donor',
        ]);
        Donor::create([
            'user_id'               => $user->id,
            'national_id'           => str_pad((string) random_int(1, 99999999999999), 14, '0', STR_PAD_LEFT),
            'age'                   => $age,
            'gender'                => 'Male',
            'phone'                 => '01000000000',
            'governorate'           => $gov,
            'city'                  => $city,
            'address'               => '1 Test St',
            'blood_type'            => $blood,
            'weight'                => $weight,
            'latitude'              => $lat,
            'longitude'             => $lng,
            'notifications_enabled' => $notifications,
        ]);
        return $user->fresh('donor');
    }

    private function makeHospital(?float $lat = null, ?float $lng = null, string $district = 'East Bank'): Hospital
    {
        $u = User::create([
            'full_name' => 'Hosp '.uniqid(),
            'email'     => 'h_'.uniqid().'@t.local',
            'password'  => Hash::make('hosp'),
            'role'      => 'hospital',
        ]);
        return Hospital::create([
            'user_id'       => $u->id,
            'hospital_name' => 'Test Hospital',
            'district'      => $district,
            'username'      => 'h_'.uniqid(),
            'latitude'      => $lat,
            'longitude'     => $lng,
        ]);
    }

    // ── Distance utility ──────────────────────────────────
    public function test_haversine_distance_is_correct(): void
    {
        // Cairo to Luxor ≈ 503 km
        $d = DonorMatcher::distanceKm(30.0444, 31.2357, 25.6872, 32.6396);
        $this->assertEqualsWithDelta(503, $d, 20);
    }

    public function test_haversine_zero_for_same_point(): void
    {
        $d = DonorMatcher::distanceKm(30.0, 31.0, 30.0, 31.0);
        $this->assertEqualsWithDelta(0, $d, 0.001);
    }

    // ── Matching logic ────────────────────────────────────
    public function test_matcher_filters_by_blood_type(): void
    {
        $hospital = $this->makeHospital();
        $matchD   = $this->makeDonorUser('a@t.local', 'A+');
        $other    = $this->makeDonorUser('b@t.local', 'O+');

        $req = BloodRequest::create([
            'hospital_id'       => $hospital->id,
            'blood_type'        => 'A+',
            'quantity_required' => 1,
            'urgency_level'     => 'high',
            'status'            => 'active',
        ]);

        $matched = app(DonorMatcher::class)->matchFor($req->load('hospital'));
        $this->assertCount(1, $matched);
        $this->assertEquals($matchD->donor->id, $matched->first()->id);
    }

    public function test_matcher_excludes_ineligible_donors(): void
    {
        $hospital = $this->makeHospital();
        $tooYoung = $this->makeDonorUser('young@t.local', 'O+', age: 16);
        $tooLight = $this->makeDonorUser('light@t.local', 'O+', weight: 45);
        $ok       = $this->makeDonorUser('ok@t.local', 'O+');

        $req = BloodRequest::create([
            'hospital_id'       => $hospital->id,
            'blood_type'        => 'O+',
            'quantity_required' => 1,
            'urgency_level'     => 'high',
            'status'            => 'active',
        ]);

        $matched = app(DonorMatcher::class)->matchFor($req->load('hospital'));
        $emails = $matched->pluck('user.email')->all();
        $this->assertContains('ok@t.local', $emails);
        $this->assertNotContains('young@t.local', $emails);
        $this->assertNotContains('light@t.local', $emails);
    }

    public function test_matcher_excludes_donors_with_notifications_off(): void
    {
        $hospital = $this->makeHospital();
        $optOut   = $this->makeDonorUser('out@t.local', 'O+', notifications: false);
        $optIn    = $this->makeDonorUser('in@t.local',  'O+', notifications: true);

        $req = BloodRequest::create([
            'hospital_id'       => $hospital->id,
            'blood_type'        => 'O+',
            'quantity_required' => 1,
            'urgency_level'     => 'high',
            'status'            => 'active',
        ]);

        $matched = app(DonorMatcher::class)->matchFor($req->load('hospital'));
        $emails = $matched->pluck('user.email')->all();
        $this->assertContains('in@t.local',  $emails);
        $this->assertNotContains('out@t.local', $emails);
    }

    public function test_matcher_uses_geo_radius_when_hospital_has_coords(): void
    {
        // Hospital in Cairo
        $hospital = $this->makeHospital(30.0444, 31.2357);
        // Donor in Cairo (close)
        $nearby = $this->makeDonorUser('near@t.local',  'O+', 30.05,   31.24);
        // Donor in Luxor (~500 km — outside default 50km radius)
        $far    = $this->makeDonorUser('far@t.local',   'O+', 25.6872, 32.6396);
        // Donor without coords (when geo matching active, should be excluded)
        $noCoords = $this->makeDonorUser('nocoords@t.local', 'O+');

        $req = BloodRequest::create([
            'hospital_id'       => $hospital->id,
            'blood_type'        => 'O+',
            'quantity_required' => 1,
            'urgency_level'     => 'high',
            'status'            => 'active',
        ]);

        $matched = app(DonorMatcher::class)->matchFor($req->load('hospital'));
        $emails = $matched->pluck('user.email')->all();
        $this->assertContains('near@t.local', $emails);
        $this->assertNotContains('far@t.local', $emails);
        $this->assertNotContains('nocoords@t.local', $emails);
    }

    public function test_matcher_falls_back_to_governorate_when_hospital_has_no_coords(): void
    {
        $hospital = $this->makeHospital(); // no coords
        $cairoDonor = $this->makeDonorUser('c@t.local', 'O+', gov: 'cairo', city: 'Cairo');
        $luxorDonor = $this->makeDonorUser('l@t.local', 'O+', gov: 'luxor', city: 'Luxor');

        $req = BloodRequest::create([
            'hospital_id'       => $hospital->id,
            'blood_type'        => 'O+',
            'location'          => 'Cairo',
            'quantity_required' => 1,
            'urgency_level'     => 'high',
            'status'            => 'active',
        ]);

        $matched = app(DonorMatcher::class)->matchFor($req->load('hospital'));
        $emails = $matched->pluck('user.email')->all();
        $this->assertContains('c@t.local', $emails);
        $this->assertNotContains('l@t.local', $emails);
    }

    // ── Fallback behaviour ────────────────────────────────
    public function test_matcher_falls_back_to_text_when_geo_returns_zero(): void
    {
        // Hospital in Cairo with coords
        $hospital = $this->makeHospital(30.0444, 31.2357);

        // Donor in Alexandria governorate, no coords, far from Cairo
        $far = $this->makeDonorUser('far@t.local', 'O+', gov: 'alexandria', city: 'Alexandria');

        // Request explicitly references Alexandria (text fallback target)
        $req = BloodRequest::create([
            'hospital_id'       => $hospital->id,
            'blood_type'        => 'O+',
            'location'          => 'Alexandria',
            'quantity_required' => 1,
            'urgency_level'     => 'high',
            'status'            => 'active',
        ]);

        // Default radius 100km — Cairo→Alexandria is ~220km, so geo branch returns 0
        // Fallback: governorate text "alexandria" matches donor.governorate
        $matched = app(DonorMatcher::class)->matchFor($req->load('hospital'));
        $emails = $matched->pluck('user.email')->all();
        $this->assertContains('far@t.local', $emails, 'Text fallback should kick in when geo returns 0');
    }

    public function test_matcher_last_resort_returns_any_matching_blood_type(): void
    {
        // Hospital with no coords, request with no location
        $hospital = $this->makeHospital();
        $donor    = $this->makeDonorUser('any@t.local', 'O+', gov: 'cairo');

        $req = BloodRequest::create([
            'hospital_id'       => $hospital->id,
            'blood_type'        => 'O+',
            'quantity_required' => 1,
            'urgency_level'     => 'low',
            'status'            => 'pending',
        ]);

        $matched = app(DonorMatcher::class)->matchFor($req->load('hospital'));
        $this->assertNotEmpty($matched, 'Last-resort branch should return blood-type-only matches');
    }

    public function test_radius_can_be_overridden_per_call(): void
    {
        $hospital = $this->makeHospital(30.0, 31.0);
        $nearby   = $this->makeDonorUser('n2@t.local', 'O+', 30.05, 31.05); // ~7km
        $faraway  = $this->makeDonorUser('f2@t.local', 'O+', 30.5, 31.5);   // ~75km

        $req = BloodRequest::create([
            'hospital_id'       => $hospital->id,
            'blood_type'        => 'O+',
            'quantity_required' => 1,
            'urgency_level'     => 'high',
            'status'            => 'active',
        ]);

        $tight = app(DonorMatcher::class)->matchFor($req->load('hospital'), radiusKm: 10);
        $this->assertCount(1, $tight);

        $loose = app(DonorMatcher::class)->matchFor($req->load('hospital'), radiusKm: 200);
        $this->assertCount(2, $loose);
    }

    public function test_default_radius_comes_from_config(): void
    {
        $this->assertSame(100, config('bloodline.match_radius_km'));
    }

    // ── Notification dispatch ─────────────────────────────
    public function test_notification_sent_when_hospital_creates_request(): void
    {
        Notification::fake();

        $hospUser = User::create([
            'full_name' => 'Hosp Admin',
            'email'     => 'h@t.local',
            'password'  => Hash::make('hosp'),
            'role'      => 'hospital',
        ]);
        Hospital::create([
            'user_id'       => $hospUser->id,
            'hospital_name' => 'Test Hosp',
            'district'      => 'East Bank',
            'username'      => 'th',
            'latitude'      => 30.0444,
            'longitude'     => 31.2357,
        ]);

        // Matching donor nearby
        $near = $this->makeDonorUser('n@t.local', 'O+', 30.05, 31.24);
        // Wrong blood type
        $wrong = $this->makeDonorUser('w@t.local', 'A+', 30.05, 31.24);

        $this->actingAs($hospUser)->post('/requests', [
            'blood_type'        => 'O+',
            'quantity_required' => 1,
            'urgency_level'     => 'high',
        ])->assertRedirect('/hospital/dashboard');

        Notification::assertSentTo($near, UrgentBloodRequestNotification::class);
        Notification::assertNotSentTo($wrong, UrgentBloodRequestNotification::class);
    }

    public function test_notification_includes_distance_when_available(): void
    {
        Notification::fake();

        $hospUser = User::create([
            'full_name' => 'H', 'email' => 'h2@t.local',
            'password' => Hash::make('x'), 'role' => 'hospital',
        ]);
        Hospital::create([
            'user_id' => $hospUser->id, 'hospital_name' => 'H', 'district' => 'D',
            'username' => 'h2', 'latitude' => 30.0, 'longitude' => 31.0,
        ]);

        $donor = $this->makeDonorUser('d@t.local', 'O+', 30.05, 31.05);

        $this->actingAs($hospUser)->post('/requests', [
            'blood_type'        => 'O+',
            'quantity_required' => 1,
            'urgency_level'     => 'medium',
        ]);

        Notification::assertSentTo($donor, UrgentBloodRequestNotification::class, function ($notif) {
            return $notif->distanceKm !== null && $notif->distanceKm < 20;
        });
    }

    // ── Map ───────────────────────────────────────────────
    public function test_map_page_loads_for_guests(): void
    {
        $this->makeHospital(30.0, 31.0);
        $this->get('/map')->assertOk()->assertSee('Hospitals');
    }

    public function test_map_only_shows_hospitals_with_coordinates(): void
    {
        $withCoords = $this->makeHospital(30.0, 31.0, 'WithCoords');
        $withoutCoords = $this->makeHospital(); // no coords
        $withoutCoords->update(['hospital_name' => 'NoCoords Hospital']);

        $response = $this->get('/map');
        $response->assertOk();
        $response->assertSee('WithCoords');
        $response->assertDontSee('NoCoords Hospital');
    }

    // ── Recommended for you ───────────────────────────────
    public function test_donor_dashboard_shows_recommended_section(): void
    {
        $user = $this->makeDonorUser('rec@t.local', 'O+', 30.05, 31.05);
        $hospital = $this->makeHospital(30.0, 31.0);

        BloodRequest::create([
            'hospital_id'       => $hospital->id,
            'blood_type'        => 'O+',
            'quantity_required' => 1,
            'urgency_level'     => 'high',
            'status'            => 'active',
        ]);

        $response = $this->actingAs($user)->get('/donor/dashboard');
        $response->assertOk();
        $response->assertSee('Recommended for You');
    }

    // ── Notifications toggle in profile ───────────────────
    public function test_donor_can_disable_notifications(): void
    {
        $user = $this->makeDonorUser('toggle@t.local', 'O+');
        $this->assertTrue($user->donor->notifications_enabled);

        $this->actingAs($user)->put('/profile', [
            'full_name'             => $user->full_name,
            'email'                 => $user->email,
            'notifications_enabled' => '0',
        ])->assertRedirect('/profile');

        $this->assertFalse($user->donor->fresh()->notifications_enabled);
    }

    // ── Geo fields persisted via register ─────────────────
    public function test_register_persists_coordinates(): void
    {
        $this->post('/register', [
            'fullname'              => 'Geo Tester',
            'email'                 => 'geo@t.local',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'national_id'           => '12345678901234',
            'age'                   => 25, 'gender' => 'Male',
            'phone'                 => '01000000000',
            'governorate'           => 'cairo', 'city' => 'Cairo',
            'address'               => '1 Test St',
            'blood_type'            => 'O+', 'weight' => 70,
            'latitude'              => 30.0444,
            'longitude'             => 31.2357,
        ])->assertRedirect('/login');

        $donor = Donor::whereHas('user', fn ($q) => $q->where('email', 'geo@t.local'))->first();
        $this->assertNotNull($donor);
        $this->assertEqualsWithDelta(30.0444, $donor->latitude,  0.0001);
        $this->assertEqualsWithDelta(31.2357, $donor->longitude, 0.0001);
    }
}
