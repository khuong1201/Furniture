<?php

declare(strict_types=1);

namespace Modules\Review\Events;

use Modules\Review\Domain\Models\Review;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReviewApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(public Review $review) {}
}