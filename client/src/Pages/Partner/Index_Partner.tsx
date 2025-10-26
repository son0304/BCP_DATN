import { Link } from 'react-router-dom';


const Index_Partner = () => {
  return (
    <div className="bg-gray-50 min-h-screen">
      <div className="container mx-auto px-4 py-12 md:py-16 max-w-5xl">

        {/* --- Phần Hero --- */}
        <section className="text-center mb-16">
          <h1 className="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
            Trở thành Đối tác của <span className="text-[#348738]">Court Prime</span>
          </h1>
          <p className="text-xl text-gray-600 mb-8">
            Tối ưu hóa việc quản lý, gia tăng doanh thu và lấp đầy sân trống của bạn một cách hiệu quả nhất.
          </p>
          <Link to={'create_venue'}>
            <button className="bg-[#348738] text-white font-bold py-3 px-8 rounded-lg text-lg hover:opacity-90 transition-all shadow-lg" >
              Đăng Ký Làm Chủ Sân
            </button>
          </Link>
        </section>

        {/* --- Phần Lợi ích --- */}
        <section className="mb-16">
          <h2 className="text-3xl font-bold text-center mb-10">Tại sao nên hợp tác với Court Prime?</h2>
          <div className="grid md:grid-cols-3 gap-8">
            {/* Lợi ích 1 */}
            <div className="bg-white p-6 rounded-lg shadow-md border-t-4 border-[#348738]">
              {/* <FaChartLine className="text-4xl text-[#348738] mb-3" /> */}
              <h3 className="text-xl font-semibold mb-2">Tăng Trưởng Doanh Thu</h3>
              <p className="text-gray-700">Tiếp cận hàng ngàn người chơi mới mỗi ngày, tối ưu hóbuttonlịch đặt và giảm thiểu thời gian sân trống.</p>
            </div>
            {/* Lợi ích 2 */}
            <div className="bg-white p-6 rounded-lg shadow-md border-t-4 border-[#348738]">
              {/* <FaTasks className="text-4xl text-[#348738] mb-3" /> */}
              <h3 className="text-xl font-semibold mb-2">Quản Lý Thông Minh</h3>
              <p className="text-gray-700">Cung cấp công cụ quản lý lịch đặt sân, theo dõi doanh thu và nhân viên theo thời gian thực, mọi lúc mọi nơi.</p>
            </div>
            {/* Lợi ích 3 */}
            <div className="bg-white p-6 rounded-lg shadow-md border-t-4 border-[#348738]">
              {/* <FaShieldAlt className="text-4xl text-[#348738] mb-3" /> */}
              <h3 className="text-xl font-semibold mb-2">Marketing & Hỗ Trợ</h3>
              <p className="text-gray-700">Chúng tôi giúp bạn quảng bá hình ảnh sân và hỗ trợ kỹ thuật 24/7, giúp bạn tập trung vào vận hành.</p>
            </div>
          </div>
        </section>

        {/* --- Phần Chính sách & Yêu cầu (Quan trọng nhất) --- */}
        <section className="mb-16">
          <h2 className="text-3xl font-bold text-center mb-10">Chính sách & Yêu cầu dành cho Đối tác</h2>
          <div className="bg-white p-8 rounded-lg shadow-xl">
            <div className="grid md:grid-cols-2 gap-10">

              {/* Cột 1: Yêu cầu */}
              <div>
                <h3 className="text-2xl font-semibold mb-4 text-[#348738]">Yêu cầu đối với Chủ sân</h3>
                <ul className="list-disc list-inside space-y-3 text-gray-700">
                  <li>
                    <strong>Pháp lý:</strong> Có giấy phép kinh doanh (nếu là công ty) hoặc đăng ký hộ kinh doanh cá thể hợp lệ.
                  </li>
                  <li>
                    <strong>Chất lượng sân:</strong> Sân đạt tiêu chuẩn thi đấu (mặt sân, ánh sáng, lưới, khu vực chờ...) và đảm bảo an toàn.
                  </li>
                  <li>
                    <strong>Hình ảnh:</strong> Cung cấp hình ảnh/video rõ ràng, chân thực về tổng quan và chi tiết cơ sở vật chất của sân.
                  </li>
                  <li>
                    <strong>Tiện ích:</strong> Đảm bảo các tiện ích cơ bản cho người chơi (nước uống, khu vực nghỉ, bãi đỗ xe, nhà vệ sinh).
                  </li>
                  <li>
                    <strong>Cam kết vận hành:</strong> Cam kết cập nhật đúng tình trạng sân (trống/đã đặt, bảo trì) lên hệ thống của Court Prime.
                  </li>
                </ul>
              </div>

              {/* Cột 2: Chính sách */}
              <div>
                <h3 className="text-2xl font-semibold mb-4 text-[#348738]">Chính sách Hợp tác</h3>
                <ul className="list-disc list-inside space-y-3 text-gray-700">
                  <li>
                    <strong>Phí hoa hồng:</strong> Court Prime áp dụng mức phí hoa hồng cạnh tranh (thường từ 10-20%) trên mỗi lượt đặt sân thành công qua nền tảng.
                  </li>
                  <li>
                    <strong>Thanh toán:</strong> Chúng tôi thực hiện đối soát và thanh toán doanh thu tự động qua tài khoản ngân hàng của đối tác định kỳ (ví dụ: vào ngày 5 hàng tháng).
                  </li>
                  <li>
                    <strong>Chính sách hủy:</strong> Đối tác và Court Prime tuân thủ quy định chung về hoàn/hủy cho khách hàng để đảm bảo trải nghiệm đồng nhất.
                  </li>
                  <li>
                    <strong>Hỗ trợ:</strong> Cung cấp đội ngũ hỗ trợ kỹ thuật và chăm sóc khách hàng 24/7 cho đối tác.
                  </li>
                  <li>
                    <strong>Bảo mật:</strong> Cam kết bảo mật mọi thông tin liên quan đến doanh thu và dữ liệu khách hàng của đối tác.
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </section>

        {/* --- Phần Quy trình Đăng ký --- */}
        <section className="mb-16">
          <h2 className="text-3xl font-bold text-center mb-10">Quy trình Đăng ký Đơn giản</h2>
          <div className="flex flex-col md:flex-row justify-between items-center text-center">
            {/* Bước 1 */}
            <div className="flex-1 p-4">
              <div className="text-5xl font-bold text-[#348738] mb-3">1</div>
              <h4 className="text-xl font-semibold mb-2">Đăng ký Form</h4>
              <p className="text-gray-600">Điền thông tin của bạn và thông tin cơ bản về sân vào form đăng ký trực tuyến.</p>
            </div>

            {/* Mũi tên */}
            <div className="text-3xl text-gray-300 hidden md:block mx-4">→</div>

            {/* Bước 2 */}
            <div className="flex-1 p-4">
              <div className="text-5xl font-bold text-[#348738] mb-3">2</div>
              <h4 className="text-xl font-semibold mb-2">Xác minh & Hợp đồng</h4>
              <p className="text-gray-600">Court Prime sẽ liên hệ, xác minh thông tin, khảo sát sân (nếu cần) và ký hợp đồng hợp tác.</p>
            </div>

            {/* Mũi tên */}
            <div className="text-3xl text-gray-300 hidden md:block mx-4">→</div>

            {/* Bước 3 */}
            <div className="flex-1 p-4">
              <div className="text-5xl font-bold text-[#348738] mb-3">3</div>
              <h4 className="text-xl font-semibold mb-2">Lên Sóng & Nhận Khách</h4>
              <p className="text-gray-600">Sân của bạn xuất hiện trên Court Prime và bắt đầu nhận những lượt đặt sân đầu tiên.</p>
            </div>
          </div>
        </section>

        {/* --- Phần CTA Cuối --- */}
        <section className="text-center mt-16 bg-white p-10 rounded-lg shadow-lg">
          <h2 className="text-3xl font-bold text-gray-800 mb-4">Sẵn sàng tăng doanh thu cùng Court Prime?</h2>
          <p className="text-lg text-gray-600 mb-8">Hãy tham gia cộng đồng hàng trăm đối tác chủ sân của chúng tôi ngay hôm nay.</p>
          <Link to={'create_venue'}>
            <button className="bg-[#348738] text-white font-bold py-3 px-8 rounded-lg text-lg hover:opacity-90 transition-all shadow-lg" >
              Đăng Ký Ngay
            </button>
          </Link>
        </section>

      </div>
    </div>
  );
}

export default Index_Partner;