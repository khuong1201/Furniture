<?php

declare(strict_types=1);

namespace Modules\Product\Services;

use Modules\Shared\Services\BaseService;
use Modules\Product\Domain\Repositories\AttributeRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class AttributeService extends BaseService 
{
    public function __construct(AttributeRepositoryInterface $repo) 
    { 
        parent::__construct($repo); 
    }

    public function create(array $data): Model 
    {
        return DB::transaction(function () use ($data) {
            $attribute = parent::create([
                'name' => $data['name'], 
                'slug' => $data['slug'], 
                'type' => $data['type']
            ]);
            
            if (!empty($data['values'])) {
                $attribute->values()->createMany($data['values']);
            }
            
            return $attribute->load('values');
        });
    }

    public function update(string $uuid, array $data): Model 
    {
        $attribute = $this->findByUuidOrFail($uuid);

        return DB::transaction(function () use ($attribute, $data) {
            $attribute->update($data);

            if (isset($data['values'])) {
                $attribute->values()->delete();
                $attribute->values()->createMany($data['values']);
            }

            return $attribute->load('values');
        });
    }
}