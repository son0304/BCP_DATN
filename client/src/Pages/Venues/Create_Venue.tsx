import React, { useMemo, useState } from 'react';
import { useForm, useFieldArray } from 'react-hook-form';
import type { Control, UseFormRegister, FieldErrors } from 'react-hook-form';
import Input from '../../Components/Input';
import Select from '../../Components/Select';
import { useFetchData, usePostData } from '../../Hooks/useApi';
import { message } from 'antd';
import { useNavigate } from 'react-router-dom';

// --- Interface Cập nhật ---

interface District {
  id: number;
  name: string;
  code: string;
}
interface Province {
  id: number;
  name: string;
  code: string;
  districts: District[];
}
// Giả định interface cho VenueType
interface VenueType {
  id: number;
  name: string;
}

interface TimeSlot {
  start_time: string;
  end_time: string;
  price: string; // Sử dụng string cho input, sẽ convert sang number khi submit
}
interface Court {
  name: string;
  venue_type_id: string;
  surface: string;
  is_indoor: boolean;
  time_slots: TimeSlot[];
}

interface FormData {
  name: string;
  phone: string;
  provinceId: string; // Sẽ đổi tên thành province_id khi submit
  districtId: string; // Sẽ đổi tên thành district_id khi submit
  address: string;    // Sẽ đổi tên thành address_detail khi submit
  start_time: string;
  end_time: string;
  courts: Court[]; // Mảng các sân
}

// --- Component Con cho Khung Giờ ---

interface CourtTimeSlotsProps {
  courtIndex: number;
  control: Control<FormData>;
  register: UseFormRegister<FormData>;
  errors: FieldErrors<FormData>;
}

const CourtTimeSlots: React.FC<CourtTimeSlotsProps> = ({ courtIndex, control, register, errors }) => {
  const { fields, append, remove } = useFieldArray({
    control,
    name: `courts.${courtIndex}.time_slots`
  });

  return (
    <div className="pl-6 mt-4 border-l-2 border-green-600">
      <h4 className="font-semibold text-md text-gray-700 mb-2">🏷️ Khung giờ & Giá cho sân này</h4>
      {fields.map((field, slotIndex) => (
        <div key={field.id} className="grid grid-cols-4 gap-2 mb-2 p-2 border border-gray-200 rounded-md items-start">
          <Input
            label="Giờ bắt đầu"
            type="time"
            {...register(`courts.${courtIndex}.time_slots.${slotIndex}.start_time`, { required: 'Bắt buộc' })}
            error={errors.courts?.[courtIndex]?.time_slots?.[slotIndex]?.start_time?.message}
          />
          <Input
            label="Giờ kết thúc"
            type="time"
            {...register(`courts.${courtIndex}.time_slots.${slotIndex}.end_time`, { required: 'Bắt buộc' })}
            error={errors.courts?.[courtIndex]?.time_slots?.[slotIndex]?.end_time?.message}
          />
          <Input
            label="Giá (VNĐ)"
            type="number"
            placeholder="VD: 150000"
            {...register(`courts.${courtIndex}.time_slots.${slotIndex}.price`, { required: 'Bắt buộc', min: { value: 0, message: 'Giá phải >= 0' } })}
            error={errors.courts?.[courtIndex]?.time_slots?.[slotIndex]?.price?.message}
          />
          <button
            type="button"
            onClick={() => remove(slotIndex)}
            className="h-10 mt-6 bg-red-500 hover:bg-red-600 text-white rounded-md self-start font-medium text-sm"
          >
            Xóa giờ
          </button>
        </div>
      ))}
      <button
        type="button"
        onClick={() => append({ start_time: '', end_time: '', price: '0' })}
        className="mt-2 bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded-md text-sm"
      >
        + Thêm khung giờ
      </button>
    </div>
  );
};


// --- Component Chính ---

const Create_Venue = () => {
  // Lấy dữ liệu tỉnh/huyện
  const { data: proData } = useFetchData('provinces');
  const provinces: Province[] = (proData?.data as Province[]) || [];

  const [selectedVenueTypes, setSelectedVenueTypes] = useState<string[]>([]);

  // Lấy dữ liệu loại sân (mới)
  const { data: venueTypesData } = useFetchData('venue_types');
  const venueTypes: VenueType[] = (venueTypesData?.data as VenueType[]) || [];

  const { mutate } = usePostData('venues');

  const navigate = useNavigate();

  const { register, handleSubmit, watch, setValue, control, formState: { errors }, } = useForm<FormData>({
    defaultValues: {
      provinceId: '',
      districtId: '',
      courts: [] // Khởi tạo mảng sân
    },
  });

  // Hook cho mảng sân
  const { fields: courtFields, append: appendCourt, remove: removeCourt } = useFieldArray({
    control,
    name: 'courts'
  });

  const selectedProvinceId = watch('provinceId');
  useMemo(() => setValue('districtId', ''), [selectedProvinceId, setValue]);
  const provincesById = provinces.find(p => p.id.toString() === selectedProvinceId);

  const onSubmit = async (data: FormData) => {
    // 1. Kiểm tra User
    const userStr = localStorage.getItem("user");
    const user = userStr ? JSON.parse(userStr) : null;

    if (!user) {
      alert("Không tìm thấy thông tin người dùng. Vui lòng đăng nhập.");
      return;
    }

    // 2. Kiểm tra sân
    if (!data.courts || data.courts.length === 0) {
      alert('Vui lòng thêm ít nhất một sân (court)!');
      return;
    }

    // 3. Kiểm tra khung giờ
    for (const court of data.courts) {
      if (!court.time_slots || court.time_slots.length === 0) {
        alert(`Vui lòng thêm ít nhất một khung giờ (time slot) cho sân "${court.name}"!`);
        return;
      }
    }

    // 4. Tạo payload gửi đi (dạng JSON, không dùng FormData)
    const payload = {
      owner_id: user.id.toString(), // Đổi tên
      name: data.name,
      phone: data.phone,
      province_id: data.provinceId, // Đổi tên
      district_id: data.districtId, // Đổi tên
      address_detail: data.address, // Đổi tên
      start_time: data.start_time,
      end_time: data.end_time,
      courts: data.courts.map(court => ({
        ...court,
        is_indoor: court.is_indoor === true,
        surface: court.surface || null,      // Gửi null nếu rỗng
        time_slots: court.time_slots.map(slot => ({
          ...slot,
          price: parseFloat(slot.price) || 0 // Convert giá sang số
        }))
      }))
    };

    try {
      await mutate(payload); // Gửi dữ liệu
      message.success('🎉 Đăng ký sân thành công!'); // chỉ hiện khi API trả 201
      navigate('/congratulations');
    } catch (err: any) {
      console.error(err);

      const status = err?.response?.status;
      const errData = err?.response?.data;

      if (status === 409 && errData?.alreadyRegistered) {
        // User đã đăng ký → điều hướng, không show success
        navigate('/congratulations', { state: { alreadyRegistered: true } });
      } else {
        const errMsg = errData?.message || '❌ Đăng ký thất bại. Vui lòng thử lại!';
        message.error(errMsg);
      }
    }


  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 p-4">
      <div className="container max-w-[800px] mx-auto rounded-2xl shadow-lg p-10 border-t-6 border-orange-500 bg-white">
        <h1 className="text-3xl font-extrabold text-[#348738] mb-8 text-center">Đăng kí sân</h1>

        <form onSubmit={handleSubmit(onSubmit)}>
          {/* --- Thông tin cơ bản --- */}
          <h2 className="text-xl font-bold text-gray-800 mb-3">📌 Thông tin cơ bản</h2>
          <div className="grid grid-cols-2 gap-5 py-5 border-b border-gray-200">
            <Input label="Tên thương hiệu (*)" id="name" type="text" placeholder="Nhập tên thương hiệu" {...register('name', { required: 'Tên thương hiệu là bắt buộc' })} error={errors.name?.message} />
            <Input label="Số điện thoại (*)" id="phone" type="tel" placeholder="Nhập số điện thoại" {...register('phone', { required: 'Số điện thoại là bắt buộc' })} error={errors.phone?.message} />
          </div>

          {/* --- Vị trí & Thời gian --- */}
          <h2 className="text-xl font-bold text-gray-800 mt-6 mb-3">📍 Vị trí & Thời gian hoạt động</h2>
          <div className="border-b py-5 border-gray-200">
            <div className="grid grid-cols-2 gap-5">
              <Select
                id="provinceId"
                label="Chọn tỉnh/thành (*)"
                {...register('provinceId', { required: 'Vui lòng chọn tỉnh/thành' })}
                options={provinces.map(p => ({ value: p.id.toString(), label: p.name }))}
                error={errors.provinceId?.message}
              />
              <Select
                id="districtId"
                label="Chọn quận/huyện (*)"
                {...register('districtId', { required: 'Vui lòng chọn quận/huyện' })}
                options={provincesById?.districts.map(d => ({ value: d.id.toString(), label: d.name })) || []}
                disabled={!selectedProvinceId}
                error={errors.districtId?.message}
              />
              {/* Sửa lại grid-cols-1 ở đây */}
              <div className="col-span-2">
                <Input label="Địa chỉ chi tiết (*)" id="address" type="text" placeholder="Nhập địa chỉ chi tiết (Số nhà, tên đường, phường/xã)" {...register('address', { required: 'Địa chỉ chi tiết là bắt buộc' })} error={errors.address?.message} />
              </div>
              {/* --- Chọn loại hình sân --- */}
              <div className="col-span-2">
                <label className="font-semibold text-gray-700 block mb-2">⚙️ Chọn loại hình sân (*)</label>
                <div className="flex flex-wrap gap-3">
                  {venueTypes.map((vt) => (
                    <label key={vt.id} className="flex items-center space-x-2 border rounded-lg px-3 py-2 bg-gray-50 hover:bg-gray-100 cursor-pointer">
                      <input
                        type="checkbox"
                        value={vt.id.toString()}
                        checked={selectedVenueTypes.includes(vt.id.toString())}
                        onChange={(e) => {
                          const { value, checked } = e.target;
                          setSelectedVenueTypes((prev) =>
                            checked ? [...prev, value] : prev.filter((v) => v !== value)
                          );
                        }}
                      />
                      <span className="text-gray-800">{vt.name}</span>
                    </label>
                  ))}
                </div>
              </div>

              <Input label="Giờ mở cửa (*)" id="start_time" type="time" {...register('start_time', { required: 'Giờ mở cửa là bắt buộc' })} error={errors.start_time?.message} />
              <Input label="Giờ đóng cửa (*)" id="end_time" type="time" {...register('end_time', { required: 'Giờ đóng cửa là bắt buộc' })} error={errors.end_time?.message} />
            </div>
          </div>

          {/* --- Quản lý Sân (Courts) --- */}
          <h2 className="text-xl font-bold text-gray-800 mt-6 mb-3">⚽ Quản lý Sân</h2>
          <div className="border-b py-5 border-gray-200 space-y-6">
            {courtFields.map((field, index) => (
              <div key={field.id} className="p-4 border rounded-lg bg-gray-50 relative">
                <button
                  type="button"
                  onClick={() => removeCourt(index)}
                  className="absolute top-2 right-2 text-red-500 hover:text-red-700 font-bold text-xl"
                  title="Xóa sân này"
                >
                  &times;
                </button>
                <h3 className="text-lg font-semibold text-gray-700 mb-3">Sân {index + 1}</h3>
                <div className="grid grid-cols-2 gap-4">
                  <Input
                    label="Tên sân (*)"
                    placeholder="VD: Sân số 1"
                    {...register(`courts.${index}.name`, { required: 'Tên sân là bắt buộc' })}
                    error={errors.courts?.[index]?.name?.message}
                  />
                  <Select
                    label="Loại hình sân (*)"
                    {...register(`courts.${index}.venue_type_id`, { required: 'Loại sân là bắt buộc' })}
                    options={venueTypes
                      .filter(vt => selectedVenueTypes.includes(vt.id.toString()))
                      .map(vt => ({
                        value: vt.id.toString(),
                        label: vt.name
                      }))
                    }
                    disabled={selectedVenueTypes.length === 0}
                    error={errors.courts?.[index]?.venue_type_id?.message}
                  />

                  <Input
                    label="Loại mặt sân (*)"
                    placeholder="VD: Cỏ nhân tạo"
                    {...register(`courts.${index}.surface`)}
                    error={errors.courts?.[index]?.surface?.message}
                  />
                  <div className="mt-4">
                    <Select
                      label="Loại sân"
                      {...register(`courts.${index}.is_indoor`, {
                        required: 'Vui lòng chọn loại sân'
                      })}
                      options={[
                        { value: '1', label: 'Trong nhà' },
                        { value: '0', label: 'Ngoài trời' }
                      ]}
                      error={errors.courts?.[index]?.is_indoor?.message}
                    />
                  </div>

                </div>

                {/* --- Quản lý Khung Giờ (TimeSlots) --- */}
                <CourtTimeSlots
                  courtIndex={index}
                  control={control}
                  register={register}
                  errors={errors}
                />
              </div>
            ))}

            <button
              type="button"
              onClick={() =>
                appendCourt({
                  name: "",
                  venue_type_id: "",
                  surface: "",
                  is_indoor: "0",
                  time_slots: [],
                })
              }
              className="
    px-4 py-2 
    bg-blue-600 
    hover:bg-blue-700 
    text-white 
    text-sm 
    font-medium 
    rounded-lg 
    transition 
    duration-200 
    shadow 
    hover:shadow-md
    flex items-center gap-1
  "
            >
              <span className="text-lg">＋</span> Thêm Sân Mới
            </button>

          </div>

          {/* --- Nút Submit --- */}
          <button type="submit" className="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 rounded-lg transition-all shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2 mt-8">
            Gửi Đăng Kí
          </button>
        </form>
      </div>
    </div>
  );
};

export default Create_Venue;