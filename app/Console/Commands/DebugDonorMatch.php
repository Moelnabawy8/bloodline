<?php

namespace App\Console\Commands;

use App\Models\BloodRequest;
use App\Models\Donor;
use App\Services\DonorMatcher;
use Illuminate\Console\Command;

class DebugDonorMatch extends Command
{
    protected $signature = 'donors:debug-match {request_id : Blood request ID}
                                                 {--radius= : Override radius in km}';

    protected $description = 'Diagnose why a blood request matches (or does not match) any donors.';

    public function handle(DonorMatcher $matcher): int
    {
        $id = (int) $this->argument('request_id');
        $request = BloodRequest::with('hospital')->find($id);

        if (! $request) {
            $this->error("Blood request #{$id} not found.");
            return self::FAILURE;
        }

        $this->line('');
        $this->info("=== Blood Request #{$request->id} ===");
        $this->line("Blood type      : {$request->blood_type}");
        $this->line("Location field  : " . ($request->location ?? 'NULL'));
        $this->line("Urgency / status: {$request->urgency_level} / {$request->status}");

        $h = $request->hospital;
        if ($h) {
            $this->info("\n=== Hospital ===");
            $this->line("Name            : {$h->hospital_name}");
            $this->line("District        : {$h->district}");
            if ($h->hasCoordinates()) {
                $this->line("Coordinates     : {$h->latitude}, {$h->longitude}  ✅ (geo matching active)");
            } else {
                $this->line('Coordinates     : NULL  ⚠️  geo matching disabled — using text match');
            }
        }

        // Step-by-step donor funnel
        $this->info("\n=== Donor Funnel ===");
        $all          = Donor::count();
        $sameBlood    = Donor::where('blood_type', $request->blood_type)->count();
        $eligible     = Donor::where('blood_type', $request->blood_type)
                            ->whereBetween('age', [18, 65])->where('weight', '>=', 50)->count();
        $eligibleOpt  = Donor::where('blood_type', $request->blood_type)
                            ->whereBetween('age', [18, 65])->where('weight', '>=', 50)
                            ->where('notifications_enabled', true)->count();
        $withCoords   = Donor::where('blood_type', $request->blood_type)
                            ->whereBetween('age', [18, 65])->where('weight', '>=', 50)
                            ->where('notifications_enabled', true)
                            ->whereNotNull('latitude')->whereNotNull('longitude')->count();

        $this->line("Total donors in DB                            : {$all}");
        $this->line("  └─ same blood type ({$request->blood_type})                          : {$sameBlood}");
        $this->line("       └─ + eligible (age 18-65, weight ≥50) : {$eligible}");
        $this->line("            └─ + notifications enabled        : {$eligibleOpt}");
        $this->line("                 └─ + has coordinates         : {$withCoords}");

        if ($h && $h->hasCoordinates() && $withCoords > 0) {
            $this->info("\n=== Distance from hospital to each eligible donor (km) ===");
            $donors = Donor::with('user')
                ->where('blood_type', $request->blood_type)
                ->whereBetween('age', [18, 65])->where('weight', '>=', 50)
                ->where('notifications_enabled', true)
                ->whereNotNull('latitude')->whereNotNull('longitude')
                ->get();

            $rows = $donors->map(function (Donor $d) use ($h) {
                $dist = DonorMatcher::distanceKm(
                    (float) $d->latitude, (float) $d->longitude,
                    (float) $h->latitude, (float) $h->longitude,
                );
                return [
                    $d->id,
                    $d->user?->full_name ?? '—',
                    "{$d->latitude}, {$d->longitude}",
                    sprintf('%.1f', $dist),
                ];
            })->sortBy(3)->values()->all();

            $this->table(['Donor ID', 'Name', 'Coords', 'Distance (km)'], $rows);
        }

        // Final result
        $radius = $this->option('radius') !== null ? (int) $this->option('radius') : null;
        $matches = $matcher->matchFor($request, $radius);

        $this->info("\n=== Final Matching Result ===");
        $this->line('Radius used     : ' . ($radius ?? config('bloodline.match_radius_km', 100)) . ' km');
        $this->line('Donors matched  : ' . $matches->count());

        if ($matches->isEmpty()) {
            $this->warn("\n💡 Suggestions:");
            if ($h && $h->hasCoordinates() && $withCoords > 0) {
                $this->line('   • Try a larger radius: php artisan donors:debug-match '
                    . $id . ' --radius=200');
            }
            if (! $request->location) {
                $this->line('   • The request has no location text — text fallback unavailable.');
            }
            if ($eligibleOpt === 0) {
                $this->line('   • No eligible donors with this blood type & notifications on exist.');
            }
        } else {
            $rows = $matches->map(fn (Donor $d) => [
                $d->id,
                $d->user?->full_name ?? '—',
                $d->user?->email ?? '—',
                isset($d->distance_km) ? sprintf('%.1f km', $d->distance_km) : 'n/a',
            ])->all();
            $this->table(['ID', 'Name', 'Email', 'Distance'], $rows);
        }

        return self::SUCCESS;
    }
}
