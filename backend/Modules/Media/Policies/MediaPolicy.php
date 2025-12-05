<?php

declare(strict_types=1);

namespace Modules\Media\Policies;

use Modules\User\Domain\Models\User;
use Modules\Media\Domain\Models\Media;
use Illuminate\Auth\Access\HandlesAuthorization;

class MediaPolicy
{
    use HandlesAuthorization;
    
    public function create(User $user): bool
    {
        // Mọi user login đều được upload (ví dụ avatar)
        return true;
    }

    public function delete(User $user, Media $media): bool
    {
        // Admin xóa tất cả
        if ($user->hasPermissionTo('media.delete')) {
            return true;
        }

        // User chỉ được xóa media do mình sở hữu (gắn vào User Model của mình)
        if ($media->model_type === get_class($user) && $media->model_id === $user->id) {
            return true;
        }

        return false;
    }
}