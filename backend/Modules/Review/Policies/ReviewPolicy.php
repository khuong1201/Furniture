<?php

namespace Modules\Review\Policies;

use Modules\User\Domain\Models\User; 
use Modules\Review\Domain\Models\Review; 
use Illuminate\Auth\Access\HandlesAuthorization;

class ReviewPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Review $review)
    {
        return $user->id === $review->user_id || $user->hasRole('admin');
    }

    public function delete(User $user, Review $review)
    {
        return $user->id === $review->user_id || $user->hasRole('admin');
    }
}