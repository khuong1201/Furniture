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

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $order = $this->orderRepo->findByUuid($data['order_uuid']);
            
            if (!$order) throw ValidationException::withMessages(['order_uuid' => 'Order not found']);
            
            if (!auth()->user()->hasRole('admin') && $order->user_id !== auth()->id()) {
                throw ValidationException::withMessages(['order_uuid' => 'Unauthorized access to this order']);
            }

            if ($order->payment_status === 'paid') {
                throw ValidationException::withMessages(['order_uuid' => 'Order already paid']);
            }
            
            if ($order->status === 'cancelled') {
                throw ValidationException::withMessages(['order_uuid' => 'Cannot pay for cancelled order']);
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

            try {
                $gateway = $this->gatewayFactory->get($data['method']);
                $url = $gateway->createPaymentUrl($order->uuid, $order->total_amount);

                return [
                    'payment' => $payment,
                    'redirect_url' => $url
                ];
            } catch (\Exception $e) {
                throw ValidationException::withMessages(['method' => $e->getMessage()]);
            }
        });
    }

    public function processCallback(string $provider, array $payload)
    {
        $gateway = $this->gatewayFactory->get($provider);
        
        if (!$gateway->verifyWebhook($payload)) {
             throw new \Exception('Invalid Signature');
        }
        
    }
    
    public function update(string $uuid, array $data): Model
    {
        $payment = $this->repository->findByUuid($uuid);
        if (!$payment) throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Payment not found");

        if ($payment->status !== 'paid' && ($data['status'] ?? '') === 'paid') {
            $data['paid_at'] = now();
            $payment->update($data);
            
            $payment->order->update(['payment_status' => 'paid']);
            
            event(new PaymentCompleted($payment));
        } else {
            $payment->update($data);
        }
        
        return $payment;
    }
}