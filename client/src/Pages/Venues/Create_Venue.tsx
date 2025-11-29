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

    // Gửi dữ liệu
    mutate(formData);
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 font-sans">
      <div className="max-w-3xl w-full bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
        
        {/* --- Header Form --- */}
        <div className="bg-gray-50/50 px-6 py-5 border-b border-gray-100 flex flex-col items-center">
          <div className="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center mb-3 text-[#10B981]">
             <i className="fa-solid fa-store text-xl"></i>
          </div>
          <h1 className="text-2xl font-bold text-[#11182C]">Đăng Ký Sân Mới</h1>
          <p className="text-sm text-gray-500 mt-1">Điền thông tin để đưa sân của bạn lên hệ thống BCP Sports</p>
        </div>

        <form onSubmit={handleSubmit(onSubmit)} className="p-6 md:p-8 space-y-6">
          
          {/* Section 1: Thông tin cơ bản */}
          <div>
            <h3 className="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4 flex items-center gap-2">
               <i className="fa-regular fa-id-card text-[#10B981]"></i> Thông tin chung
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
              <Input 
                label="Tên sân / Thương hiệu (*)" 
                id="name" 
                type="text" 
                placeholder="VD: Sân bóng BCP..." 
                {...register('name', { required: 'Tên thương hiệu là bắt buộc' })} 
                error={errors.name?.message} 
              />
              <Input 
                label="Số điện thoại liên hệ (*)" 
                id="phone" 
                type="tel" 
                placeholder="0912..." 
                {...register('phone', { required: 'Số điện thoại là bắt buộc' })} 
                error={errors.phone?.message} 
              />
            </div>
          </div>

          <hr className="border-gray-100" />

          {/* Section 2: Vị trí & Thời gian */}
          <div>
             <h3 className="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4 flex items-center gap-2">
               <i className="fa-solid fa-map-location-dot text-[#10B981]"></i> Địa điểm & Thời gian
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-5 mb-4">
              <Select
                id="provinceId"
                label="Tỉnh/Thành phố (*)"
                {...register('provinceId', { required: 'Vui lòng chọn tỉnh/thành' })}
                options={provinces.map(p => ({ value: p.id.toString(), label: p.name }))}
                error={errors.provinceId?.message}
              />
              <Select
                id="districtId"
                label="Quận/Huyện (*)"
                {...register('districtId', { required: 'Vui lòng chọn quận/huyện' })}
                options={provincesById?.districts.map(d => ({ value: d.id.toString(), label: d.name })) || []}
                disabled={!selectedProvinceId}
                error={errors.districtId?.message}
              />
            </div>
            
            <div className="mb-4">
               <Input 
                  label="Địa chỉ chi tiết (Số nhà, đường...) (*)" 
                  id="address" 
                  type="text" 
                  placeholder="VD: 123 Đường Nguyễn Văn A..." 
                  {...register('address', { required: 'Địa chỉ chi tiết là bắt buộc' })} 
                  error={errors.address?.message} 
               />
            </div>

            <div className="grid grid-cols-2 gap-5">
              <Input label="Giờ mở cửa (*)" id="start_time" type="time" {...register('start_time', { required: 'Bắt buộc' })} error={errors.start_time?.message} />
              <Input label="Giờ đóng cửa (*)" id="end_time" type="time" {...register('end_time', { required: 'Bắt buộc' })} error={errors.end_time?.message} />
            </div>
          </div>

          <hr className="border-gray-100" />

          {/* Section 3: Upload ảnh (Cải tiến UI) */}
          <div>
            <h3 className="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4 flex items-center gap-2">
               <i className="fa-regular fa-images text-[#10B981]"></i> Hình ảnh sân
            </h3>
            <div className="bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:bg-green-50/50 hover:border-[#10B981]/50 transition-colors">
               <Input label="Chọn ảnh (Nên chọn nhiều ảnh đẹp)" id="images" type="file" accept="image/*" multiple onChange={handleImagesChange} />
               <p className="text-xs text-gray-400 mt-2 italic">Hỗ trợ: JPG, PNG, WEBP (Tối đa 5MB/ảnh)</p>
            </div>
            
            {images.length > 0 && (
              <div className="mt-5 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                {images.map((img, idx) => (
                  <div 
                    key={idx} 
                    onClick={() => setMainImage(idx)}
                    className={`relative group rounded-lg overflow-hidden border-2 cursor-pointer transition-all ${img.is_primary ? 'border-[#10B981] ring-2 ring-[#10B981]/20 shadow-md' : 'border-gray-200 hover:border-gray-300'}`}
                  >
                    <img src={img.url} alt={`preview-${idx}`} className="w-full h-24 object-cover" />
                    
                    {/* Badge chọn ảnh chính */}
                    <div className={`absolute bottom-0 inset-x-0 py-1.5 text-[10px] font-bold text-center transition-colors ${img.is_primary ? 'bg-[#10B981] text-white' : 'bg-black/60 text-white/80 group-hover:bg-gray-700'}`}>
                      <input
                        type="radio"
                        name="mainImage"
                        checked={img.is_primary === 1}
                        onChange={() => {}} // Đã xử lý ở onClick div
                        className="hidden"
                      />
                      {img.is_primary === 1 ? <><i className="fa-solid fa-circle-check mr-1"></i> Ảnh đại diện</> : "Đặt làm ảnh chính"}
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>

          <hr className="border-gray-100" />

          {/* Section 4: Mô tả */}
          <div>
             <h3 className="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4 flex items-center gap-2">
               <i className="fa-solid fa-pen-to-square text-[#10B981]"></i> Mô tả
            </h3>
             <Textarea 
                id="description" 
                label="Giới thiệu về sân" 
                placeholder="Nhập các thông tin như: loại sân, chất lượng mặt cỏ, tiện ích đi kèm (wifi, căng tin, bãi xe...)" 
                rows={4} 
                {...register('description')} 
             />
          </div>

          {/* Submit Button */}
          <div className="pt-4">
            <button 
              type="submit" 
              className="w-full bg-[#10B981] hover:bg-[#059669] text-white text-sm font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-green-200 hover:shadow-xl hover:-translate-y-0.5 active:scale-95 flex items-center justify-center gap-2"
            >
               <i className="fa-solid fa-paper-plane"></i> Gửi Đăng Ký
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default Create_Venue;