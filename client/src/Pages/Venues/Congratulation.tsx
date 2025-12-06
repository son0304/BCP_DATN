import { CheckCircle } from "lucide-react";
import { Link, useLocation } from "react-router-dom";

const Congratulation = () => {
    
    const location = useLocation();
    const alreadyRegistered = location.state?.alreadyRegistered === true;

    return (
        <div className="min-h-[70vh] flex items-center justify-center px-5">
            <div className="bg-white shadow-xl rounded-2xl p-10 max-w-md text-center animate-fadeIn">

                <CheckCircle className="mx-auto text-green-500" size={80} />

                <h1 className="text-3xl font-bold mt-6 text-gray-800">
                    {alreadyRegistered
                        ? "Bạn đã đăng ký sân trước đó!"
                        : "Đăng ký sân thành công!"}
                </h1>

                <p className="text-gray-600 mt-3 leading-relaxed">
                    {alreadyRegistered
                        ? "Hệ thống ghi nhận rằng bạn đã có sân đăng ký. Mỗi tài khoản chỉ được đăng ký một lần."
                        : "Chúc mừng bạn! Sân của bạn đã được gửi lên hệ thống và đang chờ duyệt."}
                </p>

                <div className="mt-8 flex flex-col gap-3">
                    <Link to="/" className="w-full bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-xl font-semibold transition-all">
                        Quay lại trang chủ
                    </Link>
                </div>

            </div>
        </div>
    );
};

export default Congratulation;
