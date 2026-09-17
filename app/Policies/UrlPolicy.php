<?php

namespace App\Policies;

use App\Models\Url;
use App\Models\User;

class UrlPolicy
{
    /**
     * Determine if the user can view a specific URL.
     * A user may only view URLs they own.
     */
    public function view(User $user, Url $url): bool
    {
        return $user->id === $url->user_id;
    }

    /**
     * Determine if the user can delete a specific URL.
     * A user may only delete URLs they own.
     */
    public function delete(User $user, Url $url): bool
    {
        return $user->id === $url->user_id;
    }
}
