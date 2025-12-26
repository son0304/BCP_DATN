import React, { useEffect, useState, useMemo } from 'react';
import { useNotification } from '../../../../Components/Notification';
import { useFetchDataById } from '../../../../Hooks/useApi';


// --- TYPES (Export để dùng bên Container) ---
export type VenueSlot = {
    id: number;
    time_slot_id?: number;
    start_time: string;
    end_time: string;
    price: number | string;
    status?: 'open' | 'booked' | 'closed' | 'maintenance';
    sale_price: number | string | null;
    flash_status: "active" | "sold_out" | "inactive";
    quantity: number;
    sold_count: number;
};

export type SelectedItem = {
    court_id: number;
    court_name: string;
    time_slot_id: number;
    start_time: string;
    end_time: string;
    date: string;
    price: number;
    sale_price: number;
};

// --- PROPS ---
type OrderBookingProps = {
    id: number; // Venue ID
    selectedItems: SelectedItem[]; // Nhận từ cha để hiển thị màu đã chọn
    onChange: (items: SelectedItem[]) => void; // Hàm update state của cha
    refreshTrigger: number; // Thêm dòng này

};

const Order_Booking: React.FC<OrderBookingProps> = ({ id, selectedItems, onChange, refreshTrigger  }) => {
    const { showNotification } = useNotification();

    // State nội bộ
    const [selectedDate, setSelectedDate] = useState<string>(new Date().toISOString().slice(0, 10));
    const [activeCourtId, setActiveCourtId] = useState<number | null>(null);

    // 1. GỌI API: Lấy dữ liệu sân theo ID và Date
    const { data, refetch, isLoading } = useFetchDataById<any>('court', id, { date: selectedDate });
    const courts = data?.data || [];


    // Logic: Refetch khi đổi ngày
    useEffect(() => {
        refetch();
    }, [selectedDate, refetch]);

    // Logic: Auto select court đầu tiên
    useEffect(() => {
        if (courts.length > 0 && activeCourtId === null) {
            setActiveCourtId(courts[0].id);
        }
    }, [courts, activeCourtId]);

    useEffect(() => {
        if (refreshTrigger > 0) {
            refetch(); 
        }
    }, [refreshTrigger, refetch]);

    // Logic: Lấy slots của court đang active
    const activeCourtSlots = useMemo(() => {
        if (!activeCourtId) return [];
        const court = courts.find((c: any) => c.id === activeCourtId);
        const rawSlots: VenueSlot[] = court?.time_slots || court?.availabilities || [];
        return rawSlots.sort((a, b) => a.start_time.localeCompare(b.start_time));
    }, [courts, activeCourtId]);

    // Helper
    const isSlotPast = (dateStr: string, timeStr: string) => {
        const slotDate = new Date(`${dateStr}T${timeStr}`);
        const now = new Date();
        return slotDate < now;
    };

    // Logic: Xử lý click chọn slot
    const handleToggleSlot = (court: any, slot: VenueSlot) => {
        const slotUniqueId = slot.time_slot_id || slot.id;

        if (slot.status && slot.status !== 'open') {
            const msgMap: Record<string, string> = {
                booked: 'Sân đã có người đặt.',
                maintenance: 'Sân đang bảo trì.',
                closed: 'Sân đóng cửa.',
            };
            return showNotification(msgMap[slot.status] || 'Khung giờ này không khả dụng.', 'error');
        }

        if (isSlotPast(selectedDate, slot.start_time)) {
            return showNotification('Khung giờ này đã trôi qua.', 'error');
        }

        const isSelected = selectedItems.some(
            (item) => item.court_id === court.id && item.time_slot_id === slotUniqueId && item.date === selectedDate
        );

        let newItems;
        if (isSelected) {
            // Bỏ chọn
            newItems = selectedItems.filter(
                (i) => !(i.court_id === court.id && i.time_slot_id === slotUniqueId && i.date === selectedDate)
            );
        } else {
            // Thêm mới
            newItems = [
                ...selectedItems,
                {
                    court_id: court.id,
                    court_name: court.name,
                    time_slot_id: slotUniqueId,
                    start_time: slot.start_time,
                    end_time: slot.end_time,
                    date: selectedDate,
                    price: Number(slot.price),
                    sale_price: Number(slot.sale_price || 0),
                },
            ];
        }
        onChange(newItems);
    };

    return (
        <div className="bg-white rounded-xl shadow-lg shadow-gray-200/50 border border-gray-100 overflow-hidden flex flex-col h-full mb-4">
            <div className="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <h4 className="text-sm font-bold flex items-center gap-2 uppercase tracking-wide text-gray-700">
                    <i className="fa-solid fa-calendar-days text-[#10B981]"></i> Chọn lịch đặt
                </h4>
            </div>

            <div className="p-4 space-y-5 flex-1 overflow-y-auto custom-scrollbar">
                {/* DATE PICKER */}
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div className="relative w-full sm:w-auto">
                        <input
                            type="date"
                            value={selectedDate}
                            onChange={(e) => setSelectedDate(e.target.value)}
                            min={new Date().toISOString().slice(0, 10)}
                            className="w-full sm:w-48 pl-3 pr-2 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:ring-1 focus:ring-[#10B981] outline-none font-medium text-gray-700 cursor-pointer"
                        />
                    </div>
                    <div className="flex gap-3 text-[10px] text-gray-500">
                        <div className="flex items-center gap-1"><span className="w-2 h-2 rounded-full bg-white border border-gray-300"></span> Trống</div>
                        <div className="flex items-center gap-1"><span className="w-2 h-2 rounded-full bg-[#10B981]"></span> Chọn</div>
                        <div className="flex items-center gap-1"><span className="w-2 h-2 rounded-full bg-gray-200"></span> Kín</div>
                    </div>
                </div>

                {isLoading ? (
                    <div className="text-center py-5 text-gray-400 text-xs">Đang tải danh sách sân...</div>
                ) : (
                    <>
                        {/* COURT TABS */}
                        <div className="border-b border-gray-100">
                            <div className="flex gap-2 overflow-x-auto pb-2 custom-scrollbar">
                                {courts.map((court: any) => (
                                    <button
                                        key={court.id}
                                        onClick={() => setActiveCourtId(court.id)}
                                        className={`px-3 py-1.5 rounded-md text-xs font-bold whitespace-nowrap transition-all ${activeCourtId === court.id
                                            ? 'bg-[#10B981] text-white shadow-sm'
                                            : 'bg-gray-50 text-gray-500 hover:bg-gray-100'
                                            }`}
                                    >
                                        {court.name}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* SLOTS GRID */}
                        <div className="min-h-[200px]">
                            <div className="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 lg:grid-cols-5 xl:grid-cols-6 gap-2">
                                {activeCourtSlots.length === 0 ? (
                                    <div className="col-span-full text-center py-10 text-gray-400 text-xs flex flex-col items-center">
                                        <i className="fa-regular fa-calendar-xmark text-2xl mb-2 opacity-30"></i>
                                        <span>Không có lịch trống cho sân này</span>
                                    </div>
                                ) : (
                                    activeCourtSlots.map((slot) => {
                                        const slotUniqueId = slot.time_slot_id || slot.id;
                                        const isSelected = selectedItems.some(
                                            (item) => item.court_id === activeCourtId && item.time_slot_id === slotUniqueId && item.date === selectedDate
                                        );
                                        const isPast = isSlotPast(selectedDate, slot.start_time);
                                        const isOpen = !slot.status || slot.status === 'open';
                                        const isDisabled = !isOpen || isPast;
                                        const hasSale = slot.sale_price !== null && Number(slot.sale_price) > 0;

                                        let btnClass = "relative flex flex-col items-center justify-center gap-1 py-3 px-2 min-h-[65px] min-w-[75px] rounded border transition-all duration-200 ";

                                        if (isDisabled) {
                                            btnClass += "bg-gray-100 border-gray-100 text-gray-300 cursor-not-allowed";
                                        } else if (isSelected) {
                                            btnClass += "bg-[#10B981] border-[#10B981] text-white shadow-md ring-2 ring-green-100 cursor-pointer transform -translate-y-0.5";
                                        } else {
                                            btnClass += "bg-white border-gray-200 text-gray-600 hover:border-[#10B981] hover:text-[#10B981] hover:shadow-sm cursor-pointer";
                                        }

                                        return (
                                            <button
                                                key={`${activeCourtId}-${slotUniqueId}`}
                                                onClick={() => !isDisabled && handleToggleSlot(courts.find((c: any) => c.id === activeCourtId), slot)}
                                                disabled={isDisabled}
                                                className={btnClass}
                                            >
                                                <span className="text-sm font-bold leading-none">
                                                    {slot.start_time.slice(0, 5)}
                                                </span>
                                                <div className="flex flex-col items-center justify-end h-8">
                                                    {hasSale ? (
                                                        <>
                                                            <span className={`text-[10px] line-through leading-tight ${isSelected ? 'text-green-100 opacity-80' : 'text-gray-400'}`}>
                                                                {Number(slot.price) / 1000}k
                                                            </span>
                                                            <span className={`text-xs font-bold leading-tight ${isSelected ? 'text-white' : 'text-red-600'}`}>
                                                                {Number(slot.sale_price) / 1000}k
                                                            </span>
                                                        </>
                                                    ) : (
                                                        <span className={`text-xs font-medium ${isSelected ? 'text-white' : 'text-gray-500'}`}>
                                                            {Number(slot.price) / 1000}k
                                                        </span>
                                                    )}
                                                </div>
                                            </button>
                                        );
                                    })
                                )}
                            </div>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};

export default Order_Booking;