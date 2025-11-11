import { useState, useEffect } from "react"; // Thêm useEffect
import { useFetchData } from "../../../hooks/useApi";
import type { Voucher } from "./Booking_Detail_Venue";
import type { AxiosError } from "axios";

interface VoucherProps {
  onVoucherApply: (v: Voucher | null) => void;
  totalPrice: number; // MỚI: Cần tổng giá để tính voucher tốt nhất
}

const Voucher_Detail_Venue: React.FC<VoucherProps> = ({
  onVoucherApply,
  totalPrice, // Prop mới
}) => {
  const [voucherCode, setVoucherCode] = useState<string>("");
  const [currentVoucher, setCurrentVoucher] = useState<Voucher | null>(null);
  // MỚI: Theo dõi xem người dùng đã tương tác (chọn, gõ, xóa) chưa
  const [userInteracted, setUserInteracted] = useState(false);

  // Hook để lấy một voucher duy nhất bằng mã (dành cho nút "Áp dụng")
  const {
    data: singleVoucherData,
    error,
    refetch,
  } = useFetchData<Voucher>(
    'promotions',
    (voucherCode && voucherCode !== currentVoucher?.code) ? { code: voucherCode } : undefined
  );

  // Hook để lấy TẤT CẢ voucher
  const { data: allVouchersData, error: allVouchersError } =
    useFetchData<Voucher[]>('promotions');
  const allVouchers = allVouchersData?.data;

  const axiosError = error as AxiosError<any>;

  const formatPrice = (price: number) =>
    price.toLocaleString("vi-VN", { style: "currency", currency: "VND" });

  // MỚI: Hàm trợ giúp tính toán giá trị giảm giá thực tế
  const calculateDiscount = (voucher: Voucher, total: number): number => {
    if (voucher.type === '%') {
      // Giả sử không có max_discount, nếu có, bạn cần thêm logic đó ở đây
      const discount = (total * voucher.value) / 100;
      return discount;
    }
    // Loại 'VND'
    // Đảm bảo giảm giá không vượt quá tổng tiền
    return Math.min(voucher.value, total);
  };

  // MỚI: useEffect để tự động áp dụng voucher tốt nhất
  useEffect(() => {
    // Chỉ chạy nếu:
    // 1. Đã tải xong danh sách voucher
    // 2. Có ít nhất 1 voucher
    // 3. Người dùng CHƯA tương tác
    // 4. Chưa có voucher nào được áp dụng
    // 5. Có tổng giá > 0
    if (
      allVouchers &&
      allVouchers.length > 0 &&
      !userInteracted &&
      !currentVoucher &&
      totalPrice > 0
    ) {
      // 1. Tính toán chiết khấu cho mỗi voucher
      const vouchersWithDiscount = allVouchers.map((voucher) => ({
        voucher,
        discountAmount: calculateDiscount(voucher, totalPrice),
      }));

      // 2. Sắp xếp để tìm chiết khấu cao nhất
      vouchersWithDiscount.sort((a, b) => b.discountAmount - a.discountAmount);

      // 3. Lấy voucher tốt nhất
      const bestVoucher = vouchersWithDiscount[0].voucher;
      const bestDiscount = vouchersWithDiscount[0].discountAmount;

      // 4. Chỉ áp dụng nếu chiết khấu có ý nghĩa (lớn hơn 0)
      if (bestDiscount > 0) {
        setCurrentVoucher(bestVoucher);
        setVoucherCode(bestVoucher.code); // Đồng bộ UI (input và select)
        onVoucherApply(bestVoucher);
        // Không set userInteracted = true ở đây, vì đây là hành động tự động
      }
    }
  }, [
    allVouchers,
    totalPrice,
    userInteracted,
    currentVoucher,
    onVoucherApply,
  ]);

  // Xử lý khi nhấn nút "Áp dụng"
  const handleApply = async () => {
    setUserInteracted(true); // Đánh dấu người dùng tương tác
    if (!voucherCode.trim() || !!currentVoucher) return;

    const result = await refetch();

    if (result?.data?.data) {
      const fetchedVoucher = result.data.data;
      setCurrentVoucher(fetchedVoucher);
      onVoucherApply(fetchedVoucher);
    } else {
      setCurrentVoucher(null);
      onVoucherApply(null);
    }
  };

  // Xử lý khi nhấn nút "Xóa"
  const handleRemoveVoucher = () => {
    setUserInteracted(true); // Đánh dấu người dùng tương tác
    onVoucherApply(null);
    setCurrentVoucher(null);
    setVoucherCode("");
  };

  // Xử lý khi chọn từ dropdown
  const handleSelectVoucher = (e: React.ChangeEvent<HTMLSelectElement>) => {
    setUserInteracted(true); // Đánh dấu người dùng tương tác
    const selectedCode = e.target.value;
    setVoucherCode(selectedCode);

    if (!selectedCode) {
      setCurrentVoucher(null);
      onVoucherApply(null);
      return;
    }

    const selectedVoucher = allVouchers?.find((v) => v.code === selectedCode);
    if (selectedVoucher) {
      setCurrentVoucher(selectedVoucher);
      onVoucherApply(selectedVoucher);
    }
  };

  // Xử lý khi nhập text vào input
  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setUserInteracted(true); // Đánh dấu người dùng tương tác
    const code = e.target.value;
    setVoucherCode(code);

    if (code !== currentVoucher?.code) {
      if (currentVoucher) {
        setCurrentVoucher(null);
        onVoucherApply(null);
      }
    }
  };

  return (
    <div className="flex flex-col gap-2">
      {/* MỚI: Select Dropdown */}
      <select
        value={voucherCode} // Liên kết giá trị với state
        onChange={handleSelectVoucher}
        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
      >
        <option value="">Chọn voucher...</option>
        {allVouchers && allVouchers.length > 0 ? (
          allVouchers.map((v) => (
            <option key={v.code} value={v.code}>
              {/* Hiển thị thông tin voucher rõ ràng */}
              {v.code} - Giảm: {v.type === '%' ? `${v.value}%` : formatPrice(v.value)}
            </option>
          ))
        ) : (
          <option disabled>
            {allVouchersError ? "Lỗi tải voucher" : "Đang tải..."}
          </option>
        )}
      </select>

      <input
        type="text"
        placeholder="Hoặc nhập mã voucher..." // Cập nhật placeholder
        value={voucherCode}
        onChange={handleInputChange} // Sử dụng handler mới
        className="flex-1 px-3 py-2 border border-gray-300 rounded-lg placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 outline-none"
      />

      {/* Chỉ hiển thị lỗi từ việc áp dụng thủ công */}
      {axiosError && (
        <p className="text-red-500">!{axiosError?.response?.data?.message}</p>
      )}

      {/* Thông báo thành công giờ đây dựa trên state `currentVoucher` */}
      {currentVoucher && (
        <div className="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
          <p className="text-sm font-semibold text-green-800">
            <i className="fa-solid fa-check-circle mr-2" />
            Mã {currentVoucher.code} đã được lấy thành công
          </p>
          <p className="text-xs text-green-700 mt-1">
            Giảm: {currentVoucher.type === '%' ? `${currentVoucher.value}%` : formatPrice(currentVoucher.value)}
          </p>
        </div>
      )}

      <div className="flex gap-2">
        <button
          onClick={handleApply}
          type="button"
          // Vô hiệu hóa nếu không có mã hoặc nếu mã đó đã được áp dụng
          disabled={!voucherCode || !!currentVoucher}
          className="flex-1 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg transition-all disabled:bg-gray-400 disabled:cursor-not-allowed"
        >
          Áp dụng
        </button>
        <button
          onClick={handleRemoveVoucher}
          type="button"
          className="flex-1 px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition-all"
        >
          Xóa
        </button>
      </div>
    </div>
  );
};

export default Voucher_Detail_Venue;