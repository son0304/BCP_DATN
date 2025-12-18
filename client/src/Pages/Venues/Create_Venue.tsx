import { useEffect, useState } from "react";
import { useForm, useFieldArray } from "react-hook-form";
import { MapContainer, TileLayer, Marker, useMapEvents } from "react-leaflet";
import "leaflet/dist/leaflet.css";
import L from "leaflet";
import Input from "../../Components/Input";
import { useFetchData, usePostData } from "../../Hooks/useApi";
import { useNotification } from "../../Components/Notification";
import { useLocation, useNavigate } from "react-router-dom";

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
    courts: CourtData[];
};

type DataApi = {
    merchant: any;
    venue: Venue
}

// --- MOCK DATA ---
const PROVINCES = [{ id: 6, name: "Hà Nội" }, { id: 2, name: "TP. HCM" }];
const DISTRICTS = [{ id: 26, name: "Quận Ba Đình" }, { id: 102, name: "Quận Cầu Giấy" }];
const VENUE_TYPES = [{ id: 1, name: "Sân 5 người" }, { id: 2, name: "Sân 7 người" }];
const SURFACES = [
    { id: "artificial_grass", name: "Cỏ nhân tạo" },
    { id: "natural_grass", name: "Cỏ tự nhiên" },
    { id: "concrete", name: "Sân bê tông" }
];

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
    const [mapMarker, setMapMarker] = useState<{ lat: number; lng: number } | null>(null);

    const { mutate: createVenueMutate, isPending: isCreating } = usePostData('venues');
    const { data, isLoading } = useFetchData('merchant');
    const { showNotification } = useNotification();
    const navigate = useNavigate();



    const dataApi = data?.data as DataApi;
    const merchant = dataApi?.merchant;
    const venue = dataApi?.venue;

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

    const { fields, append, remove } = useFieldArray({ control, name: "courts" });
    const legalFiles = watch("user_profiles");
    const venueFiles = watch("venue_profiles");

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
                navigate('/partner/congratulations');
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

                    {/* Background Decoration */}
                    <div className="absolute top-0 right-0 w-64 h-64 bg-blue-50 rounded-full mix-blend-multiply filter blur-3xl opacity-50 -translate-y-1/2 translate-x-1/2"></div>
                    <div className="absolute bottom-0 left-0 w-64 h-64 bg-emerald-50 rounded-full mix-blend-multiply filter blur-3xl opacity-50 translate-y-1/2 -translate-x-1/2"></div>

                    <div className="relative z-10">
                        {/* Header */}
                        <div className="text-center mb-8"> {/* Giảm mb-10 xuống mb-8 */}
                            {/* Giảm size icon container w-20 -> w-16 */}
                            <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner">
                                <i className="fa-solid fa-clipboard-check text-3xl text-blue-600"></i> {/* text-4xl -> text-3xl */}
                            </div>

                            {/* Giảm text-3xl -> text-2xl */}
                            <h2 className="text-2xl font-bold text-gray-900 mb-2">Hồ sơ đã được ghi nhận</h2>

                            {/* Giảm text-lg -> text-sm */}
                            <p className="text-gray-500 max-w-md mx-auto text-sm leading-relaxed">
                                Tài khoản này đã gửi hồ sơ đăng ký. Bạn không cần tạo mới. Vui lòng chọn mục bên dưới để kiểm tra chi tiết.
                            </p>
                        </div>

                        {/* 2 Đường dẫn kiểm tra (Cards) */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8"> {/* gap-6 -> gap-4 */}

                            {/* Card 1: Thông tin Doanh Nghiệp */}
                            <div
                                onClick={() => navigate('/partner/merchant-profile', { state: merchant })}
                                className="group bg-white p-5 rounded-2xl border border-gray-200 hover:border-blue-500 hover:shadow-md transition-all cursor-pointer flex flex-col items-center text-center relative"
                            >
                                {/* Giảm w-14 -> w-12, text-2xl -> text-xl */}
                                <div className="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition">
                                    <i className="fa-solid fa-building-user"></i>
                                </div>

                                {/* text-lg -> text-base */}
                                <h3 className="text-base font-bold text-gray-800 mb-1">Thông Tin Doanh Nghiệp</h3>

                                {/* text-sm -> text-xs */}
                                <p className="text-xs text-gray-500 mb-3 line-clamp-1 px-2">{merchant?.business_name || "Đang cập nhật..."}</p>

                                {/* Status Badge: text-xs -> text-[10px] */}
                                <span
                                    className={`px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                           ${merchant?.status === 'approved'
                                            ? 'bg-green-100 text-green-700'
                                            : merchant?.status === 'rejected'
                                                ? 'bg-red-100 text-red-700'
                                                : merchant?.status === 'resubmitted'
                                                    ? 'bg-blue-100 text-blue-700'
                                                    : 'bg-yellow-100 text-yellow-700'
                                        }`}
                                >
                                    {merchant?.status === 'approved'
                                        ? 'Đã duyệt'
                                        : merchant?.status === 'rejected'
                                            ? 'Bị từ chối'
                                            : merchant?.status === 'resubmitted'
                                                ? 'Chờ duyệt lại'
                                                : 'Chờ duyệt'}
                                </span>

                                <span className="absolute top-3 right-3 text-gray-300 group-hover:text-blue-500 transition text-xs">
                                    <i className="fa-solid fa-arrow-right"></i>
                                </span>
                            </div>

                            {/* Card 2: Thông tin Sân Bãi */}
                            <div
                                onClick={() => navigate('/partner/venue-profile', { state: venue })}
                                className="group bg-white p-5 rounded-2xl border border-gray-200 hover:border-emerald-500 hover:shadow-md transition-all cursor-pointer flex flex-col items-center text-center relative"
                            >
                                <div className="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition">
                                    <i className="fa-solid fa-map-location-dot"></i>
                                </div>

                                <h3 className="text-base font-bold text-gray-800 mb-1">Thông Tin Bãi Sân</h3>
                                <p className="text-xs text-gray-500 mb-3 line-clamp-1 px-2">{venue?.name || "Đang cập nhật..."}</p>

                                <span className="bg-gray-100 text-gray-600 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                    Xem chi tiết
                                </span>

                                <span className="absolute top-3 right-3 text-gray-300 group-hover:text-emerald-500 transition text-xs">
                                    <i className="fa-solid fa-arrow-right"></i>
                                </span>
                            </div>
                        </div>

                        {/* Footer Action */}
                        <div className="text-center">
                            <button onClick={() => navigate('/')} className="px-6 py-2.5 bg-emerald-600 text-white text-sm rounded-xl font-bold hover:bg-emerald-500 transition shadow-md inline-flex items-center gap-2">
                                <i className="fa-solid fa-house"></i>
                                Về trang chủ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        ) : (
            <div className="min-h-screen bg-[#F3F4F6] py-12 px-4 font-sans text-gray-800">
                <form onSubmit={handleSubmit(onSubmit)} className="max-w-7xl mx-auto">

                    {/* HEADER */}
                    <div className="text-center mb-12">
                        <h1 className="text-4xl font-extrabold text-[#111827] tracking-tight">Đăng Ký Đối Tác</h1>
                        <p className="text-gray-500 mt-3 text-lg max-w-2xl mx-auto">Hãy điền thông tin chi tiết để chúng tôi xác thực và đưa sân bóng của bạn tiếp cận hàng ngàn khách hàng.</p>
                    </div>

                    <div className="space-y-8">
                        {/* --- SECTION 1: MERCHANT INFO --- */}
                        <div className="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="bg-gradient-to-r from-blue-600 to-blue-500 px-8 py-4 flex items-center gap-3">
                                <div className="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-sm">1</div>
                                <h2 className="text-xl font-bold text-white">Thông Tin Chủ Sở Hữu</h2>
                            </div>

                            <div className="p-8">
                                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
                                    {/* Left: Business Info */}
                                    <div className="lg:col-span-8 space-y-6">
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div className="md:col-span-2">
                                                <Input label="Tên Doanh Nghiệp / Cá Nhân" id="business_name" placeholder="Ví dụ: Công ty TNHH Thể Thao..." error={errors.business_name?.message} {...register("business_name", { required: "Nhập tên doanh nghiệp" })} />
                                            </div>
                                            <div className="md:col-span-2">
                                                <Input label="Địa chỉ đăng ký kinh doanh" id="business_address" placeholder="Số nhà, đường, phường/xã..." {...register("business_address", { required: "Nhập địa chỉ" })} />
                                            </div>
                                        </div>

                                        {/* Bank Group */}
                                        <div className="bg-gray-50 rounded-2xl p-6 border border-gray-200">
                                            <h3 className="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Thông tin thanh toán</h3>
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                                                <Input label="Ngân hàng" id="bank_name" placeholder="MB Bank" {...register("bank_name", { required: "Nhập tên ngân hàng" })} />
                                                <Input label="Số tài khoản" id="bank_account_number" placeholder="0000xxxxx" {...register("bank_account_number", { required: "Nhập số tài khoản" })} />
                                                <div className="md:col-span-2">
                                                    <Input label="Chủ tài khoản (Viết hoa không dấu)" id="bank_account_name" placeholder="NGUYEN VAN A" {...register("bank_account_name", { required: "Nhập tên chủ tài khoản" })} />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Right: Upload */}
                                    <div className="lg:col-span-4 flex flex-col">
                                        <label className="block text-sm font-bold text-gray-700 mb-2">Giấy phép KD / CCCD</label>
                                        <div className="flex-1 bg-blue-50 border-2 border-dashed border-blue-200 rounded-2xl p-6 flex flex-col items-center justify-center text-center hover:bg-blue-100 transition cursor-pointer relative group min-h-[200px]">
                                            <input
                                                type="file"
                                                multiple
                                                accept="image/*"
                                                className="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                                {...register("user_profiles", { required: "Vui lòng tải giấy tờ" })}
                                            />
                                            <div className="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-3 shadow-sm group-hover:scale-110 transition">
                                                <i className="fa-solid fa-cloud-arrow-up text-2xl text-blue-500"></i>
                                            </div>
                                            <span className="text-blue-700 font-semibold">Tải lên tài liệu</span>
                                            <span className="text-blue-400 text-xs mt-1">Hỗ trợ .JPG, .PNG</span>
                                        </div>
                                        {errors.user_profiles && <p className="text-red-500 text-sm mt-2">{errors.user_profiles.message}</p>}

                                        {/* Previews */}
                                        {legalPreviews.length > 0 && (
                                            <div className="mt-4 grid grid-cols-3 gap-2">
                                                {legalPreviews.map((src, i) => (
                                                    <div key={i} className="aspect-square rounded-lg overflow-hidden border border-gray-200">
                                                        <img src={src} className="w-full h-full object-cover" alt="Preview" />
                                                    </div>
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* --- SECTION 2: VENUE & COURTS (MERGED) --- */}
                        <div className="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="bg-gradient-to-r from-emerald-600 to-teal-500 px-8 py-4 flex items-center gap-3">
                                <div className="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-sm">2</div>
                                <h2 className="text-xl font-bold text-white">Thông Tin Địa Điểm & Sân</h2>
                            </div>

                            <div className="p-8">
                                {/* PART A: VENUE INFO & MAP */}
                                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-10">
                                    {/* Left: Info Inputs */}
                                    <div className="lg:col-span-7 space-y-5">
                                        <Input label="Tên Sân Bãi (Thương hiệu)" id="venue_name" placeholder="Sân Bóng K300" error={errors.venue_name?.message} {...register("venue_name", { required: "Nhập tên sân" })} />

                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                                            <Input label="Hotline đặt sân" id="venue_phone" placeholder="09xxxx" {...register("venue_phone", { required: "Nhập SĐT" })} />
                                            <Input label="Địa chỉ chi tiết" id="address_detail" placeholder="Số 123 đường ABC..." {...register("address_detail", { required: "Nhập địa chỉ" })} />
                                        </div>

                                        <div className="grid grid-cols-2 gap-5">
                                            <div>
                                                <label className="text-sm font-bold text-gray-700 mb-1 block">Tỉnh/Thành</label>
                                                <div className="relative">
                                                    <select className="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 appearance-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" {...register("province_id", { required: true })}>
                                                        <option value="">Chọn Tỉnh</option>
                                                        {PROVINCES.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
                                                    </select>
                                                    <i className="fa-solid fa-chevron-down absolute right-4 top-4 text-gray-400 pointer-events-none"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <label className="text-sm font-bold text-gray-700 mb-1 block">Quận/Huyện</label>
                                                <div className="relative">
                                                    <select className="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 appearance-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" {...register("district_id", { required: true })}>
                                                        <option value="">Chọn Quận</option>
                                                        {DISTRICTS.map(d => <option key={d.id} value={d.id}>{d.name}</option>)}
                                                    </select>
                                                    <i className="fa-solid fa-chevron-down absolute right-4 top-4 text-gray-400 pointer-events-none"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="flex gap-5">
                                            <div className="flex-1">
                                                <label className="text-sm font-bold text-gray-700 mb-1 block">Giờ Mở</label>
                                                <input type="time" className="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 focus:ring-2 focus:ring-emerald-500" {...register("open_time", { required: true })} />
                                            </div>
                                            <div className="flex-1">
                                                <label className="text-sm font-bold text-gray-700 mb-1 block">Giờ Đóng</label>
                                                <input type="time" className="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 focus:ring-2 focus:ring-emerald-500" {...register("close_time", { required: true })} />
                                            </div>
                                        </div>

                                        {/* Venue Image Upload */}
                                        <div className="mt-4">
                                            <label className="block text-sm font-bold text-gray-700 mb-2">Hình ảnh sân bãi</label>
                                            <div className="flex items-center gap-4">
                                                <div className="w-24 h-24 bg-emerald-50 border-2 border-dashed border-emerald-300 rounded-xl flex items-center justify-center relative cursor-pointer hover:bg-emerald-100 transition flex-shrink-0">
                                                    <input type="file" multiple accept="image/*" className="absolute inset-0 opacity-0 cursor-pointer" {...register("venue_profiles", { required: "Cần ảnh sân" })} />
                                                    <i className="fa-solid fa-camera text-emerald-500 text-xl"></i>
                                                </div>
                                                <div className="flex gap-2 overflow-x-auto pb-2">
                                                    {venuePreviews.map((src, i) => (
                                                        <img key={i} src={src} className="w-24 h-24 rounded-xl object-cover border border-gray-200" alt="Venue" />
                                                    ))}
                                                </div>
                                            </div>
                                            {errors.venue_profiles && <p className="text-red-500 text-sm mt-1">{errors.venue_profiles.message}</p>}
                                        </div>
                                    </div>

                                    {/* Right: Map */}
                                    <div className="lg:col-span-5">
                                        <div className="sticky top-6">
                                            <label className="block text-sm font-bold text-gray-700 mb-2">Ghim vị trí trên bản đồ</label>
                                            <div className="h-[400px] w-full rounded-2xl overflow-hidden shadow-md border border-gray-200 z-0">
                                                <MapContainer center={[21.0285, 105.8542]} zoom={13} style={{ height: "100%", width: "100%" }}>
                                                    <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
                                                    <LocationMarker setMarker={setMapMarker} setValue={setValue} />
                                                    {mapMarker && <Marker position={[mapMarker.lat, mapMarker.lng]} />}
                                                </MapContainer>
                                            </div>
                                            <p className="text-xs text-gray-500 mt-2 text-center bg-gray-50 py-1 px-3 rounded-full inline-block shadow-sm mx-auto border block w-fit">
                                                <i className="fa-solid fa-location-dot mr-1 text-red-500"></i>
                                                Chạm vào bản đồ để xác định tọa độ
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {/* SEPARATOR */}
                                <div className="border-t border-gray-100 my-8"></div>

                                {/* PART B: COURTS LIST (INSIDE CARD) */}
                                <div>
                                    <div className="flex items-center justify-between mb-6">
                                        <div>
                                            <h3 className="text-xl font-bold text-gray-800">Danh Sách Sân Con</h3>
                                            <p className="text-sm text-gray-500">Khai báo các sân nhỏ thuộc địa điểm này</p>
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => append({ name: `Sân ${fields.length + 1}`, price_per_hour: 0, venue_type_id: "1", surface: "artificial_grass" })}
                                            className="bg-emerald-600 text-white px-4 py-2 rounded-xl font-bold hover:bg-emerald-700 transition shadow-md flex items-center gap-2 text-sm"
                                        >
                                            <i className="fa-solid fa-plus"></i> Thêm sân
                                        </button>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                                        {fields.map((item, index) => (
                                            <div key={item.id} className="bg-gray-50 p-5 rounded-2xl border border-gray-200 relative group hover:border-emerald-300 transition-colors">
                                                <div className="flex justify-between items-start mb-3">
                                                    <div className="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-emerald-600 font-bold text-sm shadow-sm">
                                                        {index + 1}
                                                    </div>
                                                    <button type="button" onClick={() => remove(index)} className="text-gray-400 hover:text-red-500 transition">
                                                        <i className="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </div>

                                                <div className="space-y-3">
                                                    <input
                                                        {...register(`courts.${index}.name` as const, { required: true })}
                                                        className="w-full bg-transparent border-b border-gray-300 focus:border-emerald-500 outline-none font-bold text-gray-800 text-lg placeholder-gray-400"
                                                        placeholder="Tên sân"
                                                    />

                                                    <div className="grid grid-cols-2 gap-3">
                                                        <div>
                                                            <label className="text-[10px] font-bold text-gray-500 uppercase block mb-1">Loại</label>
                                                            <select {...register(`courts.${index}.venue_type_id` as const)} className="w-full px-2 py-1.5 bg-white rounded border border-gray-200 text-sm">
                                                                {VENUE_TYPES.map(t => <option key={t.id} value={t.id}>{t.name}</option>)}
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label className="text-[10px] font-bold text-gray-500 uppercase block mb-1">Mặt sân</label>
                                                            <select {...register(`courts.${index}.surface` as const)} className="w-full px-2 py-1.5 bg-white rounded border border-gray-200 text-sm">
                                                                {SURFACES.map(s => <option key={s.id} value={s.id}>{s.name}</option>)}
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <label className="text-[10px] font-bold text-gray-500 uppercase block mb-1">Giá / Giờ</label>
                                                        <div className="relative">
                                                            <input type="number" {...register(`courts.${index}.price_per_hour` as const, { required: true })} className="w-full pl-3 pr-8 py-2 bg-white border border-gray-200 rounded-lg text-sm font-semibold" placeholder="0" />
                                                            <span className="absolute right-3 top-2 text-gray-400 text-xs">đ</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}

                                        {fields.length === 0 && (
                                            <div className="col-span-full py-8 text-center text-gray-400 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                                                <p className="text-sm">Chưa có sân nào. Nhấn nút "Thêm sân" ở trên.</p>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* --- FOOTER ACTION --- */}
                        <div className="pt-8 border-t border-gray-200 sticky bottom-0 bg-[#F3F4F6]/90 backdrop-blur-sm pb-4 z-30">
                            <button
                                type="submit"
                                disabled={isCreating}
                                className="w-full max-w-lg mx-auto py-4 rounded-full font-bold text-white text-lg shadow-xl bg-gray-900 hover:bg-black transition transform hover:-translate-y-1 flex items-center justify-center gap-3 disabled:opacity-70 disabled:transform-none"
                            >
                                {isCreating ? <i className="fa-solid fa-spinner fa-spin"></i> : <i className="fa-solid fa-paper-plane"></i>}
                                <span>Hoàn Tất & Gửi Hồ Sơ</span>
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        )

    );
};

export default CreateVenue;