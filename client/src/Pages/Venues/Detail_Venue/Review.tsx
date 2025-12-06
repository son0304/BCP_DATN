import { useState } from "react";
import type { Review } from "../../../Types/review";
import type { Ticket } from "../../../Types/tiket";
import type { User } from "../../../Types/user";
import type { Venue } from "../../../Types/venue";
import { useDeleteData, usePostData } from "../../../Hooks/useApi";
import { useNotification } from '../../../Components/Notification';

interface ReviewVenueProps {
    venue: Venue;
    reviews: Review[];
    tickets: Ticket[];
    user: User | null;
    refetch?: () => void;
}

const Review_Venue = ({ venue, reviews = [], tickets = [], user, refetch }: ReviewVenueProps) => {
    // --- STATE ---
    const [selectedComment, setSelectedComment] = useState(false);
    const [rating, setRating] = useState(0);
    const [hover, setHover] = useState(0);
    const [comment, setComment] = useState("");
    const [selectedFile, setSelectedFile] = useState<File | null>(null);
    const [isEditing, setIsEditing] = useState(false);

    const { showNotification } = useNotification();

    // --- LOGIC ---
    const existingReview = reviews.find((review) => String(review.user_id) === String(user?.id));
    const hasReview = !!existingReview;

    // --- API HOOKS ---
    // 1. Hook Tạo mới và Cập nhật
    const createApi = usePostData('reviews');
    // Cập nhật hook postData để nhận id động khi existingReview thay đổi
    const updateApi = usePostData(existingReview ? `reviews/${existingReview.id}` : '');

    // 2. Hook Xóa (Đặt tên deleteApi để lấy state isPending riêng cho việc xóa)
    const deleteApi = useDeleteData('reviews');

    // Gom trạng thái loading của Form (Tạo/Sửa)
    const isSubmitting = createApi.isPending || updateApi.isPending;

    const hasBooking = tickets.filter((ticket) => {
        if (!user) return false;
        const isCompleted = ticket.status === 'completed';
        const isAtVenue = ticket.items?.some((item) =>
            String(item.booking?.court?.venue?.id) === String(venue.id)
        );
        return isCompleted && isAtVenue;
    });

    const canReview = hasBooking.length > 0;

    // --- HANDLERS ---
    const handleEditClick = () => {
        if (!existingReview) return;
        setRating(existingReview.rating || 0);
        setComment(existingReview.comment || "");
        setIsEditing(true);
        setSelectedComment(true);
    };

    const handleDelete = (id: number) => {
        if (window.confirm("Bạn có chắc chắn muốn xóa đánh giá này không?")) {
            deleteApi.mutate(id, {
                onSuccess: () => {
                    showNotification("Xóa đánh giá thành công", "success");
                    // Reset các state edit nếu đang mở
                    if (isEditing) {
                        setIsEditing(false);
                        setComment("");
                        setRating(0);
                    }
                    refetch && refetch();
                },
                onError: (err: any) => {
                    const msg = err?.response?.data?.message || err.message || "Lỗi xóa đánh giá";
                    showNotification(msg, "error");
                }
            });
        }
    }

    const handleCancelEdit = () => {
        setIsEditing(false);
        setRating(0);
        setComment("");
        setSelectedFile(null);
    };

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            if (file.size > 5 * 1024 * 1024) {
                showNotification("File quá lớn! Vui lòng chọn ảnh dưới 5MB.", "error");
                return;
            }
            setSelectedFile(file);
        }
    };

    const handleSubmit = () => {
        if (rating === 0) return showNotification("Vui lòng chọn số sao đánh giá!", 'error');
        if (!comment.trim()) return showNotification("Vui lòng nhập nội dung trải nghiệm!");

        const formData = new FormData();
        formData.append("venue_id", String(venue.id));
        formData.append("user_id", String(user?.id));
        formData.append("rating", String(rating));
        formData.append("comment", comment);

        if (selectedFile) {
            formData.append("image", selectedFile);
        }

        if (isEditing && existingReview) {
            // Cần thêm _method: PUT cho FormData khi update bằng method POST
            formData.append("_method", "PUT");

            updateApi.mutate(formData, {
                onSuccess: () => {
                    showNotification("Cập nhật đánh giá thành công!", 'success');
                    setIsEditing(false);
                    setSelectedFile(null);
                    if (refetch) refetch();
                },
                onError: (error: any) => {
                    const msg = error?.response?.data?.message || "Lỗi cập nhật đánh giá.";
                    showNotification(msg, 'error');
                }
            });
        } else {
            createApi.mutate(formData, {
                onSuccess: () => {
                    showNotification("Gửi đánh giá thành công!", 'success');
                    setComment("");
                    setRating(0);
                    setSelectedFile(null);
                    if (refetch) refetch();
                },
                onError: (error: any) => {
                    const msg = error?.response?.data?.message || "Lỗi gửi đánh giá.";
                    showNotification(msg, 'error');
                }
            });
        }
    };

    return (
        <section id="reviews" className="bg-white rounded-lg p-5 border border-gray-100 shadow-sm font-sans">
            <div className="flex items-center gap-2 mb-4 border-b border-gray-100 pb-2">
                <i className="fa-solid fa-star text-amber-500"></i>
                <h3 className="text-base font-bold text-gray-800 uppercase tracking-wide">Đánh giá ({reviews?.length})</h3>
            </div>

            <div className="flex items-center gap-6 mb-6 p-4 bg-gray-50 rounded-lg">
                <div className="text-center min-w-[100px]">
                    <div className="text-3xl font-black text-gray-800 leading-none">
                        {Number(venue.reviews_avg_rating || 0).toFixed(1)}
                    </div>
                    <div className="flex justify-center gap-0.5 my-1">
                        {Array.from({ length: 5 }).map((_, i) => (
                            <i key={i} className={`fa-solid fa-star text-xs ${i < Math.round(Number(venue.reviews_avg_rating || 0)) ? 'text-amber-400' : 'text-gray-300'}`}></i>
                        ))}
                    </div>
                    <p className="text-xs text-gray-500 uppercase font-semibold">Trung bình</p>
                </div>

                <div className="flex-1 h-px bg-gray-200 hidden sm:block"></div>

                <div className="text-sm text-gray-500 flex-1">
                    <p>Bạn đánh giá thế nào về sân này?</p>
                    <button
                        onClick={() => setSelectedComment(!selectedComment)}
                        className="mt-1 text-emerald-600 font-semibold hover:underline text-xs flex items-center gap-1"
                    >
                        {selectedComment ? (
                            <><i className="fa-solid fa-angle-up"></i> Đóng lại</>
                        ) : (
                            <><i className="fa-regular fa-pen-to-square"></i> {hasReview ? "Xem đánh giá của bạn" : "Viết đánh giá ngay"}</>
                        )}
                    </button>
                </div>
            </div>

            {selectedComment && (
                <div className="animate-fade-in-down">
                    {/* CASE 1: Hiển thị Review Cũ (Khi không edit) */}
                    {hasReview && !isEditing && (
                        <div className="mb-6 bg-emerald-50 border border-emerald-200 p-4 rounded-lg flex justify-between items-start group shadow-sm transition-all hover:shadow-md">

                            {/* PHẦN TRÁI: NỘI DUNG */}
                            <div className="flex gap-4">
                                <div className="mt-1 p-2 bg-emerald-100 rounded-full h-fit text-emerald-600">
                                    <i className="fa-solid fa-user-check text-xl"></i>
                                </div>

                                <div className="flex-1">
                                    <h4 className="text-sm font-bold text-emerald-800 flex items-center gap-2">
                                        Đánh giá của bạn
                                        <span className="text-[10px] font-normal px-2 py-0.5 bg-emerald-100 rounded-full text-emerald-700">Đã đăng</span>
                                    </h4>

                                    {/* Số sao */}
                                    <div className="flex items-center gap-1 my-1.5">
                                        <span className="text-xl font-bold text-emerald-700 mr-1 leading-none">{existingReview?.rating}</span>
                                        <div className="flex text-xs text-amber-400">
                                            {Array.from({ length: 5 }).map((_, i) => (
                                                <i key={i} className={`fa-solid fa-star ${i < (existingReview?.rating || 0) ? '' : 'text-gray-300'}`}></i>
                                            ))}
                                        </div>
                                    </div>

                                    {/* Nội dung comment */}
                                    <p className="text-sm text-gray-700 leading-relaxed mb-3">
                                        {existingReview?.comment}
                                    </p>

                                    {/* Hình ảnh (Giữ nguyên UI) */}
                                    {existingReview?.images && Array.isArray(existingReview.images) && existingReview.images.length > 0 && (
                                        <div className="flex flex-wrap gap-2">
                                            {existingReview.images.map(img => (
                                                <img
                                                    key={img.id}
                                                    src={img.url}
                                                    alt="review"
                                                    className="w-16 h-16 rounded-lg object-cover border border-emerald-200 hover:scale-105 transition-transform"
                                                />
                                            ))}
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* PHẦN PHẢI: NÚT THAO TÁC (Đã cập nhật loading cho xóa) */}
                            <div className="flex flex-col gap-2 ml-4 shrink-0">
                                {/* Nút Sửa */}
                                <button
                                    onClick={handleEditClick}
                                    // Vô hiệu hóa nút sửa khi đang xóa để tránh xung đột
                                    disabled={deleteApi.isPending}
                                    className={`flex items-center justify-center gap-2 w-[95px] py-1.5 bg-white text-emerald-600 border border-emerald-200 rounded-md text-xs font-bold shadow-sm hover:bg-emerald-600 hover:text-white transition-all active:scale-95 ${deleteApi.isPending ? 'opacity-50 cursor-not-allowed' : ''}`}
                                >
                                    <i className="fa-solid fa-pen-to-square"></i>
                                    <span>Sửa</span>
                                </button>

                                {/* Nút Xóa (Đã thêm Loading Spinner) */}
                                <button
                                    onClick={() => handleDelete(existingReview!.id)}
                                    disabled={deleteApi.isPending}
                                    className={`flex items-center justify-center gap-2 w-[95px] py-1.5 bg-white border rounded-md text-xs font-bold shadow-sm transition-all active:scale-95 
                                        ${deleteApi.isPending
                                            ? 'text-gray-400 border-gray-200 cursor-wait bg-gray-50'
                                            : 'text-red-500 border-red-200 hover:bg-red-500 hover:text-white'
                                        }`}
                                >
                                    {deleteApi.isPending ? (
                                        <div className="animate-spin w-3 h-3 border-2 border-current border-t-transparent rounded-full"></div>
                                    ) : (
                                        <i className="fa-regular fa-trash-can"></i>
                                    )}
                                    <span>{deleteApi.isPending ? "Đang xóa..." : "Xóa"}</span>
                                </button>
                            </div>
                        </div>
                    )}

                    {/* CASE 2: Chưa Booking */}
                    {hasBooking.length === 0 && !hasReview && (
                        <div className="mb-6 bg-gray-50 border border-gray-200 p-4 rounded-lg text-center">
                            <p className="text-sm text-gray-500 italic">
                                Bạn cần đặt sân và hoàn thành trận đấu để viết đánh giá.
                            </p>
                        </div>
                    )}

                    {/* CASE 3: Form Input (Create hoặc Edit) */}
                    {((canReview && !hasReview) || isEditing) && (
                        <div className="mb-6 bg-white border border-gray-200 p-4 rounded-lg shadow-sm relative">
                            {isEditing && (
                                <div className="flex justify-between items-center mb-3 pb-2 border-b border-gray-100">
                                    <h4 className="text-sm font-bold text-gray-700">Chỉnh sửa đánh giá</h4>
                                    <button onClick={handleCancelEdit} className="text-xs text-red-500 hover:text-red-700 flex items-center gap-1">
                                        <i className="fa-solid fa-xmark"></i> Hủy
                                    </button>
                                </div>
                            )}

                            {isSubmitting && (
                                <div className="absolute inset-0 bg-white/70 z-10 flex items-center justify-center rounded-lg">
                                    <div className="animate-spin w-6 h-6 border-2 border-emerald-500 border-t-transparent rounded-full"></div>
                                </div>
                            )}

                            <div className="flex items-center gap-3 mb-3">
                                <span className="text-sm font-bold text-gray-700">Chấm điểm:</span>
                                <div className="flex items-center gap-1">
                                    {[1, 2, 3, 4, 5].map((star) => (
                                        <button
                                            key={star}
                                            type="button"
                                            className="focus:outline-none transition-transform hover:scale-110 active:scale-95 p-0.5"
                                            onClick={() => setRating(star)}
                                            onMouseEnter={() => setHover(star)}
                                            onMouseLeave={() => setHover(0)}
                                            disabled={isSubmitting}
                                        >
                                            <i className={`fa-solid fa-star text-lg transition-colors ${star <= (hover || rating) ? "text-amber-400" : "text-gray-200"}`}></i>
                                        </button>
                                    ))}
                                </div>
                                <span className="text-xs font-medium text-emerald-600 min-w-[80px]">
                                    {hover || rating ? (
                                        rating === 5 || hover === 5 ? "Tuyệt vời" :
                                            rating === 4 || hover === 4 ? "Hài lòng" :
                                                rating === 3 || hover === 3 ? "Bình thường" :
                                                    rating === 2 || hover === 2 ? "Không tốt" : "Tệ"
                                    ) : <span className="text-gray-400">Chọn sao</span>}
                                </span>
                            </div>

                            <textarea
                                value={comment}
                                onChange={(e) => setComment(e.target.value)}
                                disabled={isSubmitting}
                                placeholder={isEditing ? "Cập nhật nội dung..." : "Sân cỏ thế nào? Chia sẻ ngay..."}
                                className="w-full text-sm p-3 bg-gray-50 border border-gray-100 rounded-lg focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 resize-none outline-none text-gray-700 transition-all"
                                rows={3}
                            />

                            <div className="flex justify-between items-center mt-3 pt-3 border-t border-gray-100">
                                <label className={`cursor-pointer transition flex items-center gap-2 group ${isSubmitting ? 'pointer-events-none opacity-50' : 'hover:text-emerald-600 text-gray-500'}`}>
                                    <div className={`w-8 h-8 rounded-full flex items-center justify-center transition-colors ${selectedFile ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 group-hover:bg-emerald-50'}`}>
                                        <i className="fa-solid fa-camera"></i>
                                    </div>
                                    <div className="flex flex-col">
                                        <span className="text-xs font-medium">{selectedFile ? "Đổi ảnh khác" : (isEditing ? "Thay ảnh (tùy chọn)" : "Thêm ảnh")}</span>
                                        {selectedFile && <span className="text-[10px] text-emerald-600 max-w-[100px] truncate">{selectedFile.name}</span>}
                                    </div>
                                    <input
                                        type="file"
                                        className="hidden"
                                        accept="image/*"
                                        onChange={handleFileChange}
                                        disabled={isSubmitting}
                                    />
                                </label>

                                <button
                                    onClick={handleSubmit}
                                    disabled={isSubmitting}
                                    className={`px-5 py-2 text-white text-xs font-bold rounded-lg shadow-sm transition-all flex items-center gap-2
                                        ${isSubmitting ? 'bg-gray-400 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700 active:scale-95'}`}
                                >
                                    {isSubmitting ? (
                                        <>
                                            <div className="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                            Đang xử lý...
                                        </>
                                    ) : (isEditing ? 'Cập nhật' : 'Gửi đánh giá')}
                                </button>
                            </div>
                        </div>
                    )}

                    {/* List Reviews */}
                    <div className="space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                        {reviews.length === 0 ? (
                            <p className="text-center text-gray-400 text-sm italic py-4">Chưa có đánh giá nào.</p>
                        ) : (
                            reviews.map((review) => (
                                <div key={review.id} className={`flex gap-3 border-b border-gray-100 pb-3 last:border-0 ${review.id === existingReview?.id ? 'bg-emerald-50/30 p-2 rounded' : ''}`}>
                                    {/* Ảnh Avatar User */}
                                    <div className="flex-shrink-0">
                                        {/* Kiểm tra mảng hay object để tránh lỗi length */}
                                        {review.user?.images && Array.isArray(review.user.images) && review.user.images.length > 0 ? (
                                            review.user.images.map((img, index) => (
                                                <img
                                                    key={index}
                                                    src={img.url}
                                                    alt="avt"
                                                    className="w-8 h-8 rounded-full object-cover border border-gray-100"
                                                />
                                            ))
                                        ) : (review.user?.images as any)?.url ? (
                                            /* Fallback nếu images là object đơn */
                                            <img
                                                src={(review.user?.images as any).url}
                                                alt="avt"
                                                className="w-8 h-8 rounded-full object-cover border border-gray-100"
                                            />
                                        ) : (
                                            <div className="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">
                                                {review.user?.name?.charAt(0).toUpperCase() || "?"}
                                            </div>
                                        )}
                                    </div>

                                    <div className="flex-1">
                                        <div className="flex justify-between items-start">
                                            <h4 className="text-sm font-bold text-gray-800">
                                                {review.user?.name || "Khách ẩn danh"}
                                                {review.id === existingReview?.id && <span className="ml-2 text-[10px] bg-emerald-100 text-emerald-600 px-1 rounded">Của bạn</span>}
                                            </h4>
                                            <span className="text-[10px] text-gray-400">{new Date(review.created_at).toLocaleDateString('vi-VN')}</span>
                                        </div>
                                        <div className="flex text-[10px] text-amber-400 mb-1">
                                            {Array.from({ length: 5 }).map((_, i) => (
                                                <i key={i} className={`fa-solid fa-star ${i < review.rating ? '' : 'text-gray-200'}`}></i>
                                            ))}
                                        </div>
                                        <p className="text-sm text-gray-600 leading-snug">{review.comment}</p>

                                        {/* Danh sách ảnh đánh giá */}
                                        {review.images && Array.isArray(review.images) && (
                                            <div>
                                                {review.images.map((img) => (
                                                    <img key={img.id} src={img.url} alt="review-img" className="w-20 h-20 object-cover rounded-lg mt-2 mr-2 inline-block border border-gray-100" />
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                </div>
            )}
        </section>
    );
};

export default Review_Venue;