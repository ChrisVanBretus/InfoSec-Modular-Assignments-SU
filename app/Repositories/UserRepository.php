<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function clearSecurityFlags(User $user): void
    {
        $user->forceFill([
            'failed_attempts' => 0,
            'locked_at' => null,
            'must_reset_password' => false,
        ])->save();
    }

    public function registerFailedAttempt(User $user, int $maxAttempts): int
    {
        $user->failed_attempts = (int) $user->failed_attempts + 1;

        if ($user->failed_attempts >= $maxAttempts) {
            $user->forceFill([
                'failed_attempts' => $user->failed_attempts,
                'locked_at' => now(),
                'must_reset_password' => true,
            ])->save();

            return 0;
        }

        $user->save();

        return $maxAttempts - $user->failed_attempts;
    }

    public function resetPassword(User $user, string $hashedPassword): void
    {
        $user->forceFill([
            'password' => $hashedPassword,
            'failed_attempts' => 0,
            'locked_at' => null,
            'must_reset_password' => false,
        ])->save();
    }
}
