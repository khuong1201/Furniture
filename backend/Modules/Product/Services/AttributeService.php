<?php
namespace Modules\Product\Services;
use Modules\Shared\Services\BaseService;
use Modules\Product\Domain\Repositories\AttributeRepositoryInterface;
use Modules\Product\Domain\Models\AttributeValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class AttributeService extends BaseService {

    public function __construct(AttributeRepositoryInterface $repo) 
    { 
        parent::__construct($repo); 
    }

    public function create(array $data): Model {

        return DB::transaction(function () use ($data) {

            $attribute = parent::create(['name' => $data['name'], 'slug' => $data['slug'], 'type' => $data['type']]);
            
            if (!empty($data['values'])) {
                foreach ($data['values'] as $val) {
                    $attribute->values()->create(['value' => $val['value'], 'code' => $val['code'] ?? null]);
                }
            }

            return $attribute->load('values');
        });
    }

    public function update(string $uuid, array $data): Model {
        return DB::transaction(function () use ($uuid, $data) {
            
            $attribute = $this->findByUuidOrFail($uuid);

            $attribute->update($data);

            if (isset($data['values'])) {
                foreach ($data['values'] as $val) {
                    if (isset($val['uuid'])) {
                        $valModel = AttributeValue::where('uuid', $val['uuid'])->first();
                        if ($valModel) $valModel->update(['value' => $val['value'], 'code' => $val['code'] ?? null]);
                    } else { 
                        $attribute->values()->create(['value' => $val['value'], 'code' => $val['code'] ?? null]);
                    }
                }
            }

            return $attribute->load('values');
        });
    }
}