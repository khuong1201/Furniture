<?php

namespace Modules\Review\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Review\Services\ReviewService;
use Modules\Review\Http\Requests\StoreReviewRequest;
use Modules\Review\Http\Requests\UpdateReviewRequest;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(protected ReviewService $service) {}

    public function index(Request $request)
    {
        $reviews = $this->service->list($request->all(), $request->query('per_page', 10));
        return response()->json($reviews);
    }

    public function store(StoreReviewRequest $request)
    {
        $review = $this->service->create($request->validated());
        return response()->json($review, 201);
    }

    public function update(UpdateReviewRequest $request, string $uuid)
    {
        $review = $this->service->update($uuid, $request->validated());
        return response()->json($review);
    }

    public function destroy(string $uuid)
    {
        $this->service->delete($uuid);
        return response()->json(['message' => 'Review deleted']);
    }
}
