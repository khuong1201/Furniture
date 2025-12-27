<?php

declare(strict_types=1);

namespace Modules\Product\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Contracts\Pagination\LengthAwarePaginator; 
use Modules\Product\Domain\Repositories\AttributeRepositoryInterface;
use Modules\Shared\Exceptions\BusinessException;
use Modules\Shared\Services\BaseService;

class AttributeService extends BaseService 
{
    public function __construct(AttributeRepositoryInterface $repo) 
    { 
        parent::__construct($repo); 
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        if (!isset($filters['with'])) {
            $filters['with'] = ['values'];
        }

        return $this->repository->filter($filters);
    }

    public function create(array $data): Model 
    {
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $exists = $this->repository->findBy(['slug' => $data['slug']])->first();
        if ($exists) {
            throw new BusinessException(409163, "Attribute slug '{$data['slug']}' already exists");
        }

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
                
                $keepIds = [];
                
                foreach ($data['values'] as $valData) {
                    if (isset($valData['id']) || isset($valData['uuid'])) {
                        $valModel = $attribute->values()->where('value', $valData['value'])->first();
                        
                        if ($valModel) {
                            $valModel->update([
                                'value' => $valData['value'],
                                'code'  => $valData['code'] ?? null
                            ]);
                            $keepIds[] = $valModel->id;
                        } else {
                            $newVal = $attribute->values()->create($valData);
                            $keepIds[] = $newVal->id;
                        }
                    } else {
                        $existing = $attribute->values()->where('value', $valData['value'])->first();
                        if ($existing) {
                            $keepIds[] = $existing->id; 
                        } else {
                            $newVal = $attribute->values()->create($valData);
                            $keepIds[] = $newVal->id;
                        }
                    }
                }
                try {
                    $attribute->values()->whereNotIn('id', $keepIds)->delete();
                } catch (\Exception $e) {
                    // Tùy nghiệp vụ: Có thể bỏ qua lỗi này hoặc báo lỗi cho user
                    // throw new BusinessException(409, "Không thể xóa giá trị đang được sử dụng");
                }
            }

            return $attribute->load('values');
        });
    }
}