<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Modules\Product\Domain\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Category\Domain\Models\Category;

class EloquentProductRepository extends EloquentBaseRepository implements ProductRepositoryInterface 
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }
    
    public function filter(array $filters): LengthAwarePaginator 
    {
        // 1. KHỞI TẠO QUERY
        $query = $this->model->newQuery()
            // Eager Load: Để lấy dữ liệu hiển thị (KHÔNG phải để lọc)
            ->with([
                'category', 
                'images', 
                'brand',
                'variants.stock',
                // Load variants và attributes để Resource xử lý nhanh, tránh N+1
                'variants.attributeValues.attribute', 
                
                // QUAN TRỌNG: Load promotion đang chạy để Model tính giá (original vs sale price)
                'promotions' => function($q) {
                    $q->active(); 
                }
            ]);

        // 2. TÌM KIẾM (Search)
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function(Builder $sub) use ($searchTerm) {
                $sub->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('sku', 'like', "%{$searchTerm}%");
            });
        }

        // 3. TRẠNG THÁI (Active)
        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        // 4. DANH MỤC (Category)
        if (!empty($filters['category_uuid'])) {
            $query->whereHas('category', fn($c) => $c->where('uuid', $filters['category_uuid']));
        }

        // DANH MỤC (Category SLUG - Dùng cho Frontend CategoryPage)
        if (!empty($filters['category_slug'])) {
            $slug = $filters['category_slug'];
            $category = Category::where('slug', $slug)->first();
            
            if ($category) {
                $catIds = $category->children()->pluck('id')->toArray();
                $catIds[] = $category->id;
                $query->whereIn('category_id', $catIds);
            } else {
                $query->where('id', -1);
            }
        } 
        elseif (!empty($filters['category_uuid'])) {
            $query->whereHas('category', fn($c) => $c->where('uuid', $filters['category_uuid']));
        }
        // THƯƠNG HIỆU (Brand)
        if (!empty($filters['brand_slug'])) {
            $query->whereHas('brand', fn($q) => $q->where('slug', $filters['brand_slug']));
        }
        elseif (!empty($filters['brand_uuid'])) {
            $uuids = is_array($filters['brand_uuid']) 
                ? $filters['brand_uuid'] 
                : explode(',', (string)$filters['brand_uuid']);
                
            $query->whereHas('brand', fn($q) => $q->whereIn('uuid', $uuids));
        }

        // 5. KHOẢNG GIÁ (Price Range)
        // Logic: Lấy sản phẩm có giá nằm trong khoảng (xét cả giá cha và giá con/variants)
        if (isset($filters['price_min'])) {
            $min = (int) $filters['price_min'];
            $query->where(function($sub) use ($min) {
                 $sub->where('price', '>=', $min)
                     ->orWhereHas('variants', fn($v) => $v->where('price', '>=', $min));
            });
        }

        if (isset($filters['price_max'])) {
            $max = (int) $filters['price_max'];
            $query->where(function($sub) use ($max) {
                 $sub->where('price', '<=', $max)
                     ->orWhereHas('variants', fn($v) => $v->where('price', '<=', $max));
            });
        }

        if (!empty($filters['is_flash_sale']) && filter_var($filters['is_flash_sale'], FILTER_VALIDATE_BOOLEAN)) {
            $query->whereHas('promotions', function ($q) {
                $q->active(); 
            });
        }

        // 7. SẮP XẾP (Sorting)
        $sortBy = $filters['sort_by'] ?? 'latest';
        
        switch ($sortBy) {
            case 'best_selling': 
                $query->orderByDesc('sold_count'); 
                break;
            case 'top_rated': 
                $query->orderByDesc('rating_avg'); 
                break;
            case 'price_asc': 
                $query->orderBy('price', 'asc'); 
                break;
            case 'price_desc': 
                $query->orderByDesc('price'); 
                break;
            case 'latest':
            default: 
                $query->latest(); 
                break;
        }

        return $query->paginate((int) ($filters['per_page'] ?? 15));
    }
}