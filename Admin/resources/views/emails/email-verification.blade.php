<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Xác nhận email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 5px 5px;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Xác nhận email đăng ký</h2>
    </div>

    <div class="content">
        <h3>Xin chào {{ $user->name }}!</h3>

        <p>Cảm ơn bạn đã đăng ký tài khoản tại BCP. Để hoàn tất quá trình đăng ký, vui lòng click vào nút bên dưới để xác nhận email của bạn:</p>

        <div style="text-align: center;">
            <a href="{{ $verificationUrl }}" class="button">Xác nhận email</a>
        </div>

        <p>Nếu bạn không thể click vào nút trên, hãy copy và paste URL này vào trình duyệt:</p>
        <p style="background-color: #e9ecef; padding: 10px; border-radius: 3px; word-break: break-all;">
            {{ $verificationUrl }}
        </p>

        <p><strong>Lưu ý:</strong> Link này sẽ hết hạn sau 24 giờ.</p>

        <p>Nếu bạn không thực hiện đăng ký này, vui lòng bỏ qua email này.</p>

        <p>Trân trọng,<br><strong>Đội ngũ BCP</strong></p>
    </div>

    <div class="footer">
        <p>Email này được gửi tự động, vui lòng không trả lời.</p>
    </div>
</body>
</html>
