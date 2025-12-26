import React, { useState, useMemo } from 'react';
import { useNavigate } from 'react-router-dom';

// Import Components
import Order_Booking, { type SelectedItem } from './Order_Booking';
import Order_Service, { type ServiceItem } from './Order_Service';
import { useNotification } from '../../../../Components/Notification';
import { usePostData } from '../../../../Hooks/useApi';
import type { ApiResponse } from '../../../../Types/api';
import type { User } from '../../../../Types/user';
import Voucher_Detail_Venue from '../Voucher_Detail_Venue';

// --- TYPES ---
export type Voucher = {
    id: number;
    code: string;
    value: number;
    type: '%' | 'VND';
    start_at: string;
    expires_at: string | null;
    max_discount_amount: number | null;
};

const Order_Container = ({ id }: { id: any }) => {
    const navigate = useNavigate();
    const { showNotification } = useNotification();
    const { mutate } = usePostData<ApiResponse<number>, any>('tickets');

    // Lấy user an toàn hơn
    const userRaw = localStorage.getItem('user');
    const user = userRaw ? JSON.parse(userRaw) as User : null;

    // --- STATE ---
    const [selectedItems, setSelectedItems] = useState<SelectedItem[]>([]);
    const [selectedServices, setSelectedServices] = useState<ServiceItem[]>([]);
    const [selectedVoucher, setSelectedVoucher] = useState<Voucher | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [refreshTrigger, setRefreshTrigger] = useState(0); 


    // --- LOGIC TÍNH TIỀN ---
    const { rawTotalPrice, finalPrice, discountAmount } = useMemo(() => {
        const bookingTotal = selectedItems.reduce((sum, item) => {
            const effectivePrice = (item.sale_price && item.sale_price > 0) ? item.sale_price : item.price;
            return sum + Number(effectivePrice);
        }, 0);

        const serviceTotal = selectedServices.reduce((sum, item) => {
            return sum + (Number(item.price) * item.quantity);
        }, 0);

        const total = bookingTotal + serviceTotal;
        let discount = 0;

        if (selectedVoucher) {
            const now = new Date();
            if (!selectedVoucher.expires_at || new Date(selectedVoucher.expires_at) >= now) {
                if (selectedVoucher.type === '%') {
                    discount = (total * selectedVoucher.value) / 100;
                    if (selectedVoucher.max_discount_amount && discount > selectedVoucher.max_discount_amount) {
                        discount = selectedVoucher.max_discount_amount;
                    }
                } else {
                    discount = selectedVoucher.value;
                }
            }
        }
        const validDiscount = Math.min(discount, total);

        return {
            rawTotalPrice: total,
            discountAmount: validDiscount,
            finalPrice: Math.max(0, total - validDiscount)
        };
    }, [selectedItems, selectedServices, selectedVoucher]);

    const formatPrice = (price: number | string) =>
        Number(price).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });

    // --- SUBMIT ---
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!user || !user.id) return showNotification('Vui lòng đăng nhập để đặt sân.', 'error');
        if (selectedItems.length === 0) return showNotification('Vui lòng chọn ít nhất 1 khung giờ.', 'error');

        const hasPastItem = selectedItems.some(item => {
            const slotDate = new Date(`${item.date}T${item.start_time}`);
            return slotDate < new Date();
        });
        if (hasPastItem) return showNotification('Có khung giờ đã quá hạn.', 'error');

        setIsSubmitting(true);

        const payload = {
            user_id: user.id,
            venue_id: id,
            promotion_id: selectedVoucher?.id || null,
            discount_amount: discountAmount,
            total_amount: finalPrice,
            bookings: selectedItems.map((item) => ({
                court_id: item.court_id,
                time_slot_id: item.time_slot_id,
                date: item.date,
                unit_price: item.price,
                sale_price: item.sale_price,
            })),
            services: selectedServices.map(s => ({
                venue_service_id: s.id,
                quantity: s.quantity,
                price: s.price
            }))
        };

        console.log('Payload đặt sân:', payload);


        mutate(payload, {
            onSuccess: (res) => {
                if (res.success) {
                    showNotification('Đặt sân thành công!', 'success');
                    setRefreshTrigger(prev => prev + 1); 
                    setSelectedItems([]); 
                    navigate(`/booking/${res.data}`);
                } else {
                    showNotification(res.message || 'Đặt sân thất bại.', 'error');
                }
            },
            onError: () => showNotification('Lỗi kết nối server.', 'error'),
            onSettled: () => setIsSubmitting(false),
        });
    };

    // --- UI RENDER (LÀM LẠI LAYOUT) ---
    return (
        <div className="bg-gray-50 min-h-screen py-8">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                {/* PAGE HEADER */}
                <div className="mb-8">
                    <h1 className="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <span className="bg-[#10B981] text-white p-2 rounded-lg">
                            <i className="fa-solid fa-calendar-check"></i>
                        </span>
                        Đặt Sân Trực Tuyến
                    </h1>
                    <p className="text-gray-500 text-sm mt-1 ml-11">Chọn lịch và dịch vụ mong muốn</p>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    {/* --- CỘT TRÁI (CHIẾM 8 PHẦN): BOOKING & SERVICE --- */}
                    <div className="lg:col-span-8 space-y-8">

                        {/* 1. SECTION BOOKING */}
                        <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <Order_Booking
                                id={id}
                                selectedItems={selectedItems}
                                onChange={setSelectedItems}
                                refreshTrigger={refreshTrigger} // 4. Truyền xuống cho con

                            />
                        </div>

                        {/* 2. SECTION SERVICE */}
                        <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <Order_Service
                                venueId={id}
                                selectedServices={selectedServices}
                                onChange={setSelectedServices}
                                refreshTrigger={refreshTrigger} 

                            />
                        </div>
                    </div>

                    {/* --- CỘT PHẢI (CHIẾM 4 PHẦN): CHECKOUT (STICKY) --- */}
                    <div className="lg:col-span-4 sticky top-24 self-start">
                        <div className="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">

                            {/* Header Checkout */}
                            <div className="bg-gray-900 px-5 py-4 flex justify-between items-center">
                                <h3 className="text-white font-bold text-base flex items-center gap-2">
                                    <i className="fa-solid fa-receipt text-[#10B981]"></i> Thông tin thanh toán
                                </h3>
                                <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded-full">
                                    {selectedItems.length} slots
                                </span>
                            </div>

                            <div className="p-5">
                                {/* LIST BOOKINGS */}
                                {selectedItems.length > 0 ? (
                                    <div className="mb-4">
                                        <div className="text-xs font-bold text-gray-400 uppercase mb-2 tracking-wider">Lịch đặt sân</div>
                                        <div className="space-y-2 max-h-[200px] overflow-y-auto custom-scrollbar pr-2">
                                            {selectedItems.map((item, idx) => {
                                                const effectivePrice = (item.sale_price && item.sale_price > 0) ? item.sale_price : item.price;
                                                return (
                                                    <div key={idx} className="flex justify-between items-center text-sm p-2 rounded-lg bg-gray-50 border border-gray-100 group hover:border-[#10B981] transition-all">
                                                        <div>
                                                            <div className="font-semibold text-gray-800">{item.court_name}</div>
                                                            <div className="text-xs text-gray-500">{item.date} | {item.start_time.slice(0, 5)} - {item.end_time.slice(0, 5)}</div>
                                                        </div>
                                                        <div className="text-right">
                                                            {Number(item.sale_price) > 0 && (
                                                                <div className="text-[10px] text-gray-400 line-through">{formatPrice(item.price)}</div>
                                                            )}
                                                            <div className={`font-bold ${Number(item.sale_price) > 0 ? 'text-red-500' : 'text-gray-800'}`}>
                                                                {formatPrice(effectivePrice)}
                                                            </div>
                                                        </div>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    </div>
                                ) : (
                                    <div className="text-center py-6 border-2 border-dashed border-gray-100 rounded-lg mb-4">
                                        <p className="text-gray-400 text-sm">Chưa chọn lịch nào</p>
                                    </div>
                                )}

                                {/* LIST SERVICES */}
                                {selectedServices.length > 0 && (
                                    <div className="mb-4 pt-4 border-t border-gray-100">
                                        <div className="text-xs font-bold text-gray-400 uppercase mb-2 tracking-wider">Dịch vụ đi kèm</div>
                                        <div className="space-y-2">
                                            {selectedServices.map((item, idx) => (
                                                <div key={`s-${idx}`} className="flex justify-between items-center text-sm p-2 rounded-lg bg-green-50 border border-green-100 text-green-800">
                                                    <div className="flex items-center gap-2">
                                                        <span className="font-medium">{item.name}</span>
                                                        <span className="text-xs bg-white px-1.5 rounded border border-green-200">x{item.quantity}</span>
                                                    </div>
                                                    <div className="font-bold">{formatPrice(item.price * item.quantity)}</div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}

                                {/* VOUCHER */}
                                <div className="pt-4 border-t border-gray-100">
                                    <Voucher_Detail_Venue onVoucherApply={setSelectedVoucher} totalPrice={rawTotalPrice} />
                                </div>

                                {/* TOTAL SUMMARY */}
                                <div className="mt-6 space-y-3 bg-gray-50 p-4 rounded-xl border border-gray-200">
                                    <div className="flex justify-between text-sm text-gray-600">
                                        <span>Tạm tính</span>
                                        <span className="font-medium">{formatPrice(rawTotalPrice)}</span>
                                    </div>

                                    {selectedVoucher && (
                                        <div className="flex justify-between text-sm text-[#10B981]">
                                            <span className="flex items-center gap-1"><i className="fa-solid fa-ticket"></i> Voucher giảm</span>
                                            <span className="font-medium">-{formatPrice(discountAmount)}</span>
                                        </div>
                                    )}

                                    <div className="flex justify-between items-end pt-3 border-t border-gray-200">
                                        <span className="text-base font-bold text-gray-800">Tổng thanh toán</span>
                                        <span className="text-xl font-extrabold text-[#F59E0B] leading-none">{formatPrice(finalPrice)}</span>
                                    </div>
                                </div>

                                {/* BUTTON SUBMIT */}
                                <button
                                    onClick={handleSubmit}
                                    disabled={selectedItems.length === 0 || isSubmitting}
                                    className={`w-full mt-4 py-3.5 rounded-xl font-bold text-white shadow-lg flex items-center justify-center gap-2 transition-all transform active:scale-95 ${selectedItems.length === 0 || isSubmitting
                                        ? 'bg-gray-300 cursor-not-allowed shadow-none'
                                        : 'bg-[#10B981] hover:bg-[#059669] hover:shadow-green-200'
                                        }`}
                                >
                                    {isSubmitting ? (
                                        <><i className="fa-solid fa-circle-notch fa-spin"></i> Đang xử lý...</>
                                    ) : (
                                        <>Xác nhận đặt sân <i className="fa-solid fa-arrow-right"></i></>
                                    )}
                                </button>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    );
};

export default Order_Container;