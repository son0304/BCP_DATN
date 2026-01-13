import React, { useEffect, useState, useMemo } from 'react';
import { useFetchDataById } from '../../../../Hooks/useApi';

// --- Types ---
export type ServiceItem = {
    id: number;
    name: string;
    price: number;
    quantity: number;
    unit: string;
};

type ApiServiceData = {
    id: number;
    venue_id: number;
    service_id: number;
    price: string;
    stock: number;
    status: number;
    service: {
        id: number;
        category_id: number;
        name: string;
        unit: string;
        type: string;
        images: Array<{ id: number; url: string }>;
        category: { id: number; name: string; };
    };
};

type OrderServiceProps = {
    venueId: number;
    selectedServices: ServiceItem[];
    onChange: (services: ServiceItem[]) => void;
    refreshTrigger: number;
};

const Order_Service: React.FC<OrderServiceProps> = ({ venueId, selectedServices, onChange, refreshTrigger }) => {
    const MAX_QUANTITY = 10;
    const [activeTab, setActiveTab] = useState<number | 'all'>('all');

    // --- Gọi API ---
    const { data, isLoading, refetch } = useFetchDataById('services', venueId);
    const servicesList: ApiServiceData[] = (data as any)?.data || [];

    useEffect(() => {
        if (refreshTrigger > 0) refetch();
    }, [refreshTrigger, refetch]);

    // --- Xử lý Danh mục (Tabs) ---
    const categories = useMemo(() => {
        if (!servicesList.length) return [];
        const unique = servicesList.reduce((acc: any[], item) => {
            const cat = item.service.category;
            if (cat && !acc.find(c => c.id === cat.id)) acc.push(cat);
            return acc;
        }, []);
        return [{ id: 'all', name: 'Tất cả' }, ...unique];
    }, [servicesList]);

    // --- Lọc dữ liệu hiển thị ---
    const filteredServices = useMemo(() => {
        if (activeTab === 'all') return servicesList;
        return servicesList.filter(item => item.service.category_id === activeTab);
    }, [activeTab, servicesList]);

    // --- Logic Tăng/Giảm số lượng ---
    const handleUpdateQuantity = (item: ApiServiceData, isIncrement: boolean) => {
        if (item.service.type === 'amenities') return;

        const existing = selectedServices.find(s => s.id === item.id);
        let updated = [...selectedServices];

        if (existing) {
            if (isIncrement) {
                if (existing.quantity >= MAX_QUANTITY || existing.quantity >= item.stock) return;
                updated = updated.map(s => s.id === item.id ? { ...s, quantity: s.quantity + 1 } : s);
            } else {
                if (existing.quantity > 1) {
                    updated = updated.map(s => s.id === item.id ? { ...s, quantity: s.quantity - 1 } : s);
                } else {
                    updated = updated.filter(s => s.id !== item.id);
                }
            }
        } else if (isIncrement && item.stock > 0) {
            updated.push({
                id: item.id,
                name: item.service.name,
                price: parseFloat(item.price),
                unit: item.service.unit,
                quantity: 1
            });
        }
        onChange(updated);
    };

    if (isLoading) return <div className="p-4 text-center text-[11px] text-gray-400 italic">Đang tải dịch vụ...</div>;
    if (!servicesList.length) return null;

    return (
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6 flex flex-col">

            {/* 1. Header Nhỏ gọn */}
            <div className="px-4 py-2 bg-white border-b border-gray-50 flex justify-between items-center">
                <h4 className="text-[11px] font-bold uppercase tracking-tight text-gray-600 flex items-center gap-1.5">
                    <i className="fa-solid fa-square-poll-horizontal text-[#10B981]"></i> Dịch vụ & Tiện ích
                </h4>
                <span className="text-[9px] bg-gray-100 text-gray-400 px-1.5 py-0.5 rounded font-bold uppercase">
                    {servicesList.length} Mục
                </span>
            </div>

            {/* 2. Thanh TAB Siêu nhỏ */}
            <div className="px-3 py-2 bg-white border-b border-gray-50 flex gap-1.5 overflow-x-auto no-scrollbar items-center">
                {categories.map((cat) => (
                    <button
                        key={cat.id}
                        onClick={() => setActiveTab(cat.id)}
                        className={`px-3 py-1 rounded-full text-[10px] font-bold whitespace-nowrap transition-all uppercase tracking-tighter border
                            ${activeTab === cat.id
                                ? 'bg-[#10B981] text-white border-[#10B981] shadow-sm'
                                : 'bg-white text-gray-400 border-gray-100 hover:border-emerald-200'
                            }`}
                    >
                        {cat.name}
                    </button>
                ))}
            </div>

            {/* 3. Danh sách Grid */}
            <div className="p-3 grid grid-cols-1 sm:grid-cols-2 gap-2.5 bg-gray-50/20">
                {filteredServices.map((item) => {
                    const isAmenity = item.service.type === 'amenities';
                    const price = parseFloat(item.price);
                    const selected = selectedServices.find(s => s.id === item.id);
                    const quantity = selected ? selected.quantity : 0;
                    const isOut = !isAmenity && item.stock <= 0;

                    return (
                        <div key={item.id} className={`flex items-center justify-between p-2 rounded-lg border transition-all
                            ${quantity > 0 ? 'bg-emerald-50/50 border-emerald-200' : 'bg-white border-gray-100 shadow-sm'}`}>

                            {/* Trái: Ảnh & Info */}
                            <div className="flex items-center gap-2.5 overflow-hidden">
                                <div className="relative w-11 h-11 flex-shrink-0">
                                    <img
                                        src={item.service.images?.[0]?.url || `https://ui-avatars.com/api/?name=${item.service.name}`}
                                        className={`w-full h-full rounded-md object-cover border border-gray-50 ${isOut ? 'grayscale opacity-40' : ''}`}
                                    />
                                    {isOut && <div className="absolute inset-0 bg-black/20 flex items-center justify-center rounded-md text-[8px] text-white font-bold">HẾT</div>}
                                </div>
                                <div className="min-w-0">
                                    <h5 className="text-[12px] font-bold text-gray-700 truncate leading-tight mb-0.5" title={item.service.name}>
                                        {item.service.name}
                                    </h5>
                                    {isAmenity ? (
                                        <div className="text-[9px] font-bold text-blue-500 bg-blue-50 px-1 rounded flex items-center gap-1 w-fit">
                                            <i className="fa-solid fa-leaf text-[8px]"></i> MIỄN PHÍ
                                        </div>
                                    ) : (
                                        <div className="flex flex-col">
                                            <span className="text-[11px] font-bold text-[#F59E0B] leading-none">
                                                {price.toLocaleString()}đ<span className="text-[9px] text-gray-400 font-normal">/{item.service.unit}</span>
                                            </span>
                                            <span className={`text-[9px] mt-0.5 ${item.stock < 5 ? 'text-red-400 font-bold' : 'text-gray-400'}`}>
                                                Kho: {item.stock}
                                            </span>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Phải: Điều khiển */}
                            <div className="flex-shrink-0 ml-2">
                                {isAmenity ? (
                                    <div className={`text-[9px] font-bold px-2 py-1 rounded border ${item.stock === 1 ? 'text-emerald-500 border-emerald-100 bg-white' : 'text-gray-300 border-gray-100'}`}>
                                        {item.stock === 1 ? 'SẴN CÓ' : 'BẢO TRÌ'}
                                    </div>
                                ) : (
                                    <div className={`flex items-center border rounded-md h-7 overflow-hidden bg-white ${quantity > 0 ? 'border-[#10B981]' : 'border-gray-200'}`}>
                                        <button
                                            onClick={() => handleUpdateQuantity(item, false)}
                                            disabled={quantity === 0}
                                            className="w-7 h-full text-gray-400 hover:bg-gray-50 disabled:opacity-20 transition-colors"
                                        >
                                            <i className="fa-solid fa-minus text-[8px]"></i>
                                        </button>
                                        <span className={`w-5 text-center text-[11px] font-bold ${quantity > 0 ? 'text-[#10B981]' : 'text-gray-400'}`}>
                                            {quantity}
                                        </span>
                                        <button
                                            onClick={() => handleUpdateQuantity(item, true)}
                                            disabled={isOut || quantity >= MAX_QUANTITY || quantity >= item.stock}
                                            className="w-7 h-full text-[#10B981] hover:bg-emerald-50 disabled:opacity-20 transition-colors"
                                        >
                                            <i className="fa-solid fa-plus text-[8px]"></i>
                                        </button>
                                    </div>
                                )}
                            </div>
                        </div>
                    );
                })}

                {/* Empty State */}
                {filteredServices.length === 0 && (
                    <div className="col-span-full py-8 text-center text-[11px] text-gray-400 italic">
                        Không có dịch vụ trong mục này
                    </div>
                )}
            </div>
        </div>
    );
};

export default Order_Service;