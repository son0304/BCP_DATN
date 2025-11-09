import React from 'react';
import { Link } from 'react-router-dom';

// Định nghĩa lại các màu theo quy tắc đã cung cấp
const PRIMARY_COLOR = "#10B981"; // Emerald Green
const SECONDARY_COLOR = "#F59E0B"; // Amber
const BG_COLOR = "#F9FAFB"; // Trắng Xám (Nền)
const CARD_BG = "#FFFFFF"; // Trắng tinh (Nền Card)
const TEXT_PRIMARY = "text-[#11182C]"; // Đen Than (Tiêu đề)
const TEXT_BODY = "text-[#4B5563]"; // Xám Đậm (Nội dung)
const TEXT_SECONDARY = "text-[#6B7280]"; // Xám Vừa (Text phụ)
const BORDER_COLOR = "#E5E7EB"; // Xám Nhạt (Đường viền)


const Index_Partner = () => {
  return (
    // Màu nền tổng thể: #F9FAFB
    <div style={{ backgroundColor: BG_COLOR }} className="min-h-screen">
      <div className="container mx-auto px-4 py-12 md:py-16 max-w-5xl">

        {/* --- Phần Hero --- */}
        <section className="text-center mb-16">
          {/* Tiêu đề chính (H1 - Hero): text-3xl (Mobile) / text-4xl (Desktop) */}
          <h1 className={`text-3xl md:text-4xl font-extrabold ${TEXT_PRIMARY} mb-4`}>
            Trở thành Đối tác của <span style={{ color: PRIMARY_COLOR }}>Court Prime</span>
          </h1>
          {/* Chữ nội dung (Body/Paragraph): text-base (Mobile/Desktop) - Dùng text-lg cho nổi bật */}
          <p className={`text-base sm:text-lg ${TEXT_BODY} mb-8`}>
            Tối ưu hóa việc quản lý, gia tăng doanh thu và lấp đầy sân trống của bạn một cách hiệu quả nhất.
          </p>
          <Link to={'create_venue'}>
            {/* Nút bấm chính dùng Primary Color */}
            {/* Cỡ chữ nút: text-lg (Nổi bật) */}
            <button
              style={{ backgroundColor: PRIMARY_COLOR }}
              className={`text-white font-bold py-3 px-10 rounded-lg text-lg hover:opacity-90 transition-all shadow-xl`}
            >
              Đăng Ký Làm Chủ Sân
            </button>
          </Link>
        </section>

        {/* --- Phần Lợi ích --- */}
        <section className="mb-16">
          {/* Tiêu đề mục (Section Title): text-xl (Mobile) / text-2xl (Desktop) */}
          <h2 className={`text-xl md:text-2xl font-bold ${TEXT_PRIMARY} text-center mb-10`}>Tại sao nên hợp tác với Court Prime?</h2>
          <div className="grid md:grid-cols-3 gap-8">
            {/* Các Card lợi ích */}
            {['Tăng Trưởng Doanh Thu', 'Quản Lý Thông Minh', 'Marketing & Hỗ Trợ'].map((title, index) => (
              <div key={index}
                className="p-6 rounded-xl shadow-lg border-t-4 transition-transform duration-300 hover:shadow-2xl hover:-translate-y-1"
                style={{ backgroundColor: CARD_BG, borderColor: PRIMARY_COLOR }} // Màu nền Card, Border Primary
              >
                {/* <FaIcon className="text-4xl mb-3" style={{ color: PRIMARY_COLOR }} /> */}

                {/* Tiêu đề thẻ (Card Title): text-lg (Mobile) / text-xl (Desktop) */}
                <h3 className={`text-lg md:text-xl font-semibold mb-3 ${TEXT_PRIMARY}`}>{title}</h3>
                {/* Chữ nội dung (Body/Paragraph): text-base (16px) */}
                <p className={`text-base ${TEXT_BODY}`}>
                  {index === 0 && "Tiếp cận hàng ngàn người chơi mới mỗi ngày, tối ưu hóa lịch đặt và giảm thiểu thời gian sân trống."}
                  {index === 1 && "Cung cấp công cụ quản lý lịch đặt sân, theo dõi doanh thu và nhân viên theo thời gian thực, mọi lúc mọi nơi."}
                  {index === 2 && "Chúng tôi giúp bạn quảng bá hình ảnh sân và hỗ trợ kỹ thuật 24/7, giúp bạn tập trung vào vận hành."}
                </p>
              </div>
            ))}
          </div>
        </section>

        {/* --- Phần Chính sách & Yêu cầu --- */}
        <section className="mb-16">
          {/* Tiêu đề mục (Section Title): text-xl (Mobile) / text-2xl (Desktop) */}
          <h2 className={`text-xl md:text-2xl font-bold ${TEXT_PRIMARY} text-center mb-10`}>Chính sách & Yêu cầu dành cho Đối tác</h2>
          <div className="p-8 rounded-xl shadow-2xl" style={{ backgroundColor: CARD_BG }}>
            <div className="grid md:grid-cols-2 gap-10">

              {/* Cột 1: Yêu cầu */}
              <div>
                {/* Tiêu đề thẻ (Card Title): text-lg (Mobile) / text-xl (Desktop) - Dùng 2XL cho nổi bật */}
                <h3 className={`text-xl md:text-2xl font-bold mb-5`} style={{ color: PRIMARY_COLOR }}>Yêu cầu đối với Chủ sân</h3>
                <ul className={`list-disc list-inside space-y-4 ${TEXT_BODY}`}>
                  {/* Chữ nội dung (Body/Paragraph): text-base (16px) */}
                  <li className="text-base">
                    <strong>Pháp lý:</strong> Có giấy phép kinh doanh (nếu là công ty) hoặc đăng ký hộ kinh doanh cá thể hợp lệ.
                  </li>
                  <li className="text-base">
                    <strong>Chất lượng sân:</strong> Sân đạt tiêu chuẩn thi đấu (mặt sân, ánh sáng, lưới, khu vực chờ...) và đảm bảo an toàn.
                  </li>
                  <li className="text-base">
                    <strong>Hình ảnh:</strong> Cung cấp hình ảnh/video rõ ràng, chân thực về tổng quan và chi tiết cơ sở vật chất của sân.
                  </li>
                  <li className="text-base">
                    <strong>Tiện ích:</strong> Đảm bảo các tiện ích cơ bản cho người chơi (nước uống, khu vực nghỉ, bãi đỗ xe, nhà vệ sinh).
                  </li>
                  <li className="text-base">
                    <strong>Cam kết vận hành:</strong> Cam kết cập nhật đúng tình trạng sân (trống/đã đặt, bảo trì) lên hệ thống của Court Prime.
                  </li>
                </ul>
              </div>

              {/* Cột 2: Chính sách */}
              <div>
                {/* Tiêu đề thẻ (Card Title): text-lg (Mobile) / text-xl (Desktop) - Dùng 2XL cho nổi bật */}
                <h3 className={`text-xl md:text-2xl font-bold mb-5`} style={{ color: PRIMARY_COLOR }}>Chính sách Hợp tác</h3>
                <ul className={`list-disc list-inside space-y-4 ${TEXT_BODY}`}>
                  {/* Chữ nội dung (Body/Paragraph): text-base (16px) */}
                  <li className="text-base">
                    <strong>Phí hoa hồng:</strong> Court Prime áp dụng mức phí hoa hồng cạnh tranh (thường từ 10-20%) trên mỗi lượt đặt sân thành công qua nền tảng.
                  </li>
                  <li className="text-base">
                    <strong>Thanh toán:</strong> Chúng tôi thực hiện đối soát và thanh toán doanh thu tự động qua tài khoản ngân hàng của đối tác định kỳ (ví dụ: vào ngày 5 hàng tháng).
                  </li>
                  <li className="text-base">
                    <strong>Chính sách hủy:</strong> Đối tác và Court Prime tuân thủ quy định chung về hoàn/hủy cho khách hàng để đảm bảo trải nghiệm đồng nhất.
                  </li>
                  <li className="text-base">
                    <strong>Hỗ trợ:</strong> Cung cấp đội ngũ hỗ trợ kỹ thuật và chăm sóc khách hàng 24/7 cho đối tác.
                  </li>
                  <li className="text-base">
                    <strong>Bảo mật:</strong> Cam kết bảo mật mọi thông tin liên quan đến doanh thu và dữ liệu khách hàng của đối tác.
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </section>

        {/* --- Phần Quy trình Đăng ký --- */}
        <section className="mb-16">
          {/* Tiêu đề mục (Section Title): text-xl (Mobile) / text-2xl (Desktop) */}
          <h2 className={`text-xl md:text-2xl font-bold ${TEXT_PRIMARY} text-center mb-10`}>Quy trình Đăng ký Đơn giản</h2>
          <div className="flex flex-col md:flex-row justify-between items-start text-center">
            {/* Bước 1, 2, 3 */}
            {[
              { num: 1, title: 'Đăng ký Form', desc: 'Điền thông tin của bạn và thông tin cơ bản về sân vào form đăng ký trực tuyến.' },
              { num: 2, title: 'Xác minh & Hợp đồng', desc: 'Court Prime sẽ liên hệ, xác minh thông tin, khảo sát sân (nếu cần) và ký hợp đồng hợp tác.' },
              { num: 3, title: 'Lên Sóng & Nhận Khách', desc: 'Sân của bạn xuất hiện trên Court Prime và bắt đầu nhận những lượt đặt sân đầu tiên.' }
            ].map((step, index) => (
              <React.Fragment key={step.num}>
                <div className="flex-1 p-4">
                  {/* Số bước dùng màu Primary, font-size lớn */}
                  <div className="text-5xl md:text-6xl font-extrabold mb-3" style={{ color: PRIMARY_COLOR }}>{step.num}</div>
                  {/* Tiêu đề thẻ (Card Title): text-lg (Mobile) / text-xl (Desktop) */}
                  <h4 className={`text-lg md:text-xl font-semibold mb-2 ${TEXT_PRIMARY}`}>{step.title}</h4>
                  {/* Text phụ: text-sm (14px) - Dùng text-base cho mô tả */}
                  <p className={`text-base ${TEXT_BODY}`}>{step.desc}</p>
                </div>
                {/* Mũi tên chỉ hiển thị trên Desktop */}
                {index < 2 && <div className="text-3xl hidden md:block mx-4" style={{ color: BORDER_COLOR }}>→</div>}
              </React.Fragment>
            ))}
          </div>
        </section>

        {/* --- Phần CTA Cuối --- */}
        <section className="text-center mt-16 p-10 rounded-xl shadow-xl" style={{ backgroundColor: CARD_BG }}>
          {/* Tiêu đề mục (Section Title): text-xl (Mobile) / text-2xl (Desktop) - Dùng 3XL cho CTA nổi bật */}
          <h2 className={`text-2xl md:text-3xl font-bold ${TEXT_PRIMARY} mb-4`}>Sẵn sàng tăng doanh thu cùng Court Prime?</h2>
          {/* Chữ nội dung (Body/Paragraph): text-base (16px) - Dùng text-lg cho nổi bật */}
          <p className={`text-base sm:text-lg ${TEXT_BODY} mb-8`}>Hãy tham gia cộng đồng hàng trăm đối tác chủ sân của chúng tôi ngay hôm nay.</p>
          <Link to={'create_venue'}>
            {/* Nút bấm chính dùng Primary Color */}
            <button
              style={{ backgroundColor: PRIMARY_COLOR }}
              className={`text-white font-bold py-3 px-10 rounded-lg text-lg hover:opacity-90 transition-all shadow-xl`}
            >
              Đăng Ký Ngay
            </button>
          </Link>
        </section>

      </div>
    </div>
  );
}

export default Index_Partner;