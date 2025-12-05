import { useForm } from 'react-hook-form';
import Input from '../Components/Input';
import logo from "/logo.png";
import { usePostData } from '../Hooks/useApi';
import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useNotification } from '../Components/Notification';

type FormData = {
    email: string;
    password: string;
};

const Login = () => {
    const [serverErrors, setServerErrors] = useState<{ [key: string]: string }>({});
    const [isLoading, setIsLoading] = useState(false);

    const navigate = useNavigate();
    const { showNotification } = useNotification();
    const { mutate } = usePostData<{ access_token: string; user: any }, FormData>('login');

    const {
        register,
        handleSubmit,
        formState: { errors },
    } = useForm<FormData>();

    const onSubmit = (data: FormData) => {
        setServerErrors({});
        setIsLoading(true);
        mutate(data, {
            onSuccess: (response) => {
                const { success, data: resData, message } = response;
                if (success) {
                    if (resData?.access_token) {
                        localStorage.setItem("token", resData.access_token);
                        localStorage.setItem("user", JSON.stringify(resData.user));
                    }
                    showNotification(message, 'success');
                    navigate('/');
                } else {
                    showNotification(message, 'error');
                }
            },
            onError: (error: any) => {
                if (error.response?.data?.errors) {
                    setServerErrors(error.response.data.errors);
                } else {
                    console.error("Lỗi đăng nhập:", error.message);
                    showNotification("Tài khoản hoặc mật khẩu không chính xác.", "error");
                }
            },
            onSettled: () => setIsLoading(false),
        });
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-50 font-sans p-4 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]">
            <div className="w-full max-w-[440px] bg-white rounded-3xl shadow-xl border border-gray-100 p-8 md:p-10 animate-fade-in-up">

                {/* --- HEADER --- */}
                <div className="text-center mb-8">
                    <Link to="/" className="inline-block hover:scale-105 transition-transform duration-300">
                        <img src={logo} alt="Logo" className="h-12 w-auto mx-auto mb-4 object-contain" />
                    </Link>
                    <h1 className="text-2xl font-extrabold text-[#11182C] tracking-tight">Chào mừng trở lại!</h1>
                    <p className="text-sm text-gray-500 mt-2">
                        Vui lòng đăng nhập để quản lý sân và đặt lịch.
                    </p>
                </div>

                {/* --- FORM --- */}
                <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">

                    {/* Email */}
                    <div className="space-y-1">
                        <Input
                            label="Email"
                            id="email"
                            type="email"
                            placeholder="name@example.com"
                            error={errors.email?.message || serverErrors.email}
                            {...register('email', {
                                required: 'Vui lòng nhập email',
                                pattern: { value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, message: 'Email không hợp lệ' },
                            })}
                        />
                    </div>

                    {/* Password */}
                    <div className="space-y-1 relative"> {/* Thêm relative vào thẻ cha */}

                        <Input
                            label="Mật khẩu" // 1. Truyền label chuẩn vào component
                            id="password"
                            type="password"
                            placeholder="••••••••"
                            error={errors.password?.message || serverErrors.password}
                            {...register('password', {
                                required: 'Vui lòng nhập mật khẩu',
                                minLength: { value: 6, message: 'Tối thiểu 6 ký tự' },
                            })}
                        />

                        {/* 2. Đặt Link "Quên mật khẩu" nổi lên góc phải trên cùng */}
                        <div className="absolute top-0 right-0">
                            <Link
                                to="/forgot-password"
                                className="text-xs font-semibold text-[#10B981] hover:text-[#059669] hover:underline"
                            >
                                Quên mật khẩu?
                            </Link>
                        </div>
                    </div>

                    {/* Submit Button */}
                    <button
                        type="submit"
                        disabled={isLoading}
                        className={`w-full py-3 rounded-xl font-bold text-white text-sm shadow-lg shadow-emerald-200/50 transition-all duration-300 transform active:scale-95 ${isLoading
                            ? "bg-gray-400 cursor-not-allowed"
                            : "bg-[#10B981] hover:bg-[#059669] hover:-translate-y-0.5"
                            }`}
                    >
                        {isLoading ? (
                            <div className="flex items-center justify-center gap-2">
                                <i className="fa-solid fa-circle-notch fa-spin"></i>
                                <span>Đang xử lý...</span>
                            </div>
                        ) : "Đăng Nhập"}
                    </button>
                </form>

                {/* --- FOOTER --- */}
                <div className="mt-8 pt-6 border-t border-gray-100 text-center">
                    <p className="text-sm text-gray-600">
                        Chưa có tài khoản?{' '}
                        <Link to="/register" className="font-bold text-[#10B981] hover:text-[#059669] hover:underline transition-all">
                            Đăng ký ngay
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    );
};

export default Login;