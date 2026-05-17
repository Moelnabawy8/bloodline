<?php

namespace Tests\Feature;

use App\Models\BloodCar;
use App\Models\BloodRequest;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::create([
            'full_name' => 'Test Admin',
            'email'     => 'admin@test.local',
            'password'  => Hash::make('admin123'),
            'role'      => 'admin',
        ]);
    }

    private function makeDonor(): User
    {
        $user = User::create([
            'full_name' => 'Test Donor',
            'email'     => 'donor@test.local',
            'password'  => Hash::make('secret123'),
            'role'      => 'donor',
        ]);
        Donor::create([
            'user_id'     => $user->id,
            'national_id' => '12345678901234',
            'age'         => 28,
            'gender'      => 'Male',
            'phone'       => '01000000000',
            'governorate' => 'cairo',
            'city'        => 'Cairo',
            'address'     => 'Some Street',
            'blood_type'  => 'O+',
            'weight'      => 75,
        ]);
        return $user;
    }

    private function makeHospitalUser(string $username = 'h1'): User
    {
        $user = User::create([
            'full_name' => 'Test Hospital',
            'email'     => "{$username}@test.local",
            'password'  => Hash::make('hosp123'),
            'role'      => 'hospital',
        ]);
        Hospital::create([
            'user_id'       => $user->id,
            'hospital_name' => "Test Hospital {$username}",
            'district'      => 'East Bank',
            'username'      => $username,
        ]);
        return $user;
    }

    // ── Public routes ─────────────────────────────────────
    public function test_public_pages_load(): void
    {
        foreach (['/', '/about', '/faqs', '/contact', '/login', '/register',
                  '/requests', '/urgent', '/campaigns', '/donors/top',
                  '/password/forgot'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_404_for_nonexistent_routes(): void
    {
        $this->get('/totally-nonexistent')->assertNotFound();
    }

    // ── Register / Login ──────────────────────────────────
    public function test_donor_can_register(): void
    {
        $response = $this->post('/register', [
            'fullname'              => 'Mona Tester',
            'email'                 => 'mona@test.local',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'national_id'           => '11122233344455',
            'age'                   => 25,
            'gender'                => 'Female',
            'phone'                 => '01200000000',
            'governorate'           => 'luxor',
            'city'                  => 'Luxor',
            'address'               => '1 Main St',
            'blood_type'            => 'A+',
            'weight'                => 65,
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('users', ['email' => 'mona@test.local', 'role' => 'donor']);
        $this->assertDatabaseHas('donors', ['national_id' => '11122233344455', 'blood_type' => 'A+']);
    }

    public function test_register_validation_rejects_bad_input(): void
    {
        $response = $this->post('/register', [
            'fullname'  => '',
            'email'     => 'not-an-email',
            'password'  => 'a',
        ]);
        $response->assertSessionHasErrors(['fullname', 'email', 'password']);
    }

    public function test_donor_login_redirects_to_dashboard(): void
    {
        $this->makeDonor();

        $response = $this->post('/login', [
            'email'    => 'donor@test.local',
            'password' => 'secret123',
            'role'     => 'donor',
        ]);

        $response->assertRedirect('/donor/dashboard');
        $this->assertAuthenticated();
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $this->makeAdmin();

        $response = $this->post('/login', [
            'email'    => 'admin@test.local',
            'password' => 'admin123',
            'role'     => 'admin',
        ]);

        $response->assertRedirect('/admin/dashboard');
    }

    public function test_hospital_login_redirects_to_hospital_dashboard(): void
    {
        $this->makeHospitalUser();

        $response = $this->post('/login', [
            'email'    => 'h1@test.local',
            'password' => 'hosp123',
            'role'     => 'hospital',
        ]);

        $response->assertRedirect('/hospital/dashboard');
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $this->makeDonor();

        $response = $this->post('/login', [
            'email'    => 'donor@test.local',
            'password' => 'wrong',
            'role'     => 'donor',
        ]);
        $response->assertRedirect();
        $this->assertGuest();
    }

    public function test_login_with_mismatched_role_fails(): void
    {
        $this->makeDonor();

        $response = $this->post('/login', [
            'email'    => 'donor@test.local',
            'password' => 'secret123',
            'role'     => 'admin',
        ]);
        $this->assertGuest();
    }

    public function test_logout_clears_session(): void
    {
        $user = $this->makeDonor();
        $this->actingAs($user);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    // ── Authorization (role middleware) ───────────────────
    public function test_donor_cannot_access_admin(): void
    {
        $user = $this->makeDonor();
        $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_donor_cannot_access_hospital(): void
    {
        $user = $this->makeDonor();
        $this->actingAs($user)->get('/hospital/dashboard')->assertForbidden();
    }

    public function test_hospital_cannot_access_admin(): void
    {
        $user = $this->makeHospitalUser();
        $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_guest_cannot_access_donor_dashboard(): void
    {
        $this->get('/donor/dashboard')->assertRedirect('/login');
    }

    // ── Donor flows ───────────────────────────────────────
    public function test_donor_dashboard_loads(): void
    {
        $user = $this->makeDonor();
        $this->actingAs($user)->get('/donor/dashboard')->assertOk()
            ->assertSee('Test Donor');
    }

    public function test_donor_can_create_donation(): void
    {
        $donor = $this->makeDonor();
        $hospital = $this->makeHospitalUser('h2');
        $req = BloodRequest::create([
            'hospital_id'       => $hospital->hospital->id,
            'blood_type'        => 'O+',
            'quantity_required' => 2,
            'urgency_level'     => 'high',
            'status'            => 'active',
        ]);

        $response = $this->actingAs($donor)->post('/donate', [
            'blood_request_id' => $req->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('donations', ['user_id' => $donor->id, 'blood_request_id' => $req->id]);
        $this->assertEquals('completed', $req->fresh()->status);
        $this->assertEquals(10, $donor->donor->fresh()->points);
    }

    public function test_donate_page_renders_with_request(): void
    {
        $donor = $this->makeDonor();
        $hospital = $this->makeHospitalUser('h3');
        $req = BloodRequest::create([
            'hospital_id'       => $hospital->hospital->id,
            'blood_type'        => 'O+',
            'quantity_required' => 1,
            'urgency_level'     => 'medium',
            'status'            => 'pending',
        ]);

        $this->actingAs($donor)->get("/donate/{$req->id}")->assertOk();
        $this->actingAs($donor)->get('/donate')->assertOk();
    }

    // ── Hospital flows ────────────────────────────────────
    public function test_hospital_can_create_blood_request(): void
    {
        $user = $this->makeHospitalUser('h4');

        $response = $this->actingAs($user)->post('/requests', [
            'blood_type'        => 'B+',
            'location'          => 'Luxor',
            'quantity_required' => 3,
            'urgency_level'     => 'high',
            'patient_name'      => 'Patient X',
            'patient_info'      => 'Critical case',
        ]);

        $response->assertRedirect('/hospital/dashboard');
        $this->assertDatabaseHas('blood_requests', ['blood_type' => 'B+', 'patient_name' => 'Patient X']);
    }

    public function test_hospital_create_form_loads(): void
    {
        $user = $this->makeHospitalUser('h5');
        $this->actingAs($user)->get('/requests/create')->assertOk();
    }

    public function test_hospital_dashboard_loads(): void
    {
        $user = $this->makeHospitalUser('h6');
        $this->actingAs($user)->get('/hospital/dashboard')->assertOk()
            ->assertSee('Test Hospital');
    }

    public function test_hospital_can_add_car(): void
    {
        $user = $this->makeHospitalUser('h7');
        $response = $this->actingAs($user)->post('/hospital/cars', [
            'vehicle_name' => 'Ambulance 01',
            'driver_name'  => 'Ahmed',
            'status'       => 'available',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('blood_cars', ['vehicle_name' => 'Ambulance 01']);
    }

    // ── Admin flows ───────────────────────────────────────
    public function test_admin_dashboard_loads(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
    }

    public function test_admin_can_create_hospital(): void
    {
        $admin = $this->makeAdmin();
        $response = $this->actingAs($admin)->post('/admin/hospitals', [
            'hospital_name' => 'New Hospital',
            'username'      => 'new_hosp',
            'district'      => 'West Bank',
            'email'         => 'new_hosp@test.local',
            'password'      => 'secret123',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'new_hosp@test.local', 'role' => 'hospital']);
        $this->assertDatabaseHas('hospitals', ['username' => 'new_hosp']);
    }

    // ── Profile ───────────────────────────────────────────
    public function test_profile_show_loads(): void
    {
        $user = $this->makeDonor();
        $this->actingAs($user)->get('/profile')->assertOk()->assertSee('Test Donor');
    }

    public function test_profile_update_works(): void
    {
        $user = $this->makeDonor();
        $response = $this->actingAs($user)->put('/profile', [
            'full_name'  => 'Updated Name',
            'email'      => 'donor@test.local',
            'phone'      => '01100000000',
            'blood_type' => 'A-',
            'city'       => 'Alexandria',
            'age'        => 30,
            'weight'     => 80,
        ]);
        $response->assertRedirect('/profile');
        $this->assertEquals('Updated Name', $user->fresh()->full_name);
        $this->assertEquals('01100000000', $user->donor->fresh()->phone);
    }

    public function test_password_change_requires_correct_current(): void
    {
        $user = $this->makeDonor();
        $response = $this->actingAs($user)->put('/profile', [
            'full_name'             => 'Test Donor',
            'email'                 => 'donor@test.local',
            'current_password'      => 'wrong',
            'new_password'          => 'newpass123',
            'new_password_confirmation' => 'newpass123',
        ]);
        $response->assertSessionHasErrors(['current_password']);
    }

    // ── Password reset ────────────────────────────────────
    public function test_password_reset_request_creates_token(): void
    {
        $user = $this->makeDonor();
        \Illuminate\Support\Facades\Mail::fake();

        $this->post('/password/forgot', ['email' => 'donor@test.local'])
            ->assertRedirect();

        $this->assertNotNull($user->fresh()->reset_token);
    }

    public function test_password_reset_with_valid_token_works(): void
    {
        $user = $this->makeDonor();
        $user->forceFill([
            'reset_token'        => 'abc123token',
            'reset_token_expire' => now()->addHour(),
        ])->save();

        $this->get('/password/reset/abc123token')->assertOk();

        $response = $this->post('/password/reset', [
            'token'                 => 'abc123token',
            'password'              => 'brandnew',
            'password_confirmation' => 'brandnew',
        ]);
        $response->assertRedirect('/login');
        $this->assertTrue(Hash::check('brandnew', $user->fresh()->password));
        $this->assertNull($user->fresh()->reset_token);
    }

    public function test_expired_reset_token_rejected(): void
    {
        $user = $this->makeDonor();
        $user->forceFill([
            'reset_token'        => 'expired_token',
            'reset_token_expire' => now()->subHour(),
        ])->save();

        $this->get('/password/reset/expired_token')->assertRedirect('/password/forgot');
    }

    // ── Contact ───────────────────────────────────────────
    public function test_contact_submission(): void
    {
        $response = $this->post('/contact', [
            'first_name' => 'A',
            'last_name'  => 'B',
            'email'      => 'a@b.com',
            'subject'    => 'Hello',
            'message'    => 'A test message.',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('contacts', ['email' => 'a@b.com', 'subject' => 'Hello']);
    }

    // ── Route ordering (regression) ───────────────────────
    public function test_requests_create_route_matches_correctly(): void
    {
        $user = $this->makeHospitalUser('h8');
        // /requests/create must NOT be routed to show (which would 404)
        $this->actingAs($user)->get('/requests/create')->assertOk();
    }

    public function test_campaigns_create_route_matches_correctly(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->get('/campaigns/create')->assertOk();
    }
}
