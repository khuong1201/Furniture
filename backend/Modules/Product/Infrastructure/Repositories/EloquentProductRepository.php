<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Modules\Product\Domain\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentProductRepository extends EloquentBaseRepository implements ProductRepositoryInterface 
{
    public function __construct(Product $model) { parent::__construct($model); }
    
    public function filter(array $filters): LengthAwarePaginator 
    {
        $query = $this->model->newQuery()
            ->with([
                'category', 
                'images', 
                // Tối ưu: chỉ load promotion đang chạy
                'promotions' => function($q) {
                    $q->active(); 
                }
            ]);
            
        // Nếu cần hiện biến thể ở list (thường list chỉ hiện min/max price, nhưng nếu cần load):
        // $query->with('variants');

        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $query->where(fn($sub) => $sub->where('name', 'like', "%$q%")->orWhere('sku', 'like', "%$q%"));
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool)$filters['is_active']);
        }

        if (!empty($filters['category_uuid'])) {
            $query->whereHas('category', fn($c) => $c->where('uuid', $filters['category_uuid']));
        }

        // Filter theo Price Range (nâng cao)
        if (!empty($filters['price_min']) && !empty($filters['price_max'])) {
             $query->where(function($q) use ($filters) {
                 // Check giá ở bảng products (simple) HOẶC bảng variants (configurable)
                 $q->whereBetween('price', [$filters['price_min'], $filters['price_max']])
                   ->orWhereHas('variants', fn($v) => $v->whereBetween('price', [$filters['price_min'], $filters['price_max']]));
             });
        }

        // Sort
        if (isset($filters['sort_by'])) {
            switch ($filters['sort_by']) {
                case 'best_selling': $query->orderByDesc('sold_count'); break;
                case 'top_rated': $query->orderByDesc('rating_avg'); break;
                case 'price_asc': $query->orderBy('price'); break; // Lưu ý: Logic sort giá biến thể phức tạp hơn, đây là basic
                case 'price_desc': $query->orderByDesc('price'); break;
                default: $query->latest();
            }
        } else {
            $query->latest();
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }
}