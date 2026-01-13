import { useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import { MapContainer, TileLayer, Marker, useMapEvents } from "react-leaflet";
import "leaflet/dist/leaflet.css";
import L from "leaflet";
import Input from "../../Components/Input";
import { useFetchData, usePostData } from "../../Hooks/useApi";
import { useNotification } from "../../Components/Notification";
import { useNavigate } from "react-router-dom";

// --- CONFIG LEAFLET ---
import icon from "leaflet/dist/images/marker-icon.png";
import iconShadow from "leaflet/dist/images/marker-shadow.png";

let DefaultIcon = L.icon({
    iconUrl: icon,
    shadowUrl: iconShadow,
    iconSize: [25, 41],
    iconAnchor: [12, 41],
});
L.Marker.prototype.options.icon = DefaultIcon;

// --- TYPES ---
type MerchantDataApi = {
    merchant: any;
    venue: any;
};

type CreateVenueFormData = {
    business_name: string;
    business_address: string;
    bank_name: string;
    bank_account_number: string;
    bank_account_name: string;
    user_profiles: FileList;
    venue_name: string;
    venue_phone: string;
    province_id: string;
    district_id: string;
    address_detail: string;
    lat: number;
    lng: number;
    open_time: string;
    close_time: string;
    venue_profiles: FileList;
    document_images: FileList;
};

// --- MOCK DATA ---
const PROVINCES = [{ id: "1", name: "Hà Nội" }, { id: "2", name: "TP. HCM" }];
const DISTRICTS = [{ id: "1", name: "Quận Ba Đình" }, { id: "2", name: "Quận Cầu Giấy" }];

const LocationMarker = ({ setMarker, setValue }: any) => {
    useMapEvents({
        click(e) {
            setMarker(e.latlng);
            setValue("lat", e.latlng.lat);
            setValue("lng", e.latlng.lng);
        },
    });
    return null;
};

const CreateVenue = () => {
    const { showNotification } = useNotification();
    const navigate = useNavigate();
    const [mapMarker, setMapMarker] = useState<{ lat: number; lng: number } | null>(null);
    const [previews, setPreviews] = useState({ legal: [] as string[], venue: [] as string[], docs: [] as string[] });

    // --- API HOOKS ---
    const { mutate: createVenueMutate, isPending: isCreating } = usePostData('venues');
    const { data: merchantStatus, isLoading } = useFetchData<MerchantDataApi>('merchant');
    console.log(merchantStatus);


    const merchant = merchantStatus?.data?.merchant;
    const venue = merchantStatus?.data?.venue;
    const hasRegistration = !!(merchantStatus?.data?.merchant || merchantStatus?.data?.venue);

    const { register, handleSubmit, watch, setValue, formState: { errors } } = useForm<CreateVenueFormData>({
        defaultValues: { lat: 21.0285, lng: 105.8542 }
    });

    const watchLegal = watch("user_profiles");
    const watchVenue = watch("venue_profiles");
    const watchDocs = watch("document_images");

    useEffect(() => {
        const handlePreview = (files: FileList | null, key: keyof typeof previews) => {
            if (files && files.length > 0) {
                const urls = Array.from(files).map(file => URL.createObjectURL(file));
                setPreviews(prev => ({ ...prev, [key]: urls }));
                return () => urls.forEach(URL.revokeObjectURL);
            }
            setPreviews(prev => ({ ...prev, [key]: [] }));
        };
        handlePreview(watchLegal, 'legal');
        handlePreview(watchVenue, 'venue');
        handlePreview(watchDocs, 'docs');
    }, [watchLegal, watchVenue, watchDocs]);

    const onSubmit = (data: CreateVenueFormData) => {
        const formData = new FormData();
        Object.entries(data).forEach(([key, value]) => {
            if (typeof value === 'string' || typeof value === 'number') formData.append(key, value.toString());
        });

        if (data.user_profiles) Array.from(data.user_profiles).forEach(f => formData.append('user_profiles[]', f));
        if (data.venue_profiles) Array.from(data.venue_profiles).forEach(f => formData.append('venue_profiles[]', f));
        if (data.document_images) Array.from(data.document_images).forEach(f => formData.append('document_images[]', f));

        createVenueMutate(formData as any, {
            onSuccess: () => {
                showNotification("Gửi hồ sơ thành công!", "success");
                navigate('/partner/dashboard');
            },
            onError: (err: any) => showNotification(err.response?.data?.message || "Lỗi hệ thống", "error")
        });
    };

    // Component dùng chung cho Preview ảnh để code sạch hơn
    const ImagePreviewList = ({ images }: { images: string[] }) => (
        <div className="flex gap-2 flex-wrap mt-3">
            {images.map((url, i) => (
                <div key={i} className="relative group overflow-hidden rounded-xl border-2 border-white shadow-sm hover:shadow-md transition-all">
                    <img src={url} className="w-24 h-24 object-cover" alt="preview" />
                    <div className="absolute inset-0 bg-black/10 group-hover:bg-transparent transition-colors"></div>
                </div>
            ))}
        </div>
    );

    if (isLoading) return <div className="h-screen flex items-center justify-center text-xs font-bold text-emerald-600">Đang tải...</div>;

    return (
        <div className="min-h-screen bg-[#F8FAFC] py-8 px-4 text-xs">
            {hasRegistration ? (
                <div className="min-h-screen flex items-center justify-center bg-[#F3F4F6] px-4 py-10 font-sans">
                    <div className="max-w-2xl w-full bg-white p-6 md:p-8 rounded-3xl shadow-xl border border-gray-100 relative overflow-hidden">
                        <div className="absolute top-0 right-0 w-64 h-64 bg-blue-50 rounded-full mix-blend-multiply filter blur-3xl opacity-50 -translate-y-1/2 translate-x-1/2"></div>
                        <div className="absolute bottom-0 left-0 w-64 h-64 bg-emerald-50 rounded-full mix-blend-multiply filter blur-3xl opacity-50 translate-y-1/2 -translate-x-1/2"></div>

                        <div className="relative z-10">
                            <div className="text-center mb-8">
                                <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner">
                                    <i className="fa-solid fa-clipboard-check text-3xl text-blue-600"></i>
                                </div>
                                <h2 className="text-2xl font-bold text-gray-900 mb-2">Hồ sơ đã được ghi nhận</h2>
                                <p className="text-gray-500 max-w-md mx-auto text-sm leading-relaxed">
                                    Tài khoản này đã gửi hồ sơ đăng ký. Bạn không cần tạo mới. Vui lòng chọn mục bên dưới để kiểm tra chi tiết.
                                </p>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                                <div
                                    onClick={() => navigate('/partner/merchant-profile', { state: merchant })}
                                    className="group bg-white p-5 rounded-2xl border border-gray-200 hover:border-blue-500 hover:shadow-md transition-all cursor-pointer flex flex-col items-center text-center relative"
                                >
                                    <div className="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition">
                                        <i className="fa-solid fa-building-user"></i>
                                    </div>
                                    <h3 className="text-base font-bold text-gray-800 mb-1">Thông Tin Doanh Nghiệp</h3>
                                    <p className="text-xs text-gray-500 mb-3 line-clamp-1 px-2">{merchant?.business_name || "Đang cập nhật..."}</p>
                                    <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ${merchant?.status === 'approved' ? 'bg-green-100 text-green-700' : merchant?.status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'}`}>
                                        {merchant?.status === 'approved' ? 'Đã duyệt' : merchant?.status === 'rejected' ? 'Bị từ chối' : 'Chờ duyệt'}
                                    </span>
                                </div>

                                <div
                                    onClick={() => navigate('/partner/venue-profile', { state: venue })}
                                    className="group bg-white p-5 rounded-2xl border border-gray-200 hover:border-emerald-500 hover:shadow-md transition-all cursor-pointer flex flex-col items-center text-center relative"
                                >
                                    <div className="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition">
                                        <i className="fa-solid fa-map-location-dot"></i>
                                    </div>
                                    <h3 className="text-base font-bold text-gray-800 mb-1">Thông Tin Bãi Sân</h3>
                                    <p className="text-xs text-gray-500 mb-3 line-clamp-1 px-2">{venue?.name || "Đang cập nhật..."}</p>
                                    <span className="bg-gray-100 text-gray-600 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">Xem chi tiết</span>
                                </div>
                            </div>

                            <div className="text-center">
                                <button onClick={() => navigate('/')} className="px-6 py-2.5 bg-emerald-600 text-white text-sm rounded-xl font-bold hover:bg-emerald-500 transition shadow-md inline-flex items-center gap-2">
                                    <i className="fa-solid fa-house"></i> Về trang chủ
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            ) : (
                <form onSubmit={handleSubmit(onSubmit)} className="max-w-5xl mx-auto space-y-6">
                    <header className="text-center mb-8">
                        <h1 className="text-2xl font-black text-gray-900 uppercase tracking-tight">Đăng Ký Đối Tác</h1>
                        <p className="text-gray-500 text-[11px] mt-1">Vui lòng cung cấp thông tin chính xác để được phê duyệt nhanh nhất</p>
                    </header>

                    {/* SECTION 1: MERCHANT */}
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div className="bg-blue-600 px-6 py-3 text-white flex items-center gap-3">
                            <i className="fa-solid fa-id-card text-base"></i>
                            <h2 className="text-sm font-bold uppercase">Thông tin chủ sở hữu</h2>
                        </div>
                        <div className="p-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div className="lg:col-span-2 space-y-4">
                                <Input label="Tên doanh nghiệp / Chủ sân *" placeholder="Ví dụ: Nguyễn Văn A" error={errors.business_name?.message} {...register("business_name", { required: "Bắt buộc" })} />
                                <Input label="Địa chỉ thường trú *" placeholder="Số nhà, tên đường..." error={errors.business_address?.message} {...register("business_address", { required: "Bắt buộc" })} />
                                <div className="grid grid-cols-2 gap-4">
                                    <Input label="Ngân hàng *" placeholder="Vietcombank..." error={errors.bank_name?.message} {...register("bank_name", { required: "Bắt buộc" })} />
                                    <Input label="Số tài khoản *" placeholder="Số tài khoản..." error={errors.bank_account_number?.message} {...register("bank_account_number", { required: "Bắt buộc", pattern: { value: /^[0-9]+$/, message: "Chỉ nhập số" } })} />
                                </div>
                                <Input label="Chủ tài khoản (Không dấu) *" placeholder="NGUYEN VAN A" error={errors.bank_account_name?.message} {...register("bank_account_name", { required: "Bắt buộc" })} />
                            </div>
                            <div className="space-y-3">
                                <label className="block text-[11px] font-bold text-gray-600 uppercase">Ảnh CCCD / Giấy phép *</label>
                                <div className={`h-32 border-2 border-dashed rounded-xl flex flex-col items-center justify-center relative transition hover:bg-blue-50/50 ${errors.user_profiles ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50'}`}>
                                    <input type="file" multiple accept="image/*" className="absolute inset-0 opacity-0 cursor-pointer" {...register("user_profiles", { required: "Thiếu ảnh" })} />
                                    <i className="fa-solid fa-cloud-arrow-up text-xl text-blue-400 mb-1"></i>
                                    <p className="text-[10px] text-gray-400">Click để tải ảnh</p>
                                </div>
                                <ImagePreviewList images={previews.legal} />
                            </div>
                        </div>
                    </div>

                    {/* SECTION 2: VENUE */}
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div className="bg-emerald-600 px-6 py-3 text-white flex items-center gap-3">
                            <i className="fa-solid fa-map-location-dot text-base"></i>
                            <h2 className="text-sm font-bold uppercase">Thông tin sân & Vị trí</h2>
                        </div>
                        <div className="p-6">
                            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                <div className="space-y-4">
                                    <Input label="Tên sân hiển thị *" placeholder="Sân bóng đá ABC..." error={errors.venue_name?.message} {...register("venue_name", { required: "Bắt buộc" })} />
                                    <div className="grid grid-cols-2 gap-4">
                                        <Input label="Số điện thoại sân *" placeholder="09..." error={errors.venue_phone?.message} {...register("venue_phone", { required: "Bắt buộc" })} />
                                        <Input label="Địa chỉ cụ thể *" placeholder="Ngõ, ngách..." error={errors.address_detail?.message} {...register("address_detail", { required: "Bắt buộc" })} />
                                    </div>
                                    <div className="grid grid-cols-2 gap-4">
                                        <select className="w-full px-3 py-2 text-[11px] bg-gray-50 border border-gray-200 rounded-lg outline-none" {...register("province_id", { required: true })}>
                                            <option value="">Tỉnh/Thành</option>
                                            {PROVINCES.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
                                        </select>
                                        <select className="w-full px-3 py-2 text-[11px] bg-gray-50 border border-gray-200 rounded-lg outline-none" {...register("district_id", { required: true })}>
                                            <option value="">Quận/Huyện</option>
                                            {DISTRICTS.map(d => <option key={d.id} value={d.id}>{d.name}</option>)}
                                        </select>
                                    </div>
                                    <div className="flex gap-4">
                                        <div className="flex-1">
                                            <label className="text-[10px] font-bold text-gray-400 uppercase mb-1 block">Mở cửa</label>
                                            <input type="time" className="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs" {...register("open_time", { required: true })} />
                                        </div>
                                        <div className="flex-1">
                                            <label className="text-[10px] font-bold text-gray-400 uppercase mb-1 block">Đóng cửa</label>
                                            <input type="time" className="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs" {...register("close_time", { required: true })} />
                                        </div>
                                    </div>

                                    {/* PHẦN REVIEW ẢNH SÂN VÀ GIẤY TỜ SÂN */}
                                    <div className="grid grid-cols-1 gap-6 pt-2">
                                        <div>
                                            <label className="text-[11px] font-bold mb-2 block uppercase text-gray-500">Ảnh thực tế sân *</label>
                                            <div className="h-24 border border-dashed border-emerald-200 bg-emerald-50 rounded-lg flex items-center justify-center relative hover:bg-emerald-100 transition">
                                                <input type="file" multiple accept="image/*" className="absolute inset-0 opacity-0 cursor-pointer" {...register("venue_profiles", { required: true })} />
                                                <div className="text-center">
                                                    <i className="fa-solid fa-camera text-emerald-400 text-lg"></i>
                                                    <p className="text-[9px] text-emerald-600 block">Tải ảnh sân</p>
                                                </div>
                                            </div>
                                            <ImagePreviewList images={previews.venue} />
                                        </div>

                                        <div>
                                            <label className="text-[11px] font-bold mb-2 block uppercase text-gray-500">Giấy tờ pháp lý sân *</label>
                                            <div className="h-24 border border-dashed border-blue-200 bg-blue-50 rounded-lg flex items-center justify-center relative hover:bg-blue-100 transition">
                                                <input type="file" multiple accept="image/*" className="absolute inset-0 opacity-0 cursor-pointer" {...register("document_images", { required: true })} />
                                                <div className="text-center">
                                                    <i className="fa-solid fa-file-shield text-blue-400 text-lg"></i>
                                                    <p className="text-[9px] text-blue-600 block">Tải giấy phép</p>
                                                </div>
                                            </div>
                                            <ImagePreviewList images={previews.docs} />
                                        </div>
                                    </div>
                                </div>

                                <div className="space-y-3">
                                    <label className="block text-[11px] font-bold text-gray-600 uppercase">Vị trí bản đồ (Click để chọn) *</label>
                                    <div className="h-[400px] rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                                        <MapContainer center={[21.0285, 105.8542]} zoom={13} style={{ height: "100%", width: "100%", zIndex: 0 }}>
                                            <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
                                            <LocationMarker setMarker={setMapMarker} setValue={setValue} />
                                            {mapMarker && <Marker position={[mapMarker.lat, mapMarker.lng]} />}
                                        </MapContainer>
                                    </div>
                                    <div className="bg-gray-100 p-2 rounded-lg flex justify-between items-center">
                                        <span className="text-[10px] text-gray-500 font-bold uppercase">Tọa độ đã chọn:</span>
                                        <code className="text-[11px] text-emerald-600 font-bold">{watch("lat").toFixed(6)}, {watch("lng").toFixed(6)}</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* SUBMIT */}
                    <div className="sticky bottom-4 pt-4 z-[999]">
                        <button
                            type="submit"
                            disabled={isCreating}
                            className={`w-full max-w-sm mx-auto block py-3 rounded-xl text-white font-bold text-sm shadow-xl transition-all ${isCreating ? 'bg-gray-400 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700 active:scale-95'}`}
                        >
                            {isCreating ? <i className="fa-solid fa-spinner fa-spin mr-2"></i> : <i className="fa-solid fa-paper-plane mr-2"></i>}
                            {isCreating ? 'ĐANG XỬ LÝ...' : 'GỬI HỒ SƠ ĐĂNG KÝ'}
                        </button>
                    </div>
                </form>
            )}
        </div>
    );
};

export default CreateVenue;