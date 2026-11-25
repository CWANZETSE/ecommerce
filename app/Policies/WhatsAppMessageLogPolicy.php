<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WhatsAppMessageLog;

class WhatsAppMessageLogPolicy
{
    public function viewAny(?User $user): bool
    {
        return $user?->isAdmin() ?? false;
    }

    public function view(
        User $user,
        WhatsAppMessageLog $message
    ): bool {
        return $user->isAdmin();
    }
}
