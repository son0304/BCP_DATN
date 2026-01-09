import React, { useState, useEffect, useRef } from "react";

// --- INTERFACES ---
export interface Voucher {
  id: number;
  code: string;
  description: string;
  type: 'percentage' | 'fixed';
  value: string;
  max_discount_amount: number;
  min_order_value: number;
  usage_limit: number;
  used_count: number;
  target_user_type: 'new_user' | 'all' | 'old_user';
  process_status: 'active' | 'inactive' | 'expired';
  start_at: string;
  end_at: string;
  creator_user_id: number;
  venue_id: number;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
}

interface VoucherProps {
  availableVouchers: Voucher[];
  onVoucherApply: (v: Voucher | null) => void;
  totalPrice: number;
}

const Voucher_Detail_Venue: React.FC<VoucherProps> = ({
  availableVouchers = [],
  onVoucherApply,
  totalPrice
}) => {
  // State
  const [voucherCode, setVoucherCode] = useState<string>("");
  const [currentVoucher, setCurrentVoucher] = useState<Voucher | null>(null);
  const [userInteracted, setUserInteracted] = useState(false);
  const [manualError, setManualError] = useState<string | null>(null);

  // State cho Custom Dropdown
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const dropdownRef = useRef<HTMLDivElement>(null);

  // Helper format tiền
  const formatPrice = (price: number) =>
    price.toLocaleString("vi-VN", { style: "currency", currency: "VND" });

  // Xử lý click outside để đóng dropdown
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsDropdownOpen(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  // --- 1. CHECK HỢP LỆ ---
  const isValidVoucher = (voucher: Voucher, total: number) => {
    // 1.1 Check trạng thái
    if (voucher.process_status !== 'active') return false;

    // 1.2 Check thời gian
    const now = new Date();
    const start = new Date(voucher.start_at);
    const end = new Date(voucher.end_at);
    if (now < start || now > end) return false;

    // 1.3 Check Min Order
    if (voucher.min_order_value > 0 && total < voucher.min_order_value) {
      return false;
    }

    // 1.4 Check Usage Limit
    if (voucher.usage_limit > 0 && voucher.used_count >= voucher.usage_limit) {
      return false;
    }

    return true;
  };

  // --- 2. TÍNH TOÁN (CORE LOGIC) ---
  const calculateDiscount = (voucher: Voucher, total: number): number => {
    if (!isValidVoucher(voucher, total)) return 0;

    const valNum = parseFloat(voucher.value);
    let discount = 0;

    if (voucher.type === 'percentage') {
      discount = (total * valNum) / 100;
      if (voucher.max_discount_amount > 0 && discount > voucher.max_discount_amount) {
        discount = voucher.max_discount_amount;
      }
    } else {
      discount = valNum;
    }
    return Math.min(discount, total);
  };

  // --- 3. AUTO APPLY BEST VOUCHER ---
  useEffect(() => {
    if (availableVouchers.length > 0 && !userInteracted && !currentVoucher && totalPrice > 0) {
      const validVouchers = availableVouchers.filter(v => isValidVoucher(v, totalPrice));
      if (validVouchers.length === 0) return;

      const vouchersWithDiscount = validVouchers.map((voucher) => ({
        voucher,
        discountAmount: calculateDiscount(voucher, totalPrice),
      }));

      vouchersWithDiscount.sort((a, b) => b.discountAmount - a.discountAmount);

      const bestOption = vouchersWithDiscount[0];
      if (bestOption && bestOption.discountAmount > 0) {
        setCurrentVoucher((prev) => {
          if (prev?.id === bestOption.voucher.id) return prev;
          setVoucherCode(bestOption.voucher.code);
          onVoucherApply(bestOption.voucher);
          return bestOption.voucher;
        });
      }
    } else if (totalPrice === 0 && currentVoucher) {
      handleRemoveVoucher();
    }
  }, [availableVouchers, totalPrice, userInteracted, onVoucherApply, currentVoucher]);

  // --- HANDLERS ---
  const handleRemoveVoucher = (e?: React.MouseEvent) => {
    if (e) e.stopPropagation();
    setUserInteracted(true);
    onVoucherApply(null);
    setCurrentVoucher(null);
    setVoucherCode("");
    setManualError(null);
  };

  // Handler khi chọn từ Dropdown mới
  const handleSelectFromDropdown = (voucher: Voucher) => {
    setUserInteracted(true);
    setManualError(null);

    // Nếu click lại voucher đang chọn -> Bỏ chọn
    if (currentVoucher?.id === voucher.id) {
      handleRemoveVoucher();
      setIsDropdownOpen(false);
      return;
    }

    if (isValidVoucher(voucher, totalPrice)) {
      setVoucherCode(voucher.code);
      setCurrentVoucher(voucher);
      onVoucherApply(voucher);
      setIsDropdownOpen(false);
    } else {
      // Không cho chọn nếu không hợp lệ, nhưng update input code để user biết
      setVoucherCode(voucher.code);
      setManualError(`Đơn tối thiểu ${formatPrice(voucher.min_order_value)}`);
      // Không đóng dropdown để user xem lỗi
    }
  };

  // Handler Input thủ công (Giữ nguyên)
  const handleApplyManual = () => {
    setUserInteracted(true);
    const trimmedCode = voucherCode.trim().toUpperCase();
    if (!trimmedCode) return;

    const found = availableVouchers.find(v => v.code === trimmedCode);

    if (found) {
      if (isValidVoucher(found, totalPrice)) {
        setCurrentVoucher(found);
        onVoucherApply(found);
        setManualError(null);
      } else {
        setManualError(`Chưa đủ điều kiện: Min ${formatPrice(found.min_order_value)}`);
        setCurrentVoucher(null);
        onVoucherApply(null);
      }
    } else {
      setManualError("Mã không hợp lệ hoặc không áp dụng cho sân này");
      setCurrentVoucher(null);
      onVoucherApply(null);
    }
  };

  return (
    <div className="flex flex-col gap-4 p-4 bg-white rounded-xl border border-gray-100 shadow-sm" ref={dropdownRef}>

      {/* Title */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-2 text-sm font-bold text-gray-800">
          <i className="fa-solid fa-ticket text-[#10B981]"></i> Mã giảm giá
        </div>
        {currentVoucher && (
          <span className="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">
            Đã áp dụng
          </span>
        )}
      </div>

      {/* --- CUSTOM DROPDOWN (Thay thế thẻ SELECT) --- */}
      <div className="relative">
        <div
          onClick={() => setIsDropdownOpen(!isDropdownOpen)}
          className={`
                w-full px-4 py-3 rounded-xl border cursor-pointer flex justify-between items-center transition-all bg-white
                ${isDropdownOpen ? 'border-[#10B981] ring-2 ring-[#10B981]/10' : 'border-gray-200 hover:border-[#10B981]'}
            `}
        >
          {currentVoucher ? (
            <div className="flex items-center gap-3 overflow-hidden">
              <div className="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center text-[#10B981] flex-shrink-0">
                <i className="fa-solid fa-check"></i>
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-bold text-gray-800 truncate">{currentVoucher.code}</p>
                <p className="text-xs text-gray-500 truncate">
                  {currentVoucher.type === 'percentage'
                    ? `Giảm ${parseFloat(currentVoucher.value)}%`
                    : `Giảm ${formatPrice(parseFloat(currentVoucher.value))}`}
                </p>
              </div>
            </div>
          ) : (
            <span className="text-gray-400 text-sm">-- Chọn mã ưu đãi --</span>
          )}

          <div className="flex items-center gap-2 ml-2">
            {currentVoucher && (
              <div
                onClick={handleRemoveVoucher}
                className="w-6 h-6 rounded-full hover:bg-red-50 text-gray-300 hover:text-red-500 flex items-center justify-center transition-colors z-10"
              >
                <i className="fa-solid fa-xmark text-sm"></i>
              </div>
            )}
            <i className={`fa-solid fa-chevron-down text-xs text-gray-400 transition-transform duration-200 ${isDropdownOpen ? 'rotate-180' : ''}`}></i>
          </div>
        </div>

        {/* LIST VOUCHER XỔ XUỐNG */}
        {isDropdownOpen && (
          <div className="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-100 rounded-xl shadow-xl z-50 max-h-[300px] overflow-y-auto custom-scrollbar animate-fadeIn">
            <div className="p-2 space-y-2">
              {availableVouchers.length > 0 ? availableVouchers.map((v) => {
                const isValid = isValidVoucher(v, totalPrice);
                const isSelected = currentVoucher?.id === v.id;
                const valNum = parseFloat(v.value);

                const discountText = v.type === 'percentage'
                  ? `${valNum}%`
                  : formatPrice(valNum);

                return (
                  <div
                    key={v.id}
                    onClick={() => isValid && handleSelectFromDropdown(v)}
                    className={`
                                    relative flex items-start gap-3 p-3 rounded-lg border transition-all duration-200 group
                                    ${isSelected
                        ? 'bg-[#10B981]/5 border-[#10B981] cursor-pointer'
                        : isValid
                          ? 'bg-white border-transparent hover:bg-gray-50 hover:border-gray-200 cursor-pointer hover:shadow-sm'
                          : 'bg-gray-50 border-transparent opacity-60 cursor-not-allowed'
                      }
                                `}
                  >
                    {/* Left Icon */}
                    <div className={`
                                    flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-lg border border-dashed
                                    ${isSelected || isValid ? 'border-green-300 text-[#10B981] bg-white' : 'border-gray-300 text-gray-400'}
                                `}>
                      <i className="fa-solid fa-ticket"></i>
                    </div>

                    {/* Content */}
                    <div className="flex-1 min-w-0">
                      <div className="flex justify-between items-start">
                        <h4 className={`text-sm font-bold truncate ${isSelected ? 'text-[#10B981]' : 'text-gray-800'}`}>
                          {v.code}
                        </h4>
                        {v.target_user_type === 'new_user' && (
                          <span className="text-[10px] bg-orange-100 text-orange-600 px-1.5 py-0.5 rounded font-bold">MỚI</span>
                        )}
                      </div>

                      <p className="text-xs text-gray-600 font-medium mt-0.5">Giảm {discountText}</p>

                      {/* Error Message Inline */}
                      {!isValid && (
                        <p className="text-[10px] text-red-500 mt-1 flex items-center gap-1">
                          <i className="fa-solid fa-circle-exclamation"></i>
                          Min đơn: {formatPrice(v.min_order_value)}
                        </p>
                      )}
                    </div>

                    {isSelected && (
                      <div className="absolute top-3 right-3 text-[#10B981]">
                        <i className="fa-solid fa-check-circle"></i>
                      </div>
                    )}
                  </div>
                )
              }) : (
                <div className="text-center py-6 text-gray-400 text-sm">
                  <i className="fa-regular fa-folder-open text-xl mb-2 block"></i>
                  Không có mã giảm giá nào
                </div>
              )}
            </div>
          </div>
        )}
      </div>

      {/* --- MANUAL INPUT (Nhập tay) --- */}
      <div className="flex gap-2 pt-2 border-t border-gray-100">
        <div className="relative flex-1">
          <input
            type="text"
            placeholder="Nhập mã khác..."
            value={voucherCode}
            onChange={(e) => setVoucherCode(e.target.value.toUpperCase())}
            onKeyDown={(e) => e.key === 'Enter' && handleApplyManual()}
            className="w-full pl-3 pr-8 py-2.5 text-sm border border-gray-200 rounded-lg focus:border-[#10B981] focus:ring-1 focus:ring-[#10B981] outline-none uppercase bg-gray-50 focus:bg-white transition-all"
          />
        </div>

        <button
          onClick={handleApplyManual}
          type="button"
          disabled={!voucherCode}
          className="px-5 py-2.5 bg-gray-900 hover:bg-[#10B981] text-white text-sm font-bold rounded-lg transition-all disabled:bg-gray-300 disabled:cursor-not-allowed shadow-sm"
        >
          Áp dụng
        </button>
      </div>

      {/* --- ERROR MESSAGE --- */}
      {manualError && (
        <div className="p-2 bg-red-50 text-red-600 text-xs rounded-lg flex items-center gap-2 animate-pulse">
          <i className="fa-solid fa-circle-exclamation"></i>
          {manualError}
        </div>
      )}

      {/* --- SUCCESS SUMMARY (Chi tiết giảm giá) --- */}
      {currentVoucher && !manualError && (
        <div className="p-3 bg-[#10B981]/10 border border-[#10B981]/20 rounded-lg flex flex-col gap-1 animate-fadeIn">
          <div className="flex justify-between items-center">
            <span className="text-xs font-bold text-gray-600">Mã: {currentVoucher.code}</span>
            <span className="text-xs font-bold text-[#10B981]">
              -{formatPrice(calculateDiscount(currentVoucher, totalPrice))}
            </span>
          </div>
          <p className="text-[10px] text-gray-500">
            {currentVoucher.description || "Đã áp dụng mã giảm giá thành công"}
          </p>
          {currentVoucher.max_discount_amount > 0 && currentVoucher.type === 'percentage' && (
            <p className="text-[10px] text-orange-600">
              *Tối đa: {formatPrice(currentVoucher.max_discount_amount)}
            </p>
          )}
        </div>
      )}
    </div>
  );
};

export default Voucher_Detail_Venue;