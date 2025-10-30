import { useForm, useWatch } from 'react-hook-form';
import { Link, useNavigate } from 'react-router-dom';
import { useState } from 'react';
import { useFetchData, usePostData } from '../Hooks/useApi';
import Input from '../Components/Input';

type FormData = {
  name: string;
  email: string;
  phone: string;
  province_id: string;
  district_id: string;
  password: string;
  password_confirmation: string; // Laravel confirmed rule
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

  console.log(selectedProvinceId, filteredDistricts);
  
  const { mutate, isPending } = usePostData('register');

  const onSubmit = (data: FormData) => {
    setServerError(null);
    console.log(data);
    

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
    <div className="min-h-screen flex justify-center items-center bg-gray-50 p-4 sm:p-6 lg:p-8">
      <div className="container max-w-5xl grid md:grid-cols-2 bg-white rounded-2xl shadow-2xl overflow-hidden">
        {/* Form */}
        <div className="w-full p-8 sm:p-12 overflow-y-auto" style={{ maxHeight: '90vh' }}>
          <div className="mb-8">
            <h1 className="text-3xl font-bold text-gray-900">Tạo tài khoản</h1>
            <p className="text-gray-500 mt-2">Chỉ mất vài giây để bắt đầu.</p>
          </div>

          {serverError && (
            <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
              {serverError}
            </div>
          )}

          <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <Input
                label="Họ và tên"
                id="name"
                type="text"
                placeholder="Nguyễn Văn A"
                error={errors.name?.message}
                {...register('name', { required: 'Họ tên không được để trống' })}
              />
              <Input
                label="Số điện thoại"
                id="phone"
                type="tel"
                placeholder="09xxxxxxx"
                error={errors.phone?.message}
                {...register('phone', {
                  required: 'Số điện thoại không được để trống',
                  pattern: {
                    value: /^(0[3|5|7|8|9])+([0-9]{8})\b$/,
                    message: 'Số điện thoại không hợp lệ',
                  },
                })}
              />
            </div>

            <Input
              label="Email"
              id="email"
              type="email"
              placeholder="ví_dụ@gmail.com"
              error={errors.email?.message}
              {...register('email', {
                required: 'Email không được để trống',
                pattern: {
                  value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                  message: 'Email không hợp lệ',
                },
              })}
            />

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Tỉnh/Thành phố
                </label>
                <select
                  {...register('province_id', { required: 'Vui lòng chọn Tỉnh/Thành' })}
                  className={`w-full px-4 py-3 border rounded-lg bg-gray-50 focus:outline-none focus:ring-2 appearance-none
                    ${errors.province_id ? 'border-red-500 focus:ring-red-400' : 'border-gray-300 focus:ring-[#348738]'}
                  `}
                >
                  <option value="">Chọn Tỉnh/Thành phố</option>
                  {provinceList.map((p) => (
                    <option key={p.id} value={p.id}>
                      {p.name}
                    </option>
                  ))}
                </select>
                {errors.province_id && (
                  <p className="mt-1 text-xs text-red-600">{errors.province_id.message}</p>
                )}
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Quận/Huyện</label>
                <select
                  {...register('district_id', { required: 'Vui lòng chọn Quận/Huyện' })}
                  disabled={!selectedProvinceId || filteredDistricts.length === 0}
                  className={`w-full px-4 py-3 border rounded-lg bg-gray-50 focus:outline-none focus:ring-2 appearance-none
                    ${errors.district_id ? 'border-red-500 focus:ring-red-400' : 'border-gray-300 focus:ring-[#348738]'}
                    ${!selectedProvinceId ? 'cursor-not-allowed bg-gray-200' : ''}
                  `}
                >
                  <option value="">Chọn Quận/Huyện</option>
                  {filteredDistricts.map((d) => (
                    <option key={d.id} value={d.id}>
                      {d.name}
                    </option>
                  ))}
                </select>
                {errors.district_id && (
                  <p className="mt-1 text-xs text-red-600">{errors.district_id.message}</p>
                )}
              </div>
            </div>

            <Input
              label="Mật khẩu"
              id="password"
              type="password"
              placeholder="Tạo mật khẩu"
              error={errors.password?.message}
              {...register('password', {
                required: 'Mật khẩu không được để trống',
                minLength: { value: 8, message: 'Mật khẩu phải có ít nhất 8 ký tự' },
              })}
            />

            <Input
              label="Xác nhận mật khẩu"
              id="password_confirmation"
              type="password"
              placeholder="Nhập lại mật khẩu"
              error={errors.password_confirmation?.message}
              {...register('password_confirmation', {
                required: 'Vui lòng xác nhận mật khẩu',
                validate: (value) =>
                  value === password || 'Mật khẩu xác nhận không khớp',
              })}
            />

            <button
              type="submit"
              disabled={isPending}
              className={`w-full py-3 rounded-lg font-semibold shadow-sm transition-all duration-300 ease-in-out
                ${isPending
                  ? 'bg-gray-400 cursor-not-allowed'
                  : 'bg-orange-500 hover:bg-orange-600 text-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2'
                }`}
            >
              {isPending ? 'Đang xử lý...' : 'Đăng ký'}
            </button>

            <div className="text-sm text-center text-gray-600">
              Đã có tài khoản?{' '}
              <Link to="/login" className="font-medium text-orange-500 hover:text-orange-600">
                Đăng nhập ngay
              </Link>
            </div>
          </form>
        </div>

        {/* Hình ảnh */}
        <div
          className="hidden md:flex w-full flex-col justify-center items-center p-12 lg:p-16"
          style={{ background: 'linear-gradient(to bottom right, #348738, #2b6e2d)' }}
        >
          <div className="bg-white p-6 rounded-full shadow-lg mb-6">
            <img src="/logo.png" alt="Logo" className="w-full max-w-[160px] h-auto" />
          </div>
          <div className="text-center">
            <h2 className="text-3xl font-bold text-white leading-tight">
              Giải pháp của Tương lai
            </h2>
            <p className="text-lg text-green-100 mt-3 max-w-xs">
              Nền tảng quản lý thông minh, trực quan và hiệu quả nhất.
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Register;
