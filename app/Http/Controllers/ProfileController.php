<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('donor');

        $totalDonations = $user->donations()->count();
        $lastDonation = $user->donations()->latest('donation_date')->first();
        $history = $user->donations()->with('bloodRequest.hospital')->latest('donation_date')->take(10)->get();

        return view('profile.show', compact('user', 'totalDonations', 'lastDonation', 'history'));
    }

    public function edit()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('donor');
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $data = $request->validate([
            'full_name'             => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'                 => ['nullable', 'string', 'max:20'],
            'blood_type'            => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
            'city'                  => ['nullable', 'string', 'max:100'],
            'age'                   => ['nullable', 'integer', 'between:18,65'],
            'weight'                => ['nullable', 'integer', 'min:50', 'max:200'],
            'latitude'              => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'             => ['nullable', 'numeric', 'between:-180,180'],
            'notifications_enabled' => ['nullable', 'boolean'],
            'current_password'      => ['nullable', 'string'],
            'new_password'          => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        DB::transaction(function () use ($user, $data) {
            $user->update([
                'full_name' => $data['full_name'],
                'email'     => strtolower($data['email']),
            ]);

            if ($user->donor) {
                $donorUpdates = array_filter([
                    'phone'      => $data['phone'] ?? null,
                    'blood_type' => $data['blood_type'] ?? null,
                    'city'       => $data['city'] ?? null,
                    'age'        => $data['age'] ?? null,
                    'weight'     => $data['weight'] ?? null,
                    'latitude'   => $data['latitude'] ?? null,
                    'longitude'  => $data['longitude'] ?? null,
                ], fn ($v) => ! is_null($v));

                // Boolean toggle (false is a valid value, so handle separately)
                if (array_key_exists('notifications_enabled', $data)) {
                    $donorUpdates['notifications_enabled'] = (bool) $data['notifications_enabled'];
                }

                $user->donor->update($donorUpdates);
            }

            if (! empty($data['new_password'])) {
                if (! Hash::check($data['current_password'] ?? '', $user->password)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'current_password' => 'Current password is incorrect.',
                    ]);
                }
                $user->update(['password' => $data['new_password']]);
            }
        });

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully.');
    }
}
