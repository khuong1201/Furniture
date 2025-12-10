<!DOCTYPE html>
<html>
<head>
    <style>
        .otp-box {
            font-size: 24px;
            font-weight: bold;
            color: #2d3748;
            letter-spacing: 5px;
            padding: 10px 20px;
            border: 1px dashed #cbd5e0;
            display: inline-block;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h2>Xin chào {{ $user->name }},</h2>
    <p>Cảm ơn bạn đã đăng ký tài khoản. Để hoàn tất, vui lòng nhập mã OTP dưới đây:</p>
    
    <div class="otp-box">{{ $otp }}</div>
    
    <p>Mã này sẽ hết hạn sau <strong>10 phút</strong>.</p>
    <p>Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email.</p>
</body>
</html>