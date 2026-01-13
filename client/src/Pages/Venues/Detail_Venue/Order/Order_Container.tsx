import React, { useState, useMemo, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

// Import Components
import Order_Booking, { type SelectedItem } from './Order_Booking';
import Order_Service, { type ServiceItem } from './Order_Service';
import { useNotification } from '../../../../Components/Notification';
import { usePostData } from '../../../../Hooks/useApi';
import type { ApiResponse } from '../../../../Types/api';
import type { User } from '../../../../Types/user';
import Voucher_Detail_Venue, { type Voucher } from '../Voucher_Detail_Venue';

const Order_Container = ({ id, promotions }: { id: any, promotions: Voucher[] }) => {
    const navigate = useNavigate();
    const { showNotification } = useNotification();
    const { mutate } = usePostData<ApiResponse<number>, any>('tickets');

    const userRaw = localStorage.getItem('user');
    const user = userRaw ? JSON.parse(userRaw) as User : null;

    const [selectedItems, setSelectedItems] = useState<SelectedItem[]>([]);
    const [selectedServices, setSelectedServices] = useState<ServiceItem[]>([]);
    const [selectedVoucher, setSelectedVoucher] = useState<Voucher | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [refreshTrigger, setRefreshTrigger] = useState(0);

    const formatPrice = (price: number | string) =>
        Number(price).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });

    const rawTotalPrice = useMemo(() => {
        const bookingTotal = selectedItems.reduce((sum, item) => {
            const effectivePrice = (item.sale_price && item.sale_price > 0) ? item.sale_price : item.price;
            return sum + Number(effectivePrice);
        }, 0);

        const serviceTotal = selectedServices.reduce((sum, item) => {
            return sum + (Number(item.price) * item.quantity);
        }, 0);

        return bookingTotal + serviceTotal;
    }, [selectedItems, selectedServices]);

    useEffect(() => {
        if (selectedVoucher) {
            if (rawTotalPrice < selectedVoucher.min_order_value) {
                setSelectedVoucher(null);
            }
        }
    }, [rawTotalPrice, selectedVoucher]);

    const { finalPrice, discountAmount } = useMemo(() => {
        let discount = 0;
        if (selectedVoucher) {
            const now = new Date();
            const start = new Date(selectedVoucher.start_at);
            const end = new Date(selectedVoucher.end_at);

            const isValid =
                selectedVoucher.process_status === 'active' &&
                now >= start &&
                now <= end &&
                (selectedVoucher.min_order_value === 0 || rawTotalPrice >= selectedVoucher.min_order_value);

            if (isValid) {
                const voucherVal = parseFloat(selectedVoucher.value);
                if (selectedVoucher.type === 'percentage') {
                    discount = (rawTotalPrice * voucherVal) / 100;
                    if (selectedVoucher.max_discount_amount > 0 && discount > selectedVoucher.max_discount_amount) {
                        discount = selectedVoucher.max_discount_amount;
                    }
                } else {
                    discount = voucherVal;
                }
            }
        }
        const validDiscount = Math.min(discount, rawTotalPrice);
        return {
            discountAmount: validDiscount,
            finalPrice: Math.max(0, rawTotalPrice - validDiscount)
        };
    }, [rawTotalPrice, selectedVoucher]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!user || !user.id) return showNotification('Vui lòng đăng nhập để đặt sân.', 'error');
        if (selectedItems.length === 0) return showNotification('Vui lòng chọn ít nhất 1 khung giờ.', 'error');

        if (selectedVoucher && rawTotalPrice < selectedVoucher.min_order_value) {
            return showNotification(`Đơn hàng không đủ điều kiện áp dụng mã giảm giá (Tối thiểu ${formatPrice(selectedVoucher.min_order_value)})`, 'error');
        }

        setIsSubmitting(true);
        const payload = {
            user_id: user.id,
            venue_id: id,
            promotion_id: (selectedVoucher && discountAmount > 0) ? selectedVoucher.id : null,
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

        mutate(payload, {
            onSuccess: (res) => {
                if (res.success) {
                    showNotification('Đặt sân thành công!', 'success');
                    setRefreshTrigger(prev => prev + 1);
                    setSelectedItems([]);
                    setSelectedServices([]);
                    setSelectedVoucher(null);
                    navigate(`/booking/${res.data}`);
                } else {
                    showNotification(res.message || 'Đặt sân thất bại.', 'error');
                }
            },
            onError: () => showNotification('Lỗi kết nối server.', 'error'),
            onSettled: () => setIsSubmitting(false),
        });
    };

    return (
        <div className="bg-gray-50 min-h-screen py-8">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="mb-8">
                    <h1 className="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <span className="bg-[#10B981] text-white p-2 rounded-lg">
                            <i className="fa-solid fa-calendar-check"></i>
                        </span>
                        Đặt Sân Trực Tuyến
                    </h1>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <div className="lg:col-span-8 space-y-8">
                        <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <Order_Booking
                                id={id}
                                selectedItems={selectedItems}
                                onChange={setSelectedItems}
                                refreshTrigger={refreshTrigger}
                            />
                        </div>
                        <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <Order_Service
                                venueId={id}
                                selectedServices={selectedServices}
                                onChange={setSelectedServices}
                                refreshTrigger={refreshTrigger}
                            />
                        </div>
                    </div>

                    <div className="lg:col-span-4 sticky top-24 self-start">
                        <div className="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                            <div className="bg-gray-900 px-5 py-4 flex justify-between items-center">
                                <h3 className="text-white font-bold text-base flex items-center gap-2">
                                    <i className="fa-solid fa-receipt text-[#10B981]"></i> Thông tin thanh toán
                                </h3>
                                <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded-full">
                                    {selectedItems.length} slots
                                </span>
                            </div>

                            <div className="p-5">
                                {selectedItems.length > 0 ? (
                                    <div className="mb-4">
                                        <div className="space-y-2 max-h-[200px] overflow-y-auto custom-scrollbar pr-2">
                                            {selectedItems.map((item, idx) => (
                                                <div key={idx} className="flex justify-between text-sm">
                                                    <span>{item.court_name} ({item.start_time.slice(0, 5)})</span>
                                                    <span className="font-bold">{formatPrice(item.sale_price || item.price)}</span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                ) : (
                                    <p className="text-center text-gray-400 text-sm py-4">Chưa chọn lịch</p>
                                )}

                                {selectedServices.length > 0 && (
                                    <div className="border-t pt-2 mb-2">
                                        {selectedServices.map((s, idx) => (
                                            <div key={idx} className="flex justify-between text-sm text-gray-600">
                                                <span>{s.name} (x{s.quantity})</span>
                                                <span>{formatPrice(s.price * s.quantity)}</span>
                                            </div>
                                        ))}
                                    </div>
                                )}

                                <div className="pt-4 border-t border-gray-100">
                                    <Voucher_Detail_Venue
                                        availableVouchers={promotions}
                                        onVoucherApply={setSelectedVoucher}
                                        totalPrice={rawTotalPrice}
                                        selectedVoucher={selectedVoucher}
                                    />
                                </div>

                                <div className="mt-6 space-y-3 bg-gray-50 p-4 rounded-xl border border-gray-200">
                                    <div className="flex justify-between text-sm text-gray-600">
                                        <span>Tạm tính</span>
                                        <span className="font-medium">{formatPrice(rawTotalPrice)}</span>
                                    </div>

                                    {selectedVoucher && discountAmount > 0 && (
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

                                {/* POLICY NOTICE */}
                                <div className="mt-6 p-3 bg-amber-50 border-l-4 border-amber-400 rounded-r-xl text-[13px] text-gray-700 leading-relaxed">
                                    <h4 className="font-bold text-amber-800 mb-1 flex items-center gap-1">
                                        <i className="fa-solid fa-circle-info"></i> Lưu ý hoàn tiền về ví:
                                    </h4>
                                    <ul className="list-disc ml-4 space-y-1">
                                        <li><span className="font-semibold text-gray-800">Sân bóng:</span> Trước 24h (Hoàn 100%), 2h - 24h (Hoàn 50%), dưới 2h (Phạt 100%).</li>
                                        <li><span className="font-semibold text-gray-800">Dịch vụ:</span> Hoàn 100% nếu mục đó chưa được sử dụng/check-in.</li>
                                        <li><span className="font-semibold text-gray-800">Voucher:</span> Hệ thống sẽ tính lại mức giảm giá dựa trên đơn hàng mới khi hủy lẻ.</li>
                                    </ul>
                                </div>

                                <button
                                    onClick={handleSubmit}
                                    disabled={selectedItems.length === 0 || isSubmitting}
                                    className={`w-full mt-4 py-3.5 rounded-xl font-bold text-white shadow-lg flex items-center justify-center gap-2 transition-all ${selectedItems.length === 0 || isSubmitting
                                        ? 'bg-gray-300 cursor-not-allowed shadow-none'
                                        : 'bg-[#10B981] hover:bg-[#059669]'
                                        }`}
                                >
                                    {isSubmitting ? 'Đang xử lý...' : 'Xác nhận đặt sân'}
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