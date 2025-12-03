<?php
namespace Modules\Product\Infrastructure\Repositories;
use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Modules\Product\Domain\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class EloquentProductRepository extends EloquentBaseRepository implements ProductRepositoryInterface {

    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function filter(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator {

        $query = $this->model->newQuery()->with(['category', 'images', 'variants.attributeValues.attribute']);

        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $query->where(fn($sub) => $sub->where('name', 'like', "%$q%")->orWhere('sku', 'like', "%$q%"));
        }

        if (isset($filters['is_active'])) $query->where('is_active', (bool)$filters['is_active']);

        if (!empty($filters['category_uuid'])) {
            $query->whereHas('category', fn($c) => $c->where('uuid', $filters['category_uuid']));
        }

        if (!empty($filters['attributes']) && is_array($filters['attributes'])) {
            foreach ($filters['attributes'] as $slug => $value) {
                $query->whereHas('variants.attributeValues', function (Builder $q) use ($slug, $value) {
                    $q->where('value', $value)->whereHas('attribute', fn($aq) => $aq->where('slug', $slug));
                });
            }
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }
}