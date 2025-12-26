import React, { useEffect } from 'react';
import { useFetchDataById } from '../../../../Hooks/useApi';

// 1. Định nghĩa Type cho Item được chọn
export type ServiceItem = {
    id: number;
    name: string;
    price: number;
    quantity: number;
    unit: string;
};

// 2. Định nghĩa Type API
type ApiServiceData = {
    id: number;
    venue_id: number;
    service_id: number;
    price: string;
    stock: number;
    status: number;
    service: {
        id: number;
        name: string;
        unit: string;
        type: string; // 'service' | 'amenities'
        description: string | null;
        images: Array<{
            id: number;
            url: string;
        }>;
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

    const { data, isLoading, refetch } = useFetchDataById('services', venueId);
    const servicesList: ApiServiceData[] = (data as any)?.data || [];


    useEffect(() => {
        if (refreshTrigger > 0) {
            refetch(); 
        }
    }, [refreshTrigger, refetch]);
    // --- Hàm xử lý Tăng/Giảm (Chỉ áp dụng cho dịch vụ thường) ---
    const handleToggleService = (item: ApiServiceData, increment: boolean = true) => {
        // [Safety check] Không xử lý nếu là tiện ích
        if (item.service.type === 'amenities') return;

        if (increment && item.stock <= 0) {
            alert('Sản phẩm này hiện đang hết hàng!');
            return;
        }

        const existingItem = selectedServices.find(s => s.id === item.id);
        let newServices = [...selectedServices];

        if (existingItem) {
            if (increment) {
                if (existingItem.quantity >= MAX_QUANTITY) {
                    alert(`Bạn chỉ được chọn tối đa ${MAX_QUANTITY} ${item.service.unit} cho dịch vụ này.`);
                    return;
                }
                if (existingItem.quantity >= item.stock) {
                    alert(`Kho chỉ còn ${item.stock} ${item.service.unit}.`);
                    return;
                }
                newServices = newServices.map(s =>
                    s.id === item.id ? { ...s, quantity: s.quantity + 1 } : s
                );
            } else {
                if (existingItem.quantity > 1) {
                    newServices = newServices.map(s =>
                        s.id === item.id ? { ...s, quantity: s.quantity - 1 } : s
                    );
                } else {
                    newServices = newServices.filter(s => s.id !== item.id);
                }
            }
        } else {
            if (increment) {
                newServices.push({
                    id: item.id,
                    name: item.service.name,
                    price: parseFloat(item.price),
                    unit: item.service.unit,
                    quantity: 1
                });
            }
        }
        onChange(newServices);
    };

    if (isLoading) return <div className="p-8 text-center text-gray-500"><i className="fa-solid fa-circle-notch fa-spin mr-2"></i>Đang tải dịch vụ...</div>;
    if (!servicesList || servicesList.length === 0) return null;

    return (
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col mb-6">
            <div className="bg-gray-50 px-5 py-3 border-b border-gray-100 flex justify-between items-center">
                <h4 className="text-sm font-bold flex items-center gap-2 uppercase tracking-wide text-gray-700">
                    <i className="fa-solid fa-bell-concierge text-[#10B981]"></i> Dịch vụ & Tiện ích
                </h4>
                <span className="text-xs text-gray-400 font-medium">{servicesList.length} mục</span>
            </div>

            <div className="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                {servicesList.map((item) => {
                    // === CHECK TYPE ===
                    const isAmenity = item.service.type === 'amenities';

                    // Lấy thông tin cơ bản
                    const price = parseFloat(item.price);

                    // Xử lý ảnh
                    const imageUrl = (item.service.images && item.service.images.length > 0)
                        ? item.service.images[0].url
                        : `https://ui-avatars.com/api/?name=${encodeURIComponent(item.service.name)}&background=${isAmenity ? 'DBEAFE' : 'f3f4f6'}&color=${isAmenity ? '2563EB' : '10b981'}&size=128`;

                    // --- LOGIC CHO AMENITIES ---
                    if (isAmenity) {
                        // stock == 1 là Hoạt động, stock == 0 là Bảo trì
                        const isActive = item.stock === 1;

                        return (
                            <div key={item.id} className="relative flex justify-between items-center p-3 border border-gray-100 rounded-xl bg-gray-50/50">
                                {/* Cột trái: Thông tin tiện ích */}
                                <div className="flex items-center gap-3 overflow-hidden">
                                    <div className="relative w-14 h-14 flex-shrink-0">
                                        <img src={imageUrl} alt={item.service.name} className="w-full h-full rounded-lg object-cover border border-gray-200 grayscale-[20%]" />
                                    </div>
                                    <div className='flex flex-col justify-center min-w-0'>
                                        <div className="text-sm font-bold text-gray-700 truncate pr-2" title={item.service.name}>
                                            {item.service.name}
                                        </div>
                                        <div className="text-xs text-blue-500 font-medium bg-blue-50 px-1.5 py-0.5 rounded w-fit mt-1">
                                            Tiện ích miễn phí
                                        </div>
                                    </div>
                                </div>

                                {/* Cột phải: Trạng thái (Không cho mua) */}
                                <div>
                                    {isActive ? (
                                        <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                            <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Hoạt động
                                        </span>
                                    ) : (
                                        <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500 border border-gray-200">
                                            <i className="fa-solid fa-wrench text-[10px]"></i>
                                            Bảo trì
                                        </span>
                                    )}
                                </div>
                            </div>
                        );
                    }

                    // --- LOGIC CHO DỊCH VỤ THƯỜNG (Mua bán được) ---
                    const isSelected = selectedServices.find(s => s.id === item.id);
                    const quantity = isSelected ? isSelected.quantity : 0;

                    const isOutOfStock = item.stock <= 0;
                    const isLowStock = item.stock > 0 && item.stock < 5;
                    const disableAddBtn = isOutOfStock || quantity >= MAX_QUANTITY || quantity >= item.stock;

                    return (
                        <div
                            key={item.id}
                            className={`relative flex justify-between items-center p-3 border rounded-xl transition-all duration-200
                                ${quantity > 0 ? 'border-[#10B981] bg-emerald-50/30' : 'border-gray-200 bg-white hover:border-emerald-200 hover:shadow-sm'}
                            `}
                        >
                            {/* Cột trái */}
                            <div className="flex items-center gap-3 overflow-hidden">
                                <div className="relative w-14 h-14 flex-shrink-0">
                                    <img
                                        src={imageUrl}
                                        alt={item.service.name}
                                        className={`w-full h-full rounded-lg object-cover border border-gray-100 ${isOutOfStock ? 'grayscale opacity-70' : ''}`}
                                    />
                                    {isOutOfStock && (
                                        <div className="absolute inset-0 bg-black/40 rounded-lg flex items-center justify-center">
                                            <span className="text-[10px] font-bold text-white uppercase text-center leading-3">Hết<br />hàng</span>
                                        </div>
                                    )}
                                </div>

                                <div className='flex flex-col justify-center gap-0.5 min-w-0'>
                                    <div className="text-sm font-bold text-gray-800 truncate pr-2" title={item.service.name}>
                                        {item.service.name}
                                    </div>
                                    <div className="text-xs font-semibold text-[#F59E0B]">
                                        {price.toLocaleString('vi-VN')}₫
                                        <span className='text-gray-400 font-normal ml-1'>/{item.service.unit}</span>
                                    </div>
                                    {/* Tồn kho */}
                                    <div className="text-[10px] flex items-center gap-1 mt-0.5">
                                        {isOutOfStock ? (
                                            <span className="text-red-500 font-bold bg-red-50 px-1.5 rounded-sm">Hết hàng</span>
                                        ) : (
                                            <>
                                                <i className={`fa-solid fa-box-archive ${isLowStock ? 'text-orange-500' : 'text-gray-400'}`}></i>
                                                <span className={`${isLowStock ? 'text-orange-500 font-bold' : 'text-gray-500'}`}>
                                                    Sẵn có: {item.stock}
                                                </span>
                                            </>
                                        )}
                                    </div>
                                </div>
                            </div>

                            {/* Cột phải: Nút bấm */}
                            <div className="flex items-center gap-1">
                                {isOutOfStock && quantity === 0 ? (
                                    <button disabled className="w-8 h-8 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed flex items-center justify-center">
                                        <i className="fa-solid fa-plus text-xs"></i>
                                    </button>
                                ) : (
                                    <div className={`flex items-center bg-white rounded-lg border ${quantity > 0 ? 'border-[#10B981] shadow-sm' : 'border-gray-200'}`}>
                                        <button
                                            onClick={() => handleToggleService(item, false)}
                                            disabled={quantity === 0}
                                            className="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-red-500 hover:bg-red-50 rounded-l-lg transition-colors disabled:opacity-30"
                                            type="button"
                                        >
                                            <i className="fa-solid fa-minus text-xs"></i>
                                        </button>

                                        <div className="w-6 text-center text-sm font-bold text-gray-800 select-none">
                                            {quantity}
                                        </div>

                                        <button
                                            onClick={() => handleToggleService(item, true)}
                                            disabled={disableAddBtn}
                                            className={`w-8 h-8 flex items-center justify-center rounded-r-lg transition-colors
                                                ${disableAddBtn ? 'text-gray-300 cursor-not-allowed bg-gray-50' : 'text-[#10B981] hover:bg-[#10B981] hover:text-white'}
                                            `}
                                            type="button"
                                        >
                                            <i className="fa-solid fa-plus text-xs"></i>
                                        </button>
                                    </div>
                                )}
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
};

export default Order_Service;