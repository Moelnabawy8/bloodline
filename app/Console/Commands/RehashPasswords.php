<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class RehashPasswords extends Command
{
    protected $signature = 'users:rehash-passwords {--dry-run : Show what would be rehashed without saving}';

    protected $description = 'Rehash all plain-text passwords in users table using bcrypt.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $rehashed = 0;
        $skipped = 0;

        User::query()->chunkById(200, function ($users) use (&$rehashed, &$skipped, $dryRun) {
            foreach ($users as $user) {
                if ($this->isBcrypt($user->password)) {
                    $skipped++;
                    continue;
                }

                $this->line("[{$user->id}] {$user->email} — rehashing");

                if (! $dryRun) {
                    $user->forceFill([
                        'password' => Hash::make($user->password),
                    ])->saveQuietly();
                }

                $rehashed++;
            }
        });

        $this->info("Rehashed: {$rehashed}");
        $this->info("Skipped (already bcrypt): {$skipped}");

        if ($dryRun) {
            $this->warn('Dry run — no changes were saved.');
        }

        return self::SUCCESS;
    }

    private function isBcrypt(?string $hash): bool
    {
        return is_string($hash) && preg_match('/^\$2[aby]\$\d{2}\$/', $hash) === 1;
    }
}
