<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .btn {
            display: inline-block;
            padding: 12px 20px;
            margin-top: 20px;
            background-color: #348738;
            color: #fff !important;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #2c6c2f;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🎉 Chúc mừng bạn!</h2>
        <p>Thương hiệu của bạn đã được duyệt thành công.</p>
        <p>Hãy truy cập vào link dưới đây để quản lý thương hiệu của bạn:</p>
        <a href="{{ $urlWebAdmin }}" class="btn">Quản lý thương hiệu</a>
        <p>Chúc bạn một ngày tuyệt vời!</p>
    </div>
</body>
</html>
