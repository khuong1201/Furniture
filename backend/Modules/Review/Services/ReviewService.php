<?php

declare(strict_types=1);

namespace Modules\Review\Services;

use Modules\Shared\Services\BaseService;
use Modules\Review\Domain\Repositories\ReviewRepositoryInterface;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Model;
use Modules\Review\Events\ReviewPosted; // Event mới
use Modules\Review\Events\ReviewApproved; // Event mới

class ReviewService extends BaseService
{
    public function __construct(
        ReviewRepositoryInterface $repository,
        protected ProductRepositoryInterface $productRepo
    ) {
        parent::__construct($repository);
    }

    public function create(array $data): Model
    {
        $product = $this->productRepo->findByUuid($data['product_uuid']);
        if (!$product) throw ValidationException::withMessages(['product_uuid' => 'Product not found']);

        // Check unique review
        $exists = $this->repository->query()
            ->where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['product_uuid' => 'You have already reviewed this product.']);
        }

        $reviewData = [
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'images' => $data['images'] ?? [],
            'is_approved' => false // Mặc định cần duyệt
        ];

        // Nếu có quyền auto-approve (ví dụ trusted user) thì set true luôn
        // Ở đây giữ false cho an toàn

        $review = $this->repository->create($reviewData);
        
        event(new ReviewPosted($review));

        return $review;
    }

    public function update(string $uuid, array $data): Model
    {
        $review = $this->repository->findByUuidOrFail($uuid);

        // Chỉ admin mới được sửa trạng thái duyệt
        if (!auth()->user()->hasRole('admin')) {
            unset($data['is_approved']);
        }

        $oldStatus = $review->is_approved;
        
        $this->repository->update($review, $data);
        
        // Nếu trạng thái chuyển sang approved -> tính lại rating sản phẩm
        if (!$oldStatus && ($data['is_approved'] ?? false)) {
             event(new ReviewApproved($review));
        }

        return $review;
    }
    
    public function delete(string $uuid): bool
    {
        $review = $this->repository->findByUuidOrFail($uuid);
        $productId = $review->product_id;
        
        $result = $this->repository->delete($review);
        
        if ($result) {
             // Trigger event để tính lại rating (hoặc gọi trực tiếp logic tính toán)
             // Ở đây ta dùng cơ chế Event Listener ở Product Module để decouple
             event(new ReviewApproved($review)); // Tái sử dụng event này để trigger recalc
        }
        
        return $result;
    }
}