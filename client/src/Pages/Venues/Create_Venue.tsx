import React, { useMemo, useState } from 'react';
import { useForm } from 'react-hook-form';
import Input from '../../Components/Input';
import Select from '../../Components/Select';
import Textarea from '../../Components/Textarea';
import { useFetchData, usePostData } from '../../Hooks/useApi';

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

interface FormData {
  name: string;
  phone: string;
  provinceId: string;
  districtId: string;
  address: string;
  start_time: string;
  end_time: string;
  description: string;
  images?: FileList;
}

interface ImagePreview {
  file: File;
  url: string;
  is_primary: 0 | 1; // 1 = ảnh chính
}

const Create_Venue = () => {
  const { data: proData } = useFetchData('provinces');
  const provinces: Province[] = (proData?.data as Province[]) || [];
  const { mutate } = usePostData('venues')
  const { register, handleSubmit, watch, setValue, formState: { errors }, } = useForm<FormData>({
    defaultValues: { provinceId: '', districtId: '' },
  });

  const selectedProvinceId = watch('provinceId');
  const [images, setImages] = useState<ImagePreview[]>([]);

  useMemo(() => setValue('districtId', ''), [selectedProvinceId, setValue]);
  const provincesById = provinces.find(p => p.id.toString() === selectedProvinceId);

  const handleImagesChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files;
    if (!files) return;

    const newImages: ImagePreview[] = Array.from(files).map((file, idx) => ({
      file,
      url: URL.createObjectURL(file),
      is_primary: idx === 0 ? 1 : 0, // Ảnh đầu tiên là chính
    }));
    setImages(newImages);
  };

  const setMainImage = (index: number) => {
    setImages(prev =>
      prev.map((img, idx) => ({
        ...img,
        is_primary: idx === index ? 1 : 0,
      }))
    );
  };



  const onSubmit = (data: FormData) => {
    if (images.length === 0) {
      alert('Vui lòng upload ít nhất 1 ảnh!');
      return;
    }

    const formData = new FormData();

    const userStr = localStorage.getItem("user");
    const user = userStr ? JSON.parse(userStr) : null;
    if (!user) {
      alert("Không tìm thấy thông tin người dùng. Vui lòng đăng nhập.");
      return;
    }
    formData.append('user_id', user.id.toString());

    formData.append('name', data.name);
    formData.append('phone', data.phone);
    formData.append('provinceId', data.provinceId);
    formData.append('districtId', data.districtId);
    formData.append('address', data.address);
    formData.append('start_time', data.start_time);
    formData.append('end_time', data.end_time);
    formData.append('description', data.description || '');

    images.forEach((img, idx) => {
      formData.append('images[]', img.file);
      if (img.is_primary === 1) {
        formData.append('mainImageIndex', idx.toString());
      }
    });

    // // --- Log dữ liệu FormData ---
    // console.log('=== FormData entries ===');
    // for (let [key, value] of formData.entries()) {
    //   if (value instanceof File) {
    //     console.log(key, value.name, value.size, value.type);
    //   } else {
    //     console.log(key, value);
    //   }
    // }

    // Gửi dữ liệu
    mutate(formData);
  };





  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 p-4">
      <div className="container max-w-[600px] mx-auto rounded-2xl shadow-lg p-10 border-t-6 border-orange-500">
        <h1 className="text-3xl font-extrabold text-[#348738] mb-8 text-center">Đăng kí sân</h1>

        <form onSubmit={handleSubmit(onSubmit)}>
          {/* Thông tin cơ bản */}
          <div className="grid grid-cols-2 gap-5 py-5 border-b border-gray-200">
            <Input label="Tên thương hiệu (*)" id="name" type="text" placeholder="Nhập tên thương hiệu" {...register('name', { required: 'Tên thương hiệu là bắt buộc' })} error={errors.name?.message} />
            <Input label="Số điện thoại (*)" id="phone" type="tel" placeholder="Nhập số điện thoại" {...register('phone', { required: 'Số điện thoại là bắt buộc' })} error={errors.phone?.message} />
          </div>

          {/* Vị trí & Thời gian */}
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
              <Input label="Địa chỉ chi tiết (*)" id="address" type="text" placeholder="Nhập địa chỉ chi tiết" {...register('address', { required: 'Địa chỉ chi tiết là bắt buộc' })} error={errors.address?.message} />
              <Input label="Giờ mở cửa (*)" id="start_time" type="time" {...register('start_time', { required: 'Giờ mở cửa là bắt buộc' })} error={errors.start_time?.message} />
              <Input label="Giờ đóng cửa (*)" id="end_time" type="time" {...register('end_time', { required: 'Giờ đóng cửa là bắt buộc' })} error={errors.end_time?.message} />
            </div>
          </div>

          {/* Upload ảnh */}
          <div className="border-b py-5 border-gray-200">
            <Input label="Ảnh sân" id="images" type="file" accept="image/*" multiple onChange={handleImagesChange} />
            {images.length > 0 && (
              <div className="mt-3 flex flex-wrap gap-3">
                {images.map((img, idx) => (
                  <div key={idx} className="relative">
                    <img src={img.url} alt={`preview-${idx}`} className="w-24 h-24 object-cover rounded-lg border" />
                    <label className="absolute bottom-1 left-1 bg-white px-1 text-xs flex items-center gap-1 rounded">
                      <input
                        type="radio"
                        name="mainImage"
                        checked={img.is_primary === 1}
                        onChange={() => setMainImage(idx)}
                      />
                      Ảnh chính
                    </label>
                  </div>
                ))}
              </div>
            )}
          </div>

          {/* Mô tả */}
          <div className="border-b py-5 border-gray-200">
            <Textarea id="description" label="Mô tả chi tiết về sân" placeholder="Nhập các thông tin như: loại sân, chất lượng mặt cỏ, tiện ích đi kèm" rows={5} {...register('description')} />
          </div>

          <button type="submit" className="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 rounded-lg transition-all shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2 mt-4">
            Gửi Đăng Kí
          </button>
        </form>
      </div>
    </div>
  );
};

export default Create_Venue;
