import { useState, useEffect } from "react";
import { useFetchData } from "../../../Hooks/useApi";
import type { Voucher } from "./Booking_Detail_Venue";
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

  const calculateDiscount = (voucher: Voucher, total: number): number => {
    if (voucher.type === '%') {
      const discount = (total * voucher.value) / 100;
      if (voucher.max_discount_amount && voucher.max_discount_amount > 0 && discount > voucher.max_discount_amount) {
        return voucher.max_discount_amount;
      }
      return discount;
    }
    return Math.min(voucher.value, total);
  };

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
  }, [allVouchers, totalPrice, userInteracted, currentVoucher, onVoucherApply]);

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

  const handleRemoveVoucher = () => {
    setUserInteracted(true);
    onVoucherApply(null);
    setCurrentVoucher(null);
    setVoucherCode("");
  };

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
    <div className="flex flex-col gap-3 p-3 bg-gray-50/50 rounded-xl border border-gray-100">
      {/* Title nhỏ */}
      <div className="flex items-center gap-2 text-xs font-semibold text-gray-600 uppercase tracking-wide">
         <i className="fa-solid fa-ticket text-[#10B981]"></i> Mã giảm giá
      </div>

      {/* Select Box */}
      <select
        value={voucherCode}
        onChange={handleSelectVoucher}
        className="w-full px-3 py-2 text-xs md:text-sm border border-gray-200 rounded-lg focus:border-[#10B981] focus:ring-1 focus:ring-[#10B981] outline-none bg-white transition-all cursor-pointer"
      >
        <option value="">-- Chọn mã ưu đãi --</option>
        {allVouchers && allVouchers.length > 0 ? (
          allVouchers.map((v) => (
            <option key={v.code} value={v.code}>
              {v.code} - Giảm {v.type === '%' 
                ? `${v.value}% ${v.max_discount_amount ? `(max ${formatPrice(v.max_discount_amount)})` : ''}` 
                : formatPrice(v.value)}
            </option>
          ))
        ) : (
          <option disabled>
            {allVouchersError ? "Lỗi tải voucher" : "Đang tải..."}
          </option>
        )}
      </select>

      {/* Input Code + Button Apply */}
      <div className="flex gap-2">
        <div className="relative flex-1">
            <input
            type="text"
            placeholder="Nhập mã..."
            value={voucherCode}
            onChange={handleInputChange}
            className="w-full pl-3 pr-8 py-2 text-xs md:text-sm border border-gray-200 rounded-lg focus:border-[#10B981] focus:ring-1 focus:ring-[#10B981] outline-none uppercase placeholder:normal-case transition-all"
            />
            {voucherCode.length > 0 && (
            <button
                type="button"
                onClick={handleRemoveVoucher}
                className="absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400 hover:text-red-500 transition-colors"
                title="Xóa voucher"
            >
                <i className="fa-solid fa-circle-xmark text-xs"></i>
            </button>
            )}
        </div>
        
        <button
            onClick={handleApply}
            type="button"
            disabled={!voucherCode || !!currentVoucher}
            className="px-4 py-2 bg-[#10B981] hover:bg-[#059669] text-white text-xs font-bold rounded-lg transition-all disabled:bg-gray-300 disabled:cursor-not-allowed shadow-sm active:scale-95"
        >
            Áp dụng
        </button>
      </div>

      {/* Error Message */}
      {axiosError && (
        <p className="text-[10px] text-red-500 flex items-center gap-1">
            <i className="fa-solid fa-circle-exclamation"></i>
            {axiosError?.response?.data?.message}
        </p>
      )}

      {/* Success Message Card */}
      {currentVoucher && (
        <div className="p-2.5 bg-green-50/80 border border-green-100 rounded-lg flex items-start gap-2 animate-fade-in">
           <div className="mt-0.5 text-[#10B981]">
              <i className="fa-solid fa-check-circle text-sm" />
           </div>
           <div>
              <p className="text-xs font-bold text-green-800">
                Áp dụng thành công: <span className="uppercase">{currentVoucher.code}</span>
              </p>
              <p className="text-[10px] text-green-700 mt-0.5">
                Đã giảm: {currentVoucher.type === '%' 
                  ? `${currentVoucher.value}% (Tối đa ${currentVoucher.max_discount_amount ? formatPrice(currentVoucher.max_discount_amount) : 'không giới hạn'})` 
                  : formatPrice(currentVoucher.value)}
              </p>
           </div>
        </div>
      )}
    </div>
  );
};

export default Voucher_Detail_Venue;