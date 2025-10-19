import List_Venue from "./Venues/List_Venues"

const Content = () => {
    return (

        <>
            {/* Nền xanh lá nhạt */}
            <section className="bg-gradient-to-br from-green-100 via-emerald-50 to-teal-100 h-[200px] md:h-[400px] mt-2 relative overflow-hidden">
                <div className="absolute inset-0 bg-gradient-to-r from-[#348738]/20 via-[#2d6a2d]/20 to-green-400/20"></div>
                <div className="absolute top-10 left-10 w-32 h-32 bg-gradient-to-r from-[#348738]/30 to-green-400/30 rounded-full blur-xl"></div>
                <div className="absolute bottom-10 right-10 w-40 h-40 bg-gradient-to-r from-green-400/30 to-[#348738]/30 rounded-full blur-xl"></div>
            </section>
            
            <section className="container mx-auto max-w-7xl bg-white/95 backdrop-blur-sm md:h-64 h-full md:-mt-20 md:relative md:z-10 rounded-2xl shadow-2xl border border-white/20 p-6">
                <div className="w-full m-auto">
                    <div className="my-6 text-center">
                        {/* Tiêu đề xanh lá */}
                        <h1 className="text-4xl md:text-5xl font-bold text-[#2d6a2d] my-4">
                            Đặt sân ngay
                        </h1>
                        <p className="text-lg text-gray-600">Tìm kiếm sân chơi thể thao phù hợp với bạn</p>
                    </div>
                    <div>
                        <form action="" className="grid md:grid-cols-4 gap-4 grid-cols-1">
                            {/* Input fields màu xanh lá */}
                            <div className="relative group">
                                <div className="absolute inset-0 bg-gradient-to-r from-[#348738] to-[#2d6a2d] rounded-2xl opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>
                                <div className="relative flex items-center border-2 border-gray-200 group-hover:border-[#348738] p-3 w-full rounded-2xl bg-white/80 backdrop-blur-sm transition-all duration-300">
                                    <div className="flex items-center">
                                        <i className="fa-solid fa-futbol text-[#348738] text-lg"></i>
                                        <div className="h-6 border-l border-gray-300 mx-3"></div>
                                    </div>
                                    <div className="flex-1">
                                        <select name="" id="" className="w-full border-none outline-none bg-transparent text-gray-700 font-medium">
                                            <option value="">Chọn môn thể thao</option>
                                            <option value="football">⚽ Bóng đá</option>
                                            <option value="badminton">🏸 Cầu lông</option>
                                            <option value="tennis">🎾 Tennis</option>
                                            <option value="basketball">🏀 Bóng rổ</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div className="relative group">
                                <div className="absolute inset-0 bg-gradient-to-r from-[#348738] to-[#2d6a2d] rounded-2xl opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>
                                <div className="relative flex items-center border-2 border-gray-200 group-hover:border-[#348738] p-3 w-full rounded-2xl bg-white/80 backdrop-blur-sm transition-all duration-300">
                                    <div className="flex items-center">
                                        <i className="fa-solid fa-map-marker-alt text-[#348738] text-lg"></i>
                                        <div className="h-6 border-l border-gray-300 mx-3"></div>
                                    </div>
                                    <div className="flex-1">
                                        <select name="" id="" className="w-full border-none outline-none bg-transparent text-gray-700 font-medium">
                                            <option value="">Chọn khu vực</option>
                                            <option value="district1">Quận 1</option>
                                            <option value="district2">Quận 2</option>
                                            <option value="district3">Quận 3</option>
                                            <option value="district7">Quận 7</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div className="relative group">
                                <div className="absolute inset-0 bg-gradient-to-r from-[#348738] to-[#2d6a2d] rounded-2xl opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>
                                <div className="relative flex items-center border-2 border-gray-200 group-hover:border-[#348738] p-3 w-full rounded-2xl bg-white/80 backdrop-blur-sm transition-all duration-300">
                                    <div className="flex items-center">
                                        <i className="fa-solid fa-calendar text-[#348738] text-lg"></i>
                                        <div className="h-6 border-l border-gray-300 mx-3"></div>
                                    </div>
                                    <div className="flex-1">
                                        <select name="" id="" className="w-full border-none outline-none bg-transparent text-gray-700 font-medium">
                                            <option value="">Chọn ngày</option>
                                            <option value="today">Hôm nay</option>
                                            <option value="tomorrow">Ngày mai</option>
                                            <option value="weekend">Cuối tuần</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {/* --- ĐỔI MÀU CTA --- */}
                            {/* Nút CTA chính màu Cam */}
                            <button className="w-full p-3 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 flex items-center justify-center gap-2">
                                <i className="fa-solid fa-search"></i>
                                <span>Tìm kiếm ngay</span>
                            </button>
                        </form>
                    </div>
                </div>
            </section>
            
            <section className="py-8 md:py-16 from-white to-gray-50">
                <div className="container max-w-7xl mx-auto px-4">
                    <div className="text-center mb-12">
                        {/* Tiêu đề xanh lá */}
                        <h1 className="md:text-5xl text-3xl font-bold text-[#2d6a2d] my-4">
                            Gợi ý cho bạn
                        </h1>
                        <p className="text-lg text-gray-600">Những sân thể thao được yêu thích nhất</p>
                    </div>

                    <List_Venue limit={4} />
                </div>
            </section>


            <section className="py-12 md:py-20 to-green-50">
                <div className="container mx-auto max-w-7xl px-4">
                    <div className="text-center mb-16">
                         {/* Tiêu đề xanh lá */}
                        <h1 className="md:text-5xl text-3xl font-bold text-[#2d6a2d] my-4">
                            Tại sao lại chọn chúng tôi
                        </h1>
                        <p className="text-lg text-gray-600">Những lý do khiến BCP trở thành lựa chọn hàng đầu</p>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        {/* Các card "Why Us" dùng màu xanh lá */}
                        <div className="group relative bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg hover:shadow-2xl flex flex-col items-center justify-center text-center transition-all duration-500 hover:-translate-y-2 border border-white/20 min-h-[280px] p-8">
                            <div className="absolute inset-0 bg-gradient-to-br from-[#348738]/10 to-[#2d6a2d]/10 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            <div className="relative z-10 flex flex-col items-center justify-center">
                                <div className="w-16 h-16 bg-gradient-to-br from-[#2d6a2d] to-[#348738] hover:from-[#348738] hover:to-[#2d6a2d] rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                    <i className="fas fa-bolt text-white text-2xl"></i>
                                </div>
                                <h2 className="text-xl font-bold text-gray-800 mb-3">Đặt sân nhanh chóng</h2>
                                <p className="text-gray-600 text-sm leading-relaxed">
                                    Chỉ mất 2 phút để hoàn tất đặt sân trực tuyến 24/7
                                </p>
                            </div>
                        </div>

                        {/* (Các card 2, 3, 4 tương tự) */}
                        <div className="group relative bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg hover:shadow-2xl flex flex-col items-center justify-center text-center transition-all duration-500 hover:-translate-y-2 border border-white/20 min-h-[280px] p-8">
                            <div className="absolute inset-0 bg-gradient-to-br from-[#348738]/10 to-[#2d6a2d]/10 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            <div className="relative z-10 flex flex-col items-center justify-center">
                                <div className="w-16 h-16 bg-gradient-to-br from-[#2d6a2d] to-[#348738] hover:from-[#348738] hover:to-[#2d6a2d] rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                    <i className="fa-solid fa-hand-holding-dollar text-white text-2xl"></i>
                                </div>
                                <h2 className="text-xl font-bold text-gray-800 mb-3">Giá cả hợp lý</h2>
                                <p className="text-gray-600 text-sm leading-relaxed">
                                    So sánh giá từ nhiều sân, nhiều ưu đãi hấp dẫn
                                </p>
                            </div>
                        </div>

                        <div className="group relative bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg hover:shadow-2xl flex flex-col items-center justify-center text-center transition-all duration-500 hover:-translate-y-2 border border-white/20 min-h-[280px] p-8">
                            <div className="absolute inset-0 bg-gradient-to-br from-[#348738]/10 to-[#2d6a2d]/10 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            <div className="relative z-10 flex flex-col items-center justify-center">
                                <div className="w-16 h-16 bg-gradient-to-br from-[#2d6a2d] to-[#348738] hover:from-[#348738] hover:to-[#2d6a2d] rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                    <i className="fa-solid fa-trophy text-white text-2xl"></i>
                                </div>
                                <h2 className="text-xl font-bold text-gray-800 mb-3">Sân chất lượng</h2>
                                <p className="text-gray-600 text-sm leading-relaxed">
                                    Đối tác sân uy tín, cơ sở vật chất hiện đại
                                </p>
                            </div>
                        </div>

                        <div className="group relative bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg hover:shadow-2xl flex flex-col items-center justify-center text-center transition-all duration-500 hover:-translate-y-2 border border-white/20 min-h-[280px] p-8">
                            <div className="absolute inset-0 bg-gradient-to-br from-[#348738]/10 to-[#2d6a2d]/10 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            <div className="relative z-10 flex flex-col items-center justify-center">
                                <div className="w-16 h-16 bg-gradient-to-br from-[#2d6a2d] to-[#348738] hover:from-[#348738] hover:to-[#2d6a2d] rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                    <i className="fas fa-bullseye text-white text-2xl"></i>
                                </div>
                                <h2 className="text-xl font-bold text-gray-800 mb-3">Đa dạng lựa chọn</h2>
                                <p className="text-gray-600 text-sm leading-relaxed">
                                    Bóng đá, cầu lông, tennis, bóng rổ và nhiều hơn nữa
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </section>


            <section className=""></section>
            <section className=""></section>
        </>
    )
}
export default Content