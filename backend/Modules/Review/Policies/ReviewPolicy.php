<?php
namespace Modules\Review\Policies;
use Modules\User\Domain\Models\User;
use Modules\Review\Domain\Models\Review;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReviewPolicy {
    use HandlesAuthorization;

    public function viewAny(?User $user): bool { return true; }

    public function update(User $user, Review $review): bool {
        return $user->id === $review->user_id || $user->hasPermissionTo('review.edit');
    }

    public function delete(User $user, Review $review): bool {
        return $user->id === $review->user_id || $user->hasPermissionTo('review.delete');
    }
}