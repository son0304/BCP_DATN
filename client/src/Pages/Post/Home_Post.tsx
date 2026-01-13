import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import {
    ArrowRight, MapPin, Zap, Loader2, ShieldCheck, TicketPercent
} from 'lucide-react';
import { useFetchData } from '../../Hooks/useApi';

export default function Home_Post() {
    const navigate = useNavigate();

    // --- 1. API DATA ---
    // Chỉ cần fetch, không cần post data nữa
    const { data: response, isLoading: isFetching } = useFetchData<any>("posts");
    const allPosts = response?.data?.data || [];
    console.log(allPosts);

    // --- 2. SUB-COMPONENT (CARD UI) ---
    const SalePostCard = ({ post }: { post: any }) => (
        <article className="bg-white rounded-2xl border border-amber-100 overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 group">
            {/* Header Card */}
            <div className="p-4 flex gap-3 items-center border-b border-amber-50 bg-gradient-to-r from-amber-50/50 to-white">
                <div className="w-10 h-10 rounded-full bg-amber-500 flex items-center justify-center text-white shadow-lg shadow-amber-200 group-hover:scale-110 transition-transform">
                    <Zap className="w-5 h-5 fill-current" />
                </div>
                <div>
                    <h4 className="text-sm font-black text-slate-800 uppercase leading-none mb-1">
                        {post.venue?.name || "Hệ thống sân"}
                    </h4>
                    <span className="text-[10px] bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-bold uppercase tracking-wide">
                        Flash Sale 🔥
                    </span>
                </div>
            </div>

            {/* Body Card */}
            <div className="p-4">
                <p className="text-sm text-slate-600 whitespace-pre-wrap mb-4 leading-relaxed">
                    {post.content}
                </p>

                {post.images?.[0] && (
                    <div className="relative overflow-hidden rounded-xl mb-4 border border-slate-100">
                        <img
                            src={post.images[0].url}
                            alt="Sale promotion"
                            className="w-full h-64 object-cover transform group-hover:scale-105 transition-transform duration-500"
                        />
                        <div className="absolute top-2 right-2 bg-red-600 text-white text-[10px] font-bold px-2 py-1 rounded shadow-sm">
                            HOT DEAL
                        </div>
                    </div>
                )}

                {/* Footer Card */}
                <div className="flex items-center justify-between mt-2 pt-3 border-t border-slate-50">
                    <div className="flex items-center gap-1.5 text-slate-500">
                        <MapPin className="w-4 h-4 text-rose-500" />
                        <span className="text-xs font-bold truncate max-w-[150px]">
                            {post.venue?.name || "Địa điểm ưu đãi"}
                        </span>
                    </div>
                    <button
                        onClick={() => navigate(`/venues/${post.venue_id}`)}
                        className="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2.5 rounded-xl text-xs font-black flex items-center gap-2 transition-all shadow-lg shadow-amber-200 hover:shadow-amber-300"
                    >
                        SĂN DEAL NGAY <ArrowRight className="w-4 h-4" />
                    </button>
                </div>
            </div>
        </article>
    );

    // --- 3. MAIN RENDER ---
    return (
        <div className="bg-[#f8fafc] min-h-screen py-10 px-4">
            <div className="max-w-5xl mx-auto">

                {/* PAGE HEADER */}
                <div className="flex items-center justify-between mb-8">
                    <div className="flex items-center gap-3">
                        <div className="bg-amber-500 p-2.5 rounded-xl text-white shadow-lg shadow-amber-200">
                            <TicketPercent className="w-6 h-6" />
                        </div>
                        <div>
                            <h1 className="text-2xl font-black text-slate-800 uppercase tracking-tight">Săn Deal Hot</h1>
                            <p className="text-xs text-slate-500 font-medium">Cập nhật các chương trình khuyến mãi mới nhất từ các sân</p>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    {/* CỘT CHÍNH: DANH SÁCH SALE */}
                    <main className="lg:col-span-8 space-y-6">
                        {isFetching ? (
                            <div className="text-center py-20 bg-white rounded-2xl border border-slate-100 shadow-sm">
                                <Loader2 className="w-8 h-8 animate-spin mx-auto text-amber-500 mb-2" />
                                <span className="text-xs text-slate-400 font-bold">Đang tải ưu đãi...</span>
                            </div>
                        ) : allPosts.length > 0 ? (
                            allPosts.map((post: any) => (
                                <SalePostCard key={post.id} post={post} />
                            ))
                        ) : (
                            <div className="bg-white rounded-2xl p-16 text-center border border-dashed border-slate-200">
                                <div className="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                                    <TicketPercent className="w-8 h-8" />
                                </div>
                                <p className="text-slate-500 font-bold text-sm">Hiện tại chưa có chương trình khuyến mãi nào.</p>
                                <p className="text-slate-400 text-xs mt-1">Vui lòng quay lại sau nhé!</p>
                            </div>
                        )}
                    </main>

                    {/* CỘT PHẢI: BANNER / QUY ĐỊNH */}
                    <aside className="hidden lg:block lg:col-span-4 space-y-6">
                        {/* Box Thông tin */}
                        <div className="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 sticky top-24">
                            <div className="flex items-center gap-2 mb-4 pb-3 border-b border-slate-50">
                                <ShieldCheck className="w-5 h-5 text-emerald-500" />
                                <h3 className="text-sm font-black text-slate-800 uppercase tracking-tight">Lưu ý khi đặt sân</h3>
                            </div>
                            <ul className="space-y-4">
                                <li className="flex gap-3 items-start">
                                    <span className="text-emerald-500 font-bold text-xs mt-0.5">01.</span>
                                    <p className="text-[11px] text-slate-500 leading-relaxed">
                                        Vui lòng kiểm tra kỹ thông tin <span className="font-bold text-slate-700">thời gian và địa điểm</span> trước khi xác nhận đặt lịch khuyến mãi.
                                    </p>
                                </li>
                                <li className="flex gap-3 items-start">
                                    <span className="text-emerald-500 font-bold text-xs mt-0.5">02.</span>
                                    <p className="text-[11px] text-slate-500 leading-relaxed">
                                        Các chương trình Flash Sale thường có số lượng giới hạn, hãy nhanh tay đặt lịch.
                                    </p>
                                </li>
                                <li className="flex gap-3 items-start">
                                    <span className="text-emerald-500 font-bold text-xs mt-0.5">03.</span>
                                    <p className="text-[11px] text-slate-500 leading-relaxed">
                                        Nếu có thắc mắc, vui lòng liên hệ trực tiếp số điện thoại của sân được đính kèm trong chi tiết sân.
                                    </p>
                                </li>
                            </ul>

                            {/* Fake Ads / Banner nhỏ */}
                            <div className="mt-6 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl p-4 text-white text-center">
                                <h4 className="font-bold text-sm mb-1">Bạn là chủ sân?</h4>
                                <p className="text-[10px] opacity-90 mb-3">Đăng ký đối tác ngay để tiếp cận hàng ngàn khách hàng.</p>
                                <Link to={'/partner'}>
                                    <button className="bg-white text-indigo-600 text-[10px] font-black px-4 py-2 rounded-lg shadow-sm hover:bg-indigo-50 transition">
                                        LIÊN HỆ HỢP TÁC
                                    </button>
                                </Link>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    );
}