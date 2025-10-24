# Hướng dẫn cấu hình Email Verification

## Cấu hình trong file .env

Thêm các dòng sau vào file .env của bạn:

```env
# Email Configuration for Email Verification
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="BCP System"
```

## Cách lấy App Password cho Gmail:

1. Vào Google Account Settings
2. Security → 2-Step Verification (bật nếu chưa)
3. App passwords → Generate password
4. Copy password và dán vào MAIL_PASSWORD

## Test email verification:

1. Đăng ký tài khoản mới
2. Kiểm tra email để nhận link xác nhận
3. Click vào link để xác nhận
4. Đăng nhập thành công

## Troubleshooting:

- Nếu không nhận được email, kiểm tra spam folder
- Đảm bảo MAIL_HOST và MAIL_PORT đúng
- Kiểm tra MAIL_USERNAME và MAIL_PASSWORD
- Test với MAIL_MAILER=log để debug
