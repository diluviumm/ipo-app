<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

// Provider user yang memakai kolom password_hash (bukan password bawaan),
// termasuk saat Laravel me-rehash password lama ke cost terbaru.
class IpoUserProvider extends EloquentUserProvider
{
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $remember = false): void
    {
        if (!$this->hasher->needsRehash($user->getAuthPassword())) {
            return;
        }
        $user->forceFill([
            'password_hash' => $this->hasher->make($credentials['password']),
        ])->save();
    }
}
