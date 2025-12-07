<?php

declare(strict_types=1);

namespace Modules\Payment\Services;

use Modules\Shared\Services\BaseService;
use Modules\Payment\Domain\Repositories\PaymentRepositoryInterface;
use Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use Modules\Payment\Events\PaymentCompleted;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;
use Exception;

class PaymentService extends BaseService
{
    public function __construct(
        PaymentRepositoryInterface $repository,
        protected OrderRepositoryInterface $orderRepo,
        protected PaymentGatewayFactory $gatewayFactory
    ) {
        parent::__construct($repository);
    }

    public function initiatePayment(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $order = $this->orderRepo->findByUuid($data['order_uuid']);
            
            if (!$order) throw ValidationException::withMessages(['order_uuid' => 'Order not found']);
            
            // Check Owner
            if (!auth()->user()->hasRole('admin') && $order->user_id !== auth()->id()) {
                throw ValidationException::withMessages(['order_uuid' => 'Unauthorized access to this order']);
            }

            if ($order->payment_status === 'paid') {
                throw ValidationException::withMessages(['order_uuid' => 'Order already paid']);
            }
            
            if ($order->status === 'cancelled') {
                throw ValidationException::withMessages(['order_uuid' => 'Cannot pay for cancelled order']);
            }

            // Create Payment Record
            $payment = $this->repository->create([
                'order_id' => $order->id,
                'method' => $data['method'],
                'amount' => $order->total_amount,
                'status' => 'pending'
            ]);

            // Handle COD (Cash On Delivery)
            if ($data['method'] === 'cod') {
                return [
                    'payment' => $payment,
                    'redirect_url' => null 
                ];
            }

            // Handle Online Gateway
            try {
                $gateway = $this->gatewayFactory->get($data['method']);
                
                if (!$gateway) {
                     throw new Exception("Gateway for {$data['method']} not implemented.");
                }

                $url = $gateway->createPaymentUrl($order->uuid, (float)$order->total_amount);

                return [
                    'payment' => $payment,
                    'redirect_url' => $url
                ];
            } catch (Exception $e) {
                // Xóa payment record nếu lỗi tạo URL để user có thể thử lại
                $payment->forceDelete();
                throw ValidationException::withMessages(['method' => $e->getMessage()]);
            }
        });
    }

    public function processCallback(string $provider, array $payload): void
    {
        $gateway = $this->gatewayFactory->get($provider);
        
        if (!$gateway || !$gateway->verifyWebhook($payload)) {
             throw new Exception('Invalid Signature or Gateway not found');
        }
        
        // Logic update status based on payload (Cần implement cụ thể theo từng gateway)
        // Ví dụ: $orderId = $payload['orderId']; $status = $payload['resultCode'] == 0 ? 'paid' : 'failed';
        // $this->updateByTransactionId(..., ['status' => 'paid']);
    }
    
    public function update(string $uuid, array $data): Model
{
    return DB::transaction(function () use ($uuid, $data) { 

        $payment = $this->findByUuidOrFail($uuid);

        $isStatusChangeToPaid = ($payment->status !== 'paid' && ($data['status'] ?? '') === 'paid');

        if ($isStatusChangeToPaid) {
            $data['paid_at'] = now();
            $payment = $this->repository->update($payment, $data); 
            
            event(new PaymentCompleted($payment));
        } else {
            $payment = $this->repository->update($payment, $data); 
        }
        
        return $payment;
    }); 
}
}