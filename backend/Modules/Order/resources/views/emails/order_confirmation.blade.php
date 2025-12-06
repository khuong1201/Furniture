<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Cảm ơn {{ $order->user->name ?? 'Quý khách' }} đã đặt hàng!</h2>
    <p>Mã đơn hàng: <strong>#{{ $order->uuid }}</strong></p>
    <p>Trạng thái: Đang xử lý</p>

    <h3>Chi tiết đơn hàng:</h3>
    <table>
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    {{ $item->product_snapshot['name'] ?? 'Sản phẩm' }}<br>
                    <small>SKU: {{ $item->product_snapshot['sku'] ?? 'N/A' }}</small>
                </td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->subtotal) }}đ</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3 style="text-align: right;">Tổng cộng: {{ number_format($order->total_amount) }}đ</h3>
</body>
</html>