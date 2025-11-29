import { useForm, useWatch } from 'react-hook-form';
import { Link, useNavigate } from 'react-router-dom';
import { useState } from 'react';
import { useFetchData, usePostData } from '../Hooks/useApi';
import Input from '../Components/Input';
import logo from "/logo.png";

type FormData = {
  name: string;
  email: string;
  phone: string;
  province_id: string;
  district_id: string;
  password: string;
  password_confirmation: string;
};

interface Province {
  id: number;
  name: string;
}

interface District {
  id: number;
  name: string;
  province_id: number;
}

const Register = () => {
  const navigate = useNavigate();
  const [serverError, setServerError] = useState<string | null>(null);

  const { data: provincesResponse } = useFetchData<Province[]>('provinces');
  const { data: districtsResponse } = useFetchData<District[]>('districts');

  const provinceList = provincesResponse?.data || [];
  const districtList = districtsResponse?.data || [];

  const {
    register,
    handleSubmit,
    formState: { errors },
    control,
    setError,
  } = useForm<FormData>();

  const password = useWatch({ control, name: 'password' });
  const selectedProvinceId = useWatch({ control, name: 'province_id' });

  const filteredDistricts = districtList.filter(
    (d) => d.province_id.toString() === selectedProvinceId
  );

  const { mutate, isPending } = usePostData('register');

  const onSubmit = (data: FormData) => {
    setServerError(null);
    mutate(data, {
      onSuccess: () => navigate('/login?register=success'),
      onError: (error: any) => {
        if (error.response && error.response.status === 422) {
          const apiErrors = error.response.data.errors;
          if (apiErrors) {
            Object.keys(apiErrors).forEach((key) => {
              setError(key as keyof FormData, {
                type: 'server',
                message: apiErrors[key][0],
              });
            });
          }
        } else {
          setServerError('Đã có lỗi xảy ra. Vui lòng thử lại sau.');
        }
      },
    });
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50/50 p-4 font-sans bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]">
      <div className="w-full max-w-lg bg-white rounded-2xl shadow-xl border border-gray-100 p-6 md:p-10 animate-fade-in-up">
        
        {/* --- HEADER --- */}
        <div className="text-center mb-6">
            <Link to="/" className='inline-block mb-3 p-3 bg-green-50 rounded-full'>
                <img src={logo} alt="Logo" className='w-10 h-auto object-contain' />
            </Link>
            <h1 className="text-2xl font-bold text-gray-900">Tạo tài khoản</h1>
            <p className="text-sm text-gray-500 mt-1">Tham gia cộng đồng Court Prime ngay hôm nay.</p>
        </div>

        {serverError && (
            <div className="bg-red-50 border border-red-100 text-red-600 px-3 py-2 rounded-lg mb-5 text-sm flex items-center gap-2">
               <i className="fa-solid fa-circle-exclamation"></i> {serverError}
            </div>
        )}

        {/* --- FORM --- */}
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
            
            {/* Tên & SĐT cùng 1 hàng */}
            <div className="grid grid-cols-2 gap-4">
                <Input
                  label="Họ tên"
                  id="name"
                  type="text"
                  placeholder="Nguyễn Văn A"
                  error={errors.name?.message}
                  {...register('name', { required: 'Bắt buộc' })}
                />
                <Input
                  label="Số điện thoại"
                  id="phone"
                  type="tel"
                  placeholder="0912..."
                  error={errors.phone?.message}
                  {...register('phone', {
                    required: 'Bắt buộc',
                    pattern: { value: /^(0[3|5|7|8|9])+([0-9]{8})\b$/, message: 'SĐT sai' },
                  })}
                />
            </div>

            <Input
                label="Email"
                id="email"
                type="email"
                placeholder="name@example.com"
                error={errors.email?.message}
                {...register('email', {
                  required: 'Bắt buộc',
                  pattern: { value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, message: 'Email sai' },
                })}
            />

            {/* Tỉnh & Huyện cùng 1 hàng */}
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Tỉnh/Thành</label>
                <div className="relative">
                  <select
                    {...register('province_id', { required: 'Chọn tỉnh' })}
                    className={`w-full px-3 py-2 bg-white border rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 appearance-none
                      ${errors.province_id ? 'border-red-300' : 'border-gray-200'}
                    `}
                  >
                    <option value="">-- Tỉnh --</option>
                    {provinceList.map((p) => (
                      <option key={p.id} value={p.id}>{p.name}</option>
                    ))}
                  </select>
                  <i className="fa-solid fa-chevron-down absolute right-3 top-3 text-xs text-gray-400 pointer-events-none"></i>
                </div>
                {errors.province_id && <p className="mt-1 text-xs text-red-500">{errors.province_id.message}</p>}
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Quận/Huyện</label>
                <div className="relative">
                  <select
                    {...register('district_id', { required: 'Chọn huyện' })}
                    disabled={!selectedProvinceId}
                    className={`w-full px-3 py-2 bg-white border rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 appearance-none
                      ${errors.district_id ? 'border-red-300' : 'border-gray-200'}
                      ${!selectedProvinceId ? 'bg-gray-50 cursor-not-allowed' : ''}
                    `}
                  >
                    <option value="">-- Huyện --</option>
                    {filteredDistricts.map((d) => (
                      <option key={d.id} value={d.id}>{d.name}</option>
                    ))}
                  </select>
                  <i className="fa-solid fa-chevron-down absolute right-3 top-3 text-xs text-gray-400 pointer-events-none"></i>
                </div>
                {errors.district_id && <p className="mt-1 text-xs text-red-500">{errors.district_id.message}</p>}
              </div>
            </div>

            {/* Mật khẩu cùng 1 hàng */}
            <div className="grid grid-cols-2 gap-4">
                <Input
                    label="Mật khẩu"
                    id="password"
                    type="password"
                    placeholder="******"
                    error={errors.password?.message}
                    {...register('password', {
                      required: 'Bắt buộc',
                      minLength: { value: 8, message: 'Min 8 ký tự' },
                    })}
                />
                <Input
                    label="Nhập lại MK"
                    id="password_confirmation"
                    type="password"
                    placeholder="******"
                    error={errors.password_confirmation?.message}
                    {...register('password_confirmation', {
                      required: 'Bắt buộc',
                      validate: (value) => value === password || 'Không khớp',
                    })}
                />
            </div>

            <button
                type="submit"
                disabled={isPending}
                className={`w-full py-3 mt-2 rounded-xl font-bold text-white text-sm shadow-lg shadow-emerald-200/50 transition-all transform active:scale-95 ${
                    isPending
                    ? "bg-gray-400 cursor-not-allowed"
                    : "bg-[#10B981] hover:bg-[#059669] hover:-translate-y-0.5"
                }`}
            >
                {isPending ? <i className="fa-solid fa-circle-notch fa-spin"></i> : "Đăng Ký Tài Khoản"}
            </button>
        </form>

        <div className="mt-6 pt-4 border-t border-gray-100 text-center">
            <p className='text-sm text-gray-600'>
                Đã có tài khoản?{' '}
                <Link to="/login" className='font-bold text-[#10B981] hover:text-[#059669] hover:underline transition-all'>
                    Đăng nhập
                </Link>
            </p>
        </div>
      </div>
    </div>
  );
};

export default Register;