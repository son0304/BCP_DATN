import { useEffect, useState } from "react";
import { useForm, useFieldArray } from "react-hook-form";
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
import type { Venue } from "../../Types/venue";
let DefaultIcon = L.icon({
    iconUrl: icon,
    shadowUrl: iconShadow,
    iconSize: [25, 41],
    iconAnchor: [12, 41],
});
L.Marker.prototype.options.icon = DefaultIcon;

// --- TYPES ---
type CourtData = {
    name: string;
    venue_type_id: string;
    surface: string;
    price_per_hour: number;
};
type CreateVenueFormData = {
    // Merchant Group
    business_name: string;
    business_address: string;
    bank_name: string;
    bank_account_number: string;
    bank_account_name: string;
    user_profiles: FileList;
    // Venue Group
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
    document_images: FileList; // <-- THÊM MỚI THEO YÊU CẦU
    courts: CourtData[];
};
type DataApi = {
    merchant: any;
    venue: Venue
}
type VenueType = {
    id: string;
    name: string;
}

// --- MOCK DATA ---
const PROVINCES = [{ id: 1, name: "Hà Nội" }, { id: 2, name: "TP. HCM" }];
const DISTRICTS = [{ id: 1, name: "Quận Ba Đình" }, { id: 2, name: "Quận Cầu Giấy" }];

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
    // --- STATE & HOOKS ---
    const [legalPreviews, setLegalPreviews] = useState<string[]>([]);
    const [venuePreviews, setVenuePreviews] = useState<string[]>([]);
    const [docPreviews, setDocPreviews] = useState<string[]>([]); // <-- THÊM PREVIEW MỚI
    const [mapMarker, setMapMarker] = useState<{ lat: number; lng: number } | null>(null);
    const [selectedTypes, setSelectedTypes] = useState<VenueType[]>([]);

    const { mutate: createVenueMutate, isPending: isCreating } = usePostData('venues');
    const { data, isLoading } = useFetchData('merchant');
    const { data: venueType } = useFetchData<VenueType[]>('venueType');
    const { showNotification } = useNotification();
    const navigate = useNavigate();

    const dataApi = data?.data as DataApi;
    const merchant = dataApi?.merchant;
    const venue = dataApi?.venue;
    const venueTypes = venueType?.data || [];
    const hasRegistration = !!merchant || !!venue;

    const {
        register,
        control,
        handleSubmit,
        watch,
        setValue,
        formState: { errors },
    } = useForm<CreateVenueFormData>({
        defaultValues: {
            lat: 21.0285,
            lng: 105.8542,
            courts: [{ name: "Sân 1", price_per_hour: 0, venue_type_id: "1", surface: "artificial_grass" }]
        }
    });

    const legalFiles = watch("user_profiles");
    const venueFiles = watch("venue_profiles");
    const docFiles = watch("document_images"); // <-- WATCH THÊM MỚI

    const handleCheck = (type: VenueType) => {
        setSelectedTypes(prev =>
            prev.some(item => item.id === type.id)
                ? prev.filter(item => item.id !== type.id)
                : [...prev, type]
        );
    };

    // --- PREVIEW IMAGES ---
    useEffect(() => {
        if (legalFiles && legalFiles.length > 0) {
            const urls = Array.from(legalFiles).map((file) => URL.createObjectURL(file));
            setLegalPreviews(urls);
            return () => urls.forEach((url) => URL.revokeObjectURL(url));
        } else { setLegalPreviews([]) }
    }, [legalFiles]);

    useEffect(() => {
        if (venueFiles && venueFiles.length > 0) {
            const urls = Array.from(venueFiles).map((file) => URL.createObjectURL(file));
            setVenuePreviews(urls);
            return () => urls.forEach((url) => URL.revokeObjectURL(url));
        } else { setVenuePreviews([]) }
    }, [venueFiles]);

    // Preview cho document_images
    useEffect(() => {
        if (docFiles && docFiles.length > 0) {
            const urls = Array.from(docFiles).map((file) => URL.createObjectURL(file));
            setDocPreviews(urls);
            return () => urls.forEach((url) => URL.revokeObjectURL(url));
        } else { setDocPreviews([]) }
    }, [docFiles]);

    // --- MAIN SUBMIT HANDLER ---
    const onSubmit = (data: CreateVenueFormData) => {
        const formData = new FormData();
        // 1. Merchant
        formData.append('business_name', data.business_name);
        formData.append('business_address', data.business_address);
        formData.append('bank_name', data.bank_name);
        formData.append('bank_account_number', data.bank_account_number);
        formData.append('bank_account_name', data.bank_account_name);
        if (data.user_profiles?.length > 0) Array.from(data.user_profiles).forEach((file) => formData.append('user_profiles[]', file));

        // 2. Venue
        formData.append('venue_name', data.venue_name);
        formData.append('venue_phone', data.venue_phone);
        formData.append('start_time', data.open_time);
        formData.append('end_time', data.close_time);
        formData.append('province_id', data.province_id);
        formData.append('district_id', data.district_id);
        formData.append('address_detail', data.address_detail);
        formData.append('lat', data.lat.toString());
        formData.append('lng', data.lng.toString());
        if (data.venue_profiles?.length > 0) Array.from(data.venue_profiles).forEach((file) => formData.append('venue_profiles[]', file));
        if (data.document_images?.length > 0) Array.from(data.document_images).forEach((file) => formData.append('document_images[]', file)); // <-- GỬI THÊM MỚI

        // 3. Courts
        data.courts.forEach((court, index) => {
            formData.append(`courts[${index}][name]`, court.name);
            formData.append(`courts[${index}][venue_type_id]`, court.venue_type_id);
            formData.append(`courts[${index}][surface]`, court.surface);
            formData.append(`courts[${index}][price_per_hour]`, court.price_per_hour.toString());
        });

        createVenueMutate(formData as any, {
            onSuccess: () => {
                showNotification("Đăng ký đối tác thành công!", "success");
                navigate('/partner/create_venue');
            },
            onError: (err: any) => {
                showNotification(err.response?.data?.message || "Lỗi đăng ký", "error");
            },
        });
    };

    if (isLoading) {
        return (
            <div className="h-screen flex items-center justify-center bg-gray-50">
                <div className="text-center">
                    <i className="fa-solid fa-circle-notch fa-spin text-4xl text-emerald-600 mb-4"></i>
                    <p className="text-gray-500 font-medium">Đang kiểm tra thông tin...</p>
                </div>
            </div>
        );
    }

    return (
        hasRegistration ? (
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
                            <div onClick={() => navigate('/partner/merchant-profile', { state: merchant })} className="group bg-white p-5 rounded-2xl border border-gray-200 hover:border-blue-500 hover:shadow-md transition-all cursor-pointer flex flex-col items-center text-center relative">
                                <div className="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition">
                                    <i className="fa-solid fa-building-user"></i>
                                </div>
                                <h3 className="text-base font-bold text-gray-800 mb-1">Thông Tin Doanh Nghiệp</h3>
                                <p className="text-xs text-gray-500 mb-3 line-clamp-1 px-2">{merchant?.business_name || "Đang cập nhật..."}</p>
                                <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ${merchant?.status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'}`}>
                                    {merchant?.status === 'approved' ? 'Đã duyệt' : 'Chờ duyệt'}
                                </span>
                            </div>
                            <div onClick={() => navigate('/partner/venue-profile', { state: venue })} className="group bg-white p-5 rounded-2xl border border-gray-200 hover:border-emerald-500 hover:shadow-md transition-all cursor-pointer flex flex-col items-center text-center relative">
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
            <div className="min-h-screen bg-[#F3F4F6] py-12 px-4 font-sans text-gray-800">
                <form onSubmit={handleSubmit(onSubmit)} className="max-w-7xl mx-auto">
                    <div className="text-center mb-12">
                        <h1 className="text-4xl font-extrabold text-[#111827] tracking-tight">Đăng Ký Đối Tác</h1>
                        <p className="text-gray-500 mt-3 text-lg max-w-2xl mx-auto">Hãy điền thông tin chi tiết để chúng tôi xác thực và đưa sân bóng của bạn tiếp cận khách hàng.</p>
                    </div>

                    <div className="space-y-8">
                        {/* --- SECTION 1: MERCHANT INFO --- */}
                        <div className="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="bg-gradient-to-r from-blue-600 to-blue-500 px-8 py-4 flex items-center gap-3 text-white font-bold">
                                <span className="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm">1</span>
                                <h2 className="text-xl">Thông Tin Chủ Sở Hữu</h2>
                            </div>
                            <div className="p-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
                                <div className="lg:col-span-8 space-y-6">
                                    <Input label="Tên Doanh Nghiệp" {...register("business_name", { required: true })} />
                                    <Input label="Địa chỉ" {...register("business_address", { required: true })} />
                                    <div className="grid grid-cols-2 gap-5">
                                        <Input label="Ngân hàng" {...register("bank_name", { required: true })} />
                                        <Input label="Số tài khoản" {...register("bank_account_number", { required: true })} />
                                    </div>
                                    <Input label="Chủ tài khoản" {...register("bank_account_name", { required: true })} />
                                </div>
                                <div className="lg:col-span-4">
                                    <label className="block text-sm font-bold text-gray-700 mb-2">Giấy phép KD / CCCD</label>
                                    <div className="bg-blue-50 border-2 border-dashed border-blue-200 rounded-2xl p-6 text-center relative group min-h-[200px] flex flex-col items-center justify-center">
                                        <input type="file" multiple accept="image/*" className="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" {...register("user_profiles", { required: true })} />
                                        <i className="fa-solid fa-cloud-arrow-up text-2xl text-blue-500 mb-2"></i>
                                        <span className="text-blue-700 font-semibold text-xs">Tải lên hồ sơ cá nhân</span>
                                    </div>
                                    <div className="mt-4 grid grid-cols-3 gap-2">
                                        {legalPreviews.map((src, i) => <img key={i} src={src} className="aspect-square rounded-lg object-cover border" alt="preview" />)}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* --- SECTION 2: VENUE INFO & MAP --- */}
                        <div className="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="bg-gradient-to-r from-emerald-600 to-teal-500 px-8 py-4 flex items-center gap-3 text-white font-bold">
                                <span className="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm">2</span>
                                <h2 className="text-xl">Thông Tin Địa Điểm & Sân</h2>
                            </div>
                            <div className="p-8">
                                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-10">
                                    <div className="lg:col-span-7 space-y-5">
                                        <Input label="Tên Sân Bãi" {...register("venue_name", { required: true })} />
                                        <div className="grid grid-cols-2 gap-5">
                                            <Input label="Hotline" {...register("venue_phone", { required: true })} />
                                            <Input label="Địa chỉ chi tiết" {...register("address_detail", { required: true })} />
                                        </div>
                                        <div className="grid grid-cols-2 gap-5">
                                            <select className="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50" {...register("province_id", { required: true })}>
                                                <option value="">Tỉnh/Thành</option>
                                                {PROVINCES.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
                                            </select>
                                            <select className="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50" {...register("district_id", { required: true })}>
                                                <option value="">Quận/Huyện</option>
                                                {DISTRICTS.map(d => <option key={d.id} value={d.id}>{d.name}</option>)}
                                            </select>
                                        </div>
                                        <div className="flex gap-5">
                                            <input type="time" className="flex-1 px-4 py-3 border border-gray-300 rounded-xl" {...register("open_time", { required: true })} />
                                            <input type="time" className="flex-1 px-4 py-3 border border-gray-300 rounded-xl" {...register("close_time", { required: true })} />
                                        </div>
                                        <div>
                                            <label className="text-sm font-bold text-gray-700 mb-3 block">Loại hình kinh doanh</label>
                                            <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
                                                {venueTypes.map(type => (
                                                    <label key={type.id} className={`flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition ${selectedTypes.some(t => t.id === type.id) ? "border-emerald-500 bg-emerald-50" : "bg-white"}`}>
                                                        <input type="checkbox" className="hidden" onChange={() => handleCheck(type)} />
                                                        <span className="text-sm font-medium">{type.name}</span>
                                                    </label>
                                                ))}
                                            </div>
                                        </div>

                                        {/* --- KHU VỰC HÌNH ẢNH (GỒM CẢ DOCUMENT_IMAGES) --- */}
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                            {/* Ảnh thực tế sân */}
                                            <div>
                                                <label className="block text-sm font-bold text-gray-700 mb-2">Hình ảnh sân bãi</label>
                                                <div className="w-full h-24 bg-emerald-50 border-2 border-dashed border-emerald-300 rounded-xl flex items-center justify-center relative cursor-pointer">
                                                    <input type="file" multiple accept="image/*" className="absolute inset-0 opacity-0 cursor-pointer" {...register("venue_profiles", { required: true })} />
                                                    <i className="fa-solid fa-camera text-emerald-500"></i>
                                                </div>
                                                <div className="mt-2 flex gap-2 overflow-x-auto">
                                                    {venuePreviews.map((src, i) => <img key={i} src={src} className="w-12 h-12 rounded-lg object-cover border" alt="venue preview" />)}
                                                </div>
                                            </div>

                                            {/* Trường mới: document_images */}
                                            <div>
                                                <label className="block text-sm font-bold text-gray-700 mb-2">Giấy tờ pháp lý sân</label>
                                                <div className="w-full h-24 bg-blue-50 border-2 border-dashed border-blue-300 rounded-xl flex items-center justify-center relative cursor-pointer">
                                                    <input type="file" multiple accept="image/*" className="absolute inset-0 opacity-0 cursor-pointer" {...register("document_images", { required: true })} />
                                                    <i className="fa-solid fa-file-invoice text-blue-500"></i>
                                                </div>
                                                <div className="mt-2 flex gap-2 overflow-x-auto">
                                                    {docPreviews.map((src, i) => <img key={i} src={src} className="w-12 h-12 rounded-lg object-cover border" alt="doc preview" />)}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="lg:col-span-5">
                                        <div className="h-[400px] w-full rounded-2xl overflow-hidden border">
                                            <MapContainer center={[21.0285, 105.8542]} zoom={13} style={{ height: "100%", width: "100%" }}>
                                                <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
                                                <LocationMarker setMarker={setMapMarker} setValue={setValue} />
                                                {mapMarker && <Marker position={[mapMarker.lat, mapMarker.lng]} />}
                                            </MapContainer>
                                        </div>
                                    </div>
                                </div>

                                <div className="border-t border-gray-100 my-8"></div>

                                
                            </div>
                        </div>

                        <div className="pt-8 sticky bottom-0 bg-[#F3F4F6]/90 backdrop-blur-sm pb-4">
                            <button type="submit" disabled={isCreating} className="w-full max-w-lg mx-auto py-4 rounded-full font-bold text-white text-lg bg-gray-900 shadow-xl flex items-center justify-center gap-3">
                                {isCreating ? <i className="fa-solid fa-spinner fa-spin"></i> : <i className="fa-solid fa-paper-plane"></i>}
                                Hoàn Tất & Gửi Hồ Sơ
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        )
    );
};
export default CreateVenue;