import { useState } from "react";
import { useFetchData } from "../../../Hooks/useApi";
import type { Voucher } from "./Booking_Detail_Venue";
import type { AxiosError } from "axios";

interface VoucherProps {
  onVoucherApply: (v: Voucher | null) => void;
}

const Voucher_Detail_Venue: React.FC<VoucherProps> = ({ onVoucherApply }) => {
  const [voucherCode, setVoucherCode] = useState<string>("");
  const { data, error, refetch } = useFetchData<Voucher>(
    'promotions',
    voucherCode ? { code: voucherCode } : undefined
  );

  const handleApply = async () => {
    if (!voucherCode.trim()) return;
    const result = await refetch();
    if (result?.data?.data) {
      onVoucherApply(result.data.data);
    }
  };

  const handleRemoveVoucher = () => {
    onVoucherApply(null);
    setVoucherCode("");
  };

  const voucher = data?.data;
  const axiosError = error as AxiosError<any>;

  const formatPrice = (price: number) =>
    price.toLocaleString("vi-VN", { style: "currency", currency: "VND" });

  return (
    <div className="flex flex-col gap-2">
      <input
        type="text"
        placeholder="Nhập mã voucher..."
        value={voucherCode}
        onChange={(e) => setVoucherCode(e.target.value)}
        className="flex-1 px-3 py-2 border border-gray-300 rounded-lg placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 outline-none"
      />
      {axiosError && (
        <p className="text-red-500">!{axiosError?.response?.data?.message}</p>
      )}
      {voucher && (
        <div className="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
          <p className="text-sm font-semibold text-green-800">
            <i className="fa-solid fa-check-circle mr-2" />
            Mã {voucher.code} đã được áp dụng
          </p>
          <p className="text-xs text-green-700 mt-1">
            Giảm: {voucher.type === '%' ? `${voucher.value}%` : formatPrice(voucher.value)}
          </p>
        </div>
      )}
      <div className="flex gap-2">
        <button
          onClick={handleApply}
          type="button"
          className="flex-1 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg transition-all"
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
