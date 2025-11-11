import { useState, useEffect } from "react";
import { useFetchData } from "../../../Hooks/useApi"; // Corrected path
import type { Voucher } from "./Booking_Detail_Venue"; // Đảm bảo type này có 'max_discount_amount'
import type { AxiosError } from "axios";

interface VoucherProps {
  onVoucherApply: (v: Voucher | null) => void;
  totalPrice: number;
}

const Voucher_Detail_Venue: React.FC<VoucherProps> = ({
  onVoucherApply,
  totalPrice,
}) => {
  const [voucherCode, setVoucherCode] = useState<string>("");
  const [currentVoucher, setCurrentVoucher] = useState<Voucher | null>(null);
  const [userInteracted, setUserInteracted] = useState(false);

  const {
    data: singleVoucherData,
    error,
    refetch,
  } = useFetchData<Voucher>(
    'promotions',
    (voucherCode && voucherCode !== currentVoucher?.code) ? { code: voucherCode } : undefined
  );

  const { data: allVouchersData, error: allVouchersError } =
    useFetchData<Voucher[]>('promotions');
  const allVouchers = allVouchersData?.data;

  const axiosError = error as AxiosError<any>;

  const formatPrice = (price: number) =>
    price.toLocaleString("vi-VN", { style: "currency", currency: "VND" });

  // MỚI: Cập nhật hàm tính toán
  const calculateDiscount = (voucher: Voucher, total: number): number => {
    if (voucher.type === '%') {
      const discount = (total * voucher.value) / 100;
      
      // *** LOGIC CHÍNH: Áp dụng giới hạn (max_discount_amount) ***
      if (voucher.max_discount_amount && voucher.max_discount_amount > 0 && discount > voucher.max_discount_amount) {
        return voucher.max_discount_amount;
      }
      return discount;
    }
    
    // Loại 'VND'
    return Math.min(voucher.value, total);
  };

  // useEffect để tự động áp dụng voucher tốt nhất
  useEffect(() => {
    if (
      allVouchers &&
      allVouchers.length > 0 &&
      !userInteracted &&
      !currentVoucher &&
      totalPrice > 0
    ) {
      const vouchersWithDiscount = allVouchers.map((voucher) => ({
        voucher,
        discountAmount: calculateDiscount(voucher, totalPrice), 
      }));

      vouchersWithDiscount.sort((a, b) => b.discountAmount - a.discountAmount);

      const bestVoucher = vouchersWithDiscount[0].voucher;
      const bestDiscount = vouchersWithDiscount[0].discountAmount;

      if (bestDiscount > 0) {
        setCurrentVoucher(bestVoucher);
        setVoucherCode(bestVoucher.code);
        onVoucherApply(bestVoucher);
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
    setUserInteracted(true);
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

  // Xử lý khi nhấn nút "Xóa" (dùng cho nút 'x' mới)
  const handleRemoveVoucher = () => {
    setUserInteracted(true);
    onVoucherApply(null);
    setCurrentVoucher(null);
    setVoucherCode("");
  };

  // Xử lý khi chọn từ dropdown
  const handleSelectVoucher = (e: React.ChangeEvent<HTMLSelectElement>) => {
    setUserInteracted(true);
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
    setUserInteracted(true);
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
      <select
        value={voucherCode}
        onChange={handleSelectVoucher}
        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
      >
        <option value="">Chọn voucher...</option>
        {allVouchers && allVouchers.length > 0 ? (
          allVouchers.map((v) => (
            <option key={v.code} value={v.code}>
              {v.code} - Giảm: {v.type === '%' 
                ? `${v.value}% ${v.max_discount_amount ? `(tối đa ${formatPrice(v.max_discount_amount)})` : ''}` 
                : formatPrice(v.value)}
            </option>
          ))
        ) : (
          <option disabled>
            {allVouchersError ? "Lỗi tải voucher" : "Đang tải..."}
          </option>
        )}
      </select>

      {/* === THAY ĐỔI (1): Bọc input trong div.relative === */}
      <div className="relative flex-1">
        <input
          type="text"
          placeholder="Hoặc nhập mã voucher..."
          value={voucherCode}
          onChange={handleInputChange}
          // Thêm pr-10 để chữ không đè lên nút "x"
          className="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 outline-none"
        />

        {/* === THAY ĐỔI (2): Thêm nút "x" === */}
        {/* Hiện khi có text, và dùng chung hàm 'handleRemoveVoucher' */}
        {voucherCode.length > 0 && (
          <button
            type="button"
            onClick={handleRemoveVoucher}
            className="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-red-500"
            title="Xóa voucher"
          >
            <span className="font-semibold text-lg">✕</span>
          </button>
        )}
      </div>

      {axiosError && (
        <p className="text-red-500">!{axiosError?.response?.data?.message}</p>
      )}

      {currentVoucher && (
        <div className="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
          <p className="text-sm font-semibold text-green-800">
            <i className="fa-solid fa-check-circle mr-2" />
            Mã {currentVoucher.code} đã được lấy thành công
          </p>
          <p className="text-xs text-green-700 mt-1">
            Giảm: {currentVoucher.type === '%' 
              // === SỬA LỖI TYPO TẠI ĐÂY ===
              ? `${currentVoucher.value}% ${currentVoucher.max_discount_amount ? `(tối đa ${formatPrice(currentVoucher.max_discount_amount)})` : ''}` 
              : formatPrice(currentVoucher.value)}
          </p>
        </div>
      )}

      {/* === THAY ĐỔI (3): Bỏ div.flex và nút "Xóa" === */}
      <button
        onClick={handleApply}
        type="button"
        disabled={!voucherCode || !!currentVoucher}
        // Thay flex-1 bằng w-full
        className="w-full px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg transition-all disabled:bg-gray-400 disabled:cursor-not-allowed"
      >
        Áp dụng
      </button>
      {/* Nút "Xóa" đã bị xóa khỏi đây */}

    </div>
  );
};

export default Voucher_Detail_Venue;