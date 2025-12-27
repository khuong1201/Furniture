<?php

declare(strict_types=1);

namespace Modules\Payment\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Enums\PaymentStatus;
use Modules\Payment\Contracts\PaymentGatewayInterface;
use Modules\Payment\Domain\Repositories\PaymentRepositoryInterface;
use Modules\Payment\Gateways\CodGateway;
use Modules\Payment\Gateways\MomoGateway;
use Modules\Shared\Exceptions\BusinessException;
use Modules\Shared\Services\BaseService;
use Throwable;

class PaymentService extends BaseService
{
    public function __construct(
        PaymentRepositoryInterface $repository,
        protected OrderRepositoryInterface $orderRepo
    ) {
        parent::__construct($repository);
    }

    public function getGateway(string $method): PaymentGatewayInterface
    {
        return match ($method) {
            'cod'   => new CodGateway(),
            'momo'  => new MomoGateway(),
            default => throw new BusinessException(400141, "Payment method '$method' not supported"),
        };
    }

    public function initiatePayment(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $order = $this->orderRepo->findForPayment($data['order_uuid']);
            
            if (!$order) throw new BusinessException(404130, 'Order not found.');

            if ($order->status === OrderStatus::CANCELLED) {
                throw new BusinessException(409138, 'Cannot initiate payment for a cancelled order.');
            }

            $payment = $this->repository->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'uuid' => (string) Str::uuid(),
                    'method' => $data['method'],
                    'amount' => $order->grand_total,
                    'status' => ($data['method'] === 'cod') ? 'pending' : 'pending', 
                ]
            );

            if ($data['method'] === 'cod') {
                $order->update(['payment_status' => \Modules\Order\Enums\PaymentStatus::UNPAID]); 
            }

            $gateway = $this->getGateway($data['method']);
            $redirectUrl = $gateway->createPaymentUrl($payment->uuid, (float)$payment->amount, "Payment for #{$order->code}");

            return [
                'payment_uuid' => $payment->uuid,
                'redirect_url' => $redirectUrl,
                'is_cod' => $data['method'] === 'cod'
            ];
        });
    }

    public function updateStatus(string $uuid, string $status): Model
    {
        return DB::transaction(function () use ($uuid, $status) {
            $payment = $this->repository->findByUuid($uuid);
            if (!$payment) throw new BusinessException(404140, 'Payment record not found.');
            
            $order = $payment->order;

            if ($status === 'paid') {
                if ($payment->method === 'cod') {
                    $allowedOrderStatusesForCod = [
                        OrderStatus::SHIPPING->value, 
                        OrderStatus::DELIVERED->value
                    ];

                    if (!in_array($order->status->value, $allowedOrderStatusesForCod)) {
                        throw new BusinessException(400994, 'COD orders must be shipped or delivered before payment confirmation.');
                    }
                }
            }

            $payment->update([
                'status' => $status,
                'paid_at' => $status === 'paid' ? now() : $payment->paid_at
            ]);

            if ($status === 'paid') {
                $order->update(['payment_status' => PaymentStatus::PAID]);
                
                // Nếu khách trả tiền Online sớm khi đơn đang Pending -> Chuyển sang Processing ngay
                if ($payment->method !== 'cod' && $order->status === OrderStatus::PENDING) {
                    $order->update(['status' => OrderStatus::PROCESSING]);
                }
            }

            event(new \Modules\Payment\Events\PaymentCompleted($payment));

            return $payment->refresh();
        });
    }

    public function processCallback(string $provider, array $payload): void
    {
        try {
            $gateway = $this->getGateway($provider);
            $result = $gateway->verifyWebhook($payload);
            
            if (!$result) throw new BusinessException(400142, 'Invalid webhook signature.');

            $paymentUuid = $payload['orderId'] ?? $payload['vnp_TxnRef'] ?? null;
            $payment = $this->repository->findByUuid($paymentUuid);
            
            if (!$payment) throw new BusinessException(404140, 'Payment record missing.');
            if ($payment->status === 'paid') return;

            $status = $result['status']; 

            DB::transaction(function () use ($payment, $status, $result, $payload) {
                $payment->update([
                    'status' => $status,
                    'transaction_id' => $result['transaction_id'],
                    'payment_data' => $payload,
                    'paid_at' => $status === 'paid' ? now() : null
                ]);
                
                if ($status === 'paid') {
                    $payment->order()->update([
                        'payment_status' => PaymentStatus::PAID,
                        'status' => OrderStatus::PROCESSING 
                    ]);
                }
            });

            if (in_array($status, ['paid', 'failed', 'refunded'])) {
                event(new \Modules\Payment\Events\PaymentCompleted($payment));
            }

        } catch (Throwable $e) {
            Log::error("Payment Callback Failed [$provider]: " . $e->getMessage());
            throw $e; 
        }
    }

    public function filter(int $perPage, array $filters = [])
    {
        $filters['per_page'] = $perPage;
        return $this->repository->filter($filters);
    }
}