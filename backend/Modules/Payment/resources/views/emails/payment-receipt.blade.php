@component('mail::message')
# 🥳 Xác nhận Thanh toán Thành công

Xin chào **{{ $user->name ?? 'Quý khách' }}**,

Chúng tôi xác nhận đã nhận được thanh toán của bạn. Đơn hàng của bạn đang được xử lý. Dưới đây là chi tiết giao dịch:

---

## 🧾 Thông tin giao dịch

| Chi tiết | Giá trị |
| :--- | :--- |
| **Mã giao dịch** | {{ $payment->uuid }} |
| **Số tiền** | **{{ number_format($payment->amount) }} VND** |
| **Thời gian** | {{ $payment->created_at->format('H:i:s, d/m/Y') }} |
| **Phương thức** | {{ $payment->method ?? 'N/A' }} |

---

@if ($payment->order_id ?? false)
Bạn có thể xem chi tiết đơn hàng (Mã: #{{ $payment->order_id }}) tại website.
@component('mail::button', ['url' => url('/profile/orders/' . $payment->order_id)])
Xem chi tiết đơn hàng
@endcomponent
@endif

Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của chúng tôi.

Trân trọng,

{{ config('app.name') }}
@endcomponent