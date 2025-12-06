<?php

declare(strict_types=1);

namespace Modules\Review\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Review\Domain\Models\Review;

class ReviewApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Review $review
    ) {}
}