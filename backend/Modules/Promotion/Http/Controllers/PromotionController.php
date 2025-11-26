<?php

namespace Modules\Promotion\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Promotion\Http\Requests\StorePromotionRequest;
use Modules\Promotion\Http\Requests\UpdatePromotionRequest;
use Modules\Promotion\Services\PromotionService;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function __construct(protected PromotionService $service) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $data = $this->service->paginate($perPage);
        return response()->json($data);
    }

    public function store(StorePromotionRequest $request)
    {
        $promotion = $this->service->store($request->validated());
        return response()->json($promotion, 201);
    }

    public function update(UpdatePromotionRequest $request, string $uuid)
    {
        $promotion = $this->service->update($uuid, $request->validated());
        return response()->json($promotion);
    }

    public function destroy(string $uuid)
    {
        $this->service->delete($uuid);
        return response()->json(['message' => 'Promotion deleted']);
    }
}
