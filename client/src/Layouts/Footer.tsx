
const Footer = () => {
    return (
        <footer className="bg-gradient-to-br from-slate-900 via-gray-900 to-slate-800 text-white relative overflow-hidden">
            {/* Background decoration */}
            <div className="absolute inset-0 bg-gradient-to-r from-[#348738]/10 via-transparent to-[#2d6a2d]/10"></div>
            <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#348738] via-blue-500 to-[#2d6a2d]"></div>
            <div className="absolute top-20 left-10 w-32 h-32 bg-gradient-to-r from-[#348738]/20 to-blue-500/20 rounded-full blur-3xl"></div>
            <div className="absolute bottom-20 right-10 w-40 h-40 bg-gradient-to-r from-blue-500/20 to-[#2d6a2d]/20 rounded-full blur-3xl"></div>

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 relative z-10">
                <div className="grid md:grid-cols-4 grid-cols-2 gap-8 mb-8">
                    <div className="w-full">
                        <div className="flex items-center gap-3 mb-6">
                            <div className="w-12 h-12 bg-gradient-to-br from-[#348738] to-[#2d6a2d] rounded-xl flex items-center justify-center">
                                <i className="fa-solid fa-futbol text-white text-xl"></i>
                            </div>
                            <h3 className="text-3xl font-bold bg-gradient-to-r from-[#348738] to-blue-400 bg-clip-text text-transparent">
                                BCP Sports
                            </h3>
                        </div>
                        <p className="text-gray-300 text-sm leading-relaxed mb-6">
                            Nền tảng đặt sân thể thao trực tuyến hàng đầu. Kết nối người chơi với các sân chất lượng, đặt sân dễ dàng chỉ trong vài giây.
                        </p>
                        <div className="flex gap-4">
                            <a href="#" className="group w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 hover:shadow-lg">
                                <svg className="w-5 h-5 group-hover:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                </svg>
                            </a>
                            <a href="#" className="group w-12 h-12 bg-gradient-to-br from-pink-600 to-pink-700 hover:from-pink-700 hover:to-pink-800 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 hover:shadow-lg">
                                <svg className="w-5 h-5 group-hover:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                                </svg>
                            </a>
                            <a href="#" className="group w-12 h-12 bg-gradient-to-br from-sky-500 to-sky-600 hover:from-sky-600 hover:to-sky-700 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 hover:shadow-lg">
                                <svg className="w-5 h-5 group-hover:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                                </svg>
                            </a>
                            <a href="#" className="group w-12 h-12 bg-gradient-to-br from-blue-700 to-blue-800 hover:from-blue-800 hover:to-blue-900 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 hover:shadow-lg">
                                <svg
                                    className="w-5 h-5 group-hover:scale-110 transition-transform duration-300"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path d="M22.23 0H1.77C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.77 24h20.46C23.208 24 24 23.227 24 22.271V1.729C24 .774 23.208 0 22.23 0zm-14.615 20.452H3.55V9h3.065v11.452zM5.337 7.433c-1.144 0-2.064-.926-2.064-2.065 0-1.138.92-2.063 2.064-2.063s2.064.925 2.064 2.063c0 1.139-.92 2.065-2.064 2.065zm15.673 13.019h-3.066v-5.569c0-1.328-.027-3.037-1.851-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667h-3.065V9h3.065v1.561h.045c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286z" />
                                </svg>

                            </a>
                        </div>
                    </div>

                    <div className="w-full">
                        <h4 className="text-xl font-bold mb-6 text-white flex items-center gap-2">
                            <i className="fa-solid fa-link text-[#348738]"></i>
                            Liên kết nhanh
                        </h4>
                        <ul className="space-y-3">
                            <li>
                                <a href="#" className="text-gray-300 hover:text-white transition-all duration-300 text-sm flex items-center group p-2 rounded-lg hover:bg-white/10">
                                    <i className="fa-solid fa-home mr-3 text-[#348738] group-hover:scale-110 transition-transform duration-300"></i>
                                    <span className="group-hover:translate-x-1 transition-transform duration-300">Trang chủ</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" className="text-gray-300 hover:text-white transition-all duration-300 text-sm flex items-center group p-2 rounded-lg hover:bg-white/10">
                                    <i className="fa-solid fa-info-circle mr-3 text-[#348738] group-hover:scale-110 transition-transform duration-300"></i>
                                    <span className="group-hover:translate-x-1 transition-transform duration-300">Về chúng tôi</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" className="text-gray-300 hover:text-white transition-all duration-300 text-sm flex items-center group p-2 rounded-lg hover:bg-white/10">
                                    <i className="fa-solid fa-cogs mr-3 text-[#348738] group-hover:scale-110 transition-transform duration-300"></i>
                                    <span className="group-hover:translate-x-1 transition-transform duration-300">Dịch vụ</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" className="text-gray-300 hover:text-white transition-all duration-300 text-sm flex items-center group p-2 rounded-lg hover:bg-white/10">
                                    <i className="fa-solid fa-blog mr-3 text-[#348738] group-hover:scale-110 transition-transform duration-300"></i>
                                    <span className="group-hover:translate-x-1 transition-transform duration-300">Blog</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" className="text-gray-300 hover:text-white transition-all duration-300 text-sm flex items-center group p-2 rounded-lg hover:bg-white/10">
                                    <i className="fa-solid fa-envelope mr-3 text-[#348738] group-hover:scale-110 transition-transform duration-300"></i>
                                    <span className="group-hover:translate-x-1 transition-transform duration-300">Liên hệ</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div className="w-full">
                        <h4 className="text-xl font-bold mb-6 text-white flex items-center gap-2">
                            <i className="fa-solid fa-futbol text-[#348738]"></i>
                            Dịch vụ
                        </h4>
                        <ul className="space-y-3">
                            <li>
                                <a href="#" className="text-gray-300 hover:text-white transition-all duration-300 text-sm flex items-center group p-2 rounded-lg hover:bg-white/10">
                                    <span className="mr-3 text-lg group-hover:scale-110 transition-transform duration-300">⚽</span>
                                    <span className="group-hover:translate-x-1 transition-transform duration-300">Đặt sân bóng đá</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" className="text-gray-300 hover:text-white transition-all duration-300 text-sm flex items-center group p-2 rounded-lg hover:bg-white/10">
                                    <span className="mr-3 text-lg group-hover:scale-110 transition-transform duration-300">🏸</span>
                                    <span className="group-hover:translate-x-1 transition-transform duration-300">Đặt sân cầu lông</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" className="text-gray-300 hover:text-white transition-all duration-300 text-sm flex items-center group p-2 rounded-lg hover:bg-white/10">
                                    <span className="mr-3 text-lg group-hover:scale-110 transition-transform duration-300">🎾</span>
                                    <span className="group-hover:translate-x-1 transition-transform duration-300">Đặt sân tennis</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" className="text-gray-300 hover:text-white transition-all duration-300 text-sm flex items-center group p-2 rounded-lg hover:bg-white/10">
                                    <span className="mr-3 text-lg group-hover:scale-110 transition-transform duration-300">🏀</span>
                                    <span className="group-hover:translate-x-1 transition-transform duration-300">Đặt sân bóng rổ</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" className="text-gray-300 hover:text-white transition-all duration-300 text-sm flex items-center group p-2 rounded-lg hover:bg-white/10">
                                    <span className="mr-3 text-lg group-hover:scale-110 transition-transform duration-300">🤝</span>
                                    <span className="group-hover:translate-x-1 transition-transform duration-300">Trở thành đối tác</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div className="w-full">
                        <h4 className="text-xl font-bold mb-6 text-white flex items-center gap-2">
                            <i className="fa-solid fa-phone text-[#348738]"></i>
                            Liên hệ
                        </h4>
                        <ul className="space-y-4">
                            <li className="flex items-start text-sm text-gray-300 group p-2 rounded-lg hover:bg-white/10 transition-all duration-300">
                                <i className="fa-solid fa-location-dot mr-3 mt-1 text-[#348738] group-hover:scale-110 transition-transform duration-300"></i>
                                <span className="group-hover:text-white transition-colors duration-300">123 Đường ABC, Quận 1, TP. Hồ Chí Minh</span>
                            </li>
                            <li className="flex items-center text-sm text-gray-300 group p-2 rounded-lg hover:bg-white/10 transition-all duration-300">
                                <i className="fa-solid fa-phone mr-3 text-[#348738] group-hover:scale-110 transition-transform duration-300"></i>
                                <span className="group-hover:text-white transition-colors duration-300">1900 xxxx</span>
                            </li>
                            <li className="flex items-center text-sm text-gray-300 group p-2 rounded-lg hover:bg-white/10 transition-all duration-300">
                                <i className="fa-solid fa-envelope mr-3 text-[#348738] group-hover:scale-110 transition-transform duration-300"></i>
                                <span className="group-hover:text-white transition-colors duration-300">contact@bcp.vn</span>
                            </li>
                            <li className="flex items-center text-sm text-gray-300 group p-2 rounded-lg hover:bg-white/10 transition-all duration-300">
                                <i className="fa-solid fa-clock mr-3 text-[#348738] group-hover:scale-110 transition-transform duration-300"></i>
                                <span className="group-hover:text-white transition-colors duration-300">8:00 - 22:00 (Hàng ngày)</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {/* Divider */}
                <div className="border-t border-gray-700/50 pt-8">
                    <div className="flex flex-col md:flex-row justify-between items-center gap-6">
                        <div className="flex items-center gap-2">
                            <div className="w-8 h-8 bg-gradient-to-br from-[#348738] to-[#2d6a2d] rounded-lg flex items-center justify-center">
                                <i className="fa-solid fa-futbol text-white text-sm"></i>
                            </div>
                            <p className="text-gray-400 text-sm">
                                © 2025 <span className="font-bold text-white bg-gradient-to-r from-[#348738] to-blue-400 bg-clip-text text-transparent">BCP Sports</span>. All rights reserved.
                            </p>
                        </div>
                        <div className="flex gap-6 text-sm">
                            <a href="#" className="text-gray-400 hover:text-white transition-all duration-300 hover:scale-105 flex items-center gap-1">
                                <i className="fa-solid fa-file-contract text-xs"></i>
                                <span>Điều khoản sử dụng</span>
                            </a>
                            <span className="text-gray-600">|</span>
                            <a href="#" className="text-gray-400 hover:text-white transition-all duration-300 hover:scale-105 flex items-center gap-1">
                                <i className="fa-solid fa-shield-halved text-xs"></i>
                                <span>Chính sách bảo mật</span>
                            </a>
                            <span className="text-gray-600">|</span>
                            <a href="#" className="text-gray-400 hover:text-white transition-all duration-300 hover:scale-105 flex items-center gap-1">
                                <i className="fa-solid fa-cookie-bite text-xs"></i>
                                <span>Cookies</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    )
}

export default Footer