<?php

namespace App\Http\Controllers;

use App\Models\Donor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            'role'     => ['required', Rule::in(['donor', 'hospital', 'admin'])],
        ]);

        $user = User::where('email', strtolower($data['email']))
            ->where('role', $data['role'])
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return back()
                ->withInput($request->only('email', 'role'))
                ->with('error', 'Invalid email, password, or role.');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return $this->redirectByRole($user->role);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'fullname'    => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'unique:users,email'],
            'password'    => ['required', 'string', 'min:6', 'confirmed'],
            'national_id' => ['required', 'digits:14', 'unique:donors,national_id'],
            'age'         => ['required', 'integer', 'between:18,65'],
            'gender'      => ['required', Rule::in(['Male', 'Female'])],
            'phone'       => ['required', 'string', 'max:20'],
            'governorate' => ['required', Rule::in(['cairo', 'alexandria', 'giza', 'luxor', 'aswan', 'mansoura', 'qena', 'sohag'])],
            'city'        => ['nullable', 'string', 'max:100'],
            'address'     => ['required', 'string', 'max:255'],
            'latitude'    => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'   => ['nullable', 'numeric', 'between:-180,180'],
            'blood_type'  => ['required', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
            'weight'      => ['required', 'integer', 'min:50', 'max:200'],
        ]);

        DB::transaction(function () use ($data, &$user) {
            $user = User::create([
                'full_name' => $data['fullname'],
                'email'     => strtolower($data['email']),
                'password'  => $data['password'],
                'role'      => 'donor',
            ]);

            Donor::create([
                'user_id'     => $user->id,
                'national_id' => $data['national_id'],
                'age'         => $data['age'],
                'gender'      => $data['gender'],
                'phone'       => $data['phone'],
                'governorate' => $data['governorate'],
                'city'        => $data['city'] ?? null,
                'address'     => $data['address'],
                'latitude'    => $data['latitude']  ?? null,
                'longitude'   => $data['longitude'] ?? null,
                'blood_type'  => $data['blood_type'],
                'weight'      => $data['weight'],
            ]);
        });

        return redirect()->route('login')->with('success', 'Account created. You can now log in.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }

    private function redirectByRole(string $role)
    {
        return match ($role) {
            'admin'    => redirect()->route('admin.dashboard'),
            'hospital' => redirect()->route('hospital.dashboard'),
            default    => redirect()->route('donor.dashboard'),
        };
    }
}
