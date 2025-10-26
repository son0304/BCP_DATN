import { useForm } from 'react-hook-form';
import Input from '../Components/Input';
import logo from "/logo.png";
import { usePostData } from '../Hooks/useApi';
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useNotification } from '../Components/Notification';

type FormData = {
    email: string;
    password: string;
};

const Login = () => {
    const [serverErrors, setServerErrors] = useState<{ [key: string]: string }>({});
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
        mutate(data, {
            onSuccess: (response) => {
                const { success, data: resData, message } = response;
                if (success) {
                    if (resData?.access_token) {
                        localStorage.setItem("token", resData.access_token);
                        localStorage.setItem("user", JSON.stringify(resData.user));
                    }
                    showNotification(message, 'success')

                    navigate('/');
                } else {
                    showNotification(message, 'error')

                }
            },
            onError: (error: any) => {
                if (error.response?.data?.errors) {
                    setServerErrors(error.response.data.errors);
                } else {
                    console.error(" Lỗi đăng nhập:", error.message);
                }
            },
        });
    };

    return (
        <div className='min-h-screen flex justify-center items-center bg-gray-50 p-4 sm:p-6 lg:p-8'>
            <div className='container max-w-4xl grid md:grid-cols-2 bg-white rounded-2xl shadow-2xl overflow-hidden'>

                {/* === FORM LOGIN === */}
                <div className='w-full p-8 sm:p-12'>
                    <div className='mb-10'>
                        <h1 className='text-3xl font-bold text-gray-900'>Chào mừng trở lại!</h1>
                        <p className='text-gray-500 mt-2'>Đăng nhập để tiếp tục hành trình của bạn.</p>
                    </div>

                    <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
                        <Input label="Email" id="email" type="email" placeholder="ví_dụ@gmail.com" error={errors.email?.message || serverErrors.email}
                            {...register('email', { required: 'Email không được để trống', pattern: { value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, message: 'Email không hợp lệ', }, })}
                        />

                        <Input label="Mật khẩu" id="password" type="password" placeholder="Nhập mật khẩu" error={errors.password?.message || serverErrors.password}
                            {...register('password', { required: 'Mật khẩu không được để trống', minLength: { value: 6, message: 'Mật khẩu phải có ít nhất 6 ký tự', }, })}
                        />

                        <button type="submit" className="w-full py-3 bg-orange-500 text-white rounded-lg font-semibold  shadow-sm hover:bg-orange-600  focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-  transition-all duration-300 ease-in-out">
                            Đăng nhập
                        </button>

                        <div className='text-sm text-center'>
                            <a href="#" className='font-medium text-orange-500 hover:text-orange-600'>
                                Quên mật khẩu?
                            </a>
                        </div>

                    </form>
                </div>

                {/* === CỘT HÌNH ẢNH === */}
                <div className='hidden md:flex w-full flex-col justify-center items-center p-12 lg:p-16'
                    style={{ background: 'linear-gradient(to bottom right, #348738, #2b6e2d)' }}
                >
                    <div className='bg-white p-6 rounded-full shadow-lg mb-6'>
                        <img src={logo} alt="Logo" className='w-full max-w-[160px] h-auto' />
                    </div>

                    <div className='text-center'>
                        <h2 className='text-3xl font-bold text-white leading-tight'> Giải pháp của Tương lai</h2>
                        <p className='text-lg text-green-100 mt-3 max-w-xs'>Nền tảng quản lý thông minh, trực quan và hiệu quả nhất.</p>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Login;
