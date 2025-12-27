<?php

declare(strict_types=1);

namespace Modules\Shared\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Shared\Http\Traits\ApiResponseTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Shared\Services\BaseService;
use OpenApi\Annotations as OA;


/**
 * @OA\Info(
 *   title="Ecommerce API",
 *   version="1.0.0",
 *   description="Tài liệu API cho hệ thống Backend",
 *   @OA\Contact(
 *     email="admin@example.com"
 *   )
 * )
 *
 * @OA\Server(
 *   url=L5_SWAGGER_CONST_HOST,
 *   description="API Server"
 * )
 */

abstract class BaseController extends Controller
{
    use AuthorizesRequests, ValidatesRequests, ApiResponseTrait;

    protected $service;

    public function __construct(BaseService $service)
    {
        $this->service = $service;
    }
}