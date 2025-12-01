<?php

namespace Modules\Payment\Services;

use Modules\Shared\Services\BaseService;
use Modules\Payment\Domain\Repositories\PaymentRepositoryInterface;
use Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use Modules\Payment\Events\PaymentCompleted;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;
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
            $order = \Modules\Order\Models\Order::where('uuid', $data['order_uuid'])->first();
            
            if (!$order) throw ValidationException::withMessages(['order_uuid' => 'Order not found']);
            
            if ($order->payment_status === 'paid') {
                throw ValidationException::withMessages(['order_uuid' => 'Order already paid']);
            }

            $payment = $this->repository->create([
                'order_id' => $order->id,
                'method' => $data['method'],
                'amount' => $order->total_amount,
                'status' => 'pending'
            ]);

            if ($data['method'] === 'cod') {
                return [
                    'payment' => $payment,
                    'redirect_url' => null 
                ];
            }

            $gateway = $this->gatewayFactory->get($data['method']);
            $url = $gateway->createPaymentUrl($order->uuid, $order->total_amount);

            return [
                'payment' => $payment,
                'redirect_url' => $url
            ];
        });
    }

    public function processCallback(string $method, array $payload)
    {
        // ... Logic verify signature của gateway ở đây ...
        // Giả sử verify thành công và lấy được order_uuid
        
        // Demo logic update
        // $payment = ... tìm payment by order_id
        // $payment->update(['status' => 'paid', 'paid_at' => now()]);
        // event(new PaymentCompleted($payment));
    }
    
    public function update(string $uuid, array $data): Model
    {
        $payment = $this->repository->findByUuid($uuid);

        if ($payment->status !== 'paid' && ($data['status'] ?? '') === 'paid') {
            $data['paid_at'] = now();
            $payment->update($data);
            event(new PaymentCompleted($payment));
        } else {
            $payment->update($data);
        }
        
        return $payment;
    }
}