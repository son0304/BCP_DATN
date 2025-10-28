# Hệ thống Quản lý Người dùng

## Tổng quan
Hệ thống quản lý người dùng cung cấp đầy đủ các chức năng CRUD (Create, Read, Update, Delete) để quản lý thông tin người dùng trong hệ thống Booking Court Prime.

## Các tính năng chính

### 1. Danh sách người dùng (Index)
- **URL**: `/admin/users`
- **Chức năng**:
  - Hiển thị danh sách tất cả người dùng với pagination
  - Tìm kiếm theo tên, email, số điện thoại
  - Lọc theo vai trò và trạng thái hoạt động
  - Xem chi tiết, chỉnh sửa, xóa người dùng
  - Toggle trạng thái hoạt động của người dùng

### 2. Thêm người dùng mới (Create)
- **URL**: `/admin/users/create`
- **Chức năng**:
  - Form thêm người dùng mới với validation đầy đủ
  - Chọn vai trò, tỉnh/thành phố, quận/huyện
  - Nhập tọa độ GPS (tùy chọn)
  - Thiết lập trạng thái hoạt động

### 3. Chỉnh sửa người dùng (Edit)
- **URL**: `/admin/users/{id}/edit`
- **Chức năng**:
  - Chỉnh sửa thông tin người dùng
  - Cập nhật mật khẩu (tùy chọn)
  - Thay đổi vai trò và địa chỉ
  - Cập nhật trạng thái hoạt động

### 4. Xem chi tiết người dùng (Show)
- **URL**: `/admin/users/{id}`
- **Chức năng**:
  - Hiển thị thông tin chi tiết người dùng
  - Thống kê hoạt động (số lượt đặt sân, địa điểm sở hữu, đánh giá)
  - Danh sách đặt sân gần đây
  - Danh sách địa điểm sở hữu

### 5. Xóa người dùng (Delete)
- **URL**: `/admin/users/{id}` (DELETE)
- **Chức năng**:
  - Xóa người dùng với kiểm tra ràng buộc
  - Không cho phép xóa nếu người dùng có đặt sân hoặc sở hữu địa điểm

### 6. Toggle trạng thái
- **URL**: `/admin/users/{id}/toggle-status` (POST)
- **Chức năng**:
  - Chuyển đổi trạng thái hoạt động/không hoạt động
  - Sử dụng AJAX để cập nhật không cần reload trang

## Cấu trúc Database

### Bảng users
```sql
- id (Primary Key)
- role_id (Foreign Key -> roles.id)
- name (VARCHAR)
- email (VARCHAR, UNIQUE)
- password (HASHED)
- phone (VARCHAR, NULLABLE)
- district_id (Foreign Key -> districts.id, NULLABLE)
- province_id (Foreign Key -> provinces.id, NULLABLE)
- lat (DECIMAL, NULLABLE)
- lng (DECIMAL, NULLABLE)
- is_active (BOOLEAN, DEFAULT: true)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
- deleted_at (TIMESTAMP, SOFT DELETE)
```

## Validation Rules

### StoreUserRequest
- `name`: Bắt buộc, tối đa 255 ký tự
- `email`: Bắt buộc, định dạng email, duy nhất
- `password`: Bắt buộc, tối thiểu 8 ký tự, phải có xác nhận
- `role_id`: Bắt buộc, phải tồn tại trong bảng roles
- `phone`: Tùy chọn, tối đa 20 ký tự
- `province_id`: Tùy chọn, phải tồn tại trong bảng provinces
- `district_id`: Tùy chọn, phải tồn tại trong bảng districts
- `lat`: Tùy chọn, số từ -90 đến 90
- `lng`: Tùy chọn, số từ -180 đến 180
- `is_active`: Boolean

### UpdateUserRequest
- Tương tự StoreUserRequest nhưng:
- `email`: Cho phép trùng với email hiện tại của user
- `password`: Tùy chọn (chỉ cập nhật nếu có nhập)

## API Endpoints

### Districts API
- **GET** `/api/districts?province_id={id}`: Lấy danh sách quận/huyện theo tỉnh/thành phố

## JavaScript Features

### user-management.js
- AJAX toggle status với loading state
- Enhanced delete confirmation
- Auto-submit search form
- Province-District dependency
- Auto-hide alerts sau 5 giây

## Security Features

1. **CSRF Protection**: Tất cả form đều có CSRF token
2. **Input Validation**: Validation đầy đủ cho tất cả input
3. **SQL Injection Protection**: Sử dụng Eloquent ORM
4. **Authorization**: Kiểm tra quyền truy cập
5. **Soft Delete**: Không xóa vĩnh viễn dữ liệu

## Error Handling

- **Database Transactions**: Sử dụng transaction để đảm bảo tính nhất quán
- **Exception Handling**: Bắt và xử lý exception
- **User-friendly Messages**: Thông báo lỗi bằng tiếng Việt
- **Input Validation**: Hiển thị lỗi validation chi tiết

## Performance Optimizations

1. **Eager Loading**: Load relationships để tránh N+1 query
2. **Pagination**: Phân trang cho danh sách lớn
3. **Indexing**: Index trên các trường thường xuyên tìm kiếm
4. **Caching**: Cache danh sách roles, provinces, districts

## Usage Examples

### Tạo người dùng mới
```php
User::create([
    'name' => 'Nguyễn Văn A',
    'email' => 'nguyenvana@example.com',
    'password' => Hash::make('password123'),
    'role_id' => 1,
    'phone' => '0901234567',
    'province_id' => 1,
    'district_id' => 1,
    'is_active' => true
]);
```

### Tìm kiếm người dùng
```php
$users = User::with(['role', 'province', 'district'])
    ->where('name', 'like', '%Nguyễn%')
    ->where('is_active', true)
    ->paginate(15);
```

## Troubleshooting

### Lỗi thường gặp
1. **Email đã tồn tại**: Kiểm tra tính duy nhất của email
2. **Role không tồn tại**: Đảm bảo role_id hợp lệ
3. **Province/District không tồn tại**: Kiểm tra foreign key constraints
4. **Password không khớp**: Kiểm tra password_confirmation

### Debug Tips
1. Kiểm tra logs trong `storage/logs/laravel.log`
2. Sử dụng `dd()` để debug dữ liệu
3. Kiểm tra database constraints
4. Verify CSRF token trong form

