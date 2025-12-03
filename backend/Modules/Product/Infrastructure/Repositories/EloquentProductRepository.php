<?php

namespace Modules\Product\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Modules\Product\Domain\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentProductRepository extends EloquentBaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with([
                'category', 
                'images', 
                'variants.attributeValues.attribute', 
                'promotions' => function($q) {
                    $q->where('status', true)
                      ->where('start_date', '<=', now())
                      ->where('end_date', '>=', now());
                }
            ]);

        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $query->where(function(Builder $sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%") 
                    ->orWhereHas('variants', function($v) use ($q) {
                        $v->where('sku', 'like', "%{$q}%");
                    })
                    ->orWhereHas('category', function($c) use ($q) {
                        $c->where('name', 'like', "%{$q}%");
                    });
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool)$filters['is_active']);
        }

        if (!empty($filters['category_uuid'])) {
            $query->whereHas('category', fn($c) => $c->where('uuid', $filters['category_uuid']));
        }

        if (!empty($filters['attributes']) && is_array($filters['attributes'])) {
            foreach ($filters['attributes'] as $slug => $value) {
                $query->whereHas('variants.attributeValues', function (Builder $q) use ($slug, $value) {
                    $q->where('value', $value)
                      ->whereHas('attribute', fn($aq) => $aq->where('slug', $slug));
                });
            }
        }

        if (!empty($filters['on_sale'])) {
            $query->whereHas('promotions', function ($q) {
                $q->where('status', true)
                  ->where('start_date', '<=', now())
                  ->where('end_date', '>=', now());
            });
        }

        if (isset($filters['sort_by'])) {
            switch ($filters['sort_by']) {
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
                    $query->orderBy('price', 'desc');
                    break;
                case 'latest':
                default:
                    $query->latest(); 
                    break;
            }
        } else {
            $query->latest(); 
        }
        return $query->paginate($filters['per_page'] ?? 15);
    }
}