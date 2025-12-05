<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f8f9fa; padding: 20px; text-align: center; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 12px; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Cảm ơn bạn đã đặt hàng!</h2>
        </div>
        <p>Xin chào <strong>{{ $order->user->name }}</strong>,</p>
        <p>Mã đơn: {{ $order->uuid }}</p>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th class="text-right">SL</th>
                    <th class="text-right">Giá</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product_snapshot['name'] ?? 'SP' }}</td>
                        <td class="text-right">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->subtotal) }}đ</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2" class="text-right"><strong>Tổng cộng:</strong></td>
                    <td class="text-right"><strong>{{ number_format($order->total_amount) }}đ</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>