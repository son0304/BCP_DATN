import React, { useEffect, useState, useMemo } from 'react';
import { useNotification } from '../../../../Components/Notification';
import { useFetchDataById } from '../../../../Hooks/useApi';

// --- TYPES ---
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

type OrderBookingProps = {
    id: number;
    selectedItems: SelectedItem[];
    onChange: (items: SelectedItem[]) => void;
    refreshTrigger: number;
};

const Order_Booking: React.FC<OrderBookingProps> = ({ id, selectedItems, onChange, refreshTrigger }) => {
    const { showNotification } = useNotification();

    // State nội bộ
    const [selectedDate, setSelectedDate] = useState<string>(new Date().toISOString().slice(0, 10));
    const [activeVenueTypeId, setActiveVenueTypeId] = useState<number | null>(null);
    const [activeCourtId, setActiveCourtId] = useState<number | null>(null);

    // GỌI API
    const { data, refetch, isLoading } = useFetchDataById<any>('court', id, { date: selectedDate });
    const courts = data?.data || [];

    // 1. Lấy danh sách các loại sân duy nhất (Venue Types)
    const venueTypes = useMemo(() => {
        if (!courts.length) return [];
        const typesMap = new Map();
        courts.forEach((court: any) => {
            if (court.venue_type) {
                typesMap.set(court.venue_type.id, court.venue_type);
            }
        });
        return Array.from(typesMap.values());
    }, [courts]);

    // 2. Tự động chọn Venue Type đầu tiên khi có dữ liệu
    useEffect(() => {
        if (venueTypes.length > 0 && activeVenueTypeId === null) {
            setActiveVenueTypeId(venueTypes[0].id);
        }
    }, [venueTypes, activeVenueTypeId]);

    // 3. Lọc danh sách sân theo Venue Type đã chọn
    const filteredCourts = useMemo(() => {
        if (!activeVenueTypeId) return [];
        return courts.filter((c: any) => c.venue_type?.id === activeVenueTypeId);
    }, [courts, activeVenueTypeId]);

    // 4. Tự động chọn Sân đầu tiên khi đổi Venue Type
    useEffect(() => {
        if (filteredCourts.length > 0) {
            setActiveCourtId(filteredCourts[0].id);
        } else {
            setActiveCourtId(null);
        }
    }, [filteredCourts]);

    useEffect(() => {
        refetch();
    }, [selectedDate, refetch]);

    useEffect(() => {
        if (refreshTrigger > 0) refetch();
    }, [refreshTrigger, refetch]);

    const activeCourtSlots = useMemo(() => {
        if (!activeCourtId) return [];
        const court = filteredCourts.find((c: any) => c.id === activeCourtId);
        const rawSlots: VenueSlot[] = court?.time_slots || court?.availabilities || [];
        return [...rawSlots].sort((a, b) => a.start_time.localeCompare(b.start_time));
    }, [filteredCourts, activeCourtId]);

    const isSlotPast = (dateStr: string, timeStr: string) => {
        const slotDate = new Date(`${dateStr}T${timeStr}`);
        const now = new Date();
        return slotDate < now;
    };

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
            newItems = selectedItems.filter(
                (i) => !(i.court_id === court.id && i.time_slot_id === slotUniqueId && i.date === selectedDate)
            );
        } else {
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
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col h-full mb-4">
            {/* HEADER */}
            <div className="bg-gray-50/50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                <h4 className="text-sm font-bold flex items-center gap-2 uppercase tracking-wide text-gray-700">
                    <i className="fa-solid fa-calendar-check text-[#10B981]"></i> Chọn lịch đặt sân
                </h4>
                <div className="flex gap-3 text-[10px] font-medium text-gray-400">
                    <div className="flex items-center gap-1"><span className="w-2 h-2 rounded-full bg-white border border-gray-300"></span> Trống</div>
                    <div className="flex items-center gap-1"><span className="w-2 h-2 rounded-full bg-[#10B981]"></span> Chọn</div>
                    <div className="flex items-center gap-1"><span className="w-2 h-2 rounded-full bg-gray-200"></span> Kín</div>
                </div>
            </div>

            <div className="p-4 space-y-4 flex-1 overflow-y-auto custom-scrollbar">
                {/* DATE PICKER & VENUE TYPE FILTER */}
                <div className="flex flex-col md:flex-row gap-4">
                    <div className="flex-shrink-0">
                        <label className="text-[10px] font-bold text-gray-400 uppercase mb-1 block pl-1">Ngày đặt</label>
                        <input
                            type="date"
                            value={selectedDate}
                            onChange={(e) => setSelectedDate(e.target.value)}
                            min={new Date().toISOString().slice(0, 10)}
                            className="w-full md:w-44 px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:ring-1 focus:ring-[#10B981] outline-none font-semibold text-gray-700 cursor-pointer"
                        />
                    </div>

                    <div className="flex-1">
                        <label className="text-[10px] font-bold text-gray-400 uppercase mb-1 block pl-1">Loại sân</label>
                        <div className="flex gap-2 overflow-x-auto pb-1 custom-scrollbar">
                            {venueTypes.map((type: any) => (
                                <button
                                    key={type.id}
                                    onClick={() => setActiveVenueTypeId(type.id)}
                                    className={`px-4 py-2 rounded-lg text-xs font-bold transition-all border whitespace-nowrap ${activeVenueTypeId === type.id
                                        ? 'bg-green-50 border-[#10B981] text-[#10B981]'
                                        : 'bg-white border-gray-200 text-gray-500 hover:border-gray-300'
                                        }`}
                                >
                                    <i className="fa-solid fa-layer-group mr-1.5 opacity-70"></i>
                                    {type.name}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>

                {isLoading ? (
                    <div className="text-center py-10">
                        <div className="inline-block animate-spin rounded-full h-5 w-5 border-2 border-[#10B981] border-t-transparent"></div>
                        <p className="mt-2 text-xs text-gray-400">Đang tải dữ liệu...</p>
                    </div>
                ) : (
                    <>
                        {/* COURT TABS */}
                        <div className="pt-2">
                            <label className="text-[10px] font-bold text-gray-400 uppercase mb-2 block pl-1">Danh sách sân</label>
                            <div className="flex gap-2 overflow-x-auto pb-2 custom-scrollbar">
                                {filteredCourts.map((court: any) => (
                                    <button
                                        key={court.id}
                                        onClick={() => setActiveCourtId(court.id)}
                                        className={`px-4 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-all border ${activeCourtId === court.id
                                            ? 'bg-[#10B981] border-[#10B981] text-white shadow-sm'
                                            : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'
                                            }`}
                                    >
                                        {court.name}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* SLOTS GRID */}
                        <div className="min-h-[250px] bg-gray-50/30 rounded-xl p-3 border border-dashed border-gray-200">
                            <div className="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-2">
                                {activeCourtSlots.length === 0 ? (
                                    <div className="col-span-full text-center py-12 text-gray-400 flex flex-col items-center">
                                        <i className="fa-regular fa-clock text-3xl mb-3 opacity-20"></i>
                                        <span className="text-xs font-medium">Không tìm thấy khung giờ phù hợp</span>
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

                                        return (
                                            <button
                                                key={`${activeCourtId}-${slotUniqueId}`}
                                                onClick={() => !isDisabled && handleToggleSlot(filteredCourts.find((c: any) => c.id === activeCourtId), slot)}
                                                disabled={isDisabled}
                                                className={`relative flex flex-col items-center justify-center p-2 rounded-xl border transition-all duration-200 min-h-[70px] ${isDisabled
                                                        ? "bg-gray-100 border-gray-100 text-gray-300 cursor-not-allowed opacity-60"
                                                        : isSelected
                                                            ? "bg-[#10B981] border-[#10B981] text-white shadow-lg shadow-green-200 scale-95 ring-2 ring-white"
                                                            : "bg-white border-gray-200 text-gray-700 hover:border-[#10B981] hover:text-[#10B981] hover:shadow-md"
                                                    }`}
                                            >
                                                <span className="text-sm font-black mb-1">
                                                    {slot.start_time.slice(0, 5)}
                                                </span>
                                                <div className="text-center">
                                                    {hasSale ? (
                                                        <div className="flex flex-col">
                                                            <span className={`text-[9px] line-through leading-none opacity-70 ${isSelected ? 'text-white' : 'text-gray-400'}`}>
                                                                {Math.round(Number(slot.price) / 1000)}k
                                                            </span>
                                                            <span className={`text-[11px] font-bold ${isSelected ? 'text-white' : 'text-red-500'}`}>
                                                                {Math.round(Number(slot.sale_price) / 1000)}k
                                                            </span>
                                                        </div>
                                                    ) : (
                                                        <span className={`text-[11px] font-bold ${isSelected ? 'text-white' : 'text-gray-500'}`}>
                                                            {Math.round(Number(slot.price) / 1000)}k
                                                        </span>
                                                    )}
                                                </div>
                                                {isSelected && (
                                                    <div className="absolute -top-1 -right-1 bg-white text-[#10B981] rounded-full w-4 h-4 flex items-center justify-center shadow-sm">
                                                        <i className="fa-solid fa-check text-[8px]"></i>
                                                    </div>
                                                )}
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