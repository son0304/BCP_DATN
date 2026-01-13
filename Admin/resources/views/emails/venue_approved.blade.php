<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            width: 100%;
            background-color: #f4f6f8;
            padding: 40px 0;
        }

        .main-content {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .header {
            background-color: #348738;
            padding: 30px 20px;
            text-align: center;
        }

        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .body-content {
            padding: 40px 30px;
            text-align: center;
            color: #555555;
        }

        .icon-circle {
            width: 70px;
            height: 70px;
            background-color: #e6f4ea;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .icon-check {
            font-size: 35px;
            color: #348738;
        }

        .btn {
            display: inline-block;
            padding: 14px 30px;
            background-color: #348738;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            margin-top: 20px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="main-content">
            <div class="header">
                <h1>SPORT VENUE</h1>
            </div>
            <div class="body-content">
                <div class="icon-circle"><span class="icon-check">✓</span></div>
                <h2 style="color: #333; margin-top: 0;">Địa điểm đã được duyệt!</h2>
                <p>Xin chào <strong>{{ $user->name }}</strong>,</p>
                <p>Chúc mừng! Địa điểm kinh doanh của bạn đã vượt qua quy trình kiểm duyệt và hiện đã sẵn sàng hoạt động
                    trên hệ thống.</p>

                <!-- Nối chuỗi URL Frontend -->
                <a href="{{ $url }}/owner/venues" class="btn">Quản lý địa điểm ngay</a>

                <p style="margin-top: 30px; font-size: 13px; color: #888;">
                    Hoặc truy cập: <a href="{{ $url }}/owner/venues"
                        style="color: #348738;">{{ $url }}/owner/venues</a>
                </p>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Sport Venue System.</p>
        </div>
    </div>
</body>

</html>
