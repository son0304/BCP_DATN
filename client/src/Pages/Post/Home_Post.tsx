import React, { useState, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import {
    Phone, Image as ImageIcon, Send, X, Loader2,
    MapPin, Clock, MoreHorizontal, ArrowRight,
    Tag, Zap, Share2, MessageCircle, ShieldCheck
} from 'lucide-react';
import { useFetchData, usePostData } from '../../Hooks/useApi';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import 'dayjs/locale/vi';

dayjs.extend(relativeTime);
dayjs.locale('vi');

export default function Home_Post() {
    const navigate = useNavigate();
    const fileInputRef = useRef<HTMLInputElement>(null);

    // --- 1. STATES ---
    const [content, setContent] = useState("");
    const [phone, setPhone] = useState("");
    const [selectedFiles, setSelectedFiles] = useState<File[]>([]);
    const [previews, setPreviews] = useState<string[]>([]);
    const [isSubmitting, setIsSubmitting] = useState(false);

    // State quản lý bộ lọc: 'all' | 'sale' | 'user_post'
    const [activeFilter, setActiveFilter] = useState<'all' | 'sale' | 'user_post'>('all');

    // --- 2. API DATA ---
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    const { data: response, isLoading: isFetching, refetch } = useFetchData<any>("posts");
    const { mutate } = usePostData<any, any>('posts');

    const allPosts = response?.data?.data || [];

    // --- 3. LOGIC LỌC (QUAN TRỌNG) ---
    const filteredPosts = allPosts.filter((post: any) => {
        if (activeFilter === 'all') return true;
        return post.type === activeFilter;
    });

    // --- 4. HANDLERS ---
    const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
        const files = Array.from(e.target.files || []);
        setSelectedFiles(prev => [...prev, ...files]);
        setPreviews(prev => [...prev, ...files.map(file => URL.createObjectURL(file))]);
    };

    const handleCreatePost = (e: React.FormEvent) => {
        e.preventDefault();
        if (content.trim().length < 5) return alert("Nội dung quá ngắn");
        setIsSubmitting(true);
        const formData = new FormData();
        formData.append('content', content);
        formData.append('type', 'user_post');
        formData.append('phone_contact', phone || user.phone || "");
        selectedFiles.forEach(file => formData.append('images[]', file));

        mutate(formData, {
            onSuccess: () => {
                setContent(""); setPhone(""); setSelectedFiles([]); setPreviews([]);
                refetch();
            },
            onSettled: () => setIsSubmitting(false)
        });
    };

    // --- 5. SUB-COMPONENTS (CARD UI) ---
    const SalePostCard = ({ post }: { post: any }) => (
        <article className="bg-gradient-to-br from-amber-50 to-white rounded-2xl border border-amber-200 overflow-hidden shadow-sm">
            <div className="p-4 flex gap-3 items-center border-b border-amber-100 bg-white/50">
                <div className="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white shadow-sm">
                    <Zap className="w-4 h-4 fill-current" />
                </div>
                <div>
                    <h4 className="text-sm font-black text-amber-900 uppercase leading-none">{post.venue?.name}</h4>
                    <span className="text-[10px] text-amber-600 font-bold uppercase tracking-tighter">Ưu đãi Flash Sale</span>
                </div>
            </div>
            <div className="p-4">
                <p className="text-sm text-slate-700 whitespace-pre-wrap mb-4 italic leading-relaxed">{post.content}</p>
                {post.images?.[0] && (
                    <img src={post.images[0].url} className="w-full h-64 object-cover rounded-xl mb-4 border border-amber-100" />
                )}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-1.5 text-slate-500">
                        <MapPin className="w-4 h-4 text-rose-500" />
                        <span className="text-xs font-bold">{post.venue?.name}</span>
                    </div>
                    <button onClick={() => navigate(`/venues/${post.venue_id}`)} className="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2 rounded-xl text-xs font-black flex items-center gap-2 transition-all shadow-md shadow-amber-200">
                        ĐẶT LỊCH NGAY <ArrowRight className="w-4 h-4" />
                    </button>
                </div>
            </div>
        </article>
    );

    const UserPostCard = ({ post }: { post: any }) => (
        <article className="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:border-emerald-200 transition-all">
            <div className="p-4 flex justify-between items-start">
                <div className="flex items-center gap-3">
                    <img src={post.author?.avt || `https://ui-avatars.com/api/?name=${post.author?.name}&background=random`} className="w-10 h-10 rounded-full border shadow-sm" />
                    <div>
                        <h4 className="text-sm font-bold text-slate-900">{post.author?.name}</h4>
                        <span className="text-[10px] text-slate-400 font-bold flex items-center gap-1">
                            <Clock className="w-3 h-3" /> {dayjs(post.created_at).fromNow()}
                        </span>
                    </div>
                </div>
                <button className="text-slate-300 hover:text-slate-600"><MoreHorizontal className="w-5 h-5" /></button>
            </div>
            <div className="px-4 pb-3">
                <p className="text-[15px] text-slate-700 leading-snug">{post.content}</p>
            </div>
            {post.images && post.images.length > 0 && (
                <div className={`px-4 mb-3 grid gap-1 ${post.images.length >= 2 ? 'grid-cols-2' : 'grid-cols-1'}`}>
                    {post.images.slice(0, 4).map((img: any, idx: number) => (
                        <img key={idx} src={img.url} className={`w-full ${post.images.length === 1 ? 'h-72' : 'h-48'} object-cover rounded-xl border border-slate-100`} />
                    ))}
                </div>
            )}
            <div className="px-4 py-3 border-t border-slate-50 bg-slate-50/50 flex items-center justify-between">
                <div className="flex items-center gap-4">
                    <button className="flex items-center gap-1 text-slate-400 hover:text-emerald-600 transition text-[11px] font-bold"><MessageCircle className="w-4 h-4" /> Quan tâm</button>
                    <button className="flex items-center gap-1 text-slate-400 hover:text-blue-600 transition text-[11px] font-bold"><Share2 className="w-4 h-4" /> Chia sẻ</button>
                </div>
                {post.phone_contact && (
                    <a href={`tel:${post.phone_contact}`} className="flex items-center gap-2 bg-[#00b67a] text-white px-4 py-1.5 rounded-full text-[11px] font-black shadow-sm">
                        <Phone className="w-3 h-3 fill-current" /> {post.phone_contact}
                    </a>
                )}
            </div>
        </article>
    );

    // --- 6. MAIN RENDER ---
    return (
        <div className="bg-[#f8fafc] min-h-screen py-10 px-4">
            <div className="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8">

                {/* CỘT TRÁI: BỘ LỌC (FILTER) */}
                <aside className="hidden lg:block lg:col-span-3 space-y-4">
                    <div className="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 sticky top-24">
                        <h3 className="font-black text-slate-800 uppercase text-[10px] mb-4 tracking-widest opacity-50">Danh mục bài viết</h3>
                        <nav className="space-y-1">
                            <button
                                onClick={() => setActiveFilter('all')}
                                className={`w-full text-left px-4 py-3 rounded-xl font-bold text-sm transition-all ${activeFilter === 'all' ? 'bg-emerald-50 text-emerald-700' : 'text-slate-500 hover:bg-slate-50'}`}
                            >
                                🔥 Tất cả bài viết
                            </button>
                            <button
                                onClick={() => setActiveFilter('sale')}
                                className={`w-full text-left px-4 py-3 rounded-xl font-bold text-sm transition-all ${activeFilter === 'sale' ? 'bg-amber-50 text-amber-700' : 'text-slate-500 hover:bg-slate-50'}`}
                            >
                                ⚡ Ưu đãi Flash Sale
                            </button>
                            <button
                                onClick={() => setActiveFilter('user_post')}
                                className={`w-full text-left px-4 py-3 rounded-xl font-bold text-sm transition-all ${activeFilter === 'user_post' ? 'bg-blue-50 text-blue-700' : 'text-slate-500 hover:bg-slate-50'}`}
                            >
                                🤝 Tìm đối thủ
                            </button>
                        </nav>
                    </div>
                </aside>

                {/* CỘT GIỮA: FEED */}
                <main className="lg:col-span-6 space-y-6">
                    {/* KHUNG ĐĂNG BÀI */}
                    <section className="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                        <div className="flex gap-4">
                            <img src={user?.avt || `https://ui-avatars.com/api/?name=${user?.name}`} className="w-12 h-12 rounded-full border" />
                            <div className="flex-1">
                                <textarea
                                    value={content}
                                    onChange={(e) => setContent(e.target.value)}
                                    placeholder={`${user?.name || 'Bạn'} ơi, hôm nay có kèo gì hot không?`}
                                    className="w-full border-none focus:ring-0 text-slate-700 placeholder:text-slate-400 min-h-[70px] text-base resize-none"
                                />
                                {previews.length > 0 && (
                                    <div className="flex flex-wrap gap-2 mb-4">
                                        {previews.map((url, i) => (
                                            <div key={i} className="relative w-16 h-16 rounded-lg overflow-hidden border">
                                                <img src={url} className="w-full h-full object-cover" />
                                                <button onClick={() => { setPreviews(p => p.filter((_, idx) => idx !== i)); setSelectedFiles(f => f.filter((_, idx) => idx !== i)); }} className="absolute top-0 right-0 bg-black/50 text-white p-0.5"><X className="w-3 h-3" /></button>
                                            </div>
                                        ))}
                                    </div>
                                )}
                                <div className="flex items-center justify-between pt-4 border-t">
                                    <div className="flex items-center gap-3">
                                        <button onClick={() => fileInputRef.current?.click()} className="flex items-center gap-1.5 text-slate-500 hover:text-emerald-600 font-bold text-xs"><ImageIcon className="w-5 h-5 text-emerald-500" /> Ảnh</button>
                                        <input type="text" placeholder="SĐT liên hệ..." value={phone} onChange={e => setPhone(e.target.value)} className="bg-slate-50 border rounded-lg text-[11px] w-32 px-3 py-1.5 focus:ring-0 outline-none" />
                                    </div>
                                    <button onClick={handleCreatePost} disabled={!content.trim() || isSubmitting} className="bg-[#00b67a] hover:bg-[#009664] disabled:bg-slate-200 text-white px-6 py-2 rounded-xl font-black text-[11px] uppercase transition-all shadow-md shadow-emerald-100 flex items-center gap-2">
                                        {isSubmitting ? <Loader2 className="w-3 h-3 animate-spin" /> : <Send className="w-3 h-3" />} Đăng bài
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="file" multiple hidden ref={fileInputRef} onChange={handleFileSelect} accept="image/*" />
                    </section>

                    {/* LIST POSTS ĐÃ LỌC */}
                    <section className="space-y-6">
                        {isFetching ? (
                            <div className="text-center py-20"><Loader2 className="w-8 h-8 animate-spin mx-auto text-slate-300" /></div>
                        ) : filteredPosts.length > 0 ? (
                            filteredPosts.map((post: any) => (
                                post.type === 'sale'
                                    ? <SalePostCard key={post.id} post={post} />
                                    : <UserPostCard key={post.id} post={post} />
                            ))
                        ) : (
                            <div className="bg-white rounded-2xl p-20 text-center border border-dashed border-slate-200 text-slate-400 font-bold text-sm">Chưa có bài viết nào.</div>
                        )}
                    </section>
                </main>

                {/* CỘT PHẢI: QUY ĐỊNH (ĐÃ BỎ APP MOBILE) */}
                <aside className="hidden lg:block lg:col-span-3">
                    <div className="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 sticky top-24">
                        <div className="flex items-center gap-2 mb-4">
                            <ShieldCheck className="w-5 h-5 text-emerald-500" />
                            <h3 className="text-sm font-black text-slate-800 uppercase tracking-tight">Cộng đồng văn minh</h3>
                        </div>
                        <ul className="space-y-4">
                            <li className="text-[11px] text-slate-500 leading-relaxed italic border-l-2 border-emerald-500 pl-3">Vui lòng không đăng tải nội dung không liên quan đến thể thao.</li>
                            <li className="text-[11px] text-slate-500 leading-relaxed italic border-l-2 border-emerald-500 pl-3">Mọi hành vi lừa đảo sẽ bị khóa tài khoản vĩnh viễn.</li>
                            <li className="text-[11px] text-slate-500 leading-relaxed italic border-l-2 border-emerald-500 pl-3">Hãy tôn trọng đối thủ và đồng đội trong mọi kèo đấu.</li>
                        </ul>
                    </div>
                </aside>

            </div>
        </div>
    );
}