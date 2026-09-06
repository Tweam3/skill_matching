<?php

namespace App\Services\Moderation;

use App\Models\User;

class ModerationService
{
    public function escalateViolation(User $user): array
    {
        $count = (int) $user->Warning_Count;

        if ($count >= 2) {
            return $this->applyBan($user, $count);
        }

        if ($count === 1) {
            return $this->applySuspension($user, $count);
        }

        return $this->applyWarning($user, $count);
    }

    protected function applyWarning(User $user, int $count): array
    {
        $user->update([
            'Account_Status' => 'Warning',
            'Warning_Count' => $count + 1,
        ]);

        return [
            'status' => 'Warning',
            'level' => 1,
            'message' => 'This is your first warning. Further violations will result in account suspension.',
        ];
    }

    protected function applySuspension(User $user, int $count): array
    {
        $user->update([
            'Account_Status' => 'Suspended',
            'Warning_Count' => $count + 1,
            'Suspended_At' => now(),
        ]);

        return [
            'status' => 'Suspended',
            'level' => 2,
            'message' => 'Your account has been suspended for 3 days due to repeated violations. A further violation will result in a permanent ban.',
        ];
    }

    protected function applyBan(User $user, int $count): array
    {
        $user->update([
            'Account_Status' => 'Banned',
            'Warning_Count' => $count + 1,
        ]);

        return [
            'status' => 'Banned',
            'level' => 3,
            'message' => 'Your account has been permanently banned.',
        ];
    }
}
