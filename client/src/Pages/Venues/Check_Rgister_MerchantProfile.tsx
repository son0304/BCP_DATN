import { useEffect, useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { useForm } from "react-hook-form";
import { usePostData } from "../../Hooks/useApi"; // Dùng POST để hỗ trợ FormData
import { useNotification } from "../../Components/Notification";
import Input from "../../Components/Input";

// --- TYPES ---
type MerchantUpdateFormData = {
    business_name: string;
    business_address: string;
    bank_name: string;
    bank_account_number: string;
    bank_account_name: string;
    user_profiles: FileList;
};

const MerchantProfileUpdate = () => {
    // 1. Hooks & Config
    const location = useLocation();
    const navigate = useNavigate();
    const { showNotification } = useNotification();

    // Lấy data từ state
    const merchantData = location.state;
    const merchantId = merchantData?.id;

    // --- CHECK STATUS ---
    // Kiểm tra xem hồ sơ đã được duyệt chưa
    const isApproved = merchantData?.status === 'approved';

    const [newPreviews, setNewPreviews] = useState<string[]>([]);
    const { mutate: updateMerchant, isPending } = usePostData(merchantId ? `merchant/${merchantId}` : '');

    const {
        register,
        handleSubmit,
        reset,
        watch,
        formState: { errors },
    } = useForm<MerchantUpdateFormData>();

    const selectedFiles = watch("user_profiles");

    useEffect(() => {
        if (!merchantData || !merchantId) {
            showNotification("Không tìm thấy thông tin hồ sơ hoặc ID không hợp lệ.", "error");
            navigate('/');
            return;
        }

        reset({
            business_name: merchantData.business_name || "",
            business_address: merchantData.business_address || "",
            bank_name: merchantData.bank_name || "",
            bank_account_number: merchantData.bank_account_number || "",
            bank_account_name: merchantData.bank_account_name || "",
        });
    }, [merchantData, merchantId, reset, navigate]);

    useEffect(() => {
        if (selectedFiles && selectedFiles.length > 0) {
            const urls = Array.from(selectedFiles).map((file) => URL.createObjectURL(file));
            setNewPreviews(urls);
            return () => {
                urls.forEach((url) => URL.revokeObjectURL(url));
            };
        } else {
            setNewPreviews([]);
        }
    }, [selectedFiles]);

    const onUpdate = (data: MerchantUpdateFormData) => {
        // Nếu đã duyệt thì chặn hàm này (phòng trường hợp user hack html enable nút)
        if (!merchantId || isApproved) return;

        const formData = new FormData();
        formData.append('business_name', data.business_name);
        formData.append('business_address', data.business_address);
        formData.append('bank_name', data.bank_name);
        formData.append('bank_account_number', data.bank_account_number);
        formData.append('bank_account_name', data.bank_account_name);

        if (data.user_profiles && data.user_profiles.length > 0) {
            Array.from(data.user_profiles).forEach((file) => {
                formData.append('user_profiles[]', file);
            });
        }

        formData.append('_method', 'POST');

        updateMerchant(formData as any, {
            onSuccess: () => {
                showNotification("Cập nhật hồ sơ thành công! Vui lòng chờ duyệt lại.", "success");
                navigate('/partner/create_venue');
            },
            onError: (err: any) => {
                const message = err.response?.data?.message || "Đã có lỗi xảy ra khi cập nhật.";
                showNotification(message, "error");
            }
        });
    };

    if (!merchantData) return null;

    return (
        <div className="min-h-screen bg-gray-50 py-10 px-4 font-sans">
            <div className="max-w-4xl mx-auto">

                {/* --- PAGE HEADER --- */}
                <div className="flex items-center gap-4 mb-6">
                    <button
                        onClick={() => navigate(-1)}
                        className="w-10 h-10 rounded-full bg-white shadow flex items-center justify-center text-gray-600 hover:text-blue-600 transition"
                        title="Quay lại"
                    >
                        <i className="fa-solid fa-arrow-left"></i>
                    </button>
                    <h1 className="text-2xl font-bold text-gray-800">Cập nhật hồ sơ doanh nghiệp</h1>
                </div>

                {/* --- STATUS WARNINGS --- */}
                
                {/* 1. Nếu bị từ chối / yêu cầu sửa */}
                {(!isApproved && (merchantData.status === 'rejected' || merchantData.status === 'request_change')) && (
                    <div className="bg-red-50 border border-red-200 rounded-2xl p-5 mb-6 flex items-start gap-4 shadow-sm">
                        <div className="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0 text-red-500 mt-1">
                            <i className="fa-solid fa-triangle-exclamation text-lg"></i>
                        </div>
                        <div>
                            <h3 className="font-bold text-red-800 mb-1 text-lg">Yêu cầu chỉnh sửa từ Admin</h3>
                            <p className="text-red-700 text-sm leading-relaxed bg-white/50 p-3 rounded-lg border border-red-100 mt-2">
                                {merchantData.admin_note || "Thông tin hồ sơ chưa hợp lệ. Vui lòng kiểm tra kỹ và cập nhật lại các trường bên dưới."}
                            </p>
                        </div>
                    </div>
                )}

                {/* 2. Nếu đã được DUYỆT (Approved) -> Hiển thị thông báo Read-only */}
                {isApproved && (
                    <div className="bg-green-50 border border-green-200 rounded-2xl p-5 mb-6 flex items-center gap-4 shadow-sm">
                        <div className="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 text-green-600">
                            <i className="fa-solid fa-check-circle text-xl"></i>
                        </div>
                        <div>
                            <h3 className="font-bold text-green-800 text-lg">Hồ sơ đã được phê duyệt</h3>
                            <p className="text-green-700 text-sm">Thông tin doanh nghiệp đã được xác thực. Bạn không thể chỉnh sửa thông tin vào lúc này.</p>
                        </div>
                    </div>
                )}

                {/* --- MAIN FORM --- */}
                <form onSubmit={handleSubmit(onUpdate)} className="bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden">

                    <div className={`bg-gradient-to-r px-8 py-5 flex items-center gap-3 ${isApproved ? 'from-green-600 to-teal-600' : 'from-blue-600 to-indigo-600'}`}>
                        <div className="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-lg backdrop-blur-sm">
                            {isApproved ? <i className="fa-solid fa-lock"></i> : <i className="fa-solid fa-pen-to-square"></i>}
                        </div>
                        <h2 className="text-xl font-bold text-white tracking-wide">
                            {isApproved ? "Thông tin hồ sơ" : "Chỉnh sửa thông tin"}
                        </h2>
                    </div>

                    <div className="p-8">
                        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">

                            {/* LEFT COLUMN: TEXT INPUTS */}
                            <div className="lg:col-span-8 space-y-8">
                                <div className="space-y-6">
                                    <h3 className="text-sm font-bold text-gray-400 uppercase tracking-wider border-b pb-2">Thông tin chung</h3>
                                    <Input
                                        label="Tên Doanh Nghiệp / Cá Nhân"
                                        id="business_name"
                                        disabled={isApproved} // Disable
                                        placeholder="Ví dụ: Công ty TNHH Thể Thao..."
                                        error={errors.business_name?.message}
                                        {...register("business_name", { required: !isApproved && "Vui lòng nhập tên doanh nghiệp" })}
                                    />
                                    <Input
                                        label="Địa chỉ đăng ký kinh doanh"
                                        id="business_address"
                                        disabled={isApproved} // Disable
                                        placeholder="Số nhà, đường, phường/xã..."
                                        error={errors.business_address?.message}
                                        {...register("business_address", { required: !isApproved && "Vui lòng nhập địa chỉ" })}
                                    />
                                </div>

                                <div className="space-y-6">
                                    <h3 className="text-sm font-bold text-gray-400 uppercase tracking-wider border-b pb-2">Thông tin thanh toán</h3>
                                    <div className="bg-blue-50/50 rounded-2xl p-6 border border-blue-100/50 grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <Input
                                            label="Ngân hàng"
                                            id="bank_name"
                                            disabled={isApproved} // Disable
                                            placeholder="MB Bank"
                                            {...register("bank_name", { required: !isApproved && "Nhập tên ngân hàng" })}
                                        />
                                        <Input
                                            label="Số tài khoản"
                                            id="bank_account_number"
                                            disabled={isApproved} // Disable
                                            placeholder="0000xxxxx"
                                            {...register("bank_account_number", { required: !isApproved && "Nhập số tài khoản" })}
                                        />
                                        <div className="md:col-span-2">
                                            <Input
                                                label="Chủ tài khoản (Viết hoa không dấu)"
                                                id="bank_account_name"
                                                disabled={isApproved} // Disable
                                                placeholder="NGUYEN VAN A"
                                                {...register("bank_account_name", { required: !isApproved && "Nhập tên chủ tài khoản" })}
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* RIGHT COLUMN: IMAGE UPLOAD */}
                            <div className="lg:col-span-4 flex flex-col">
                                <h3 className="text-sm font-bold text-gray-400 uppercase tracking-wider border-b pb-2 mb-6">Giấy tờ pháp lý</h3>

                                {/* Upload Area - Thay đổi style khi Disabled */}
                                <div className={`flex-1 rounded-2xl p-6 flex flex-col items-center justify-center text-center transition relative group min-h-[250px]
                                    ${isApproved 
                                        ? 'bg-gray-100 border-2 border-gray-200 cursor-not-allowed opacity-70' // Style khi disable
                                        : 'bg-gray-50 border-2 border-dashed border-gray-300 hover:bg-blue-50 hover:border-blue-300 cursor-pointer' // Style khi enable
                                    }`}
                                >
                                    <input
                                        type="file"
                                        multiple
                                        accept="image/*"
                                        disabled={isApproved} // Disable input file
                                        className={`absolute inset-0 w-full h-full z-20 ${isApproved ? 'cursor-not-allowed' : 'cursor-pointer'} opacity-0`}
                                        {...register("user_profiles")}
                                    />

                                    <div className={`w-16 h-16 rounded-full flex items-center justify-center mb-4 shadow-sm z-10 transition
                                        ${isApproved ? 'bg-gray-200' : 'bg-white group-hover:scale-110'}`}>
                                        <i className={`fa-solid text-3xl ${isApproved ? 'fa-lock text-gray-400' : 'fa-cloud-arrow-up text-blue-500'}`}></i>
                                    </div>
                                    
                                    <span className={`font-bold z-10 ${isApproved ? 'text-gray-500' : 'text-blue-700'}`}>
                                        {isApproved ? "Không thể thay đổi ảnh" : "Tải ảnh mới"}
                                    </span>
                                    {!isApproved && (
                                        <span className="text-gray-400 text-xs mt-2 z-10 px-4">Nhấn vào đây để thay đổi ảnh giấy phép KD hoặc CCCD</span>
                                    )}
                                </div>
                                {errors.user_profiles && <p className="text-red-500 text-sm mt-2 font-medium">{errors.user_profiles.message}</p>}

                                {/* IMAGE PREVIEW SECTION */}
                                <div className="mt-6">
                                    <div className="flex justify-between items-end mb-3">
                                        <label className="text-xs font-bold text-gray-500 uppercase">
                                            {newPreviews.length > 0 ? "Ảnh mới chọn:" : "Ảnh hồ sơ:"}
                                        </label>
                                        {newPreviews.length > 0 && (
                                            <span className="text-[10px] bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-bold">Mới</span>
                                        )}
                                    </div>

                                    <div className="grid grid-cols-2 gap-3">
                                        {newPreviews.length > 0 ? (
                                            newPreviews.map((src, i) => (
                                                <div key={i} className="aspect-video rounded-xl overflow-hidden border border-blue-200 shadow-sm relative group">
                                                    <img src={src} className="w-full h-full object-cover" alt="New Preview" />
                                                </div>
                                            ))
                                        ) : (
                                            merchantData.images && merchantData.images.length > 0 ? (
                                                merchantData.images.map((img: any) => (
                                                    <div key={img.id} className="aspect-video rounded-xl overflow-hidden border border-gray-200 shadow-sm relative group">
                                                        <img src={img.url} className="w-full h-full object-cover" alt="Old Data" />
                                                    </div>
                                                ))
                                            ) : (
                                                <div className="col-span-2 py-4 text-center text-gray-400 bg-gray-50 rounded-xl text-sm border border-gray-200 border-dashed">
                                                    Chưa có ảnh nào được tải lên
                                                </div>
                                            )
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Footer Buttons */}
                    <div className="bg-gray-50 px-8 py-5 border-t border-gray-200 flex flex-col md:flex-row justify-end gap-3">
                        <button
                            type="button"
                            onClick={() => navigate(-1)}
                            className="px-6 py-3 rounded-xl font-bold text-gray-600 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-800 transition shadow-sm"
                        >
                            {isApproved ? "Quay lại" : "Hủy bỏ"}
                        </button>

                        {/* CHỈ HIỂN THỊ NÚT LƯU KHI CHƯA APPROVED */}
                        {!isApproved && (
                            <button
                                type="submit"
                                disabled={isPending}
                                className="px-8 py-3 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-lg hover:shadow-blue-500/30 transition flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
                            >
                                {isPending ? <i className="fa-solid fa-circle-notch fa-spin"></i> : <i className="fa-solid fa-floppy-disk"></i>}
                                <span>Lưu thay đổi</span>
                            </button>
                        )}
                    </div>

                </form>
            </div>
        </div>
    );
};

export default MerchantProfileUpdate;