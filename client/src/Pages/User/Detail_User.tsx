import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import type { User } from '../../Types/user';
import Booking_history from './Booking_history';

const Detail_User = () => {
    const [user, setUser] = useState<User | null>(null);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        try {
            const userStr = localStorage.getItem("user");
            if (userStr) setUser(JSON.parse(userStr));
            else setError("Vui lòng đăng nhập lại.");
        } catch (e) {
            setError("Dữ liệu lỗi.");
        }
    }, []);

    if (error || !user) return (
        <div className="min-h-[60vh] flex items-center justify-center text-gray-400 text-sm">
            <i className="fa-solid fa-circle-exclamation mr-2"></i> {error || "Đang tải..."}
        </div>
    );
    console.log(user.avt);


    const avatarUrl = user.avt || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=10B981&color=fff`;

    const provinceName = typeof user.province === 'object' && user.province ? (user.province as any).name : user.province;
    const districtName = typeof user.district === 'object' && user.district ? (user.district as any).name : user.district;
    const fullAddress = [districtName, provinceName].filter(Boolean).join(", ") || "Chưa cập nhật địa chỉ";

    return (
        <div className="bg-[#F9FAFB] min-h-screen py-8 px-4 font-sans">
            <div className="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6">

                {/* --- LEFT SIDEBAR: PROFILE --- */}
                <div className="lg:col-span-4 space-y-6">
                    <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:sticky lg:top-24">
                        {/* Header Avatar */}
                        <div className="flex flex-col items-center text-center mb-6">
                            <div className="relative group cursor-pointer">
                                {user.avt && user.avt.length > 0 ? (
                                    user.avt.map((avtItem: any) => (
                                        <img
                                            key={avtItem.id}
                                            src={avtItem.url}
                                            alt="Avatar"
                                            className="w-24 h-24 rounded-full object-cover border-4 border-white shadow-sm"
                                        />
                                    ))
                                ) : (
                                    <img
                                        src="/default-avatar.png"
                                        alt={user.name}
                                        className="w-24 h-24 rounded-full object-cover border-4 border-white shadow-sm"
                                    />
                                )}

                                <div className="absolute bottom-0 right-0 w-6 h-6 bg-[#10B981] border-2 border-white rounded-full flex items-center justify-center text-white text-[10px]">
                                    <i className="fa-solid fa-pen"></i>
                                </div>
                            </div>


                            <h2 className="text-lg font-bold text-gray-800 mt-3">{user.name}</h2>
                            <p className="text-xs text-gray-500 font-medium">{user.email}</p>

                            <div className="mt-3">
                                <span className={`inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border ${user.role_id === 1
                                    ? 'bg-red-50 text-red-600 border-red-100'
                                    : 'bg-green-50 text-green-600 border-green-100'
                                    }`}>
                                    {user.role_id === 1 ? 'Quản trị viên' : 'Thành viên'}
                                </span>
                            </div>
                        </div>

                        <div className="border-t border-gray-50 my-5"></div>

                        {/* Info List */}
                        <div className="space-y-4">
                            <div className="flex items-start gap-3">
                                <div className="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 flex-shrink-0">
                                    <i className="fa-solid fa-phone text-xs"></i>
                                </div>
                                <div>
                                    <p className="text-[10px] uppercase font-bold text-gray-400">Điện thoại</p>
                                    <p className="text-sm font-semibold text-gray-700">{user.phone || "---"}</p>
                                </div>
                            </div>

                            <div className="flex items-start gap-3">
                                <div className="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 flex-shrink-0">
                                    <i className="fa-solid fa-map-location-dot text-xs"></i>
                                </div>
                                <div>
                                    <p className="text-[10px] uppercase font-bold text-gray-400">Địa chỉ</p>
                                    <p className="text-sm font-semibold text-gray-700 leading-snug">{fullAddress}</p>
                                </div>
                            </div>
                        </div>

                        {/* Actions */}
                        <div className="mt-8 pt-4 border-t border-gray-50">
                            <Link
                                to="/profile/edit"
                                state={{ user }}
                                className="flex items-center justify-center w-full py-2.5 text-xs font-bold text-gray-600 bg-gray-50 hover:bg-gray-100 hover:text-[#10B981] rounded-lg border border-gray-200 hover:border-gray-300 transition-all"
                            >
                                <i className="fa-solid fa-user-pen mr-2"></i>
                                Cập nhật thông tin
                            </Link>
                        </div>
                    </div>
                </div>

                {/* --- RIGHT CONTENT: HISTORY --- */}
                <div className="lg:col-span-8">
                    <Booking_history user={user} />
                </div>

            </div>
        </div>
    );
};

export default Detail_User;